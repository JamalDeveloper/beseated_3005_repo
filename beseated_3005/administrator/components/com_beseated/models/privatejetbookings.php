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
 * Beseated Private Jet Bookings Model
 *
 * @since  0.0.1
 */
class BeseatedModelPrivateJetBookings extends JModelList
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
				'private_jet_booking_id','a.private_jet_booking_id',
				'flight_date','a.flight_date',
				'created','a.created',
				'from_location','a.from_location',
				'to_location','a.to_location',
				'person_name','a.person_name',
				'company_name','b.company_name',
				'phone','a.phone'
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
		$query->select('a.private_jet_booking_id,a.private_jet_id,a.flight_date,a.flight_time,a.return_flight_date,a.return_flight_time,a.from_location,a.to_location,a.total_guest,a.person_name,a.email,a.phone,a.extra_information,a.created')
			->from($db->quoteName('#__beseated_private_jet_booking', 'a'));

		$query->select('b.company_name')
			->join('INNER','#__beseated_private_jet AS b ON b.private_jet_id=a.private_jet_id');

		$query->select('c.full_name')
			->join('INNER','#__beseated_user_profile AS c ON c.user_id=a.user_id');

		$search = $this->state->get('filter.search','');
		if(!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$searchArray = array();
			$searchArray[] = 'c.full_name LIKE ' . $like;
			$searchArray[] = 'b.company_name LIKE ' . $like;
			$searchArray[] = 'a.flight_date LIKE ' . $like;
			$searchArray[] = 'a.flight_time LIKE ' . $like;
			$searchArray[] = 'a.return_flight_date LIKE ' . $like;
			$searchArray[] = 'a.return_flight_time LIKE ' . $like;
			$searchArray[] = 'a.person_name LIKE ' . $like;
			$searchArray[] = 'a.from_location LIKE ' . $like;
			$searchArray[] = 'a.to_location LIKE ' . $like;
			$searchArray[] = 'a.total_guest LIKE ' . $like;
			$searchArray[] = 'a.extra_information LIKE ' . $like;


			$query->where('((' . implode(' OR ', $searchArray) .'))');
		}

		// Add the list ordering clause.
		//$orderCol	= $this->state->get('list.ordering', 'a.time_stamp');
		//$orderDirn 	= $this->state->get('list.direction', 'desc');
		//$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		$fullordering = $this->state->get('list.fullordering');

		if(empty($fullordering))
		{
			$fullordering = "a.created DESC";
		}

		$orderArray = explode(" ", $fullordering);
		$ordering = $orderArray[0];
		$direction = $orderArray[1];

		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}
}
