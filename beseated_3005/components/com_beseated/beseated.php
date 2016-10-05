<?php
/**
 * @package     The Beseated.site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

JLoader::registerPrefix('Beseated', __DIR__);

JLoader::registerPrefix('Beseated', JPATH_ADMINISTRATOR . '/components/com_beseated/');

// Require helper file
JLoader::register('BeseatedHelper', dirname(__FILE__) . DS . 'helpers' . DS . 'beseated.php');

JFormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_beseated/models/fields');
JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_beseated/tables');

// Get an instance of the controller prefixed by The Beseated
$controller = JControllerLegacy::getInstance('Beseated');

// Perform the Request task
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
