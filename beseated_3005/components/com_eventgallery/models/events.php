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

jimport( 'joomla.application.component.model' );


//jimport( 'joomla.application.component.helper' );

class EventgalleryModelEvents extends JModelLegacy
{

    var $_commentCount = null;
    var $_total = 0;
    var $_entries = null;
    var $_pagination;
    /**
     * @var JCacheControllerCallback $cache
     */
    protected $cache;

    function __construct()
    {
        parent::__construct();
        /**
         * @var JCacheControllerCallback $cache
         */
        $cache = JFactory::getCache('com_eventgallery');
        $this->cache = $cache;

        $limitstart =  JRequest::getInt('limitstart');
        $limit =  JComponentHelper::getParams('com_eventgallery')->get('max_events_per_page', 12);
        $this->setState('limit',$limit);
        $this->setState('com_eventgallery.events.limitstart',$limitstart);
    }

    /**
     * This method gets the entries for this model. It uses caching to prevent getting data multiple times.
     *
     * @param int $limitstart
     * @param int $limit
     * @param string $tags
     * @param string $sortAttribute
     * @param $usergroups
     * @param int $catid the category id to filter the events
     * @param bool $recursive defines if we should get the events for the subcategories too.
     * @param bool $filterByUserGroups defines if we remove user group specific events from list which are invisible for the current user
     * @return array
     */
    function getEntries($limitstart=0, $limit=0, $tags = "", $sortAttribute='ordering', $usergroups, $catid = null, $recursive = false, $filterByUserGroups=true)
    {
        if($limit==0)  {
            $limit = $this->getState('limit');
        } else {
            $this->setState('limit',$limit);
        }

        if($limitstart==0) {
            $limitstart = $this->getState('com_eventgallery.events.limitstart');
        }

        // fix issue with events list where paging was working
        if($limitstart <0 ) {
            $limitstart = 0;
        }

        $entries =  $this->cache->call(
            array($this, 'getEntriesUnCached'), $limitstart, $limit, $tags, $sortAttribute, $usergroups, $catid, $recursive, $filterByUserGroups
        );

        $this->_entries = $entries;
        $this->_total = count($entries);

        return array_slice($this->_entries, $limitstart, $limit);
    }

    /**
     * just get the entries for this model.
     *
     * @param int $limitstart
     * @param int $limit
     * @param string $tags
     * @param string $sortAttribute
     * @param array $usergroups array even if unused we need this for the cache call
     * @param int $catid the category id to filter the events
     * @param bool $recursive defines if we should get the events for the subcategories too.
     * @param bool $filterByUserGroups defines if we remove user group specific events from list which are invisible for the current user
     * @return array
     */
    function getEntriesUnCached(/** @noinspection PhpUnusedParameterInspection */$limitstart=0, $limit=0, $tags = "", $sortAttribute='ordering', $usergroups, $catid, $recursive = false, $filterByUserGroups = true)
    {


        if ($this->_entries == null) {
            $query = $this->_db->getQuery(true)
                ->select('folder.*, count(1) AS '.$this->_db->quoteName('overallCount'))
                ->from($this->_db->quoteName('#__eventgallery_folder') . ' AS folder')
                ->join('', $this->_db->quoteName('#__eventgallery_file') . ' AS file ON folder.folder = file.folder and file.published=1')
                ->where('(file.ismainimageonly IS NULL OR file.ismainimageonly=0)')
                ->where('folder.published=1')                
                ->group('folder.id');

            if (null != $catid && (int)$catid != 0) {
                if ($recursive) {
                    $options = array();
                    $categories = JCategories::getInstance('Eventgallery', $options);

                    /**
                     * @var JCategoryNode $currentCategory
                     * @var JCategoryNode $childCategory
                     */

                    $currentCategory = $categories->get($catid);
                    $childCategories = $currentCategory->getChildren(true);
                    $conditions = array($catid);
                    foreach($childCategories as $childCategory) {
                        array_push($conditions, $childCategory->id);
                    }
                    $query->where('catid in ('. implode(',', $conditions) .') ');
                } else {
                    $query->where('catid='. $this->_db->quote($catid));
                }
            }

            if ($sortAttribute == "date_asc") {
                $query->order('date ASC, ordering DESC');
            } elseif ($sortAttribute == "date_desc") {
                $query->order('date DESC, ordering DESC');
            } elseif ($sortAttribute == "name_asc") {
                $query->order('folder.folder ASC');
            } elseif ($sortAttribute == "name_desc") {
                $query->order('folder.folder DESC');
            } else {
                $query->order('ordering DESC');
            }
            
            $entries = $this->_getList($query);


            $newList = Array();
            /**
             * @var EventgalleryLibraryFactoryFolder $folderFactory
             */
            $folderFactory = EventgalleryLibraryFactoryFolder::getInstance();

            foreach ($entries as $entry)
            {
                $entryObject = $folderFactory->getFolder($entry->folder);
                array_push($newList, $entryObject);

            }

            
            $entries = $newList;
            
            if (strlen($tags)!=0) {
                
                // remove all non matching entries
                // handle space and comma separated lists like "foo bar" or "foo, bar"

                
                $finalWinners = Array();

                /**
                 * @var EventgalleryLibraryFolder $entry
                 */
                foreach($entries as $entry) {
                    if (EventgalleryHelpersTags::checkTags($entry->getFolderTags(), $tags) ) {
                        $finalWinners[] = $entry;
                    }
                }

                $entries = $finalWinners;
            }

            /**
             * @var EventgalleryLibraryFolder $entry
             */
            // filter by user group
            if ($filterByUserGroups) {
                foreach ($entries as $key => $entry) {
                    if (!$entry->isVisible()) {
                        unset($entries[$key]);
                    }
                }
            }

            $this->_entries = $entries;
            $this->_total = count($entries);
        }

        return $this->_entries;
        
    }

    /**
     * returns the paging bar for the current data set.
     *
     * @return JPagination
     */
    function getPagination()
    {

        if (empty($this->_pagination))
        {
            
            $total = $this->_total;

            /**
             * @var integer $limit
             */
            $limit      = $this->getState('limit');

            /**
             * @var integer $limitstart
             */
            $limitstart = $this->getState('com_eventgallery.events.limitstart');
     

            if ($limitstart > $total || JRequest::getVar('limitstart','0')==0) {
                $limitstart=0;             
                $this->setState('com_eventgallery.event.limitstart',$limitstart);
            }
            
            $this->_pagination = new JPagination($total, $limitstart, $limit);
        }
        
        return $this->_pagination;
        
    }

}
