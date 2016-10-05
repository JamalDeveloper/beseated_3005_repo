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
 * The Beseated Club Summary Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubSummary extends JModelList
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
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'msg_id','a.msg_id',
				'message','a.message',
				'published','a.published'
			);
		}

		$this->liveUsers = BeseatedHelper::getLiveBeseatedGuests();

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$user          = JFactory::getUser();
		$elementType   = BeseatedHelper::getUserType($user->id);
		$elementDetail = BeseatedHelper::getUserElementID($user->id);

		if($elementType != 'Venue')
		{
			return array();
		}

		if(!$elementDetail->venue_id)
		{
			return array();
		}

		$db     = JFactory::getDbo();
		$queryV = $db->getQuery(true);
		$queryV->select('venue_table_booking_id')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($elementDetail->venue_id))
			/*->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'))*/
			->where($db->quoteName('deleted_by_venue') . ' = ' . $db->quote('0'));

		$db->setQuery($queryV);
		$bookingIDs = $db->loadColumn();
		$query      = $db->getQuery(true);
		$query->select('vb.*')
			->from($db->quoteName('#__beseated_venue_table_booking','vb'));

		if(count($bookingIDs) == 0)
		{
			return array();
		}
		else if(is_array($bookingIDs))
		{
			$bookingIDStr = implode(",", $bookingIDs);
			$query->where($db->quoteName('vb.venue_table_booking_id') . ' IN (' . $bookingIDStr . ')');
		}
		else
		{
			$query->where($db->quoteName('vb.venue_table_booking_id') . ' = ' . $db->quote($bookingIDs));
		}

		// Create the base select statement.
		$query->select('ps.payment_id')
				->join('LEFT','#__beseated_payment_status AS ps ON ps.booking_id=vb.venue_id AND ps.booking_type LIKE "%venue%"');

		$query->select('vt.table_name,,vt.premium_table_id,vt.min_price,vt.capacity')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

		$query->select('bs.status_display AS status_text')
			->join('LEFT','#__beseated_status AS bs ON bs.status_id=vb.venue_status');

		$query->select('bus.status_display AS user_status_text')
			->join('LEFT','#__beseated_status AS bus ON bus.status_id=vb.user_status');

		$query->select('v.venue_name,v.location,v.currency_code,v.currency_sign,v.description,v.avg_ratting,v.working_days')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

		$query->select('img.thumb_image,img.image')
			->join('LEFT','#__beseated_element_images AS img ON img.element_id=vb.venue_id');

		$query->select('u.name')
			->join('LEFT','#__users AS u ON u.id=vb.user_id');


		$query->select('bu.full_name,bu.phone')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=vb.user_id');

		$query->order("FIElD (".$db->quoteName("vb.venue_status")." ,'1','6,','7','5','9','11','10') , booking_date ASC");

		$limit = $this->getState('list.limit');
		$limit = $this->getState('list.start');

		return $query;
	}

	/**
	 * Method to get a list of articles.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */

	public function getClubSummary()
	{
		$user = JFactory::getUser();
		$elementType   = BctedHelper::getUserGroupType($user->id);
		$elementDetail = BctedHelper::getUserElementID($user->id);

		if($elementType != 'Venue')
		{
			return array();
		}

		if(!$elementDetail->venue_id)
		{
			return array();
		}

		$db     = JFactory::getDbo();
		$queryV = $db->getQuery(true);
		$queryV->select('venue_table_booking_id')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($elementDetail->venue_id))
			/*->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'))*/
			->where($db->quoteName('deleted_by_venue') . ' = ' . $db->quote('0'));

		$db->setQuery($queryV);
		$bookingIDs = $db->loadColumn();
		$query      = $db->getQuery(true);
		$query->select('vb.*')
			->from($db->quoteName('#__beseated_venue_table_booking','vb'));


		if(!$bookingIDs)
		{
			return array();
		}
		else if(is_array($bookingIDs))
		{
			$bookingIDStr = implode(",", $bookingIDs);
			$query->where($db->quoteName('vb.venue_booking_id') . ' IN (' . $bookingIDStr . ')');
		}
		else
		{
			$query->where($db->quoteName('vb.venue_booking_id') . ' = ' . $db->quote($bookingIDs));
		}

		// Create the base select statement.
		$query->select('ps.payment_id')
				->join('LEFT','#__beseated_payment_status AS ps ON ps.booking_id=vb.venue_id AND ps.booking_type LIKE "%venue%"');

		$query->select('vt.table_name,vt.image,vt.thumb_image,vt.min_price,vt.capacity')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

		$query->select('bs.status_display AS status_text')
			->join('LEFT','#__beseated_status AS bs ON bs.status_id=vb.venue_status');

		$query->select('bus.status_display AS user_status_text')
			->join('LEFT','#__beseated_status AS bus ON bus.status_id=vb.user_status');

		$query->select('v.venue_name,v.location,v.currency_code,v.currency_sign,v.description,v.avg_ratting,v.working_days')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

		$query->select('img.thumb_image,img.image')
			->join('LEFT','#__beseated_element_images AS img ON img.element_id=vb.venue_id');

		$query->select('u.name')
			->join('LEFT','#__users AS u ON u.id=vb.user_id');

		$query->select('bu.full_name,bu.phone')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=vb.user_id');

		$query->order($db->quoteName('vb.booking_date') . ' DESC');
		$db->setQuery($query);
		$bookings = $db->loadObjectList();
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
		$query->select('count(venue_status) as total_count,venue_status')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('deleted_by_venue') . ' = ' . $db->quote('0'))
			/*->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'))*/
			->group($db->quoteName('venue_status'));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	public function totalRequestOfVenue($venueID)
	{
		// Initialiase variables.
		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);
		$statusID = BeseatedHelper::getStatusIDFromStatusName('Request');

		// Create the base select statement.
		$query->select('count(venue_status) as total_count,venue_status')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('deleted_by_venue') . ' = ' . $db->quote('0'))
			->where($db->quoteName('venue_status') . ' = ' . $db->quote($statusID))
			/*->where($db->quoteName('created') . ' >= ' . $db->quote(date('Y-m-d', strtotime('now'))))*/;
			/*->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'));*/

		if(count($this->liveUsers) != 0)
		{

			$query->where($db->quoteName('user_id') . ' IN (' . implode(',',$this->liveUsers) . ')');
		}

		$query->group($db->quoteName('venue_status'));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadResult();

		if(!$result)
		{
			$result = 0;
		}

		return $result;
	}

	public function getRevenueForVenue($venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$sqlBookingIDs = $db->getQuery(true);
		$sqlBookingIDs->select('venue_table_booking_id')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID));
		$db->setQuery($sqlBookingIDs);
		$resultAllBookingIDs = $db->loadColumn();

		if($resultAllBookingIDs)
		{
			$sqlPaymentStatusBookingIDs = $db->getQuery(true);
			$sqlPaymentStatusBookingIDs->select('booking_id')
				->from($db->quoteName('#__beseated_payment_status'))
				->where($db->quoteName('booking_type') . ' LIKE '.$db->quote('%venue%'))
				/*->where($db->quoteName('payment_status') . ' = ' . $db->quote('Success'))*/
				->where($db->quoteName('booking_id') . ' IN ( ' . implode(",", $resultAllBookingIDs) . ' ) ');
			$db->setQuery($sqlPaymentStatusBookingIDs);
			$resultAllPaidBookingIDs = $db->loadColumn();

			if($resultAllPaidBookingIDs)
			{
				// Create the base select statement.
				$query->select('sum(bill_post_amount) as revenue')
					->from($db->quoteName('#__beseated_venue_table_booking'))
					->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
					/*->where($db->quoteName('venue_status') . ' <> ' . $db->quote(10))
					->where($db->quoteName('user_status') . ' <> ' . $db->quote(10))*/
					->where($db->quoteName('venue_table_booking_id') . ' IN ( ' . implode(",", $resultAllPaidBookingIDs) . ' ) ');


				// Set the query and load the result.
				$db->setQuery($query);

				$result = $db->loadResult();

				return $result;
			}
		}

		return 0;
	}
}
