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
 * The Beseated Club Guest Requests Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubGuestRequests extends JModelList
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

	public function getGuestLists()
	{
		$user      = JFactory::getUser();
		$venue     = BeseatedHelper::venueUserDetail($user->id);
		$status_id = BeseatedHelper::getStatusID('accept');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('DISTINCT(booking_date)')
			->from($db->quoteName('#__beseated_venue_guest_booking'))
			->where($db->quoteName('venue_status') . ' = ' . $db->quote($status_id))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venue->venue_id))
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->order($db->quoteName('time_stamp') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$guest_bookings = $db->loadColumn();

		$countGuestBooking = count($guest_bookings);

		if (count($countGuestBooking) == 0)
		{
			return array();
		}

		$guestList = array();
		for ($i = 0; $i < $countGuestBooking; $i++)
		{
			$bookingdate = $guest_bookings[$i];

			// Initialiase variables.
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('SUM(remaining_guest)')
				->from($db->quoteName('#__beseated_venue_guest_booking'))
				->where($db->quoteName('booking_date') . ' = ' . $db->quote($bookingdate))
				->where($db->quoteName('venue_id') . ' = ' . $db->quote($venue->venue_id))
				->where($db->quoteName('venue_status') . ' = ' . $db->quote($status_id))
				->order($db->quoteName('time_stamp') . ' DESC');

			// Set the query and load the result.
			$db->setQuery($query);
			$totalguest_byDate = $db->loadResult();

			$guestList[$i]['bookingDate'] = date('d-m-Y',strtotime($bookingdate));
			$guestList[$i]['totalGuest']  = $totalguest_byDate;
		}

		return $guestList;
	}
}
