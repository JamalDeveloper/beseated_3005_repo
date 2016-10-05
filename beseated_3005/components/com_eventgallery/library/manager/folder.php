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

class EventgalleryLibraryManagerFolder extends  EventgalleryLibraryManagerManager
{

    public static $SYNC_STATUS_NOSYNC = 0;
    public static $SYNC_STATUS_SYNC = 1;
    public static $SYNC_STATUS_DELTED = 2;

    /**
     * scans the main dir and adds new folders to the database
     * Does not add Files!
     *
     * @return array with error strings
     */
    public function addNewFolders() {

        $errors = Array();

        /**
         * @var EventgalleryLibraryFactoryFoldertype $foldertypeFactory
         * @var EventgalleryLibraryFoldertype $folderType
         */
        $foldertypeFactory = EventgalleryLibraryFactoryFoldertype::getInstance();

        foreach($foldertypeFactory->getFolderTypes(true) as $folderType) {
            $folderClass = $folderType->getFolderHandlerClassname();
            /**
             * @var EventgalleryLibraryFolder $folderClass
             * */
            $errors = array_merge($errors, $folderClass::addNewFolders());
        }

        return $errors;
    }


}
