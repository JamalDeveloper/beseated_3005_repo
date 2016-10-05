<?php
/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT.'/components/com_eventgallery/config.php';
require_once 'Resizeimage.php';

class EventgalleryControllerDownload extends EventgalleryControllerResizeimage
{
    public function display($cachable = false, $urlparams = array())
    {
        /**
         * @var JApplicationSite $app
         * @var \Joomla\Registry\Registry $registry
         */
        $app = JFactory::getApplication();
        $params = $app->getParams();
        $allowDownloadOfOriginalImage = $params->get('download_original_images', 0) == 1;

        $str_folder = JRequest::getVar('folder', null);
        $str_file = JRequest::getVar('file', null);
        $is_sharing_download = JRequest::getBool('is_for_sharing', false);

        /**
         * @var EventgalleryLibraryFactoryFile $fileFactory
         */
        $fileFactory = EventgalleryLibraryFactoryFile::getInstance();

        $file = $fileFactory->getFile($str_folder, $str_file);

        if (!is_object($file) || !$file->isPublished()) {
            JError::raiseError(404, JText::_('COM_EVENTGALLERY_SINGLEIMAGE_NO_PUBLISHED_MESSAGE'));
        }

        $folder = $file->getFolder();

        if (!$folder->isPublished() || !$folder->isVisible()) {
            JError::raiseError(404, JText::_('COM_EVENTGALLERY_EVENT_NO_PUBLISHED_MESSAGE'));
        }

        // deny downloads if the social sharing option is disabled
        if (    $params->get('use_social_sharing_button', 0)==0  ) {
            $allowDownloadOfOriginalImage = false;
        } 
                
        // allow the download if at least one sharing type is enabled both global and for the event
        if (        
                ($params->get('use_social_sharing_facebook', 0)==1 && $folder->getAttribs()->get('use_social_sharing_facebook', 1)==1)
            ||  ($params->get('use_social_sharing_google', 0)==1 && $folder->getAttribs()->get('use_social_sharing_google', 1)==1)
            ||  ($params->get('use_social_sharing_twitter', 0)==1 && $folder->getAttribs()->get('use_social_sharing_twitter', 1)==1)
            ||  ($params->get('use_social_sharing_pinterest', 0)==1 && $folder->getAttribs()->get('use_social_sharing_pinterest', 1)==1)
            ||  ($params->get('use_social_sharing_email', 0)==1 && $folder->getAttribs()->get('use_social_sharing_email', 1)==1)
            ||  ($params->get('use_social_sharing_download', 0)==1 && $folder->getAttribs()->get('use_social_sharing_download', 1)==1)
            
            ) {
        	
        } else {
            $allowDownloadOfOriginalImage = false;
        }



        $basename = COM_EVENTGALLERY_IMAGE_FOLDER_PATH . $folder->getFolderName() . '/';
        

        if ( $allowDownloadOfOriginalImage ) {

			// try the path to a possible original file
        	$filename = $basename.'/'. COM_EVENTGALLERY_IMAGE_ORIGINAL_SUBFOLDER .'/'.$file->getFileName();

        	if (!file_exists($filename)) {
            	$filename = $basename . $file->getFileName();
        	}

            $mime = ($mime = getimagesize($filename)) ? $mime['mime'] : $mime;
            $size = filesize($filename);
            $fp   = fopen($filename, "rb");
            if (!($mime && $size && $fp)) {
                // Error.
                return;
            }


            header("Content-type: " . $mime);
            header("Content-Length: " . $size);
            if (!$is_sharing_download) {
                header("Content-Disposition: attachment; filename=" . $file->getFileName());
            }
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            fpassthru($fp);
            fclose($fp);
            die();
        } else {
            if (!$is_sharing_download) {
                header("Content-Disposition: attachment; filename=" . $file->getFileName());
            }
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            $this->resize($file->getFolderName(), $file->getFileName(), COM_EVENTGALLERY_IMAGE_ORIGINAL_MAX_WIDTH , null, null);
            die();
        }

    }

    /**
     * This method is used to enable the download of files.
     *
     * @throws Exception
     */
    public function order() {

        $app = JFactory::getApplication();

        $str_orderid = JRequest::getString('orderid', null);
        $str_lineitemid = JRequest::getString('lineitemid', null);
        $str_token = JRequest::getString('token', null);


        /**
         * @var EventgalleryLibraryFactoryOrder $orderFactory
         */
        $orderFactory = EventgalleryLibraryFactoryOrder::getInstance();
        $order = $orderFactory->getOrderById($str_orderid);
        if ($order == null) {

        }

        $lineitem = $order->getLineItem($str_lineitemid);
        if ($lineitem == null) {
            throw new Exception("Invalid Request.");
        }

        if (strcmp($str_token, $order->getToken())!=0) {
            throw new Exception("Invalid Request.");
        }

        $file = $lineitem->getFile();

        if (strcmp($file->getFolder()->getFolderType()->getName(),'local')!=0) {
            $app->redirect($file->getOriginalImageUrl());
        }

        $basename = COM_EVENTGALLERY_IMAGE_FOLDER_PATH . $file->getFolderName() . '/';

        $filename = $basename . $file->getFileName();

        $imageSize = intval($lineitem->getImageType()->getSize());

        header("Content-Disposition: attachment; filename=" . $order->getDocumentNumber(). '-' . $lineitem->getId() . '-' . $file->getFileName());
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        if (is_int($imageSize) && $imageSize>0 ) {
            $this->resize($file->getFolderName(), $file->getFileName(), $imageSize , null, null, false, false, false, false);
            die();
        }

        // try the path to a possible original file
        $fullfilename = $basename.'/'. COM_EVENTGALLERY_IMAGE_ORIGINAL_SUBFOLDER .'/'.$file->getFileName();

        if (file_exists($fullfilename)) {
            $filename = $fullfilename;
        }

        $mime = ($mime = getimagesize($filename)) ? $mime['mime'] : $mime;
        $size = filesize($filename);
        $fp   = fopen($filename, "rb");
        if (!($mime && $size && $fp)) {
            // Error.
            return;
        }

        header("Content-type: " . $mime);
        header("Content-Length: " . $size);
        fpassthru($fp);
        fclose($fp);
        die();

    }

}
