<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Beseated Protection Bookings Model
 *
 * @since  0.0.1
 */
class BeseatedModelVenueBookings extends JModelList
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
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'venue_table_booking_id','a.venue_table_booking_id',
				'booking_date','a.booking_date',
				'total_guest','a.total_guest',
				'venue_name','b.venue_name',
				'table_name','d.table_name',
				'full_name','c.full_name',
				'total_price','a.total_price'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	public function getListQuery()
	{
		require_once JPATH_SITE . '/components/com_ijoomeradv/extensions/beseated/helper.php';

		$this->helper            = new beseatedAppHelper;
		$statusArray = array();
		$statusArray[] = $this->helper->getStatusID('booked');
		$statusArray[] = $this->helper->getStatusID('confirmed');

		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.venue_table_booking_id,a.venue_id,a.table_id,a.user_id,a.booking_date,a.booking_time,a.total_guest,a.male_guest,a.female_guest,a.total_price,a.booking_currency_code,a.pay_deposite,a.is_bill_posted,a.bill_post_amount')
			->where($db->quoteName('a.user_status') . ' IN ('.implode(",", $statusArray).')')
			->from($db->quoteName('#__beseated_venue_table_booking', 'a'));

		$query->select('b.venue_name')
			->join('LEFT','#__beseated_venue AS b ON b.venue_id=a.venue_id');

		$query->select('d.table_name')
			->join('LEFT','#__beseated_venue_table AS d ON d.table_id=a.table_id');

		$query->select('c.full_name')
			->join('LEFT','#__beseated_user_profile AS c ON c.user_id=a.user_id');

		$search = $this->state->get('filter.search','');
		if(!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$searchArray = array();
			$searchArray[] = 'c.full_name LIKE ' . $like;
			$searchArray[] = 'd.table_name LIKE ' . $like;
			$searchArray[] = 'b.venue_name LIKE ' . $like;

			$query->where('((' . implode(' OR ', $searchArray) .'))');
		}

		// Add the list ordering clause.
		//$orderCol	= $this->state->get('list.ordering', 'a.time_stamp');
		//$orderDirn 	= $this->state->get('list.direction', 'desc');

		$fullordering = $this->state->get('list.fullordering', '');

		if(empty($fullordering))
		{
			$fullordering = "a.time_stamp ASC";
		}

		$orderArray = explode(" ", $fullordering);
		$ordering = $orderArray[0];
		$direction = $orderArray[1];

		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}
}
