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
class BeseatedModelProtectionBookings extends JModelList
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
				'protection_booking_id','a.protection_booking_id',
				'booking_date','a.booking_date',
				'total_hours','a.total_hours',
				'protection_name','b.protection_name',
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
		$query->select('a.protection_booking_id,a.protection_id,a.booking_date,a.meetup_location,a.total_guard,a.total_hours,a.total_price,a.booking_currency_code')
			->where($db->quoteName('a.user_status') . ' IN ('.implode(",", $statusArray).')')
			->from($db->quoteName('#__beseated_protection_booking', 'a'));

		$query->select('b.protection_name')
			->join('LEFT','#__beseated_protection AS b ON b.protection_id=a.protection_id');

		$query->select('d.service_name')
			->join('LEFT','#__beseated_protection_services AS d ON d.service_id=a.service_id');

		$query->select('c.full_name')
			->join('LEFT','#__beseated_user_profile AS c ON c.user_id=a.user_id');

		$search = $this->state->get('filter.search','');
		if(!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$searchArray = array();
			$searchArray[] = 'c.full_name LIKE ' . $like;
			$searchArray[] = 'b.protection_name LIKE ' . $like;
			$searchArray[] = 'd.service_name LIKE ' . $like;
			$searchArray[] = 'a.meetup_location LIKE ' . $like;
			$searchArray[] = 'a.booking_currency_code LIKE ' . $like;
			$searchArray[] = 'a.protection_booking_id LIKE ' . $like;
			$searchArray[] = 'a.protection_id LIKE ' . $like;
			$searchArray[] = 'a.booking_date LIKE ' . $like;
			$searchArray[] = 'a.total_guard LIKE ' . $like;
			$searchArray[] = 'a.total_hours LIKE ' . $like;
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
		//orderCol	= $this->state->get('list.ordering', 'a.time_stamp');
		//$orderDirn 	= $this->state->get('list.direction', 'desc');
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}

	/**
	 * Method to get Revenues of Protection companies
	 *
	 * @return      string  An SQL query
	 */
	public function getRevenues()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('protection_id,sum(total_price) AS total_price,sum(remaining_amount) remaining_amount')
			->from($db->quoteName('#__beseated_protection_booking'))
			->where($db->quoteName('protection_status') . ' = ' . $db->quote('5'))
			->group($db->quoteName('protection_id'))
			->order($db->quoteName('protection_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
			$protectionRevenue = array();
			foreach ($result as $key => $value)
			{
				$protectionRevenue[$value->protection_id] = $value->total_price - $value->remaining_amount;
			}

			return $protectionRevenue;

		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}
}
