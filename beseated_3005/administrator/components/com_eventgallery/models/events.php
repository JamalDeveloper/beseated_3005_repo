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

/** @noinspection PhpUndefinedClassInspection */
class EventgalleryModelEvents extends JModelList
{

    protected $context = '';

    /**
     * Constructor.
     *
     * @param   array  $config An optional associative array of configuration settings.
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'folder',
                'date',
                'ordering',
                'published',
                'cartable',
                'hits'
            );
        }
        parent::__construct($config);
    }
	
	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function getListQuery()
	{
		
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

        // Filter by tag
        $filtertag = $this->getState('filter.tag', null);
        if ($filtertag !== null && strlen($filtertag)>0 && $filtertag!='*')
        {
            if ($filtertag=='------') {
                $query->where('foldertags = \'\'');
            } else {
                $query->where('foldertags like \'%'.$db->escape($filtertag).'%\'');
            }
        }


        // Filter by type
        $filtertag = $this->getState('filter.type', null);
        if ($filtertag !== null && strlen($filtertag)>0 && $filtertag!='*')
        {
            $query->where('f.foldertypeid = '. $db->quote($filtertag));
        }

        // Filter by category
        $filtertag = $this->getState('filter.category', null);
        if ($filtertag !== null && strlen($filtertag)>0 && $filtertag!='*')
        {
                $query->where('f.catid=' . $db->escape($filtertag) );
        }

		$query->select('f.*, COUNT(c.id) AS '.$db->quoteName('commentCount'));
		$query->from('#__eventgallery_foldertype ft, #__eventgallery_folder f left join #__eventgallery_comment c on f.folder=c.folder');
        $query->where('f.foldertypeid=ft.id');
		$query->group('f.id');

        // Join over the categories.
        $query->select('cat.title AS category_title')
            ->join('LEFT', '#__categories AS cat ON cat.id = f.catid');

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            $search = $db->Quote('%'.$db->escape($search, true).'%');
            $query->where('(f.folder LIKE '.$search.' OR f.text LIKE '.$search.' OR f.foldertags LIKE '.$search.' OR f.description LIKE '.$search.')');
        }

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering');
        if ($orderCol=='' || $orderCol=='id') {
            $orderCol = 'ordering';
        }

        $orderDirn	= $this->state->get('list.direction');
        if ($orderDirn=='') {
            $orderDirn = 'DESC';
        }

        $query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     * @param null $ordering
     * @param null $direction
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // set the search state
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context.'.filter.type', 'filter_type');
        $this->setState('filter.type', $search);
        $search = $this->getUserStateFromRequest($this->context.'.filter.tag', 'filter_tag');
        $this->setState('filter.tag', $search);
        $search = $this->getUserStateFromRequest($this->context.'.filter.category', 'filter_category');
        $this->setState('filter.category', $search);

        // List state information.
        parent::populateState('ordering', 'desc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id	A prefix for the store id.
     * @return  string  A store id.
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.type');
        $id .= ':' . $this->getState('filter.tag');
        $id .= ':' . $this->getState('filter.category');

        return parent::getStoreId($id);
    }

    /**
     * returns an hashmap with foldertags tag=>displayname
     */
    public function getTags() {

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('foldertags');
        $query->from('#__eventgallery_folder');

        $db->setQuery($query);
        $rawtags = $db->loadObjectList();

        $tags = array();

        foreach($rawtags as $rawtag) {
            $tags = array_merge($tags, EventgalleryHelpersTags::splitTags($rawtag->foldertags));
        }

        $tags = array_unique($tags);


        $result = array();
        foreach($tags as $tag) {
            $result[$tag] = $tag;
        }

        unset($result['']);

        asort($result);

        return $result;
    }

    public function getContentPluginButtonForm(/** @noinspection PhpUnusedParameterInspection */$data = array(), $loadData = true) {

        // Get the form.
        JForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/forms');
        JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
        $form = JForm::getInstance('com_eventgallery.contentpluginbutton', 'contentpluginbutton', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)){
            return false;
        }

        return $form;
    }


}
