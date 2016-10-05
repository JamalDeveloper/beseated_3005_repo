<?php

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.modellist' );

class EventgalleryModelSync extends JModelList
{

    /**
     * adds new folders to the databases
     * @return array with error Strings
     */
    public function addNewFolders() {
        /**
         * @var EventgalleryLibraryManagerFolder $folderMgr
         */
        $folderMgr = EventgalleryLibraryManagerFolder::getInstance();
        return $folderMgr->addNewFolders();

    }

    /*
    * returns the folders
    */
    public function getFolders() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('folder')
            ->from('#__eventgallery_folder')
            ->where('foldertypeid=0');
        $db->setQuery($query);
        $result = $db->loadColumn(0);

        return $result;
    }

    /*
    * syncs a folder and returns the status
    */
    public function syncFolder($folder) {

        $syncResult = EventgalleryLibraryFolderLocal::syncFolder($folder);

        $result = "";

        if ($syncResult == EventgalleryLibraryManagerFolder::$SYNC_STATUS_NOSYNC) {
            $result = "nosync";
        }

        if ($syncResult == EventgalleryLibraryManagerFolder::$SYNC_STATUS_SYNC)  {
            $result = "sync";
        }

        if ($syncResult == EventgalleryLibraryManagerFolder::$SYNC_STATUS_DELTED)  {
            $result = "deleted";
        }

        return $result;
    }
}
