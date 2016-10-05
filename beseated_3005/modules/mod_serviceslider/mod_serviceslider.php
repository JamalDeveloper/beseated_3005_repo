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
$service_id    = $input->get('service_id');

if(!$service_id)
{
	$service_id     = $input->getstring('unique_code','');
}

$user          = JFactory::getUser();
$elementDetail = BeseatedHelper::getUserElementID($user->id);


if ($view == "yachtownerserviceedit")
{
	$images = ModServiceSliderHelper::getServiceImages($elementDetail->yacht_id,$service_id,$elementType="yacht.service");
}
else if($view == "chauffeurownerserviceedit")
{
	$images = ModServiceSliderHelper::getServiceImages($elementDetail->chauffeur_id,$service_id,$elementType="chauffeur.service");
}

require JModuleHelper::getLayoutPath('mod_serviceslider', $params->get('layout', 'default'));
