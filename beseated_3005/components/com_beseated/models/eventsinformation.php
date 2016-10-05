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
 * The Beseated Club Information Model
 *
 * @since  0.0.1
 */
class BeseatedModelEventsInformation extends JModelList
{
	public $db = null;
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelList
	 * @since   0.0.1
	 */
	public function __construct($config = array())
	{
		$this->db = JFactory::getDbo();
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array();
		}

		parent::__construct($config);
	}

	public function getEventDetail()
	{
		$app        = JFactory::getApplication();
		$input      = $app->input;
		$serachType = '';
		$eventID    = $input->get('event_id',0,'int');
		$query      = $this->db->getQuery(true);


		// Set the query and load the result.
		$query->select('a.*')
			->from($this->db->quoteName('#__beseated_event','a'))
			->where($this->db->quoteName('a.published') . ' =  ' .  $this->db->quote(1))
			->where($this->db->quoteName('a.event_id') . ' =  ' .  $this->db->quote($eventID));

		$this->db->setQuery($query);
		$result = $this->db->loadObject();

		return $result;
	}

	public function getEventsImages()
	{
		$app        = JFactory::getApplication();
		$input      = $app->input;
		$serachType = '';
		$eventID    = $input->get('event_id',0,'int');
		$query      = $this->db->getQuery(true);


		// Set the query and load the result.
		$query->select('i.*')
			->from($this->db->quoteName('#__beseated_element_images','i'))
			->where($this->db->quoteName('i.element_id') . ' =  ' .  $this->db->quote($eventID))
			->where($this->db->quoteName('i.element_type') . ' =  ' .  $this->db->quote('Event'))
			->order('i.is_default DESC');

		$this->db->setQuery($query);
		$imageResult = $this->db->loadObjectList();

		return $imageResult;
	}

	public function bookEventTicket($data)
	{
		$tblTicketBooking = JTable::getInstance('TicketBooking', 'BeseatedTable');
		$tblTicketBooking->load(0);

		$tblTicketBooking->bind($data);

		if(!$tblTicketBooking->store())
		{
			return false;
		}
		return $tblTicketBooking->ticket_booking_id;

	}

	function getTicketTypeDetail()
	{
		$app          = JFactory::getApplication();
		$ticket_type_zone_id      = $app->input->getInt('ticket_type_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		// Create the base select statement.
		$query->select('a.*,e.image as event_image,e.event_name,e.currency_sign,e.currency_code')
			->from($db->quoteName('#__beseated_event_ticket_type_zone').' AS a')
			->where($db->quoteName('a.ticket_type_zone_id') . ' = ' . $db->quote($ticket_type_zone_id))
			->where($db->quoteName('a.is_deleted') . ' = ' . $db->quote('0'))
			->join('LEFT','#__beseated_event AS e ON e.event_id=a.event_id')
			->order($db->quoteName('a.available_tickets') . ' DESC');
		
		// Set the query and load the result.
		$db->setQuery($query);
		
		$ticketTypeDetail = $db->loadObject();

		$ticketTypeDetail->image           = ($ticketTypeDetail->ticket_type_image)?JUri::root().'images/beseated/'.$ticketTypeDetail->ticket_type_image:JUri::root().'images/beseated/'.$ticketTypeDetail->event_image;
			
		return $ticketTypeDetail;

	}

}
