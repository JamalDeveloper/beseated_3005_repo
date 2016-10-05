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
 * Beseated Venues Model
 *
 * @since  0.0.1
 */
class BeseatedModelVenues extends JModelList
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
				'venue_id','a.venue_id',
				'venue_name','a.venue_name',
				'published', 'a.published'
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
		$query->select('a.*')
			->from($db->quoteName('#__beseated_venue', 'a'))
			->where($db->quoteName('a.is_deleted') . ' = ' . $db->quote('0'));

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.venue_name');
		$orderDirn 	= $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	public function getVenues()
	{
		$search = $this->getState('filter.search');

		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_venue', 'a'))
			->where($db->quoteName('a.published') . ' = ' . $db->quote('1'))
			->where($db->quoteName('a.is_deleted') . ' = ' . $db->quote('0'));

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('((a.venue_name LIKE ' . $like.') OR (a.location LIKE '.$like .'))');
		}

		$db->setQuery($query);

		$venueList = $db->loadObjectList();

		foreach ($venueList as $key => $venue)
		{
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('SUM(people_count)')
				->where($db->quoteName('element_type') . ' = ' . $db->quote('venue'))
				->where($db->quoteName('element_id') . ' = ' . $db->quote($venue->venue_id))
				->from($db->quoteName('#__beseated_promotion_message'));

			$db->setQuery($query);

			$people_count = $db->loadResult();

			$venue->people_count = ($people_count)? $people_count :'0';
		}

		$fullordering = $this->state->get('list.fullordering', '');

		if(empty($fullordering))
		{
			$fullordering = "venue_name ASC";
		}


		$orderArray = explode(" ", $fullordering);
		$ordering = $orderArray[0];
		$direction = $orderArray[1];

		if(strtoupper($direction) == 'ASC')
		{
			$sortingOrder = SORT_ASC;
		}
		else
		{
			$sortingOrder = SORT_DESC;
		}

		BeseatedHelper::array_sort_by_column($venueList,$ordering,$sortingOrder);

		$this->total = count($venueList);

		$limit     = ($this->state->get('list.limit')) ? $this->state->get('list.limit') : 100000;
		$start     = $this->state->get('list.start');
		$venueList = array_slice($venueList, $start, $limit);

		return $venueList;
	}



	/*public function deleteVenue(&$ids)
	{
		if(count($ids) == 0)
		{
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_venue'))
			->set($db->quoteName('is_deleted') . ' = ' . $db->quote('1'))
			->set($db->quoteName('published') . ' = ' . $db->quote('0'))
			->where($db->quoteName('venue_id') . ' IN (' . implode(',', $ids).')');

		// Set the query and execute the update.
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}*/

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
		$query->select('venue_id,sum(total_price) AS total_price,sum(remaining_amount) remaining_amount,sum(pay_deposite) as pay_deposite')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_status') . ' = ' . $db->quote('5'))
			->group($db->quoteName('venue_id'))
			->order($db->quoteName('venue_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
			$venueRevenue = array();
			foreach ($result as $key => $value)
			{
				$venueRevenue[$value->venue_id] = $value->pay_deposite + $value->total_price;
			}

			return $venueRevenue;

		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

}
