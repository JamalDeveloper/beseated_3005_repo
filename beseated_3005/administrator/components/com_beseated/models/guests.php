<?php
/**
 * @package     Bcted.Administrator
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Bcted Bookings Model
 *
 * @since  0.0.1
 */
class BeseatedModelGuests extends JModelList
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
			/*$config['filter_fields'] = array(
				'full_name','full_name',
				'email','email',
				'city','city',
				'phone','phone',
				'birthdate','birthdate',
				'lastvisitDate','lastvisitDate',
				'registerDate','registerDate'
			);*/
		}

		$this->total = 0;
		parent::__construct($config);
	}

	public function getLiveUserID()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('id')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('block') . ' = ' . $db->quote('0'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadColumn();

		return $result;
	}

	protected function getListQuery()
	{
		/*echo "<pre>";
		print_r($this->state);
		echo "</pre>";*/
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Filter: like / search
		$search = $this->getState('filter.search');

		/*echo "<pre>";
		print_r($this->state);
		echo "</pre>";*/

		$liveUSers = $this->getLiveUserID();
		$liveUSersStr = implode(",", $liveUSers);

		// Create the base select statement.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_user_profile','a'))
			->where($db->quoteName('a.user_type') . ' = ' . $db->quote('beseated_guest'))
			->where($db->quoteName('a.user_id') . ' IN ('.$liveUSersStr.')');

		$query->select('b.*')
			->join('LEFT','#__users AS b ON b.id=a.user_id');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('(b.name LIKE ' . $like.') OR (b.email LIKE '.$like .') OR (a.city LIKE '.$like .') OR (a.phone LIKE '.$like .')');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'b.name');
		$orderDirn 	= $this->state->get('list.direction', 'ASC');

		$fullordering = $this->state->get('list.fullordering', '');

		if(!empty($fullordering))
		{
			$query->order($db->escape($fullordering));
		}
		else
		{
			$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
		}

		return $query;
	}

	function getGuestListDetail()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Filter: like / search
		$search = $this->getState('filter.search');

		$liveUSers = $this->getLiveUserID();
		$liveUSersStr = implode(",", $liveUSers);

		// Create the base select statement.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_user_profile','a'))
			->where($db->quoteName('a.is_deleted') . ' = ' . $db->quote('0'))
			//->where($db->quoteName('a.user_type') . ' = ' . $db->quote('beseated_guest'))
			->where($db->quoteName('a.user_type') . ' = ' . $db->quote('beseated_guest'));
			//->where($db->quoteName('a.user_id') . ' IN ('.$liveUSersStr.')');

				//echo $query;exit;

		$query->select('b.*')
			->join('INNER','#__users AS b ON b.id=a.user_id');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('((b.name LIKE ' . $like.') OR (b.email LIKE '.$like .') OR (a.city LIKE '.$like .') OR (a.phone LIKE '.$like .'))');
			//$query->where('((a.event_name LIKE ' . $like.') OR (a.location LIKE '.$like .') OR (a.city LIKE '.$like .'))');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'b.name');
		$orderDirn 	= $this->state->get('list.direction', 'ASC');

		$fullordering = $this->state->get('list.fullordering', '');

		/*if(!empty($fullordering))
		{
			$query->order($db->escape($fullordering));
		}*/

		/*else
		{
			$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
		}*/

		// Set the query and load the result.
		$db->setQuery($query);
		$guestDetails = $db->loadObjectList();

		$this->total = count($guestDetails);

		foreach ($guestDetails as $key => $guestDetail)
		{
			$totalLoyaltyPoints = $this->get_user_sum_of_loyalty_point($guestDetail->user_id);
			$tier_name          = $this->get_tier_name_from_loyalty_point($totalLoyaltyPoints);
			$totalBookings      = $this->getTotalBookingAndAmount($guestDetail->user_id);


			$guestDetail->totalLoyaltyPoint = $totalLoyaltyPoints;
			$guestDetail->tier_name         = $tier_name;
			$guestDetail->totalBookings     = $totalBookings[0];
			$guestDetail->totalAmount       = $totalBookings[1];
		//	$guestDetail->totalAmount = 1;
			//$guestDetail->totalBookings =  1;

		}

		$limit     = ($this->state->get('list.limit')) ? $this->state->get('list.limit') : 10000;
		$start     = $this->state->get('list.start');



		$fullordering = $this->state->get('list.fullordering');

		if(empty($fullordering))
		{
			$fullordering = "full_name ASC";
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



		$this->array_sort_by_column($guestDetails,$ordering,$sortingOrder);
		$guestDetails = array_slice($guestDetails, $start, $limit);

		return $guestDetails;
	}

	public function get_user_sum_of_loyalty_point($userID)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('sum(earn_point) AS loyalty_points')
			->from($db->quoteName('#__beseated_loyalty_point'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('is_valid') . ' = ' . $db->quote(1));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadResult();

		if(!$result)
		{
			$result = 0.00;
		}

		return $result;
	}

	public function get_tier_name_from_loyalty_point($totalLoyaltyPoint)
	{

		// Initialiase variables.
		$db    = JFactory::getDbo();

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('params')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' = ' . $db->quote('com_beseated'));

		// Set the query and load the result.
		$db->setQuery($query);
		$params = $db->loadResult();

		$params = json_decode($params);

		$tier_name ='';

		if($totalLoyaltyPoint == '0')
		{
			$tier_name = 'No Tier';
		}
		elseif($totalLoyaltyPoint >= $params->loyalty_level_lowest_min && $totalLoyaltyPoint <= $params->loyalty_level_lowest_max)
		{
			$tier_name = $params->loyalty_tier_name_lowest;
		}
		elseif($totalLoyaltyPoint >= $params->loyalty_level_middle_min && $totalLoyaltyPoint <= $params->loyalty_level_middle_max)
		{
			$tier_name = $params->loyalty_tier_name_middle;
		}
		elseif($totalLoyaltyPoint >= $params->loyalty_level_highest)
		{
			$tier_name = $params->loyalty_tier_name_highest;
		}

		return $tier_name;
	}

	/*
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState($ordering = 'id', $direction = 'ASC')
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$value = $app->input->get('limit', $app->getCfg('list_limit', 0), 'uint');
		$this->setState('list.limit', 10);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);

		$value = $app->input->get('listdirection', 'ASC', 'string');
		$this->setState('list.direction', $value);

		/*$searchdate = $this->getUserStateFromRequest($this->context . '.filter.selecteddate', 'filter_selecteddate');
		$this->setState('filter.selecteddate', $searchdate);*/

		//$orderCol = $app->input->get('filter_order', 'a.company_id');

		//if (!in_array($orderCol, $this->filter_fields))
		//{
			//$orderCol = 'a.company_id';
		//}

		//$this->setState('list.ordering', $orderCol);

		/*$listOrder = $app->input->get('filter_order_Dir', 'ASC');

		$this->setState('layout', $app->input->getString('layout'));*/

		// List state information.
		parent::populateState();

	}

	public function getTotalBookingAndAmount($user_id)
	{
		$statusArray = array();
		$statusArray[] = $this->getStatusIDFromStatusName('booked');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('yb.total_price,yb.booking_currency_code')
			->from($db->quoteName('#__beseated_yacht_booking') . ' AS yb')
			->where($db->quoteName('yb.user_id') . ' = ' . $db->quote($user_id))
			->where($db->quoteName('yb.yacht_status') . ' IN ('.implode(",", $statusArray).')')
			->join('LEFT','#__beseated_yacht AS y ON y.yacht_id=yb.yacht_id')
			->join('LEFT','#__beseated_yacht_services AS ys ON ys.service_id=yb.service_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resYachtBookings = $db->loadObjectList();

		$amountInUSD = '0';
		foreach ($resYachtBookings as $key => $yachtBooking)
		{
			$amountInUSD += $this->convertCurrencyGoogle($yachtBooking->total_price,$yachtBooking->booking_currency_code,'USD');
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('cb.total_price,cb.booking_currency_code')
			->from($db->quoteName('#__beseated_chauffeur_booking') . ' AS cb')
			->where($db->quoteName('cb.user_id') . ' = ' . $db->quote($user_id))
			->where($db->quoteName('cb.chauffeur_status') . ' IN ('.implode(",", $statusArray).')')
			->join('LEFT','#__beseated_chauffeur AS c ON c.chauffeur_id=cb.chauffeur_id')
	        ->join('LEFT','#__beseated_yacht_services AS cs ON cs.service_id=cb.service_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resChauffeurBookings = $db->loadObjectList();

		foreach ($resChauffeurBookings as $key => $chauffeurBooking)
		{
			$amountInUSD += $this->convertCurrencyGoogle($chauffeurBooking->total_price,$chauffeurBooking->booking_currency_code,'USD');
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('pb.total_price,pb.booking_currency_code')
			->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
			->where($db->quoteName('pb.user_id') . ' = ' . $db->quote($user_id))
			->where($db->quoteName('pb.protection_status') . ' IN ('.implode(",", $statusArray).')')
			->join('LEFT','#__beseated_protection AS p ON p.protection_id=pb.protection_id')
			->join('LEFT','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resProtectionBookings = $db->loadObjectList();

		foreach ($resProtectionBookings as $key => $protectionBooking)
		{
			$amountInUSD += $this->convertCurrencyGoogle($protectionBooking->total_price,$protectionBooking->booking_currency_code,'USD');
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('vb.total_price,vb.booking_currency_code')
			->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vb')
			->where($db->quoteName('vb.user_id') . ' = ' . $db->quote($user_id))
			->where($db->quoteName('vb.venue_status') . ' IN ('.implode(",", $statusArray).')')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id')
			->join('LEFT','#__beseated_venue_premium_table AS vpt ON vpt.premium_id=vt.premium_table_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resVenueBookings = $db->loadObjectList();

		foreach ($resVenueBookings as $key => $venueBooking)
		{
			$amountInUSD += $this->convertCurrencyGoogle($venueBooking->total_price,$venueBooking->booking_currency_code,'USD');
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('tb.total_price,tb.booking_currency_code')
			->from($db->quoteName('#__beseated_event_ticket_booking','tb'))
			->where($db->quoteName('tb.user_id') . ' = ' . $db->quote($user_id))
			->where($db->quoteName('tb.status') . ' IN ('.implode(",", $statusArray).')')
			->join('LEFT','#__beseated_event AS e ON e.event_id=tb.event_id')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=tb.user_id');

		$db->setQuery($query);
		$resTicketBookings = $db->loadObjectList();

		foreach ($resTicketBookings as $key => $ticketBooking)
		{
			$amountInUSD += $this->convertCurrencyGoogle($ticketBooking->total_price,$ticketBooking->booking_currency_code,'USD');
		}

		$totalBookings = count($resYachtBookings) + count($resChauffeurBookings) + count($resProtectionBookings) + count($resVenueBookings) + count($resTicketBookings);

		$BookingsDetail = array();
		$BookingsDetail[] = $totalBookings;
		$BookingsDetail[] = $amountInUSD;

		return $BookingsDetail;


	}

	function getStatusIDFromStatusName($statusName)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('status_id')
			->from($db->quoteName('#__beseated_status'))
			->where($db->quoteName('status_name') . ' = ' . $db->quote($statusName));


		// Set the query and load the result.
		$db->setQuery($query);
		$status_id = $db->loadResult();

		return $status_id;
	}

	public function convertCurrencyGoogle($amount = 1, $from, $to)
	{
		$url  = "https://www.google.com/finance/converter?a=$amount&from=$from&to=$to";
		$data = file_get_contents($url);
		preg_match("/<span class=bld>(.*)<\/span>/",$data, $converted);

		$converted = preg_replace("/[^0-9.]/", "", @$converted[1]);

		return round($converted, 2);
	}

	function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
	{

		$sort_col = array();

		foreach ($arr as $key=> $row)
		{
			$sort_col[$key] = $row->$col;
		}

		//echo "<pre/>";print_r($dir);
		array_multisort($sort_col, $dir, $arr);
	}

	public function getPagination()
	{
		jimport('joomla.html.pagination');
		$this->_pagination = new JPagination($this->total, $this->state->get('list.start'), $this->state->get('list.limit') );
		return $this->_pagination;
	}




}
