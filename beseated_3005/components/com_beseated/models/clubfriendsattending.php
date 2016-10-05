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
 * The Beseated Club Friends Attending Messages Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubFriendsAttending extends JModelList
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

	public function getMyFbFriendID()
	{
		$user = JFactory::getUser();
		if(!$user->id)
		{
			return array();
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('fb_friends_id')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));

		// Set the query and load the result.
		$db->setQuery($query);

		$fbFriends = $db->loadResult();
		try
		{
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}


		if(empty($fbFriends))
		{
			return array();
		}


		$query1 = $db->getQuery(true);

		// Create the base select statement.
		$query1->select('user_id')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('fb_id') . ' IN (' . $fbFriends . ')');

		// Set the query and load the result.
		$db->setQuery($query1);

		$foundArray = $db->loadColumn();
		$app        = JFactory::getApplication();
		$input      = $app->input;
		$venueID    = $input->get('club_id', 0, 'int');

		if(!$venueID)
		{
			return array();
		}

		$statusArray   = array();
		$statusArray[] = BeseatedHelper::getStatusID('confirmed');
		$statusArray[] = BeseatedHelper::getStatusID('booked');

		$firstDate     = date('Y-m-d');
		$date          = strtotime("+7 day");
		$lastDate      = date('Y-m-d', $date);

		// Initialiase variables.
		$query3 = $db->getQuery(true);


		$query3->select('vb.*')
			->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vb')
			//->where($db->quoteName('vb.user_id') . ' IN ('.$userIDs.')' )
			->where($db->quoteName('vb.user_id') . ' <> ' . $db->quote($user->id))
			->where($db->quoteName('vb.user_id') . ' IN ('.implode(",", $foundArray).')')
			->where($db->quoteName('vb.user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('vb.deleted_by_user') . ' = ' . $db->quote(0))
			->where($db->quoteName('vb.venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('vb.booking_date') . ' >= ' . $db->quote($firstDate))
			->where($db->quoteName('vb.booking_date') . ' <= ' . $db->quote($lastDate))
			->order($db->quoteName('vb.booking_date') . ' ASC');

		$query3->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

		$query3->select('vt.table_name,vt.premium_table_id,vt.min_price,vt.capacity,vt.thumb_image,vt.image')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

		$query3->select('vpt.premium_table_name')
			->join('LEFT','#__beseated_venue_premium_table AS vpt ON vpt.premium_id=vt.premium_table_id');

		$query3->select('u.name,u.username')
			->join('LEFT','#__users AS u ON u.id=vb.user_id');

		$query3->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar,usr.show_friends_only,usr.show_public_table,usr.fb_id')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=vb.user_id');

		$query3->group('vb.venue_table_booking_id');

		$db->setQuery($query3);

		$resVenueBookings = $db->loadObjectList();



		$filterFbFrndEmails         = BeseatedHelper::filterFbIdsToUserIDs($fbFriends);
		$resultFriendsVenueBookings = array();
		$resultPublicVenueBookings  = array();

		foreach ($resVenueBookings as $key => $booking)
		{
			if(in_array($booking->user_id, $filterFbFrndEmails['guest']))
			{
				$resultFriendsVenueBookings[] = $booking;
			}
			else if($booking->show_friends_only == 0 && $booking->privacy == 0)
			{
				if($guestUserDetail->show_public_table == '1')
				{
					$resultPublicVenueBookings[] = $booking;
				}
			}
		}

		$finalArray = array_merge($resultFriendsVenueBookings, $resultPublicVenueBookings);

		return $finalArray;
	}

	public function checkForRequestAlreadySent($bookingID, $table_id, $venue_id)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$loginUser = JFactory::getUser();

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_invitation'))
			->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($bookingID))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($venue_id))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('venue'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($loginUser->id));



		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();



			$status = BeseatedHelper::getStatusName($result->user_action);

			/*if(!$result)
			{
				return '';
			}

			if(empty($result->user_action))
			{
				$status = BeseatedHelper::getStatusName('9');
				return 'Pending';
			}
			elseif($result->user_action=='reject' || $result->venue_action=='reject')
			{
				return 'Rejected';
			}
			elseif($result->user_action=='accept' && $result->venue_action=='accept')
			{
				return 'Added';
			}
			elseif($result->user_action=='accept' && empty($result->venue_action))
			{
				return 'Pending';
			}
			else
			{
				return '';
			}*/

			return $status;
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return '';
	}
}
