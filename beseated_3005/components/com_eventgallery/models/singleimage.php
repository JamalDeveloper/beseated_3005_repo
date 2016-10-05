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

jimport('joomla.application.component.model');


//jimport( 'joomla.application.component.helper' );

class EventgalleryModelSingleimage extends JModelLegacy
{

    /**
     * @var EventgalleryLibraryFolder
     */
    var $folder = NULL;
    /**
     * @var EventgalleryLibraryFile
     */
    var $file = NULL;
    var $nextFile = NULL;
    var $prevFile = NULL;
    var $nextFiles = Array();
    var $prevFiles = Array();
    var $firstFile = NULL;
    var $lastFile = NULL;
    var $position = 0;
    var $overallcount = 0;
    var $comments = NULL;
    var $_dataLoaded = false;
    var $currentLimitStart = 0;

    function __construct()
    {
        parent::__construct();

        $app = JFactory::getApplication();
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', JComponentHelper::getParams('com_eventgallery')->get('max_images_per_page', 20), 'int');
        $this->setState('limit', $limit);

        $params = JComponentHelper::getParams('com_eventgallery');
        $this->paging_images_count = $params->get('paging_images_count', 0);


    }


    function getData($foldername, $filename)
    {
        if (!$this->_dataLoaded) {
            $this->loadFolder($foldername);

            if ($this->folder == null) {
                return null;
            }
            
            // picasa files are not stored in the database
            $files = $this->folder->getFiles(0, -1);


            $i = 0;
            $filesCount = count($files);

            /**
             * @var EventgalleryLibraryFile $file
             */
            foreach ($files as $file) {
                if (strcmp($file->getFileName(), $filename) == 0) {
                    /**
                     * Update Hits
                     */

                    $file->countHit();

                    /**
                     * Set Data
                     */
                    $this->_dataLoaded = true;
                    $this->file = $file;
                    $this->prevFile = $files[max(0, $i - 1)];
                    $this->nextFile = $files[min($filesCount - 1, $i + 1)];


                    $lower = floor($this->paging_images_count / 2);
                    $upper = $this->paging_images_count - $lower;

                    $lowerStop = $i - 1;
                    $upperStart = $i + 1;


                    /**
                    Wenn weniger Bilder da sind, als für das BildPaging gebraucht werden
                     */
                    if ($filesCount - 1 < $this->paging_images_count) {
                        $lowerStart = 0;
                        $upperStop = $filesCount - 1;
                    } /* Wenn genug Bilder da sind*/
                    else {
                        $lowerStart = $i - $lower;
                        $upperStop = $i + $upper;

                        if ($lowerStart < 0) {
                            $upperStop += 0 - $lowerStart;
                            $lowerStart = 0;
                        }

                        if ($upperStop >= $filesCount) {
                            $lowerStart = $lowerStart - ($upperStop - $filesCount) - 1;
                            $upperStop = $filesCount - 1;
                        }

                    }


                    $this->nextFiles = array_slice($files, $upperStart, $upperStop - $upperStart + 1);
                    if ($lowerStop >= 0) {
                        $this->prevFiles = array_slice($files, $lowerStart, $lowerStop - $lowerStart + 1);
                    }

                    $this->lastFile = $files[count($files) - 1];
                    $this->firstFile = $files[0];
                    $this->loadComments();
                    $this->overallcount = count($files);
                    $this->position = $i + 1;

                    if ($this->getState('limit') > 0) {
                        $this->currentLimitStart = $i - ($i % $this->getState('limit'));
                    } else {
                        $this->currentLimitStart = 0;
                    }


                }


                $i++;
            }

        }
    }

    function loadFolder($foldername)
    {
        if (!$this->folder) {
            /**
             * @var EventgalleryLibraryFactoryFolder $folderFactory
             */
            $folderFactory = EventgalleryLibraryFactoryFolder::getInstance();
            $this->folder = $folderFactory->getFolder($foldername);
        }
    }

    function loadComments()
    {
        if (!$this->comments) {
            $query = $this->_db->getQuery(true)
                ->select('*')
                ->from($this->_db->quoteName('#__eventgallery_comment'))
                ->where('published=1')
                ->where('file=' . $this->_db->quote($this->file->getFileName()))
                ->where('folder=' . $this->_db->quote($this->file->getFolderName()))
                ->order('date DESC');
            $this->comments = $this->_getList($query);
            if (!$this->comments) {
                $this->comments = Array();
            }
        }
    }

    /**
     * @param $data
     * @param $published
     *
     * @return bool|TableComment
     */
    function store_comment($data, $published)
    {
        /**
         * @var TableComment $entry
         */
        $entry = $this->getTable('Comment');

        if (!$entry->bind($data, "published")) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        $entry->date = date("Y.m.d H:i:s", time());
        $entry->published = $published;
        $entry->user_id = JRequest::getVar("user_id", '0', 'COOKIE', 'INT');
        $entry->ip = $_SERVER['REMOTE_ADDR'];

        if (!$entry->store()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return $entry;
    }

    /**
     * This function was used as a comment filter before.
     *
     * @return array
     */
    function getBuzzwords()
    {
        /*$query = "SELECT * from #__buzzword where published=1";
        $buzzwordList = $this->_getList($query);
        $buzzwords = Array();
        if (is_array($buzzwordList))
        {
        	foreach($buzzwordList as $buzzword)
	        {
	            array_push($buzzwords, $buzzword->buzzword);
	        }
        }*/
        $buzzwords = array();
        return $buzzwords;

    }

}