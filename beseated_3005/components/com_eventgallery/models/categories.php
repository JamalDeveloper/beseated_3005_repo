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

require_once(__DIR__.'/events.php');

class  EventgalleryModelCategories extends EventgalleryModelEvents
{

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
        /**
         * @var \Joomla\Registry\Registry $params
         */
        $params = JComponentHelper::getParams('com_eventgallery');
        $recursive = $params->get('show_items_per_category_recursive', false);

        return parent::getEntries($limitstart, $limit, $tags, $sortAttribute, $usergroups, $catid, $recursive, $filterByUserGroups);
    }



}
