<?php
/**
 * Tine 2.0
 * 
 * @package     Tinebase
 * @subpackage  Notes
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Philipp Schuele <p.schuele@metaways.de>
 * @copyright   Copyright (c) 2007-2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id$
 * 
 * @todo        add more filters (created_by, real datetime filter, ...)
 */

/**
 *  notes filter class
 * 
 * @package     Tinebase
 * @subpackage  Notes 
 */
class Tinebase_Model_NoteFilter extends Tinebase_Model_Filter_FilterGroup
{    
    /**
     * @var string application of this filter group
     */
    protected $_applicationName = 'Tinebase';
    
    /**
     * @var array filter model fieldName => definition
     */
    protected $_filterModel = array(
        'creation_time'  => array('filter' => 'Tinebase_Model_Filter_Text'),
        'query'          => array('filter' => 'Tinebase_Model_Filter_Query', 'options' => array('fields' => array('note'))),
        'record_id'      => array('filter' => 'Tinebase_Model_Filter_Text'),
        'record_model'   => array('filter' => 'Tinebase_Model_Filter_Text'),
        'record_backend' => array('filter' => 'Tinebase_Model_Filter_Text'),        
    
        //
        
        // not used yet
        /*
            'created_by'     => array('custom' => true),
            'record_id'              => array(),
            'record_model'           => array(),
            'record_backend'         => array(),        
            'note_type_id'           => array(),
        */
    );
    
    /**
     * appends current filters to a given select object
     * 
     * @param  Zend_Db_Select
     * @return void
     * 
     * @todo add created_by filter (join with user table for that?)
     */
    /*
    public function appendFilterSql($_select)
    {
        parent::appendFilterSql($_select);
    }
    */
}
