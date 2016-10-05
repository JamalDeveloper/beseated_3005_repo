<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



require_once JPATH_COMPONENT_SITE.'/config.php';

// Include the component versioning
include_once JPATH_COMPONENT_ADMINISTRATOR.'/version.php';

//load tables
JTable::addIncludePath(
    JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'tables'
);

JLoader::registerPrefix('Eventgallery', JPATH_COMPONENT_SITE);
JLoader::registerPrefix('Eventgallery', JPATH_COMPONENT);

JLoader::discover('EventgalleryPluginsShipping', JPATH_PLUGINS.DIRECTORY_SEPARATOR.'eventgallery_ship', true, true);
JLoader::discover('EventgalleryPluginsSurcharge', JPATH_PLUGINS.DIRECTORY_SEPARATOR.'eventgallery_sur', true, true);
JLoader::discover('EventgalleryPluginsPayment', JPATH_PLUGINS.DIRECTORY_SEPARATOR.'eventgallery_pay', true, true);

$view = JFactory::getApplication()->input->get('view');
$task = JFactory::getApplication()->input->get('task');

//avoid rendering those messages during the sync.
if ($task != 'sync.process') {
// check for the right Joomla Version
    if ((version_compare(JVERSION, '2.5.11', 'lt') || (version_compare(JVERSION, '3.0.0', 'gt') && version_compare(JVERSION, '3.2.0', 'lt')))) {
        ?>
        <div class="alert alert-error">
        <?php echo JText::_('COM_EVENTGALLERY_ERR_OLDJOOMLA'); ?>
        </div><?php
    }

//Check foe the right PHP version
    if (version_compare(PHP_VERSION, '5.3.0') < 0) {
        ?>
        <div class="alert alert-error">
        <?php echo JText::_('COM_EVENTGALLERY_ERR_OLDPHP'); ?>
        </div><?php
    }

    if (EVENTGALLERY_EXTENDED && (version_compare(JVERSION, '2.5.19', 'lt') || (version_compare(JVERSION, '3.0.0', 'gt') && version_compare(JVERSION, '3.2.1', 'lt')))):?>
        <div class="alert alert-error">
            <?php echo JText::_('COM_EVENTGALLERY_ERR_OLDJOOMLANOUPDATES'); ?>
        </div>
    <?php elseif (EVENTGALLERY_EXTENDED && version_compare(JVERSION, '2.5.999', 'lt') && !EventgalleryLibraryHelperCheckupdateplugin::isUpdatePluginEnabled()): ?>
        <div class="alert alert-warning">
            <?php echo JText::_('COM_EVENTGALLERY_ERR_NOPLUGINNOUPDATES'); ?>
        </div>
    <?php endif;

}

if (!JFactory::getUser()->authorise('core.manage', 'com_eventgallery'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

JLoader::register('JHtmlEventgalleryBatch', JPATH_ADMINISTRATOR . '/components/com_eventgallery/helpers/html/eventgallerybatch.php');

// Load necessary media files 
EventgalleryHelpersBackendmedialoader::load();

$version =  new JVersion();
if (!$version->isCompatible('3.0')) {
    require_once(JPATH_COMPONENT.'/helpers/legacy_layout.php');
    require_once(JPATH_COMPONENT.'/helpers/legacy_base.php');
    require_once(JPATH_COMPONENT.'/helpers/legacy_file.php');
    require_once(JPATH_COMPONENT.'/helpers/legacy_sidebar.php');
}

//JObserverMapper::addObserverClassToClass('JTableObserverTags', 'TableEvent', array('typeAlias' => 'com_eventgallery.event'));

// Execute the task.
$controller	= JControllerLegacy::getInstance('Eventgallery');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

