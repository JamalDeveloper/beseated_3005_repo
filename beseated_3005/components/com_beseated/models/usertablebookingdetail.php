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
 * The Beseated User Table Booking Detail Model
 *
 * @since  0.0.1
 */
class BctedModelUserTableBookingDetail extends JModelList
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

	public function getCompanyBooking()
	{
		$app       = JFactory::getApplication();
		$bookingID = $app->input->get('booking_id',0,'int');
		$db        = JFactory::getDbo();
		$query     = $db->getQuery(true);

		$query->select('sb.*')
			->from($db->quoteName('#__bcted_service_booking','sb'))
			->where($db->quoteName('sb.service_booking_id') . ' = ' . $db->quote($bookingID));

		$query->select('cs.service_name,cs.service_image,cs.service_description,cs.service_price')
			->join('LEFT','#__bcted_company_services AS cs ON cs.service_id=sb.service_id');

		$query->select('bs.status AS status_text')
			->join('LEFT','#__bcted_status AS bs ON bs.id=sb.status');

		$query->select('bus.status AS user_status_text')
			->join('LEFT','#__bcted_status AS bus ON bus.id=sb.user_status');

		$query->select('c.commission_rate,c.company_name,c.company_image,c.company_address,c.company_about,c.currency_code,c.currency_sign')
			->join('LEFT','#__bcted_company AS c ON c.company_id=sb.company_id');

		$query->select('u.name')
			->join('LEFT','#__users AS u ON u.id=sb.user_id');

		$query->select('bu.last_name,bu.phoneno')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.userid=sb.user_id');

		$query->order($db->quoteName('sb.service_booking_datetime') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$bookings = $db->loadObject();

		if(!$bookings)
		{
			return array();
		}

		return $bookings;
	}

	public function getClubBooking()
	{
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$bookingID = $input->get('booking_id',0,'int');
		$db        = JFactory::getDbo();
		$query     = $db->getQuery(true);

		$query->select('vb.*')
			->from($db->quoteName('#__bcted_venue_booking','vb'))
			->where($db->quoteName('vb.venue_booking_id') . ' = ' . $db->quote($bookingID));

		$query->select('vt.premium_table_id,vt.venue_table_name,vt.custom_table_name,vt.venue_table_image,vt.venue_table_price,vt.venue_table_capacity,venue_table_description')
			->join('LEFT','#__bcted_venue_table AS vt ON vt.venue_table_id=vb.venue_table_id');

		$query->select('ps.created AS deposit_paid_on')
			->join('LEFT','#__bcted_payment_status AS ps ON ps.booked_element_id=vb.venue_booking_id AND ps.booked_element_type="venue" AND ps.paid_status=1');

		$query->select('bs.status AS status_text')
			->join('LEFT','#__bcted_status AS bs ON bs.id=vb.status');

		$query->select('bus.status AS user_status_text')
			->join('LEFT','#__bcted_status AS bus ON bus.id=vb.user_status');

		$query->select('v.commission_rate,v.venue_name,v.venue_address,v.currency_code,v.currency_sign,v.venue_about,v.currency_sign,v.currency_code,v.venue_amenities,v.venue_signs,v.venue_rating,v.venue_timings,v.venue_image,v.is_smart,v.is_casual,v.is_food,v.is_drink,v.working_days,v.is_smoking')
			->join('LEFT','#__bcted_venue AS v ON v.venue_id=vb.venue_id');

		$query->select('u.name')
			->join('LEFT','#__users AS u ON u.id=vb.user_id');

		$query->select('bu.last_name,bu.phoneno')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.userid=vb.user_id');

		$query->order($db->quoteName('vb.venue_booking_datetime') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$bookings = $db->loadObject();

		if(!$bookings)
		{
			return array();
		}

		return $bookings;
	}

	public function getPackageRequest()
	{
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$packageID = $input->get('purchase_id',0,'int');
		$db        = JFactory::getDbo();
		$query     = $db->getQuery(true);

		$query->select('pp.*')
			->from($db->quoteName('#__bcted_package_purchased','pp'))
			->where($db->quoteName('pp.package_purchase_id') . ' = ' . $db->quote($packageID));

		$query->select('pckg.package_name,pckg.package_image,pckg.package_details,pckg.package_price,pckg.currency_sign AS pakcge_current_currency_sign,pckg.currency_code AS pakcge_current_currency_code,pckg.package_date')
			->join('LEFT','#__bcted_package AS pckg ON pckg.package_id=pp.package_id');

		$query->select('ps.currency_code AS package_currency_code,ps.currency_sign AS package_currency_sign')
			->join('LEFT','#__bcted_payment_status AS ps ON ps.booked_element_id=pp.package_purchase_id AND ps.booked_element_type="package"');

		$query->select('bs.status AS status_text')
			->join('LEFT','#__bcted_status AS bs ON bs.id=pp.status');

		$query->select('bus.status AS user_status_text')
			->join('LEFT','#__bcted_status AS bus ON bus.id=pp.user_status');

		$query->select('v.userid AS venue_owner,v.venue_name,v.city,v.country,v.latitude,v.longitude,v.currency_code,v.currency_sign,v.venue_address,v.venue_about,v.venue_amenities,v.venue_signs,v.venue_rating,v.venue_timings,v.venue_image,v.venue_video,v.is_smart,v.is_casual,v.is_food,v.is_drink,v.working_days,v.is_smoking')
			->join('LEFT','#__bcted_venue AS v ON v.venue_id=pp.venue_id');

		$query->select('c.userid AS company_owner,c.company_name,c.city,c.country,c.latitude,c.longitude,c.currency_code,c.currency_sign,c.company_address,c.company_about,c.company_image')
			->join('LEFT','#__bcted_company AS c ON c.company_id=pp.company_id');

		$query->select('u.name')
			->join('LEFT','#__users AS u ON u.id=pp.user_id');

		$query->select('bu.last_name,bu.phoneno')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.userid=pp.user_id');

		$query->order($db->quoteName('pp.time_stamp') . ' DESC');

		// Set the query and load the result.
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
		$user = JFactory::getUser();
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
