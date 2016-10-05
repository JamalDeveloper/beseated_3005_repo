<?php
/**
* @version		$Id: mod_bctedintro.php 10000 2014-01-16 03:35:53Z schro $
* @package		Joomla 3.2.x
* @copyright	Copyright (C) 2005 - 2014 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/// no direct access
defined('_JEXEC') or die('Restricted access');


// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$doc		= JFactory::getDocument();
$modulebase 	= ''.JURI::base().'modules/mod_bctlogout/';

$layout 	= $params->get('layout', 'default');


/* call jquery lib */
JHtml::_('jquery.framework');


//$items = modAppZoomHelper::getItems($params);

require( JModuleHelper::getLayoutPath('mod_bctlogout', $layout) );
