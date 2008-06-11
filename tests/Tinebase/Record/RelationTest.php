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

// Call Tinebase_Record_RelationTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'inebase_Record_RelationTest::main');
}

require_once 'PHPUnit/Framework.php';

/**
 * Test class for Tinebase_Record_Relation.
 * Generated by PHPUnit on 2008-02-22 at 18:34:08.
 */
class Tinebase_Record_RelationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Tinebase_Record_Relation
     * @access protected
     */
    protected $object;

    /**
     * initial data
     *
     * @var array
     */
    private $relationData = array(
        array(
            'own_model'              => 'Crm_Model_Lead',
            'own_backend'            => 'SQL',
            'own_id'                 => '268d586e46aad336de8fa2530b5b8faf921e494d',
            'own_degree'             => Tinebase_Model_Relation::DEGREE_PARENT,
            'related_model'          => 'Tasks_Model_Task',
            'related_backend'        => Tasks_Backend_Factory::SQL,
            'related_id'             => '8a572723e867dd73dd68d1740dd94f586eff5432',
            'type'                   => 'CRM_TASK'
        ),
        array(
            'own_model'              => 'Crm_Model_Lead',
            'own_backend'            => 'SQL',
            'own_id'                 => '268d586e46aad336de8fa2530b5b8faf921e494d',
            'own_degree'             => Tinebase_Model_Relation::DEGREE_PARENT,
            'related_model'          => 'Addressbook_Model_Contact',
            'related_backend'        => Addressbook_Backend_Factory::SQL,
            'related_id'             => 'ad59dd6d2e75aa0aca0abf2ab46b55bdcb0d6b18',
            'type'                   => 'PARTNER'
        ),
        array(
            'own_model'              => 'Tasks_Model_Task',
            'own_backend'            => Tasks_Backend_Factory::SQL,
            'own_id'                 => '8a572723e867dd73dd68d1740dd94f586eff5432',
            'own_degree'             => Tinebase_Model_Relation::DEGREE_SIBLING,
            'related_model'          => 'Addressbook_Model_Contact',
            'related_backend'        => Addressbook_Backend_Factory::SQL,
            'related_id'             => 'ad59dd6d2e75aa0aca0abf2ab46b55bdcb0d6b18',
            'type'                   => Tinebase_Model_Relation::TYPE_MANUAL,
            'remark'                 => 'Manually created relation by PHPUNIT'
        ),
    );
    
    /**
     * relation objects
     *
     * @var array
     */
    private $relations = array();
    
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite('Tinebase_Record_RelationTest');
        PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->object = Tinebase_Record_Relation::getInstance();
        foreach ($this->relationData as $num => $relation) {
        	$this->relations[$num] = $this->object->addRelation(new Tinebase_Model_Relation($relation, true));
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
        $db = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'record_relations'));

        foreach ($this->relations as $relation) {
             $db->delete(array(
                 $db->getAdapter()->quoteInto('id = ?',$relation->getId())
            ));
        }
    }

    /**
     * testGetInstance().
     */
    public function testGetInstance()
    {
        $this->assertTrue($this->object instanceof Tinebase_Record_Relation );
    }

    /**
     * testAddRelation().
     */
    public function testAddRelation() 
    {
        foreach ($this->relations as $num => $relation) {
            foreach ($this->relationData[$num] as $key => $value) {
                $this->assertEquals($value, $relation[$key]);
            }
        }
    }
    /**
     * test if swaped relations got created
     */
    public function testAddSwapRelation()
    {
    foreach ($this->relationData as $num => $relation) {
            $rel = $this->relations[$num];
            $swap = $this->object->getRelation($rel->getId(), $relation['related_model'], $relation['related_backend'], $relation['related_id']);
            $this->assertEquals($relation['own_id'], $swap->related_id);
            $this->assertEquals($relation['related_id'], $swap->own_id);
        }
    }
    /**
     * testBreakRelation in forward direktion.
     */
    public function testBreakRelationForward()
    {
        $rel = $this->relations[0];
        $this->object->breakRelation($rel->getId());
        $this->setExpectedException('Tinebase_Record_Exception_NotDefined');
        $this->object->getRelation($rel->getId(), $rel->own_model, $rel->own_backend, $rel->own_id);
    }
    /**
     * testBreakRelation in swaped direction.
     */
    public function testBreakRelationSwap()
    {
        $rel = $this->relations[0];
        $this->object->breakRelation($rel->getId());
        $this->setExpectedException('Tinebase_Record_Exception_NotDefined');
        $this->object->getRelation($rel->getId(), $rel->related_model, $rel->related_backend, $rel->related_id);
    }
    
    
    
    public function testBreakRelationExcludeSet()
    {    
        $rel = $this->relations[0];
        $this->object->breakRelation($rel->getId());
        
        // test getAllRelations, $this->relations[0] should not be in resultSet
        $record = new $this->relations[0]['own_model'](array(), true);
        $record->setId( $this->relations[0]->own_id );
        
        $relations = $this->object->getAllRelations($record);
        // test that the other relations still exists
        $this->assertGreaterThan(0, count($relations));
        foreach ($relations as $relation) {
        	$this->assertFalse($relation->getId() == $this->relations[0]->getId());
        }
    }

    /**
     * testBreakAllRelations().
     */
    public function testBreakAllRelations() {
        $record = new $this->relations[0]['own_model'](array(), true);
        $record->setId( $this->relations[0]->own_id );
        
        $this->object->breakAllRelations($record);
        $relations = $this->object->getAllRelations($record);
        $this->assertEquals(0, count($relations));
        
        // test that the other relations still exists
        $rel = $this->relations[2];
        $this->object->getRelation($rel->getId(), $rel->related_model, $rel->related_backend, $rel->related_id);
        
    }
}

// Call Tinebase_Record_RelationTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'Tinebase_Record_RelationTest::main') {
    Tinebase_Record_RelationTest::main();
}
?>
