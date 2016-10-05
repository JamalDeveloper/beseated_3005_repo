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
 * Beesated Event Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerVenueBooking extends JControllerForm
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 */
	public function getVenueServcies()
	{
		$venue_id = JRequest::getInt('venue_id');

		$this->getServiceList($venue_id);

		$options  =array();
		$services = $this->serviceList;

		if ($services)
		{
			foreach ($services as $key => $service)
			{
				$options[] = JHtml::_('select.option', $service->table_id, $service->table_name);
			}
		}

		//echo "<pre/>";print_r($options);exit;

		$dropdown = JHTML::_('select.genericlist', $options, 'jform[table_id]', 'class="inputbox service_name_chzn"', 'value', 'text', 1);

		echo $dropdown;
		exit;
	}

	/**
	 * Get list of mail's type to display
	 *
	 * @return  [type]  [description]
	 */
	public function getServiceList($venue_id)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select(
			array(
				'table_id',
				'table_name',

			)
		);
			$query->from($db->quoteName('#__beseated_venue_table'))
			     ->where($db->quoteName('venue_id') . ' = ' . $db->quote($venue_id));

		// Set the query and load the result.
		$db->setQuery((string) $query);

		try
		{
			$this->serviceList = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}
	}

	public function isDayClub()
	{
		$venue_id = JRequest::getInt('venue_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('is_day_club')
		      ->from($db->quoteName('#__beseated_venue'))
		      ->where($db->quoteName('venue_id') . ' = ' . $db->quote($venue_id))
		      ->where($db->quoteName('has_table') . ' = ' . $db->quote('1'))
			  ->where($db->quoteName('published') . ' = ' . $db->quote('1'))
			  ->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'));

		// Set the query and load the result.
		$db->setQuery($query);
		$isDayClub = $db->loadResult();

		echo $isDayClub;
		exit;

	}

	public function getTableCapacity()
	{
		$table_id = JRequest::getInt('table_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('capacity')
			->from($db->quoteName('#__beseated_venue_table'))
			->where($db->quoteName('table_id') . ' = ' . $db->quote($table_id));

		// Set the query and load the result.
		$db->setQuery($query);
		$totalGuest = $db->loadResult();

		echo $totalGuest;
		exit;


	}

	public function checkForVenueTableAvaibility()
	{
		$venueID     = JRequest::getInt('venue_id');
		$tableID     = JRequest::getInt('table_id');
		$is_day_club = JRequest::getInt('is_day_club');
		$date        = JRequest::getString('booking_date');
		$fromTime    = JRequest::getString('booking_time');
		$toTime      = '';

		if(!$is_day_club)
		{
			$fromTime = '00:00';
		}

		$currentBookingDate = $date;
		$datesArray         = array();

		if($is_day_club)
		{
			$datesArray[]       = date('Y-m-d', strtotime($currentBookingDate .' -1 day'));   // 6-04-16
			$datesArray[]       = date('Y-m-d', strtotime($currentBookingDate .' +1 day'));  // 8-04-16
		}
		else
		{
			$datesArray[]       = date('Y-m-d', strtotime($currentBookingDate));
		}

		$datesArray[]       =  date('Y-m-d', strtotime($date));

		$currentBookingFrom = $fromTime;
		$currentBookingTo   = $toTime;

		$bookingStatus[] = $this->getStatusID('booked');
		$bookingStatus[] = $this->getStatusID('confirmed');

		if(!$toTime)
		{
			$currentBookingFromTS = date('Y-m-d H:i:s', strtotime($currentBookingFrom));
			$currentBookingTo= date('H:i:s', strtotime($currentBookingFromTS . ' + 2 hours'));
		}

		$currentBookingFrom = $this->convertToHMS($currentBookingFrom);
		$currentBookingTo = $this->convertToHMS($currentBookingTo);



		if (strtotime($currentBookingFrom)>strtotime($currentBookingTo))
		{
			$currentBookingFrom = date('Y-m-d H:i:s', strtotime($currentBookingDate .' '.$currentBookingFrom));
			$currentBookingTo = date('Y-m-d H:i:s', strtotime($currentBookingDate .' '.$currentBookingTo.' +1 day'));
		} else {
			$currentBookingFrom = date('Y-m-d H:i:s', strtotime($currentBookingDate .' '.$currentBookingFrom));
			$currentBookingTo = date('Y-m-d H:i:s', strtotime($currentBookingDate .' '.$currentBookingTo));
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('table_id') . ' = ' . $db->quote($tableID))
			//->where('(('.$db->quoteName('venue_status') . ' <> '. $db->quote('6') . ' AND '.$db->quoteName('user_status') . ' <> '. $db->quote('8').'))')
			->where($db->quoteName('venue_status') . ' IN ('.implode(',', $bookingStatus).')')
			->where($db->quoteName('booking_date') . ' IN (\''. implode("','", $datesArray) .'\')' );
			// ->where($db->quoteName('venue_booking_id') . ' <> ' . $db->quote($tblVenuebooking->venue_booking_id))


		// Set the query and load the result.
		$db->setQuery($query);

		$slotBooked = 0;
		$bookingsOnSameDate = $db->loadObjectList();



		$timeSlots = array();

		foreach ($bookingsOnSameDate as $key => $booking)
		{
			$bookingDate = $booking->booking_date;
			$bookingFrom = $booking->booking_time;
			$bookingTo = date('H:i:s', strtotime($bookingDate.' '.$bookingFrom . ' + ' . $booking->total_hours . ' hours'));

			if (strtotime($bookingDate.' '.$bookingFrom)>strtotime($bookingDate.' '.$bookingTo))
			{
				$bookingFrom = date('Y-m-d H:i:s', strtotime($bookingDate .' '.$bookingFrom));
				$bookingTo = date('Y-m-d H:i:s', strtotime($bookingDate .' '.$bookingTo.' +1 days'));
			}
			else
			{
				$bookingFrom = date('Y-m-d H:i:s', strtotime($bookingDate .' '.$bookingFrom));
				$bookingTo = date('Y-m-d H:i:s', strtotime($bookingDate .' '.$bookingTo));
			}

			$numbercurrentBookingFrom = strtotime($currentBookingFrom);  // 2016-04-07 00:01:01
			$numbercurrentBookingTo   = strtotime($currentBookingTo);    // 2016-04-07 02:01:01
			$numberbookingFrom        = strtotime($bookingFrom);         // 2016-04-06 22:30:00
			$numberbookingTo          = strtotime($bookingTo);           // 2016-04-07 00:30:00


			if(($numberbookingFrom < $numbercurrentBookingFrom) && ($numbercurrentBookingFrom < $numberbookingTo))
			{
				$slotBooked = $slotBooked + 1;
			}
			else if(($numberbookingFrom < $numbercurrentBookingTo) && ($numbercurrentBookingTo < $numberbookingTo))
			{
				$slotBooked = $slotBooked + 1;
			}
			else if(($numberbookingTo == $numbercurrentBookingTo) && ($numbercurrentBookingFrom == $numberbookingFrom))
			{
				$slotBooked = $slotBooked + 1;
			}

		}

		if($slotBooked)
		{
			echo 0;exit;
		}

		echo 1;exit;
	}

	public function getStatusID($statusName)
	{
		if(empty($statusName))
		{
			return 0;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_status'))
			->where($db->quoteName('status_name') . ' = ' . $db->quote($statusName));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result->status_id;

	}

	public function convertToHMS($time)
	{
		$timeFormat = explode(":", $time);

		if(count($timeFormat)==1)
		{
			$hmsTime = $timeFormat[0].":"."00:00";
		}
		else if(count($timeFormat)==2)
		{
			$hmsTime = $timeFormat[0].":".$timeFormat[1].":00";
		}
		else if(count($timeFormat)>=3)
		{
			$hmsTime = $timeFormat[0].":".$timeFormat[1].":".$timeFormat[2];
		}

		return $hmsTime;
	}


}
