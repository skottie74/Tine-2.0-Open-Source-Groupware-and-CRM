<?php
/**
 * Tine 2.0
 * 
 * @package     Tinebase
 * @subpackage  Account
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2007-2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @version     $Id$
 */

/**
 * sql implementation of the SQL accounts interface
 * 
 * @package     Tinebase
 * @subpackage  Account
 */
class Tinebase_Account_Sql implements Tinebase_Account_Interface
{
    /**
     * the constructor
     *
     * don't use the constructor. use the singleton 
     */
    private function __construct() {}
    
    /**
     * don't clone. Use the singleton.
     *
     */
    private function __clone() {}

    /**
     * holdes the instance of the singleton
     *
     * @var Tinebase_Account_Sql
     */
    private static $_instance = NULL;
    
    protected $rowNameMapping = array(
        'accountId'             => 'account_id',
        'accountDisplayName'    => 'n_fileas',
        'accountFullName'       => 'n_fn',
        'accountFirstName'      => 'n_given',
        'accountLastName'       => 'n_family',
        'accountLoginName'      => 'account_lid',
        'accountLastLogin'      => 'account_lastlogin',
        'accountLastLoginfrom'  => 'account_lastloginfrom',
        'accountLastPasswordChange' => 'account_lastpwd_change',
        'accountStatus'         => 'account_status',
        'accountExpires'        => 'account_expires',
        'accountPrimaryGroup'   => 'account_primary_group',
        'accountEmailAddress'   => 'contact_email'
    );
    
    
    /**
     * the singleton pattern
     *
     * @return Tinebase_Account_Sql
     */
    public static function getInstance() 
    {
        if (self::$_instance === NULL) {
            self::$_instance = new Tinebase_Account_Sql;
        }
        
        return self::$_instance;
    }
    
    /**
     * 
     * groupmembership handling
     * 
     */

    /**
     * return the group ids a account is member of
     *
     * @param int $accountId the accountid of a account
     * @todo the group info do not belong into the ACL table, there should be a separate group table
     * @return array list of group ids
     */
    public function getGroupMemberships($_accountId)
    {
        $accountId = (int)$_accountId;
        if($accountId != $_accountId) {
            throw new InvalidArgumentException('$_accountId must be integer');
        }
        
        $aclTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'acl'));
        
        $groupMemberShips = array();
        
        $where = array(
            $aclTable->getAdapter()->quoteInto('acl_appname = ?', 'phpgw_group'),
            $aclTable->getAdapter()->quoteInto('acl_account = ?', $_accountId)
        );
        
        $rowSet = $aclTable->fetchAll($where);
        
        foreach($rowSet as $row) {
            $groupMemberShips[] = $row->acl_location;
        }
        
        return $groupMemberShips;
    }
    
    /**
     * return a list of group members account id's
     *
     * @param int $groupId
     * @todo the group info do not belong into the ACL table, there should be a separate group table
     * @deprecated 
     * @return array list of group members account id's
     */
    public function getGroupMembers($_groupId)
    {
        $groupId = (int)$_groupId;
        if($groupId != $_groupId) {
            throw new InvalidArgumentException('$_groupId must be integer');
        }
        
        $aclTable = new Tinebase_Acl_Sql();
        $members = array();
        
        $where = array(
            "acl_appname = 'phpgw_group'",
            $aclTable->getAdapter()->quoteInto('acl_location = ?', $groupId)
        );
        
        $rowSet = $aclTable->fetchAll($where);
        
        foreach($rowSet as $row) {
            $members[] = $row->acl_account;
        }
        
        return $members;
    }
    
    /**
     * 
     * public account data handling
     * 
     */
    
    /**
     * get list of accounts
     *
     * @param string $_filter
     * @param string $_sort
     * @param string $_dir
     * @param int $_start
     * @param int $_limit
     * @param string $_accountClass the type of subclass for the Tinebase_Record_RecordSet to return
     * @return Tinebase_Record_RecordSet with record class Tinebase_Account_Model_Account
     */
    public function getAccounts($_filter = NULL, $_sort = NULL, $_dir = NULL, $_start = NULL, $_limit = NULL, $_accountClass = 'Tinebase_Account_Model_Account')
    {        
        $select = $this->_getAccountSelectObject()
            ->limit($_limit, $_start);
            
        if($_sort !== NULL) {
            $select->order($this->rowNameMapping[$_sort] . ' ' . $_dir);
        }

        if($_filter !== NULL) {
            $select->where('(n_family LIKE ? OR n_given LIKE ? OR account_lid LIKE ?)', '%' . $_filter . '%');
        }
        // return only active accounts, when searching for simple accounts
        if($_accountClass == 'Tinebase_Account_Model_Account') {
            $select->where('account_status = ?', 'A');
        }
        //error_log("getAccounts:: " . $select->__toString());

        $stmt = $select->query();

        $rows = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);

        $result = new Tinebase_Record_RecordSet($_accountClass, $rows);
        
        return $result;
    }
    
    /**
     * get account by login name
     *
     * @param string $_loginName the loginname of the account
     * @return Tinebase_Account_Model_Account the account object
     */
    public function getAccountByLoginName($_loginName, $_accountClass = 'Tinebase_Account_Model_Account')
    {
        $select = $this->_getAccountSelectObject()
            ->where(SQL_TABLE_PREFIX . 'accounts.account_lid = ?', $_loginName);

        //error_log("getAccounts:: " . $select->__toString());

        $stmt = $select->query();

        $row = $stmt->fetch(Zend_Db::FETCH_ASSOC);

        try {
            $account = new $_accountClass();
            $account->setFromArray($row);
        } catch (Exception $e) {
            $validation_errors = $account->getValidationErrors();
            Zend_Registry::get('logger')->debug( 'Tinebase_Account_Sql::getAccountByLoginName: ' . $e->getMessage() . "\n" .
                "Tinebase_Account_Model_Account::validation_errors: \n" .
                print_r($validation_errors,true));
            throw ($e);
        }
        
        return $account;
    }
    
    /**
     * get account by accountId
     *
     * @param int $_accountId the account id
     * @return Tinebase_Account_Model_Account the account object
     */
    public function getAccountById($_accountId, $_accountClass = 'Tinebase_Account_Model_Account')
    {
        $accountId = (int)$_accountId;
        if($accountId != $_accountId) {
            throw new InvalidArgumentException('$_accountId must be integer');
        }
        
        $select = $this->_getAccountSelectObject()
            ->where(SQL_TABLE_PREFIX . 'accounts.account_id = ?', $accountId);

        //error_log("getAccounts:: " . $select->__toString());

        $stmt = $select->query();

        $row = $stmt->fetch(Zend_Db::FETCH_ASSOC);
        if($row === false) {
            throw new Exception('account not found');
        }

        try {
            $account = new $_accountClass();
            $account->setFromArray($row);
        } catch (Exception $e) {
            $validation_errors = $account->getValidationErrors();
            Zend_Registry::get('logger')->debug( 'Tinebase_Account_Sql::_getAccountFromSQL: ' . $e->getMessage() . "\n" .
                "Tinebase_Account_Model_Account::validation_errors: \n" .
                print_r($validation_errors,true));
            throw ($e);
        }
        
        return $account;
    }
    
    protected function _getAccountSelectObject()
    {
        $db = Zend_Registry::get('dbAdapter');
        
        $select = $db->select()
            ->from(SQL_TABLE_PREFIX . 'accounts', 
                array(
                    'accountId' => $this->rowNameMapping['accountId'],
                    'accountLoginName' => $this->rowNameMapping['accountLoginName'],
                    'accountLastLogin' => 'FROM_UNIXTIME(`' . SQL_TABLE_PREFIX . 'accounts`.`account_lastlogin`)',
                    'accountLastLoginfrom' => $this->rowNameMapping['accountLastLoginfrom'],
                    'accountLastPasswordChange' => 'FROM_UNIXTIME(`' . SQL_TABLE_PREFIX . 'accounts`.`account_lastpwd_change`)',
                    'accountStatus' => $this->rowNameMapping['accountStatus'],
                    'accountExpires' => 'FROM_UNIXTIME(`' . SQL_TABLE_PREFIX . 'accounts`.`account_expires`)',
                    'accountPrimaryGroup' => $this->rowNameMapping['accountPrimaryGroup']
                )
            )
            ->join(
                SQL_TABLE_PREFIX . 'addressbook',
                SQL_TABLE_PREFIX . 'accounts.account_id = ' . SQL_TABLE_PREFIX . 'addressbook.account_id',
                array(
                    'accountDisplayName'    => $this->rowNameMapping['accountDisplayName'],
                    'accountFullName'       => $this->rowNameMapping['accountFullName'],
                    'accountFirstName'      => $this->rowNameMapping['accountFirstName'],
                    'accountLastName'       => $this->rowNameMapping['accountLastName'],
                    'accountEmailAddress'   => $this->rowNameMapping['accountEmailAddress']
                )
            );
                
        return $select;
    }
    
    /**
     * set the status of the account
     *
     * @param int $_accountId
     * @param unknown_type $_status
     * @return unknown
     */
    public function setStatus($_accountId, $_status)
    {
        $accountId = (int)$_accountId;
        if($accountId != $_accountId) {
            throw new InvalidArgumentException('$_accountId must be integer');
        }
        
        switch($_status) {
            case 'enabled':
                $accountData['account_status'] = 'A';
                break;
                
            case 'disabled':
                $accountData['account_status'] = 'D';
                break;
                
            case 'expired':
                $accountData['account_expires'] = Zend_Date::getTimestamp();
                break;
            
            default:
                throw new InvalidArgumentException('$_status can be only enabled, disabled or epxired');
                break;
        }
        
        $accountsTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'accounts'));

        $where = array(
            $accountsTable->getAdapter()->quoteInto('account_id = ?', $accountId)
        );
        
        $result = $accountsTable->update($accountData, $where);
        
        return $result;
    }
    
    /**
     * set the password for given account
     *
     * @param int $_accountId
     * @param string $_password
     * @deprecated moved to authentication class
     * @return void
     */
    private function setPassword($_accountId, $_password)
    {
        $accountId = (int)$_accountId;
        if($accountId != $_accountId) {
            throw new InvalidArgumentException('$_accountId must be integer');
        }
        
        $accountsTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'accounts'));
        
        $accountData['account_pwd'] = md5($_password);
        $accountData['account_lastpwd_change'] = Zend_Date::now()->getTimestamp();
        
        $where = array(
            $accountsTable->getAdapter()->quoteInto('account_id = ?', $accountId)
        );
        
        $result = $accountsTable->update($accountData, $where);
        if ($result != 1) {
            throw new Exception('Unable to update password');
        }
        
        return $result;
    }
    
    /**
     * update the lastlogin time of account
     *
     * @param int $_accountId
     * @param string $_ipAddress
     * @return void
     */
    public function setLoginTime($_accountId, $_ipAddress) 
    {
        $accountId = (int)$_accountId;
        if($accountId != $_accountId) {
            throw new InvalidArgumentException('$_accountId must be integer');
        }
        
        $accountsTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'accounts'));
        
        $accountData['account_lastloginfrom'] = $_ipAddress;
        $accountData['account_lastlogin'] = Zend_Date::now()->getTimestamp();
        
        $where = array(
            $accountsTable->getAdapter()->quoteInto('account_id = ?', $accountId)
        );
        
        $result = $accountsTable->update($accountData, $where);
        
        return $result;
    }
    
    /**
     * save a account
     * 
     * this function creates or updates an account 
     *
     * @param Tinebase_Account_Model_FullAccount $_account
     * @return Tinebase_Account_Model_FullAccount
     */
    public function saveAccount(Tinebase_Account_Model_FullAccount $_account)
    {
        if(!$_account->isValid()) {
            throw(new Exception('invalid account object'));
        }

        $accountsTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'accounts'));

        $accountData = array(
            'account_lid'       => $_account->accountLoginName,
            'account_status'    => $_account->accountStatus,
            'account_expires'   => ($_account->accountExpires instanceof Zend_Date ? $_account->accountExpires->getTimestamp() : NULL),
            'account_primary_group' => '-4'
        );
        
/*        if(!empty($_account->accountPassword)) {
            $accountData['account_pwd']            = new Zend_Db_Expr("MD5('" . $_account->accountPassword . "')");
            $accountData['account_lastpwd_change'] = Zend_Date::now()->getTimestamp();
        }*/
        
        $contactData = array(
            'n_family'      => $_account->accountLastName,
            'n_given'       => $_account->accountFirstName,
            'n_fn'          => $_account->accountFullName,
            'n_fileas'      => $_account->accountDisplayName,
            'contact_email' => $_account->accountEmailAddress
            #'account_id' 8
        );

        try {
            Zend_Registry::get('dbAdapter')->beginTransaction();
            
            $accountsTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'accounts'));
            $contactsTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'addressbook'));
            
            if(!empty($_account->accountId)) {
                $accountId = $_account->accountId;
                $where = array(
                    Zend_Registry::get('dbAdapter')->quoteInto('account_id = ?', $accountId)
                );
                
                $accountsTable->update($accountData, $where);
                $contactsTable->update($contactData, $where);
            } else {
                // add new account
                $accountData['account_type']    = 'u';
                $accountId = $accountsTable->insert($accountData);
                
                if($accountId == 0) {
                    throw new Exception("returned accountId is 0");
                }
                
                $contactData['account_id'] = $accountId;
                $contactData['contact_tid'] = 'n';
                $contactData['contact_owner'] = 1;
                
                $contactsTable->insert($contactData);
            }
            
            Zend_Registry::get('dbAdapter')->commit();
            
        } catch (Exception $e) {
            Zend_Registry::get('dbAdapter')->rollBack();
            throw($e);
        }
        
        return $this->getAccountById($accountId, 'Tinebase_Account_Model_FullAccount');
    }
    
    /**
     * add an account
     * 
     * this function adds an account 
     *
     * @param Tinebase_Account_Model_FullAccount $_account
     * @return Tinebase_Account_Model_FullAccount
     */
    public function addAccount(Tinebase_Account_Model_FullAccount $_account)
    {
        if(!$_account->isValid()) {
            throw(new Exception('invalid account object'));
        }

        $accountsTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'accounts'));

        $accountData = array(
            'login_name'    => $_account->accountLoginName,
            'status'        => $_account->accountStatus,
            'expires_at'    => ($_account->accountExpires instanceof Zend_Date ? $_account->accountExpires->getIso() : NULL),
            'primary_group' => $_account->accountPrimaryGroup,
        );
        if(!empty($_account->accountId)) {
            $accountData['id'] = $_account->accountId;
        }
        
        $contactData = array(
            'n_family'      => $_account->accountLastName,
            'n_given'       => $_account->accountFirstName,
            'n_fn'          => $_account->accountFullName,
            'n_fileas'      => $_account->accountDisplayName,
            'contact_email' => $_account->accountEmailAddress
        );

        try {
            Zend_Registry::get('dbAdapter')->beginTransaction();
            
            $accountsTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'accounts'));
            $contactsTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'addressbook'));
            
            // add new account
            $accountId = $accountsTable->insert($accountData);
            
            // if we insert an account without an accountId, we need to get back one
            if(empty($_account->accountId) && $accountId == 0) {
                throw new Exception("returned accountId is 0");
            }
            
            // if the account had no accountId set, set the id now
            if(empty($_account->accountId)) {
                $_account->accountId = $accountId;
            }
            
            $contactData['account_id'] = $accountId;
            $contactData['contact_tid'] = 'n';
            $contactData['contact_owner'] = 1;
            
            $contactsTable->insert($contactData);
            
            Zend_Registry::get('dbAdapter')->commit();
            
        } catch (Exception $e) {
            Zend_Registry::get('dbAdapter')->rollBack();
            throw($e);
        }
        
        return $_account;
    }
    
    /**
     * delete a account
     *
     * @param int $_accountId
     */
    public function deleteAccount($_accountId)
    {
        $accountId = (int)$_accountId;
        if($accountId != $_accountId) {
            throw new InvalidArgumentException('$_accountId must be integer');
        }
        
        $accountsTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'accounts'));
        $contactsTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'addressbook'));
        
        $where  = array(
            Zend_Registry::get('dbAdapter')->quoteInto('account_id = ?', $accountId),
        );
        
        try {
            Zend_Registry::get('dbAdapter')->beginTransaction();
            
            $contactsTable->delete($where);
            $accountsTable->delete($where);
            
            Zend_Registry::get('dbAdapter')->commit();
        } catch (Exception $e) {
            Zend_Registry::get('dbAdapter')->rollBack();
            throw($e);
        }
    }
}