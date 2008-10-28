<?php
/**
 * Tine 2.0
 * 
 * @package     Tinebase
 * @subpackage  Server
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @copyright   Copyright (c) 2007-2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @version     $Id: Json.php 5047 2008-10-22 10:51:07Z c.weiss@metaways.de $
 */

/**
 * Json interface to Tinebase
 * 
 * @package     Tinebase
 * @subpackage  Server
 */
class Tinebase_Frontend_Json extends Tinebase_Application_Frontend_Json_Abstract
{
	
    /**
     * get list of translated country names
     *
     * @return array list of countrys
     */
    public function getCountryList()
    {
        $locale = Zend_Registry::get('locale');

        $countries = $locale->getCountryTranslationList();
        asort($countries);
        foreach($countries as $shortName => $translatedName) {
            $results[] = array(
				'shortName'         => $shortName, 
				'translatedName'    => $translatedName
            );
        }

        $result = array(
			'results'	=> $results
        );

        return $result;
    }

    /**
     * returns list of all available translations
     * NOTE available are those, having a Tinebase translation
     * 
     * @return array list of all available translations
     *
     */
    public function getAvailableTranslations()
    {
        $availableTranslations = Tinebase_Translation::getAvailableTranslations();
        return array(
            'results'    => $availableTranslations,
            'totalcount' => count($availableTranslations)
        );
    }
    
    /**
     * sets locale
     *
     * @param  string $localeString
     * @param  bool   $saveaspreference
     * @return array
     */
    public function setLocale($localeString, $saveaspreference)
    {
        Tinebase_Core::setupUserLocale($localeString, $saveaspreference);
        $locale = Zend_Registry::get('locale');
        /* No need for return values yet. Client needs to reload!
        return array(
            'locale' => array(
                'locale'   => $locale->toString(), 
                'language' => $locale->getLanguageTranslation($locale->getLanguage()),
                'region'   => $locale->getCountryTranslation($locale->getRegion())
            )
        );
        */
    }

    /**
     * returns list of all available timezones in the current locale
     * 
     * @return array list of all available timezones
     *
     * @todo add territory to translation?
     */
    public function getAvailableTimezones()
    {
        $locale =  Zend_Registry::get('locale');

        $availableTimezonesTranslations = $locale->getTranslationList('citytotimezone');
        //asort($availableTimezones);
        //$availableTimezones = array_flip($availableTimezones);
        
        $availableTimezones = DateTimeZone::listIdentifiers();
        $result = array();
        foreach ($availableTimezones as $timezone) {
            $result[] = array(
                'timezone' => $timezone,
                'timezoneTranslation' => array_key_exists($timezone, $availableTimezonesTranslations) ? $availableTimezonesTranslations[$timezone] : NULL
            );
        }
        
        return array(
            'results'    => $result,
            'totalcount' => count($result)
        );
    }
    
    /**
     * sets timezone
     *
     * @param  string $timezoneString
     * @param  bool   $saveaspreference
     * @return string
     */
    public function setTimezone($timezoneString, $saveaspreference)
    {
        $timezone = Tinebase_Core::setupUserTimezone($timezoneString, $saveaspreference);
        
        return $timezone;
        /*
        return array(
            'locale' => array(
                'locale'   => $locale->toString(), 
                'language' => $locale->getLanguageTranslation($locale->getLanguage()),
                'region'   => $locale->getCountryTranslation($locale->getRegion())
            ),
            'translationFiles' => array(
                'generic' => Tinebase_Translation::getJsTranslationFile($locale, 'generic'),
                'tine'    => Tinebase_Translation::getJsTranslationFile($locale, 'tine'),
                'ext'     => Tinebase_Translation::getJsTranslationFile($locale, 'ext')
            ));
        */
    }
    
    /**
     * get users
     *
     * @param string $filter
     * @param string $sort
     * @param string $dir
     * @param int $start
     * @param int $limit
     * @return array with results array & totalcount (int)
     */
    public function getUsers($filter, $sort, $dir, $start, $limit)
    {
        $result = array(
            'results'     => array(),
            'totalcount'  => 0
        );
        
        if($rows = Tinebase_User::getInstance()->getUsers($filter, $sort, $dir, $start, $limit)) {
            $result['results']    = $rows->toArray();
            if($start == 0 && count($result['results']) < $limit) {
                $result['totalcount'] = count($result['results']);
            } else {
                //$result['totalcount'] = $backend->getCountByAddressbookId($addressbookId, $filter);
            }
        }
        
        return $result;
    }
    
    /**
     * get list of groups
     *
     * @param string $_filter
     * @param string $_sort
     * @param string $_dir
     * @param int $_start
     * @param int $_limit
     * @return array with results array & totalcount (int)
     */
    public function getGroups($filter, $sort, $dir, $start, $limit)
    {
        $result = array(
            'results'     => array(),
            'totalcount'  => 0
        );
        
        $groups = Tinebase_Group::getInstance()->getGroups($filter, $sort, $dir, $start, $limit);

        $result['results'] = $groups->toArray();
        $result['totalcount'] = count($groups);
        
        return $result;
    }
    
    /**
     * change password of user 
     *
     * @param string $oldPassword the old password
     * @param string $newPassword the new password
     * @return array
     */
    public function changePassword($oldPassword, $newPassword)
    {
        $response = array(
            'success'      => TRUE
        );
        
        try {
            Tinebase_Controller::getInstance()->changePassword($oldPassword, $newPassword, $newPassword);
        } catch (Tinebase_Exception $e) {
            $response = array(
                'success'      => FALSE,
                'errorMessage' => "New password could not be set! Error: " . $e->getMessage()
            );   
        }
        
        return $response;        
    }    
    
    /**
     * adds a new personal tag
     */
    public function saveTag($tag)
    {
        $tagData = Zend_Json::decode($tag);
        $inTag = new Tinebase_Model_Tag($tagData);
        
        if (strlen($inTag->getId()) < 40) {
            Zend_Registry::get('logger')->debug('creating tag: ' . print_r($inTag->toArray(), true));
            $outTag = Tinebase_Tags::getInstance()->createTag($inTag);
        } else {
            Zend_Registry::get('logger')->debug('updating tag: ' .print_r($inTag->toArray(), true));
            $outTag = Tinebase_Tags::getInstance()->updateTag($inTag);
        }
        return $outTag->toArray();
    }
    
    /**
     * gets tags for application / owners
     * 
     * @param   string  $context
     * @return array 
     * @deprecated ? use getTags or searchTags ?
     */
    public function getTags($context)
    {
        $filter = new Tinebase_Model_TagFilter(array(
            'name'        => '%',
            'application' => $context,
        ));
        $paging = new Tinebase_Model_Pagination();
        
        $tags = Tinebase_Tags::getInstance()->searchTags($filter, $paging)->toArray();
        return array(
            'results'    => $tags,
            'totalCount' => count($tags)
        );
    }
    
    /**
     * search tags
     *
     * @param string $query
     * @param string $context (application)
     * @param integer $start
     * @param integer $limit
     * @return array
     */
    public function searchTags($query, $context, $start=0, $limit=0)
    {
        $filter = new Tinebase_Model_TagFilter(array(
            'name'        => $query . '%',
            'application' => $context,
        ));
        $paging = new Tinebase_Model_Pagination(array(
            'start' => $start,
            'limit' => $limit,
            'sort'  => 'name',
            'dir'   => 'ASC'
        ));
        
        return array(
            'results'    => Tinebase_Tags::getInstance()->searchTags($filter, $paging)->toArray(),
            'totalCount' => Tinebase_Tags::getInstance()->getSearchTagsCount($filter)
        );
    }
    
    /**
     * search / get notes
     * - used by activities grid
     *
     * @param string $filter json encoded filter array
     * @param string $paging json encoded pagination info
     */
    public function searchNotes($filter, $paging)
    {
        $filter = new Tinebase_Model_NoteFilter(Zend_Json::decode($filter));
        $paging = new Tinebase_Model_Pagination(Zend_Json::decode($paging));
        
        //Zend_Registry::get('logger')->debug(print_r($filter->toArray(),true));
        //Zend_Registry::get('logger')->debug(print_r($paging->toArray(),true));
        
        return array(
            'results'       => Tinebase_Notes::getInstance()->searchNotes($filter, $paging)->toArray(),
            'totalcount'    => Tinebase_Notes::getInstance()->searchNotesCount($filter)
        );        
    }
    
    /**
     * get note types
     *
     * @todo add test
     */
    public function getNoteTypes()
    {
        $noteTypes = Tinebase_Notes::getInstance()->getNoteTypes();
        $noteTypes->translate();
        
        return array(
            'results'       => $noteTypes->toArray(),
            'totalcount'    => count($noteTypes)
        );        
    }
    
    /**
     * deletes tags identified by an array of identifiers
     * 
     * @param  array $ids
     * @return array 
     */
    public function deleteTags($ids)
    {
        Tinebase_Tags::getInstance()->deleteTags(Zend_Json::decode($ids));
        return array('success' => true);
    }
    

    /**
     * authenticate user by username and password
     *
     * @param string $username the username
     * @param string $password the password
     * @return array
     */
    public function login($username, $password)
    {
        if (Tinebase_Controller::getInstance()->login($username, $password, $_SERVER['REMOTE_ADDR']) === true) {
            $response = array(
				'success'       => TRUE,
                'account'       => Zend_Registry::get('currentAccount')->getPublicUser()->toArray(),
				'jsonKey'       => Zend_Registry::get('jsonKey'),
                'welcomeMessage' => "Welcome to Tine 2.0!"
			);
        } else {
            $response = array(
				'success'      => FALSE,
				'errorMessage' => "Wrong username or password!"
			);
        }

        return $response;
    }

    /**
     * destroy session
     *
     * @return array
     */
    public function logout()
    {
        Tinebase_Controller::getInstance()->logout($_SERVER['REMOTE_ADDR']);
        
        $result = array(
			'success'=> true,
        );

        return $result;
    }
    
    /**
     * Returns registry data of tinebase.
     * @see Tinebase_Application_Json_Abstract
     * 
     * @return mixed array 'variable name' => 'data'
     */
    public function getRegistryData()
    {
        $locale = Zend_Registry::get('locale');
        
        // default credentials
        if(isset(Zend_Registry::get('configFile')->login)) {
            $loginConfig = Zend_Registry::get('configFile')->login;
            $defaultUsername = (isset($loginConfig->username)) ? $loginConfig->username : '';
            $defaultPassword = (isset($loginConfig->password)) ? $loginConfig->password : '';
        } else {
            $defaultUsername = '';
            $defaultPassword = '';
        }
        
        $registryData =  array(
            'timeZone'         => Zend_Registry::get('userTimeZone'),
            'locale'           => array(
                'locale'   => $locale->toString(), 
                'language' => $locale->getLanguageTranslation($locale->getLanguage()),
                'region'   => $locale->getCountryTranslation($locale->getRegion()),
            ),
            'defaultUsername' => $defaultUsername,
            'defaultPassword' => $defaultPassword
        );

        if (Tinebase_Core::isRegistered(Tinebase_Core::USER)) {
            $registryData += array(    
                'currentAccount'   => Zend_Registry::get('currentAccount')->toArray(),
                'accountBackend'   => Tinebase_User::getConfiguredBackend(),
                'jsonKey'          => Zend_Registry::get('jsonKey'),
                'userApplications' => Zend_Registry::get('currentAccount')->getApplications()->toArray(),
                'NoteTypes'        => $this->getNoteTypes(),
                'CountryList'      => $this->getCountryList(),
                'version'          => array(
                    'codename'      => TINE20_CODENAME,
                    'packageString' => TINE20_PACKAGESTRING,
                    'releasetime'   => TINE20_RELEASETIME
                ),
                'changepw'         => (isset(Zend_Registry::get('configFile')->accounts) 
                                        && isset(Zend_Registry::get('configFile')->accounts->changepw))
                                            ? Zend_Registry::get('configFile')->accounts->changepw
                                            : false
            );
        }
        return $registryData;
    }
    
    /**
     * Returns registry data of all applications current user has access to
     * @see Tinebase_Application_Json_Abstract
     * 
     * @return mixed array 'variable name' => 'data'
     */
    public function getAllRegistryData()
    {
        $registryData = array();
        
        if (Tinebase_Core::isRegistered(Tinebase_Core::USER)) { 
            $userApplications = Zend_Registry::get('currentAccount')->getApplications();
            
            foreach($userApplications as $application) {
                $jsonAppName = ucfirst((string) $application) . '_Frontend_Json';
                if(class_exists($jsonAppName)) {
                    $applicationJson = new $jsonAppName;
                    
                    $registryData[ucfirst((string) $application)] = $applicationJson->getRegistryData();
                    $registryData[ucfirst((string) $application)]['rights'] = Zend_Registry::get('currentAccount')->getRights((string) $application);
                }
            }
        } else {
            $registryData['Tinebase'] = $this->getRegistryData();
        }
        
        die(Zend_Json::encode($registryData));
    }
}
