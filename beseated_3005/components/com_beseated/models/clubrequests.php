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
class BeseatedModelClubRequests extends JModelList
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

	public function getClubRequests()
	{
		$user  = JFactory::getUser();
		$venue = BeseatedHelper::venueUserDetail($user->id);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$rsvpStatus   = array();
		$rsvpStatus[] = BeseatedHelper::getStatusID('request');
		$rsvpStatus[] = BeseatedHelper::getStatusID('awaiting-payment');
		$rsvpStatus[] = BeseatedHelper::getStatusID('decline');

		// Create the base select statement.
		$query->select('vb.*')
			->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vb')
			->where($db->quoteName('vb.venue_id') . ' = ' . $db->quote($venue->venue_id))
			->where($db->quoteName('vb.venue_status') . ' IN ('.implode(',', $rsvpStatus).')')
			->where($db->quoteName('vb.deleted_by_venue') . ' = ' . $db->quote(0))
			->where($db->quoteName('vb.booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('usr.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('vb.booking_date') . ' ASC,'.$db->quoteName('vb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar,usr.fb_id')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=vb.user_id');

		$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

		$query->select('vt.table_name,min_price')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

		$query->select('vpt.premium_table_name')
			->join('LEFT','#__beseated_venue_premium_table AS vpt ON vpt.premium_id=vt.premium_table_id');

		$db->setQuery($query);

		$resBookings = $db->loadObjectList();

		return $resBookings;
	}

	public function getGuestListRequest()
	{
		$user       = JFactory::getUser();
		$venue      = BeseatedHelper::venueUserDetail($user->id);
		$glStatus   = array();
		$glStatus[] = BeseatedHelper::getStatusID('request');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('vgb.*')
			->from($db->quoteName('#__beseated_venue_guest_booking') . ' AS vgb')
			->where($db->quoteName('vgb.venue_status') . ' IN ('.implode(",", $glStatus).')')
			->where($db->quoteName('vgb.venue_id') . ' = ' . $db->quote($venue->venue_id))
			->where($db->quoteName('vgb.booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->order($db->quoteName('vgb.booking_date') . ' ASC');
		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar,usr.fb_id')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=vgb.user_id');
		$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vgb.venue_id');
		$db->setQuery($query);

		$resGuestBookings = $db->loadObjectList();

		return $resGuestBookings;
	}

	public function changeStatus($statusID,$bookingID)
	{
		$bookingTable                     = $this->getTable();
		$bookingTable->load($bookingID);
		$bookingTable->user_status        = 6;
		$bookingTable->yacht_status       = 6;
		$bookingTable->response_date_time = date('Y-m-d H:i:s');

		if($bookingTable->store())
		{
			return 200;
		}

		return 400;

	}
}
