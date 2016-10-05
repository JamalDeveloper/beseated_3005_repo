<?php
/**
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.categories');

/**
 * Build the route for the com_content component
 *
 * @param	array	An array of URL arguments
 * @return	array	The URL arguments to use to assemble the subsequent URL.
 * @since	1.5
 */
function VisformsBuildRoute(&$query)
{
	$segments	= array();
    
    //No routing for captchas
    if (isset($query['sid']))
    {
        return $segments;
    }
    //No routing for internal tasks (captcha, send, edit)
    if (isset($query['task'])) 
	{
        return $segments;
    }
    
    

	// get a menu item based on Itemid or currently active
	$app		= JFactory::getApplication();
	$menu		= $app->getMenu();

	// we need a menu item.  Either the one specified in the query, or the current active one if none specified
	if (empty($query['Itemid'])) 
    {
		$menuItem = $menu->getActive();
		$menuItemGiven = false;
        if (!$menuItem)
        {
            $lang = JFactory::getLanguage();
            $menuItem = $menu->getDefault($lang->getTag());
        }
	}
	else 
    {
		$menuItem = $menu->getItem($query['Itemid']);
		$menuItemGiven = true;
    }
    //only route links which comes from a menuItem of type component (no external urls)
    if (($menuItem instanceof stdClass) && ((isset($menuItem->type)) && ($menuItem->type != 'component')))
    {
        return $segments;
    }
	// if we are dealing with form or formdata which are linked to a menu that should be true!
    //view and id values are the same in $menuItem->query and in $query and we can unset the $query values
	if (($menuItem instanceof stdClass) && isset($query['view']) && isset($menuItem->query['view']) && $menuItem->query['view'] == $query['view'] && isset($menuItem->query['id']) && isset($query['id']) && $menuItem->query['id'] == intval($query['id'])) 
	{
		unset($query['view']);
        unset($query['id']);
	}
    
    if(isset($query['view']) && (($query['view'] == "message") || ($query['view'] == "visformsdata")))
    {
        unset($query['view']);
    }
	
	// if the layout is specified and it is the same as the layout in the menu item, we
	// unset it so it doesn't go into the query string.
	// otherwise we put it into the first segment
	if (isset($query['layout'])) 
	{
		if ($menuItemGiven && isset($menuItem->query['layout'])) 
		{
			if ($query['layout'] == $menuItem->query['layout']) 
			{

				unset($query['layout']);
			}
			else
			{
				$segments[] = $query['layout'];
				unset($query['layout']);
			}
		}
		else 
		{
			if ($query['layout'] == 'default') 
			{
				unset($query['layout']);
			}
			else
			{
				$segments[] = $query['layout'];
				unset($query['layout']);
			}
		}
		
	}
	
	// if we deal with a data detail view, there is a additional parameter cid which we put into $segments (on the last position)
	if (isset($query['cid']))
	{
		$segments[] = $query['cid'];
		unset($query['cid']);
	}
    //if we deal with a message there is no menu item and we still have the id
    if (isset($query['id']))
    {
        //use a slug (id:form-name) as segment
        if (strpos($query['id'], ':') === false)
        {
            $db = JFactory::getDbo();
            $dbQuery = $db->getQuery(true)
                ->select('name')
                ->from('#__visforms')
                ->where('id=' . (int) $query['id']);
            $db->setQuery($dbQuery);
            $alias = $db->loadResult();
            $query['id'] = $query['id'] . ':' . $alias;
        }
        $segments[] = $query['id'];
		unset($query['id']);
	}
   
	return $segments;
}



/**
 * Parse the segments of a URL.
 *
 * @param	array	The segments of the URL to parse.
 *
 * @return	array	The URL attributes to be used by the application.
 * @since	1.5
 */
function VisformsParseRoute($segments)
{
	$vars = array();

	//Get the active menu item.
	$app	= JFactory::getApplication();
	$menu	= $app->getMenu();
	$item	= $menu->getActive();
    if(!$item)
    {
        //We deal with a form that is set as home menu
        $lang = JFactory::getLanguage();
        $item = $menu->getDefault($lang->getTag());
    }

	// Count route segments
	$count = count($segments);
   
    $db = JFactory::getDbo();
	
	if (isset($item->query['id']))
	{
		$vars['id'] = $item->query['id'];
	}
	
	if (isset($item->query['view']))
	{
		$vars['view'] = $item->query['view'];
	}
	
	if (isset($item->query['layout']))
	{
		$vars['layout'] = $item->query['layout'];
	}


	// if there is only one segment, then it the task or a layout
	if ($count >= 1) 
	{	
        $vars['layout'] = $segments[0];
        unset($segments[0]);
	}
	
	if ($count >= 2) 
	{
        while ($segments)
        {
            $segment = array_shift($segments);
            //check if segment is a form id
            if (strpos($segment, ':') !== false)
            {
                $query = $db->getQuery(true);						
                $query->select('CASE WHEN CHAR_LENGTH (name) THEN CONCAT_WS(":", id, name) ELSE id END as slug');
                $query->from('#__visforms');
                $db->setQuery($query);
                $slugs = $db->loadColumn();
                
                if (!empty($slugs))
                {    
                    foreach ($slugs as $slug)
                    {
                        if ($segment === $slug)
                        {
                            $form = explode(':', $segment);
                            $vars['id'] = $form[0];
                            if ($count == 2)
                            {
                                $vars['view'] = 'message';
                            }
                            continue;
                        }
                    }
                }
                //we should not arrive here
            }
            else
            {
                $vars['cid'] = $segment;
                if ($count >= 3)
                {
                    $vars['view'] = 'visformsdata';
                }
            }
        }
	}

	return $vars;
}
