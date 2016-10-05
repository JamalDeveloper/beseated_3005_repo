<?php
/**
 * Form component for Joomla
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
include_once JPATH_ADMINISTRATOR . '/components/com_visforms/include.php';

//JPATH_ADMINISTRATOR

// Create the controller
$controller = JControllerLegacy::getInstance('Visforms');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task', 'display')); // function to execute; if not specified in request it's display();

// Redirect if set by the controller
$controller->redirect();

?>