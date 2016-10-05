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
class BeseatedModelProtectionBookingDetail extends JModelList
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

	public function getProtectionBooking()
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
		$query->select('pb.*')
			->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
			->where($db->quoteName('pb.protection_booking_id') . ' = ' . $db->quote($bookingID))
			->order($db->quoteName('pb.booking_date') . ' ASC,'.$db->quoteName('pb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar,usr.fb_id')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=pb.user_id');

		$query->select('ps.service_name,ps.thumb_image,ps.image')
			->join('LEFT','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');

		$db->setQuery($query);

		$resBookings = $db->loadObject();

		if(!$resBookings){
			return array();
		}
		return $resBookings;
	}
}
