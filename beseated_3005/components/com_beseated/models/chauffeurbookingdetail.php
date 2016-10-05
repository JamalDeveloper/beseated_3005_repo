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
class BeseatedModelChauffeurBookingDetail extends JModelList
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

	public function getChauffeurBooking()
	{
		$user          = JFactory::getUser();
		$elementType   = BeseatedHelper::getUserType($user->id);
		$elementDetail = BeseatedHelper::getUserElementID($user->id);
		$input         = JFactory::getApplication()->input;
		$bookingID     = $input->getInt('booking_id');
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('cb.booking_date,cb.booking_time,cb.pickup_location,cb.dropoff_location,cb.total_price,cb.booking_currency_code,cb.booking_currency_sign')
			->from($db->quoteName('#__beseated_chauffeur_booking') . ' AS cb')
			->where($db->quoteName('cb.chauffeur_booking_id') . ' = ' . $db->quote($bookingID));

		$query->select('usr.full_name')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=cb.user_id');

		$query->select('cs.service_name')
			->join('LEFT','#__beseated_chauffeur_services AS cs ON cs.service_id=cb.service_id');

		$db->setQuery($query);

		$resBookings = $db->loadObject();

		if(!$resBookings){
			return array();
		}
		return $resBookings;
	}
}
