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

class EventgalleryLibraryFactoryFileLocal extends EventgalleryLibraryFactoryFactory
{

    protected $_folders = Array();

    /**
     * Returns a file
     *
     * @param $foldername string
     * @param $filename string
     * @return EventgalleryLibraryFileLocal
     */
    public function getFile($foldername, $filename) {
        $db = $this->db;
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__eventgallery_file');
        $query->where('folder=' . $db->Quote($foldername));
        $query->where('file=' . $db->Quote($filename));
        $db->setQuery($query);
        $file = $db->loadObject();

        return new EventgalleryLibraryFileLocal($file);

    }


}