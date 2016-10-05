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
 * Beseated Chauffeur Bookings Model
 *
 * @since  0.0.1
 */
class BeseatedModelRewardBookings extends JModelList
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
				'reward_booking_id','a.reward_booking_id',
				'booking_date','a.booking_date',
				'reward_name','b.reward_name',
				'full_name','c.full_name',
				'reward_coin','a.reward_coin'
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
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.reward_booking_id,a.reward_id,a.booking_date,a.reward_coin')
			->from($db->quoteName('#__beseated_rewards_bookings', 'a'));

		$query->select('b.reward_name')
			->join('INNER','#__beseated_rewards AS b ON b.reward_id=a.reward_id');

		$query->select('c.full_name')
			->join('INNER','#__beseated_user_profile AS c ON c.user_id=a.user_id');

		$search = $this->state->get('filter.search','');
		if(!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$searchArray = array();
			$searchArray[] = 'c.full_name LIKE ' . $like;
			$searchArray[] = 'b.reward_name LIKE ' . $like;
			$searchArray[] = 'a.reward_booking_id LIKE ' . $like;
			$searchArray[] = 'a.booking_date LIKE ' . $like;

			$query->where('((' . implode(' OR ', $searchArray) .'))');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.time_stamp');
		$orderDirn 	= $this->state->get('list.direction', 'desc');

		$fullordering = $this->state->get('list.fullordering', '');

		if(empty($fullordering))
		{
			$fullordering = "a.time_stamp DESC";
		}

		$orderArray = explode(" ", $fullordering);
		$ordering = $orderArray[0];
		$direction = $orderArray[1];

		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}
}
