<?php
/**
* @package 		Facebook Connect Extension (joomla 3.x)
* @copyright	Copyright (C) Computer - http://www.sanwebe.com. All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @author		Saran Chamling
* @download URL	http://www.sanwebe.com
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'helper.php');

$params->def('greeting', 1);
require_once (JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_fbconnct'.DIRECTORY_SEPARATOR.'controller.php');
$type 		= modFBLoginHelper::getType();
$user 		=  JFactory::getUser();
require(JModuleHelper::getLayoutPath('mod_fbconnct'));