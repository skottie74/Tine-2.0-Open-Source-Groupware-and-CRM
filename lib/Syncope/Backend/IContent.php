<?php
/**
 * Tine 2.0
 *
 * @package     Syncope
 * @subpackage  Backend
 * @license     http://www.tine20.org/licenses/agpl-nonus.txt AGPL Version 1 (Non-US)
 *              NOTE: According to sec. 8 of the AFFERO GENERAL PUBLIC LICENSE (AGPL), 
 *              Version 1, the distribution of the Syncope module in or to the 
 *              United States of America is excluded from the scope of this license.
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @copyright   Copyright (c) 2009-2012 Metaways Infosystems GmbH (http://www.metaways.de)
 * 
 */

/**
 * sql backend class for the folder state
 *
 * @package     Syncope
 * @subpackage  Backend
 */
interface Syncope_Backend_IContent
{
    /**
     * create new content state
     *
     * @param Syncope_Model_IContent $_contentState
     * @return Syncope_Model_IContent
     */
    public function create(Syncope_Model_IContent $_contentState);
        
    /**
     * mark state as deleted. The state gets removed finally, 
     * when the synckey gets validated during next sync.
     * 
     * @param Syncope_Model_IContent|string $_id
     */
    public function delete($_id);
    
    /**
     * @param Syncope_Model_IDevice|string $_deviceId
     * @param Syncope_Model_IFolder|string $_folderId
     * @param string $_contentId
     * @return Syncope_Model_IContent
     */
    public function getContentState($_deviceId, $_folderId, $_contentId);
}
