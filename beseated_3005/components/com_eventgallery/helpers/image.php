<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
define('_JEXEC', 1);

// useless, just to satisfy the jedChecker
defined('_JEXEC') or die;


if (!defined('_JDEFINES')) {
    // remove the first 3 folders because
    // we're in a subfolder and have not
    // native Joomla help. Doing this will
    // enable this comonent to run in a subdirectory
    // like http://foo.bar/foobar
    $basefolders = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
    $basefolders = array_splice($basefolders, 0, count($basefolders) - 3);
    define('JPATH_BASE', implode(DIRECTORY_SEPARATOR, $basefolders));
    require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';
require_once JPATH_BASE . '/components/com_eventgallery/config.php';


$file = JRequest::getString('file');
$folder = JRequest::getString('folder');
$width = JRequest::getInt('width', -1);
$mode = JRequest::getString('mode', 'nocrop');


$file = STR_REPLACE("\.\.", "", $file);
$folder = STR_REPLACE("\.\.", "", $folder);
$width = STR_REPLACE("\.\.", "", $width);
$mode = STR_REPLACE("\.\.", "", $mode);

$file = STR_REPLACE("/", "", $file);
$folder = STR_REPLACE("/", "", $folder);
$width = STR_REPLACE("/", "", $width);
$mode = STR_REPLACE("/", "", $mode);


$file = STR_REPLACE("\\", "", $file);
$folder = STR_REPLACE("\\", "", $folder);
$width = STR_REPLACE("\\", "", $width);
$mode = STR_REPLACE("\\", "", $mode);


//full means max size.
if (strcmp('full', $mode) == 0) {
    $mode = 'nocrop';
    $width = 5000;
}

require_once JPATH_BASE . '/components/com_eventgallery/helpers/sizeset.php';

$sizeSet = new EventgalleryHelpersSizeset();
$saveAsSize = $sizeSet->getMatchingSize($width);


$basedir = COM_EVENTGALLERY_IMAGE_FOLDER_PATH;
$sourcedir = $basedir . $folder;
$cachebasedir = JPATH_CACHE . DIRECTORY_SEPARATOR . 'com_eventgallery_images' . DIRECTORY_SEPARATOR;
$cachedir = $cachebasedir . $folder;
$cachedir_thumbs = $cachebasedir . $folder;

$image_file = $sourcedir . DIRECTORY_SEPARATOR . $file;
$image_thumb_file = $cachedir_thumbs . DIRECTORY_SEPARATOR . $mode . $saveAsSize . $file;
//$last_modified = gmdate('D, d M Y H:i:s T', filemtime ($image_file));
$last_modified = gmdate('D, d M Y H:i:s T', mktime(0, 0, 0, 1, 1, 1998));


$debug = false;
if ($debug || !file_exists($image_thumb_file)) {

    // Mark afterLoad in the profiler.
    JDEBUG ? $_PROFILER->mark('afterLoad') : NULL;

    // Instantiate the application.
    $app = JFactory::getApplication('site');

    // Initialise the application.
    $app->initialise();

    // Mark afterIntialise in the profiler.
    JDEBUG ? $_PROFILER->mark('afterInitialise') : NULL;

    // Route the application.
    //$app->route();

    // Mark afterRoute in the profiler.
    JDEBUG ? $_PROFILER->mark('afterRoute') : NULL;

    // Dispatch the application.
    $app->dispatch();

    // Mark afterDispatch in the profiler.
    JDEBUG ? $_PROFILER->mark('afterDispatch') : NULL;

    // Render the application.
    $app->render();

    // Mark afterRender in the profiler.
    JDEBUG ? $_PROFILER->mark('afterRender') : NULL;

    // Return the response.
    /** @noinspection PhpToStringImplementationInspection */
    echo $app;
}

$mime = ($mime = getimagesize($image_thumb_file)) ? $mime['mime'] : $mime;
$size = filesize($image_thumb_file);
$fp   = fopen($image_thumb_file, "rb");
if (!($mime && $size && $fp)) {
    // Error.
    return;
}

if (!$debug) {
    header("Content-Type: " . $mime);
    header("Content-Length: " . $size);
    header("Last-Modified: $last_modified");
}

fpassthru($fp);
die();
