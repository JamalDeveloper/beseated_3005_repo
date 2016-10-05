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
 * Beseated Yachts Model
 *
 * @since  0.0.1
 */
class BeseatedModelYachts extends JModelList
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
				'yacht_id','a.yacht_id',
				'yacht_name','a.yacht_name',
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
			->from($db->quoteName('#__beseated_yacht', 'a'));

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.yacht_name');
		$orderDirn 	= $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	public function getYachts()
	{
		$search = $this->getState('filter.search');

		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_yacht', 'a'))
			->where($db->quoteName('a.published') . ' = ' . $db->quote('1'));

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('((a.yacht_name LIKE ' . $like.') OR (a.location LIKE '.$like .'))');
		}

		$db->setQuery($query);

		$yachtList = $db->loadObjectList();


		foreach ($yachtList as $key => $yacht)
		{
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('SUM(people_count)')
				->where($db->quoteName('element_type') . ' = ' . $db->quote('yacht'))
				->where($db->quoteName('element_id') . ' = ' . $db->quote($yacht->yacht_id))
				->from($db->quoteName('#__beseated_promotion_message'));

			$db->setQuery($query);

			$people_count = $db->loadResult();

			$yacht->people_count = ($people_count)? $people_count :'0';
		}

		$fullordering = $this->state->get('list.fullordering', '');

		if(empty($fullordering))
		{
			$fullordering = "yacht_name ASC";
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

		BeseatedHelper::array_sort_by_column($yachtList,$ordering,$sortingOrder);

		$this->total = count($yachtList);

		$limit     = ($this->state->get('list.limit')) ? $this->state->get('list.limit') : 100000;
		$start     = $this->state->get('list.start');
		$yachtList = array_slice($yachtList, $start, $limit);

		return $yachtList;
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
		$query->select('yacht_id,sum(total_price) AS total_price,sum(remaining_amount) remaining_amount')
			->from($db->quoteName('#__beseated_yacht_booking'))
			->where($db->quoteName('yacht_status') . ' = ' . $db->quote('5'))
			->group($db->quoteName('yacht_id'))
			->order($db->quoteName('yacht_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
			$yachtRevenue = array();
			foreach ($result as $key => $value)
			{
				$yachtRevenue[$value->yacht_id] = $value->total_price;
			}

			return $yachtRevenue;

		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

}
