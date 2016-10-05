<?php

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
jimport('joomla.error.log');
require_once JPATH_ROOT.'/components/com_eventgallery/config.php';
require_once JPATH_ROOT.'/components/com_eventgallery/library/common/logger.php';

class EventgalleryHelpersImageHelper
{


    /**
     * returns the name of the file for this url.
     * @param $url
     * @return string
     */
    public static function getPicasawebResult($url)
    {
        $params = JComponentHelper::getParams('com_eventgallery');
        $cachelifetime = $params->get('cache_picasa_lifetime', self::$cache_life);

        JLog::addLogger(
            array(
                'text_file' => 'com_eventgallery.log.php',
                'logger' => 'Eventgalleryformattedtext'
            ),
            JLog::ALL,
            'com_eventgallery'
        );
        //JLog::add('processing url '.$url, JLog::INFO, 'com_eventgallery');


        self::initCacheDirs();

        $cachefilename = self::$cachebasedir . md5($url) . '.xml';

        if (file_exists($cachefilename) && (time() - filemtime($cachefilename) <= $cachelifetime)) {
            // no need to do anything since the cache is still valid

        } else {

            //JLog::add('have writen new cache file for '.$url, JLog::INFO, 'com_eventgallery');

            $xml = self::url_get_contents($url);
            if ($xml===false) {
                JLog::add('unable to connect to remote host. Make sure allow_url_fopen=On and the server has access to the internet. url: '.$url, JLog::INFO, 'com_eventgallery');                
            }
            $xml = str_replace("xmlns='http://www.w3.org/2005/Atom'", '', $xml);
            #echo "reloading content from $url <br>";
            if (strlen($xml) > 0) {
                $fh = fopen($cachefilename, 'w') or die("can't open file $cachefilename");
                fwrite($fh, $xml);
                fclose($fh);
            }

        }

        $xml = NULL;

        return $cachefilename;

    }

    public static $cachebasedir;
    public static $cache_life = '86400'; //caching time, in seconds


    public static  function initCacheDirs() {

        if (!is_dir(JPATH_CACHE)) {
            mkdir(JPATH_CACHE);
        }

        self::$cachebasedir = COM_EVENTGALLERY_PICASA_CACHE_PATH;

        if (!is_dir(self::$cachebasedir)) {
            mkdir(self::$cachebasedir);
        }
    }


    /**
     * The following values are valid for the thumbsize and imgmax query parameters and are embeddable on a webpage. These images
     * are available as both cropped(c) and uncropped(u) sizes by appending c or u to the size. As an example, to retrieve a 72 pixel
     * image that is cropped, you would specify 72c, while to retrieve the uncropped image, you would specify 72u for the thumbsize or
     * imgmax query parameter values.
     *
     * 32, 48, 64, 72, 104, 144, 150, 160
     *
     * The following values are valid for the thumbsize and imgmax query parameters and are embeddable on a webpage. These images are
     * available as only uncropped(u) sizes by appending u to the size or just passing the size value without appending anything.
     *
     * 94, 110, 128, 200, 220, 288, 320, 400, 512, 576, 640, 720, 800, 912, 1024, 1152, 1280, 1440, 1600
     *
     * @param string $userName
     * @param string $albumNameOrId
     * @param string $picasaKey
     * @param int    $imagesize
     *
     * @return object
     */
    public static function picasaweb_ListAlbum($userName, $albumNameOrId, $picasaKey = NULL, $imagesize = 1280)
    {
        self::initCacheDirs();

        set_time_limit(30);
        $params = JComponentHelper::getParams('com_eventgallery');
        $cachelifetime = $params->get('cache_picasa_lifetime', self::$cache_life);

        $filename = md5($userName.$albumNameOrId.$picasaKey).'.obj';
        $serOBjectPath = self::$cachebasedir . $filename;

        if (file_exists($serOBjectPath) && (time() - filemtime($serOBjectPath) <= $cachelifetime)) {
            $c = file_get_contents($serOBjectPath);
            return unserialize($c);
        }


        #echo "Initial:". memory_get_usage() . "<br>";

        #$thumbsizeArray = array(32,48,64,72,104,144,150,160,'32u','48u','64u','72u','104u','144u','150u','160u',94,110,128,200,220,288,320,400,512,576,640,720,800,912,1024,1152,1280,1440);
        $thumbsizeArray = array(104, '104u');
        $thumbsize = implode(',', $thumbsizeArray);


        $authkeyParam = (strlen($picasaKey) > 0) ? "authkey=$picasaKey&" : "";
        $prettyprint = "false";

        if (is_numeric($albumNameOrId)) {
            $url = 'http://picasaweb.google.com/data/feed/api/user/' . urlencode($userName) . '/albumid/' . urlencode(
                    $albumNameOrId
                ) . "?" . $authkeyParam . "thumbsize=$thumbsize&imgmax=$imagesize&prettyprint=$prettyprint";
        } else {
            $url = 'http://picasaweb.google.com/data/feed/api/user/' . urlencode($userName) . '/album/' . urlencode(
                    $albumNameOrId
                ) . "?" . $authkeyParam . "thumbsize=$thumbsize&imgmax=$imagesize&prettyprint=$prettyprint";
        }

        $album = Array();
        $photos = Array();


        $xmlFile = EventgalleryHelpersImageHelper::getPicasawebResult($url);

        $dom = new DOMDocument;
        if (!@$dom->load($xmlFile)) {
            JLog::add('unable to load xml content from file. File Name: '.$xmlFile, JLog::INFO, 'com_eventgallery');                            
            $album['photos'] = $photos;
            $album['overallCount'] = 0;
            return (object)$album;
        }

        #echo "After DOM loaded:". memory_get_usage() . "<br>";

        $xpath = new domxpath($dom);
        $nodes = $xpath->query('//entry');

       

        foreach ($nodes as $node) {

            $photo = Array();

            /**
             * @var DOMNodeList $thumbnailNodes
             */
            $thumbnailNodes = $xpath->query('.//media:thumbnail', $node);

            $thumbnails = Array();
            $thumbnailsCrop = Array();

            /**
             * @var DOMElement $thumbnailNode
             */
            foreach ($thumbnailNodes as $thumbnailNode) {
                //if url contains a thumbsize like /s123-c/ it's a crop image
                if (preg_match("/\/s[0-9]+-c\//",$thumbnailNode->getAttribute('url'))==1 ) {
                    $thumbnailsCrop[$thumbnailNode->getAttribute('width')] = $thumbnailNode->getAttribute('url');
                } else {
                    $thumbnails[$thumbnailNode->getAttribute('width')] = $thumbnailNode->getAttribute('url');
                }
            }

            /**
             * @var DOMElement $image
             */
            $image = $xpath->query('.//media:content', $node)->item(0);


            $photo['image'] = $image->getAttribute('url');
            $photo['originalImage'] = str_replace('/s'.$imagesize.'/', "/d/", $photo['image']);
            $photo['width'] = $image->getAttribute('width');
            $photo['height'] = $image->getAttribute('height');

            $photo['thumbs'] = $thumbnails;

            //$photo['thumbsCrop'] = $thumbnails;
            // this works because picasa can deliver every image as a crop
            $photo['thumbsCrop'] = EventgalleryHelpersImageHelper::createCropThumbArray(
                $photo['image'], $thumbsizeArray, "s" . $photo['width']
            );

            //print_r($photo['thumbs']);


            $photo['caption'] = $xpath->query('.//summary', $node)->item(0)->textContent;
            $photo['title'] = "";
            $photo['folder'] = $userName . '@' . $albumNameOrId;
            $photo['file'] = $xpath->query('.//gphoto:id', $node)->item(0)->textContent;
            $photo['commentCount'] = $xpath->query('.//gphoto:commentCount', $node)->item(0)->textContent;


            $exif = Array();

            $items = $xpath->query('.//exif:tags/exif:fstop', $node);
            $items->length > 0 ? $exif['fstop'] = $items->item(0)->textContent : $exif['fstop'] = '';
            $items = $xpath->query('.//exif:tags/exif:focallength', $node);
            $items->length > 0 ? $exif['focallength'] = $items->item(0)->textContent : $exif['focallength'] = '';
            $items = $xpath->query('.//exif:tags/exif:model', $node);
            $items->length > 0 ? $exif['model'] = $items->item(0)->textContent : $exif['model'] = '';
            $items = $xpath->query('.//exif:tags/exif:iso', $node);
            $items->length > 0 ? $exif['iso'] = $items->item(0)->textContent : $exif['iso'] = '';

            $photo['exif'] = (object)$exif;
            $photo['allowcomments'] = 0;
            $photo['published'] = 1;


            $photos[] = $photo;
            unset($photo);
        }

        $album['folder'] = $userName . '@' . $albumNameOrId;
        $album['file'] = 'dummy';
        $album['photos'] = $photos;
        $album['overallCount'] = $xpath->query('//feed/openSearch:totalResults')->item(0)->textContent;
        $album['date'] = strftime(
            "%Y-%m-%d %H:%M:%S", $xpath->query('//feed/gphoto:timestamp')->item(0)->textContent / 1000
        );
        $album['text'] = $xpath->query('//feed/subtitle')->item(0)->textContent;
        $album['description'] = $xpath->query('//title')->item(0)->textContent;
        $album['thumbs'] = EventgalleryHelpersImageHelper::createCropThumbArray(
            $xpath->query('//icon')->item(0)->textContent, $thumbsizeArray
        );
        $album['width'] = 1440;
        $album['height'] = 1440;
        $album['thumbsCrop'] = $album['thumbs'];

        $album['image'] = $xpath->query('//icon')->item(0)->textContent;
        $album['originalImage'] = preg_replace('/\/s\d+-c\//', "/s1440-c/", $album['image']);

        $dom = NULL;
        $xpath = NULL;

        #echo "Finally:". memory_get_usage() . "<br>";
        #echo memory_get_peak_usage() . "<br>";

        #echo "<pre>"; 		print_r($album);		echo "</pre>";

        $album = (object)$album;

        $c = serialize($album);
        file_put_contents($serOBjectPath, $c);

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->delete('#__eventgallery_file')
            ->where('folder='.$db->quote($userName . '@' . $albumNameOrId));
        $db->setQuery($query);
        $db->execute();
        
        if (count($photos)>0) {
            $query = $db->getQuery(true);

            $query->insert($db->quoteName('#__eventgallery_file'))
                ->columns(
                    'folder,file,published,'
                    .'userid,created,modified,ordering'
                    )
                ->values(implode(',',array(
                    $db->quote($userName . '@' . $albumNameOrId),
                    $db->quote('dummy'),
                    '1',
                    '0',
                    'now()',
                    'now()',
                    0
                    )));
            $db->setQuery($query);
            $db->execute();
        }

        return $album;
    }


    public static function createCropThumbArray($thumbUrl, $thumbsizeArray)
    {

        $thumbs = Array();

        foreach ($thumbsizeArray as $thumbsize) {
            if (strpos($thumbsize, 'u') == 0) {
                $thumb = preg_replace("/\/s(\d+)-c/", "/s$thumbsize-c", $thumbUrl);
                $thumb = preg_replace("/\/s(\d+)\//", "/s$thumbsize-c/", $thumb);

                $thumbs[$thumbsize] = $thumb;
            }
        }
        #echo "<pre>";	echo $thumbUrl."\n\n";	print_r($thumbs);	echo "</pre>";
        return $thumbs;
    }


    public static function picasaweb_ListAlbums($userName, $key, $thumbsize = 666)
    {

        $url = 'http://picasaweb.google.com/data/feed/api/user/' . urlencode($userName) . '?authKey=' . $key
            . '&kind=album';
        $xml = self::url_get_contents($url);
        $xml = str_replace("xmlns='http://www.w3.org/2005/Atom'", '', $xml);

        $albums = Array();

        $dom = new domdocument;
        $dom->loadXml($xml);

        $xpath = new domxpath($dom);
        $nodes = $xpath->query('//entry');
        foreach ($nodes as $node) {

            $imageUrl = $xpath->query('.//media:thumbnail/@url', $node)->item(0)->textContent;
            $imageUrl = str_replace('?imgmax=160', '?imgmax=' . $thumbsize, $imageUrl);

            $albumId = $xpath->query('.//gphoto:id', $node)->item(0)->textContent;
            $albumName = $xpath->query('.//gphoto:name', $node)->item(0)->textContent;
            $albumTitle = $xpath->query('.//media:title', $node)->item(0)->textContent;
            $imageCount = $xpath->query('.//gphoto:numphotos', $node)->item(0)->textContent;
            $published = $xpath->query('.//published', $node)->item(0)->textContent;

            $album = Array();
            $album['folder'] = "$userName@$albumId";
            $album['name'] = $albumName;
            $album['description'] = $albumTitle;
            $album['image'] = $imageUrl;
            $album['date'] = $published;
            $album['overallCount'] = $imageCount;
            $album['url'] = 'http://picasaweb.google.com/' . urlencode($userName) . '/' . urlencode($album['name']);

            $albums[] = (object)$album;

            unset($album);
        }

        return $albums;
    }

    /**
     * tries to fetch the content from a given url. It uses CURL to get the content
     * in the first place and falls back to return file_get_contents($url) if CURL is unavailable.
     *
     * @param $url
     * @return string the content body provided by the given URL
     */
    public static function url_get_contents ($url) {
        if (function_exists('curl_init')) {
        	//JLog::add('CURL Init to ' . $url, JLog::INFO, 'com_eventgallery');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, "{$_SERVER['SERVER_NAME']}");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $output = curl_exec($ch);
            
            if(curl_exec($ch) === false)
			{
			    JLog::add('CURL-Error: ' . curl_error($ch), JLog::INFO, 'com_eventgallery');
			}
            
            curl_close($ch);
            return $output;
        }

		//JLog::add('CURL not installed. Will fall back to file_get_contents.', JLog::INFO, 'com_eventgallery');
        return @file_get_contents($url);
    }

}