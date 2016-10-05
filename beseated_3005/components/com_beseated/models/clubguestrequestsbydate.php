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
 * The Beseated Club Guest Requests By Date Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubGuestRequestsByDate extends JModelList
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

	public function getVenueGuestListRequestByDate()
	{
		$user        = JFactory::getUser();
		$input       = JFactory::getApplication()->input;
		$venueID     = $input->getInt('venue_id');
		$bookingDate = $input->getString('booking_date');
		$bookingDate = BeseatedHelper::convertToYYYYMMDD($bookingDate);
		$status_id   = BeseatedHelper::getStatusID('accept');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_guest_booking'))
			->where($db->quoteName('booking_date') . ' = ' . $db->quote($bookingDate))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('venue_status') . ' = ' . $db->quote($status_id))
			->order($db->quoteName('time_stamp') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$guestList = $db->loadObjectList();

		return $guestList;
	}
}
