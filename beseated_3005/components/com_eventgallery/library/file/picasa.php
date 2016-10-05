<?php

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


class EventgalleryLibraryFilePicasa extends EventgalleryLibraryFile
{

    public $_blank_script_path = 'media/com_eventgallery/frontend/images/blank.gif';

    public $image;
    public $thumbs;

    /**
     * @var EventgalleryLibraryFolderPicasa
     */
    protected $_folder;

    /**
     * creates the lineitem object. The foldername can either be a string or a file data object
     *
     * @param object $object
     */
    function __construct($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException("Can't create File Object without a valid data object.");
        }
        $this->_foldername = $object->folder;

        if (isset($object->file)) {
            $this->_filename = $object->file;
        }

        /**
         * @var EventgalleryLibraryFactoryFolder $folderFactory
         */
        $folderFactory = EventgalleryLibraryFactoryFolder::getInstance();

        $this->_folder = $folderFactory->getFolder($object->folder);
        $this->_file = $object;

        $album = $this->_folder->getAlbum();

        foreach ($album->photos as $photo) {

            if (strcmp($photo['file'], $this->_filename) == 0) {
                $this->_file = (object)$photo;
                break;
            }
        }

        if (isset($this->_file->height)) {
            $this->imageRatio = $this->_file->width / $this->_file->height;
        } else {
            $this->imageRatio = 1;
        }
    }


    /**
     * @return EventgalleryLibraryFolderPicasa
     */
    public function getFolder() {
        return $this->_folder;
    }

    /**
     * @return bool
     */
    public function isCommentingAllowed() {
        return true;
    }


    /**
     *  returns a title with the following format:
     *
     *   <span class="img-caption img-caption-part1">Foo</span>[<span class="img-caption img-caption-part1">Bar</span>][<span class="img-exif">EXIF</span>]
     * @param bool $showImageID
     * @param bool $showExif
     * @return string
     */

    public function getLightBoxTitle($showImageID = false, $showExif = false)
    {
        $caption = "";

        if (isset($this->_file->caption) && strlen($this->_file->caption) > 0) {
            $caption .= '<span class="img-caption img-caption-part1">' . nl2br(htmlspecialchars($this->_file->caption)) . '</span>';
        }

        if ($showExif && isset($this->_file->exif) && strlen($this->_file->exif->model) > 0 && strlen($this->_file->exif->focallength) > 0
            && strlen($this->_file->exif->fstop) > 0
        ) {
            $exif = '<span class="img-exif">' . $this->_file->exif->model . ", " . $this->_file->exif->focallength . " mm, f/"
                . $this->_file->exif->fstop . ", ISO " . $this->_file->exif->iso . "</span>";
            $caption .= $exif;
        }

        return $caption;
    }


    public function getFullImgTag($width = 104, $height = 104)
    {


        if ($this->imageRatio >= 1) {
            $height = round($width / $this->imageRatio);
        } else {
            $width = round($height * $this->imageRatio);
        }
        // css verschiebung berechnen

        return '<img src="'.JURI::root().$this->_blank_script_path.'" '.
    				 'style="width: '.$width.'px; '.
                            'height: '.$height.'px; '.
                            'background-repeat:no-repeat; '.
    						'background-image:url(\'' . htmlspecialchars($this->getThumbUrl($width, $height, true, false), ENT_NOQUOTES, "UTF-8") . '\'); '.
    						'background-position: 50% 50%;" '.
    						'alt="'.$this->getAltContent().'" />';
    }

    public function getThumbImgTag($width = 104, $height = 104, $cssClass = "", $crop = false)
    {

        return '<img class="' . $cssClass . '" '.
    				 'src="'.JURI::root().$this->_blank_script_path.'" '.
    				 'style="width: '.$width.'px; '.
                            'height: '.$height.'px; '.
                            'background-repeat:no-repeat; '.
    						'background-image:url(\'' . htmlspecialchars($this->getThumbUrl($width, $height, true, $crop), ENT_NOQUOTES, "UTF-8") . '\'); '.
    						'background-position: 50% 50%; '.
							'filter: progid:DXImageTransform.Microsoft.AlphaImageLoader( src=\'' . htmlspecialchars($this->getThumbUrl($width, $height, true, $crop), ENT_NOQUOTES, "UTF-8") . '\', sizingMethod=\'scale\'); '.
							'-ms-filter: &quot;progid:DXImageTransform.Microsoft.AlphaImageLoader( src=\'' . htmlspecialchars($this->getThumbUrl($width, $height, true, $crop), ENT_NOQUOTES, "UTF-8") . '\', sizingMethod=\'scale\')&quot;; '.
							'" '.
    				 'alt="'.$this->getAltContent().'" />';
    }

    public function getLazyThumbImgTag($width = 104, $height = 104, $cssClass = "", $crop = false)
    {

        $imgTag = '<img class="eventgallery-lazyme ' . $cssClass . '" '.
										'data-width="' . $this->_file->width . '" '.
										'data-height="' . $this->_file->height . '" '.
								    	'longdesc="' . htmlspecialchars($this->getThumbUrl($width, $height, true, $crop), ENT_NOQUOTES, "UTF-8") . '" '.
								    	'src="'.JURI::root().$this->_blank_script_path.'" '.
								    	'style=" width: '.$width.'px; '.
                                                'height: '.$height.'px; '.
                                                'background-position: 50% 50%; '.
                                                'background-repeat:no-repeat;" '.
										'alt="'.$this->getAltContent().'" '.
					    			'/>';

        return $imgTag;

    }

    public function getImageUrl($width = 104, $height = 104, $fullsize, $larger = false)
    {
        $url = "";

        if ($this->_file == null) {
            return $url;
        }

        if ($fullsize) {
            $url =  $this->_file->image;
        } else {
            if ($this->imageRatio < 1) {
                $url = $this->getThumbUrl($height * $this->imageRatio, $height, $larger);
            } else {
                $url =  $this->getThumbUrl($width, $width / $this->imageRatio, $larger);
            }
        }

        $url = str_replace('http://', 'https://', $url);

        return $url;
    }

    public function getThumbUrl($width = 104, $height = 104, $larger = true, $crop = false)
    {

        if ($this->_file == null) {
            return "";
        }

        if ($width == 0) {
            $width = 104;
        }
        if ($height == 0) {
            $height = 104;
        }


        if ($this->_file->width > $this->_file->height) {
            // querformat
            $googlewidth = $width;
            $resultingHeight = $googlewidth / $this->imageRatio;
            if ($resultingHeight < $height) {
                $googlewidth = round($height * $this->imageRatio);
            }
        } else {
            //hochformat
            $googlewidth = $height;
            $resultingWidth = $googlewidth * $this->imageRatio;
            if ($resultingWidth < $width) {
                $googlewidth = round($height / $this->imageRatio);
            }
        }


        $sizeSet = new EventgalleryHelpersSizeset();
        $saveAsSize = $sizeSet->getMatchingSize($googlewidth);

        // modify google image url. Be aware that even a normal thumb might contain a '-c'. This
        // is the case for album icons for example.
        $values = array_values($this->_file->thumbs);
        if (strpos($values[0], '/s104/')>0) {
            $winner = str_replace('/s104/', "/s$saveAsSize/", $values[0]);
        } else {
            $winner = str_replace('/s104-c/', "/s$saveAsSize-c/", $values[0]);
        }

        // let this work with HTTPS only to remove unsecure content issues
        $winner = str_replace('http://', 'https://', $winner);

        return $winner;
    }

    public function getOriginalImageUrl() {
        $url = $this->_file->originalImage;
        return $url;
    }

    public function getSharingImageUrl() {
        return $this->getOriginalImageUrl();
    }

}
