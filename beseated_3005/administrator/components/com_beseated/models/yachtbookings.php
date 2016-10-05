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
 * Beseated Yacht Bookings Model
 *
 * @since  0.0.1
 */
class BeseatedModelYachtBookings extends JModelList
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
				'yacht_booking_id','a.yacht_booking_id',
				'booking_date','a.booking_date',
				'total_hours','a.total_hours',
				'yacht_name','b.yacht_name',
				'service_name','d.service_name',
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

		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.yacht_booking_id,a.yacht_id,a.booking_date,a.total_price,a.total_hours,a.booking_currency_code')
			->where($db->quoteName('a.user_status') . ' IN ('.implode(",", $statusArray).')')
			->from($db->quoteName('#__beseated_yacht_booking', 'a'));

		$query->select('b.yacht_name')
			->join('LEFT','#__beseated_yacht AS b ON b.yacht_id=a.yacht_id');

		$query->select('d.service_name,d.service_type,d.dock,d.capacity')
			->join('LEFT','#__beseated_yacht_services AS d ON d.service_id=a.service_id');

		$query->select('c.full_name')
			->join('LEFT','#__beseated_user_profile AS c ON c.user_id=a.user_id');

		$search = $this->state->get('filter.search','');
		if(!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$searchArray = array();
			$searchArray[] = 'c.full_name LIKE ' . $like;
			$searchArray[] = 'b.yacht_name LIKE ' . $like;
			$searchArray[] = 'd.service_name LIKE ' . $like;
			$searchArray[] = 'd.dock LIKE ' . $like;
			$searchArray[] = 'a.booking_currency_code LIKE ' . $like;
			$searchArray[] = 'a.yacht_booking_id LIKE ' . $like;
			$searchArray[] = 'a.yacht_id LIKE ' . $like;
			$searchArray[] = 'a.booking_date LIKE ' . $like;
			$searchArray[] = 'a.total_price LIKE ' . $like;

			$query->where('((' . implode(' OR ', $searchArray) .'))');
		}

		$fullordering = $this->state->get('list.fullordering', '');

		if(empty($fullordering))
		{
			$fullordering = "a.time_stamp ASC";
		}

		$orderArray = explode(" ", $fullordering);
		$ordering = $orderArray[0];
		$direction = $orderArray[1];

		// Add the list ordering clause.
		//$orderCol	= $this->state->get('list.ordering', 'a.time_stamp');
		//$orderDirn 	= $this->state->get('list.direction', 'desc');
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}
}
