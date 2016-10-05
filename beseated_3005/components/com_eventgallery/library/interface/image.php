<?php

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


interface  EventgalleryLibraryInterfaceImage
{

    public function getFullImgTag($width = 104, $height = 104);

    public function getThumbImgTag($width = 104, $height = 104, $cssClass = "", $crop = false);

    public function getLazyThumbImgTag($width = 104, $height = 104, $cssClass = "", $crop = false);

    public function getImageUrl($width = 104, $height = 104, $fullsize, $larger = false);

    public function getThumbUrl($width = 104, $height = 104, $larger = true, $crop = false);

    /**
     * Returns the original download url for the image
     *
     * @return mixed
     */
    public function getOriginalImageUrl();

    /**
     * returns a url which provides the image url to be read by Facebook & stuff
     *
     * @return String
     */
    public function getSharingImageUrl();
    /**
     * @param EventgalleryLibraryImagelineitem $lineitem
     *
     * @return string
     */
    public function getMiniCartThumb($lineitem);

    /**
     * @param EventgalleryLibraryImagelineitem $lineitem
     *
     * @return string
     */
    public function getCartThumb($lineitem);

}