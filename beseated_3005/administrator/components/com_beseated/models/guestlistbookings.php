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
class BeseatedModelGuestlistBookings extends JModelList
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
				'yacht_id','a.yacht_id',
				'yacht_name','a.yacht_name',
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
		$search = $this->getState('filter.search');

		$guestLists = array();

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('SUM(a.total_guest) as total_guest,SUM(a.remaining_guest) as remaining_guest,a.booking_date,b.venue_name')
			->from($db->quoteName('#__beseated_venue_guest_booking') .'AS a')
			->where($db->quoteName('a.venue_status') . ' = ' . $db->quote('11'))
			->group($db->quoteName('a.booking_date'))
			->group($db->quoteName('a.venue_id'))
			->join('INNER', '#__beseated_venue AS b ON b.venue_id=a.venue_id');
			//->order($db->quoteName('a.time_stamp') . ' DESC');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('((b.venue_name LIKE ' . $like.'))');
		}

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
