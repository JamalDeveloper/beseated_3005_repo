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
 * Beseated Events Model
 *
 * @since  0.0.1
 */
class BeseatedModelConcierges extends JModelList
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
				'city','city',
				'concierge_id','concierge_id'
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
		$search = $this->getState('filter.search');

		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_concierge'));

			if (!empty($search))
			{
				$like = $db->quote('%' . $search . '%');
				$query->where('(city LIKE ' . $like.') OR (phone_no LIKE '.$like .')');
			}

		$fullordering = $this->state->get('list.fullordering');


		if(empty($fullordering))
		{
			$fullordering ='concierge_id Asc';
		}

		$orderArray = explode(" ", $fullordering);

		$ordering = $orderArray[0];
		$direction = $orderArray[1];

		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		//echo $query;exit;

		return $query;
	}

	public function deleteConcierge(&$ids)
	{
		if(count($ids) == 0)
		{
			return false;
		}

		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->delete()
			->from($db->quoteName('#__beseated_concierge'))
			->where($db->quoteName('concierge_id') . ' IN (' . implode(',', $ids).')');

		// Set the query and execute the delete.
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

	}
}
