<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * The Beseated Profile Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerProtectionProfile extends JControllerAdmin
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
	public function getModel($name = 'ProtectionProfile', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function contactadmin()
	{
		$bctedConfig = BctedHelper::getExtensionParam_2();
		$contactEmail = $bctedConfig->contact_email;

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('email')
			->from($db->quoteName('#__users','u'))
			->where($db->quoteName('email') . ' <> ""');
		$query->join('RIGHT','#__user_usergroup_map AS ugm ON ugm.user_id=u.id AND ugm.group_id=8');

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadColumn();

		// Initialise variables.
		$app     = JFactory::getApplication();
		$config  = JFactory::getConfig();

		$site    = $config->get('sitename');
		$from    = $config->get('mailfrom');
		$sender  = $config->get('fromname');
		$email   = $contactEmail;

		$subject = JText::_('COM_BCTED_CONATACT_EMAIL_SUBJECT');

		$conatctName    = $app->input->get('contact_name','','string');
		$conatctEmail   = $app->input->get('contact_email','','string');
		$conatctMobile  = $app->input->get('contact_mobile','','string');
		$conatctMessage = $app->input->get('contact_message','','string');
		$returnURL      = $app->input->get('return','','string');
		$imgPath        = JUri::base().'images/email-footer-logo.png';
		$imageLink      = '<img title="Beseated" alt="Beseated" src="'.$imgPath.'"/>';
		$body           = JText::sprintf('COM_BCTED_CONATACT_EMAIL_BODY', $conatctName, $conatctName, $conatctEmail, $conatctMobile,$conatctMessage,$imageLink);

		// Clean the email data.
		$sender  = JMailHelper::cleanAddress($sender);
		$subject = JMailHelper::cleanSubject($subject);
		$body    = JMailHelper::cleanBody($body);

		// Send the email.
		$return = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body, true);

		// Initialise variables.
		$app     = JFactory::getApplication();
		$config  = JFactory::getConfig();

		$site    = $config->get('sitename');
		$from    = $config->get('mailfrom');
		$sender  = $config->get('fromname');
		$conatctEmail = $app->input->get('contact_email','','string');

		$email   = $conatctEmail;
		$subject = JText::_('COM_BESEATED_THANK_YOU_CONTACT_EMAIL_SUBJECT');

		// Build the message to send.
		$body     = JText::sprintf('COM_BESEATED_THANK_YOU_CONTACT_EMAIL_BODY',$conatctName,$imageLink);

		// Clean the email data.
		$sender  = JMailHelper::cleanAddress($sender);
		$subject = JMailHelper::cleanSubject($subject);
		$body    = JMailHelper::cleanBody($body);

		// Send the email.
		$return2 = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body,true);
		$app     = JFactory::getApplication();
		$menu    = $app->getMenu()->getDefault();
		$this->setRedirect(base64_decode($returnURL).'&show_send_message=1');

	}

	public function change_currency()
	{
		$app           = JFactory::getApplication();
		$input         = $app->input;
		$protection_id = $input->get('protection_id',0,'int');
		$currency_code = $input->get('currency_code','','string');

		if(!$protection_id || empty($currency_code))
		{
			echo "400";
			exit;
		}

		$data = array();

		if($currency_code == 'EUR')
		{
			$data['currency_code'] = $currency_code;
			$data['currency_sign'] = "€";
		}
		else if ($currency_code == 'GBP')
		{
			$data['currency_code'] = $currency_code;
			$data['currency_sign'] = "£";
		}
		else if ($currency_code == 'AED')
		{
			$data['currency_code'] = $currency_code;
			$data['currency_sign'] = "AED";
		}
		else if ($currency_code == 'USD')
		{
			$data['currency_code'] = $currency_code;
			$data['currency_sign'] = "$";
		}
		else if ($currency_code == 'CAD')
		{
			$data['currency_code'] = $currency_code;
			$data['currency_sign'] = "$";
		}
		else if ($currency_code == 'AUD')
		{
			$data['currency_code'] = $currency_code;
			$data['currency_sign'] = "$";
		}

		$model  = $this->getModel();
		$result = $model->saveProtectionProfile($data,$protection_id);

		if($result)
		{
			echo "200";
			exit;
		}

		echo "500";
		exit;
	}


	public function save()
	{

		$app   = JFactory::getApplication();
		$input = $app->input;
		$user  = JFactory::getUser();

		$protection_id   = $input->get('protection_id','','string');
		$protection_name = $input->get('protection_name','','string');
		$location        = $input->get('location','','string');
		$city            = $input->get('city','','string');
		$country         = $input->get('country','','string');
		$latitude        = $input->get('latitude','','string');
		$longitude       = $input->get('longitude','','string');
		$currency_code   = $input->get('currency_code','','string');

		if($currency_code == 'EUR')
		{
			$data['currency_code'] = $currency_code;
			$data['currency_sign'] = "€";
		}
		else if ($currency_code == 'GBP')
		{
			$data['currency_code'] = $currency_code;
			$data['currency_sign'] = "£";
		}
		else if ($currency_code == 'AED')
		{
			$data['currency_code'] = $currency_code;
			$data['currency_sign'] = "AED";
		}
		else if ($currency_code == 'USD')
		{
			$data['currency_code'] = $currency_code;
			$data['currency_sign'] = "$";
		}
		else if ($currency_code == 'CAD')
		{
			$data['currency_code'] = $currency_code;
			$data['currency_sign'] = "$";
		}
		else if ($currency_code == 'AUD')
		{
			$data['currency_code'] = $currency_code;
			$data['currency_sign'] = "$";
		}

		$data['protection_name'] = $protection_name;
		$data['location']        = $location;
		$data['user_id']         = $user->id;


		if(!empty($latitude))
		{
			$data['latitude'] = $latitude;
		}

		if(!empty($city))
		{
			$data['city'] = $city;
		}

		if(!empty($longitude))
		{
			$data['longitude'] = $longitude;
		}

		$model = $this->getModel();

		if(count($data) > 0)
		{
			$result = $model->saveProtectionProfile($data,$protection_id);
		}
		else
		{
			$result = false;
		}

		$menuItem = BeseatedHelper::getBeseatedMenuItem('protection-profile');
		$Itemid   = $menuItem->id;
		$link     = $menuItem->link.'&Itemid='.$Itemid;
		$this->setRedirect($link,'Profile Saved');
	}

	public function uploadImage()
	{
		$input          = JFactory::getApplication()->input;
		$file           = $input->files->get('image');
		$user           = JFactory::getUser();
		$protection     = BeseatedHelper::getUserElementID($user->id);
		$imagePath      = BeseatedHelper::uploadFile($file,'Protection',$protection->protection_id);
		$source         = JPATH_SITE.'/'.$imagePath;
		$orignalFile    = pathinfo($source);
		$videoExtAllow  = array('mov','mp4');
		$storeImg       = $imagePath;
		$storeFlv       = "";
		$storeMp4       = "";
		$storeWebm      = "";
		$hasVideo       = 0;
		$storeThumbPath = '';

		if(!empty($imagePath) && in_array($orignalFile['extension'], $videoExtAllow))
		{
			$hasVideo = 1;
			$destPng  = $orignalFile['dirname'].'/'.$orignalFile['filename'].'_png.png';
			$storeImg =  'images/beseated/Protection/'.$protection->protection_id.'/'.$orignalFile['filename'].'_png.png';
			$command  = "/usr/bin/ffmpeg -i $source -r 1 -s 700x600 -f image2 $destPng";
			$output   = shell_exec($command);

			$destFlv  = $orignalFile['dirname'].'/'.$orignalFile['filename'].'_flv.flv';
			$storeFlv =  'images/beseated/Protection/'.$protection->protection_id.'/'.$orignalFile['filename'].'_flv.flv';
			$command  = "/usr/bin/ffmpeg -y -i $source -g 30 -vcodec copy -acodec copy $destFlv 2>".JPATH_SITE."/ffmpeg_test1.txt";
			$output   = shell_exec($command);

			$destMp4  = $orignalFile['dirname'].'/'.$orignalFile['filename'].'_mp4.mp4';
			$storeMp4 =  'images/beseated/Protection/'.$protection->protection_id.'/'.$orignalFile['filename'].'_mp4.mp4';
			$command  = "/usr/bin/ffmpeg -i ".$destFlv." -ar 22050 ".$destMp4; //Wroking
			$output   = shell_exec($command);

			$destWebm  = $orignalFile['dirname'].'/'.$orignalFile['filename'].'_webm.webm';
			$storeWebm =  'images/beseated/Protection/'.$protection->protection_id.'/'.$orignalFile['filename'].'_webm.webm';
			$command   = "/usr/bin/ffmpeg -i ".$source." -acodec libvorbis -ac 2 -ab 96k -ar 44100 -b 345k -s 640x360 ".$destWebm; //Wroking
			$output    = shell_exec($command);

			$videoPath      = JPATH_SITE.'/'.'images/beseated/'.$imagePath;
			$videoThumb     = JPATH_SITE.'/'.'images/beseated/Protection/'.$protection->protection_id.'/';
			$videoimagePath = BeseatedHelper::createVideoThumb($videoPath, $videoThumb);
			$storeThumbPath = 'Protection/'.$protection->protection_id.'/'.$videoimagePath;

			if(!file_exists(JPATH_SITE.'/images/beseated/'.$storeThumbPath))
			{
				$storeThumbPath = '';
			}
		}
		else
		{
			if(!JFolder::exists(JPATH_ROOT . "/images/beseated/Protection/". $protection->protection_id . "/thumb"))
			{
				JFolder::create(JPATH_ROOT . "/images/beseated/Protection/". $protection->protection_id . "/thumb");
			}
			$pathInfo       = pathinfo(JPATH_SITE.'/images/beseated/'.$imagePath);
			$thumbPath      = $pathInfo['dirname'].'/thumb/thumb_'.$pathInfo['basename'];
			$storeThumbPath = "Protection/". $protection->protection_id . "/thumb/thumb_".$pathInfo['basename'];
			BeseatedHelper::createThumb(JPATH_SITE.'/images/beseated/'.$imagePath,$thumbPath);
			if(!file_exists(JPATH_SITE.'/images/beseated/'.$storeThumbPath))
			{
				$storeThumbPath = '';
			}
		}

		if(!empty($imagePath))
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($db->quoteName('#__beseated_element_images'))
				->where($db->quoteName('element_id') . ' = ' . $db->quote($protection->protection_id))
				->where($db->quoteName('element_type') . ' = ' . $db->quote('Protection'))
				->where($db->quoteName('is_default') . ' = ' . $db->quote('1'));
			
			// Set the query and load the result.
			$db->setQuery($query);
			$is_element_image = $db->loadObject();

			$tblImages = JTable::getInstance('Images','BeseatedTable',array());
			$tblImages->load(0);
			$fileType  = JFile::getExt($file['name']);

			/*$tblChauffeur->load($club->venue_id);
			$prvImages             = array();
			$prvImages[]           = $tblVenue->venue_image;
			$tblVenue->venue_image = $storeImg;*/
			$tblImages->element_id   = $protection->protection_id;
			$tblImages->element_type = 'Protection';
			$tblImages->thumb_image  = $storeThumbPath;
			$tblImages->image        = $imagePath;
			$tblImages->is_video     = $hasVideo;
			$tblImages->file_type    = $fileType;
			$tblImages->time_stamp   = time();

			if(empty($is_element_image))
			{
				$tblImages->is_default    = '1';
			}


			if(!empty($storeThumbPath))
			{
				$prvImages[]            = $tblImages->thumb_image;
				$tblImages->thumb_image = $storeThumbPath;
			}

			if($hasVideo == 1)
			{
				$prvImages[] = $tblVenue->venue_video;
				$prvImages[] = $tblVenue->venue_video_flv;
				$prvImages[] = $tblVenue->venue_video_mp4;
				$prvImages[] = $tblVenue->venue_video_webm;

				$tblVenue->venue_video      = $imagePath;
				$tblVenue->venue_video_flv  = $storeFlv;
				$tblVenue->venue_video_mp4  = $storeMp4;
				$tblVenue->venue_video_webm = $storeWebm;
			}
			/*else
			{
				$tblVenue->venue_video      = "";
				$tblVenue->venue_video_flv  = "";
				$tblVenue->venue_video_mp4  = "";
				$tblVenue->venue_video_webm = "";
			}*/

			if($tblImages->store())
			{
				/*for ($i = 0; $i < count($prvImages) ; $i++)
				{
					if(file_exists( JPATH_SITE.'/'.$prvImages[$i]))
					{
						@unlink(JPATH_SITE.'/'.$prvImages[$i]);
					}
				}*/
				echo "200";
			}
			else
			{
				echo "500";
			}
		}
		else{
			echo "500";
		}

		exit;
	}

	public function changeDefaultImage()
	{
		$input     = JFactory::getApplication()->input;
		$imageId   = $input->getInt('image_id');
		$elementId = $input->getInt('element_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_element_images'))
			->set($db->quoteName('is_default') . ' = ' . $db->quote(0))
			->set($db->quoteName('image_id') . ' = ' . $db->quote($imageId))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementId));


		// Set the query and execute the update.
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		$tblImages = JTable::getInstance('Images','BeseatedTable',array());
		$tblImages->load($imageId);
		$tblImages->is_default  = 1;

		if($tblImages->store())
		{
			echo "200";
		}
		else
		{
			echo "500";
		}
		exit;
	}

	public function deleteProfileImage()
	{
		$input       = JFactory::getApplication()->input;
		$imageId     = $input->getInt('image_id');
		$elementId   = $input->getInt('element_id');
		$model       = $this->getModel();

		$deleteImage = $model->deleteImage($imageId, $elementId);

		if ($deleteImage == 1)
		{
			echo "200";
		}
		else
		{
			echo "500";
		}

		exit;
	}

}
