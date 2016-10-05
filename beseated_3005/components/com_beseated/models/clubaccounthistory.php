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
 * The Beseated Club Account History Messages Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubAccountHistory extends JModelList
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

	protected function getListQuery()
	{
		$user = JFactory::getUser();
		$elementType   = BeseatedHelper::getUserType($user->id);
		$elementDetail = BeseatedHelper::getUserElementID($user->id);

		$bookingStatus   = array();
		$bookingStatus[] = BeseatedHelper::getStatusID('booked');
	    $bookingStatus[] = BeseatedHelper::getStatusID('confirmed');
		$bookingStatus[] = BeseatedHelper::getStatusID('canceled');

		if($elementType != 'Venue')
		{
			return array();
		}

		if(!$elementDetail->venue_id)
		{
			return array();
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('vb.*')
			->from($db->quoteName('#__beseated_venue_table_booking','vb'))
			->where($db->quoteName('vb.venue_id') . ' = ' . $db->quote($elementDetail->venue_id))
			->where($db->quoteName('vb.venue_status') . ' IN ('.implode(',', $bookingStatus).')')
			->where($db->quoteName('vb.booking_date')  . ' < ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('vb.deleted_by_venue') . ' = ' . $db->quote(0))
			->where($db->quoteName('up.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('vb.booking_date') . ' DESC');

		$query->select('up.full_name,up.phone,up.avatar')
			->join('LEFT','#__beseated_user_profile AS up ON up.user_id=vb.user_id');

		$query->select('vt.min_price')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.venue_id=vb.venue_id ');

		return $query;
	}

	public function getVenueHistory()
	{
		$user = JFactory::getUser();
		$elementType   = BeseatedHelper::getUserType($user->id);
		$elementDetail = BeseatedHelper::getUserElementID($user->id);

		$bookingStatus   = array();
		$history         = array();

		$bookingStatus[] = BeseatedHelper::getStatusID('booked');
	    $bookingStatus[] = BeseatedHelper::getStatusID('confirmed');
		$bookingStatus[] = BeseatedHelper::getStatusID('canceled');

		if($elementType != 'Venue')
		{
			return array();
		}

		if(!$elementDetail->venue_id)
		{
			return array();
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('vb.*')
			->from($db->quoteName('#__beseated_venue_table_booking','vb'))
			->where($db->quoteName('vb.venue_id') . ' = ' . $db->quote($elementDetail->venue_id))
			->where($db->quoteName('vb.venue_status') . ' IN ('.implode(',', $bookingStatus).')')
			->where($db->quoteName('vb.booking_date')  . ' < ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('vb.deleted_by_venue') . ' = ' . $db->quote(0))
			->where($db->quoteName('up.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('vb.booking_date') . ' ASC,'.$db->quoteName('vb.booking_time') . ' ASC');

		/*$query->select('ps.payment_id')
			->join('LEFT','#__beseated_payment_status AS ps ON ps.booking_id=vb.venue_table_booking_id AND ps.booking_type="venue"');*/

		$query->select('up.full_name,up.phone,up.avatar')
			->join('LEFT','#__beseated_user_profile AS up ON up.user_id=vb.user_id');

		$query->select('vt.min_price')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.venue_id=vb.venue_id ');

		$db->setQuery($query);

		$venueResult = $db->loadObjectList();

		foreach ($venueResult as $key => $booking) 
		{
			$checkDateTime = date('Y-m-d H:i:s',strtotime($booking->booking_date.' '.$booking->booking_time) + (3600 * $booking->total_hours));

			if(BeseatedHelper::isPastDateTime($checkDateTime))
			{
				$history[] = $booking;
			}
			
		}

		return $history;
	}

	public function addUserToBlackList($userID,$venueID,$elementType = 'Venue')
	{
		$isBlackListed = BeseatedHelper::checkBlackList($userID,$venueID,$elementType);
		if($isBlackListed)
		{
			return "706";
		}

		$isAdded = BeseatedHelper::addUserToBlackList($userID,$venueID,$elementType);
		if($isAdded)
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base delete statement.
			$query->delete()
				->from($db->quoteName('#__beseated_favourite'))
				->where($db->quoteName('user_id') . ' = ' . $db->quote((int) $userID))
				->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
				->where($db->quoteName('element_id') . ' = ' . $db->quote((int) $venueID));

			// Set the query and execute the delete.
			$db->setQuery($query);

			$db->execute();

			return "200";
		}

		return "500";
	}

	public function removeUserFromBlackList($userID,$venueID,$elementType = 'Venue')
	{
		BeseatedHelper::removeUserFromBlackList($userID,$venueID,$elementType);

		return "200";
	}
}
