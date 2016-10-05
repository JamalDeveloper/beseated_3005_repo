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


abstract class EventgalleryLibraryFolder extends EventgalleryLibraryDatabaseObject
{

    /**
     * @var string
     */
    protected $_foldername = NULL;

    /**
     * @var TableFolder
     */
    protected $_folder = NULL;

    /**
     * @var EventgalleryLibraryImagetypeset
     */
    protected $_imagetypeset = NULL;

    protected $_filecount = NULL;

    protected $_attribs = NULL;

    protected $_metadata = NULL;

    /**
     * @var EventgalleryLibraryDatabaseLocalizablestring
     */
    protected $_ls_description = NULL;

    /**
     * @var EventgalleryLibraryDatabaseLocalizablestring
     */
    protected $_ls_text = NULL;

    /**
     * @var EventgalleryLibraryDatabaseLocalizablestring
     */
    protected $_ls_passwordhint = NULL;


    /**
     * @param $object object
     */
    public function __construct($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException("Can't create folder object without an valid data object");
        }

        $this->_folder = $object;
        $foldername = $this->_folder->folder;

        $this->_foldername = $foldername;
        if ($this->_folder == null) {
            $this->_loadFolder();
        }

        $this->_prepareData();

        parent::__construct();
    }


    /**
     * use this method to sync new folders to the database
     *
     * @return null|string
     */
    public static function addNewFolders() {
        return null;
    }

    /**
     * @return mixed returns the Factory to create a file for this folder.
     */
    public static function getFileFactory() {
        return null;
    }

    /**
     * loads a folder from the databas
     */
    protected function _loadFolder()
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__eventgallery_folder');
        $query->where('folder=' . $db->Quote($this->_foldername));

        $db->setQuery($query);
        $folderObject = $db->loadObject();

        $this->_folder = $folderObject;

        

    }

	/**
	* Load necessary data for this folder object.
	*/
    protected function _prepareData() {
    	
    	if ($this->_folder == null) {
            return;
        }

		$this->_ls_description = new EventgalleryLibraryDatabaseLocalizablestring($this->_folder->description);
        $this->_ls_text = new EventgalleryLibraryDatabaseLocalizablestring($this->_folder->text);
        $this->_ls_passwordhint = new EventgalleryLibraryDatabaseLocalizablestring($this->_folder->passwordhint);

        /**
         * @var EventgalleryLibraryFactoryImagetypeset $imagetypesetFactory
         */
        $imagetypesetFactory = EventgalleryLibraryFactoryImagetypeset::getInstance();

        if ($this->_folder->imagetypesetid == null) {
            $this->_imagetypeset = $imagetypesetFactory->getDefaultImageTypeSet(true);
        } else {
            $this->_imagetypeset = $imagetypesetFactory->getImageTypeSet($this->_folder->imagetypesetid);
            if (!$this->_imagetypeset->isPublished()) {
                $this->_imagetypeset = $imagetypesetFactory->getDefaultImageTypeSet(true);
            }
        }
    }

    /**
     * @return string
     */
    public function getFolderName()
    {
        return $this->_folder->folder;
    }

    /**
     * @return EventgalleryLibraryImagetypeset
     */
    public function getImageTypeSet()
    {
        return $this->_imagetypeset;
    }

    /**
     * @return bool
     */
    public function isCartable()
    {
        return $this->_folder->cartable == 1;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        if (!isset($this->_folder)) {
            return false;
        }
        return $this->_folder->published == 1;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->_folder->password;
    }

    public function getPasswordHint()
    {
        if ($this->_ls_passwordhint == null) {
            return "";
        }
        return $this->_ls_passwordhint->get();
    }

    public function getUserGroupIds()
    {
        return $this->_folder->usergroupids;
    }

    /**
     * returns a set of attributes
     *
     * @return \Joomla\Registry\Registry
     */
    public function getAttribs() {

        if ($this->_attribs == NULL) {
            $registry = new JRegistry;
            $registry->loadString($this->_folder->attribs);
            $this->_attribs = $registry;
        }

        return $this->_attribs;
    }

    /**
     * returns a set of attributes containing metadata information
     *
     * @return JRegistry
     */
    public function getMetadata() {

        if ($this->_metadata == NULL) {
            $registry = new JRegistry;
            $registry->loadString($this->_folder->metadata);
            $this->_metadata = $registry;
        }

        return $this->_metadata;
    }

    /**
     * Returns true if the folder has a password and this password is already entered for this session.
     *
     * @return bool
     */
    public function isAccessible()
    {

        if (strlen($this->getPassword()) > 0) {
            $session = JFactory::getSession();
            $unlockedFoldersJson = $session->get("eventgallery_unlockedFolders", "");

            $unlockedFolders = array();
            if (strlen($unlockedFoldersJson) > 0) {
                $unlockedFolders = json_decode($unlockedFoldersJson, true);
            }

            if (!in_array($this->_foldername, $unlockedFolders)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if the current user is allowed to see this item. This
     * method is useful if you want to check the user group restrictions for this folder.
     *
     * @return bool
     */
    public function isVisible() {
        $user = JFactory::getUser();

        /**
         * @var \Joomla\Registry\Registry $params
         */
        $params = JComponentHelper::getParams('com_eventgallery');
        $minUserGroups = $params->get('eventgallery_default_usergroup');

        // if no user groups are set at all
        if (strlen($this->getUserGroupIds())==0 && count($minUserGroups)==0 ) {
            return true;
        }

        // use the default usergroups if the folder does not define any
        if (strlen($this->getUserGroupIds())==0) {
            $folderUserGroups = $minUserGroups;
        } else {
            $folderUserGroups = explode(',', $this->getUserGroupIds());
        }

        // if the public user group is part of the folder user groups
        if (in_array(1, $folderUserGroups)) {
            return true;
        }


        $userUserGroups = $user->groups;
        foreach($userUserGroups as $userUserGroup) {

            if (count(array_intersect(EventgalleryHelpersUsergroups::getGroupPath($userUserGroup), $folderUserGroups))>0 ) {
                return true;
            }
        }

        return false;
    }

    /**
     * returns true is this folder is visible for the public user group.
     * @return bool
     */
    public function isPublicVisible() {

        /**
         * @var \Joomla\Registry\Registry $params
         */
        $params = JComponentHelper::getParams('com_eventgallery');
        $minUserGroups = $params->get('eventgallery_default_usergroup');

        // if no user groups are set at all
        if (strlen($this->getUserGroupIds())==0 && count($minUserGroups)==0 ) {
            return true;
        }

        // use the default usergroups if the folder does not define any
        if (strlen($this->getUserGroupIds())==0) {
            $folderUserGroups = $minUserGroups;
        } else {
            $folderUserGroups = explode(',', $this->getUserGroupIds());
        }

        // if the public user group is part of the folder user groups
        if (in_array(1, $folderUserGroups)) {
            return true;
        }

        return false;
    }

    /**
     * returns the text for the folder.
     *
     * @return String
     */
    public function getText() {
        if ($this->_ls_text == null) {
            return "";
        }

        $splittedText = EventgalleryHelpersTextsplitter::split($this->_ls_text->get());
        return $splittedText->fulltext;
    }

    /**
     * returns the intro text for the folder if there is a splitter in the text.
     * Otherwise the introtext is the same as the text.
     *
     * @return String
     */
    public function getIntroText() {
        if ($this->_ls_text == null) {
            return "";
        }
        $splittedText = EventgalleryHelpersTextsplitter::split($this->_ls_text->get());
        return $splittedText->introtext;
    }

    /**
     * Returns the display name of this folder
     *
     * @return string
     */
    public function getDisplayName() {
        if ($this->_ls_description == null) {
            return "";
        }
        return $this->_ls_description->get();
    }

    /**
     * returns the date field
     *
     * @return string
     */
    public function getDate() {
        return $this->_folder->date;
    }

    /**
     * returns the number of comments for an event.
     *
     * @return mixed
     */
    public function getCommentCount() {

        /**
         * @var EventgalleryLibraryFactoryFolder $folderFactory
         */
        $folderFactory = EventgalleryLibraryFactoryFolder::getInstance();
        return $folderFactory->getCommentCount($this->_foldername);

    }

    /**
     * returns the number of files in this folder
     *
     * @param bool $publishedOnly defines is the return value contains unpublished files.
     * @return int
     */
    public function getFileCount($publishedOnly = true) {

        // this value might be part of a sql query
        if (isset($this->_folder->overallCount)) {
            return $this->_folder->overallCount;
        }

        if ($this->_filecount === NULL) {

            $db = JFactory::getDBO();
            $query = $db->getQuery(true)
                ->select('count(1)')
                ->from($db->quoteName('#__eventgallery_file') . ' AS file')
                ->where('folder='.$db->quote($this->_foldername))
                ->where('(file.ismainimageonly IS NULL OR file.ismainimageonly=0)');
            if ($publishedOnly) {
                $query->where('file.published=1');
            }
            $db->setQuery( $query );
            $this->_filecount = $db->loadResult();

        }

        return $this->_filecount;

    }

    /**
     * @param int $limitstart
     * @param int $limit
     * @param int $imagesForEvents if true load the main images at the first position
     * @return array
     */
    public abstract function getFiles($limitstart = 0, $limit = 0, $imagesForEvents = 0);


    public function getFolderTags() {
        return $this->_folder->foldertags;
    }

    /**
     * @return bool
     */
    public function isCommentingAllowed() {
        return true;
    }


    /**
     * syncs a folder with the used data structure
     *
     * @param $foldername string
     */
    public static function syncFolder($foldername) {

    }

    /**
     * returns the watermark object for this folder
     *
     * @return EventgalleryLibraryWatermark|null
     */
    public function getWatermark() {

        /**
         * @var EventgalleryLibraryFactoryWatermark $watermarkFactory
         * @var EventgalleryLibraryWatermark $watermark
         */
        $watermarkFactory = EventgalleryLibraryFactoryWatermark::getInstance();

        $watermark = $watermarkFactory->getWatermarkById($this->_folder->watermarkid);

        return $watermark;
    }

    /**
    * Returns the category id of this folder
    *
    * @return int|null
    */
    public function getCategoryId() {
        return $this->_folder->catid;
    }

    /**
     * returns the id of the folder
     * @return int
     */
    public function getId() {
        return $this->_folder->id;
    }

    /**
     * returns the folder type for this folder
     *
     * @return EventgalleryLibraryFoldertype|null
     */
    public function getFolderType() {

        /**
         * @var EventgalleryLibraryFactoryFoldertype $foldertypeFactory
         * @var EventgalleryLibraryFoldertype $foldertype
         */
        $foldertypeFactory = EventgalleryLibraryFactoryFoldertype::getInstance();

        $foldertype = $foldertypeFactory->getFolderTypeById($this->_folder->foldertypeid);

        return $foldertype;
    }


	/**
	* Returns the number of hits for this folder.
	* @return int
	*/
    public function getHits() {
    	return $this->_folder->hits;
    }

     /**
     * increases the hit counter in the database
     */
    public function countHits() {
        /**
         * @var TableFolder $table
         */
        $table = JTable::getInstance('Folder', 'Table');


        $table->bind((array)$this->_folder);
        $table->hits++;
        $table->store();


    }
}
