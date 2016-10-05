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

class EventgalleryLibraryFactoryFolder extends EventgalleryLibraryFactoryFactory
{
    protected $_folders;
    protected $_commentCount;

    /**
     * Returns a folder
     *
     * @param $foldername string|object
     * @return EventgalleryLibraryFolder
     */
    public function getFolder($foldername) {

        if (null == $foldername) {
            return null;
        }

        if (!is_string($foldername)) {
            throw new InvalidArgumentException("can get a folder by String only.");
        }

        if (!isset($this->_folders[$foldername])) {


            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('f.*, ft.folderhandlerclassname');
            $query->from('#__eventgallery_folder f, #__eventgallery_foldertype ft');
            $query->where('f.foldertypeid=ft.id');
            $query->where('folder='.$db->quote($foldername));

            $db->setQuery($query);
            $databaseFolder = $db->loadObject();

            if (isset($databaseFolder->folderhandlerclassname)) {
                $folderClass = $databaseFolder->folderhandlerclassname;
                /**
                 * @var EventgalleryLibraryFolder $folderClass
                 * */
                $this->_folders[$foldername] = new $folderClass($databaseFolder);
            } else {
                $this->_folders[$foldername] = null;
            }

        }

        return $this->_folders[$foldername];
    }


    public function getCommentCount($foldername)
    {
        if (!$this->_commentCount)
        {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true)
                ->select('folder, count(1) AS '.$db->quoteName('commentCount'))
                ->from($db->quoteName('#__eventgallery_comment'))
                ->where('published=1')
                ->group('folder');
            $db->setQuery($query);
            $comments = $db->loadObjectList();
            $this->_commentCount = array();
            foreach($comments as $comment)
            {
                $this->_commentCount[$comment->folder] = $comment->commentCount;
            }
        }

        if (isset($this->_commentCount[$foldername])) {
            return $this->_commentCount[$foldername];
        }

        return 0;
    }
}