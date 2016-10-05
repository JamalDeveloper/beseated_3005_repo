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

/**
 * Beseated Event Model
 *
 * @since  0.0.1
 */
class BeseatedModelTicketBooking extends JModelAdmin
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
	public function getTable($type = 'TicketBooking', $prefix = 'BeseatedTable', $config = array())
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
			'com_beseated.ticketbooking',
			'ticketbooking',
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
			'com_beseated.edit.ticketbooking.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/*public function save($data)
	{
		$dispatcher = JEventDispatcher::getInstance();

		$data['available_ticket'] = ($data['available_ticket']) ? $data['available_ticket'] : $data['total_ticket'];
		$isNew = true;

		$table = $this->getTable();

		$input = JFactory::getApplication()->input;
		$ticket_id = $input->getInt('ticket_id');

		if ($ticket_id > 0)
		{
			$table->load($ticket_id);
			$isNew = false;

		}

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));

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

		// Trigger the onContentAfterSave event.
		//$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));

		return true;

	}*/

	public function getUserTicketDetail()
	{
		$input = JFactory::getApplication()->input;

		$user_id           = $input->getInt('user_id');
		$event_id          = $input->getInt('event_id');
		$ticket_booking_id = $input->getInt('ticket_booking_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.image,c.full_name as bookingOwner,d.event_name,d.event_date,b.created as purchaseDate,b.user_id as inviteeUserID,b.ticket_booking_id')
			->from($db->quoteName('#__beseated_element_images').'AS a')
			->where($db->quoteName('a.element_id') . ' = ' . $db->quote($event_id))
			->where($db->quoteName('a.element_type') . ' = ' . $db->quote('Event'))
			->where($db->quoteName('b.ticket_booking_id') . ' = ' . $db->quote($ticket_booking_id))
			->where($db->quoteName('b.booking_user_id') . ' = ' . $db->quote($user_id))
			->join('INNER', '#__beseated_event_ticket_booking_detail AS b ON b.ticket_id=a.image_id')
			->join('INNER', '#__beseated_user_profile AS c ON c.user_id=b.booking_user_id')
			->join('INNER', '#__beseated_event AS d ON d.event_id=a.element_id')
			->order($db->quoteName('a.image_id') . ' ASC');

			//echo $query;exit;
		// Set the query and load the result.
		$db->setQuery($query);
		$ticketDetails = $db->loadObjectList();

		return $ticketDetails;
	}

	function getInviteeDetail($ticket_booking_id = null)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('email')
			->from($db->quoteName('#__beseated_event_ticket_booking_invite'))
			->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($ticket_booking_id));

		// Set the query and load the result.
		$db->setQuery($query);
		$inviteeEmail = $db->loadResult();

		return $inviteeEmail;


	}
}
