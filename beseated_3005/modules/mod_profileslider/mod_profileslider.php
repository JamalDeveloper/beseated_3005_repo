<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the helper functions only once
require_once __DIR__ . '/helper.php';

$input         = JFactory::getApplication()->input;
$view          = $input->get('view');
$user          = JFactory::getUser();
$elementDetail = BeseatedHelper::getUserElementID($user->id);

if ($view == "profile")
{
	$images = ModProfileSliderHelper::getProfileImages($elementDetail->venue_id, $elementType="Venue");
	$elementType = "profile";
}
else if($view == "chauffeurprofile")
{
	$images = ModProfileSliderHelper::getProfileImages($elementDetail->chauffeur_id, $elementType="Chauffeur");
	$elementType = "Chauffeur";
}
else if($view == "protectionprofile")
{
	$images = ModProfileSliderHelper::getProfileImages($elementDetail->protection_id, $elementType="Protection");
	$elementType = "Protection";
}
else if($view == "yachtprofile")
{
	$images = ModProfileSliderHelper::getProfileImages($elementDetail->yacht_id, $elementType="Yacht");
	$elementType = "Yacht";
}

require JModuleHelper::getLayoutPath('mod_profileslider', $params->get('layout', 'default'));
