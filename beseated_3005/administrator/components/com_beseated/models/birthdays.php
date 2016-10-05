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
class BeseatedModelBirthdays extends JModelList
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
				'yacht_id','a.yacht_id',
				'yacht_name','a.yacht_name',
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
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*,b.birthdate,b.phone')
			->from($db->quoteName('#__users', 'a'))
			->where($db->quoteName('a.block') . ' = ' . $db->quote('0'))
			->where($db->quoteName('b.contacted') . ' = ' . $db->quote('0'))
			->join('INNER', '#__beseated_user_profile AS b ON b.user_id=a.id');

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.name');
		$orderDirn 	= $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	public function getUserBirthdays()
	{
		/*$birthDate = '1992-05-07'; // Read this from the DB instead

		$time = strtotime($birthDate);

		if(date('m-d', $time) >= date('m-d') && date('m-d', $time) <= date('m-d', strtotime('+1 Week')))
		{
		   echo "<pre>";print_r("1");echo "</pre>";exit;
		}
		else
		{
			 echo "<pre>";print_r("2");echo "</pre>";exit;
		}*/

		$fullordering = $this->state->get('list.fullordering');
		$search = $this->getState('filter.search');
		//echo "<pre/>";print_r($this->state);exit;
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*,b.birthdate,b.phone')
			->from($db->quoteName('#__users', 'a'))
			->where($db->quoteName('a.block') . ' = ' . $db->quote('0'))
			->where($db->quoteName('b.contacted') . ' = ' . $db->quote('0'))
			->join('INNER', '#__beseated_user_profile AS b ON b.user_id=a.id');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('((a.name LIKE ' . $like.') OR (a.email LIKE '.$like .') OR (b.phone LIKE '.$like .'))');
		}

		if(empty($fullordering))
		{
			$fullordering = "bdate ASC";
		}

		$orderArray = explode(" ", $fullordering);
		$ordering = $orderArray[0];
		$direction = $orderArray[1];

		$db->setQuery($query);

		$userDetails = $db->loadObjectList();


		$userUpBirthdays = array();

		foreach ($userDetails as $key => $user)
		{
			$time = strtotime($user->birthdate);

			if(date('m-d', $time) >= date('m-d') && date('m-d', $time) <= date('m-d', strtotime('+1 Week')))
			{
				$user->bdate = $user->birthdate;
			    $userUpBirthdays[] = $user;
			}
		}

		//echo "<pre/>";print_r((object)$userUpBirthdays);exit;
		//$userBirthdays = (object)$userUpBirthdays;

		$limit     = $this->state->get('list.limit');
		$start     = $this->state->get('list.start');


		if(strtoupper($direction) == 'ASC')
		{
			$sortingOrder = SORT_ASC;
		}
		else
		{
			$sortingOrder = SORT_DESC;
		}

		$this->total = count($userUpBirthdays);

		$userBirthdays = array_slice($userUpBirthdays, $start, $limit);

		$this->array_sort_by_column($userBirthdays,$ordering,$sortingOrder);


		$userBirthdays = (object)$userBirthdays;

		return $userBirthdays;
	}

	function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
	{

		$sort_col = array();

		foreach ($arr as $key=> $row)
		{
			$sort_col[$key] = ucfirst($row->$col);
		}

		//echo "<pre/>";print_r($dir);
		array_multisort($sort_col, $dir, $arr);
	}

	public function getPagination()
	{
		//echo "<pre>";print_r($this->total);echo "</pre>";
		//$this->total = 10;
		jimport('joomla.html.pagination');
		$this->_pagination = new JPagination($this->total, $this->state->get('list.start'), $this->state->get('list.limit') );
		return $this->_pagination;
	}

}
