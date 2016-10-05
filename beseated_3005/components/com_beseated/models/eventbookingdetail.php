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
 * The Beseated User Booking Detail Model
 *
 * @since  0.0.1
 */
class BeseatedModelEventBookingDetail extends JModelList
{
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
		parent::__construct($config);
	}

	function getEventBookingDetail()
	{
		$app            = JFactory::getApplication();
		$eventId        = $app->input->getInt('event_id',0);
		$eventBookingID = $app->input->getInt('ticket_booking_id',0);
		$user           = JFactory::getUser();

		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);
		$query->select('tb.*')
			->from($db->quoteName('#__beseated_event_ticket_booking','tb'))
			->where($db->quoteName('tb.user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('tb.ticket_booking_id') . ' = ' . $db->quote($eventBookingID));

		$query->select('e.event_name,e.event_desc,e.image AS event_image,e.thumb_image AS event_thumb_image,e.event_date,e.event_time,e.location,e.city')
			->join('LEFT','#__beseated_event AS e ON e.event_id=tb.event_id');
		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=tb.user_id');

		$query->order($db->quoteName('e.event_date') . ' ASC,'.$db->quoteName('e.event_time').' ASC');
		$db->setQuery($query);
		$resTicketBooking = $db->loadObject();

		$resTicketBooking->bookedType = 'booking';

		return $resTicketBooking;

		//echo "<pre/>";print_r($tickets);exit;

	}

	public function TicketBookingDetails($resTicketBooking)
	{
		$tickets = json_decode($resTicketBooking->tickets_id);
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);

		$query->select('ticket_id')
			->from($db->quoteName('#__beseated_event_ticket_booking_detail'))
			->where($db->quoteName('ticket_id') . ' IN ('.implode(",", $tickets).')')
			->where($db->quoteName('event_id') . ' = ' . $db->quote($resTicketBooking->event_id))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($resTicketBooking->user_id))
			->where($db->quoteName('booking_user_id') . ' = ' . $db->quote($resTicketBooking->user_id))
			->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($resTicketBooking->ticket_booking_id));

		$db->setQuery($query);
		$ticketIDs = $db->loadColumn();

		if (count($ticketIDs) > 0)
		{
			$db       = JFactory::getDbo();
			$queryimg = $db->getQuery(true);

			$queryimg->select('*')
				->from($db->quoteName('#__beseated_element_images'))
				->where($db->quoteName('image_id') . ' IN ('.implode(",", $ticketIDs).')');
			$db->setQuery($queryimg);
			$resTicketImgs = $db->loadObjectList();

			return $resTicketImgs;
		}
		else
		{
			return array();
		}
	}

	function getBookedEventInvitations()
	{
		$user = JFactory::getUser();
		
		$eventStatusArray[] = BeseatedHelper::getStatusID('accept');

		$db    = JFactory::getDbo();

		// Start of Get Event RSVP
		$eventInvitesql = $db->getQuery(true);
		$eventInvitesql->select('tbi.invite_id,tbi.ticket_booking_id,tbi.ticket_booking_detail_id,tbi.invited_user_status')
			->from($db->quoteName('#__beseated_event_ticket_booking_invite','tbi'))
			->where($db->quoteName('tbi.invited_user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('tbi.deleted_by_user') . ' = ' . $db->quote('0'))
		    ->where($db->quoteName('tbi.invited_user_status') . ' IN ('.implode(",", $eventStatusArray).')')
			->where($db->quoteName('tbi.user_id') . ' <> ' . $db->quote($user->id));

		$eventInvitesql->select('e.event_id,e.event_name,e.event_date,e.event_time,e.location')
			->join('INNER','#__beseated_event AS e ON e.event_id=tbi.event_id')
			//->where('STR_TO_DATE(CONCAT('.$this->db->quoteName('e.event_date').', " ", '.$this->db->quoteName('e.event_time').'),"%Y-%m-%d %H:%i:%s")' . ' >= ' . $this->db->quote(date('Y-m-d H:i:s')));
			->where('CONCAT('.$db->quoteName('e.event_date').', " ", '.$db->quoteName('e.event_time') . ') >= ' . $db->quote(date('Y-m-d H:i:s')));

		$eventInvitesql->select('etb.ticket_price,etb.booking_currency_sign,etb.total_price,etb.created')
			->join('INNER','#__beseated_event_ticket_booking AS etb ON etb.ticket_booking_id=tbi.ticket_booking_id');

		$eventInvitesql->select('bu.full_name,bu.thumb_avatar')
			->join('INNER','#__beseated_user_profile AS bu ON bu.user_id=tbi.user_id');

		$eventInvitesql->order('CONCAT('.$db->quoteName('e.event_date').', " ", '.$db->quoteName('e.event_time').')' . ' ASC');

		$db->setQuery($eventInvitesql);
		$resEventInvitations = $db->loadObject();

		$resEventInvitations->bookedType = 'invitation';
		

		return $resEventInvitations;
	}


	public function InvitationTicketBookingDetails()
	{
		$user = JFactory::getUser();
		$app            = JFactory::getApplication();
		$eventId        = $app->input->getInt('event_id');
		$eventBookingID = $app->input->getInt('ticket_booking_id');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblEventTicketBooking = JTable::getInstance('TicketBooking', 'BeseatedTable',array());
		$tblEventTicketBooking->load($eventBookingID);

		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);

		$query->select('ticket_id')
			->from($db->quoteName('#__beseated_event_ticket_booking_detail'))
			->where($db->quoteName('event_id') . ' = ' . $db->quote($eventId))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
			//->where($db->quoteName('booking_user_id') . ' = ' . $db->quote($tblEventTicketBooking->user_id))
			->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($eventBookingID));

		$db->setQuery($query);
		$ticketIDs = $db->loadColumn();


		if (count($ticketIDs) > 0)
		{
			$db       = JFactory::getDbo();
			$queryimg = $db->getQuery(true);

			$queryimg->select('*')
				->from($db->quoteName('#__beseated_element_images'))
				->where($db->quoteName('image_id') . ' IN ('.implode(",", $ticketIDs).')');
			$db->setQuery($queryimg);
			$resTicketImgs = $db->loadObjectList();

			return $resTicketImgs;
		}
		else
		{
			return array();
		}


	}


}
