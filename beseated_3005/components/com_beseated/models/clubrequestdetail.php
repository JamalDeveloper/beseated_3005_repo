<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * The Besaeted Club Request Detail Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubRequestDetail extends JModelList
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
		parent::__construct($config);
	}

	public function getClubBooking()
	{
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$requestID = $input->get('request_id',0,'int');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('vb.*')
			->from($db->quoteName('#__beseated_venue_table_booking','vb'))
			->where($db->quoteName('vb.venue_table_booking_id') . ' = ' . $db->quote($requestID));

			$query->select('vt.table_name,vt.premium_table_id,vt.min_price,vt.capacity')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

		$query->select('bs.status_display AS status_text')
			->join('LEFT','#__beseated_status AS bs ON bs.status_id=vb.venue_status');

		$query->select('bus.status_display AS user_status_text')
			->join('LEFT','#__beseated_status AS bus ON bus.status_id=vb.user_status');

		$query->select('v.venue_name,v.location,v.currency_code,v.currency_sign,v.description,v.avg_ratting,v.working_days')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

		$query->select('img.thumb_image,img.image')
			->join('LEFT','#__beseated_element_images AS img ON img.element_id=vb.venue_id');

		$query->select('u.name')
			->join('LEFT','#__users AS u ON u.id=vb.user_id');

		$query->select('bu.full_name,bu.phone')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=vb.user_id');

		$query->order($db->quoteName('vb.booking_date') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$bookings = $db->loadObject();

		if(!$bookings)
		{
			return array();
		}

		return $bookings;
	}

	public function summaryForVenue($venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('count(venue_status) as total_count,venue_status')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('deleted_by_venue') . ' = ' . $db->quote('0'))
			/*->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'))*/
			->group($db->quoteName('venue_status'));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	public function getRevenueForVenue($venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('sum(bill_post_amount) as revenue')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			/*->where($db->quoteName('venue_status') . ' <> ' . $db->quote(10))
			->where($db->quoteName('user_status') . ' <> ' . $db->quote(10))*/
			->where($db->quoteName('venue_table_booking_id') . ' IN ( ' . implode(",", $resultAllPaidBookingIDs) . ' ) ');


		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadResult();


		return $result;
	}

	public function changeRequestStatus($requestID,$status,$owner_message)
	{
		$tblVenueBooking = JTable::getInstance('Venuebooking','BeseatedTable',array());
		$tblVenue        = JTable::getInstance('Venue','BeseatedTable',array());
		$tblTable        = JTable::getInstance('Table','BeseatedTable',array());
		$tblVenueBooking->load($requestID);
		$tblVenue->load($tblVenueBooking->venue_id);
		$tblTable->load($tblVenueBooking->venue_table_booking_id);
		if($status == 'cancel')
		{
			$tblVenueBooking->venue_status = BeseatedHelper::getStatusIDFromStatusName('Decline');
			$tblVenueBooking->user_status  = BeseatedHelper::getStatusIDFromStatusName('Decline');
		}
		else if ($status == 'ok')
		{
			$currentBookingDate = $tblVenueBooking->booking_date;
			$datesArray         = array();
			$datesArray[]       = date('Y-m-d', strtotime($currentBookingDate .' -1 day'));
			$datesArray[]       = $tblVenueBooking->booking_date;
			$datesArray[]       = date('Y-m-d', strtotime($currentBookingDate .' +1 day'));

			/*$currentBookingFrom = $tblVenueBooking->booking_from_time;
			$currentBookingTo   = $tblVenueBooking->booking_to_time;*/

			/*if (strtotime($currentBookingFrom)>strtotime($currentBookingTo))
			{
				$currentBookingFrom = date('Y-m-d H:i:s', strtotime($currentBookingDate .' '.$currentBookingFrom));
				$currentBookingTo   = date('Y-m-d H:i:s', strtotime($currentBookingDate .' '.$currentBookingTo.' +1 day'));
			} else {
				$currentBookingFrom = date('Y-m-d H:i:s', strtotime($currentBookingDate .' '.$currentBookingFrom));
				$currentBookingTo   = date('Y-m-d H:i:s', strtotime($currentBookingDate .' '.$currentBookingTo));
			}*/

			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($db->quoteName('#__beseated_venue_table_booking'))
				->where($db->quoteName('venue_id') . ' = ' . $db->quote($tblVenueBooking->venue_id))
				->where($db->quoteName('table_id') . ' = ' . $db->quote($tblVenueBooking->table_id))
				->where($db->quoteName('venue_table_booking_id') . ' <> ' . $db->quote($tblVenueBooking->venue_table_booking_id))
				->where($db->quoteName('venue_status') . ' = ' . $db->quote(5))
				->where($db->quoteName('booking_date') . ' IN (\''. implode("','", $datesArray) .'\')' );

			// Set the query and load the result.
			$db->setQuery($query);

			$slotBooked         = 0;
			$bookingsOnSameDate = $db->loadObjectList();
			$timeSlots          = array();

			foreach ($bookingsOnSameDate as $key => $booking)
			{
				$bookingDate = $booking->booking_date;
				/*$bookingFrom = $booking->booking_time;*/
				/*$bookingTo   = $booking->booking_to_time;*/

				/*if (strtotime($bookingDate.' '.$bookingFrom)>strtotime($bookingDate.' '.$bookingTo)) {
					$bookingFrom = date('Y-m-d H:i:s', strtotime($bookingDate .' '.$bookingFrom));
					$bookingTo   = date('Y-m-d H:i:s', strtotime($bookingDate .' '.$bookingTo.' +1 days'));
				} else {
					$bookingFrom = date('Y-m-d H:i:s', strtotime($bookingDate .' '.$bookingFrom));
					$bookingTo   = date('Y-m-d H:i:s', strtotime($bookingDate .' '.$bookingTo));
				}*/
/*
				$numbercurrentBookingFrom = strtotime($currentBookingFrom);
				$numbercurrentBookingTo   = strtotime($currentBookingTo);
				$numberbookingFrom        = strtotime($bookingFrom);
				$numberbookingTo          = strtotime($bookingTo);

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
				}*/
			}

			$tblVenueBooking->venue_status = BeseatedHelper::getStatusIDFromStatusName('Awaiting Payment');
			$tblVenueBooking->user_status  = BeseatedHelper::getStatusIDFromStatusName('Available');
		}

	
		if(isset($slotBooked))
		{
			return 3;
		}

		if(!$tblVenueBooking->store())
		{
			return 0;
		}

		if($status == 'ok')
		{
			$userProfile = BeseatedHelper::getUserElementID($tblVenueBooking->user_id);
			$joomlaUser  = JFactory::getUser($tblVenueBooking->user_id);
			$email       = $joomlaUser->email;
			$subject     = JText::_('COM_BESEATED_VENUE_BOOKING_STATUS_CHANGED_ACCEPTED_EMAIL_SUBJECT');
			$venueName   = $tblVenue->venue_name;

			if($tblTable->premium_table_id)
				$tableName = $tblTable->table_name;
			else
				$tableName = $tblTable->table_name;

			$bookingDate = date('d-m-Y',strtotime($tblVenueBooking->booking_date));
			$bookingTime = BeseatedHelper::convertToHM($tblVenueBooking->booking_time);
			$imgPath     = JUri::base().'images/email-footer-logo.png';
			$imageLink   = '<img src="'.$imgPath.'" alt="Beesated"/>';
			$body        = JText::sprintf('COM_BESEATED_VENUE_BOOKING_STATUS_CHANGED_ACCEPTED_EMAIL_BODY',$joomlaUser->name, $venueName, $tableName, $bookingDate, $bookingTime, $imageLink);

			BeseatedHelper::sendEmail($email,$subject,$body);
		}
		else if($status == 'cancel')
		{
			$userProfile = BeseatedHelper::getUserElementID($tblVenueBooking->user_id);
			$joomlaUser  = JFactory::getUser($tblVenueBooking->user_id);
			$email       = $joomlaUser->email;
			$subject     = JText::_('COM_BESEATED_VENUE_BOOKING_STATUS_CHANGED_DECLINED_EMAIL_SUBJECT');
			$venueName   = $tblVenue->venue_name;

			if($tblTable->premium_table_id)
				$tableName = $tblTable->table_name;
			else
				$tableName = $tblTable->table_name;

			$bookingDate = date('d-m-Y',strtotime($tblVenueBooking->booking_date));
			$bookingTime = BeseatedHelper::convertToHM($tblVenueBooking->booking_time);
			$imgPath     = JUri::base().'images/email-footer-logo.png';
			$imageLink   = '<img src="'.$imgPath.'" alt="Beesated"/>';
			$body        = JText::sprintf('COM_BESEATED_VENUE_BOOKING_STATUS_CHANGED_DECLINED_EMAIL_BODY',$joomlaUser->name, $venueName, $tableName, $bookingDate, $bookingTime, $imageLink);

			BeseatedHelper::sendEmail($email,$subject,$body);
		}
		else if($status == 'waiting')
		{
			$userProfile = BeseatedHelper::getUserElementID($tblVenueBooking->user_id);
			$joomlaUser  = JFactory::getUser($tblVenueBooking->user_id);
			$email       = $joomlaUser->email;
			$subject     = JText::_('COM_BESEATED_VENUE_BOOKING_STATUS_CHANGED_WAITINGLIST_EMAIL_SUBJECT');
			$venueName   = $tblVenue->venue_name;

			if($tblTable->premium_table_id)
				$tableName = $tblTable->venue_table_name;
			else
				$tableName = $tblTable->custom_table_name;

			$bookingDate = date('d-m-Y',strtotime($tblVenueBooking->venue_booking_datetime));
			$bookingTime = BeseatedHelper::convertToHM($tblVenueBooking->booking_from_time);
			$imgPath     = JUri::base().'images/email-footer-logo.png';
			$imageLink   = '<img src="'.$imgPath.'" alt="Beesated"/>';
			$body        = JText::sprintf('COM_BESEATED_VENUE_BOOKING_STATUS_CHANGED_WAITINGLIST_EMAIL_BODY',$joomlaUser->name, $tableName, $venueName, $imageLink);

			BeseatedHelper::sendEmail($email,$subject,$body);
		}

		BeseatedHelper::defineBeseatedAppConfig();
		$tblVenue = JTable::getInstance('Venue','BeseatedTable',array());
		$tblVenue->load($tblVenueBooking->venue_id);
		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/bcted/helper.php';
		$appHelper                             = new bctedAppHelper;
		$bookingData                           = $appHelper->getBookingDetailForVenueTable($requestID,'User');
		$pushcontentdata                       = array();
		$pushcontentdata['data']               = $bookingData;
		$pushcontentdata['elementType']        = "venue";
		$pushOptions['detail']['content_data'] = $pushcontentdata;
		$pushOptions                           = gzcompress(json_encode($pushOptions));
		$db           = JFactory::getDbo();
		$obj          = new stdClass();
		$obj->id      = null;
		$obj->detail  = $pushOptions;
		$obj->tocount = 1;
		$db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');
		if($obj->id)
		{
			$userProfile    = $appHelper->getUserProfile($tblVenueBooking->user_id);
			$updateMyBookingStatus = 0;
			if($userProfile)
			{
				$params = json_decode($userProfile->params);
				$updateMyBookingStatus = $params->settings->pushNotification->updateMyBookingStatus;
			}
			if($updateMyBookingStatus)
			{
				$message = JText::sprintf('PUSHNOTIFICATION_TYPE_REQUESTSTATUSCHANGED_MESSAGE',$tblVenue->venue_name);
				$jsonarray['pushNotificationData']['id']         = $obj->id;
				$jsonarray['pushNotificationData']['to']         = $tblVenueBooking->user_id;
				$jsonarray['pushNotificationData']['message']    = $message;
				$jsonarray['pushNotificationData']['type']       = JText::_('PUSHNOTIFICATION_TYPE_REQUESTSTATUSCHANGED'); //'RequestStatusChanged';
				$jsonarray['pushNotificationData']['configtype'] = '';
				BeseatedHelper::sendPushNotification($jsonarray);
			}
		}

		return 1;
	}
}
