<?php

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class EventgalleryHelpersBackendmedialoader
{

    static $loaded = false;

    public static function load()
    {

        if (self::$loaded) {
            return;
        }

        self::$loaded = true;

        include_once JPATH_ROOT . '/administrator/components/com_eventgallery/version.php';

        $document = JFactory::getDocument();
       
        JHtml::_('behavior.formvalidation');

        
        $CSSs = Array();
        $JSs = Array();

        if (version_compare(JVERSION, '3.0', 'gt'))
        {
            JHtml::_('jquery.framework');
        }
        else
        {
            $JSs[] = 'common/js/jquery/jquery.min.js';
            $JSs[] = 'common/js/jquery/jquery-migrate-1.2.1.min.js';
        }
        $JSs[] = 'common/js/jquery/namespace.js';
        $JSs[] = 'backend/js/eventgallery.js';


        $CSSs[] = 'backend/css/eventgallery.css';
       

        $joomlaVersion =  new JVersion();
        if (!$joomlaVersion->isCompatible('3.0')) {
            $CSSs[] =  'backend/css/legacy.css';
        } 

        $JSs = array_merge($JSs, Array(                
        ));

        foreach($CSSs as $css) {
            $script = JURI::root() . 'media/com_eventgallery/'.$css.'?v=' . EVENTGALLERY_VERSION;
            $document->addStyleSheet($script);
        }

        foreach($JSs as $js) {
            $script = JURI::root() . 'media/com_eventgallery/'.$js.'?v=' . EVENTGALLERY_VERSION;
            $document->addScript($script);
        }


    }

}

	
	
