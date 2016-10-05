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
 * Beseated System Messages Model
 *
 * @since  0.0.1
 */
class BeseatedModelSystemMessages extends JModelList
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
				'message_id','a.message_id',
				'message','a.message',
				'city','a.city',
				'created', 'a.created'
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
			// Filter: like / search
		$search = $this->getState('filter.search');

		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_system_message', 'a'));

		$fullordering = $this->state->get('list.fullordering', '');

		if(empty($fullordering))
		{
			$fullordering = "a.message_id ASC";
		}
		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('((a.message LIKE ' . $like.') OR (a.city LIKE '.$like .'))');
		}

		$orderArray = explode(" ", $fullordering);
		$ordering = $orderArray[0];
		$direction = $orderArray[1];

		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));
		//$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}
}
