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
 * Beseated Chauffeur Bookings Model
 *
 * @since  0.0.1
 */
class BeseatedModelChauffeurBookings extends JModelList
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
				'chauffeur_booking_id','a.chauffeur_booking_id',
				'booking_date','a.booking_date',
				'chauffeur_name','b.chauffeur_name',
				'service_type','d.service_type',
				'full_name','c.full_name',
				'total_price','a.total_price'
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
		//echo "<pre>";print_r($this->state);echo "</pre>";

		require_once JPATH_SITE . '/components/com_ijoomeradv/extensions/beseated/helper.php';

		$this->helper            = new beseatedAppHelper;
		$statusArray = array();
		$statusArray[] = $this->helper->getStatusID('booked');

		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.chauffeur_booking_id,a.chauffeur_id,a.booking_date,a.pickup_location,a.dropoff_location,a.capacity,a.total_price,a.booking_currency_code')
			->where($db->quoteName('a.user_status') . ' IN ('.implode(",", $statusArray).')')
			->from($db->quoteName('#__beseated_chauffeur_booking', 'a'));

		$query->select('b.chauffeur_name')
			->join('LEFT','#__beseated_chauffeur AS b ON b.chauffeur_id=a.chauffeur_id');

		$query->select('d.service_name,d.service_type')
			->join('LEFT','#__beseated_chauffeur_services AS d ON d.service_id=a.service_id');

		$query->select('c.full_name')
			->join('LEFT','#__beseated_user_profile AS c ON c.user_id=a.user_id');

		$search = $this->state->get('filter.search','');
		if(!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$searchArray = array();
			$searchArray[] = 'c.full_name LIKE ' . $like;
			$searchArray[] = 'b.chauffeur_name LIKE ' . $like;
			$searchArray[] = 'd.service_type LIKE ' . $like;
			$searchArray[] = 'a.pickup_location LIKE ' . $like;
			$searchArray[] = 'a.dropoff_location LIKE ' . $like;
			$searchArray[] = 'a.booking_currency_code LIKE ' . $like;
			$searchArray[] = 'a.chauffeur_booking_id LIKE ' . $like;
			$searchArray[] = 'a.booking_date LIKE ' . $like;
			$searchArray[] = 'a.total_price LIKE ' . $like;

			$query->where('((' . implode(' OR ', $searchArray) .'))');
		}

		// Add the list ordering clause.
		//$orderCol	= $this->state->get('list.ordering', 'a.time_stamp');
		//$orderDirn 	= $this->state->get('list.direction', 'desc');

		$fullordering = $this->state->get('list.fullordering', '');

		if(empty($fullordering))
		{
			$fullordering = "a.time_stamp ASC";
		}

		$orderArray = explode(" ", $fullordering);
		$ordering = $orderArray[0];
		$direction = $orderArray[1];
		$query->order($db->escape($ordering) . ' ' . $db->escape($direction));

		return $query;
	}
}
