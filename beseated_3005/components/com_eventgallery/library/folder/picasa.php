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


class EventgalleryLibraryFolderPicasa extends EventgalleryLibraryFolder
{

    const PICASA_FOLDERID_DELIMITER = '@';

    /**
     * the picasa album data container
     */
    protected $_album;


    /**
     * Returns the number of files in this album.
     *
     * @param bool $publishedOnly
     * @return int
     */
    public function getFileCount($publishedOnly = true) {
        return $this->getAlbum()->overallCount;
    }

    /**
     * @param int $limitstart
     * @param int $limit
     * @param int $imagesForEvents if true load the main images at the first position
     * @return array
     */
    public function getFiles($limitstart = 0, $limit = 0, $imagesForEvents = 0) {

        if ($limitstart < 0) {
            $limitstart = 0;
        }

        $album = $this->getAlbum();

        $entries = $album->photos;

        if ( $imagesForEvents && $album->overallCount>0) {
            array_unshift($entries, new EventgalleryLibraryFilePicasa($album));
        }

        $entries = $limit > 0 ? array_slice($entries, $limitstart, $limit) : $entries;

        $result = Array();
        /**
         * @var EventgalleryLibraryFactoryFilePicasa $picasaFileFactory
         */
        $picasaFileFactory = EventgalleryLibraryFactoryFilePicasa::getInstance();

        foreach($entries as $entry) {
            if (is_array($entry)) {
                $result[] = $picasaFileFactory->getFile($entry['folder'], $entry['file']);
            } else {
                $result[] = $entry;
            }
        }

        return $result;

    }

    /**
     * returns the picasa key
     *
     * @return string
     */
    public function getPicasaKey() {
        if ($this->_folder == null) {
            return "";
        }
        return $this->_folder->picasakey;
    }

    /**
     * returns the picasa user
     *
     * @return string
     */
    public function getUserId() {
        $values = explode(self::PICASA_FOLDERID_DELIMITER, $this->_foldername);
        $userId = implode(self::PICASA_FOLDERID_DELIMITER, array_slice($values, 0, count($values)-1) );
        return $userId;
    }

    /**
     * returns the picasa album id
     *
     * @return string
     */
    public function getAlbumId() {
        $values = explode(self::PICASA_FOLDERID_DELIMITER, $this->_foldername);
        if (count($values) > 1) {
            $albumId = implode(self::PICASA_FOLDERID_DELIMITER, array_slice($values, count($values)-1, 1) );
            return $albumId;
        }
        return $this->_foldername;
    }

    /**
     * Returns the parsed picasa album object.
     *
     * @return object
     */
    public function getAlbum() {
        if ($this->_album == NULL) {
            $this->_album = EventgalleryHelpersImageHelper::picasaweb_ListAlbum($this->getUserId(), $this->getAlbumId(), $this->getPicasaKey());
        }

        return $this->_album;
    }

    /**
     * @return bool
     */
    public function isCommentingAllowed() {
        return true;
    }

    public static function syncFolder($foldername) {
        return EventgalleryLibraryManagerFolder::$SYNC_STATUS_NOSYNC;
    }

    public static function addNewFolders() {
        return Array();

    }

    public static function getFileFactory() {
        return EventgalleryLibraryFactoryFilePicasa::getInstance();
    }
}
