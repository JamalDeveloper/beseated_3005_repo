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

class EventgalleryModelFiles extends JModelList
{

    protected $_id = null;
    protected $_item = null;

    public function __construct() {
        $ids = JRequest::getString('folderid');
        $this->_id = $ids;
        parent::__construct();
    }

	function getListQuery()
	{
		// Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);



		$query->select('file.*');
        $query->select('COUNT(comment.id) AS '.$db->quoteName('commentCount'));
		$query->from('#__eventgallery_file AS file');
        $query->join('','#__eventgallery_folder AS folder on folder.folder=file.folder');
        $query->leftJoin('#__eventgallery_comment comment on file.folder=comment.folder and file.file=comment.file');
		$query->where('folder.id='.$this->_db->Quote($this->_id));
		$query->group('file.id');
		$query->order('file.ordering DESC, file.file');

		return $query;
	}

    function getItem()
    {
        // Load the data
        if (empty( $this->_item )) {
            $query = $this->_db->getQuery(true)
                ->select('*')
                ->from($this->_db->quoteName('#__eventgallery_folder'))
                ->where('id='.$this->_db->quote($this->_id));
            $this->_db->setQuery( $query );
            $this->_item = $this->_db->loadObject();
        }

        if (!$this->_item) {

            $this->_item = $this->getTable('folder');
        }
        return $this->_item;
    }

	


}
