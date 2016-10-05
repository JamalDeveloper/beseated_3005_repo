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
 * Beseated Protections Model
 *
 * @since  0.0.1
 */
class BeseatedModelProtections extends JModelList
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
		/*if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'protection_id','a.protection_id',
				'protection_name','a.protection_name',
				'published', 'a.published'
			);
		}*/

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
			->from($db->quoteName('#__beseated_protection', 'a'));

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.protection_name');
		$orderDirn 	= $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	public function getProtection()
	{
		$search = $this->getState('filter.search');

		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_protection', 'a'))
			->where($db->quoteName('a.published') . ' = ' . $db->quote('1'));

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('((a.protection_name LIKE ' . $like.') OR (a.location LIKE '.$like .'))');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.protection_name');
		$orderDirn 	= $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		$db->setQuery($query);

		$protectionList = $db->loadObjectList();


		foreach ($protectionList as $key => $protection)
		{
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('SUM(people_count)')
				->where($db->quoteName('element_type') . ' = ' . $db->quote('protection'))
				->where($db->quoteName('element_id') . ' = ' . $db->quote($protection->protection_id))
				->from($db->quoteName('#__beseated_promotion_message'));

			$db->setQuery($query);

			$people_count = $db->loadResult();

			$protection->people_count = ($people_count)? $people_count :'0';
		}

		$fullordering = $this->state->get('list.fullordering', '');

		if(empty($fullordering))
		{
			$fullordering = "protection_name ASC";
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

		BeseatedHelper::array_sort_by_column($protectionList,$ordering,$sortingOrder);

		$this->total = count($protectionList);

		$limit     = ($this->state->get('list.limit')) ? $this->state->get('list.limit') : 100000;
		$start     = $this->state->get('list.start');
		$protectionList = array_slice($protectionList, $start, $limit);

		return $protectionList;
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
				$protectionRevenue[$value->protection_id] = $value->total_price;
			}

			return $protectionRevenue;

		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}
}
