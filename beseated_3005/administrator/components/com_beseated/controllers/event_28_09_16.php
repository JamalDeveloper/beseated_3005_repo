<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.utilities.utility');

/**
 * Beesated Event Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerEvent extends JControllerForm
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 * @since   1.6
	 */
	public function unpublish()
	{
		$user   = JFactory::getUser();
		$ids    = $this->input->get('cid', 0, 'int');

		// Get the model.
		$model = $this->getModel();

		// Publish the items.
		if (!$model->unpublished($ids))
		{
			JError::raiseWarning(500, $model->getError());
		}

		$this->setRedirect('index.php?option=com_beseated&view=events');
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 * @since   1.6
	 */
	public function publish()
	{
		$user   = JFactory::getUser();
		$ids    = $this->input->get('cid', 0, 'int');

		// Get the model.
		$model = $this->getModel();

		// Publish the items.
		if (!$model->published($ids))
		{
			JError::raiseWarning(500, $model->getError());
		}

		$this->setRedirect('index.php?option=com_beseated&view=events');
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 * @since   1.6
	 */
	public function delete()
	{
		$user   = JFactory::getUser();
		$ids    = $this->input->get('cid', array(), 'array');

		// Get the model.
		$model = $this->getModel();

		// Publish the items.
		if (!$model->delete($ids))
		{
			JError::raiseWarning(500, $model->getError());
		}

		$this->setRedirect('index.php?option=com_beseated&view=events');
	}

	/**
	 * Method to save event data ussing ajax
	 *
	 * @return  void
	 * @since   1.6
	 */
	public function ajax_event_save()
	{
		$user   = JFactory::getUser();

		$input    = JFactory::getApplication()->input;

		$form_data    = $input->get('form_data',array(),'array');

		$file = $this->input->files->get('file','');

		$file = '';


		/*$event_name       = $this->input->get('event_name','','string');
		$event_desc       = $this->input->get('event_desc','','string');
		$location         = $this->input->get('location','','string');
		$ticket_price     = $this->input->get('ticket_price');
		$currency_code    = $this->input->get('currency_code','','string');
		$total_ticket     = $this->input->get('total_ticket');
		$available_ticket = $this->input->get('available_ticket');
		$event_date       = $this->input->get('event_date');
		$event_time       = $this->input->get('event_time','','string');
		$city             = $this->input->get('city');
		$event_id         = $this->input->get('event_id',0,'int');*/

		// Get the model.
		$model = $this->getModel();

		$data = array();
		$data['event_name']       = $form_data['event_name'];
		$data['event_id']         = (empty($form_data['event_id'])) ? '0' : $form_data['event_id'];

		if(empty($data['event_id']))
		{
			$data['event_id']  = 0;
		}
		$data['event_desc']       = $form_data['event_desc'];
		$data['location']         = $form_data['location'];
		$data['city']             = $form_data['city'];
		$data['event_date']       = $form_data['event_date'];
		$data['event_time']       = $form_data['event_time'];
		$data['price_per_ticket'] = $form_data['ticket_price'];
		$data['currency_code']    = $form_data['currency_code'];
		$data['total_ticket']     = $form_data['total_ticket'];
		$data['available_ticket'] = $form_data['available_ticket'];
		$data['latitude']         = $form_data['latitude'];
		$data['longitude']        = $form_data['longitude'];
		$data['is_deleted']       = 1;
		$data['time_stamp']       = time();

		$result = $this->save_ajax($data,$file,$data['event_id']);

		echo $result;
		exit;

		//$this->setRedirect('index.php?option=com_beseated&view=events');
	}

	public function save_ajax($data,$file,$event_id)
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$table = JTable::getInstance('Event', 'BeseatedTable');

		//$table    = $this->getTable();

		$table->load($event_id);

		if(isset($data['currency_code']) && !empty($data['currency_code']))
		{
			if($data['currency_code'] == 'AED'){ $data['currency_sign'] = 'AED'; }
			else if($data['currency_code'] == 'USD' || $data['currency_code'] == 'CAD' || $data['currency_code'] == 'AUD'){ $data['currency_sign'] = '$'; }
			else if($data['currency_code'] == 'EUR'){ $data['currency_sign'] = '€'; }
			else if($data['currency_code'] == 'GBP'){ $data['currency_sign'] = '£'; }
		}

		//$data['available_ticket'] = $data['available_ticket'] ? $data['available_ticket'] : $data['total_ticket'];

		$table->bind($data);
		if(!$table->store()){
			return 0;
		}

		$event_id = $table->event_id;

		if($file)
		{
			$tmp_name  = $file['tmp_name'];
			$imageName = $file['name'];
			$imageName = str_replace(' ', '', $imageName);
			$imageName = JFile::makeSafe($imageName);
			$imgnm     = JFile::stripExt($imageName);
			$ext       = JFile::getExt($imageName);

			$allowedExts = array("gif", "jpeg", "jpg", "png");
			$extension   = end(explode(".", $file["name"]));

			if(!empty($imageName))
			{
				//$table    = $this->getTable();
				$table->load($event_id);

				if ((($file["type"] == "image/gif") || ($file["type"] == "image/jpeg") || ($file["type"] == "image/jpg") || ($file["type"] == "image/png")) && in_array($extension, $allowedExts))
				{
					if(!JFolder::exists(JPATH_ROOT . "images/beseated/"))
					{
						JFolder::create(JPATH_ROOT . "images/beseated/");
					}
					if(!JFolder::exists(JPATH_ROOT . "images/beseated/Event/"))
					{
						JFolder::create(JPATH_ROOT . "images/beseated/Event/");
					}
					if(!JFolder::exists(JPATH_ROOT . "images/beseated/Event/".$event_id."/"))
					{
						JFolder::create(JPATH_ROOT . "images/beseated/Event/".$event_id."/");
					}

					$img  = $imgnm . "_" . rand(0, 99999) . "." . $ext;
					$dest = "../images/beseated/Event/$event_id/$img";
					$path = "Event/$event_id/$img";

					if(!JFile::upload($tmp_name, $dest))
					{
					}
					$table->image       = $path;
					$table->thumb_image = $path;
					$table->store();
				}
			}
		}


		return $event_id;
	}

	public function upload_images()
	{
		$app    = JFactory::getApplication();
		$input    = JFactory::getApplication()->input;

		//echo "<pre>";print_r($input);echo "<pre/>";exit();
		
		$imageIDs = array();

		$elementID = $input->get('unique_code');
		$event_id = $input->get('event_id',0,'int');

		//$event_id = $elementID ;

		if(isset($_FILES["Filedata"]))
		{
			$ret = array();

		  	$fileCount = count($_FILES["Filedata"]["name"]);

			for($i=0; $i < $fileCount; $i++)
			{
				$totalTicket = $this->getTotalTickets($elementID);

				/*if(count($totalTicket) >= 30)
				{
					$mainframe  = JFactory::getApplication();
					$mainframe->enqueueMessage(JText::_('You have reached maximum number of limit for image uploads'),'error');
					$this->setRedirect('index.php?option=com_beseated&view=event&layout=edit&unique_code='.$elementID.'&event_id='.$event_id);
					return false;
				}*/

			  	$tmpName           = $_FILES["Filedata"]["tmp_name"][$i];
			  	$filename          = JApplication::getHash($_FILES["Filedata"]['tmp_name'][$i] . time() );
				$hashFileName      = JString::substr( $filename , 0 , 24 );
				$info['extension'] = pathinfo($_FILES["Filedata"]["name"][$i],PATHINFO_EXTENSION);
				$info['extension'] = '.'.$info['extension'];

				if(!JFolder::exists(JPATH_ROOT . "/images/beseated/"))
				{
					JFolder::create(JPATH_ROOT . "/images/beseated/");
				}

				if(!JFolder::exists(JPATH_ROOT . "/images/beseated/Ticket/"))
				{
					JFolder::create(JPATH_ROOT . "/images/beseated/Ticket/");
				}

				if(!JFolder::exists(JPATH_ROOT . "/images/beseated/Ticket/". $elementID . '/'))
				{
					JFolder::create(JPATH_ROOT . "/images/beseated/Ticket/". $elementID . '/');
				}

				$storage      = JPATH_ROOT . '/images/beseated/Ticket/'. $elementID . '/';
				$storageImage = $storage . '/' . $hashFileName .  $info['extension'] ;
				$uploadedImage   = 'Ticket/' .$elementID .'/'. $hashFileName . $info['extension'] ;

				if(strtolower($info['extension']) == '.jpeg'|| strtolower($info['extension']) == '.jpg'|| strtolower($info['extension']) == '.png')
				{

					if(JFile::upload($tmpName, $storageImage))
					{
						JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
			            $tblImages = JTable::getInstance('Images', 'BeseatedTable');

			            $tblImages->load(0);

						$data['element_id']   = $elementID;
						$data['token_id']     = $elementID;
						$data['element_type'] = 'Event';
						$data['thumb_image']  = $uploadedImage;
						$data['image']        = $uploadedImage;
						$data['is_video']     = '0';
						$data['is_default']   = '0';
						$data['file_type']    = str_replace('.', '', $info['extension']);
						$data['time_stamp']   = time();

						$tblImages->bind($data);
						$tblImages->store();

						$imageIDs[] = $tblImages->image_id;

					}
				}

			}

			$imageIDs = json_encode($imageIDs);
			setcookie('imageID', $imageIDs);


			$this->setRedirect('index.php?option=com_beseated&view=event&layout=edit&unique_code='.$elementID.'&event_id='.$event_id);
		}
	}

	public function uploadFile($file,$elementID = 0)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.utilities.utility');

		$filename          = JApplication::getHash( $file['tmp_name'] . time() );
		$hashFileName      = JString::substr( $filename , 0 , 24 );
		$info['extension'] = pathinfo($file['name'],PATHINFO_EXTENSION);
		$info['extension'] = '.'.$info['extension'];

		if(!JFolder::exists(JPATH_ROOT . "images/beseated/"))
		{
			JFolder::create(JPATH_ROOT . "images/beseated/");
		}

		if(!JFolder::exists(JPATH_ROOT . "images/beseated/Ticket/"))
		{
			JFolder::create(JPATH_ROOT . "images/beseated/Ticket/");
		}

		if(!JFolder::exists(JPATH_ROOT . "images/beseated/Ticket/". $elementID . '/'))
		{
			JFolder::create(JPATH_ROOT . "images/beseated/Ticket/". $elementID . '/');
		}

		$storage      = JPATH_ROOT . '/images/beseated/Ticket/'. $elementID . '/';
		$storageImage = $storage . '/' . $hashFileName .  $info['extension'] ;
		$uploadedImage   = 'Ticket/' .$elementID .'/'. $hashFileName . $info['extension'] ;

		if(!JFile::upload($file['tmp_name'], $storageImage))
		{
			return '';
		}

		return $uploadedImage;
	}

	public function getTotalTickets($elementID)
	{
			// Initialiase variables.
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('image_id')
				->from($db->quoteName('#__beseated_element_images'))
				->where($db->quoteName('token_id') . ' = ' . $db->quote($elementID))
				->where($db->quoteName('element_type') . ' = ' . $db->quote('Event'));

			// Set the query and load the result.
			$db->setQuery($query);
			$totalTicket = $db->loadColumn();

			return $totalTicket;
	}

	public function cancel()
	{
		//echo "<pre>";print_r($_COOKIE);echo "<pre/>";exit();

		$input    = JFactory::getApplication()->input;

		$ticket_types = json_decode($_COOKIE['tickettypezone']);

		//echo "<pre>";print_r($ticket_types);echo "<pre/>";exit();

		$imageIDs = array();

		$form =  $input->get('jform',array(),'array');

		$event_id = $input->get('event_id');
		$unique_code = $input->get('unique_code');

		//echo "<pre/>";print_r($input);exit;

	    if(isset($event_id) && !empty($event_id) && empty($unique_code) && $_COOKIE['isNew'] == '0')
		{

			//echo "<pre/>";print_r("hi");exit;
			JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
			$tableEvent = JTable::getInstance('Event', 'BeseatedTable');
			$tableEvent->load($event_id);

			$event_date = date('y-m-d',strtotime($_COOKIE['event_date']));

			$tableEvent->event_name         =$_COOKIE['event_name'];
			$tableEvent->event_desc         =$_COOKIE['event_desc'];
			$tableEvent->location           =$_COOKIE['location'];
			$tableEvent->currency_code      =$_COOKIE['currency_code'];
			$tableEvent->total_ticket       =$_COOKIE['total_ticket'];
			//$tableEvent->available_ticket =$_COOKIE['available_ticket'];
			$tableEvent->price_per_ticket   =$_COOKIE['ticket_price'];
			$tableEvent->event_date         =$event_date ;
			$tableEvent->event_time         =$_COOKIE['event_time'];
			$tableEvent->city               =$_COOKIE['city'];
			$tableEvent->image              =$_COOKIE['eventImage'];
			$tableEvent->latitude           =$_COOKIE['latitude'];
			$tableEvent->longitude          =$_COOKIE['longitude'];
			$tableEvent->is_deleted         = 0;

			if(isset($_COOKIE['currency_code']) && !empty($_COOKIE['currency_code']))
			{
				if($_COOKIE['currency_code'] == 'AED'){ $_COOKIE['currency_sign'] = 'AED'; }
				else if($_COOKIE['currency_code'] == 'USD' || $_COOKIE['currency_code'] == 'CAD' || $_COOKIE['currency_code'] == 'AUD'){ $_COOKIE['currency_sign'] = '$'; }
				else if($_COOKIE['currency_code'] == 'EUR'){ $_COOKIE['currency_sign'] = '€'; }
				else if($_COOKIE['currency_code'] == 'GBP'){ $_COOKIE['currency_sign'] = '£'; }
			}

			$tableEvent->currency_sign             =$_COOKIE['currency_sign'];

			$tableEvent->store();

			foreach ($ticket_types as $key => $ticket) 
			{
				$tableTicketTypeZone = JTable::getInstance('Eventtickettypezone', 'BeseatedTable');

				$tableTicketTypeZone->load($ticket->ticket_type_zone_id);
				$tableTicketTypeZone->ticket_type  = $ticket->ticket_type;
				$tableTicketTypeZone->ticket_zone  = $ticket->ticket_zone;
				$tableTicketTypeZone->ticket_price = $ticket->ticket_price;
				$tableTicketTypeZone->is_deleted   = 0;
				$tableTicketTypeZone->store();
			}
		}

		$deleteCookie = $this->unset_cookie('event_name');
		$deleteCookie = $this->unset_cookie('event_desc');
		$deleteCookie = $this->unset_cookie('location');
		$deleteCookie = $this->unset_cookie('currency_code');
		$deleteCookie = $this->unset_cookie('total_ticket');
		$deleteCookie = $this->unset_cookie('available_ticket');
		$deleteCookie = $this->unset_cookie('ticket_price');
		$deleteCookie = $this->unset_cookie('event_date');
		$deleteCookie = $this->unset_cookie('event_time');
		$deleteCookie = $this->unset_cookie('city');
		$deleteCookie = $this->unset_cookie('currency_sign');
		$deleteCookie = $this->unset_cookie('event_id');
		$deleteCookie = $this->unset_cookie('page_load');
		$deleteCookie = $this->unset_cookie('latitude');
		$deleteCookie = $this->unset_cookie('longitude');
		$deleteCookie = $this->unset_cookie('tickettypezone');

		if (!empty($event_id) && $_COOKIE['isNew'] == '1')
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_event'))
				->set($db->quoteName('is_deleted') . ' = ' . $db->quote('1'))
				->where($db->quoteName('event_id') . ' = ' . $db->quote($event_id));

			// Set the query and execute the update.
			$db->setQuery($query);

			$db->execute();
		}
		else if (!empty($form['event_id']) && $_COOKIE['isNew'] == '1')
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_event'))
				->set($db->quoteName('is_deleted') . ' = ' . $db->quote('1'))
				->where($db->quoteName('event_id') . ' = ' . $db->quote($form['event_id']));

			// Set the query and execute the update.
			$db->setQuery($query);

			$db->execute();
		}

	/*	if(isset($unique_code) && !empty($unique_code) && strlen($unique_code) < 8)
		{
			echo "<pre/>";print_r("hi");exit;
			// Initialiase variables.
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_event'))
				->set($db->quoteName('is_deleted') . ' = ' . $db->quote('1'))
				->where($db->quoteName('event_id') . ' = ' . $db->quote($unique_code));

			// Set the query and execute the update.
			$db->setQuery($query);

			$db->execute();

		}
		else if (!empty($form['event_id']) && strlen($unique_code) == 8)
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_event'))
				->set($db->quoteName('is_deleted') . ' = ' . $db->quote('1'))
				->where($db->quoteName('event_id') . ' = ' . $db->quote($form['event_id']));

			// Set the query and execute the update.
			$db->setQuery($query);

			$db->execute();
		}
*/
	   //echo "<pre>";print_r($_COOKIE);exit;
		if(isset($_COOKIE['imageID']))
		{
			$imageIDs = json_decode($_COOKIE['imageID']);

			$imageIDs = implode(',', $imageIDs);

			// Initialiase variables.
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base delete statement.
			$query->delete()
				->from($db->quoteName('#__beseated_element_images'))
				->where('image_id IN (' . $imageIDs . ')');

			// Set the query and execute the delete.
			$db->setQuery($query);

			try
			{
				$db->execute();
				$deleteCookie = $this->unset_cookie('imageID');


				//echo "<pre/>";print_r($_COOKIE);exit;

				$this->setRedirect('index.php?option=com_beseated&view=events');
				return true;
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}
		else
		{
			$this->setRedirect('index.php?option=com_beseated&view=events');
			return true;
		}
	}

	public function unset_cookie($cookie_name)
	{
	    if (isset($_COOKIE[$cookie_name]))
	    {
	        unset($_COOKIE[$cookie_name]);
	        setcookie($cookie_name, null, -1);
	    }
	    else
	    {
	    	return false;
	    }
	}


	public function uploadEventImage()
	{
		$event_id = $_GET['event_id'];

		$file = $this->input->files->get('file','');

		$tmp_name  = $file['tmp_name'];
		$imageName = $file['name'];
		$imageName = str_replace(' ', '', $imageName);
		$imageName = JFile::makeSafe($imageName);
		$imgnm     = JFile::stripExt($imageName);
		$ext       = JFile::getExt($imageName);

		$allowedExts = array("gif", "jpeg", "jpg", "png");
		$extension = end(explode(".", $file["name"]));

		if(!empty($imageName))
		{

			if ((($file["type"] == "image/gif") || ($file["type"] == "image/jpeg") || ($file["type"] == "image/jpg") || ($file["type"] == "image/png")) && in_array($extension, $allowedExts))
			{
				if(!JFolder::exists(JPATH_ROOT . "images/beseated/"))
				{
					JFolder::create(JPATH_ROOT . "images/beseated/");
				}
				if(!JFolder::exists(JPATH_ROOT . "images/beseated/Event/"))
				{
					JFolder::create(JPATH_ROOT . "images/beseated/Event/");
				}
				if(!JFolder::exists(JPATH_ROOT . "images/beseated/Event/".$event_id."/"))
				{
					JFolder::create(JPATH_ROOT . "images/beseated/Event/".$event_id."/");
				}

				$img = $imgnm . "_" . rand(0, 99999) . "." . $ext;
				$dest = "../images/beseated/Event/$event_id/$img";
				$path = "Event/$event_id/$img";

				//$data['image'] = $path;

				if(!JFile::upload($tmp_name, $dest))
				{
					$this->setError('error in file uploading');
					echo 2;
					exit();
				}

				JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
				$tableEvent = JTable::getInstance('Event', 'BeseatedTable');
				$tableEvent->load($event_id);
				$tableEvent->image       =$path;
				$tableEvent->store();

				echo 1;
				exit();

			}
			else
			{
				$this->setError('invalid type of image');
				echo 3;
				exit();
			}
		}
	}

	public function saveTicketTypeZone()
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		
		$input    = JFactory::getApplication()->input;
		$event_id = $input->get('event_id',0,'int');

		$updatedTicketIDs = array();

		$ticket_types = $input->get('ticket_types',array(),'array');

		foreach ($ticket_types as $key => $ticket) 
		{
			$tableTicketTypeZone = JTable::getInstance('Eventtickettypezone', 'BeseatedTable');

			if($ticket['ticket_type_zone_id'])
			{
				$tableTicketTypeZone->load($ticket['ticket_type_zone_id']);
			}
			else
			{
				$tableTicketTypeZone->load(0);
			}

			$tableTicketTypeZone->event_id     = $event_id;
			$tableTicketTypeZone->ticket_type  = $ticket['ticket_type'];
			$tableTicketTypeZone->ticket_zone  = $ticket['ticket_zone'];
			$tableTicketTypeZone->ticket_price = $ticket['ticket_price'];
			$tableTicketTypeZone->store();

			$updatedTicketIDs[] = $tableTicketTypeZone->ticket_type_zone_id; 
		}

		$updatedTicketIDs = implode(',', $updatedTicketIDs);


		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_event_ticket_type_zone'))
			->set($db->quoteName('is_deleted') . ' = ' . $db->quote('1'))
			->where($db->quoteName('ticket_type_zone_id') . ' NOT IN ('.$updatedTicketIDs.')');
		
		// Set the query and execute the update.
		$db->setQuery($query);
		
		$db->execute();
		
	}


}
