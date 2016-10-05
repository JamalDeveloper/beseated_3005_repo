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

/**
 * Beseated Event Model
 *
 * @since  0.0.1
 */
class BeseatedModelEvent extends JModelAdmin
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Event', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   0.0.1
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_beseated.event',
			'event',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   0.0.1
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState(
			'com_beseated.edit.Event.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		//echo "<pre/>";print_r($data);exit;

		$input = JFactory::getApplication()->input;
		$event_id = $input->get('event_id');
        $elementID = ($input->get('event_id')) ? $input->get('event_id') : $input->get('unique_code');

        if($elementID)
        {
        	// Initialiase variables.
			$db    = JFactory::getDbo();

			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($db->quoteName('#__beseated_element_images'))
				->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
				->where($db->quoteName('element_type') . ' = ' . $db->quote('Event'));

			// Set the query and load the result.
			$db->setQuery($query);
			$elemt_image_details = $db->loadObjectList();

			$data->total_ticket = count($elemt_image_details);
			$data->available_ticket = $data->total_ticket;

        }

        if($event_id)
        {
        	// Initialiase variables.
        	$db    = JFactory::getDbo();

        	// $db    = $this->getDbo();
        	$query = $db->getQuery(true);

        	// Create the base select statement.
        	$query->select('available_ticket,total_ticket')
        		->from($db->quoteName('#__beseated_event'))
        		->where($db->quoteName('event_id') . ' = ' . $db->quote($event_id));

        	// Set the query and load the result.
        	$db->setQuery($query);
        	$old_ticket_details = $db->loadObject();

        	if($old_ticket_details->total_ticket == $data->total_ticket)
	        {
	        	$data->total_ticket     = $old_ticket_details->total_ticket;
	        	$data->available_ticket = $old_ticket_details->available_ticket;
	        }
	        else
	        {
	        	$new_ticket              = $data->total_ticket - $old_ticket_details->total_ticket;
	        	$data->total_ticket      = $new_ticket + $old_ticket_details->total_ticket;
	        	$data->available_ticket  = $old_ticket_details->available_ticket + $new_ticket ;
	        }

        }

		$data->time_stamp = time();
		return $data;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $ids      Ids to delete
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   0.0.1
	 */
	/*public function delete(&$ids)
	{
		if(count($ids) == 0)
		{
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_event'))
			->set($db->quoteName('published') . ' = ' . $db->quote('0'))
			->where($db->quoteName('event_id') . ' IN (' . implode(',', $ids).')');

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
	}*/

	public function deleteEvent(&$ids)
	{
		if(count($ids) == 0)
		{
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_event'))
			->set($db->quoteName('is_deleted') . ' = ' . $db->quote('1'))
			->set($db->quoteName('published') . ' = ' . $db->quote('0'))
			->where($db->quoteName('event_id') . ' IN (' . implode(',', $ids).')');

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
	}


	public function save($data)
	{
		$input    = JFactory::getApplication()->input;

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

		$cityName = BeseatedHelper::getAddressFromLatlong($data['latitude'],$data['longitude']);

		if(empty($data['city']))
		{
			$data['city'] = $cityName;
		}

		$table    = $this->getTable();
		$table->load($data['event_id']);

		$deleteCookie = $this->unset_cookie('event_name');
		$deleteCookie = $this->unset_cookie('event_desc');
		$deleteCookie = $this->unset_cookie('location');
		$deleteCookie = $this->unset_cookie('currency_code');
		$deleteCookie = $this->unset_cookie('total_ticket');
		//$deleteCookie = $this->unset_cookie('available_ticket');
		$deleteCookie = $this->unset_cookie('ticket_price');
		$deleteCookie = $this->unset_cookie('event_date');
		$deleteCookie = $this->unset_cookie('event_time');
		$deleteCookie = $this->unset_cookie('city');
		$deleteCookie = $this->unset_cookie('currency_sign');
		$deleteCookie = $this->unset_cookie('event_id');
		$deleteCookie = $this->unset_cookie('page_load');
		$deleteCookie = $this->unset_cookie('tickettypezone');

		$event_date = date('Y-m-d',strtotime($data['event_date']));
		$data['event_date'] = $event_date;

		/*$app = JFactory::getApplication();
		echo "<pre>";
		print_r($app);
		echo "</pre>";
		exit;axet6_beseated_event_ticket_booking_detail*/

		$deleteCookie = $this->unset_cookie('imageID');

		$dispatcher = JEventDispatcher::getInstance();

		if(isset($data['currency_code']) && !empty($data['currency_code']))
		{
			if($data['currency_code'] == 'AED'){ $data['currency_sign'] = 'AED'; }
			else if($data['currency_code'] == 'USD' || $data['currency_code'] == 'CAD' || $data['currency_code'] == 'AUD'){ $data['currency_sign'] = '$'; }
			else if($data['currency_code'] == 'EUR'){ $data['currency_sign'] = '€'; }
			else if($data['currency_code'] == 'GBP'){ $data['currency_sign'] = '£'; }
		}

		if($table->has_ticket_booked)
		{
			$data['available_ticket'] = $data['available_ticket'];
		}
		else
		{
			$data['available_ticket'] = $data['available_ticket'] ? $data['available_ticket'] : $data['total_ticket'];
		}
		//$data['available_ticket'] = $data['available_ticket'] ? $data['available_ticket'] : $data['total_ticket'];

		$isNew = true;

		$table    = $this->getTable();
		$input    = JFactory::getApplication()->input;
		$event_id = $input->getInt('event_id');
		$file     = $input->files->get('jform', '', 'array');

		if ($event_id > 0)
		{
			$isNew = false;
		}

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));

		if(isset($data['event_id']) && $data['event_id'])
		{
			$data['published'] = 1;
			$data['is_deleted'] = 0;
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		$this->updateTicketsTypeZoneAfterSave($input,$event_id);

		// for upload image for event

		$tmp_name  = $file['image']['tmp_name'];
		$imageName = $file['image']['name'];
		$imageName = str_replace(' ', '', $imageName);
		$imageName = JFile::makeSafe($imageName);
		$imgnm     = JFile::stripExt($imageName);
		$ext       = JFile::getExt($imageName);

		$allowedExts = array("gif", "jpeg", "jpg", "png");
		$extension = end(explode(".", $file['image']["name"]));

		if(!empty($imageName))
		{
			$event_id = $table->event_id;

			if ((($file['image']["type"] == "image/gif") || ($file['image']["type"] == "image/jpeg") || ($file['image']["type"] == "image/jpg") || ($file['image']["type"] == "image/png")) && in_array($extension, $allowedExts))
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
					return false;
				}

				$table->load($event_id);

				$data1['image'] = $path;

				$table->bind($data1);
				$table->store();

				
			}
			else
			{
				$this->setError('invalid type of image');
				return false;
			}
		}

		$elementID = $input->get('unique_code');

		if(!empty($elementID))
		{
			$oldPath = JPATH_ROOT . "/images/beseated/Ticket/". $elementID;
			$newPath = JPATH_ROOT . "/images/beseated/Ticket/". $table->event_id;

			if(rename($oldPath, $newPath))
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();

				// $db    = $this->getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('*')
					->from($db->quoteName('#__beseated_element_images'))
					->where($db->quoteName('token_id') . ' = ' . $db->quote($elementID));

				// Set the query and load the result.
				$db->setQuery($query);
				$elemt_details = $db->loadObjectList();

				foreach ($elemt_details as $key => $detail)
				{
					//echo "<pre/>";print_r($detail);exit;
					$imagepath = str_replace($elementID, $table->event_id, $detail->image);

					//echo "<pre/>";print_r($imagepath);

					// Initialiase variables.
					$db    = JFactory::getDbo();

					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__beseated_element_images'))
						->set($db->quoteName('element_id') . ' = ' . $db->quote($table->event_id))
						->set($db->quoteName('image') . ' = ' . $db->quote($imagepath))
						//->set($db->quoteName('token_id') . ' = ' . $db->quote($elementID))
						->set($db->quoteName('thumb_image') . ' = ' . $db->quote($imagepath))
						->where($db->quoteName('token_id') . ' = ' . $db->quote($elementID))
						->where($db->quoteName('image_id') . ' = ' . $db->quote($detail->image_id));

					// Set the query and execute the update.
					$db->setQuery($query);

					$db->execute();

				}//exit;
			}

		}


		return true;
	}

	public function getEventTickets()
	{
		$input = JFactory::getApplication()->input;
		$event_id = $input->get('event_id');
        $elementID = ($input->get('event_id')) ? $input->get('event_id') : $input->get('unique_code');

        if($elementID)
        {
        	// Initialiase variables.
			$db    = JFactory::getDbo();

			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('a.*,b.ticket_booking_id')
				->from($db->quoteName('#__beseated_element_images').'AS a')
				->where($db->quoteName('a.element_id') . ' = ' . $db->quote($elementID))
				->where($db->quoteName('a.element_type') . ' = ' . $db->quote('Event'))
				->join('LEFT', '#__beseated_event_ticket_booking_detail AS b ON b.ticket_id=a.image_id');

			// Set the query and load the result.
			$db->setQuery($query);
			$elemt_image_details = $db->loadObjectList();

			return $elemt_image_details;

		}

		return array();
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

	public function getTicketsTypeZone()
	{

		$input    = JFactory::getApplication()->input;
		$event_id = $input->get('event_id',0,'int');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_event_ticket_type_zone'))
			->where($db->quoteName('event_id') . ' = ' . $db->quote($event_id))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'));
		
		// Set the query and load the result.
		$db->setQuery($query);
		
		$ticketsTypeZone = $db->loadObjectList();

		return $ticketsTypeZone;
		
	}

	public function updateTicketsTypeZoneAfterSave($input,$event_id)
	{
		//echo "<pre>";print_r($input);echo "<pre/>";exit();

		$ticket_type = $input->get('ticket_type',array(),'array');
		$ticket_zone = $input->get('ticket_zone',array(),'array');
		$ticket_price = $input->get('ticket_price',array(),'array');
		$ticket_type_zone_id = $input->get('ticket_type_zone_id',array(),'array');
		$updatedTicketIDs = array();

		for ($i=0; $i < count($ticket_price); $i++) 
		{ 
			$tableTicketTypeZone = JTable::getInstance('Eventtickettypezone', 'BeseatedTable');

			if($ticket_type_zone_id[$i])
			{
				$tableTicketTypeZone->load($ticket_type_zone_id[$i]);
			}
			else
			{
				$tableTicketTypeZone->load(0);
			}

			$tableTicketTypeZone->event_id     = $event_id;
			$tableTicketTypeZone->ticket_type  = $ticket_type[$i];
			$tableTicketTypeZone->ticket_zone  = $ticket_zone[$i];
			$tableTicketTypeZone->ticket_price = $ticket_price[$i];
			$tableTicketTypeZone->store();

			$updatedTicketIDs[] = $tableTicketTypeZone->ticket_type_zone_id; 
			
		}

		$updatedTicketIDs = implode(',', $updatedTicketIDs);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		// Create the base delete statement.
		$query->delete()
			->from($db->quoteName('#__beseated_event_ticket_type_zone'))
			->where($db->quoteName('event_id') . ' = ' . $db->quote((int) $event_id))
			->where($db->quoteName('ticket_type_zone_id') . ' NOT IN ('.$updatedTicketIDs.')');
		
		// Set the query and execute the delete.
		$db->setQuery($query);
		
		$db->execute();
			
	}


}
