<?php
/**
 * Tine 2.0
 *
 * @package     Crm
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Thomas Wadewitz <t.wadewitz@metaways.de>
 * @copyright   Copyright (c) 2007-2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id$
 *
 */

/**
 * interface for leads class
 *
 * @package     Crm
 */
class Crm_Backend_Sql implements Crm_Backend_Interface
{
	/**
	* Instance of Crm_Backend_Sql_Leads
	*
	* @var Crm_Backend_Sql_Leads
	*/
    protected $leadTable;

	/**
	* Instance of Crm_Backend_Sql_Leadsources
	*
	* @var Crm_Backend_Sql_Leadsources
	*/
    protected $leadSourceTable;

	/**
	* Instance of Crm_Backend_Sql_Leadtypes
	*
	* @var Crm_Backend_Sql_Leadtypes
	*/
    protected $leadTypeTable;
    
	/**
	* Instance of Crm_Backend_Sql_Products
	*
	* @var Crm_Backend_Sql_Products
	*/
    protected $productSourceTable;
    
	/**
	* Instance of Crm_Backend_Sql_Leadstates
	*
	* @var Crm_Backend_Sql_Leadstates
	*/
    protected $leadStateTable;    
        
    /**
    * Instance of Zend::db->adapter
    *
    */
    protected $_db;
    
	/**
	* the constructor
	*
	*/
    public function __construct()
    {
        $this->leadTable      		= new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'metacrm_lead'));
        $this->leadSourceTable   	= new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'metacrm_leadsource'));
        $this->leadTypeTable     	= new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'metacrm_leadtype'));
		$this->productSourceTable 	= new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'metacrm_productsource'));
        $this->leadStateTable    	= new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'metacrm_leadstate'));
        $this->productsTable   		= new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'metacrm_product'));
        $this->linksTable 			= new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'links'));
        
        $this->_db = Zend_Registry::get('dbAdapter');
        
    }
    
	/**
	 * get Leadsources
	 * 
     * @param string $_sort
     * @param string $_dir
	 * @return Tinebase_Record_RecordSet of subtype Crm_Model_Leadsource
	 */
    public function getLeadSources($_sort = 'id', $_dir = 'ASC')
    {	
		$rows = $this->leadSourceTable->fetchAll(NULL, $_sort, $_dir);
		
		$result = new Tinebase_Record_RecordSet('Crm_Model_Leadsource', $rows->toArray());
		
        return $result;
	}

    /**
    * add or updates an option
    *
    * @param Tinebase_Record_Recordset $_leadSources list of lead sources
    * @return unknown
    */
    public function saveLeadsources(Tinebase_Record_Recordset $_leadSources)
    {
        $db = Zend_Registry::get('dbAdapter');
  
        $db->beginTransaction();
        
        try {
            $db->delete(SQL_TABLE_PREFIX . 'metacrm_leadsource');

            foreach($_leadSources as $leadSource) {
                $db->insert(SQL_TABLE_PREFIX . 'metacrm_leadsource', $leadSource->toArray());                
            }

            $db->commit();

        } catch (Exception $e) {
            $db->rollBack();
            error_log($e->getMessage());
        }

        return $_leadSources;
    }
    
    /**
     * delete option identified by id and table
     *
     * @param int $_Id option id
     * @return int the number of rows deleted
     */
    public function deleteLeadsourceById($_Id)
    {
        if(empty($_Id)) {
            throw new InvalidArgumentException('$_Id ID has to be set');
        }
        $where  = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('leadsource_id') . ' = ?', $Id),
        );
             
        $result = $this->leadSourceTable->delete($where);

        return $result;
    }
    
    
	/**
	 * get Leadtypes
	 * 
     * @param string $_sort
     * @param string $_dir
	 * @return Tinebase_Record_RecordSet of subtype Crm_Model_Leadtype
	 */
    public function getLeadTypes($_sort = 'id', $_dir = 'ASC')
    {	
		$rows = $this->leadTypeTable->fetchAll(NULL, $_sort, $_dir);
        
		$result = new Tinebase_Record_RecordSet('Crm_Model_Leadtype', $rows->toArray());
		
		return $result;
    }	
    
	/**
	* add or updates an option
	*
	* @param Crm_Leadtype $_optionData the optiondata
	* @return unknown
	*/
    public function saveLeadtypes(Tinebase_Record_Recordset $_optionData)
    {

        $_daten = $_optionData->toArray();
    

        $db = Zend_Registry::get('dbAdapter');
  
        $db->beginTransaction();
        
        try {
            $db->delete(SQL_TABLE_PREFIX . 'metacrm_leadtype');

            foreach($_daten as $_data) {
                $db->insert(SQL_TABLE_PREFIX . 'metacrm_leadtype', $_data);                
            }

            $db->commit();

        } catch (Exception $e) {
            $db->rollBack();
            error_log($e->getMessage());
        }

        return $_optionData;
    }

    /**
     * delete option identified by id and table
     *
     * @param int $_Id option id
     * @param $_table which option section
     * @return int the number of rows deleted
     */
    public function deleteLeadtypeById($_Id = NULL)
    {
        if(NULL === $_Id) {
            throw new InvalidArgumentException('ID has to be set');
        }
            $where  = array(
                $this->_db->quoteInto($this->_db->quoteIdentifier('leadtype_id') . ' = ?', $_Id),
            );
             
            $result = $this->leadTypeTable->delete($where);

        return $result;
    }    
    
	/**
	* get LeadContacts
	*
	* @return unknown
	*/  
    public function getLeadContacts(Tinebase_Record_Recordset $_leads)
    {    
        $leads = $_leads->toArray();
        
        foreach($leads AS $lead)
        {
            $_id = $lead['id'];
            $_contact = $this->getContactsById($_id);
            $leadContacts[$_id] = $_contact;
        }
    
        return $leadContacts;
    }
  
    /**
     * get leadstates
     * 
     * @param string $_sort
     * @param string $_dir
     * @return Tinebase_Record_RecordSet of subtype Crm_Model_Leadstate
     */
    public function getLeadStates($_sort = 'id', $_dir = 'ASC')
    {   
        $rows = $this->leadStateTable->fetchAll(NULL, $_sort, $_dir);
        
        $result = new Tinebase_Record_RecordSet('Crm_Model_Leadstate', $rows->toArray());
        
        return $result;
    }   
    
	/**
     * get state identified by id
     *
     * @param int $_leadStateId
     * @return Crm_Model_Leadstate
     */
    public function getLeadState($_leadStateId = NULL)
    {   
        if(NULL === $_leadStateId) {
            throw new InvalidArgumentException('leadStateId must be set');
        }
        $rowSet = $this->leadStateTable->find($_leadStateId);
        
        if(count($rowSet) === 0) {
            throw new Exception('lead state not found');
        }
        
        $result = new Crm_Model_Leadstate($rowSet->current()->toArray());
   
        return $result;
    }
        
    /**
    * get leadtype identified by id
    *
    * @param int $_typeId
    * @return Crm_Model_Leadtype
    */
    public function getLeadType($_typeId = NULL)
    {   
        if(NULL === $_typeId) {
            throw new InvalidArgumentException('typeId must be set');
        }
        $rowSet = $this->leadTypeTable->find($_typeId);
        
        if(count($rowSet) == 0) {
            throw new Exception('lead type not found');
        }
        
        $result = new Crm_Model_Leadtype($rowSet->current()->toArray());
   
        return $result;
    }
        
    /**
    * get leadsource identified by id
    *
    * @return Crm_Model_Leadsource
    */
    public function getLeadSource($_sourceId = NULL)
    {   
        if(NULL === $_sourceId) {
            throw new InvalidArgumentException('$_sourceId must be integer');
        }
        $rowSet = $this->leadSourceTable->find($_sourceId);
        
        if(count($rowSet) == 0) {
            // something bad happend
        }
        
        $result = new Crm_Model_Leadsource($rowSet->current()->toArray());
   
        return $result;
    }    
    
	/**
	* add or updates an option
	*
	* @param Crm_Leadstate $_optionData the optiondata
	* @return unknown
	*/
    public function saveLeadstates(Tinebase_Record_Recordset $_optionData)
    {

        $_daten = $_optionData->toArray();
  
        $this->_db->beginTransaction();
        
        try {
            $this->_db->delete(SQL_TABLE_PREFIX . 'metacrm_leadstate');

            foreach($_daten as $_data) {
                $this->_db->insert(SQL_TABLE_PREFIX . 'metacrm_leadstate', $_data);                
            }

            $this->_db->commit();

        } catch (Exception $e) {
            $this->_db->rollBack();
            error_log($e->getMessage());
        }

        return $_optionData;
    }

    /**
     * delete option identified by id and table
     *
     * @param int $_Id option id
     * @param $_table which option section
     * @return int the number of rows deleted
     */
    public function deleteLeadstateById($_Id = NULL)
    {
        if(NULL === $_Id) {
            throw new InvalidArgumentException('Id must be set');
        }      
            $where  = array(
                $this->_db->quoteInto($this->_db->quoteIdentifier('leadstate_id') . ' = ?', $_Id),
            );
             
            $result = $this->leadStateTable->delete($where);

        return $result;
    }        

    /**
     * create search filter
     *
     * @param string $_filter
     * @param int $_leadstate
     * @param int $_probability
     * @param bool $_getClosedLeads
     * @return array
     */
    protected function _getSearchFilter($_filter, $_leadstate, $_probability, $_getClosedLeads)
    {
        $where = array();
        
        if(!empty($_filter)) {
            $search_values = explode(" ", $_filter);
            
            foreach($search_values AS $search_value) {
                $where[] = $this->_db->quoteInto($this->_db->quoteIdentifier('lead_name') . ' LIKE ?', '%' . $search_value . '%') . ' OR ' . $this->_db->quoteInto($this->_db->quoteIdentifier('description') . 'LIKE ?', '%' . $search_value . '%');                            
            }
        }
        if( !empty($_leadstate) ) {
            $where[] = $this->_db->quoteInto($this->_db->quoteIdentifier('lead.leadstate_id') . '= ?', $_leadstate);
        }
        
        if( is_numeric($_probability) && $_probability > 0 ) {
            $where[] = $this->_db->quoteInto($this->_db->quoteIdentifier('lead.probability') . '>= ?', $_probability);
        }       

        if($_getClosedLeads === FALSE  || $_getClosedLeads == 'false') {
            $where[] = 'end IS NULL';
        }
        return $where;
    }
    
    //handle for FOLDER->LEADS functions
    protected function _getLeadsFromTable(array $_where, $_filter, $_sort, $_dir, $_limit, $_start, $_leadstate, $_probability, $_getClosedLeads)
    {
		
        $where = array_merge($_where, $this->_getSearchFilter($_filter, $_leadstate, $_probability, $_getClosedLeads));

        $select = $this->_getLeadSelectObject()
            ->order($_sort . ' ' . $_dir)
            ->limit($_limit, $_start);

         foreach($where as $whereStatement) {
              $select->where($whereStatement);
         }               
       //echo  $select->__toString();
       
        $stmt = $this->_db->query($select);

        $rows = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
        
        $leads = new Tinebase_Record_RecordSet('Crm_Model_Lead', $rows);
        		
        return $leads;
    }   
    
    /**
     * get the basic select object to fetch leads from the database 
     *
     * @todo do we need this join here
     * @return Zend_Db_Select
     */
    protected function _getLeadSelectObject()
    {
		
        $db = Zend_Registry::get('dbAdapter');
		$selectObject = $db->select()
            ->from(array('lead' => SQL_TABLE_PREFIX . 'metacrm_lead'), array(
                'id',
                'lead_name',
                'leadstate_id',
                'leadtype_id',
                'leadsource_id',
                'container_id',
                'start',
                'description',
                'end',
                'turnover',
                'probability',
                'end_scheduled')
            )
            ->join(array('leadstate' => SQL_TABLE_PREFIX . 'metacrm_leadstate'),
				$this->_db->quoteIdentifier('lead.leadstate_id') .'=' . $this->_db->quoteIdentifier('leadstate.id'), array( 'leadstate') );        
				// 'lead.id = leadstate.id');
				
			//echo $selectObject->__toString();		
				
        return $selectObject;
    }

    protected function _addQuickSearchFilter($_where, $_filter)
    {
        if(!empty($_filter)) {
            $search_values = explode(" ", $_filter);
            
            foreach($search_values AS $search_value) {
                $where[] = $this->_db->quoteInto($this->_db->quoteIdentifier('lead_name') . ' LIKE ?', '%' . $search_value . '%') . ' OR ' . $this->_db->quoteInto($this->_db->quoteIdentifier('description') . 'LIKE ?', '%' . $search_value . '%');                            
            }
        }
        
        return $_where;
    }


    /**
     * get list of leads from all shared folders the current user has access to
     *
     * @param string $_filter string to search for in leads
     * @param unknown_type $_sort fieldname to sort by
     * @param unknown_type $_dir sort ascending or descending (ASC | DESC)
     * @param unknown_type $_limit how many leads to display
     * @param unknown_type $_start how many leads to skip
     * @param int $_leadstate
     * @param int $_probability
     * @param bool $_getClosedLeads
     * @return Tinebase_Record_RecordSet subclass Crm_Model_Lead
     */
/*    public function getAllLeads($_filter, $_sort, $_dir, $_limit = NULL, $_start = NULL, $_leadstate, $_probability, $_getClosedLeads)
    {
        $allContainer = Zend_Registry::get('currentAccount')->getContainerByACL('crm', Tinebase_Container::GRANT_READ);
        
        if(count($allContainer) === 0) {
            $this->createPersonalContainer();
            $allContainer = Zend_Registry::get('currentAccount')->getContainerByACL('crm', Tinebase_Container::GRANT_READ);
        }        
        $containerIds = array();
        
        foreach($allContainer as $container) {
            $containerIds[] = $container->id;
        }
        
        $where = array(
            Zend_Registry::get('dbAdapter')->quoteInto('container IN (?)', $containerIds)
        );
        $result = $this->_getLeadsFromTable($where, $_filter, $_sort, $_dir, $_limit, $_start, $_leadstate, $_probability, $_getClosedLeads);
         
        return $result;
    } */
        
    /**
    * add a lead
    *
    * @param Crm_Lead $_leadData the leaddata
    * @return Crm_Model_Lead
    */
    public function addLead(Crm_Model_Lead $_lead)
    {
        if(!$_lead->isValid()) {
            throw new Exception('lead object is not valid');
        }
        $id = Tinebase_Account_Model_Account::generateUID();
        $leadData = $_lead->toArray();
        if('' ==  ($_lead->id)) {
           $leadData['id'] = $id;
           $_lead->setId($id);
        }
        Zend_Registry::get('logger')->debug("LEAD ARRAY" . print_r($leadData,true));
        unset($leadData['responsible']);
        unset($leadData['customer']);
        unset($leadData['partner']);
        unset($leadData['tasks']);
        
        $this->leadTable->insert($leadData);
        
        // if we insert a contact without an id, we need to get back one
        if(empty($_lead->id) && $id == 0) {
            throw new Exception("returned lead id is 0");
        }
        
        return $this->getLead($_lead->id);
        
    }
    
    /**
     * get lead
     *
     * @param int|Crm_Model_Lead $_id
     * @return Crm_Model_Lead
     */
    public function getLead($_id)
    {
        $id = Crm_Model_Lead::convertLeadIdToInt($_id);

        $select = $this->_getLeadSelectObject()
            ->where($this->_db->quoteInto($this->_db->quoteIdentifier('lead.id') . ' = ?', $id));

      // echo $select->__toString();
       
        $stmt = $select->query();
        Zend_Registry::get('logger')->debug("lead search" . $select->__toString());

        $row = $stmt->fetch(Zend_Db::FETCH_ASSOC);
        
        if(empty($row)) {
            throw new UnderflowException("lead not found ". $select->__toString());
             Zend_Registry::get('logger')->debug("lead not found " . $select->__toString());
        }
        
        $lead = new Crm_Model_Lead($row);

        return $lead;
    }

    /**
     * get list of leads from all shared folders the current user has access to
     *
     * @param array $_container container to read the contacts from
     * @param string $_filter string to search for in leads
     * @param unknown_type $_sort fieldname to sort by
     * @param unknown_type $_dir sort ascending or descending (ASC | DESC)
     * @param unknown_type $_limit how many leads to display
     * @param unknown_type $_start how many leads to skip
     * @param int $_leadstate
     * @param int $_probability
     * @param bool $_getClosedLeads
     * @return Tinebase_Record_RecordSet subclass Crm_Model_Lead
     */
    public function getLeads(array $_container, $_filter = NULL, $_sort = 'id', $_dir = 'ASC', $_limit = NULL, $_start = NULL, $_leadState = NULL, $_probability = NULL, $_getClosedLeads = FALSE)
    {
        if(count($_container) === 0) {
            throw new Exception('$_container can not be empty');
        }        

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('container_id') . 'IN (?)', $_container)
        );
        $result = $this->_getLeadsFromTable($where, $_filter, $_sort, $_dir, $_limit, $_start, $_leadState, $_probability, $_getClosedLeads);
         
        return $result;
    }
    
    /**
     * get total count of leads matching filter
     *
     * @param array $_container
     * @param string $_filter
     * @param int $_leadState
     * @param int $_probability
     * @param bool $_getClosedLeads
     * @return int total number of matching leads
     */
    public function getCountOfLeads(array $_container, $_filter = NULL, $_leadState = NULL, $_probability = NULL, $_getClosedLeads = FALSE)
    {
        if(count($_container) === 0) {
            throw new Exception('$_container can not be empty');
        }        
        
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('container_id') . 'IN (?)', $_container)
        );
                
        $where = array_merge($where, $this->_getSearchFilter($_filter, $_leadState, $_probability, $_getClosedLeads));
        
        $result = $this->leadTable->getTotalCount($where);

        return $result;
    }
    
    /**
     * delete lead
     *
     * @param int|Crm_Model_Lead $_leads lead ids
     * @return void
     */
    public function deleteLead($_leadId)
    {
        $leadId = Crm_Model_Lead::convertLeadIdToInt($_leadId);
        
        $this->_db->beginTransaction();
        
        try {
            $where = array(
                $this->_db->quoteInto($this->_db->quoteIdentifier('lead_id') . ' = ?', $leadId)
            );          
            $this->_db->delete(SQL_TABLE_PREFIX . 'metacrm_product', $where);            

            $where = array(
                $this->_db->quoteInto($this->_db->quoteIdentifier('link_app1') . '= ?', 'Crm'),
                $this->_db->quoteInto($this->_db->quoteIdentifier('link_id1') . '= ?', $leadId),
                $this->_db->quoteInto($this->_db->quoteIdentifier('link_app2') . '= ?', 'Addressbook')
            );                                  
            $this->_db->delete(SQL_TABLE_PREFIX . 'links', $where);               
            
            $where = array(
                $this->_db->quoteInto($this->_db->quoteIdentifier('id') .' = ?', $leadId)
            );
            $this->_db->delete(SQL_TABLE_PREFIX . 'metacrm_lead', $where);

            $this->_db->commit();

        } catch (Exception $e) {
            $this->_db->rollBack();
            throw $e;
        }
    }

    /**
     * updates a lead
     *
     * @param Crm_Lead $_leadData the leaddata
     * @return Crm_Model_Lead
     */
    public function updateLead(Crm_Model_Lead $_lead)
    {
        if(!$_lead->isValid()) {
            throw new Exception('lead object is not valid');
        }
        
        $leadId = Crm_Model_Lead::convertLeadIdToInt($_lead);        

        $leadData = $_lead->toArray();
        unset($leadData['id']);
        unset($leadData['responsible']);
        unset($leadData['customer']);
        unset($leadData['partner']);
        unset($leadData['tasks']);
        
        $where  = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('id') . ' = ?', $leadId),
        );
        
        $updatedRows = $this->leadTable->update($leadData, $where);
                
        return $this->getLead($leadId);
    }

    /**
     * get available products
     * 
     * @param string $_sort
     * @param string $_dir
     * @return Tinebase_Record_RecordSet of subtype Crm_Model_Product
     */
    public function getProducts($_sort = 'id', $_dir = 'ASC')
    {   
        $rows = $this->productSourceTable->fetchAll(NULL, $_sort, $_dir);
        
        $result = new Tinebase_Record_RecordSet('Crm_Model_Productsource', $rows->toArray());
        
        return $result;
    }   
    
    /**
     * add or updates an option
     *
     * @param Crm_Productsource $_optionData the optiondata
     * @return unknown
     */
    public function saveProductsource(Tinebase_Record_Recordset $_optionData)
    {
  
        $this->_db->beginTransaction();
        
        try {
            $this->_db->delete(SQL_TABLE_PREFIX . 'metacrm_productsource');

            foreach($_optionData as $_product) {
                $this->_db->insert(SQL_TABLE_PREFIX . 'metacrm_productsource', $_product->toArray());                
            }

            $this->_db->commit();

        } catch (Exception $e) {
            $this->_db->rollBack();
            error_log($e->getMessage());
        }

        return $_optionData;
    }
    /**
     * delete option identified by id and table
     *
     * @param int $_Id option id
     * @param $_table which option section
     * @return int the number of rows deleted
     */
    public function deleteProductsourceById($_Id = NULL)
    {
        if(NULL === $_Id) {
            throw new InvalidArgumentException('Id must be set');
        }      
        $where  = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('leadsource_id') . ' = ?', $_Id),
        );
             
            $result = $this->productSourceTable->delete($where);

        return $result;
    }    
    
    /**
     * get products by lead id
     *
     * @param int $_leadId the leadId
     * @return Tinebase_Record_RecordSet of subtype Crm_Model_Product
     */
    public function getProductsByLeadId($_leadId)
    {
        $leadId = Crm_Model_Lead::convertLeadIdToInt($_leadId);

        $where  = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('lead_id') . ' = ?', $leadId)
        );

        $rows = $this->productsTable->fetchAll($where);
        
        $result = new Tinebase_Record_RecordSet('Crm_Model_Product', $rows->toArray());
   
        return $result;
    }      

    /**
    * delete products (which belong to one lead)
    *
    * @param int $_Id the id of the lead
    *
    * @return unknown
    */
    public function deleteProducts($_id = NULL)
    {
        if(NULL === $_id) {
            throw new InvalidArgumentException('ID must be integer');
        }
        
        try {          
            $this->_db->delete(SQL_TABLE_PREFIX . 'metacrm_product', $this->_db->quoteIdentifier('lead_id') . ' = ' . $_id);      
        } catch (Exception $e) {
            error_log($e->getMessage());
        }      
        
        return true;
   
    }

    /**
    * add or updates an product (which belongs to one lead)
    *
    * @param int $_productId the id of the product, NULL if new, else gets updated
    * @param Crm_Product $_productData the productdata
    * @param int $_leadId the lead id
    * @return unknown
    */
    public function saveProducts(Tinebase_Record_Recordset $_productData)
    {    
        $_daten = $_productData->toArray();
    
        $lead_id = $_daten[0]['lead_id'];

        /*
        if(!<$lead_id) {
             return $_productData;  
        }
        */

        $this->_db->beginTransaction();
        
        try {
            $this->_db->delete(SQL_TABLE_PREFIX . 'metacrm_product', $this->_db->quoteIdentifier('lead_id') . '= ' . $lead_id);

            foreach($_daten as $_data) {
                $this->_db->insert(SQL_TABLE_PREFIX . 'metacrm_product', $_data);                
            }

            $this->_db->commit();

        } catch (Exception $e) {
            $this->_db->rollBack();
            error_log($e->getMessage());
        }

        return $_optionData;
    }    
}
