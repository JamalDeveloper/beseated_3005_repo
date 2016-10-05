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
 * The Beseated Club Requests Model
 *
 * @since  0.0.1
 */
class BeseatedModelProtectionRequests extends JModelList
{
	protected $liveUsers;
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
		$this->liveUsers = BeseatedHelper::getLiveBeseatedGuests();
		parent::__construct($config);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   0.0.1
	 */
	public function getTable($type = 'ProtectionBooking', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getProtectionRsvp()
	{
		$user       = JFactory::getUser();
		$protection = BeseatedHelper::protectionUserDetail($user->id);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$rsvpStatus = array();
		$rsvpStatus[] = BeseatedHelper::getStatusID('request');
		$rsvpStatus[] = BeseatedHelper::getStatusID('awaiting-payment');
		$rsvpStatus[] = BeseatedHelper::getStatusID('decline');

		// Create the base select statement.
		$query->select('pb.*')
			->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
			->where($db->quoteName('pb.protection_id') . ' = ' . $db->quote($protection->protection_id))
			->where($db->quoteName('pb.protection_status') . ' IN ('.implode(',', $rsvpStatus).')')
			->where($db->quoteName('pb.deleted_by_protection') . ' = ' . $db->quote(0))
			->where($db->quoteName('usr.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('pb.booking_date') . ' ASC,'.$db->quoteName('pb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=pb.user_id');

		/*$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=pb.protection_status');*/

		$query->select('p.protection_name')
			->join('LEFT','#__beseated_protection AS p ON p.protection_id=pb.protection_id');

		$query->select('ps.service_name,ps.thumb_image,ps.image')
			->join('LEFT','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');


		// Set the query and load the result.
		//$db->setQuery($query,$startFrom,$pageLimit);
		$db->setQuery($query);

		$resBookings = $db->loadObjectList();
		
		$resultRsvpBookings = array();
		
		foreach ($resBookings as $key => $booking)
		{
			if(!BeseatedHelper::isPastDate($booking->booking_date))
			{
				$resultRsvpBookings[] = $booking;
			}

		}

		$resultRsvpBookings = json_decode(json_encode($resultRsvpBookings));

		return $resultRsvpBookings;
	}

	public function changePrice($bookingID, $amount)
	{
		$bookingTable                     = $this->getTable();
		$bookingTable->load($bookingID);
		$bookingTable->total_price        = $amount;
		$bookingTable->user_status        = 4;
		$bookingTable->protection_status  = 3;
		$bookingTable->response_date_time = date('Y-m-d H:i:s');

		if($bookingTable->store())
		{
			return 200;
		}

		return 400;
	}
}
