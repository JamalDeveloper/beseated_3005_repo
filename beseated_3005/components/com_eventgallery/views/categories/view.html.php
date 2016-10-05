<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;


jimport('joomla.application.component.view');
jimport('joomla.application.categories');


class EventgalleryViewCategories extends EventgalleryLibraryCommonView
{


    /**
     * @var \Joomla\Registry\Registry
     */
    public $params;
    protected $entries;
    protected $fileCount;
    protected $folderCount;
    protected $eventModel;
    protected $pageNav;
    protected $entriesCount;
    protected $currentItemid;
    protected $catid;
    /**
     * @var JCategoryNode
     */
    protected $category;

    protected $folder;


    /**
     * @var JDocument
     */
    public $document;


    function display($tpl = NULL)
    {
        /**
         * @var JSite $app
         */
        $app = JFactory::getApplication();

        $this->params = $app->getParams();

        /* Default Page fallback*/
        $active = $app->getMenu()->getActive();
        if (NULL == $active) {
            $this->params->merge($app->getMenu()->getDefault()->params);
            $active = $app->getMenu()->getDefault();
        }

        $this->currentItemid = $active->id;


        $this->catid = JRequest::getInt('catid', 0);
        if ($this->catid == 0) {
            $this->catid = 'root';
        }


        $options = array();
        $options['countItems'] = $this->params->get('show_items_per_category_count', 0);
        /**
         * @var JCategories $categories
         */
        $categories = JCategories::getInstance('Eventgallery', $options);

        /**
         * @var JCategoryNode $root
         */

        if (null != $this->catid) {
            $this->category = $categories->get($this->catid);
        }

        if ($this->category==null || $this->category->published!=1) {
            return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
        }

        $entriesPerPage = $this->params->get('max_events_per_page', 12);

        $model = $this->getModel('categories');
        $eventModel = JModelLegacy::getInstance('Event', 'EventgalleryModel');

        $user = JFactory::getUser();
        $usergroups = JUserHelper::getUserGroups($user->id);
        $filterEventsByUserGroup = $this->params->get('show_usergroup_protected_events_in_list', 0) == 0;
        $entries = $model->getEntries(JRequest::getVar('start', 0), $entriesPerPage, $this->params->get('tags'), $this->params->get('sort_events_by'), $usergroups, $this->catid, false, $filterEventsByUserGroup);

        $this->pageNav = $model->getPagination();

        $this->entries = $entries;
        $this->eventModel = $eventModel;
        
        $this->_prepareDocument();

        /**
         * @var JPathway $pathway
         */
        $pathway = $app->getPathway();
        $rootCategoryId = 0;
        if ( isset($active->query['catid']) ) {
	        $rootCategoryId = $active->query['catid'];
        } 
        EventgalleryHelpersCategories::addCategoryPathToPathway($pathway, $rootCategoryId, JRequest::getInt('catid', 0), $this->currentItemid);

        return parent::display($tpl);
    }

    /**
     * Prepares the document
     */
    protected function _prepareDocument()
    {
        $app    = JFactory::getApplication();
        $menus  = $app->getMenu();
        $title  = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();
        if($menu)
        {
            $this->params->get('page_heading', $this->params->get('page_title', $menu->title));
        }

        $title = $this->params->get('page_title', '');

        // checks for empty title or sets the category title if 
        // the current menu item has a different catid than the current catid is
        if (  empty($title)  ||
             (isset($menu->query['catid']) && $this->catid != $menu->query['catid'] )
           ) {
            
            $title = EventgalleryHelpersCategories::getCategoryTitle($this->category);
        }



        if (empty($title)) {
            $title = $app->getCfg('sitename');
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        }
        elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }
        $this->document->setTitle($title);

        if (!empty($this->category->metadesc) ) 
        {
            $this->document->setDescription($this->category->metadesc);
        } 
        else if ($this->params->get('menu-meta_description'))
        {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if (!empty($this->category->metadesc) ) 
        {
            $this->document->setMetadata('keywords', $this->category->metakey);
        } 
        else if ($this->params->get('menu-meta_keywords'))
        {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        $robots = $this->category->getMetadata()->get('robots'); 
        if (!empty($robots) ) 
        {
            $this->document->setMetadata('robots', $robots);
        } 
        else if ($this->params->get('robots'))
        {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }

    }

}
