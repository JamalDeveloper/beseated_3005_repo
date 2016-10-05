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
require_once JPATH_ROOT.'/components/com_eventgallery/config.php';

class EventgalleryLibraryFileLocal extends EventgalleryLibraryFile
{

    protected $_image_script_path = 'components/com_eventgallery/helpers/image.php';
    protected $_blank_script_path = 'media/com_eventgallery/frontend/images/blank.gif';
    public $exif;

    /**
     * creates the lineitem object. $dblineitem is the database object of this line item
     *
     * @param object $object
     * @throws Exception
     */
    function __construct($object)
    {

        parent::__construct($object);

        $app	 = JFactory::getApplication();
        $params = null;
        if ($app instanceof JSite) {
            /**
             * @var JSite $app
             */
            $params = JComponentHelper::getParams('com_eventgallery');

        }else {

            $params = JComponentHelper::getParams('com_eventgallery');
        }

        if ($params->get('use_legacy_image_rendering','0')=='1') {
            $this->_image_script_path = "index.php";
        }


        if (isset($this->_file->exif) ){
            $this->exif = json_decode($this->_file->exif);
        }
        else {
            $this->exif = new stdClass();
        }
    }





    public function getFullImgTag($width=104,  $height=104) {

        return '<img src="'.JURI::root().$this->_blank_script_path.'" '.
	    	             'style="width: '.$width.'px; '.
	    						'height: '.$height.'px; '.
	    	             		'background-repeat:no-repeat; '.
	    	    				'background-position: 50% 50%; '.
	    	    				'background-image:url(\''.htmlspecialchars($this->getThumbUrl($width,$height,false,true), ENT_NOQUOTES, "UTF-8").'\'); '.
	    	    				'" '.
	    				'alt="'.$this->getAltContent().'" />';

    }

    public function getThumbImgTag($width=104,  $height=104, $cssClass="", $crop=false) {
        return '<img src="'.JURI::root().$this->_blank_script_path.'" '.
	    				 'style="width: '.$width.'px; '.
	    						'height: '.$height.'px; '.
	    						'background-repeat:no-repeat; '.
	    						'background-position: 50% 50%; '.
	    						'background-image:url(\''.htmlspecialchars($this->getThumbUrl($width,$height, true, $height==$width), ENT_NOQUOTES, "UTF-8").'\'); '.
								'filter: progid:DXImageTransform.Microsoft.AlphaImageLoader( src=\''.htmlspecialchars($this->getThumbUrl($width,$height, true, $height==$width), ENT_NOQUOTES, "UTF-8").'\', sizingMethod=\'scale\'); '.
								'-ms-filter: &quot;progid:DXImageTransform.Microsoft.AlphaImageLoader( src=\''.htmlspecialchars($this->getThumbUrl($width,$height, true, $height==$width), ENT_NOQUOTES, "UTF-8").'\', sizingMethod=\'scale\')&quot;; '.
	    						'" '.
	    				'alt="'.$this->getAltContent().'" '.
	    				'class="'.$cssClass.'"/>';
    }

    public function getLazyThumbImgTag($width=104,  $height=104, $cssClass="", $crop=false) {
        $imgTag = '<img class="eventgallery-lazyme '.$cssClass.'" '.
    									'data-width="'.$this->_file->width.'" '.
										'data-height="'.$this->_file->height.'" '.
								    	'longdesc="'.htmlspecialchars($this->getThumbUrl($width,$height, true, $crop), ENT_NOQUOTES, "UTF-8").'" '.
								    	'src="'.JURI::root().$this->_blank_script_path.'" '.
								    	'style=" width: '.$width.'px; '.
		    									'height: '.$height.'px; '.
		    									'background-position: 50% 50%; '.
		    									'background-repeat: no-repeat;" '.
								    	'alt="'.$this->getAltContent().'" '.
					    			'/>';
        return $imgTag;
    }

    public function getImageUrl($width=104,  $height=104, $fullsize, $larger=false) {
        if ($fullsize) {
            return JURI::root().$this->_image_script_path."?option=com_eventgallery&mode=full&view=resizeimage&folder=".$this->getFolderName()."&file=".urlencode($this->getFileName());
        } else {

            if ($height>$width) {
                $width = $height;
            }

            return JURI::root().$this->_image_script_path."?option=com_eventgallery&width=".$width."&view=resizeimage&folder=".$this->getFolderName()."&file=".urlencode($this->getFileName());
        }
    }



    public function getThumbUrl ($width=104, $height=104, $larger=true, $crop=false) {

        if ($crop) {
            $mode = 'crop';
        } else {
            $mode = 'uncrop';
        }

        if ($height>$width) {
            $width = $height;
        }

        return JURI::root().$this->_image_script_path."?option=com_eventgallery&mode=".$mode."&width=".$width."&view=resizeimage&folder=".$this->getFolderName()."&file=".urlencode($this->getFileName());
    }

    public function getOriginalImageUrl() {

    	return JURI::base().substr(JRoute::_('index.php?option=com_eventgallery&view=download&&folder='.$this->getFolderName().'&file='.urlencode($this->getFileName()) ), strlen(JURI::base(true)) + 1);
        
    }

    public function getSharingImageUrl() {

        return JURI::base().substr(JRoute::_('index.php?option=com_eventgallery&is_for_sharing=true&view=download&&folder='.$this->getFolderName().'&file='.urlencode($this->getFileName()) ), strlen(JURI::base(true)) + 1);

    }

    /**
     * increases the hit counter in the database
     */
    public function countHit() {
        /**
         * @var TableFile $table
         */
        $table = JTable::getInstance('File', 'Table');


        $table->bind($this->_file);
        $table->hits++;
        $table->store();


    }

}
