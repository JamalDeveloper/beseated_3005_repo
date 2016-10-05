<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.updatenotification
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Uncomment the following line to enable debug mode (update notification email sent every single time)
// define('PLG_SYSTEM_UPDATENOTIFICATION_DEBUG', 1);

/**
 * Joomla! Update Notification plugin
 *
 * Sends out an email to all Super Users or a predefined email address when a new Joomla! version is available.
 *
 * This plugin is a direct adaptation of the corresponding plugin in Akeeba Ltd's Admin Tools. The author has
 * consented to relicensing their plugin's code under GPLv2 or later (the original version was licensed under
 * GPLv3 or later) to allow its inclusion in the Joomla! CMS.
 *
 * @since  3.5
 */
class PlgSystemLuxuryservicesdelete extends JPlugin
{
	public function onAfterInitialise()
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$input          = JFactory::getApplication()->input;
		$detail_page    = $input->getInt('detail_page',0);

		$session             = JFactory::getSession();
	    $yacht_service_saved =  $session->get( 'yacht_service_saved',0);
	  
	    if($detail_page == 0)
	    {
		    if($yacht_service_saved)
		    {
		    	$session->set( 'yacht_service_saved', 0);

				setcookie('yacht_ser_default_img_id', null, -1);
				setcookie('yacht_new_ser_img_id', null, -1);
				setcookie('page_load', null, -1);
				setcookie('yacht_service_saved', null, -1);
		    }
		    else
		    {
		    	if(isset($_COOKIE['yacht_new_ser_img_id']) && !empty($_COOKIE['yacht_new_ser_img_id']))
		    	{
		    		$new_image_ids = implode(',',json_decode($_COOKIE['yacht_new_ser_img_id']));

			    	if($new_image_ids)
			    	{
			    		// for delete new images from server
			    		// Initialiase variables.
			    		$db    = JFactory::getDbo();
			    		$query = $db->getQuery(true);
			    		
			    		// Create the base select statement.
			    		$query->select('thumb_image,image')
			    			->from($db->quoteName('#__beseated_element_images'))
			    			->where($db->quoteName('image_id') . ' IN ('.$new_image_ids.')');
			    	
			    		// Set the query and load the result.
			    		$db->setQuery($query);
			    		
			    		$newServiceImages = $db->loadObjectList();

			    		foreach ($newServiceImages as $key => $serImage) 
			    		{
			    			$newImgPath      = str_replace('/', '\\', JPATH_SITE.'/images/beseated/'.$serImage->image);
							$newThumbImgPath = str_replace('/', '\\', JPATH_SITE.'/images/beseated/'.$serImage->thumb_image);

							if(file_exists($newImgPath))
							{
								@unlink($newImgPath);
							}
							if(file_exists($newThumbImgPath))
							{
								@unlink($newThumbImgPath);
							}
			    		}
			    		
			    		// for delete image data from database
				    	// Initialiase variables.
				    	$db    = JFactory::getDbo();
				    	$query = $db->getQuery(true);
				    	
				    	// Create the base delete statement.
				    	$query->delete()
				    		->from($db->quoteName('#__beseated_element_images'))
				    		->where($db->quoteName('image_id') . ' IN ('.$new_image_ids.')');
				    	
				    	// Set the query and execute the delete.
				    	$db->setQuery($query);
				    	
				    	$db->execute();

				    	
				    }	
				}

				$service_id =   $_COOKIE['service_id'];

				if(isset($_COOKIE['yacht_ser_default_img_id']) && !empty($_COOKIE['yacht_ser_default_img_id']))
    			{
  					$def_image_id = $_COOKIE['yacht_ser_default_img_id'];
  					
  					// Initialiase variables.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__beseated_element_images'))
						->set($db->quoteName('is_default') . ' = ' . $db->quote(0))
						->where($db->quoteName('service_id') . ' = ' . $db->quote($service_id))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('yacht.service'));

				
					$db->setQuery($query);
					$db->execute();

			    	// set old image as a default image
			    	$tblImages  = JTable::getInstance('Images','BeseatedTable');
					$tblImages->load($def_image_id);

					$tblImages->is_default = 1;
					$tblImages->store();

					$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
					$tblService->load($service_id);
					$tblService->thumb_image = $tblImages->thumb_image;
					$tblService->image       = $tblImages->image;
					$tblService->store();
				}

				if(!isset($_COOKIE['yacht_ser_default_img_id']) && $service_id)
				{
					$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
					$tblService->load($service_id);
					$tblService->thumb_image = '';
					$tblService->image       = '';
					$tblService->store();
				}
			}

			unset($_COOKIE['page_load']);
			setcookie('page_load', null, -1);
			setcookie('service_id', null, -1);
			unset($_COOKIE['yacht_new_ser_img_id']);
			setcookie('yacht_new_ser_img_id', null, -1);
			unset($_COOKIE['yacht_ser_default_img_id']);
			setcookie('yacht_ser_default_img_id', null, -1);
		}
	}
	
}
