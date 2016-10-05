<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.categories');

/**
 * Build the route for the com_eventgallery component
 *
 * @param	array $query	An array of URL arguments
 * @return	array	The URL arguments to use to assemble the subsequent URL.
 * @since	1.5
 */
function EventgalleryBuildRoute(&$query)
{

    /**
     * @var \Joomla\Registry\Registry $config
     */
	$config 	= JFactory::getConfig();
	$segments = array();
	
	if(isset($query['view']))
	{
	        $segments[] = $query['view'];
	        unset( $query['view'] );
	}
	if(isset($query['folder']))
	{
	        $segments[] = $query['folder'];
	        unset( $query['folder'] );
	};
	if(isset($query['file']))
	{
		/*take care of the appended html. This will not work with file names*/
		if ($config->get('sef_suffix')==1) {
	        $segments[] = $query['file']."/file";
	     
	    } else {
	    	$result = preg_replace("/\.(.{3,4}$)/i", "-\\1", $query['file']);
    		
    		if (isset($query['format'])) {
    			$segments[] = $result;
    		} else {
    			$segments[] = $result."/";
    		}
    	
	    }
	    unset( $query['file'] );
	    
	};

    if(isset($query['catid']))
    {
        $segments[] = $query['catid'];
        unset( $query['catid'] );
    };

	
	return $segments;
	

	/*

	Due to some issues with plugin we should not remove the url parameter format to prevent issues

	if (isset($query['format'])) {
        if ($config->get('sef_suffix')!=1) {
            $segments[] = 	$query['format'];
            unset($query['format']);
        }
	}


	
	return $segments;

	*/
}


/**
 * Parse the segments of a URL.
 *
 * @param $segments array The segments of the URL to parse.
 * @return array
 * @throws Exception
 */
function EventgalleryParseRoute($segments)
{
	$vars = array();

	$vars['view']	= $segments[0];

    if ($vars['view'] == 'categories' && isset($segments[1])) {
        $vars['catid']	= $segments[1];
    }

    if ($vars['view'] == 'event' || $vars['view']=='password') {
		$vars['folder']	= str_replace(":","-",str_replace("/","",$segments[1]));
	}

	if ($vars['view'] == 'singleimage' || $vars['view'] == 'download') {
		if (count($segments)>=3) {
	        $vars['folder']	= str_replace(":","-",str_replace("/","",$segments[1]));
			$result = str_replace(":","-",str_replace("/","",$segments[2]));
			$result = preg_replace("/-(.{3,4}$)/i", ".\\1", $result);
			$vars['file']	= $result;
		}
	}

	if ($segments[count($segments)-1]=='raw') {
		$vars['format'] = 'raw';
	}

    $viewExceptions = Array('download','resizeimage');


    if (!in_array($vars['view'], $viewExceptions) &&
        !file_exists(__DIR__.'/views/'.$vars['view'])) {
        throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_VIEW_NOT_FOUND', $vars['view'], "", ""), 404);
    }

	return $vars;

}
