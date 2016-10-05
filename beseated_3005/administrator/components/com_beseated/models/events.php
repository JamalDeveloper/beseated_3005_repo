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
class BeseatedModelEvents extends JModelList
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
				'event_id','a.event_id',
				'event_name','a.event_name',
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
		//setcookie($name, '', 1);

		foreach ( $_COOKIE as $key => $value )
		{
			unset($_COOKIE[$key]);
		    setcookie( $key, null, -1);
		}

		//echo "<pre/>";print_r($_COOKIE);exit;
		$search = $this->getState('filter.search');

		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_event', 'a'))
			->where('('.$db->quoteName('a.published') . ' = ' . $db->quote('1') .' AND '.$db->quoteName('a.is_deleted') . ' = ' . $db->quote('0').')');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('((a.event_name LIKE ' . $like.') OR (a.location LIKE '.$like .') OR (a.city LIKE '.$like .'))');
		}

		$fullordering = $this->state->get('list.fullordering', '');

		if(empty($fullordering))
		{
			$fullordering = "event_name ASC";
		}


		$orderArray = explode(" ", $fullordering);
		$ordering = $orderArray[0];
		$direction = $orderArray[1];


		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		//echo $query;exit;

		return $query;
	}


}
