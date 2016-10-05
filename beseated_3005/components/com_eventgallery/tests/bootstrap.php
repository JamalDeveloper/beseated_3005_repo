<?php
/**
 * Created by JetBrains PhpStorm.
 * User: SBluege
 * Date: 27.06.13
 * Time: 10:44
 * To change this template use File | Settings | File Templates.
 */
PHPUnit_Framework_Error_Notice::$enabled = FALSE;

error_reporting(E_ALL);

define('_JEXEC', 1);

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_USER_AGENT']="foobar";

$baseDir = str_replace(DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_eventgallery'.DIRECTORY_SEPARATOR.'tests','',dirname( __FILE__ ) ) ;

if (file_exists($baseDir . '/defines.php'))
{
    include_once $baseDir . '/defines.php';
}

if (!defined('_JDEFINES'))
{
    define('JPATH_BASE', $baseDir);
    require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';

//load tables
JTable::addIncludePath(
    JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_eventgallery'
    . DIRECTORY_SEPARATOR . 'tables'
);

define('JPATH_COMPONENT', $baseDir. DIRECTORY_SEPARATOR.'components'. DIRECTORY_SEPARATOR.'com_eventgallery');

JLoader::discover('EventgalleryPluginsShipping', JPATH_PLUGINS.DIRECTORY_SEPARATOR.'eventgallery_ship', true, true);
JLoader::discover('EventgalleryPluginsSurcharge', JPATH_PLUGINS.DIRECTORY_SEPARATOR.'eventgallery_sur', true, true);
JLoader::discover('EventgalleryPluginsPayment', JPATH_PLUGINS.DIRECTORY_SEPARATOR.'eventgallery_pay', true, true);


JLoader::registerPrefix('Eventgallery', dirname(__FILE__).'/../');
require_once(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controller.php');
require_once(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'tests/FrontendTestcase.php');
JLoader::discover('EventgalleryController', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'controllers', true, true);
