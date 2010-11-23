<?php
/**
 * Tine 2.0
 * 
 * @package     Calendar
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Cornelius Weiss <c.weiss@metaways.de>
 * @copyright   Copyright (c) 2010 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id$
 */

/**
 * @see for german holydays http://www.sunbird-kalender.de/extension/kalender/
 * 
 * @todo add support for rrule exceptions
 * @todo add support for alarms
 * @todo add support for attendee / organizer
 * @todo add support for categories
 *
 */
class Calendar_Import_Ical extends Tinebase_Import_Abstract
{
    protected $_config = array(
        /**
         * force update of existing events 
         * @var boolean
         */
        'updateExisting'        => TRUE,
        /**
         * updates exiting events if sequence number is higher
         * @var boolean
         */
        'forceUpdateExisting'   => FALSE,
        /**
         * container the events should be imported in
         * @var string
         */
        'importContainerId'     => NULL,
    );
    
    /**
     * default timezone from VCALENDAR. If not present, users default tz will be taken
     * @var string
     */
    protected $_defaultTimezoneId;
    
    /**
     * maps tine20 propertynames to ical propertynames
     * @var array
     */
    protected $_eventPropertyMap = array(
        'summary'               => 'SUMMARY',
        'description'           => 'DESCRIPTION',
        'class'                 => 'CLASS',
        'transp'                => 'TRANSP',
        'seq'                   => 'SEQUENCE',
        'uid'                   => 'UID',
        'dtstart'               => 'DTSTART',
        'dtend'                 => 'DTEND',
        'rrule'                 => 'RRULE',
//        '' => 'DTSTAMP',
        'creation_time'         => 'CREATED',
        'last_modified_time'    => 'LAST-MODIFIED',
    );
    
    /**
     * import the data
     *
     * @param  stream $_resource 
     * @return array : 
     *  'results'           => Tinebase_Record_RecordSet, // for dryrun only
     *  'totalcount'        => int,
     *  'failcount'         => int,
     *  'duplicatecount'    => int,
     */
    public function import($_resource = NULL)
    {
        //@TODO check reqired configs
        if (! $this->_config['importContainerId']) {
            throw new Tinebase_Exception_InvalidArgument('you need to define a importContainerId');
        }
        
        $icalData = stream_get_contents($_resource);
        
        $parser = new qCal_Parser();
        $ical = $parser->parse($icalData);
        
        $this->_import($ical);
    }
    
    /**
     * imports given qCal_Component_Vcalendar into configured calendar
     * 
     * @param qCal_Component_Vcalendar $_ical
     */
    protected function _import(qCal_Component_Vcalendar $_ical)
    {
        $events = $this->_getEvents($_ical);
//        print_r($events->toArray());
        
        // set container
        $events->container_id = $this->_config['importContainerId'];
        
        $cc = Calendar_Controller_Event::getInstance();
        $sendNotifications = $cc->sendNotifications(FALSE);
        
        // search uid's and remove already existing -> only in import cal?
        $existingEvents = $cc->search(new Calendar_Model_EventFilter(array(
            array('field' => 'container_id', 'operator' => 'equals', 'value' => $this->_config['importContainerId']),
            array('field' => 'uid', 'operator' => 'in', 'value' => array_unique($events->uid)),
        )), NULL);
        
        // insert one by one in a single transaction
        $tid = Tinebase_TransactionManager::getInstance()->startTransaction(Tinebase_Core::getDb());
        $existingEvents->addIndices(array('uid'));
        foreach($events as $event) {
            $existingEvent = $existingEvents->find('uid', $event->uid);
            if (! $existingEvent) {
                $cc->create($event, FALSE);
            } else if ($this->_config['forceUpdateExisting'] || ($this->_config['updateExisting'] && $event->seq > $existingEvent->seq)) {
                $event->id = $existingEvent->getId();
                $event->last_modified_time = clone $existingEvent->last_modified_time;
                $cc->update($event, FALSE);
            }
        }
        Tinebase_TransactionManager::getInstance()->commitTransaction($tid);
        $cc->sendNotifications($existingEvents);
    }
    
    /**
     * converts VEVENT to an Calendar_Model_Event
     * 
     * @param   qCal_Component $vevent
     * @return  Calendar_Model_Event
     */
    protected function _getEvent(qCal_Component $vevent)
    {
        $eventData = array();
        
        // timezone
        if ($vevent->hasComponent('VTIMEZONE')) {
            $tz = array_value(0, $vevent->getComponent('VTIMEZONE'));
            $eventData['originator_tz'] = array_value(0, $tz->getProperty('TZID'))->getValue();
        } else {
            $eventData['originator_tz'] = $this->_defaultTimezoneId;
        }
        
        foreach($this->_eventPropertyMap as $tineName => $icalName) {
            if ($vevent->hasProperty($icalName)) {
                $icalValue = array_value(0, $vevent->getProperty($icalName));
                
                switch ($icalValue->getType()) {
                    case 'DATE':
                        $value = new Tinebase_DateTime($icalValue->getValue() . 'T000000', $eventData['originator_tz']);
                        
                        // events with dtstart given as date are allday events!
                        if ($tineName == 'dtstart') {
                            $eventData['is_all_day_event'] = true;
                        }
                        
                        if ($tineName == 'dtend') {
                            $value = $value->addSecond(-1);
                        } 
                        break;
                    case 'DATE-TIME':
                        $value = new Tinebase_DateTime($icalValue->getValue(), $eventData['originator_tz']);
                    case 'TEXT':
                        $value = str_replace(array('\\,', '\\n'), array(',', "\n"), $icalValue->getValue());
                        break;
                    default:
                        $value = $icalValue->getValue();
                        break;
                }
                $eventData[$tineName] = $value;
            }
        }
        
        $event = new Calendar_Model_Event($eventData);
        $event->setTimezone('UTC');
                        
        return $event;
    }
    
    /**
     * convert a VCALENDAR into a Tinebase_Record_RecordSet of Calendar_Model_Event
     * 
     * @param   qCal_Component_Vcalendar $component
     * @return  Tinebase_Record_RecordSet of Calendar_Model_Event
     */
    protected function _getEvents(qCal_Component_Vcalendar $component)
    {
        $events = new Tinebase_Record_RecordSet('Calendar_Model_Event');
        
        // do we have a generic timezone?
        if ($component->hasComponent('VTIMEZONE')) {
            $tz = array_value(0, $component->getComponent('VTIMEZONE'));
            $this->_defaultTimezoneId = array_value(0, $tz->getProperty('TZID'))->getValue();
        } else {
            $this->_defaultTimezoneId = (string) Tinebase_Core::get(Tinebase_Core::USERTIMEZONE);
        }
        
        foreach ($component->getChildren() as $children) {
            if (is_array($children)) {
                foreach ($children as $child) {
                    if ($child->getName() === 'VEVENT') {
                        $events->addRecord($this->_getEvent($child));
                    }
                }
            } else {
                if ($children->getName() === 'VEVENT') {
                    $events->addRecord($this->_getEvent($children));
                }
            }
        }
        
        return $events;
    }
}