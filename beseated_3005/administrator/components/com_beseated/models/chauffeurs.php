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
 * Beseated Chauffeurs Model
 *
 * @since  0.0.1
 */
class BeseatedModelChauffeurs extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelList
	 * @since   0.0.1
	 */
	public $total;

	public function __construct($config = array())
	{
		/*if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'chauffeur_id','a.chauffeur_id',
				'chauffeur_name','a.chauffeur_name',
				'published', 'a.published'
			);
		}*/
		$this->total = 0;
		parent::__construct($config);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	public function getListQuery()
	{
		//echo "<pre>";print_r($this->state);echo "</pre>";

		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*,b.people_count')
			->from($db->quoteName('#__beseated_chauffeur', 'a'))
			->where($db->quoteName('b.element_type') . ' = ' . $db->quote('chauffeur'))
			->join('LEFT', '#__beseated_promotion_message AS b ON b.element_id=a.chauffeur_id');

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.chauffeur_name');
		$orderDirn 	= $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	public function getChauffeurs()
	{
		$search = $this->getState('filter.search');
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_chauffeur', 'a'))
			->where($db->quoteName('a.published') . ' = ' . $db->quote('1'));


		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('((a.chauffeur_name LIKE ' . $like.') OR (a.location LIKE '.$like .'))');
		}

		$db->setQuery($query);

		$chauffeurList = $db->loadObjectList();

		foreach ($chauffeurList as $key => $chauffeur)
		{
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('SUM(people_count)')
				->where($db->quoteName('element_type') . ' = ' . $db->quote('chauffeur'))
				->where($db->quoteName('element_id') . ' = ' . $db->quote($chauffeur->chauffeur_id))
				->from($db->quoteName('#__beseated_promotion_message'));

			$db->setQuery($query);

			$people_count = $db->loadResult();

			$chauffeur->people_count = ($people_count)? $people_count :'0';
		}

		$fullordering = $this->state->get('list.fullordering', '');

		if(empty($fullordering))
		{
			$fullordering = "chauffeur_name ASC";
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

		BeseatedHelper::array_sort_by_column($chauffeurList,$ordering,$sortingOrder);

		$this->total = count($chauffeurList);

		$limit     = ($this->state->get('list.limit')) ? $this->state->get('list.limit') : 100000;
		$start     = $this->state->get('list.start');
		$chauffeurList = array_slice($chauffeurList, $start, $limit);

		//echo "<pre/>";print_r($chauffeurList);exit;
		return $chauffeurList;

	}

	public function getPagination()
	{
		//echo "<pre>";print_r($this->total);echo "</pre>";
		//$this->total = 10;
		jimport('joomla.html.pagination');
		$this->_pagination = new JPagination($this->total, $this->state->get('list.start'), $this->state->get('list.limit') );
		return $this->_pagination;
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
		$query->select('chauffeur_id,sum(total_price) AS total_price,sum(remaining_amount) remaining_amount')
			->from($db->quoteName('#__beseated_chauffeur_booking'))
			->where($db->quoteName('chauffeur_status') . ' = ' . $db->quote('5'))
			->group($db->quoteName('chauffeur_id'))
			->order($db->quoteName('chauffeur_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();

			$chauffeurRevenue = array();
			foreach ($result as $key => $value)
			{
				$chauffeurRevenue[$value->chauffeur_id] = $value->total_price;
			}

			return $chauffeurRevenue;

		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}


}
