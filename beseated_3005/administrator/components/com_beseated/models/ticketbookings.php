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
class BeseatedModelTicketBookings extends JModelList
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
				'event_name','b.event_name',
				'event_ticket_booking_id','a.event_ticket_booking_id',
				'name','c.name'

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
		$query->select('a.ticket_booking_id,a.user_id,b.event_id,b.event_name,a.total_ticket,b.event_date,b.event_time')
			->from($db->quoteName('#__beseated_event_ticket_booking', 'a'))
			//->where($db->quoteName('b.is_deleted') .'='.$db->quote('0'))
			->where($db->quoteName('a.status') .'='.$db->quote('5'))
			->join('INNER', '#__beseated_event AS b ON b.event_id=a.event_id');
		$query->select('bu.full_name')
			->join('INNER','#__beseated_user_profile AS bu ON bu.user_id=a.user_id');

		// Add the list ordering clause.
		/*$orderCol	= $this->state->get('list.ordering', 'b.time_stamp');
		$orderDirn 	= $this->state->get('list.direction', 'desc');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));*/

		$fullordering = $this->state->get('list.fullordering', '');
		$search = $this->getState('filter.search');

		if(empty($fullordering))
		{
			$fullordering = "b.time_stamp DESC";
		}
		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('((bu.full_name LIKE ' . $like.') OR (b.event_name LIKE '.$like .'))');
		}


		$orderArray = explode(" ", $fullordering);
		$ordering = $orderArray[0];
		$direction = $orderArray[1];


		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));
		return $query;
	}
}
