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
 * The Beseated Club Booking Detail Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubBookingDetail extends JModelList
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

	public function getClubBooking()
	{
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$bookingID = $input->get('booking_id',0,'int');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);


		$query->select('vb.*')
			->from($db->quoteName('#__beseated_venue_table_booking','vb'))
			->where($db->quoteName('vb.venue_table_booking_id') . ' = ' . $db->quote($bookingID));

		$query->select('vt.premium_table_id,vt.table_name,vt.image,vt.thumb_image,vt.min_price,vt.capacity')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

		$query->select('ps.created AS deposit_paid_on')
			->join('LEFT','#__beseated_payment_status AS ps ON ps.booking_id=vb.venue_table_booking_id AND ps.booking_type="venue" AND ps.paid_status=1');


		$query->select('bs.status_display AS status_text')
			->join('LEFT','#__beseated_status AS bs ON bs.status_id=vb.venue_status');

		$query->select('bus.status_display AS user_status_text')
			->join('LEFT','#__beseated_status AS bus ON bus.status_id=vb.user_status');

		$query->select('v.venue_name,v.location,v.description,v.currency_sign,v.currency_code,v.avg_ratting,v.working_days')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

		$query->select('img.thumb_image,img.image,is_default')
			->join('LEFT','#__beseated_element_images AS img ON img.element_id=vb.venue_id');

		$query->select('u.name')
			->join('LEFT','#__users AS u ON u.id=vb.user_id');

		$query->select('bu.full_name,bu.phone')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=vb.user_id');

		$query->order('vb.booking_date ASC, is_default DESC');

		$db->setQuery($query);

		$bookings = $db->loadObject();

		if(!$bookings)
		{
			return array();
		}

		return $bookings;
	}

	public function summaryForVenue($venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('count(status) as total_count,status')
			->from($db->quoteName('#__bcted_venue_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'))
			->group($db->quoteName('status'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	public function getRevenueForVenue($venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('sum(amount_payable) as revenue')
			->from($db->quoteName('#__bcted_venue_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadResult();

		return $result;
	}

	public function deleteBooking($bookingID)
	{
		$tblVenuebooking = JTable::getInstance('Venuebooking', 'BctedTable',array());
		$user            = JFactory::getUser();

		$tblVenuebooking->load($bookingID);
		if(!$tblVenuebooking->venue_booking_id)
		{
			return 400;
		}

		$status = BctedHelper::getStatusIDFromStatusName('Booked');
		if($status != $tblVenuebooking->status)
		{
			return 400;
		}

		$tblVenue = JTable::getInstance('Venue', 'BctedTable',array());
		$tblVenue->load($tblVenuebooking->venue_id);
		if($tblVenue->userid != $user->id)
		{
			return 706;
		}

		$tblVenuebooking->is_deleted = 1;

		if(!$tblVenuebooking->store())
		{
			return 500;
		}

		return 200;
	}
}
