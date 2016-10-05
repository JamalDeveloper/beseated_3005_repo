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
 * Beseated Booking Requests Model
 *
 * @since  0.0.1
 */
class BeseatedModelVenueBookingRequests extends JModelList
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
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'full_name','full_name',
				'status','status',
				'booking_type','booking_type'
			);
		}
		$this->total = 0;
		parent::__construct($config);
	}



	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	public function getAllRequests()
	{
		require_once JPATH_SITE . '/components/com_ijoomeradv/extensions/beseated/helper.php';

		$this->helper            = new beseatedAppHelper;
		$statusArray = array();
		$statusArray[] = $this->helper->getStatusID('booked');
		$statusArray[] = $this->helper->getStatusID('confirmed');

		$db    = JFactory::getDbo();
		$allRequest = array();
		$search = $this->state->get('filter.search','');

		// venue booking requests
		$query = $db->getQuery(true);
		$query->select('a.venue_table_booking_id,a.venue_status,a.request_date_time,a.response_date_time,a.booking_date')
			->where($db->quoteName('a.user_status') . ' NOT IN ('.implode(",", $statusArray).')')
			->from($db->quoteName('#__beseated_venue_table_booking', 'a'));
		$query->select('b.user_id,b.full_name')
			->join('LEFT','#__beseated_user_profile AS b ON b.user_id=a.user_id');
		$query->select('c.venue_name')
			->join('LEFT','#__beseated_venue AS c ON c.venue_id=a.venue_id');
		$query->select('d.table_name')
			->join('LEFT','#__beseated_venue_table AS d ON d.table_id=a.table_id');

		if(!empty($search))
		{
			$query->where('(('.
				$db->quoteName('b.full_name') .' LIKE '.$db->quote('%'.$search.'%').' OR '.
				$db->quoteName('c.venue_name') .' LIKE '.$db->quote('%'.$search.'%').' OR '.
				$db->quoteName('d.table_name') .' LIKE '.$db->quote('%'.$search.'%')
			.'))');
		}

		$db->setQuery($query);
		$resvenueRequests = $db->loadObjectList();
		foreach ($resvenueRequests as $key => $request)
		{
			$tempStd = new stdClass();
			$tempStd->booking_id        = $request->venue_table_booking_id;
			$tempStd->user_id           = $request->user_id;
			$tempStd->full_name         = strtolower($request->full_name);
			$tempStd->status            = $request->venue_status;
			$tempStd->request_date_time = $request->request_date_time;
			$tempStd->respone_date_time = $request->response_date_time;
			$tempStd->booking_date = $request->booking_date;
			$tempStd->element_name      = $request->venue_name;
			$tempStd->sub_element_name  = $request->table_name;
			$tempStd->booking_type      = 'venue';

			$allRequest[] = $tempStd;
		}

		// Add the list ordering clause.
		$limit     = ($this->state->get('list.limit')) ? $this->state->get('list.limit') : 10000;
		$start     = $this->state->get('list.start');
		$fullordering = $this->state->get('list.fullordering');

		if(empty($fullordering))
		{
			$fullordering ='request_date_time Desc';
		}

		$orderArray = explode(" ", $fullordering);

		$ordering = $orderArray[0];
		$direction = $orderArray[1];

		$task =  $this->state->task;
		if(strtoupper($direction) == 'ASC')
		{
			$sortingOrder = SORT_ASC;
		}
		else
		{
			$sortingOrder = SORT_DESC;
		}

		$this->array_sort_by_column($allRequest,$ordering,$sortingOrder);

		$this->total = count($allRequest);
		$allRequest = array_slice($allRequest, $start, $limit);

		//echo "<pre/>";print_r($allRequest);exit;
		return $allRequest;
	}

	public function getPagination()
	{
		jimport('joomla.html.pagination');
		$this->_pagination = new JPagination($this->total, $this->state->get('list.start'), $this->state->get('list.limit') );
		return $this->_pagination;
	}

	function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
	{
		$sort_col = array();

		foreach ($arr as $key=> $row)
		{
			$sort_col[$key] = ucfirst($row->$col);
		}

		array_multisort($sort_col, $dir, $arr);
	}
}
