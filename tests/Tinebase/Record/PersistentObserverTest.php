<?php
/**
 * Tine 2.0 - http://www.tine20.org
 * 
 * @package     Tinebase
 * @subpackage  Record
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2007-2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Cornelius Weiss <c.weiss@metaways.de>
 * @version     $Id$
 */

/**
 * Test helper
 */
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

// Call Tinebase_Record_PersistentObserverTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Tinebase_Record_PersistentObserverTest::main');
}

require_once 'PHPUnit/Framework.php';

//require_once 'Tinebase/Record/PersistentObserver.php';

/**
 * Test class for Tinebase_Record_PersistentObserver.
 * Generated by PHPUnit on 2008-02-22 at 12:22:53.
 */
class Tinebase_Record_PersistentObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Tinebase_Record_PersistentObserver
     * @access protected
     */
    protected $object;

    /**
     * initial data where test objects are created from
     *
     * @var array
     */
    protected $persistentObserverDatas = array(
        array(
            'observable_application' => 'Tasks',
            'observable_identifier'  => 3,
            'observer_application'   => 'Crm',
            'observer_identifier'    => 2,
            'observed_event'         => 'Tinebase_Record_Event_Delete'
        ),
        array(
            'observable_application' => 'Crm',
            'observable_identifier'  => 2,
            'observer_application'   => 'Addressbook',
            'observer_identifier'    => 1,
            'observed_event'         => 'Tinebase_Record_Event_Delete'
        )
    );
    
    /**
     * holds instances of the persistentObservers;
     *
     * @var array
     */
    protected $persistentObserver;
    
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('Tinebase_Record_PersistentObserverTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->object = Tinebase_Record_PersistentObserver::getInstance();
        
        foreach ($this->persistentObserverDatas as $num => $persistentObserverData) {
        	$this->persistentObserver[$num] = $this->object->addObserver(new Tinebase_Model_PersistentObserver($persistentObserverData, true));
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    	$db = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'record_persistentobserver'));
    	
    	foreach ($this->persistentObserver as $persistentObserver) {
    	     $db->delete(array(
    	         'identifier' =>$persistentObserver->getId()
    	     ));
    	}
    	
    }

    /**
     * testGetInstance().
     */
    public function testGetInstance() {
    	$this->assertTrue($this->object instanceof Tinebase_Record_PersistentObserver);
    }

    /**
     * @todo Implement testFireEvent().
     */
    public function testFireEvent() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * testAddObserver().
     */
    public function testAddObserver() {
    	$application = Tinebase_Application::getInstance();
        
    	foreach ($this->persistentObserver as $num => $persistentObserver) {
    		$this->assertEquals($application->getApplicationById($persistentObserver->observable_application)->app_name, $this->persistentObserverDatas[$num]['observable_application']);
    		$this->assertEquals($application->getApplicationById($persistentObserver->observer_application)->app_name, $this->persistentObserverDatas[$num]['observer_application']);
    	    $this->assertEquals($persistentObserver->observable_identifier, $this->persistentObserverDatas[$num]['observable_identifier']);
    	    $this->assertEquals($persistentObserver->observer_identifier, $this->persistentObserverDatas[$num]['observer_identifier']);
    	    $this->assertEquals($persistentObserver->observed_event, $this->persistentObserverDatas[$num]['observed_event']);
    	}
        
    }

    /**
     * @todo Implement testRemoveObserver().
     */
    public function testRemoveObserver() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testRemoveAllObservables().
     */
    public function testRemoveAllObservables() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetAllObservables().
     */
    public function testGetAllObservables() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetObservablesByEvent().
     */
    public function testGetObservablesByEvent() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}

// Call Tinebase_Record_PersistentObserverTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'Tinebase_Record_PersistentObserverTest::main') {
    Tinebase_Record_PersistentObserverTest::main();
}
?>
