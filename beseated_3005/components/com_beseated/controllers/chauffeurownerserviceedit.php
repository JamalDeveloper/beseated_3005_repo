<?php
/**
 * @package     The Beseated.Administrator
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * The Beseated Club Owner Tables Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerChauffeurOwnerServiceEdit extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config	An optional associative array of configuration settings.
	 * @return  ContentControllerArticles
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   0.0.1
	 */
	public function getModel($name = 'ChauffeurOwnerServiceEdit', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function save()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$view        = $input->get('view');
		$file        = $input->files->get('jform');
		$data        = $input->get('jform',array(),'array');
		$serviceID   = $input->get('service_id',0,'int');
		$chauffeurID = $input->get('chauffeur_id',0,'int');
		$unique_code = $input->get('unique_code','','string');
		$chauffeurID = $chauffeurID > 0 ? $chauffeurID : $data['chauffeur_id'];
		$user        = JFactory::getUser();
	
		$serviceID = ($unique_code) ? 0 : $serviceID;
/*
		if(!empty($imagePath))
		{
			$postData['image'] = $imagePath;
			$pathInfo          = pathinfo(JPATH_SITE.'/images/beseated/'.$imagePath);
			$thumbPath         = $pathInfo['dirname'].'/thumb/thumb_'.$pathInfo['basename'];
			$storeThumbPath    = "Chauffeur/". $chauffeurID . "/Services/thumb/thumb_".$pathInfo['basename'];

			if(!JFolder::exists($pathInfo['dirname'].'/thumb/'))
			{
				@chmod($pathInfo['dirname'].'/thumb/',0777);
				JFolder::create($pathInfo['dirname'].'/thumb/');
				@chmod($pathInfo['dirname'].'/thumb/',0777);
			}

			BeseatedHelper::createThumb(JPATH_SITE.'/images/beseated/'.$imagePath,$thumbPath);

			if(!empty($storeThumbPath))
			{
				$postData['thumb_image'] = $storeThumbPath;
			}
		}
*/
		if(!empty($data['service_name']))
		{
			$postData['service_name']        = $data['service_name'];
		}

		if(!empty($data['service_type']))
		{
			$postData['service_type']  = $data['service_type'];
		}

		if(!empty($data['capacity']))
		{
			$postData['capacity']        = $data['capacity'];
		}

		$postData['service_id']   = $serviceID;

		if ($chauffeurID){
			$postData['chauffeur_id'] = $chauffeurID;
		}else{
			$postData['chauffeur_id'] = $data['chauffeur_id'];
		}

		$postData['published']    = 1;
		$postData['created']      = date('Y-m-d H:i:s');
		$postData['time_stamp']   = time();

		$model    = $this->getModel();
		$result   = $model->save($postData,$serviceID,$unique_code);
		$menuItem = BeseatedHelper::getBeseatedMenuItem('chauffeur-services');
		$Itemid   = $menuItem->id;
		$link     = $menuItem->link.'&Itemid='.$Itemid;

		$this->setRedirect($link,'Service Saved');
	}

	public function changeDefaultImage()
	{
		$input          = JFactory::getApplication()->input;
		$image_id       = $input->getInt('image_id');
		$serviceID      = $input->getInt('service_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_element_images'))
			->set($db->quoteName('is_default') . ' = ' . $db->quote(0))
			->where($db->quoteName('service_id') . ' = ' . $db->quote($serviceID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('chauffeur.service'));

		// Set the query and execute the update.
		$db->setQuery($query);
		$db->execute();

		$tblImages  = JTable::getInstance('Images','BeseatedTable');
		$tblImages->load($image_id);

		$tblImages->is_default = 1;
		$tblImages->store();

		$tblService = JTable::getInstance('ChauffeurService', 'BeseatedTable');
		$tblService->load($serviceID);
		$tblService->thumb_image = $tblImages->thumb_image;
		$tblService->image       = $tblImages->image;
		$tblService->store();

		echo "200";
		exit();


	}

	public function uploadServiceImage()
	{
		$input          = JFactory::getApplication()->input;
		$file           = $input->files->get('image');
		$service_id     = $serviceID = $input->getInt('service_id');
		$elementId      = $input->getInt('chauffeur_id');
		$uniqueCode     = $input->getstring('unique_code','');
		$Itemid         = $input->getInt('Itemid');

		if(empty($uniqueCode))
		{
			$uniqueCode = $this->getToken();
		}

		$serviceID = ($serviceID) ? $serviceID : $uniqueCode;

		$user           = JFactory::getUser();
		$chauffeur          = BeseatedHelper::getUserElementID($user->id);

		$chauffeurID        = $chauffeur->chauffeur_id;

		if(is_array($file) && isset($file['size']) && $file['size']>0)
		{
			$defualtPath = JPATH_ROOT . '/images/beseated/';
			$serviceImage = BeseatedHelper::uplaodServiceImage($file,'Chauffeur',$chauffeurID,$serviceID);

			if(!empty($serviceImage))
			{
				if(!JFolder::exists($defualtPath.'Chauffeur/'.$chauffeurID.'/Services/'.$serviceID.'/thumb'))
				{
					JFolder::create($defualtPath.'Chauffeur/'.$chauffeurID .'/Services/'.$serviceID.'/thumb');
				}

				$pathInfo            = pathinfo($defualtPath.$serviceImage);
				$fileType            = $pathInfo['extension'];

				$thumbPath           =  $pathInfo['dirname'].'/thumb/thumb_'.$pathInfo['basename'];
				$storeThumbPath      = 'Chauffeur/'. $chauffeurID . '/Services/'.$serviceID.'/thumb/thumb_'.$pathInfo['basename'];
				BeseatedHelper::createThumb($defualtPath.$serviceImage,$thumbPath);

				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base update statement.
				$query->update($db->quoteName('#__beseated_element_images'))
					->set($db->quoteName('is_default') . ' = ' . $db->quote(0))
					->where($db->quoteName('element_id') . ' = ' . $db->quote($chauffeurID))
					->where($db->quoteName('service_id') . ' = ' . $db->quote($serviceID))
					->where($db->quoteName('element_type') . ' = ' . $db->quote('chauffeur.service'));

				// Set the query and execute the update.
				$db->setQuery($query);
				$db->execute();

				$tblImages               = JTable::getInstance('Images','BeseatedTable');
				$tblImages->load(0);
				$tblImages->element_id   = $chauffeurID;
				$tblImages->service_id   = $serviceID;
				$tblImages->element_type = 'chauffeur.service';
				$tblImages->thumb_image  = $storeThumbPath;
				$tblImages->image        = $serviceImage;
				$tblImages->is_default   = 1;
				$tblImages->file_type    = $fileType;
				$tblImages->time_stamp   = time();
				$tblImages->store();

				$imageIDs = array();
				$oldImgDecodeID = json_decode($_COOKIE['chauffeur_new_ser_img_id']);

				$newImageIDs = $tblImages->image_id;

				if($oldImgDecodeID)
				{
					$allImages = array_merge(array($newImageIDs),$oldImgDecodeID);
				}
				else
				{
					$allImages = array($newImageIDs);
				}

				$imageIDs = json_encode($allImages);
				$input->cookie->set( 'chauffeur_new_ser_img_id',  $imageIDs);

				if($service_id)
				{
					$tblService = JTable::getInstance('ChauffeurService', 'BeseatedTable');
					$tblService->load($service_id);
					$tblService->thumb_image = $storeThumbPath;
					$tblService->image       = $serviceImage;
					$tblService->store();
				}
				
			}
		}

		if($service_id)
		{
			$redirect_url = Juri::base().'/index.php?option=com_beseated&view=chauffeurownerserviceedit&detail_page=1&chauffeur_id='.$elementId.'&service_id='.$service_id.'&Itemid='.$Itemid;
		}
		else
		{
			$redirect_url = Juri::base().'index.php?option=com_beseated&view=chauffeurownerserviceedit&detail_page=1&unique_code='.$uniqueCode.'&chauffeur_id='.$elementId.'&service_id='.$service_id.'&Itemid='.$Itemid;
		}

		// http://localhost/beseated_3005/index.php?option=com_beseated&view=yachtownerserviceedit&detail_page=1&yacht_id=1&service_id=0&Itemid=301
		echo $redirect_url;
		exit();
	}

	public function getToken($length = 7)
	{
	    $token = "";
	    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
	    //$codeAlphabet.= "0123456789";
	    $max = strlen($codeAlphabet) - 1;
	    for ($i=0; $i < $length; $i++) {
	        $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max)];
	    }
	    return $token;
	}

	public function crypto_rand_secure($min, $max)
	{
	    $range = $max - $min;
	    if ($range < 1) return $min; // not so random...
	    $log = ceil(log($range, 2));
	    $bytes = (int) ($log / 8) + 1; // length in bytes
	    $bits = (int) $log + 1; // length in bits
	    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
	    do {
	        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
	        $rnd = $rnd & $filter; // discard irrelevant bits
	    } while ($rnd >= $range);
	    return $min + $rnd;
	}
}
