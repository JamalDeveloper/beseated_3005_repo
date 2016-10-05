<?php
/**
 * @package     iJoomerAdv.Site
 * @subpackage  com_ijoomeradv
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.helper' );
class guest
{
	private $db;
	private $IJUserID;
	private $helper;
	private $jsonarray;
	private $emailHelper;
	private $my;

	function __construct()
	{
		require_once JPATH_SITE . '/components/com_beseated/helpers/email.php';

		$this->db                = JFactory::getDBO();
		$this->mainframe         =  JFactory::getApplication ();
		$this->IJUserID          = $this->mainframe->getUserState ( 'com_ijoomeradv.IJUserID', 0 ); //get login user id
		$this->my                = JFactory::getUser ( $this->IJUserID ); // set the login user object
		$this->helper            = new beseatedAppHelper;
		$this->emailHelper       = new BeseatedEmailHelper;
		$this->jsonarray         = array();

		$task        = IJReq::getExtTask();

		if($task == 'getNotification')
		{
			$this->helper->updateNotification('notifications');
		}
		if($task == 'getRSVP')
		{
			$this->helper->updateNotification('requests');
		}
		if($task == 'getBookings')
		{
			$this->helper->updateNotification('bookings');
		}


		$notificationDetail = $this->helper->getNotificationCount();

		//$allNotificationCount =  $notificationDetail['allNotificationCount']+$notificationDetail['messageCount'];
		$allNotificationCount =  $notificationDetail['allNotificationCount'];

		$this->jsonarray['globalNotifications']['bookingsCount']        = ($notificationDetail['bookingCount']) ? (string)$notificationDetail['bookingCount']:"0";
		$this->jsonarray['globalNotifications']['requestsCount']        = ($notificationDetail['requestCount']) ?  (string)$notificationDetail['requestCount']:"0";
		$this->jsonarray['globalNotifications']['messagesCount']        = ($notificationDetail['messageCount']) ?  (string)$notificationDetail['messageCount']:"0";
		$this->jsonarray['globalNotifications']['notificationsCount']   = ($allNotificationCount) ? (string)$allNotificationCount:"0";
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"getBookings","taskData":{"bookingType":"Protection","pageNO":"0"}}
	 *
	 */
	function getBookings($newBookingType)
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingType        = IJReq::getTaskData('bookingType','', 'string');

		if(empty($bookingType))
		{
			$bookingType = $newBookingType;
		}

		if(ucfirst($bookingType) == 'Protection')
		{
			$bookings = $this->getProtectionBookings();

			//echo "<pre/>";print_r($bookings);exit;
		}
		else if (ucfirst($bookingType)  == 'Venue')
		{
			$bookings = $this->getVenueBookings();
		}
		else if (ucfirst($bookingType)  == 'Chauffeur')
		{
			$bookings = $this->getChauffeurBookings();
		}
		else if (ucfirst($bookingType)  == 'Event')
		{
			$bookings = $this->getEventBookings();

			//echo "<pre/>";print_r($bookings);exit;
		}
		else if(ucfirst($bookingType) == 'Yacht')
		{
			$bookings = $this->getYachtBookings();
		}
		else if(ucfirst($bookingType) == 'Luxury')
		{
			$yachtBookings      = $this->getYachtBookings();
			$protectionBookings = $this->getProtectionBookings();
			$chauffeurBookings  = $this->getChauffeurBookings();

			$allElementHistoryBookings  = array_merge($yachtBookings['history'],$protectionBookings['history'],$chauffeurBookings['history']);
			$allElementUpcomingBookings = array_merge($yachtBookings['upcoming'],$protectionBookings['upcoming'],$chauffeurBookings['upcoming']);

			$this->array_sort_by_column($allElementHistoryBookings,'bookingDate',$dir = SORT_ASC);
		    $this->array_sort_by_column($allElementUpcomingBookings,'bookingDate',$dir = SORT_ASC);

			$bookings = array();
			$bookings['history']  = $allElementHistoryBookings;
			$bookings['upcoming'] = $allElementUpcomingBookings;
		}
		else
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		if( count($bookings['history']) == 0 && count($bookings['upcoming']) == 0 )
		{
			$this->jsonarray['code'] = 204;
		}
		else
		{
			$this->jsonarray['code'] = 200;
			$this->jsonarray[strtolower($bookingType).'Bookings'] = $bookings;
		}

		return $this->jsonarray;
	}

	function getProtectionBookings()
	{
		$statusArray = array();
		$statusArray[] = $this->helper->getStatusID('booked');
		$statusArray[] = $this->helper->getStatusID('canceled');
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('pb.protection_booking_id,pb.protection_id,pb.service_id,pb.user_id,pb.booking_date,pb.booking_time,pb.meetup_location,pb.total_guard,pb.total_hours,pb.price_per_hours,pb.total_price,pb.user_status,pb.is_splitted,pb.has_invitation,pb.each_person_pay,pb.splitted_count,pb.remaining_amount,pb.total_split_count,pb.is_noshow')
			->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
			->where($db->quoteName('pb.user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('pb.user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('pb.has_booked') . ' = ' . $db->quote('1'))
			->where($db->quoteName('pb.deleted_by_user') . ' = ' . $db->quote(0))
			->order($db->quoteName('pb.booking_date') . ' ASC');

		$query->select('p.protection_name,p.location,p.city,p.currency_code,p.refund_policy,p.deposit_per')
			->join('LEFT','#__beseated_protection AS p ON p.protection_id=pb.protection_id');

		$query->select('ps.service_name,ps.thumb_image,ps.image')
			->join('LEFT','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=pb.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resProtectionBookings = $db->loadObjectList();

		$resProtectionShareInvitation = $this->getBookedProtectionShareInvitations();


		$resProtectionBookings = (object) array_merge((array) $resProtectionBookings, (array) $resProtectionShareInvitation);

		$protectionIDs         = array();
		$bookingINDX           = 0;
		$resultProtectionBookings  = array();
		$protectionBookingHistory  = array();
		$protectionBookingUpcoming = array();
		foreach ($resProtectionBookings as $key => $booking)
		{
			$tmpBooking                        = array();
			$tmpBooking['elementType']         = "Protection";
			$tmpBooking['elementBookingID']    = $booking->protection_booking_id;
			$tmpBooking['bookingDate']         = $this->helper->convertDateFormat($booking->booking_date);
			$tmpBooking['bookingTime']         = $this->helper->convertToHM($booking->booking_time);
			$tmpBooking['totalPrice']          = (string)$this->helper->currencyFormat('',$booking->total_price,0);
			$tmpBooking['priceToPay']          = $this->helper->currencyFormat('',$booking->total_price,0);
			$tmpBooking['currencyCode']        = $booking->currency_code;
			$tmpBooking['elementName']         = $booking->protection_name;
			$tmpBooking['serviceName']         = $booking->service_name;
			$tmpBooking['location']            = $booking->location;
			$tmpBooking['meetupLocation']      = $booking->meetup_location;
			$tmpBooking['totalGuard']          = $booking->total_guard;
			$tmpBooking['statusCode']          = $booking->user_status;
			$tmpBooking['totalHours']          = $booking->total_hours;
			$tmpBooking['pricePerHours']       = $this->helper->currencyFormat('',$booking->price_per_hours);
			$tmpBooking['image']               = ($booking->image)?JUri::base().'images/beseated/'.$booking->image:'';
			$tmpBooking['thumbImage']          = ($booking->thumb_image)?JUri::base().'images/beseated/'.$booking->thumb_image:'';
			$tmpBooking['fullName']            = $booking->full_name;
			$tmpBooking['avatar']              = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$tmpBooking['thumbAvatar']         = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
			//$tmpBooking['bookingType']         = 'booking';
			$tmpBooking['isSplitted']          = $booking->is_splitted;
			$tmpBooking['hasInvitation']       = $booking->has_invitation;
			$tmpBooking['eachPersonPay']       = $this->helper->currencyFormat('',$booking->each_person_pay);
			$tmpBooking['splittedCount']       = $booking->splitted_count;
			$tmpBooking['remainingAmount']     = $this->helper->currencyFormat('',$booking->remaining_amount);
			$tmpBooking['paidByOwner']         = (isset($booking->paidByOwner)) ? $booking->paidByOwner : '0';
			$tmpBooking['totalSplitCount']     = $booking->total_split_count;
			$tmpBooking['hasDeposit']          = '';
			$tmpBooking['depositRate']         = $booking->deposit_per;
            $tmpBooking['depositAmount']       = (string)($booking->total_price*$booking->deposit_per/100);
			$tmpBooking['isNoShow']            = $booking->is_noshow;
			$tmpBooking['refundPolicyHours']   = $booking->refund_policy;
			//$tmpBooking['maxSplitCount']       = $booking->total_split_count - 1 - (($booking->splitted_count) ?  $booking->splitted_count - 1 : 0);

			if($booking->total_split_count)
			{
				$tmpBooking['maxSplitCount']       = $booking->total_split_count - 1 - (($booking->splitted_count) ?  $booking->splitted_count - 1 : 0);
			}
			else
			{
				$tmpBooking['maxSplitCount']       = 0;
			}


			if($booking->bookedType == 'share')
			{
				$tmpBooking['bookedType']   = $booking->bookedType;
				$tmpBooking['invitationID'] =  $booking->protection_booking_split_id;
				$booking_invitation_id      =  $booking->protection_booking_split_id;
			}
			else if ($booking->bookedType == 'invitation')
			{
				$tmpBooking['bookedType']   = $booking->bookedType;
				$tmpBooking['invitationID'] =  $booking->invitation_id;
				$booking_invitation_id      =  $booking->invitation_id;
			}
			else
			{
				$tmpBooking['bookedType'] = 'booking';
				$booking_invitation_id    =  $booking->protection_booking_id;
				//$tmpBooking['invitationID']      =  $booking->invitation_id
			}

			$splitedUserCount = $this->getSplitedUserCount('Protection', $booking->protection_booking_id);

			if(!$splitedUserCount)
			{
				$tmpBooking['remainingSplitUser']  = 50 - 1;
			}
			else
			{
				if($splitedUserCount > 50)
				{
					$tmpBooking['remainingSplitUser']  = 0;
				}
				else
				{
					$tmpBooking['remainingSplitUser']  = 50 - $splitedUserCount;
				}

			}

			$tmpBooking['isRead']  = $this->helper->isReadBooking('protection',$tmpBooking['bookedType'],$booking_invitation_id);

			if($this->helper->isPastDate($booking->booking_date))
			{
				$protectionBookingHistory[] = $tmpBooking;
			}
			else
			{
				$protectionBookingUpcoming[] = $tmpBooking;
			}
		}

		$resultProtectionBookings['history'] = $protectionBookingHistory;
		$resultProtectionBookings['upcoming'] = $protectionBookingUpcoming;

		return $resultProtectionBookings;
	}
	function getVenueBookings()
	{
		$statusArray = array();
		$statusArray[] = $this->helper->getStatusID('confirmed');
		$statusArray[] = $this->helper->getStatusID('booked');
		$statusArray[] = $this->helper->getStatusID('canceled');
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('vb.venue_table_booking_id,vb.user_id,vb.venue_id,vb.table_id,vb.booking_date,vb.booking_time,vb.privacy,vb.passkey,vb.total_guest,vb.male_guest,vb.female_guest,vb.total_hours,vb.total_price,vb.user_status,vb.has_invitation,vb.each_person_pay,vb.is_splitted,vb.splitted_count,vb.remaining_amount,vb.booking_currency_code,vb.booking_currency_sign,vb.pay_by_cash_status,vb.is_bill_posted,vb.response_date_time,vb.bill_post_amount,vb.has_bottle,vb.total_bottle_price,vb.has_bottle,vb.total_split_count,vb.hasCMSBooking,vb.is_noshow')
			->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vb')
			->where($db->quoteName('vb.user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('vb.user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('vb.has_booked') . ' = ' . $db->quote('1'))
			->where($db->quoteName('vb.deleted_by_user') . ' = ' . $db->quote(0))
			->order($db->quoteName('vb.booking_date') . ' ASC');

		$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club,v.deposit_per,v.active_payments,v.refund_policy')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

		$query->select('vt.table_name,vt.premium_table_id,vt.min_price,vt.capacity,vt.thumb_image,vt.image')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

		$query->select('vpt.premium_table_name')
			->join('LEFT','#__beseated_venue_premium_table AS vpt ON vpt.premium_id=vt.premium_table_id');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=vb.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resVenueBookings = $db->loadObjectList();

		$resVenueShareInvitations = $this->getBookedVenueShareInvitations();

		$resVenueBookings = (object) array_merge((array) $resVenueBookings, (array) $resVenueShareInvitations);

		$resultVenueBookings = array();
		$venueBookingUpcoming = array();
		$venueBookingHistory = array();
		foreach ($resVenueBookings as $key => $booking)
		{
			$tmpBooking                       = array();
			$tmpBooking['venueBookingID']     = $booking->venue_table_booking_id;
			$tmpBooking['bookingDate']        = $this->helper->convertDateFormat($booking->booking_date);
			$tmpBooking['bookingTime']        = $this->helper->convertToHM($booking->booking_time);
			$tmpBooking['totalPrice']         = $this->helper->currencyFormat('',$booking->total_price,0);
			$tmpBooking['currencyCode']       = $booking->booking_currency_code;
			$tmpBooking['venueName']          = $booking->venue_name;
			$tmpBooking['location']           = ucfirst($booking->location);
			$tmpBooking['city']               = ucfirst($booking->city);
			$tmpBooking['venueType']          = ucfirst($booking->venue_type);
			$tmpBooking['dayClub']            = $booking->is_day_club;
			$tmpBooking['tableName']          = $booking->table_name;
			$tmpBooking['statusCode']         = $booking->user_status;
			$tmpBooking['totalHours']         = $booking->total_hours;
			$tmpBooking['privacy']            = $booking->privacy;
			$tmpBooking['passkey']            = $booking->passkey;
			$tmpBooking['totalGuest']         = $booking->total_guest;
			$tmpBooking['maleGuest']          = $booking->male_guest;
			$tmpBooking['femaleGuest']        = $booking->female_guest;
			$tmpBooking['hasInvitation']      = $booking->has_invitation;
			$tmpBooking['minSpend']           = $this->helper->currencyFormat('',$booking->min_price);
			$tmpBooking['capacity']           = $booking->capacity;
			$tmpBooking['thumbImage']         = ($booking->thumb_image)?JUri::base().'images/beseated/'.$booking->thumb_image:'';
			$tmpBooking['image']              = ($booking->image)?JUri::base().'images/beseated/'.$booking->image:'';
			$tmpBooking['fullName']           = $booking->full_name;
			$tmpBooking['avatar']             = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$tmpBooking['thumbAvatar']        = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
			$tmpBooking['isBillPosted']       = $booking->is_bill_posted;
			$tmpBooking['payByCashStatus']    = $booking->pay_by_cash_status;
			$tmpBooking['hasBottle']          = $booking->has_bottle;
			$tmpBooking['isSplitted']         = $booking->is_splitted;
			$tmpBooking['eachPersonPay']      = $this->helper->currencyFormat('',$booking->each_person_pay);
			$tmpBooking['splittedCount']      = $booking->splitted_count;
			$tmpBooking['remainingAmount']    = $this->helper->currencyFormat('',$booking->remaining_amount);
			$tmpBooking['paidByOwner']        = (isset($booking->paidByOwner)) ? $booking->paidByOwner : '0';
			$tmpBooking['totalSplitCount']    = $booking->total_split_count;
			$tmpBooking['totalGuard']         = '';
			$tmpBooking['hasDeposit']         = ($booking->deposit_per == '0') ? '0' : '1';
			$tmpBooking['depositRate']        = $booking->deposit_per ;
			$tmpBooking['depositAmount']      = '';
			$tmpBooking['activePayments']     = $booking->active_payments ;
			$tmpBooking['hasCMSBooking']      = $booking->hasCMSBooking ;
			$tmpBooking['isNoShow']           = $booking->is_noshow;
			$tmpBooking['refundPolicyHours']  = $booking->refund_policy;
			//$tmpBooking['maxSplitCount']      =  '';

			if($booking->is_bill_posted)
			{
				$tmpBooking['postPrice']  =  $this->helper->currencyFormat('',$booking->bill_post_amount);
			}
			else if($booking->has_bottle)
			{
				$tmpBooking['postPrice']  =  $this->helper->currencyFormat('',$booking->total_bottle_price);
			}
			else
			{
				$tmpBooking['postPrice']  = $this->helper->currencyFormat('',$booking->min_price);
			}

			if($booking->bookedType == 'share')
			{
				$tmpBooking['bookedType']   = $booking->bookedType;
				$tmpBooking['invitationID'] =  $booking->venue_table_booking_split_id;
				$booking_invitation_id      =  $booking->venue_table_booking_split_id;
			}
			else if ($booking->bookedType == 'invitation')
			{
				$tmpBooking['bookedType']   = $booking->bookedType;
				$tmpBooking['invitationID'] =  $booking->invitation_id;
				$booking_invitation_id      =  $booking->invitation_id;

			}
			else
			{
				$tmpBooking['bookedType']        = 'booking';
				$booking_invitation_id      =  $booking->venue_table_booking_id;
				//$tmpBooking['invitationID']      =  $booking->invitation_id
			}


			$splitedUserCount = $this->getSplitedUserCount('Venue', $booking->venue_table_booking_id);

			if(!$splitedUserCount)
			{
				$tmpBooking['remainingSplitUser']  = $booking->total_guest - 1;
			}
			else
			{
				if($splitedUserCount > $booking->total_guest)
				{
					$tmpBooking['remainingSplitUser']  = 0;
				}
				else
				{
					$tmpBooking['remainingSplitUser']  = $booking->total_guest - $splitedUserCount;
				}
			}

			if($booking->total_split_count)
			{
				$tmpBooking['maxSplitCount']       = $booking->total_split_count - 1 - (($booking->splitted_count) ?  $booking->splitted_count - 1 : 0);
			}
			else
			{
				$tmpBooking['maxSplitCount']       = $booking->total_guest;
			}

			if($booking->response_date_time != '0000-00-00 00:00:00'){
				$tmpBooking['remainingTime'] = strtotime($booking->response_date_time);
			}else{
				$tmpBooking['remainingTime'] = '';
			}

			if($booking->user_status !=  $this->helper->getStatusID('booked') && $booking->user_id == $this->IJUserID)
			{
				$tmpBooking['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$booking->venue_table_booking_id.'&booking_type=venue';
			}
			else{
				$tmpBooking['paymentURL'] = "";
			}

			$querySplit = $db->getQuery(true);
			$querySplit->select('split.venue_table_booking_split_id,split.user_id,split.email,split.splitted_amount,split.split_payment_status')
				->from($db->quoteName('#__beseated_venue_table_booking_split','split'))
				->where($db->quoteName('split.venue_table_booking_id') . ' = ' . $db->quote($booking->venue_table_booking_id));
			$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id')
				->order($db->quoteName('bu.full_name') . ' ASC');

			$db->setQuery($querySplit);
			$resSplits = $db->loadObjectList();

			if($resSplits)
			{
				$tempSplit = array();
				foreach ($resSplits as $key => $split)
				{
					$tempSingleSplit = array();
					$tempSingleSplit['invitationID']   = $split->venue_table_booking_split_id;
					$tempSingleSplit['fullName']       = ($split->full_name)?$split->full_name:$split->email;
					$tempSingleSplit['splittedAmount'] = $this->helper->currencyFormat('',$split->splitted_amount);
					$tempSingleSplit['statusCode']     = $split->split_payment_status;
					$tempSingleSplit['avatar']         = ($split->avatar)?$this->helper->getUserAvatar($split->avatar):'';
					$tempSingleSplit['thumbAvatar']    = ($split->thumb_avatar)?$this->helper->getUserAvatar($split->thumb_avatar):'';

					if($split->user_id == $booking->user_id)
					{
						$tmpBooking['priceToPay'] = $this->helper->currencyFormat('',$split->splitted_amount);
						if($split->split_payment_status == 7 && $booking->user_status !=  $this->helper->getStatusID('booked'))
						{
							$tmpBooking['isBookingUserPaid'] =  1;
							$tmpBooking['paymentURL'] = JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$booking->venue_table_booking_id.'&booking_type=venue';
						}
						else if($split->split_payment_status == 7)
						{
							$tmpBooking['isBookingUserPaid'] =  1;
							$tmpBooking['paymentURL'] = "";
						}
						else
						{
							$tmpBooking['isBookingUserPaid'] =  0;
							$tmpBooking['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$split->venue_table_booking_split_id.'&booking_type=venue.split';
						}
					}
					else
					{
						$tempSplit[] = $tempSingleSplit;
					}
				}
				$tmpBooking['splits'] = $tempSplit;
			}
			else{
				$tmpBooking['splits'] = array();
			}

			$tmpBooking['isRead']  = $this->helper->isReadBooking('venue',$tmpBooking['bookedType'],$booking_invitation_id);

			if($this->helper->isPastDate($booking->booking_date))
			{
				$venueBookingHistory[] = $tmpBooking;
			}
			else
			{
				$venueBookingUpcoming[] = $tmpBooking;
			}
		}

		$this->array_sort_by_column($venueBookingHistory,'bookingDate',$dir = SORT_ASC);
		$this->array_sort_by_column($venueBookingUpcoming,'bookingDate',$dir = SORT_ASC);

		$resultVenueBookings['history'] = $venueBookingHistory;
		$resultVenueBookings['upcoming'] = $venueBookingUpcoming;

		return $resultVenueBookings;
	}
	function getEventBookings()
	{
		$bookedInvitations  = $this->getBookedEventInvitations();

		$statusArray = array();
		$statusArray[] = $this->helper->getStatusID('booked');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('tb.*')
			->from($db->quoteName('#__beseated_event_ticket_booking','tb'))
			->where($db->quoteName('tb.user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('e.is_deleted') . ' = ' . $db->quote('0'))
			->where($db->quoteName('e.published') . ' = ' . $db->quote('1'))
			->where($db->quoteName('tb.status') . ' IN ('.implode(",", $statusArray).')');

		$query->select('e.event_name,e.event_desc,e.image AS event_image,e.thumb_image AS event_thumb_image,e.event_date,e.event_time,e.location,e.city')
			->join('LEFT','#__beseated_event AS e ON e.event_id=tb.event_id');
		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=tb.user_id');

		$query->order($db->quoteName('e.event_date') . ' ASC,'.$db->quoteName('e.event_time').' ASC');
		$db->setQuery($query);
		$resTicketBookings = $db->loadObjectList();

		$processIDs = array();
		$eventBookingHistory = array();
		$eventBookingUpcoming = array();

		$resTicketBookings = (object) array_merge((array) $resTicketBookings, (array) $bookedInvitations);

		foreach ($resTicketBookings as $key => $booking)
		{
			if(@$booking->bookedType != 'invitation')
			{
				$tickets = json_decode($booking->tickets_id);

				//echo "<pre/>";print_r($tickets);exit;

				$query = $db->getQuery(true);
				$query->select('ticket_id')
					->from($db->quoteName('#__beseated_event_ticket_booking_detail'))
					->where($db->quoteName('ticket_id') . ' IN ('.implode(",", $tickets).')')
					->where($db->quoteName('event_id') . ' = ' . $db->quote($booking->event_id))
					->where($db->quoteName('user_id') . ' = ' . $db->quote($booking->user_id))
					->where($db->quoteName('booking_user_id') . ' = ' . $db->quote($booking->user_id))
					->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($booking->ticket_booking_id));

				$db->setQuery($query);
				$tickets = $db->loadColumn();

				$ticketImgs = $this->getTicketsImages($tickets);
			}

			if($booking->event_id)
			{
				$tmpBooking = array();
				$tmpBooking['eventBookingID'] = $booking->ticket_booking_id;
				$tmpBooking['eventID']        = $booking->event_id;
				$tmpBooking['totalTicket']    = (string)$booking->total_ticket;
				$tmpBooking['eventName']      = $booking->event_name;
				$tmpBooking['eventDesc']      = $booking->event_desc;
				$tmpBooking['image']          = ($booking->event_image)?JUri::root().'images/beseated/'.$booking->event_image:'';
				$tmpBooking['thumbImage']     = ($booking->event_thumb_image)?JUri::root().'images/beseated/'.$booking->event_thumb_image:'';
				$tmpBooking['location']       = $booking->location;
				$tmpBooking['city']           = $booking->city;
				$tmpBooking['eventDate']      = $this->helper->convertDateFormat($booking->event_date);
				$tmpBooking['eventTime']      = $this->helper->convertToHM($booking->event_time);
				$tmpBooking['ticketPrice']    = $this->helper->currencyFormat('',$booking->ticket_price);
				$tmpBooking['currencyCode']   = $booking->booking_currency_code;
				$tmpBooking['currencySign']   = $booking->booking_currency_sign;
				$tmpBooking['totalPrice']     = (string)$this->helper->currencyFormat('',$booking->total_price);
				$tmpBooking['fullName']       = $booking->full_name;
				$tmpBooking['avatar']         = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
				$tmpBooking['thumbAvatar']    = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
				$tmpBooking['maxSplitCount']  =  '';
				$tmpBooking['totalSplitCount']     = '';
				$tmpBooking['isNoShow']            = '';
				$tmpBooking['refundPolicyHours']  = '';
				$processIDs[] = $booking->event_id;

				if(@$booking->bookedType == 'invitation')
				{

					$tmpBooking['bookedType']       = 'invitation';
					$tmpBooking['inviteID']         = $booking->invite_id;
					$tmpBooking['ticketImage']      = ($booking->ticket_image)?JUri::root().'images/beseated/'.$booking->ticket_image:'';
					$tmpBooking['ticketThumbImage'] = ($booking->ticket_thumb_image)?JUri::root().'images/beseated/'.$booking->ticket_thumb_image:'';
					$booking_invitation_id          = $booking->invite_id;
				}
				else
				{
					$tmpBooking['bookedType']   = 'booking';
					$tmpBooking['ticketImages'] = $ticketImgs;
					$booking_invitation_id      = $booking->ticket_booking_id;
				}

				$tmpBooking['isRead']  = $this->helper->isReadBooking('event',$tmpBooking['bookedType'],$booking_invitation_id);

				$checkDateTime = date('Y-m-d H:i:s',strtotime($booking->event_date.' '.$booking->event_time));

				if($this->helper->isPastDateTime($checkDateTime))
				{
					//$eventBookingHistory[$booking->event_id] = $tmpBooking;
					$eventBookingHistory[] = $tmpBooking;
				}
				else
				{
					$eventBookingUpcoming[] = $tmpBooking;
				}

			}
			/*else
			{
				if($this->helper->isPastDate($booking->event_date))
				{
					$eventBookingHistory[$booking->event_id]['ticketImages'] = array_merge($eventBookingHistory[$booking->event_id]['ticketImages'],$ticketImgs);
					$eventBookingHistory[$booking->event_id]['totalTicket']  = (string)($eventBookingHistory[$booking->event_id]['totalTicket'] + $booking->total_ticket);
					$eventBookingHistory[$booking->event_id]['ticketPrice']  = (string)($eventBookingHistory[$booking->event_id]['ticketPrice'] + $booking->ticket_price);
				}
				else
				{
					$eventBookingUpcoming[$booking->event_id]['ticketImages'] = array_merge($eventBookingUpcoming[$booking->event_id]['ticketImages'],$ticketImgs);
					$eventBookingUpcoming[$booking->event_id]['totalTicket']  = (string)($eventBookingUpcoming[$booking->event_id]['totalTicket'] + $booking->total_ticket);
					$eventBookingUpcoming[$booking->event_id]['ticketPrice']  = (string)($eventBookingUpcoming[$booking->event_id]['ticketPrice'] + $booking->ticket_price);
				}
			}*/
		}

		$this->array_sort_by_column(array_values($eventBookingHistory),'bookingDate',$dir = SORT_ASC);
		$this->array_sort_by_column(array_values($eventBookingUpcoming),'bookingDate',$dir = SORT_ASC);

		$resultEventBookings = array();
		$resultEventBookings['history'] = $eventBookingHistory;
		$resultEventBookings['upcoming'] = $eventBookingUpcoming;
		return $resultEventBookings;
	}
	function getYachtBookings()
	{
		$statusArray = array();
		$statusArray[] = $this->helper->getStatusID('booked');
		$statusArray[] = $this->helper->getStatusID('canceled');
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('yb.*')
			->from($db->quoteName('#__beseated_yacht_booking') . ' AS yb')
			->where($db->quoteName('yb.user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('yb.user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('yb.deleted_by_user') . ' = ' . $db->quote(0))
			->where($db->quoteName('yb.has_booked') . ' = ' . $db->quote('1'))
			->order($db->quoteName('yb.booking_date') . ' ASC');

		$query->select('y.yacht_name,y.location,y.city,y.deposit_per,y.refund_policy')
			->join('LEFT','#__beseated_yacht AS y ON y.yacht_id=yb.yacht_id');

		$query->select('ys.service_name,ys.service_type,ys.dock,ys.thumb_image,ys.image')
			->join('LEFT','#__beseated_yacht_services AS ys ON ys.service_id=yb.service_id');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=yb.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resYachtBookings = $db->loadObjectList();

		$resYachtShareInvitationBookings = $this->getBookedYachtShareInvitations();

		$resYachtBookings = (object) array_merge((array) $resYachtBookings, (array) $resYachtShareInvitationBookings);

		$yachtIDs         = array();
		$bookingINDX           = 0;
		$resultYachtBookings  = array();
		$yachtBookingHistory  = array();
		$yachtBookingUpcoming = array();
		foreach ($resYachtBookings as $key => $booking)
		{
			$tmpBooking                        = array();
			$tmpBooking['elementType']         = "Yacht";
			$tmpBooking['elementBookingID']    = $booking->yacht_booking_id;
			$tmpBooking['bookingDate']         = $this->helper->convertDateFormat($booking->booking_date);
			$tmpBooking['bookingTime']         = $this->helper->convertToHM($booking->booking_time);
			$tmpBooking['totalHours']          = $booking->total_hours;
			$tmpBooking['pricePerHours']       = $this->helper->currencyFormat('',$booking->price_per_hours);
			$tmpBooking['capacity']            = $booking->capacity;
			$tmpBooking['totalPrice']          = $this->helper->currencyFormat('',$booking->total_price,0);
			$tmpBooking['priceToPay']          = $this->helper->currencyFormat('',$booking->total_price,0);
			$tmpBooking['currencyCode']        = $booking->booking_currency_code;
			$tmpBooking['elementName']         = $booking->yacht_name;
			$tmpBooking['serviceName']         = $booking->service_name;
			$tmpBooking['location']            = $booking->location;
			$tmpBooking['meetupLocation']      = "";
			//$tmpBooking['totalGuard']          = $booking->total_guard;
			$tmpBooking['statusCode']          = $booking->user_status;
			$tmpBooking['image']               = ($booking->image)?JUri::base().'images/beseated/'.$booking->image:'';
			$tmpBooking['thumbImage']          = ($booking->thumb_image)?JUri::base().'images/beseated/'.$booking->thumb_image:'';
			$tmpBooking['fullName']            = $booking->full_name;
			$tmpBooking['avatar']              = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$tmpBooking['thumbAvatar']         = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
			//$tmpBooking['bookingType']         = 'booking';
			$tmpBooking['isSplitted']          = $booking->is_splitted;
			$tmpBooking['hasInvitation']       = $booking->has_invitation;
			$tmpBooking['eachPersonPay']       = $this->helper->currencyFormat('',$booking->each_person_pay);
			$tmpBooking['splittedCount']       = $booking->splitted_count;
			$tmpBooking['remainingAmount']     = $this->helper->currencyFormat('',$booking->remaining_amount);
			$tmpBooking['paidByOwner']         = (isset($booking->paidByOwner)) ? $booking->paidByOwner : '0';
			//$tmpBooking['maxSplitCount']       = $booking->total_split_count - $booking->splitted_count;
			$tmpBooking['totalSplitCount']     = $booking->total_split_count;
			$tmpBooking['totalGuard']          = '';
			$tmpBooking['hasDeposit']          = '';
			$tmpBooking['depositRate']         = $booking->deposit_per;
            $tmpBooking['depositAmount']       = (string)($booking->total_price*$booking->deposit_per/100);
			$tmpBooking['isNoShow']            = $booking->is_noshow;
			$tmpBooking['refundPolicyHours']   = $booking->refund_policy;

			if($booking->total_split_count)
			{
				$tmpBooking['maxSplitCount']       = $booking->total_split_count - 1 - (($booking->splitted_count) ?  $booking->splitted_count - 1 : 0);
			}
			else
			{
				$tmpBooking['maxSplitCount']       = 0;
			}

			if($booking->bookedType == 'share')
			{
				$tmpBooking['bookedType']   = $booking->bookedType;
				$tmpBooking['invitationID'] =  $booking->yacht_booking_split_id;
				$booking_invitation_id      =  $booking->yacht_booking_split_id;
			}
			else if ($booking->bookedType == 'invitation')
			{
				$tmpBooking['bookedType']   = $booking->bookedType;
				$tmpBooking['invitationID'] =  $booking->invitation_id;
				$booking_invitation_id      =  $booking->invitation_id;
			}
			else
			{
				$tmpBooking['bookedType'] = 'booking';
				$booking_invitation_id    =  $booking->yacht_booking_id;
				//$tmpBooking['invitationID']      =  $booking->invitation_id
			}

			$splitedUserCount = $this->getSplitedUserCount($elementType = 'Yacht' , $booking->yacht_booking_id);

			if(!$splitedUserCount)
			{
				$tmpBooking['remainingSplitUser']  = $booking->capacity - 1;
			}
			else
			{
				if($splitedUserCount > $booking->capacity)
				{
					$tmpBooking['remainingSplitUser']  = 0;
				}
				else
				{
					$tmpBooking['remainingSplitUser']  = $booking->capacity - $splitedUserCount;
				}
			}

			$tmpBooking['isRead']  = $this->helper->isReadBooking('yacht',$tmpBooking['bookedType'],$booking_invitation_id);

			if($this->helper->isPastDate($booking->booking_date))
			{
				$yachtBookingHistory[] = $tmpBooking;
			}
			else
			{
				$yachtBookingUpcoming[] = $tmpBooking;
			}
		}

		$resultYachtBookings['history'] = $yachtBookingHistory;
		$resultYachtBookings['upcoming'] = $yachtBookingUpcoming;

		return $resultYachtBookings;
	}
	function getChauffeurBookings()
	{
		$statusArray = array();
		$statusArray[] = $this->helper->getStatusID('booked');
		$statusArray[] = $this->helper->getStatusID('canceled');
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('cb.*')
			->from($db->quoteName('#__beseated_chauffeur_booking') . ' AS cb')
			->where($db->quoteName('cb.user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('cb.user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('cb.deleted_by_user') . ' = ' . $db->quote(0))
			->where($db->quoteName('cb.has_booked') . ' = ' . $db->quote('1'))
			->order($db->quoteName('cb.booking_date') . ' ASC');

		$query->select('c.chauffeur_name,c.location,c.city,c.deposit_per,c.refund_policy')
			->join('LEFT','#__beseated_chauffeur AS c ON c.chauffeur_id=cb.chauffeur_id');

		$query->select('cs.service_name,cs.service_type,cs.thumb_image,cs.image')
			->join('LEFT','#__beseated_chauffeur_services AS cs ON cs.service_id=cb.service_id');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=cb.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resChauffeurBookings = $db->loadObjectList();

		$resChauffeurShareInvitations = $this->getBookedChauffeurShareInvitations();

		$resChauffeurBookings = (object) array_merge((array) $resChauffeurBookings, (array) $resChauffeurShareInvitations);

		//echo "<pre/>";print_r($resChauffeurBookings);exit;

		$yachtIDs         = array();
		$bookingINDX           = 0;
		$resultChauffeurBookings  = array();
		$chauffeurBookingHistory  = array();
		$chauffeurBookingUpcoming = array();

		foreach ($resChauffeurBookings as $key => $booking)
		{
			$tmpBooking                     = array();
			$tmpBooking['elementType']      = "Chauffeur";
			$tmpBooking['elementBookingID'] = $booking->chauffeur_booking_id;
			$tmpBooking['bookingDate']      = $this->helper->convertDateFormat($booking->booking_date);
			$tmpBooking['bookingTime']      = $this->helper->convertToHM($booking->booking_time);
			$tmpBooking['totalHours']       = "";
			$tmpBooking['pricePerHours']    = "";
			$tmpBooking['capacity']         = $booking->capacity;
			$tmpBooking['totalPrice']       = $this->helper->currencyFormat('',$booking->total_price,0);
			$tmpBooking['priceToPay']       = $this->helper->currencyFormat('',$booking->total_price,0);
			$tmpBooking['currencyCode']     = $booking->booking_currency_code;
			$tmpBooking['elementName']      = $booking->chauffeur_name;
			$tmpBooking['serviceName']      = $booking->service_name;
			$tmpBooking['location']         = $booking->location;
			$tmpBooking['meetupLocation']   = "";

			$tmpBooking['totalGuard']       = "";
			$tmpBooking['pickupLocation']   = $booking->pickup_location;
			$tmpBooking['dropoffLocation']  = $booking->dropoff_location;

			$tmpBooking['statusCode']       = $booking->user_status;

			$tmpBooking['image']            = ($booking->image)?JUri::base().'images/beseated/'.$booking->image:'';
			$tmpBooking['thumbImage']       = ($booking->thumb_image)?JUri::base().'images/beseated/'.$booking->thumb_image:'';
			$tmpBooking['fullName']         = $booking->full_name;
			$tmpBooking['avatar']           = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$tmpBooking['thumbAvatar']      = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
			//$tmpBooking['bookingType']    = 'booking';
			$tmpBooking['isSplitted']       = $booking->is_splitted;
			$tmpBooking['hasInvitation']    = $booking->has_invitation;
			$tmpBooking['eachPersonPay']    = $this->helper->currencyFormat('',$booking->each_person_pay);
			$tmpBooking['splittedCount']    = $booking->splitted_count;
			$tmpBooking['remainingAmount']  = $this->helper->currencyFormat('',$booking->remaining_amount);
			$tmpBooking['paidByOwner']      = (isset($booking->paidByOwner)) ? $booking->paidByOwner : '0';
			$tmpBooking['totalSplitCount']  = $booking->total_split_count;
			$tmpBooking['totalGuard']       = '';
			$tmpBooking['hasDeposit']       = '';
			$tmpBooking['depositRate']      = $booking->deposit_per;
			$tmpBooking['depositAmount']    = (string)($booking->total_price*$booking->deposit_per/100);
			$tmpBooking['isNoShow']         = $booking->is_noshow;
			$tmpBooking['refundPolicyHours']   = $booking->refund_policy;


			//$tmpBooking['maxSplitCount']    = $booking->total_split_count - 1 - (($booking->splitted_count) ?  $booking->splitted_count - 1 : 0);

			if($booking->total_split_count)
			{
				$tmpBooking['maxSplitCount']       = $booking->total_split_count - 1 - (($booking->splitted_count) ?  $booking->splitted_count - 1 : 0);
			}
			else
			{
				$tmpBooking['maxSplitCount']       = 0;
			}

			if($booking->bookedType == 'share')
			{
				$tmpBooking['bookedType']   = $booking->bookedType;
				$tmpBooking['invitationID'] =  $booking->chauffeur_booking_split_id;
				$booking_invitation_id      =  $booking->chauffeur_booking_split_id;
			}
			else if ($booking->bookedType == 'invitation')
			{
				$tmpBooking['bookedType']   = $booking->bookedType;
				$tmpBooking['invitationID'] =  $booking->invitation_id;
				$booking_invitation_id      =  $booking->invitation_id;
			}
			else
			{
				$tmpBooking['bookedType'] = 'booking';
				$booking_invitation_id    =  $booking->chauffeur_booking_id;
			}

			$splitedUserCount = $this->getSplitedUserCount($elementType = 'Chauffeur' , $booking->chauffeur_booking_id);

			if(!$splitedUserCount)
			{
				$tmpBooking['remainingSplitUser']  = $booking->capacity - 1;
			}
			else
			{
				if($splitedUserCount > $booking->capacity)
				{
					$tmpBooking['remainingSplitUser']  = 0;
				}
				else
				{
					$tmpBooking['remainingSplitUser']  = $booking->capacity - $splitedUserCount;
				}

			}

			$tmpBooking['isRead']  = $this->helper->isReadBooking('chauffeur',$tmpBooking['bookedType'],$booking_invitation_id);


			if($this->helper->isPastDate($booking->booking_date))
			{
				$chauffeurBookingHistory[] = $tmpBooking;
			}
			else
			{
				$chauffeurBookingUpcoming[] = $tmpBooking;
			}
		}

		$resultChauffeurBookings['history'] = $chauffeurBookingHistory;
		$resultChauffeurBookings['upcoming'] = $chauffeurBookingUpcoming;

		return $resultChauffeurBookings;
	}
	function getTicketsImages($ticketIDs)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('image_id') . ' IN ('.implode(",", $ticketIDs).')');
		$db->setQuery($query);
		$resTicketImgs = $db->loadObjectList();
		$resultImages = array();
		foreach ($resTicketImgs as $key => $image)
		{
			$tmpBooking = array();
			$tmpBooking['ticketThumbImage'] = ($image->thumb_image)?JUri::root().'images/beseated/'.$image->thumb_image:'';
			$tmpBooking['ticketImage']      = ($image->image)?JUri::root().'images/beseated/'.$image->image:'';
			$resultImages[] = $tmpBooking;
		}

		return $resultImages;

	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"getInvitations","taskData":{"bookingType":"","bookingID":""}}
	 *
	 */
	function getInvitations()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingType = IJReq::getTaskData('bookingType','', 'string');
		$bookingID   = IJReq::getTaskData('bookingID','', 'string');
		$invitations = array();

		if(ucfirst($bookingType) == "Event")
		{
			$bookingType = 'Ticket';
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$bookingTypeValue          = ucfirst($bookingType);
		$tblElementBooking = JTable::getInstance($bookingTypeValue.'Booking', 'BeseatedTable');
		$tblElementBooking->load($bookingID);

		if(ucfirst($bookingType) == "Venue")
		{
			$bookingTypeIDField  = strtolower($bookingType.'_table_booking_id');
		}
		else
		{
			$bookingTypeIDField  = strtolower($bookingType.'_booking_id');
		}

		if(!$tblElementBooking->$bookingTypeIDField)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(ucfirst($bookingType) == 'Protection')
		{
			$invitations = $this->getInvitationsOnBooking($bookingID,$bookingType);

			$splitedUserCount = $this->getSplitedUserCount('Protection', $bookingID);

			if(!$splitedUserCount)
			{
				$this->jsonarray['remainingSplitUser']  = 50 - 1;
			}
			else
			{
				if($splitedUserCount > 50)
				{
					$this->jsonarray['remainingSplitUser']  = 0;
				}
				else
				{
					$this->jsonarray['remainingSplitUser']  = 50 - $splitedUserCount;
				}

			}
		}
		else if (ucfirst($bookingType)  == 'Venue')
		{
			$tblElementBooking = JTable::getInstance($bookingTypeValue.'Booking', 'BeseatedTable');
		    $tblElementBooking->load($bookingID);

		    $tblTable = JTable::getInstance('Table', 'BeseatedTable');
		    $tblTable->load($tblElementBooking->table_id);


			$invitations = $this->getInvitationsOnBooking($bookingID,$bookingType);

			$splitedUserCount = $this->getSplitedUserCount('Venue', $bookingID);

			if(!$splitedUserCount)
			{
				$this->jsonarray['remainingSplitUser']  = $tblElementBooking->total_guest - 1;
			}
			else
			{
				if($splitedUserCount > $tblElementBooking->total_guest)
				{
					$this->jsonarray['remainingSplitUser']  = 0;
				}
				else
				{
					$this->jsonarray['remainingSplitUser']  = $tblElementBooking->total_guest - $splitedUserCount;
				}
			}

			if($tblElementBooking->total_split_count)
			{
				$this->jsonarray['maxSplitCount']       = $tblElementBooking->total_split_count - 1 - (($tblElementBooking->splitted_count) ?  $tblElementBooking->splitted_count - 1 : 0);
			}
			else
			{
				$this->jsonarray['maxSplitCount']       = $tblTable->capacity;
			}
		}
		else if (ucfirst($bookingType)  == 'Yacht')
		{
			$invitations = $this->getInvitationsOnBooking($bookingID,$bookingType);

			$splitedUserCount = $this->getSplitedUserCount('Yacht', $bookingID);

			if(!$splitedUserCount)
			{
				$this->jsonarray['remainingSplitUser']  = $tblElementBooking->capacity - 1;
			}
			else
			{
				if($splitedUserCount > $tblElementBooking->capacity)
				{
					$this->jsonarray['remainingSplitUser']  = 0;
				}
				else
				{
					$this->jsonarray['remainingSplitUser']  = $tblElementBooking->capacity - $splitedUserCount;
				}
			}
		}
		else if (ucfirst($bookingType)  == 'Chauffeur')
		{
			$invitations = $this->getInvitationsOnBooking($bookingID,$bookingType);

			$splitedUserCount = $this->getSplitedUserCount('Chauffeur', $bookingID);

			if(!$splitedUserCount)
			{
				$this->jsonarray['remainingSplitUser']  = $tblElementBooking->capacity - 1;
			}
			else
			{
				if($splitedUserCount > $tblElementBooking->capacity)
				{
					$this->jsonarray['remainingSplitUser']  = 0;
				}
				else
				{
					$this->jsonarray['remainingSplitUser']  = $tblElementBooking->capacity - $splitedUserCount;
				}

			}
		}
		else if (ucfirst($bookingType)  == 'Ticket')
		{
			$invitations = $this->getInvitationsOnBooking($bookingID,$bookingType);

			$tblTicketBooking = JTable::getInstance('TicketBooking', 'BeseatedTable');
			$tblTicketBooking->load($bookingID);

			/*$splitedUserCount = $this->getSplitedUserCount('Chauffeur', $bookingID);*/

			if(!$invitations)
			{
				$this->jsonarray['remainingSplitUser']  = $tblTicketBooking->total_ticket - 1;
			}
			else
			{
				if(count($invitations) > $tblTicketBooking->total_ticket)
				{
					$this->jsonarray['remainingSplitUser']  = 0;
				}
				else
				{
					$this->jsonarray['remainingSplitUser']  = $tblTicketBooking->total_ticket - count($invitations) - 1;
				}

			}
		}
		else
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		if(count($invitations) == 0)
		{
			$this->jsonarray['code'] = 204;
			return $this->jsonarray;
		}


		$this->jsonarray['code'] = 200;
		$this->jsonarray['invitations'] = $invitations;
		$this->jsonarray['totalInvitations'] = count($invitations);

		return $this->jsonarray;
	}

	function getInvitationsOnBooking($bookingID,$bookingType){
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$statusID = $this->helper->getStatusID('decline');

		// Create the base select statement.
		 //edit	invitation_id		element_id	element_type	user_id	email	fbid	user_action	time_stamp

		if(ucfirst($bookingType == 'Ticket'))
		{
			$query->select('invt.email,invt.invited_user_status as user_action,invt.invite_id as invitation_id')
					->from($db->quoteName('#__beseated_event_ticket_booking_invite').' AS invt')
					->where($db->quoteName('invt.invited_user_status') . ' != ' . $db->quote($statusID))
					->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($bookingID));

			$query->select('usr.full_name,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON invt.invited_user_id=usr.user_id');
		}
		else
		{
			$query->select('invt.invitation_id,invt.email,invt.user_action')
				->from($db->quoteName('#__beseated_invitation') . ' AS invt')
				->where($db->quoteName('invt.element_booking_id') . ' = ' . $db->quote($bookingID))
				->where($db->quoteName('invt.element_type') . ' = ' . $db->quote(strtolower($bookingType)))
				->order($db->quoteName('invt.email') . ' ASC');

			$query->select('usr.full_name,usr.avatar,usr.thumb_avatar')
			    ->join('LEFT','#__beseated_user_profile AS usr ON invt.user_id=usr.user_id');
		}




		// Set the query and load the result.
		$db->setQuery($query);
		$resInvitations = $db->loadObjectList();

		$resultInvitations = array();
		foreach ($resInvitations as $key => $invitation)
		{
			$temp                 = array();
			$temp['invitationID'] = $invitation->invitation_id;
			$temp['email']        = $invitation->email;
			$temp['statusCode']   = $invitation->user_action;
			$temp['fullName']     = ($invitation->full_name)?$invitation->full_name:'';
			$temp['avatar']       = ($invitation->avatar)?$this->helper->getUserAvatar($invitation->avatar):'';
			$temp['thumbAvatar']  = ($invitation->thumb_avatar)?$this->helper->getUserAvatar($invitation->thumb_avatar):'';

			$resultInvitations[] = $temp;
		}

		return $resultInvitations;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"changeInvitationStatus","taskData":{"invitationID":"","statusCode":""}}
	 *
	 */
	function changeInvitationStatus()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$invitationID = IJReq::getTaskData('invitationID',0, 'int');
		$statusCode   = IJReq::getTaskData('statusCode',0, 'int');

		$possibleCode = array(6,9,10,11,12);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblInvitation = JTable::getInstance('Invitation', 'BeseatedTable');
		$tblInvitation->load($invitationID);

		if(!$tblInvitation->invitation_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_INVITATION_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!in_array($statusCode, $possibleCode))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_INVITATION_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblInvitation->user_action = $statusCode;
		$bookingType = $tblInvitation->element_type;

		if(ucfirst($bookingType) == "Venue")
		{
			$bookingTypeIDField  = strtolower($bookingType.'_table_booking_id');
		}
		else
		{
			$bookingTypeIDField  = strtolower($bookingType.'_booking_id');
		}

		$invitedUserDetail = JFactory::getUser();

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$bookingTypeValue  = ucfirst($bookingType);
		$tblElementBooking = JTable::getInstance($bookingTypeValue.'Booking', 'BeseatedTable');
		$tblElementBooking->load($tblInvitation->element_booking_id);

		if($bookingTypeValue == "Venue")
		{
			$bookingTypeIDField  = strtolower($bookingType.'_table_booking_id');
		}
		else
		{
			$bookingTypeIDField  = strtolower($bookingType.'_booking_id');
		}

		if(!$tblElementBooking->$bookingTypeIDField)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_BOOKING_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		if(!$tblInvitation->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVITATION_STATUS_NOT_CHANGED'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblElement = JTable::getInstance($bookingTypeValue, 'BeseatedTable');
		$tblElement->load($tblInvitation->element_id);

		$elementName      = strtolower($bookingTypeValue).'_name';
		$notificationType = strtolower($bookingType).".invitation.status.changed";
		$statusName       = $this->helper->getStatusName($statusCode);
		$bookingType      = strtolower($bookingTypeValue);

		$elementManagerDetail = $this->helper->guestUserDetail($tblElement->user_id);

		if(isset($tblElement->is_day_club) && $tblElement->is_day_club == 0)
		{
			$title = JText::sprintf(
					'COM_BESEATED_PUSHNOTIFICATION_VENUE_NIGHT_INVITATION_STATUS_CHANGED_BY_USER',
					$invitedUserDetail->name,
					$statusName,
					$tblElement->$elementName,
					$this->helper->convertDateFormat($tblElementBooking->booking_date)
				);

			$dbTitle = JText::sprintf(
					'COM_BESEATED_DB_PUSHNOTIFICATION_VENUE_NIGHT_INVITATION_STATUS_CHANGED_BY_USER',
					$statusName,
					$tblElement->$elementName,
					$this->helper->convertDateFormat($tblElementBooking->booking_date)
				);

		}
		else
		{
			$title = JText::sprintf(
					'COM_BESEATED_PUSHNOTIFICATION_INVITATION_STATUS_CHANGED_BY_USER',
					$invitedUserDetail->name,
					$statusName,
					$tblElement->$elementName,
					$this->helper->convertDateFormat($tblElementBooking->booking_date),
				    $this->helper->convertToHM($tblElementBooking->booking_time)
				);

			$dbTitle = JText::sprintf(
					'COM_BESEATED_DB_PUSHNOTIFICATION_INVITATION_STATUS_CHANGED_BY_USER',
					$statusName,
					$tblElement->$elementName,
					$this->helper->convertDateFormat($tblElementBooking->booking_date),
					$this->helper->convertToHM($tblElementBooking->booking_time)
				);

		}

		$actor                                 = $this->IJUserID;
		$target                                = $tblElementBooking->user_id;
		$elementID                             = $tblInvitation->element_id;
		$elementType                           = $bookingTypeValue;
		$cid                                   = $tblInvitation->element_booking_id;
		$extraParams                           = array();
		$extraParams[$bookingType."BookingID"] = $tblInvitation->element_booking_id;
		$extraParams["invitationID"]           = $tblInvitation->invitation_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$dbTitle,$cid,$extraParams,$cid))
		{
			$this->jsonarray['pushNotificationData']['id']          = $tblInvitation->element_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = ucfirst($elementType);
			$this->jsonarray['pushNotificationData']['to']          = $tblElementBooking->user_id;
			$this->jsonarray['pushNotificationData']['message']     = $title;
			$this->jsonarray['pushNotificationData']['type']        = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype']  = '';
		}

		//echo "<pre/>";print_r("hi");exit;

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"changeInvitationStatus","taskData":{"invitationID":"","statusCode":""}}
	 *
	 */
	function changeEventInvitationStatus()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$invitationID = IJReq::getTaskData('invitationID',0, 'int');
		$statusCode   = IJReq::getTaskData('statusCode',0, 'int');

		$possibleCode = array(6,11);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblInvitation = JTable::getInstance('TicketInvitation', 'BeseatedTable');
		$tblInvitation->load($invitationID);

		$tblTicketBookingDetail = JTable::getInstance('TicketBookingDetail', 'BeseatedTable');
		$tblTicketBookingDetail->load($tblInvitation->ticket_booking_detail_id);

		$tblEvent = JTable::getInstance('Event', 'BeseatedTable');
		$tblEvent->load($tblInvitation->event_id);

		if(!$tblInvitation->invite_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_INVITATION_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!in_array($statusCode, $possibleCode))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_INVITATION_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblInvitation->invited_user_status = $statusCode;

		if(!$tblInvitation->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVITATION_STATUS_NOT_CHANGED'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$invitedUserDetail = JFactory::getUser();

		if($statusCode == 11)
		{
			$notificationType = "event.invitation.accepted";

			$title = JText::sprintf(
						'COM_BESEATED_PUSHNOTIFICATION_EVENT_INVITATION_ACCEPTED_BY_USER',
						$invitedUserDetail->name,
						$tblEvent->event_name,
						$this->helper->convertDateFormat($tblEvent->event_date),
					    $this->helper->convertToHM($tblEvent->event_time)
					);

			$db_title = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_EVENT_INVITATION_ACCEPTED_BY_USER',
						$tblEvent->event_name,
						$this->helper->convertDateFormat($tblEvent->event_date),
					    $this->helper->convertToHM($tblEvent->event_time)
					);

		}
		else if($statusCode == 6)
		{
			$tblTicketBookingDetail->user_id = $tblInvitation->user_id;
			$tblTicketBookingDetail->store();

			$notificationType = "event.invitation.declined";

			$title = JText::sprintf(
						'COM_BESEATED_PUSHNOTIFICATION_EVENT_INVITATION_DECLINED_BY_USER',
						$invitedUserDetail->name,
						$tblEvent->event_name,
						$this->helper->convertDateFormat($tblEvent->event_date),
					    $this->helper->convertToHM($tblEvent->event_time)
					);

			$db_title = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_EVENT_INVITATION_DECLINED_BY_USER',
						$tblEvent->event_name,
						$this->helper->convertDateFormat($tblEvent->event_date),
					    $this->helper->convertToHM($tblEvent->event_time)
					);
		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_SERVICE_INVALID_STATUS'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$actor                         = $this->IJUserID;
		$target                        = $tblInvitation->user_id;
		$elementID                     = $tblInvitation->event_id;
		$elementType                   = "Event";
		$cid                           = $tblInvitation->ticket_booking_detail_id;
		$extraParams                   = array();
		$extraParams["eventBookingID"] = $tblInvitation->ticket_booking_detail_id;
		$extraParams["invitationID"]   = $tblInvitation->invite_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$db_title,$cid,$extraParams,$tblInvitation->ticket_booking_id))
		{
			$this->jsonarray['pushNotificationData']['id']          = $tblInvitation->ticket_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Event';
			$this->jsonarray['pushNotificationData']['to']          = $tblInvitation->user_id;
			$this->jsonarray['pushNotificationData']['message']     = $title;
			$this->jsonarray['pushNotificationData']['type']        = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype']  = '';
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"getRSVP","taskData":{"pageNO":"0"}}
	 *
	 */
	function getRSVP()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$statusArray = array();
		$statusArray[] = $this->helper->getStatusID('pending');
		$statusArray[] = $this->helper->getStatusID('available');
		$statusArray[] = $this->helper->getStatusID('decline');
		$statusArray[] = $this->helper->getStatusID('canceled');
		//$statusArray[] = $this->helper->getStatusID('confirmed');

		$invitedUserStatus   = array();
		$invitedUserStatus[] = $this->helper->getStatusID('not-going');
		$invitedUserStatus[] = $this->helper->getStatusID('maybe');
		$invitedUserStatus[] = $this->helper->getStatusID('pending');

		// Create the base select statement.
		$query->select('venue_id')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('deleted_by_user') . ' = ' . $db->quote(0))
			->group($db->quoteName('venue_id'))
			->order($db->quoteName('booking_date') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$resVenueIDs   = $db->loadColumn();

		$invitationStatus = array();
		$invitationStatus[] = $this->helper->getStatusID('pending');
		//$invitationStatus[] = $this->helper->getStatusID('paid');
		//$invitationStatus[] = $this->helper->getStatusID('decline');
		//$invitationStatus[] = $this->helper->getStatusID('canceled');

		$venuesSplitsql = $db->getQuery(true);
		$venuesSplitsql->select('venue_table_booking_id,venue_id')
			->from($db->quoteName('#__beseated_venue_table_booking_split'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('split_payment_status') . ' IN ('.implode(",", $invitationStatus).')')
			->where($db->quoteName('is_owner') . ' = ' . $db->quote(0));
		$db->setQuery($venuesSplitsql);

		$resVenueSplits   = $db->loadObjectList();
		$allVenueID       = $resVenueIDs;
		$splitedBookigIDs = array();
		$otherBookingIDs  = array();

		foreach ($resVenueSplits as $key => $splitDetail)
		{
			if(!in_array($splitDetail->venue_id, $allVenueID))
			{
				$allVenueID[] = $splitDetail->venue_id;
			}

			$splitedBookigIDs[] = $splitDetail->venue_table_booking_id;
			$otherBookingIDs[]  = $splitDetail->venue_table_booking_id;
		}

		$venuesInvitesql = $db->getQuery(true);
		$venuesInvitesql->select('element_booking_id,element_id')
			->from($db->quoteName('#__beseated_invitation'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('user_action') . ' IN ('.implode(",", $invitedUserStatus).')')  // by jamal
			->where($db->quoteName('element_type') . ' = ' . $db->quote('venue'));
		$db->setQuery($venuesInvitesql);
		$resVenueInvites = $db->loadObjectList();
		$invitationBookingIDs = array();

		foreach ($resVenueInvites as $key => $invitation)
		{
			if(!in_array($invitation->element_id, $allVenueID))
			{
				$allVenueID[] = $invitation->element_id;
			}

			$tblElementBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		    $tblElementBooking->load($invitation->element_booking_id);

		    if($tblElementBooking->user_status !== '5')
		    {
				$otherBookingIDs[]      = $invitation->element_booking_id;
				$invitationBookingIDs[] = $invitation->element_booking_id;
			}
		}

		$venuesFAsql = $db->getQuery(true);
		$venuesFAsql->select('friends_attending_id,venue_table_booking_id,venue_id')
			->from($db->quoteName('#__beseated_venue_friends_attending'))
			->where($db->quoteName('booking_user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('booking_user_status') . ' = ' . $db->quote('1'));
		$db->setQuery($venuesFAsql);
		$resVenueFriendsAttending = $db->loadObjectList();
		$FABookingIDs = array();

		$FAVenueIDs = array();

		foreach ($resVenueFriendsAttending as $key => $friendAttending)
		{
			$FAVenueIDs[] = $friendAttending->venue_id;

			if(!in_array($friendAttending->venue_id, $allVenueID))
			{
				$allVenueID[] = $friendAttending->venue_id;
			}

			$otherBookingIDs[] = $friendAttending->venue_table_booking_id;
			$FABookingIDs[]    = $friendAttending->venue_table_booking_id;
		}

		$FAVenueIDs = array_unique($FAVenueIDs);

		$resultVenueBookings = array();
		$proccessIDs              = array();
		/*echo "<pre>";
		print_r($FABookingIDs);
		echo "</pre>";
		exit;*/
		/*echo "All Venue IDs <pre>";
		print_r($allVenueID);
		echo "</pre>";
		echo "otherBookingIDs <pre>";
		print_r($otherBookingIDs);
		echo "</pre>";
		echo "invitationBookingIDs <pre>";
		print_r($invitationBookingIDs);
		echo "</pre>";
		echo "splitedBookigIDs <pre>";
		print_r($splitedBookigIDs);
		echo "</pre>";
	//	exit;*/

		foreach ($allVenueID as $key => $venueID)
		{
			$temp  = array();
			$query = $db->getQuery(true);
			// Create the base select statement.
			$query->select('vb.venue_table_booking_id,vb.venue_id,vb.table_id,vb.user_id,vb.booking_date,vb.booking_time,vb.privacy,vb.passkey,vb.total_guest,vb.male_guest,vb.female_guest,vb.total_hours,vb.total_price,vb.user_status,has_invitation,vb.is_show,vb.is_noshow,vb.each_person_pay,vb.is_splitted,vb.splitted_count,vb.remaining_amount,vb.booking_currency_code,vb.booking_currency_sign,vb.pay_by_cash_status,vb.response_date_time,vb.total_split_count')
				->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vb');
				if(count($otherBookingIDs)!= 0)
				{
					$query->where(
						'('.
							$db->quoteName('vb.venue_table_booking_id') . ' IN ('.implode(",", $otherBookingIDs).') OR '.
							'('.
								$db->quoteName('vb.user_id') . ' = ' . $db->quote($this->IJUserID) .' AND '.
								$db->quoteName('vb.user_status') . ' IN ('.implode(",", $statusArray).')' .' AND '.
								$db->quoteName('vb.deleted_by_user') . ' = ' . $db->quote(0) . ' AND '.
								$db->quoteName('vb.has_booked') . ' = ' . $db->quote(0) .
							')'.
						')'
					);
				}
				else{
					$query->where($db->quoteName('vb.user_id') . ' = ' . $db->quote($this->IJUserID) );
					$query->where($db->quoteName('vb.user_status') . ' IN ('.implode(",", $statusArray).')');
					$query->where($db->quoteName('vb.deleted_by_user') . ' = ' . $db->quote(0));

					//if(!in_array($venueID, $FAVenueIDs))
					//{
						$query->where($db->quoteName('vb.has_booked') . ' = ' . $db->quote('0'));
					//}

				}

				$query->where($db->quoteName('vb.venue_id') . ' = ' . $db->quote($venueID));

				/*if(!in_array($venueID, $FAVenueIDs))
				{
					$query->where($db->quoteName('vb.has_booked') . ' = ' . $db->quote('0'));
				}*/

				$query->where($db->quoteName('vb.booking_date') . ' >= ' . $db->quote(date('Y-m-d')));
				$query->order($db->quoteName('vb.booking_date') . ' ASC');

			$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club,v.has_bottle,v.deposit_per,v.refund_policy')
				->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

			$query->select('vt.table_name,vt.premium_table_id,vt.min_price,vt.thumb_image,vt.image')
				->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

			$query->select('vpt.premium_table_name')
				->join('LEFT','#__beseated_venue_premium_table AS vpt ON vpt.premium_id=vt.premium_table_id');

			$query->select('bu.full_name,bu.avatar,bu.thumb_avatar,bu.is_fb_user,bu.fb_id')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=vb.user_id');

			/*if($venueID == 4){
				echo $query->dump();
				exit;
			}*/



			// Set the query and load the result.
		//	echo $query->dump();
			$db->setQuery($query);

			$resVenueBookings = $db->loadObjectList();

			$venueIDs = array();
			$bookingINDX = 0;
			foreach ($resVenueBookings as $key => $booking)
			{
				$tmpBooking = array();
				if(!in_array($booking->venue_id, $venueIDs))
				{
					$proccessIDs[]     = $booking->venue_id;
					$temp['venueName'] = ucfirst($booking->venue_name);
					$temp['location']  = ucfirst($booking->location);
					$temp['city']      = ucfirst($booking->city);
					$temp['type']      = "Venue";
				}


				$tmpBooking['venueBookingID']   = $booking->venue_table_booking_id;
				$tmpBooking['venueID']          = $booking->venue_id;
				$tmpBooking['bookingDate']      = $this->helper->convertDateFormat($booking->booking_date);
				$tmpBooking['bookingTime']      = $this->helper->convertToHM($booking->booking_time);
				$tmpBooking['totalPrice']       = $this->helper->currencyFormat('',$booking->total_price,0);
				$tmpBooking['currencyCode']     = $booking->booking_currency_code;
				$tmpBooking['venueName']        = $booking->venue_name;
				$tmpBooking['venueType']        = $booking->venue_type;
				$tmpBooking['dayClub']          = $booking->is_day_club;
				$tmpBooking['tableName']        = $booking->table_name;
				$tmpBooking['tableType']        = ($booking->premium_table_name)? $booking->premium_table_name : '';
				$tmpBooking['privacy']          = $booking->privacy;
				$tmpBooking['passkey']          = $booking->passkey;
				$tmpBooking['statusCode']       = $booking->user_status;
				$tmpBooking['totalHours']       = $booking->total_hours;
				$tmpBooking['fullName']         = $booking->full_name;
				$tmpBooking['isFbUser']         = $booking->is_fb_user;
				$tmpBooking['fbID']             = ($booking->fb_id && $booking->is_fb_user) ? $booking->fb_id : "0";
				$tmpBooking['avatar']           = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
				$tmpBooking['thumbAvatar']      = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
				$tmpBooking['totalGuest']       = $booking->total_guest;
				$tmpBooking['maleGuest']        = $booking->male_guest;
				$tmpBooking['femaleGuest']      = $booking->female_guest;
				$tmpBooking['hasBottle']        = ($this->hasVenueBottle($booking->venue_id))? '1':'0';
				$tmpBooking['minSpend']         = $this->helper->currencyFormat('',$booking->min_price);
				$tmpBooking['thumbImage']       = ($booking->thumb_image)?JUri::base().'images/beseated/'.$booking->thumb_image:'';
				$tmpBooking['image']            = ($booking->image)?JUri::base().'images/beseated/'.$booking->image:'';
				$tmpBooking['pickup_location']  = '';
				$tmpBooking['dropoff_location'] = '';
				$tmpBooking['totalSplitCount']  = $booking->total_split_count;
				$tmpBooking['totalGuard']       = '';
				$tmpBooking['hasDeposit']       = ($booking->deposit_per == '0') ? '0' : '1';
				$tmpBooking['depositRate']      = $booking->deposit_per;
				$tmpBooking['depositAmount']    = '';
				$tmpBooking['refundPolicyHours']= $booking->refund_policy;

				//$tmpBooking['maxSplitCount']    = '';

				if($booking->total_split_count)
				{
					$tmpBooking['maxSplitCount']    = $booking->total_split_count - 1 - (($booking->splitted_count) ?  $booking->splitted_count - 1 : 0);
				}
				else
				{
					$tmpBooking['maxSplitCount']    = $booking->total_guest;
				}

				$splitedUserCount = $this->getSplitedUserCount($elementType = 'Venue' , $booking->venue_table_booking_id);

				if(!$splitedUserCount)
				{
					$tmpBooking['remainingSplitUser']  = $booking->total_guest - 1;
				}
				else
				{
					if($splitedUserCount > $booking->total_guest)
					{
						$tmpBooking['remainingSplitUser']  = 0;
					}
					else
					{
						$tmpBooking['remainingSplitUser']  = $booking->total_guest - $splitedUserCount;
					}

				}



				$tmpBooking['paidByOwner']    = 0;
					 /*echo "<pre>";
						print_r($booking->venue_table_booking_id);
						echo "</pre>";
						die;*/
				if(in_array($booking->venue_table_booking_id, $splitedBookigIDs))
				{
					$tmpBooking['bookedType']         = 'share';

					$querySplitDetail = $db->getQuery(true);

					// Create the base select statement.
					$querySplitDetail->select('*')
						->from($db->quoteName('#__beseated_venue_table_booking_split'))
						->where($db->quoteName('venue_table_booking_id') . ' = ' . $db->quote($booking->venue_table_booking_id))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

					// Set the query and load the result.
					$db->setQuery($querySplitDetail);

					$resSplitDetail = $db->loadObject();

					/*if($booking->venue_table_booking_id == 66){
						echo "<pre>";
						print_r($resSplitDetail);
						echo "</pre>";
						exit;
					}*/
					$tmpBooking['statusCode'] = $resSplitDetail->split_payment_status;
					$tmpBooking['params']     = array("splitID" => $resSplitDetail->venue_table_booking_split_id);
					$booking_invitation_id    = $resSplitDetail->venue_table_booking_split_id;

					$tmpBooking['payByCashStatus'] = $resSplitDetail->pay_by_cash_status;

					if($resSplitDetail->split_payment_status == 7)
					{
						$tmpBooking['paymentURL'] = "";
					}
					else
					{
						$tmpBooking['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$resSplitDetail->venue_table_booking_split_id.'&booking_type=venue.split';
						if($resSplitDetail->venue_table_booking_id == 33){
							//$tmpBooking['paymentURL Index'] = $bookingINDX;
						}

						/*echo "<pre>";
						print_r($resSplitDetail);
						echo "</pre>";*/
					}

					//echo "<br />Booking index : ".$bookingINDX;  tmpBooking


				}
				else if (in_array($booking->venue_table_booking_id, $invitationBookingIDs))
				{
					$tmpBooking['bookedType']         = 'invitation';
					$queryInviteDetail = $db->getQuery(true);
					$queryInviteDetail->select('*')
						->from($db->quoteName('#__beseated_invitation'))
						->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($booking->venue_table_booking_id))
						->where($db->quoteName('element_id') . ' = ' . $db->quote($booking->venue_id))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('venue'))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

					// Set the query and load the result.
					$db->setQuery($queryInviteDetail);

					$resInviteDetail = $db->loadObject();
					$tmpBooking['statusCode']          = $resInviteDetail->user_action;
					$tmpBooking['params'] = array("invitationID" => $resInviteDetail->invitation_id);
					$booking_invitation_id    = $resInviteDetail->invitation_id;

					$tmpBooking['paymentURL'] = "";
					$tmpBooking['payByCashStatus'] = 0;

					/*if($resInviteDetail->split_payment_status == 7)
					{
						$tmpBooking['paymentURL'] = "";
					}
					else
					{
						$tmpBooking['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$resSplitDetail->protection_booking_split_id.'&booking_type=protection.split';
					}*/
				}
				else if (!in_array($booking->venue_table_booking_id, $FABookingIDs))
				{
					$tmpBooking['bookedType']         = 'booking';
					$booking_invitation_id    = $booking->venue_table_booking_id;
					if($booking->user_status == 4){
						$tmpBooking['paymentURL'] = JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$booking->venue_table_booking_id.'&booking_type=venue';
					}
					else{
						$tmpBooking['paymentURL'] = "";
					}
					$tmpBooking['payByCashStatus'] = $booking->pay_by_cash_status;
					$tmpBooking['params'] = array();
				}
				/*else
				{
					$tmpBooking['bookedType']         = 'booking';
					if($booking->user_status == 4){
						$tmpBooking['paymentURL'] = JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$booking->venue_table_booking_id.'&booking_type=venue';
					}
					else{
						$tmpBooking['paymentURL'] = "";
					}
					$tmpBooking['payByCashStatus'] = $booking->pay_by_cash_status;
					$tmpBooking['params'] = array();
				}*/

				$tmpBooking['isSplitted']          = $booking->is_splitted;
				$tmpBooking['eachPersonPay']       = $this->helper->currencyFormat('',$booking->each_person_pay);
				$tmpBooking['splittedCount']       = $booking->splitted_count;
				$tmpBooking['remainingAmount']     = $this->helper->currencyFormat('',$booking->remaining_amount);
				if($booking->response_date_time != '0000-00-00 00:00:00'){
					$tmpBooking['remainingTime'] = strtotime($booking->response_date_time);
				}else{
					$tmpBooking['remainingTime'] = '';
				}

				$querySplit = $db->getQuery(true);
				$querySplit->select('split.venue_table_booking_split_id,split.user_id,split.is_owner,split.email,split.splitted_amount,split.split_payment_status,split.paid_by_owner')
					->from($db->quoteName('#__beseated_venue_table_booking_split','split'))
					->where($db->quoteName('split.venue_table_booking_id') . ' = ' . $db->quote($booking->venue_table_booking_id));
				$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar')
					->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id')
					->order($db->quoteName('bu.full_name') . ' ASC');

				$db->setQuery($querySplit);
				$resSplits = $db->loadObjectList();

				if($resSplits)
				{
					$tempSplit = array();
					foreach ($resSplits as $key => $split)
					{
						if($this->IJUserID == $split->user_id &&  $split->is_owner == '1')
						{
							continue;
						}

						$tempSingleSplit = array();
						$tempSingleSplit['invitationID']   = $split->venue_table_booking_split_id;
						$tempSingleSplit['fullName']       = ($split->full_name)?$split->full_name:$split->email;
						$tempSingleSplit['splittedAmount'] = $this->helper->currencyFormat('',$split->splitted_amount);
						$tempSingleSplit['statusCode']     = $split->split_payment_status;
						$tempSingleSplit['avatar']         = ($split->avatar)?$this->helper->getUserAvatar($split->avatar):'';
						$tempSingleSplit['thumbAvatar']    = ($split->thumb_avatar)?$this->helper->getUserAvatar($split->thumb_avatar):'';

						if($split->user_id == $booking->user_id)
						{
							//$bookingDetail['priceToPay'] = $this->helper->currencyFormat('',$split->splitted_amount);
							if($split->split_payment_status == 7)    // if is paid
							{
								$tmpBooking['isBookingUserPaid'] =  1;
								//$tmpBooking['paymentURL'] = "";
							}
							else
							{
								//$tmpBooking['isBookingUserPaid'] =  1;
								$tmpBooking['isBookingUserPaid'] =  0;
								//$tmpBooking['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$split->venue_table_booking_split_id.'&booking_type=venue.split';
							}

						}
						else
						{
							//$tempSplit[] = $tempSingleSplit;
						}

						if($split->user_id == $this->IJUserID && $split->split_payment_status == 7 && $split->is_owner == 0 && $split->paid_by_owner==1)
						{
							$tmpBooking['paidByOwner'] = 1;
						}
						/*else{
							$tempSingleSplit['paidByOwner'] = 0;
						}*/

						$tempSplit[] = $tempSingleSplit;
					}
					$tmpBooking['splits'] = $tempSplit;
				}
				else
				{
					$tmpBooking['splits'] = array();
				}



				if (in_array($booking->venue_table_booking_id, $FABookingIDs))
				{

					$venuesFAsql = $db->getQuery(true);
					$venuesFAsql->select('fa.*')
						->from($db->quoteName('#__beseated_venue_friends_attending','fa'))
						->where($db->quoteName('fa.venue_table_booking_id') . ' = ' . $db->quote($booking->venue_table_booking_id))
						->where($db->quoteName('fa.booking_user_id') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('fa.booking_user_status') . ' = ' . $db->quote('1'));

					$venuesFAsql->select('bu.full_name,bu.avatar,bu.thumb_avatar,bu.fb_id')
						->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=fa.user_id');
					$db->setQuery($venuesFAsql);
					$resVenueFriendsAttending = $db->loadObjectList();

					foreach ($resVenueFriendsAttending as $key => $friendAttending)
					{
						$booking_invitation_id    = $friendAttending->friends_attending_id;
						$faBooking = $tmpBooking;
						$faBooking['bookedType']         = 'friendAttending';
						$faBooking['statusCode']          = $friendAttending->booking_user_status;
						$faBooking['params'] = array("friendsAttendingID" => $friendAttending->friends_attending_id);
						$faBooking['paymentURL'] = "";
						$faBooking['payByCashStatus'] = 0;

						//$faBooking['venueBookingID'] = $friendAttending->friends_attending_id;

						$faBooking['fullName']       = $friendAttending->full_name;
						$faBooking['fbID']           = $friendAttending->fb_id;
						$faBooking['avatar']         = ($friendAttending->avatar)?$this->helper->getUserAvatar($friendAttending->avatar):'';
						$faBooking['thumbAvatar']    = ($friendAttending->thumb_avatar)?$this->helper->getUserAvatar($friendAttending->thumb_avatar):'';
						$faBooking['isRead']  = $this->helper->isReadBooking('venue',$faBooking['bookedType'],$booking_invitation_id);

						$temp['bookings'][$bookingINDX]    = $faBooking;
						$bookingINDX++;
					}
				}
				else
				{
					$tmpBooking['isRead']  = $this->helper->isReadRSVP('venue',$tmpBooking['bookedType'],$booking_invitation_id);
					$temp['bookings'][$bookingINDX]    = $tmpBooking;
					$bookingINDX++;
				}



				/*$tmpBooking['bookingType']    = 'booking';
				$tmpBooking['params']         = array();*/


				//unset($tmpBooking);
			}



			if(count($temp) != 0){
				$resultVenueBookings[] = $temp;
			}
		}

		// Start of Get Protection RSVP
		$protectionsIDsql = $db->getQuery(true);
		$protectionsIDsql->select('protection_id')
			->from($db->quoteName('#__beseated_protection_booking'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('deleted_by_user') . ' = ' . $db->quote(0))
			->group($db->quoteName('protection_id'))
			->order($db->quoteName('booking_date') . ' ASC');
		$db->setQuery($protectionsIDsql);
		$resProtectionIDs = $db->loadColumn();

		//echo "<pre/>";print_r($resProtectionIDs);exit;

		$invitationStatus = array();
		$invitationStatus[] = $this->helper->getStatusID('pending');
		//$invitationStatus[] = $this->helper->getStatusID('paid');
		$protectionsSplitsql = $db->getQuery(true);
		$protectionsSplitsql->select('protection_booking_id,protection_id')
			->from($db->quoteName('#__beseated_protection_booking_split'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('split_payment_status') . ' IN ('.implode(",", $invitationStatus).') ' )
			->where($db->quoteName('is_owner') . ' = ' . $db->quote(0));
		$db->setQuery($protectionsSplitsql);

		$resProtectionSplits = $db->loadObjectList();
		$allProtectionID     = $resProtectionIDs;
		$splitedBookigIDs    = array();
		$otherBookingIDs = array();

		foreach ($resProtectionSplits as $key => $splitDetail)
		{
			if(!in_array($splitDetail->protection_id, $allProtectionID))
			{
				$allProtectionID[] = $splitDetail->protection_id;
			}

			$splitedBookigIDs[] = $splitDetail->protection_booking_id;
			$otherBookingIDs[]  = $splitDetail->protection_booking_id;
		}

		$protectionsInvitesql = $db->getQuery(true);
		$protectionsInvitesql->select('element_booking_id,element_id')
			->from($db->quoteName('#__beseated_invitation'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('user_action') . ' IN ('.implode(",", $invitedUserStatus).')')  // by jamal
			->where($db->quoteName('element_type') . ' = ' . $db->quote('protection'));
		$db->setQuery($protectionsInvitesql);
		$resProtectionInvites = $db->loadObjectList();

		$invitationBookingIDs = array();
		$protectionInvitaionIDs = array();

		foreach ($resProtectionInvites as $key => $invitation)
		{
			$protectionInvitaionIDs[] = $invitation->element_id;

			if(!in_array($invitation->element_id, $allProtectionID))
			{
				$allProtectionID[] = $invitation->element_id;
			}

			$otherBookingIDs[]      = $invitation->element_booking_id;
			$invitationBookingIDs[] = $invitation->element_booking_id;
		}

		$resultProtectionBookings = array();
		$proccessIDs              = array();

		foreach ($allProtectionID as $key => $protectionID)
		{
			$temp  = array();
			$query = $db->getQuery(true);
			// Create the base select statement.
			$query->select('pb.protection_booking_id,pb.protection_id,pb.service_id,pb.user_id,pb.booking_date,pb.booking_time,pb.total_hours,pb.price_per_hours,pb.total_price,pb.user_status,pb.is_splitted,pb.each_person_pay,pb.splitted_count,pb.remaining_amount,pb.response_date_time,pb.total_split_count,pb.total_guard')
				->from($db->quoteName('#__beseated_protection_booking') . ' AS pb');
				if(count($otherBookingIDs)!= 0)
				{
					$query->where(
						'('.$db->quoteName('pb.protection_booking_id') . ' IN ('.implode(",", $otherBookingIDs).') OR '.
						'('.
							$db->quoteName('pb.user_id') . ' = ' . $db->quote($this->IJUserID) .' AND '.
							$db->quoteName('pb.user_status') . ' IN ('.implode(",", $statusArray).')' .' AND '.
							$db->quoteName('pb.deleted_by_user') . ' = ' . $db->quote(0) .
						')'.
						')'
					);
				}
				else{
					$query->where($db->quoteName('pb.user_id') . ' = ' . $db->quote($this->IJUserID) );
					$query->where($db->quoteName('pb.user_status') . ' IN ('.implode(",", $statusArray).')');
					$query->where($db->quoteName('pb.deleted_by_user') . ' = ' . $db->quote(0));
				}

				$query->where($db->quoteName('pb.protection_id') . ' = ' . $db->quote($protectionID));
				$query->where($db->quoteName('pb.booking_date') . ' >= ' . $db->quote(date('Y-m-d')));

				if(!in_array($protectionID, $protectionInvitaionIDs))
				{
					$query->where($db->quoteName('pb.has_booked') . ' = ' . $db->quote('0'));
				}

				$query->order($db->quoteName('pb.booking_date') . ' ASC');

			$query->select('p.protection_name,p.location,p.city,p.currency_code,p.deposit_per,p.refund_policy')
				->join('INNER','#__beseated_protection AS p ON p.protection_id=pb.protection_id');

			$query->select('ps.service_name,ps.thumb_image,ps.image')
				->join('INNER','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');

			$query->select('bu.full_name,bu.avatar,bu.thumb_avatar,bu.is_fb_user,bu.fb_id')
				->join('INNER','#__beseated_user_profile AS bu ON bu.user_id=pb.user_id');
			/*echo $query->dump();
			exit;*/
			// Set the query and load the result.
			$db->setQuery($query);
			$resProtectionBookings = $db->loadObjectList();

			//echo "<pre/>";print_r($resProtectionBookings);exit;

			$protectionIDs = array();
			$bookingINDX = 0;

			foreach ($resProtectionBookings as $key => $booking)
			{
				$tmpBooking = array();
				if(!in_array($booking->protection_id, $protectionIDs))
				{
					$proccessIDs[]          = $booking->protection_id;
					//$temp['protectionName'] = $booking->protection_name;
					$temp['elementName'] = $booking->protection_name;
					$temp['location']       = $booking->location;
					$temp['city']           = $booking->city;
					$temp['type']           = "Protection";
				}
				$tmpBooking['elementType']      = "Protection";
				$tmpBooking['elementBookingID'] = $booking->protection_booking_id;
				$tmpBooking['elementID']        = $booking->protection_id;
				$tmpBooking['bookingDate']      = $this->helper->convertDateFormat($booking->booking_date);
				$tmpBooking['bookingTime']      = $this->helper->convertToHM($booking->booking_time);
				$tmpBooking['totalPrice']       = $this->helper->currencyFormat('',$booking->total_price,0);
				$tmpBooking['priceToPay']       = $this->helper->currencyFormat('',$booking->total_price,0);
				$tmpBooking['currencyCode']     = $booking->currency_code;
				$tmpBooking['elementsName']     = $booking->protection_name;
				$tmpBooking['serviceName']      = $booking->service_name;
				$tmpBooking['totalGuard']       = $booking->total_guard;
				$tmpBooking['statusCode']       = $booking->user_status;
				$tmpBooking['totalHours']       = $booking->total_hours;
				$tmpBooking['fullName']         = $booking->full_name;
				$tmpBooking['isFbUser']         = $booking->is_fb_user;
				$tmpBooking['fbID']             = ($booking->fb_id && $booking->is_fb_user) ? $booking->fb_id : "0";
				$tmpBooking['capacity']         = "";
				$tmpBooking['pricePerHours']    = $this->helper->currencyFormat('',$booking->price_per_hours);
				$tmpBooking['thumbImage']       = ($booking->thumb_image)?JUri::base().'images/beseated/'.$booking->thumb_image:'';
				$tmpBooking['image']            = ($booking->image)?JUri::base().'images/beseated/'.$booking->image:'';
				$tmpBooking['avatar']           = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
				$tmpBooking['thumbAvatar']      = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
				$tmpBooking['pickup_location']  = '';
				$tmpBooking['dropoff_location'] = '';
				$tmpBooking['params']           = array();
				$tmpBooking['paidByOwner']      = 0;
				$tmpBooking['depositPer']       = $booking->deposit_per;
				$tmpBooking['totalSplitCount']  = $booking->total_split_count;
				$tmpBooking['hasDeposit']       = '';
				$tmpBooking['depositRate']      = $booking->deposit_per;
				$tmpBooking['depositAmount']    = (string)($booking->total_price*$booking->deposit_per/100);
				$tmpBooking['refundPolicyHours']= $booking->refund_policy;

				if($booking->total_split_count)
				{
					$tmpBooking['maxSplitCount']    = $booking->total_split_count - 1 - (($booking->splitted_count) ?  $booking->splitted_count - 1 : 0);
				}
				else
				{
					$tmpBooking['maxSplitCount']    = 0;
				}

				$splitedUserCount = $this->getSplitedUserCount($elementType = 'Protection' , $booking->protection_booking_id);

				if(!$splitedUserCount)
				{
					$tmpBooking['remainingSplitUser']  = 50 - 1;
				}
				else
				{
					if($splitedUserCount > 50)
					{
						$tmpBooking['remainingSplitUser']  = 0;
					}
					else
					{
						$tmpBooking['remainingSplitUser']  = 50 - $splitedUserCount;
					}
				}

				if(in_array($booking->protection_booking_id, $splitedBookigIDs))
				{
					$tmpBooking['bookedType']         = 'share';

					$querySplitDetail = $db->getQuery(true);

					// Create the base select statement.
					$querySplitDetail->select('*')
						->from($db->quoteName('#__beseated_protection_booking_split'))
						->where($db->quoteName('protection_booking_id') . ' = ' . $db->quote($booking->protection_booking_id))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

					// Set the query and load the result.
					$db->setQuery($querySplitDetail);

					$resSplitDetail = $db->loadObject();
					$tmpBooking['statusCode']          = $resSplitDetail->split_payment_status;
					$tmpBooking['params'] = array("splitID" => $resSplitDetail->protection_booking_split_id);

					$booking_invitation_id = $resSplitDetail->protection_booking_split_id;

					if($resSplitDetail->split_payment_status == 7)
					{
						$tmpBooking['paymentURL'] = "";
					}
					else
					{
						$tmpBooking['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$resSplitDetail->protection_booking_split_id.'&booking_type=protection.split';
					}
				}
				else if (in_array($booking->protection_booking_id, $invitationBookingIDs))
				{
					$tmpBooking['bookedType']         = 'invitation';
					$queryInviteDetail = $db->getQuery(true);
					$queryInviteDetail->select('*')
						->from($db->quoteName('#__beseated_invitation'))
						->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($booking->protection_booking_id))
						->where($db->quoteName('element_id') . ' = ' . $db->quote($booking->protection_id))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('protection'))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

					// Set the query and load the result.
					$db->setQuery($queryInviteDetail);

					$resInviteDetail = $db->loadObject();
					$tmpBooking['statusCode']          = $resInviteDetail->user_action;
					$tmpBooking['params'] = array("invitationID" => $resInviteDetail->invitation_id);
					$tmpBooking['paymentURL'] = "";
					$booking_invitation_id = $resInviteDetail->invitation_id;

					/*if($resInviteDetail->split_payment_status == 7)
					{
						$tmpBooking['paymentURL'] = "";
					}
					else
					{
						$tmpBooking['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$resSplitDetail->protection_booking_split_id.'&booking_type=protection.split';
					}*/
				}
				else
				{
					$tmpBooking['bookedType']         = 'booking';
					$booking_invitation_id            = $booking->protection_booking_id;
					if($booking->user_status == 4){
						$tmpBooking['paymentURL'] = JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$booking->protection_booking_id.'&booking_type=protection';
					}
					else{
						$tmpBooking['paymentURL'] = "";
					}
				}

				$tmpBooking['isSplitted']          = $booking->is_splitted;
				$tmpBooking['eachPersonPay']       = $this->helper->currencyFormat('',$booking->each_person_pay);
				$tmpBooking['splittedCount']       = $booking->splitted_count;
				$tmpBooking['remainingAmount']     = $this->helper->currencyFormat('',$booking->remaining_amount);
				if($booking->response_date_time != '0000-00-00 00:00:00'){
					$tmpBooking['remainingTime'] = strtotime($booking->response_date_time);
				}else{
					$tmpBooking['remainingTime'] = '';
				}

				$querySplit = $db->getQuery(true);
				$querySplit->select('split.protection_booking_split_id,split.is_owner,split.user_id,split.email,split.splitted_amount,split.split_payment_status,split.paid_by_owner')
					->from($db->quoteName('#__beseated_protection_booking_split','split'))
					->where($db->quoteName('split.protection_booking_id') . ' = ' . $db->quote($booking->protection_booking_id));
				$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar')
					->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id')
					->order($db->quoteName('bu.full_name') . ' ASC');


				$db->setQuery($querySplit);
				$resSplits = $db->loadObjectList();

				if($resSplits)
				{
					$tempSplit = array();
					foreach ($resSplits as $key => $split)
					{
						if($this->IJUserID == $split->user_id &&  $split->is_owner == '1')
						{
							continue;
						}

						$tempSingleSplit = array();
						$tempSingleSplit['invitationID']   = $split->protection_booking_split_id;
						$tempSingleSplit['fullName']       = ($split->full_name)?$split->full_name:$split->email;
						$tempSingleSplit['splittedAmount'] = $this->helper->currencyFormat('',$split->splitted_amount);
						$tempSingleSplit['statusCode']     = $split->split_payment_status;
						$tempSingleSplit['avatar']         = ($split->avatar)?$this->helper->getUserAvatar($split->avatar):'';
						$tempSingleSplit['thumbAvatar']    = ($split->thumb_avatar)?$this->helper->getUserAvatar($split->thumb_avatar):'';

						if($split->user_id == $booking->user_id)
						{
							//$bookingDetail['priceToPay'] = $this->helper->currencyFormat('',$split->splitted_amount);
							if($split->split_payment_status == 7)    // if is paid
							{
								$bookingDetail['isBookingUserPaid'] =  1;
								//$bookingDetail['paymentURL'] = "";
							}
							else
							{
								//$bookingDetail['isBookingUserPaid'] =  1;
								$bookingDetail['isBookingUserPaid'] =  0;
								//$bookingDetail['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$split->protection_booking_split_id.'&booking_type=protection.split';
							}

						}
						else
						{
							//$tempSplit[] = $tempSingleSplit;
						}

						if($split->user_id == $this->IJUserID && $split->split_payment_status == 7 && $split->is_owner == 0 && $split->paid_by_owner==1)
						{
							$tmpBooking['paidByOwner'] = 1;
						}
						/*else{
							$tempSingleSplit['paidByOwner'] = 0;
						}*/

						$tempSplit[] = $tempSingleSplit;
					}
					$tmpBooking['splits'] = $tempSplit;
				}
				else
				{
					$tmpBooking['splits'] = array();
				}

				$tmpBooking['isRead']  = $this->helper->isReadRSVP('protection',$tmpBooking['bookedType'],$booking_invitation_id);

				$temp['bookings'][$bookingINDX]    = $tmpBooking;
				$bookingINDX++;
			}

			$resultProtectionBookings[] = $temp;
		}

		// End of Get Protection RSVP

		// Start of Get Chauffeur RSVP
		$chauffeurIDsql = $db->getQuery(true);
		$chauffeurIDsql->select('chauffeur_id')
			->from($db->quoteName('#__beseated_chauffeur_booking'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('deleted_by_user') . ' = ' . $db->quote(0))
			->group($db->quoteName('chauffeur_booking_id'))
			->order($db->quoteName('booking_date') . ' ASC');
		$db->setQuery($chauffeurIDsql);

		$resChauffeurIDs = $db->loadColumn();

		$chauffeursSplitsql = $db->getQuery(true);
		$chauffeursSplitsql->select('chauffeur_booking_id,chauffeur_id')
			->from($db->quoteName('#__beseated_chauffeur_booking_split'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('split_payment_status') . ' IN ('.implode(",", $invitationStatus).')')
			->where($db->quoteName('is_owner') . ' = ' . $db->quote(0));
		$db->setQuery($chauffeursSplitsql);

		$resChauffeurSplits = $db->loadObjectList();



		$allChauffeurID     = $resChauffeurIDs;
		$splitedBookigIDs    = array();
		$otherBookingIDs = array();

		foreach ($resChauffeurSplits as $key => $splitDetail)
		{
			if(!in_array($splitDetail->chauffeur_id, $allChauffeurID))
			{
				$allChauffeurID[] = $splitDetail->chauffeur_id;
			}

			$splitedBookigIDs[] = $splitDetail->chauffeur_booking_id;
			$otherBookingIDs[]  = $splitDetail->chauffeur_booking_id;
		}

		$chauffeursInvitesql = $db->getQuery(true);
		$chauffeursInvitesql->select('element_booking_id,element_id')
			->from($db->quoteName('#__beseated_invitation'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('user_action') . ' IN ('.implode(",", $invitedUserStatus).')')  // by jamal
			->where($db->quoteName('element_type') . ' = ' . $db->quote('chauffeur'));
		$db->setQuery($chauffeursInvitesql);
		$resChauffeurInvites = $db->loadObjectList();

		$invitationBookingIDs = array();
		$chauffeurInvitaionIDs = array();

		foreach ($resChauffeurInvites as $key => $invitation)
		{
			$chauffeurInvitaionIDs[] = $invitation->element_id;

			if(!in_array($invitation->element_id, $allChauffeurID))
			{
				$allChauffeurID[] = $invitation->element_id;
			}

			$otherBookingIDs[]      = $invitation->element_booking_id;
			$invitationBookingIDs[] = $invitation->element_booking_id;
		}

		$resultChauffeurBookings = array();
		$proccessIDs              = array();


		foreach ($allChauffeurID as $key => $chauffeurID)
		{
			$temp  = array();
			$query = $db->getQuery(true);
			// Create the base select statement.
			$query->select('cb.chauffeur_booking_id,cb.chauffeur_id,cb.booking_date,cb.booking_time,cb.pickup_location,cb.dropoff_location,cb.capacity,cb.user_status,cb.total_price,cb.booking_currency_code,cb.is_splitted,cb.has_invitation,cb.each_person_pay,cb.splitted_count,cb.remaining_amount,cb.response_date_time,cb.total_split_count')
				->from($db->quoteName('#__beseated_chauffeur_booking') . ' AS cb');
				if(count($otherBookingIDs)!= 0)
				{
					$query->where(
						'('.$db->quoteName('cb.chauffeur_booking_id') . ' IN ('.implode(",", $otherBookingIDs).') OR '.
						'('.
							$db->quoteName('cb.user_id') . ' = ' . $db->quote($this->IJUserID) .' AND '.
							$db->quoteName('cb.user_status') . ' IN ('.implode(",", $statusArray).')' .' AND '.
							$db->quoteName('cb.deleted_by_user') . ' = ' . $db->quote(0) .
						')'.
						')'
					);
				}
				else{
					$query->where($db->quoteName('cb.user_id') . ' = ' . $db->quote($this->IJUserID) );
					$query->where($db->quoteName('cb.user_status') . ' IN ('.implode(",", $statusArray).')');
					$query->where($db->quoteName('cb.deleted_by_user') . ' = ' . $db->quote(0));
				}

				$query->where($db->quoteName('cb.chauffeur_id') . ' = ' . $db->quote($chauffeurID));
				$query->where($db->quoteName('cb.booking_date') . ' >= ' . $db->quote(date('Y-m-d')));

				if(!in_array($chauffeurID, $chauffeurInvitaionIDs))
				{
					$query->where($db->quoteName('cb.has_booked') . ' = ' . $db->quote('0'));
				}

				$query->order($db->quoteName('cb.booking_date') . ' ASC');

			$query->select('c.chauffeur_name,c.location,c.city,c.deposit_per,c.refund_policy')
				->join('INNER','#__beseated_chauffeur AS c ON c.chauffeur_id=cb.chauffeur_id');

			$query->select('cs.service_name,cs.service_type,cs.thumb_image,cs.image,cs.capacity')
				->join('INNER','#__beseated_chauffeur_services AS cs ON cs.service_id=cb.service_id');

			$query->select('bu.full_name,bu.avatar,bu.thumb_avatar,bu.is_fb_user,bu.fb_id')
				->join('INNER','#__beseated_user_profile AS bu ON bu.user_id=cb.user_id');
			/*echo $query->dump();
			exit;*/
			// Set the query and load the result.
			$db->setQuery($query);
			$resChauffeurBookings = $db->loadObjectList();


			$chauffeurIDs = array();
			$bookingINDX = 0;
			//$resultChauffeurBookings = array();

			//echo "<pre/>";print_r($resChauffeurBookings);exit;

			foreach ($resChauffeurBookings as $key => $booking)
			{
				$tmpBooking = array();
				if(!in_array($booking->chauffeur_id, $chauffeurIDs))
				{
					$proccessIDs[]          = $booking->chauffeur_id;
					//$temp['chauffeurName'] = $booking->chauffeur_name;
					$temp['elementName'] = $booking->chauffeur_name;
					$temp['location']       = $booking->location;
					$temp['city']           = $booking->city;
					$temp['type']           = "Chauffeur";
				}
				$tmpBooking['elementType']      = "Chauffeur";
				$tmpBooking['elementBookingID'] = $booking->chauffeur_booking_id;
				$tmpBooking['elementID']        = $booking->chauffeur_id;
				$tmpBooking['bookingDate']      = $this->helper->convertDateFormat($booking->booking_date);
				$tmpBooking['bookingTime']      = $this->helper->convertToHM($booking->booking_time);
				$tmpBooking['totalPrice']       = $this->helper->currencyFormat('',$booking->total_price,0);
				$tmpBooking['priceToPay']       = $this->helper->currencyFormat('',$booking->total_price,0);
				$tmpBooking['currencyCode']     = $booking->booking_currency_code;
				$tmpBooking['elementsName']     = $booking->chauffeur_name;
				$tmpBooking['serviceName']      = $booking->service_name;
				$tmpBooking['statusCode']       = $booking->user_status;
				$tmpBooking['totalHours']       = "";
				$tmpBooking['fullName']         = $booking->full_name;
				$tmpBooking['isFbUser']         = $booking->is_fb_user;
				$tmpBooking['fbID']             = ($booking->fb_id && $booking->is_fb_user) ? $booking->fb_id : "0";
				$tmpBooking['pricePerHours']    = "";
				$tmpBooking['capacity']         = $booking->capacity;
				$tmpBooking['thumbImage']       = ($booking->thumb_image)?JUri::base().'images/beseated/'.$booking->thumb_image:'';
				$tmpBooking['image']            = ($booking->image)?JUri::base().'images/beseated/'.$booking->image:'';
				$tmpBooking['avatar']           = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
				$tmpBooking['thumbAvatar']      = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
				$tmpBooking['pickup_location']  = $booking->pickup_location;
				$tmpBooking['dropoff_location'] = $booking->dropoff_location;
				$tmpBooking['params']           = array();
				$tmpBooking['paidByOwner']      = 0;
				$tmpBooking['depositPer']       = $booking->deposit_per;
				$tmpBooking['totalSplitCount']  = $booking->total_split_count;
				$tmpBooking['totalGuard']       = '';
				$tmpBooking['hasDeposit']       = '';
				$tmpBooking['depositRate']      = $booking->deposit_per;
				$tmpBooking['depositAmount']    = (string)($booking->total_price*$booking->deposit_per/100);
				$tmpBooking['refundPolicyHours']= $booking->refund_policy;

				//$tmpBooking['maxSplitCount']    = $booking->total_split_count - 1 - (($booking->splitted_count) ?  $booking->splitted_count - 1 : 0);

				if($booking->total_split_count)
				{
					$tmpBooking['maxSplitCount']    = $booking->total_split_count - 1 - (($booking->splitted_count) ?  $booking->splitted_count - 1 : 0);
				}
				else
				{
					$tmpBooking['maxSplitCount']    = 0;
				}


				$splitedUserCount = $this->getSplitedUserCount($elementType = 'Chauffeur' , $booking->chauffeur_booking_id);

				if(!$splitedUserCount)
				{
					$tmpBooking['remainingSplitUser']  =  $booking->capacity - 1;
				}
				else
				{
					if($splitedUserCount > $booking->capacity)
					{
						$tmpBooking['remainingSplitUser']  = 0;
					}
					else
					{
						$tmpBooking['remainingSplitUser']  =  $booking->capacity - $splitedUserCount;
					}

				}


				if(in_array($booking->chauffeur_booking_id, $splitedBookigIDs))
				{
					$tmpBooking['bookedType']         = 'share';

					$querySplitDetail = $db->getQuery(true);

					// Create the base select statement.
					$querySplitDetail->select('*')
						->from($db->quoteName('#__beseated_chauffeur_booking_split'))
						->where($db->quoteName('chauffeur_booking_id') . ' = ' . $db->quote($booking->chauffeur_booking_id))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

					// Set the query and load the result.
					$db->setQuery($querySplitDetail);

					$resSplitDetail = $db->loadObject();
					$tmpBooking['statusCode']          = $resSplitDetail->split_payment_status;
					$tmpBooking['params'] = array("splitID" => $resSplitDetail->chauffeur_booking_split_id);
					$booking_invitation_id = $resSplitDetail->chauffeur_booking_split_id;

					if($resSplitDetail->split_payment_status == 7)
					{
						$tmpBooking['paymentURL'] = "";
					}
					else
					{
						$tmpBooking['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$resSplitDetail->chauffeur_booking_split_id.'&booking_type=chauffeur.split';
					}
				}
				else if (in_array($booking->chauffeur_booking_id, $invitationBookingIDs))
				{
					$tmpBooking['bookedType']         = 'invitation';
					$queryInviteDetail = $db->getQuery(true);
					$queryInviteDetail->select('*')
						->from($db->quoteName('#__beseated_invitation'))
						->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($booking->chauffeur_booking_id))
						->where($db->quoteName('element_id') . ' = ' . $db->quote($booking->chauffeur_id))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('chauffeur'))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

					// Set the query and load the result.
					$db->setQuery($queryInviteDetail);

					$resInviteDetail = $db->loadObject();
					$tmpBooking['statusCode']          = $resInviteDetail->user_action;
					$tmpBooking['params'] = array("invitationID" => $resInviteDetail->invitation_id);
					$tmpBooking['paymentURL'] = "";
					$booking_invitation_id = $resInviteDetail->invitation_id;
				}
				else
				{
					$tmpBooking['bookedType']         = 'booking';
					$booking_invitation_id = $booking->chauffeur_booking_id;
					if($booking->user_status == 4){
						$tmpBooking['paymentURL'] = JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$booking->chauffeur_booking_id.'&booking_type=chauffeur';
					}
					else{
						$tmpBooking['paymentURL'] = "";
					}
				}

				$tmpBooking['isSplitted']          = $booking->is_splitted;
				$tmpBooking['eachPersonPay']       = $this->helper->currencyFormat('',$booking->each_person_pay);
				$tmpBooking['splittedCount']       = $booking->splitted_count;
				$tmpBooking['remainingAmount']     = $this->helper->currencyFormat('',$booking->remaining_amount);
				if($booking->response_date_time != '0000-00-00 00:00:00'){
					$tmpBooking['remainingTime'] = strtotime($booking->response_date_time);
				}else{
					$tmpBooking['remainingTime'] = '';
				}

				$querySplit = $db->getQuery(true);
				$querySplit->select('split.chauffeur_booking_split_id,split.user_id,split.is_owner,split.email,split.splitted_amount,split.split_payment_status,split.paid_by_owner')
					->from($db->quoteName('#__beseated_chauffeur_booking_split','split'))
					->where($db->quoteName('split.chauffeur_booking_id') . ' = ' . $db->quote($booking->chauffeur_booking_id));
				$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar')
					->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id')
					->order($db->quoteName('bu.full_name') . ' ASC');

				$db->setQuery($querySplit);
				$resSplits = $db->loadObjectList();

				if($resSplits)
				{
					$tempSplit = array();
					foreach ($resSplits as $key => $split)
					{
						if($this->IJUserID == $split->user_id &&  $split->is_owner == '1')
						{
							continue;
						}

						$tempSingleSplit = array();
						$tempSingleSplit['invitationID']   = $split->chauffeur_booking_split_id;
						$tempSingleSplit['fullName']       = ($split->full_name)?$split->full_name:$split->email;
						$tempSingleSplit['splittedAmount'] = $this->helper->currencyFormat('',$split->splitted_amount);
						$tempSingleSplit['statusCode']     = $split->split_payment_status;
						$tempSingleSplit['avatar']         = ($split->avatar)?$this->helper->getUserAvatar($split->avatar):'';
						$tempSingleSplit['thumbAvatar']    = ($split->thumb_avatar)?$this->helper->getUserAvatar($split->thumb_avatar):'';

						if($split->user_id == $booking->user_id)
						{
							//$bookingDetail['priceToPay'] = $this->helper->currencyFormat('',$split->splitted_amount);
							if($split->split_payment_status == 7)    // if is paid
							{
								$bookingDetail['isBookingUserPaid'] =  1;
								$bookingDetail['paymentURL'] = "";
							}
							else
							{
								//$bookingDetail['isBookingUserPaid'] =  1;
								$bookingDetail['isBookingUserPaid'] =  0;
								$bookingDetail['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$split->chauffeur_booking_split_id.'&booking_type=chauffeur.split';
							}

						}
						else
						{
							//$tempSplit[] = $tempSingleSplit;
						}

						if($split->user_id == $this->IJUserID && $split->split_payment_status == 7 && $split->is_owner == 0 && $split->paid_by_owner==1)
						{
							$tmpBooking['paidByOwner'] = 1;
						}
						/*else{
							$tempSingleSplit['paidByOwner'] = 0;
						}*/

						$tempSplit[] = $tempSingleSplit;
					}
					$tmpBooking['splits'] = $tempSplit;
				}
				else
				{
					$tmpBooking['splits'] = array();
				}

				$tmpBooking['isRead']  = $this->helper->isReadRSVP('chauffeur',$tmpBooking['bookedType'],$booking_invitation_id);

				$temp['bookings'][$bookingINDX]    = $tmpBooking;


				$bookingINDX++;

				//echo "<pre/>";print_r($temp);exit;
			}

			$resultChauffeurBookings[] = $temp;

			//echo "<pre/>";print_r($resultChauffeurBookings);exit;
		}

		$resultChauffeurBookings = array_map("unserialize", array_unique(array_map("serialize", $resultChauffeurBookings)));
		//echo "<pre/>";print_r($input);exit;
		// End of Get Chauffeur RSVP

		// Start of Get Yacht RSVP
		$yachtsIDsql = $db->getQuery(true);
		$yachtsIDsql->select('yacht_id')
			->from($db->quoteName('#__beseated_yacht_booking'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('deleted_by_user') . ' = ' . $db->quote(0))
			->group($db->quoteName('yacht_id'))
			->order($db->quoteName('booking_date') . ' ASC');
		$db->setQuery($yachtsIDsql);
		$resYachtIDs = $db->loadColumn();

		$invitationStatus = array();
		$invitationStatus[] = $this->helper->getStatusID('pending');
		//$invitationStatus[] = $this->helper->getStatusID('paid');

		$yachtsSplitsql = $db->getQuery(true);
		$yachtsSplitsql->select('yacht_booking_id,yacht_id')
			->from($db->quoteName('#__beseated_yacht_booking_split'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('split_payment_status') . ' IN ('.implode(",", $invitationStatus).') ')
			->where($db->quoteName('is_owner') . ' = ' . $db->quote(0));
		$db->setQuery($yachtsSplitsql);

		$resYachtSplits = $db->loadObjectList();
		$allYachtID     = $resYachtIDs;
		$splitedBookigIDs    = array();
		$otherBookingIDs = array();

		foreach ($resYachtSplits as $key => $splitDetail)
		{
			if(!in_array($splitDetail->yacht_id, $allYachtID))
			{
				$allYachtID[] = $splitDetail->yacht_id;
			}

			$splitedBookigIDs[] = $splitDetail->yacht_booking_id;
			$otherBookingIDs[]  = $splitDetail->yacht_booking_id;
		}

		$yachtInvitesql = $db->getQuery(true);
		$yachtInvitesql->select('element_booking_id,element_id')
			->from($db->quoteName('#__beseated_invitation'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('user_action') . ' IN ('.implode(",", $invitedUserStatus).')')  // by jamal
			->where($db->quoteName('element_type') . ' = ' . $db->quote('yacht'));
		$db->setQuery($yachtInvitesql);
		$resYachtInvites = $db->loadObjectList();

		$invitationBookingIDs  = array();
		$yachtInvitaionIDs = array();

		foreach ($resYachtInvites as $key => $invitation)
		{
			$yachtInvitaionIDs[] = $invitation->element_id;

			if(!in_array($invitation->element_id, $allYachtID))
			{
				$allYachtID[] = $invitation->element_id;
			}

			$otherBookingIDs[]      = $invitation->element_booking_id;
			$invitationBookingIDs[] = $invitation->element_booking_id;
		}

		$resultYachtBookings = array();
		$proccessIDs              = array();

		foreach ($allYachtID as $key => $yachtID)
		{
			$temp  = array();
			$query = $db->getQuery(true);
			// Create the base select statement.
			$query->select('yb.yacht_booking_id,yb.yacht_id,yb.user_id,yb.booking_date,yb.booking_time,yb.total_hours,yb.price_per_hours,yb.capacity,yb.total_price,yb.user_status,yb.booking_currency_code,yb.is_splitted,yb.has_invitation,yb.each_person_pay,yb.splitted_count,yb.remaining_amount,yb.response_date_time,yb.total_split_count,yb.splitted_count')
				->from($db->quoteName('#__beseated_yacht_booking') . ' AS yb');
				if(count($otherBookingIDs)!= 0)
				{
					$query->where(
						'('.$db->quoteName('yb.yacht_booking_id') . ' IN ('.implode(",", $otherBookingIDs).') OR '.
						'('.
							$db->quoteName('yb.user_id') . ' = ' . $db->quote($this->IJUserID) .' AND '.
							$db->quoteName('yb.user_status') . ' IN ('.implode(",", $statusArray).')' .' AND '.
							$db->quoteName('yb.deleted_by_user') . ' = ' . $db->quote(0) .
						')'.
						')'
					);
				}
				else{
					$query->where($db->quoteName('yb.user_id') . ' = ' . $db->quote($this->IJUserID) );
					$query->where($db->quoteName('yb.user_status') . ' IN ('.implode(",", $statusArray).')');
					$query->where($db->quoteName('yb.deleted_by_user') . ' = ' . $db->quote(0));
				}

				$query->where($db->quoteName('yb.yacht_id') . ' = ' . $db->quote($yachtID));
				$query->where($db->quoteName('yb.booking_date') . ' >= ' . $db->quote(date('Y-m-d')));

				if(!in_array($yachtID, $yachtInvitaionIDs))
				{
					$query->where($db->quoteName('yb.has_booked') . ' = ' . $db->quote('0'));
				}

				$query->order($db->quoteName('yb.booking_date') . ' ASC');

			$query->select('y.yacht_name,y.location,y.city,y.deposit_per,y.refund_policy')
				->join('INNER','#__beseated_yacht AS y ON y.yacht_id=yb.yacht_id');

			$query->select('ys.service_name,ys.service_type,ys.dock,ys.thumb_image,ys.image,ys.capacity')
				->join('INNER','#__beseated_yacht_services AS ys ON ys.service_id=yb.service_id');

			$query->select('bu.full_name,bu.avatar,bu.thumb_avatar,bu.is_fb_user,bu.fb_id')
				->join('INNER','#__beseated_user_profile AS bu ON bu.user_id=yb.user_id');

			// Set the query and load the result.
			$db->setQuery($query);
			$resYachtBookings = $db->loadObjectList();

			$yachtIDs = array();
			$bookingINDX = 0;

			foreach ($resYachtBookings as $key => $booking)
			{
				$tmpBooking = array();
				if(!in_array($booking->yacht_id, $yachtIDs))
				{
					$proccessIDs[]     = $booking->yacht_id;
					//$temp['yachtName'] = $booking->yacht_name;
					$temp['elementName'] = $booking->yacht_name;
					$temp['location']  = $booking->location;
					$temp['city']      = $booking->city;
					$temp['type']      = "Yacht";
				}

				$tmpBooking['elementType']      = "Yacht";
				$tmpBooking['elementBookingID'] = $booking->yacht_booking_id;
				$tmpBooking['elementID']        = $booking->yacht_id;
				$tmpBooking['bookingDate']      = $this->helper->convertDateFormat($booking->booking_date);
				$tmpBooking['bookingTime']      = $this->helper->convertToHM($booking->booking_time);
				$tmpBooking['totalPrice']       = $this->helper->currencyFormat('',$booking->total_price,0);
				$tmpBooking['priceToPay']       = $this->helper->currencyFormat('',$booking->total_price,0);
				$tmpBooking['currencyCode']     = $booking->booking_currency_code;
				$tmpBooking['elementsName']     = $booking->yacht_name;
				$tmpBooking['serviceName']      = $booking->service_name;
				$tmpBooking['statusCode']       = $booking->user_status;
				$tmpBooking['fullName']         = $booking->full_name;
				$tmpBooking['isFbUser']         = $booking->is_fb_user;
				$tmpBooking['fbID']             = ($booking->fb_id && $booking->is_fb_user) ? $booking->fb_id : "0";
				$tmpBooking['totalHours']       = $booking->total_hours;
				$tmpBooking['pricePerHours']    = $this->helper->currencyFormat('',$booking->price_per_hours);
				$tmpBooking['capacity']         = $booking->capacity;
				$tmpBooking['image']            = ($booking->image)?JUri::base().'images/beseated/'.$booking->image:'';
				$tmpBooking['thumbImage']       = ($booking->thumb_image)?JUri::base().'images/beseated/'.$booking->thumb_image:'';
				$tmpBooking['avatar']           = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
				$tmpBooking['thumbAvatar']      = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
				$tmpBooking['pickup_location']  = '';
				$tmpBooking['dropoff_location'] = '';
				$tmpBooking['params']           = array();
				$tmpBooking['paidByOwner']      = 0;
				$tmpBooking['depositPer']       = $booking->deposit_per;
				$tmpBooking['totalSplitCount']  = $booking->total_split_count;
				$tmpBooking['totalGuard']       = '';
				$tmpBooking['hasDeposit']       = '';
				$tmpBooking['depositRate']      =  $booking->deposit_per;
				$tmpBooking['depositAmount']    = (string)($booking->total_price*$booking->deposit_per/100);
				$tmpBooking['refundPolicyHours'] = $booking->refund_policy;
				//$tmpBooking['maxSplitCount']    = $booking->total_split_count - $booking->splitted_count;
				//$tmpBooking['maxSplitCount']    = $booking->total_split_count - 1 - (($booking->splitted_count) ?  $booking->splitted_count - 1 : 0);

				if($booking->total_split_count)
				{
					$tmpBooking['maxSplitCount']    = $booking->total_split_count - 1 - (($booking->splitted_count) ?  $booking->splitted_count - 1 : 0);
				}
				else
				{
					$tmpBooking['maxSplitCount']    = 0;
				}

				$splitedUserCount = $this->getSplitedUserCount($elementType = 'Yacht' , $booking->yacht_booking_id);

				if(!$splitedUserCount)
				{
					$tmpBooking['remainingSplitUser']  = $booking->capacity - 1;
				}
				else
				{
					if($splitedUserCount > $booking->capacity)
					{
						$tmpBooking['remainingSplitUser']  = 0;
					}
					else
					{
						$tmpBooking['remainingSplitUser']  = $booking->capacity - $splitedUserCount;
					}

				}

				if(in_array($booking->yacht_booking_id, $splitedBookigIDs))
				{
					$tmpBooking['bookedType']         = 'share';

					$querySplitDetail = $db->getQuery(true);

					// Create the base select statement.
					$querySplitDetail->select('*')
						->from($db->quoteName('#__beseated_yacht_booking_split'))
						->where($db->quoteName('yacht_booking_id') . ' = ' . $db->quote($booking->yacht_booking_id))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

					// Set the query and load the result.
					$db->setQuery($querySplitDetail);

					$resSplitDetail = $db->loadObject();

					/*if($booking->yacht_booking_id == 39)
					{
						echo "<pre>";
						print_r($resSplitDetail);
						echo "</pre>";
						exit;
					}*/

					$tmpBooking['statusCode'] = $resSplitDetail->split_payment_status;
					$tmpBooking['params']     = array("splitID" => $resSplitDetail->yacht_booking_split_id);
					$booking_invitation_id    = $resSplitDetail->yacht_booking_split_id;

					if($resSplitDetail->split_payment_status == 7)
					{
						$tmpBooking['paymentURL'] = "";
					}
					else
					{
						$tmpBooking['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$resSplitDetail->yacht_booking_split_id.'&booking_type=yacht.split';
					}


				}
				else if (in_array($booking->yacht_booking_id, $invitationBookingIDs))
				{
					$tmpBooking['bookedType']         = 'invitation';
					$queryInviteDetail = $db->getQuery(true);
					$queryInviteDetail->select('*')
						->from($db->quoteName('#__beseated_invitation'))
						->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($booking->yacht_booking_id))
						->where($db->quoteName('element_id') . ' = ' . $db->quote($booking->yacht_id))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('yacht'))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

					// Set the query and load the result.
					$db->setQuery($queryInviteDetail);

					$resInviteDetail = $db->loadObject();
					$tmpBooking['statusCode'] = $resInviteDetail->user_action;
					$tmpBooking['params']     = array("invitationID" => $resInviteDetail->invitation_id);
					$tmpBooking['paymentURL'] = "";
					$booking_invitation_id    = $resInviteDetail->invitation_id;
				}
				else
				{
					$tmpBooking['bookedType']         = 'booking';
					$booking_invitation_id    =  $booking->yacht_booking_id;
					if($booking->user_status == 4){
						$tmpBooking['paymentURL'] = JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$booking->yacht_booking_id.'&booking_type=yacht';
					}
					else{
						$tmpBooking['paymentURL'] = "";
					}
				}

				$tmpBooking['isSplitted']          = $booking->is_splitted;
				$tmpBooking['eachPersonPay']       = $this->helper->currencyFormat('',$booking->each_person_pay);
				$tmpBooking['splittedCount']       = $booking->splitted_count;
				$tmpBooking['remainingAmount']     = $this->helper->currencyFormat('',$booking->remaining_amount);
				if($booking->response_date_time != '0000-00-00 00:00:00'){
					$tmpBooking['remainingTime'] = strtotime($booking->response_date_time);
				}else{
					$tmpBooking['remainingTime'] = '';
				}

				$querySplit = $db->getQuery(true);
				$querySplit->select('split.yacht_booking_split_id,split.user_id,split.is_owner,split.email,split.splitted_amount,split.split_payment_status,split.paid_by_owner')
					->from($db->quoteName('#__beseated_yacht_booking_split','split'))
					->where($db->quoteName('split.yacht_booking_id') . ' = ' . $db->quote($booking->yacht_booking_id));
				$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar')
					->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id')
					->order($db->quoteName('bu.full_name') . ' ASC');

				$db->setQuery($querySplit);
				$resSplits = $db->loadObjectList();

				if($resSplits)
				{
					$tempSplit = array();
					foreach ($resSplits as $key => $split)
					{
						if($this->IJUserID == $split->user_id &&  $split->is_owner == '1')
						{
							continue;
						}

						$tempSingleSplit = array();
						$tempSingleSplit['invitationID']   = $split->yacht_booking_split_id;
						$tempSingleSplit['fullName']       = ($split->full_name)?$split->full_name:$split->email;
						$tempSingleSplit['splittedAmount'] = $this->helper->currencyFormat('',$split->splitted_amount);
						$tempSingleSplit['statusCode']     = $split->split_payment_status;
						$tempSingleSplit['avatar']         = ($split->avatar)?$this->helper->getUserAvatar($split->avatar):'';
						$tempSingleSplit['thumbAvatar']    = ($split->thumb_avatar)?$this->helper->getUserAvatar($split->thumb_avatar):'';

						if($split->user_id == $booking->user_id)
						{
							//$bookingDetail['priceToPay'] = $this->helper->currencyFormat('',$split->splitted_amount);
							if($split->split_payment_status == 7)    // if is paid
							{
								$bookingDetail['isBookingUserPaid'] =  1;
								$bookingDetail['paymentURL'] = "";
							}
							else
							{
								$bookingDetail['isBookingUserPaid'] =  0;
								$bookingDetail['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$split->yacht_booking_split_id.'&booking_type=yacht.split';
							}

						}
						else
						{
							//$tempSplit[] = $tempSingleSplit;
						}

						if($split->user_id == $this->IJUserID && $split->split_payment_status == 7 && $split->is_owner == 0 && $split->paid_by_owner==1)
						{
							$tmpBooking['paidByOwner'] = 1;
						}
						/*else{
							$tempSingleSplit['paidByOwner'] = 0;
						}*/

						$tempSplit[] = $tempSingleSplit;
					}
					$tmpBooking['splits'] = $tempSplit;
				}
				else
				{
					$tmpBooking['splits'] = array();
				}

				$tmpBooking['isRead']  = $this->helper->isReadRSVP('yacht',$tmpBooking['bookedType'],$booking_invitation_id);

				$temp['bookings'][$bookingINDX]    = $tmpBooking;
				$bookingINDX++;
			}

			$resultYachtBookings[] = $temp;
		}
		// End of Get Chauffeur RSVP

		$eventStatusArray[] = $this->helper->getStatusID('decline');
		$eventStatusArray[] = $this->helper->getStatusID('request');

		// Start of Get Event RSVP
		$eventInvitesql = $db->getQuery(true);
		$eventInvitesql->select('tbi.invite_id,tbi.ticket_booking_id,tbi.ticket_booking_detail_id,tbi.ticket_id,tbi.event_id,tbi.user_id,tbi.invited_user_id,tbi.email,tbi.fbid,tbi.invited_user_status')
			->from($db->quoteName('#__beseated_event_ticket_booking_invite','tbi'))
			->where($db->quoteName('tbi.invited_user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('tbi.deleted_by_user') . ' = ' . $db->quote('0'))
		    ->where($db->quoteName('tbi.invited_user_status') . ' IN ('.implode(",", $eventStatusArray).')')
			->where($db->quoteName('tbi.user_id') . ' <> ' . $db->quote($this->IJUserID));

		$eventInvitesql->select('e.event_name,e.event_desc,e.image,e.thumb_image,e.location,e.city,e.event_date,e.event_time,e.latitude,e.longitude')
			->join('INNER','#__beseated_event AS e ON e.event_id=tbi.event_id')
			//->where('STR_TO_DATE(CONCAT('.$this->db->quoteName('e.event_date').', " ", '.$this->db->quoteName('e.event_time').'),"%Y-%m-%d %H:%i:%s")' . ' >= ' . $this->db->quote(date('Y-m-d H:i:s')));
			->where('CONCAT('.$db->quoteName('e.event_date').', " ", '.$db->quoteName('e.event_time') . ') >= ' . $db->quote(date('Y-m-d H:i:s')));

		$eventInvitesql->select('etb.ticket_price,etb.ticket_price,etb.booking_currency_code,etb.booking_currency_sign')
			->join('INNER','#__beseated_event_ticket_booking AS etb ON etb.ticket_booking_id=tbi.ticket_booking_id');

		$eventInvitesql->select('timg.thumb_image AS ticket_thumb_image,timg.image AS ticket_image')
			->join('INNER','#__beseated_element_images AS timg ON timg.image_id=tbi.ticket_id');

		$eventInvitesql->select('bu.full_name,bu.avatar,bu.thumb_avatar,bu.is_fb_user,bu.fb_id')
			->join('INNER','#__beseated_user_profile AS bu ON bu.user_id=tbi.user_id');

		$query->order('CONCAT('.$this->db->quoteName('e.event_date').', " ", '.$this->db->quoteName('e.event_time').')' . ' ASC');

		$db->setQuery($eventInvitesql);
		$resEventInvitations = $db->loadObjectList();

		$eventIDs            = array();
		$allInvitations = array();
		$resEvents = array();
		$resultEvents = array();
		foreach ($resEventInvitations as $key => $booking)
		{
			if(!in_array($booking->event_id, $eventIDs)){
				$eventIDs[] = $booking->event_id;
				$tmpEvent = array();
				$tmpEvent['eventName'] = $booking->event_name;
				$tmpEvent['location']  = $booking->location;
				$tmpEvent['city']      = $booking->city;
				$tmpEvent['type']      = "Event";
				$tmpEvent['eventID']   = $booking->event_id;
				$resEvents[]           = $tmpEvent;
			}

			$tmpBooking = array();
			$tmpBooking['inviteID']         = $booking->invite_id;
			$tmpBooking['eventBookingID']   = $booking->ticket_booking_id;
			$tmpBooking['eventEvent']       = $booking->event_id;
			$tmpBooking['ticketID']         = $booking->ticket_id;
			$tmpBooking['eventName']        = $booking->event_name;
			$tmpBooking['eventDesc']        = $booking->event_desc;
			$tmpBooking['image']            = ($booking->image)?JUri::root().'images/beseated/'.$booking->image:'';
			$tmpBooking['thumbImage']       = ($booking->thumb_image)?JUri::root().'images/beseated/'.$booking->thumb_image:'';
			$tmpBooking['location']         = $booking->location;
			$tmpBooking['city']             = $booking->city;
			$tmpBooking['eventDate']        = $this->helper->convertDateFormat($booking->event_date);
			$tmpBooking['eventTime']        = $this->helper->convertToHM($booking->event_time);
			$tmpBooking['ticket_price']     = $this->helper->currencyFormat('',$booking->ticket_price);
			$tmpBooking['currencyCode']     = $booking->booking_currency_code;
			$tmpBooking['currencySign']     = $booking->booking_currency_sign;
			$tmpBooking['ticketThumbImage'] = ($booking->ticket_thumb_image)?JUri::root().'images/beseated/'.$booking->ticket_thumb_image:'';
			$tmpBooking['ticketImage']      = ($booking->ticket_image)?JUri::root().'images/beseated/'.$booking->ticket_image:'';
			$tmpBooking['fullName']         = $booking->full_name;
			$tmpBooking['isFbUser']         = $booking->is_fb_user;
			$tmpBooking['fbID']             = ($booking->fb_id && $booking->is_fb_user) ? $booking->fb_id : "0";
			$tmpBooking['avatar']           = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$tmpBooking['thumbAvatar']      = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
			$tmpBooking['statusCode']       = $booking->invited_user_status;
			$tmpBooking['avatar']           = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$tmpBooking['thumbAvatar']      = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
			$tmpBooking['pickup_location']  = '';
		    $tmpBooking['dropoff_location'] = '';
		    $tmpBooking['bookedType']       = 'invitation';
		    $tmpBooking['paymentURL']       = "";
		    $tmpBooking['maxSplitCount']    = '';
		    $tmpBooking['totalSplitCount']  = '';
		    $tmpBooking['totalGuard']       = '';
		    $tmpBooking['hasDeposit']       = '';
		    $tmpBooking['depositRate']      = '';
		    $tmpBooking['refundPolicyHours']= '';

		    $tmpBooking['isRead']  = $this->helper->isReadRSVP('event',$tmpBooking['bookedType'],$booking->invite_id);

			$allInvitations[$booking->event_id][] = $tmpBooking;
		}

		if(count($allInvitations) != 0)
		{
			foreach ($resEvents as $key => $event)
			{
				$tmpEvent = array();
				$tmpEvent['eventName'] = $event['eventName'];
				$tmpEvent['location']  = $event['location'];
				$tmpEvent['city']      = $event['city'];
				$tmpEvent['type']      = $event['type'];
				if(isset($allInvitations[$event['eventID']])){
					$tmpEvent['bookings'] = $allInvitations[$event['eventID']];
				}else{
					$tmpEvent['bookings'] = array();
				}
				$resultEvents[] = $tmpEvent;
			}
		}
		// End of Get Chauffeur RSVP

		$this->jsonarray['code']           = 200;

		$resultProtectionBookings = array_values(array_filter($resultProtectionBookings));
		$this->jsonarray['venueRSVP']      = $resultVenueBookings;

		$this->jsonarray['luxuryRSVP'] =  array_values(array_filter(array_merge($resultProtectionBookings,$resultChauffeurBookings,$resultYachtBookings)));
		//$this->jsonarray['protectionRSVP'] = $resultProtectionBookings;
		//$this->jsonarray['chauffeurRSVP']  = $resultChauffeurBookings;
		//$this->jsonarray['yachtRSVP']      = $resultYachtBookings;
		$this->jsonarray['eventRSVP']      = $resultEvents;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"cancelBooking","taskData":{"bookingID":"0","bookingType":"protection/venue"}}
	 *
	 */
	function cancelBooking()
	{
		//echo "<pre>";print_r($_SERVER);echo "</pre>";exit;
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingID   = IJReq::getTaskData('bookingID', 0, 'int');
		$bookingType = IJReq::getTaskData('bookingType', '', 'string');
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		//require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

		$bookingTypeValue          = ucfirst($bookingType);
		$bookingElementIDField     = strtolower($bookingType.'_id');
		$bookingUserStatusField    = 'user_status';
		$bookingElementStatusField = strtolower($bookingType.'_status');

		if(ucfirst($bookingType) == "Venue")
		{
			$bookingTypeIDField  = strtolower($bookingType.'_table_booking_id');
			$subElement          = 'Table';
			$subElementID        = 'table_id';
			$subElementNameField = 'table_name';
			$notificationType    = strtolower($bookingType).'.table.cancel';
			$lowerBookingType    = strtolower($bookingType).'_table';
		}
		else
		{
			$bookingTypeIDField  = strtolower($bookingType.'_booking_id');
			$subElement          = $bookingType.'Service';
			$subElementID        = 'service_id';
			$subElementNameField = 'service_name';
			$notificationType    = strtolower($bookingType).'.service.cancel';
			$lowerBookingType    = strtolower($bookingType);
		}

		$elementName       = strtolower($bookingType).'_name';
		$tblElementBooking = JTable::getInstance($bookingTypeValue.'Booking', 'BeseatedTable');
		$tblElement        = JTable::getInstance($bookingTypeValue, 'BeseatedTable');
		$tblElementBooking->load($bookingID);

		if(!$tblElementBooking->$bookingTypeIDField)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblService = JTable::getInstance($subElement, 'BeseatedTable');
		$tblService->load($tblElementBooking->$subElementID);
		$tblElement->load($tblElementBooking->$bookingElementIDField);

		$tblElementBooking->$bookingElementStatusField = $this->helper->getStatusID('canceled');
		$tblElementBooking->$bookingUserStatusField = $this->helper->getStatusID('canceled');

		if($tblElementBooking->response_date_time == '0000-00-00 00:00:00')
		{
			$tblElementBooking->response_date_time = date('Y-m-d H:i:s');
		}

		/*echo "<pre>";
		print_r($tblElementBooking);
		echo "</pre>";
		exit;*/
		/*if($tblElementBooking->is_splitted && strtolower($bookingType) == 'protection')
		{
			$query = $this->db->getQuery(true);

			$query->select('*')
				->from($this->db->quoteName('#__beseated_protection_booking_split'))
				->where($this->db->quoteName('protection_booking_id') . ' = ' . $this->db->quote($tblElementBooking->protection_booking_id));

			$this->db->setQuery($query);

			$bookingSplitted = $this->db->loadObjectList();
			if($bookingSplitted)
			{
				foreach ($bookingSplitted as $key => $split)
				{
					$tblProtectionBookingSplit = JTable::getInstance('ProtectionBookingSplit', 'BeseatedTable');
					$tblProtectionBookingSplit->load($split->protection_booking_split_id);
					$tblProtectionBookingSplit->split_payment_status = $this->helper->getStatusID('canceled');
					$tblProtectionBookingSplit->store();
				}
			}
		}
		else if($tblElementBooking->is_splitted && strtolower($bookingType) == 'venue')
		{
			$query = $this->db->getQuery(true);
			$query->update($this->db->quoteName('#__beseated_venue_table_booking_split'))
				->set($this->db->quoteName('split_payment_status') . ' = ' . $this->db->quote($this->helper->getStatusID('canceled')))
				->where($this->db->quoteName('venue_table_booking_id') . ' = ' . $this->db->quote($tblElementBooking->venue_table_booking_id));
			$this->db->setQuery($query);
			$this->db->execute();
		}*/

		if(!$tblElementBooking->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}
		else
		{
			    // refund code
			    $difference =  strtotime($tblElementBooking->booking_date.' '.$tblElementBooking->booking_time) - time();

				if($tblElementBooking->is_splitted)
				{
					$db    = JFactory::getDbo();
					// Initialiase variables.
					$query = $db->getQuery(true);

					// Create the base update statement
					$query->select('*')
				        ->from($db->quoteName('#__beseated_'.$lowerBookingType.'_booking_split'))
						->where($db->quoteName(''.$lowerBookingType.'_booking_id') . ' = ' . $db->quote($bookingID))
						->where($db->quoteName($bookingElementIDField) . ' = ' . $db->quote($tblElementBooking->$bookingElementIDField))
						->where($db->quoteName('paid_by_owner') . ' = ' . $db->quote('0'))
						->where($db->quoteName('pay_by_cash_status') . ' = ' . $db->quote('0'))
						->where($db->quoteName('is_owner') . ' = ' . $db->quote('0'))
						->where($db->quoteName('split_payment_status') . ' = ' . $db->quote('7'))
						->where($db->quoteName($subElementID) . ' = ' . $db->quote($tblElementBooking->$subElementID));

					// Set the query and execute the update.
					$db->setQuery($query);

					$splitedPaidUser = $db->loadObjectList();

					foreach ($splitedPaidUser as $key => $paidUser)
					{
						$splitBookingIDField = $lowerBookingType.'_booking_split_id';
						// Initialiase variables.
						$db    = JFactory::getDbo();

						// $db    = $this->getDbo();
						$query = $db->getQuery(true);

						// Create the base select statement.
						$query->select('payment_id,booking_id,booking_type,user_id,amount,txn_id')
							->from($db->quoteName('#__beseated_payment_status'))
							->where($db->quoteName('booking_id') . ' = ' . $db->quote($paidUser->$splitBookingIDField))
							->where($db->quoteName('user_id') . ' = ' . $db->quote($paidUser->user_id))
							->where($db->quoteName('paid_status') . ' = ' . $db->quote('1'))
							->where($db->quoteName('payment_status') . ' = ' . $db->quote('Success'))
							->where($db->quoteName('amount') . ' = ' . $db->quote($paidUser->splitted_amount))
							->where($db->quoteName('booking_type') . ' = ' . $db->quote(strtolower($bookingTypeValue).'.split'));

						// Set the query and load the result.
						$db->setQuery($query);
						$paymentDetail = $db->loadObject();

						if($paymentDetail && (($difference/3600) >= $tblElement->refund_policy) && $difference >= 0)
						{
							$tblOrderRefund = JTable::getInstance('OrderRefund', 'BeseatedTable');
					        $tblOrderRefund->load(0);

							$tblOrderRefund->payment_id    = $paymentDetail->payment_id;
							$tblOrderRefund->booking_type  = $paymentDetail->booking_type;
							$tblOrderRefund->user_id       = $paymentDetail->user_id;
							$tblOrderRefund->amount        = $paymentDetail->amount;
							$tblOrderRefund->booking_id    = $paymentDetail->booking_id;
							$tblOrderRefund->txn_id        = $paymentDetail->txn_id;
							$tblOrderRefund->refund_status = 1;
							$tblOrderRefund->is_failed     = 1;
							$tblOrderRefund->created_date  = date('Y-m-d H:i:s');
							$tblOrderRefund->store();

							$this->ccAvenueRefund($tblOrderRefund);
						}

						if(!empty($paymentDetail))
						{
							// Initialiase variables.
							$db    = JFactory::getDbo();

							// $db    = $this->getDbo();
							$query = $db->getQuery(true);

							// Create the base select statement.
							$query->select('*')
								->from($db->quoteName('#__beseated_loyalty_point'))
								->where($db->quoteName('user_id') . ' = ' . $db->quote($paymentDetail->user_id))
								->where($db->quoteName('point_app') . ' = ' . $db->quote('purchase.splited.'.strtolower($bookingTypeValue)))
								->where($db->quoteName('cid') . ' = ' . $db->quote($paymentDetail->payment_id))
								->where($db->quoteName('money_used') . ' = ' . $db->quote($paymentDetail->amount));

							// Set the query and load the result.
							$db->setQuery($query);
							$loyaltyResult = $db->loadObject();

							$tblLoyaltyPoint = JTable::getInstance('LoyaltyPoint', 'BeseatedTable');
					        $tblLoyaltyPoint->load(0);

							$tblLoyaltyPoint->user_id    = $loyaltyResult->user_id;
							$tblLoyaltyPoint->money_used = $loyaltyResult->money_used;
							$tblLoyaltyPoint->money_usd  = $loyaltyResult->money_usd;
							$tblLoyaltyPoint->earn_point = '-'.$loyaltyResult->earn_point;
							$tblLoyaltyPoint->point_app  = $loyaltyResult->point_app;
							$tblLoyaltyPoint->title      = $loyaltyResult->title;
							$tblLoyaltyPoint->cid        = $loyaltyResult->cid;
							$tblLoyaltyPoint->is_valid   = $loyaltyResult->is_valid;
							$tblLoyaltyPoint->time_stamp = time();
							$tblLoyaltyPoint->created    = date('Y-m-d H:i:s');
							$tblLoyaltyPoint->store();
						}
					}

				}

				//echo "<pre>";print_r($tblElementBooking);echo "</pre>";exit;

				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('payment_id,booking_id,booking_type,user_id,amount,txn_id')
					->from($db->quoteName('#__beseated_payment_status'))
					->where($db->quoteName('booking_id') . ' = ' . $db->quote($bookingID))
					->where($db->quoteName('paid_status') . ' = ' . $db->quote('1'))
					->where($db->quoteName('payment_status') . ' = ' . $db->quote('Success'))
					->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

					if(strtolower($bookingTypeValue) == 'venue' && $tblElementBooking->pay_deposite && $tblElementBooking->is_paid_deposite== '1')
					{
						$query->where($db->quoteName('booking_type') . ' = ' . $db->quote('venue.confirm'));
					}
					else
					{
						$query->where($db->quoteName('booking_type') . ' = ' . $db->quote(strtolower($bookingTypeValue)));
					}

				// Set the query and load the result.
				$db->setQuery($query);
				$ownerPaymentDetail = $db->loadObject();

				if($ownerPaymentDetail && (($difference/3600) >= $tblElement->refund_policy) && $difference >= 0)
				{
					$tblOrderRefund = JTable::getInstance('OrderRefund', 'BeseatedTable');
			        $tblOrderRefund->load(0);

					$tblOrderRefund->payment_id   = $ownerPaymentDetail->payment_id;
					$tblOrderRefund->booking_type = $ownerPaymentDetail->booking_type;
					$tblOrderRefund->user_id      = $ownerPaymentDetail->user_id;
					$tblOrderRefund->amount       = $ownerPaymentDetail->amount;
					$tblOrderRefund->booking_id   = $ownerPaymentDetail->booking_id;
					$tblOrderRefund->txn_id       = $ownerPaymentDetail->txn_id;
					$tblOrderRefund->refund_status = 1;
				    $tblOrderRefund->is_failed     = 1;
					$tblOrderRefund->created_date = date('Y-m-d H:i:s');
					$tblOrderRefund->store();

					$this->ccAvenueRefund($tblOrderRefund);
				}

				if(!empty($ownerPaymentDetail))
				{
					// Initialiase variables.
					$db    = JFactory::getDbo();

					// $db    = $this->getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('*')
						->from($db->quoteName('#__beseated_loyalty_point'))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($ownerPaymentDetail->user_id))
						->where($db->quoteName('point_app') . ' = ' . $db->quote('purchase.'.strtolower($bookingTypeValue)))
						->where($db->quoteName('cid') . ' = ' . $db->quote($ownerPaymentDetail->payment_id))
						->where($db->quoteName('money_used') . ' = ' . $db->quote($ownerPaymentDetail->amount));

					// Set the query and load the result.
					$db->setQuery($query);
					$loyaltyResult = $db->loadObject();

					$tblLoyaltyPoint = JTable::getInstance('LoyaltyPoint', 'BeseatedTable');
			        $tblLoyaltyPoint->load(0);

					$tblLoyaltyPoint->user_id    = $loyaltyResult->user_id;
					$tblLoyaltyPoint->money_used = $loyaltyResult->money_used;
					$tblLoyaltyPoint->money_usd  = $loyaltyResult->money_usd;
					$tblLoyaltyPoint->earn_point = '-'.$loyaltyResult->earn_point;
					$tblLoyaltyPoint->point_app  = $loyaltyResult->point_app;
					$tblLoyaltyPoint->title      = $loyaltyResult->title;
					$tblLoyaltyPoint->cid        = $loyaltyResult->cid;
					$tblLoyaltyPoint->is_valid   = $loyaltyResult->is_valid;
					$tblLoyaltyPoint->time_stamp = time();
					$tblLoyaltyPoint->created    = date('Y-m-d H:i:s');
					$tblLoyaltyPoint->store();
				}


			// Initialiase variables.
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base delete statement.
			$query->delete()
				->from($db->quoteName('#__beseated_invitation'))
				->where($db->quoteName('element_booking_id') . ' = ' . $db->quote((int) $bookingID))
				->where($db->quoteName('element_id') . ' = ' . $db->quote((int) $tblElementBooking->$bookingElementIDField))
				->where($db->quoteName('element_type') . ' = ' . $db->quote(strtolower($bookingType)));

			// Set the query and execute the delete.
			$db->setQuery($query);

			$db->execute();

			$query = $db->getQuery(true);

			// Create the base update statement
			$query->delete()
		        ->from($db->quoteName('#__beseated_'.$lowerBookingType.'_booking_split'))
				->where($db->quoteName(''.$lowerBookingType.'_booking_id') . ' = ' . $db->quote($bookingID))
				->where($db->quoteName($bookingElementIDField) . ' = ' . $db->quote($tblElementBooking->$bookingElementIDField))
				->where($db->quoteName($subElementID) . ' = ' . $db->quote($tblElementBooking->$subElementID));

			// Set the query and execute the update.
			$db->setQuery($query);

		    $db->execute();  // by jamal

		    if(strtolower($bookingType) == 'venue')
		    {
		    	// Initialiase variables.
				$db    = JFactory::getDbo();

				// $db    = $this->getDbo();
				$query = $db->getQuery(true);

				// Create the base delete statement.
				$query->delete()
					->from($db->quoteName('#__beseated_venue_friends_attending'))
					->where($db->quoteName('venue_table_booking_id') . ' = ' . $db->quote((int) $bookingID))
					->where($db->quoteName('venue_id') . ' = ' . $db->quote((int) $tblElementBooking->$bookingElementIDField))
					->where($db->quoteName('table_id') . ' = ' . $db->quote($tblElementBooking->$subElementID))
					->where($db->quoteName('booking_user_id') . ' = ' . $db->quote($this->IJUserID));

				// Set the query and execute the delete.
				$db->setQuery($query);

				$db->execute(); // by jamal
		    }

		}

		if(strtolower($bookingType) == 'protection')
		{
			$paramsID = "protectionBookingID";
		}
		else if(strtolower($bookingType) == 'venue')
		{
			$paramsID = "venueBookingID";
		}
		else
		{
			$paramsID = "bookingID";
		}

		$guestUserDetail = $this->helper->guestUserDetail($this->IJUserID);

		if(isset($tblElement->is_day_club) && $tblElement->is_day_club == 0)
		{
			$title = JText::sprintf(
				'COM_IJOOMERADV_BESEATED_CANCEL_VENUE_NIGHT_BOOKING_BY_USER_FOR_'.strtoupper($bookingType),
				$guestUserDetail->full_name,
				$tblService->$subElementNameField,
				$this->helper->convertDateFormat($tblElementBooking->booking_date)
				);
		}
		else
		{
			$title = JText::sprintf(
				'COM_IJOOMERADV_BESEATED_CANCEL_BOOKING_BY_USER_FOR_'.strtoupper($bookingType),
				$guestUserDetail->full_name,
				$tblService->$subElementNameField,
				$this->helper->convertDateFormat($tblElementBooking->booking_date),
				$this->helper->convertToHM($tblElementBooking->booking_time)
				);
		}

		$actor            = $this->IJUserID;
		$target           = $tblElement->user_id;
		$elementID        = $tblElementBooking->$bookingElementIDField;
		$elementType      = $bookingTypeValue;
		$cid              = $tblElementBooking->$bookingTypeIDField;
		$extraParams      = array();
		$extraParams[$paramsID] = $tblElementBooking->$bookingTypeIDField;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$cid))
		{
			$element_name = strtolower($bookingType).'_name';
			$this->emailHelper->cancelBookingByUserMail($guestUserDetail->full_name,$tblElement->$element_name,$tblService->$subElementNameField,$this->helper->convertDateFormat($tblElementBooking->booking_date),$this->helper->convertToHM($tblElementBooking->booking_time),$tblElement->is_day_club,$guestUserDetail->email);

			//echo "<pre/>";print_r($tblElement);exit;

			if(strtolower($bookingType) == 'venue')
			{
				if($tblElement->is_day_club)
				{
					$emailContent = ''.$guestUserDetail->full_name.' has cancelled their booking at '.$tblElement->venue_name.' for '.$tblElementBooking->total_guest.' people on '.$this->helper->convertDateFormat($tblElementBooking->booking_date).' at '.$this->helper->convertToHM($tblElementBooking->booking_time).'.';
				}
				else
				{
					$emailContent = ''.$guestUserDetail->full_name.' has cancelled their booking at '.$tblElement->venue_name.' for '.$tblElementBooking->total_guest.' people on '.$this->helper->convertDateFormat($tblElementBooking->booking_date).'.';
				}
			}
			else
			{
				$emailContent = ''.$guestUserDetail->full_name.' has cancelled their booking at '.$tblElement->$elementName.' on '.$this->helper->convertDateFormat($tblElementBooking->booking_date).' at '.$this->helper->convertToHM($tblElementBooking->booking_time).'.';

			}

			//echo "<pre/>";print_r($emailContent);exit;

			$companyDetail = JFactory::getUser($tblElement->user_id);

			$this->emailHelper->cancelBookingToManagerMail($tblElement->$element_name,$emailContent,$companyDetail->email);
		}


		$this->jsonarray['pushNotificationData']['id']         = $tblElementBooking->$bookingTypeIDField;
		$this->jsonarray['pushNotificationData']['elementType'] = ucfirst($elementType);
		$this->jsonarray['pushNotificationData']['to']         = $target;
		$this->jsonarray['pushNotificationData']['message']    = $title;
		$this->jsonarray['pushNotificationData']['type']       = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype'] = '';

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"deleteBooking","taskData":{"bookingID":"0","bookingType":"Protection"}}
	 *
	 */
	function deleteBooking()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingID   = IJReq::getTaskData('bookingID', 0, 'int');
		$bookingType = IJReq::getTaskData('bookingType', '', 'string');
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$bookingTypeValue          = ucfirst($bookingType);

		if($bookingTypeValue == 'Event')
		{
			$tblElementBooking = JTable::getInstance('TicketInvitation', 'BeseatedTable');
			$tblElementBooking->load($bookingID);

			if(!$tblElementBooking->invite_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}
		else
		{
			if(ucfirst($bookingType) == "Venue")
			{
				$bookingTypeIDField  = strtolower($bookingType.'_table_booking_id');
			}
			elseif(ucfirst($bookingType) == "Chauffeur")
			{
				$bookingTypeIDField  = strtolower($bookingType.'_booking_id');
			}
			elseif(ucfirst($bookingType) == "Yacht")
			{
				$bookingTypeIDField  = strtolower($bookingType.'_booking_id');
			}
			else
			{
				$bookingTypeIDField  = strtolower($bookingType.'_booking_id');
			}

			$tblElementBooking = JTable::getInstance($bookingTypeValue.'Booking', 'BeseatedTable');
			$tblElementBooking->load($bookingID);

			if(!$tblElementBooking->$bookingTypeIDField)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}

		$tblElementBooking->deleted_by_user = 1;

		if(!$tblElementBooking->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"getBookingDetail","taskData":{"bookingID":"7","bookingType":"protection"}}
	 *
	 */
	function getBookingDetail()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingID   = IJReq::getTaskData('bookingID', 0, 'int');
		$bookingType = IJReq::getTaskData('bookingType', '', 'string');

		if(empty($bookingType))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		if(ucfirst($bookingType) == 'Protection')
		{
			// Create the base select statement.
			$query->select('pb.protection_booking_id,pb.protection_id,pb.service_id,pb.user_id,pb.booking_date,pb.booking_time,pb.total_hours,pb.total_guard,pb.price_per_hours,pb.total_price,pb.user_status,pb.is_splitted,pb.each_person_pay,pb.splitted_count,pb.remaining_amount,pb.response_date_time,pb.total_split_count')
				->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
				->where($db->quoteName('pb.protection_booking_id') . ' = ' . $db->quote($bookingID))
				->order($db->quoteName('pb.booking_date') . ' ASC');

			$query->select('p.protection_name,p.location,p.city,p.currency_code,p.deposit_per,p.refund_policy')
				->join('LEFT','#__beseated_protection AS p ON p.protection_id=pb.protection_id');

			$query->select('ps.service_name,ps.thumb_image,ps.image')
				->join('LEFT','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');

			// Set the query and load the result.
			$db->setQuery($query);
			$resBookingDetail = $db->loadObject();
			if(!$resBookingDetail){
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$bookingDetail = array();
			$bookingDetail['elementType']      = "Protection";
			$bookingDetail['elementBookingID'] = $resBookingDetail->protection_booking_id;
			$bookingDetail['bookingDate']         = $this->helper->convertDateFormat($resBookingDetail->booking_date);
			$bookingDetail['bookingTime']         = $this->helper->convertToHM($resBookingDetail->booking_time);
			$bookingDetail['totalPrice']          = $this->helper->currencyFormat('',$resBookingDetail->total_price,0);
			$bookingDetail['priceToPay']          = $this->helper->currencyFormat('',$resBookingDetail->total_price,0);
			$bookingDetail['currencyCode']        = $resBookingDetail->currency_code;
			$bookingDetail['elementsName']      = $resBookingDetail->protection_name;
			$bookingDetail['depositPer']          = $resBookingDetail->deposit_per;
			$bookingDetail['serviceName']         = $resBookingDetail->service_name;
			$bookingDetail['statusCode']          = $resBookingDetail->user_status;
			$bookingDetail['totalHours']          = $resBookingDetail->total_hours;
			$bookingDetail['pricePerHours']       = $this->helper->currencyFormat('',$resBookingDetail->price_per_hours);
			$bookingDetail['thumbImage']          = ($resBookingDetail->thumb_image)?JUri::base().'images/beseated/'.$resBookingDetail->thumb_image:'';
			$bookingDetail['image']               = ($resBookingDetail->image)?JUri::base().'images/beseated/'.$resBookingDetail->image:'';
			//$bookingDetail['bookedType']          = 'booking';
			$bookingDetail['isSplitted']          = $resBookingDetail->is_splitted;
			$bookingDetail['eachPersonPay']       = $this->helper->currencyFormat('',$resBookingDetail->each_person_pay);
			$bookingDetail['splittedCount']       = $resBookingDetail->splitted_count;
			$bookingDetail['remainingAmount']     = $this->helper->currencyFormat('',$resBookingDetail->remaining_amount);
			$bookingDetail['totalSplitCount']     = $resBookingDetail->total_split_count;
			$bookingDetail['totalGuard']          = $resBookingDetail->total_guard;
			$bookingDetail['hasDeposit']          = '';
			$bookingDetail['depositRate']         = $resBookingDetail->deposit_per;
			$bookingDetail['depositAmount']       = (string)($resBookingDetail->total_price*$resBookingDetail->deposit_per/100);
			$bookingDetail['refundPolicyHours']   = $resBookingDetail->refund_policy;

			if($resBookingDetail->total_split_count)
			{
				$bookingDetail['maxSplitCount']       = $resBookingDetail->total_split_count - 1 - (($resBookingDetail->splitted_count) ?  $resBookingDetail->splitted_count - 1 : 0);
			}
			else
			{
				$bookingDetail['maxSplitCount']       = 0;
			}


			if($resBookingDetail->user_id == $this->IJUserID)
			{
				$bookingDetail['bookedType'] = 'booking';
			}
			else
			{
				$resProtectionShareInvitations = $this->getBookedProtectionShareInvitations($resBookingDetail->protection_booking_id);

				foreach ($resProtectionShareInvitations as $key => $shareInvitations)
				{
					if($shareInvitations->bookedType == 'share')
					{
						$bookingDetail['bookedType'] = 'share';
					}
					else
					{
						$bookingDetail['bookedType'] = 'invitation';
					}
				}
			}

			$splitedUserCount = $this->getSplitedUserCount($elementType = 'Protection' , $resBookingDetail->protection_booking_id);

			if(!$splitedUserCount)
			{
				$bookingDetail['remainingSplitUser']  = 50 - 1;
			}
			else
			{
				if($splitedUserCount > 50)
				{
					$bookingDetail['remainingSplitUser']  = 0;
				}
				else
				{
					$bookingDetail['remainingSplitUser']  = 50 - $splitedUserCount;
				}

			}

			if($resBookingDetail->response_date_time != '0000-00-00 00:00:00'){
				$bookingDetail['remainingTime'] = strtotime($resBookingDetail->response_date_time);
			}else{
				$bookingDetail['remainingTime'] = '';
			}

			$querySplit = $db->getQuery(true);
			$querySplit->select('split.protection_booking_split_id,split.user_id,split.email,split.splitted_amount,split.split_payment_status')
				->from($db->quoteName('#__beseated_protection_booking_split','split'))
				->where($db->quoteName('split.protection_booking_id') . ' = ' . $db->quote($resBookingDetail->protection_booking_id));
			$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id')
				->order($db->quoteName('bu.full_name') . ' ASC');

			$db->setQuery($querySplit);
			$resSplits = $db->loadObjectList();

			if($resSplits)
			{
				$tempSplit = array();
				foreach ($resSplits as $key => $split)
				{
					$tempSingleSplit = array();

					if($split->user_id != $resBookingDetail->user_id)
					{
						$tempSingleSplit['invitationID']   = $split->protection_booking_split_id;
						$tempSingleSplit['fullName']       = ($split->full_name)?$split->full_name:$split->email;
						$tempSingleSplit['splittedAmount'] = $this->helper->currencyFormat('',$split->splitted_amount);
						$tempSingleSplit['statusCode']     = $split->split_payment_status;
						$tempSingleSplit['avatar']         = ($split->avatar)?$this->helper->getUserAvatar($split->avatar):'';
						$tempSingleSplit['thumbAvatar']    = ($split->thumb_avatar)?$this->helper->getUserAvatar($split->thumb_avatar):'';
					}

					if($split->user_id == $resBookingDetail->user_id)
					{
						$bookingDetail['priceToPay'] = $this->helper->currencyFormat('',$split->splitted_amount);
						if($split->split_payment_status == 7)
						{
							$bookingDetail['isBookingUserPaid'] =  1;
						}
						else
						{
							$bookingDetail['isBookingUserPaid'] =  0;
							$bookingDetail['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$split->protection_booking_split_id.'&booking_type=protection.split';
						}
					}
					else
					{
						$tempSplit[] = $tempSingleSplit;
					}
				}
				$bookingDetail['splits'] = $tempSplit;
			}
			else{
				$bookingDetail['splits'] = array();
			}

			//if($resBookingDetail->user_status == 4 && $resBookingDetail->is_splitted == 0)

			if($resBookingDetail->user_status == 4)
			{
				$bookingDetail['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$resBookingDetail->protection_booking_id.'&booking_type=protection';
			}
			else if(!isset($bookingDetail['paymentURL']))
			{
				$bookingDetail['paymentURL'] = '';
			}

		}
		else if (ucfirst($bookingType) == 'Venue')
		{
			// Create the base select statement.
			$query->select('vb.*')
				->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vb')
				->where($db->quoteName('vb.venue_table_booking_id') . ' = ' . $db->quote($bookingID))
				->order($db->quoteName('vb.booking_date') . ' ASC');

			$query->select('v.venue_name,v.location,v.city,v.deposit_per,v.active_payments,v.refund_policy')
				->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

			$query->select('vt.table_name,vt.table_type,vt.premium_table_id,vt.min_price,vt.capacity,vt.thumb_image,vt.image')
				->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

			// Set the query and load the result.
			$db->setQuery($query);
			$resBookingDetail = $db->loadObject();
			if(!$resBookingDetail){
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$bookingDetail = array();

			$bookingDetail['venueBookingID']  = $resBookingDetail->venue_table_booking_id;
			$bookingDetail['bookingDate']     = $this->helper->convertDateFormat($resBookingDetail->booking_date);
			$bookingDetail['bookingTime']     = $this->helper->convertToHM($resBookingDetail->booking_time);
			$bookingDetail['totalPrice']      = $this->helper->currencyFormat('',$resBookingDetail->total_price,0);
			if($resBookingDetail->is_bill_posted && $resBookingDetail->bill_post_amount){
				$bookingDetail['priceToPay']      = $this->helper->currencyFormat('',$resBookingDetail->bill_post_amount,0);
			}else{
				$bookingDetail['priceToPay']      = $this->helper->currencyFormat('',$resBookingDetail->total_price,0);
			}

			$bookingDetail['currencyCode']    = $resBookingDetail->booking_currency_code;
			$bookingDetail['venueName']       = $resBookingDetail->venue_name;
			$bookingDetail['location']        = $resBookingDetail->location;
			$bookingDetail['city']            = $resBookingDetail->city;
			$bookingDetail['depositPer']      = $resBookingDetail->deposit_per;
			$bookingDetail['tableName']       = $resBookingDetail->table_name;
			$bookingDetail['totalGuest']      = $resBookingDetail->total_guest;
			$bookingDetail['maleGuest']       = $resBookingDetail->male_guest;
			$bookingDetail['femaleGuest']     = $resBookingDetail->female_guest;
			$bookingDetail['statusCode']      = $resBookingDetail->user_status;
			$bookingDetail['totalHours']      = $resBookingDetail->total_hours;
			$bookingDetail['minSpend']        = $this->helper->currencyFormat('',$resBookingDetail->min_price);
			$bookingDetail['capacity']        = $resBookingDetail->capacity;
			$bookingDetail['thumbImage']      = ($resBookingDetail->thumb_image)?JUri::base().'images/beseated/'.$resBookingDetail->thumb_image:'';
			$bookingDetail['image']           = ($resBookingDetail->image)?JUri::base().'images/beseated/'.$resBookingDetail->image:'';
			$bookingDetail['hasDeposit']      = ($resBookingDetail->deposit_per == '0') ? '0' : '1';
			$bookingDetail['depositRate']     = $resBookingDetail->deposit_per ;
			$bookingDetail['depositAmount']   = '';
			$bookingDetail['activePayments']  = $resBookingDetail->active_payments ;
			$bookingDetail['refundPolicyHours']   = $resBookingDetail->refund_policy;

			if($resBookingDetail->user_id == $this->IJUserID)
			{
				$bookingDetail['bookedType'] = 'booking';
			}
			else
			{
				$resVenueShareInvitations = $this->getBookedVenueShareInvitations($resBookingDetail->venue_table_booking_id);

				foreach ($resVenueShareInvitations as $key => $shareInvitations)
				{
					if($shareInvitations->bookedType == 'share')
					{
						$bookingDetail['bookedType'] = 'share';
					}
					else
					{
						$bookingDetail['bookedType'] = 'invitation';
					}
				}
			}

			$bookingDetail['isSplitted']        = $resBookingDetail->is_splitted;
			$bookingDetail['eachPersonPay']     = $this->helper->currencyFormat('',$resBookingDetail->each_person_pay);
			$bookingDetail['splittedCount']     = $resBookingDetail->splitted_count;
			$bookingDetail['remainingAmount']   = $this->helper->currencyFormat('',$resBookingDetail->remaining_amount);
			$bookingDetail['isBookingUserPaid'] =  0;
			$bookingDetail['totalSplitCount']     = $resBookingDetail->total_split_count;
			$bookingDetail['totalGuard']          = '';
			//$bookingDetail['maxSplitCount']     = '';

			if($resBookingDetail->total_split_count)
			{
				$bookingDetail['maxSplitCount']       = $resBookingDetail->total_split_count - 1 - (($resBookingDetail->splitted_count) ?  $resBookingDetail->splitted_count - 1 : 0);
			}
			else
			{
				$bookingDetail['maxSplitCount']       = $resBookingDetail->total_guest;
			}

			if($resBookingDetail->is_bill_posted)
			{
				$bookingDetail['postPrice']  =  $this->helper->currencyFormat('',$resBookingDetail->bill_post_amount);
			}
			else if ($resBookingDetail->has_bottle)
			{
				$bookingDetail['postPrice']  =  $this->helper->currencyFormat('',$resBookingDetail->total_bottle_price);
			}
			else
			{
				$bookingDetail['postPrice']  = $this->helper->currencyFormat('',$resBookingDetail->min_price);
			}

			$splitedUserCount = $this->getSplitedUserCount($elementType = 'Venue' , $resBookingDetail->venue_table_booking_id);

			if(!$splitedUserCount)
			{
				$bookingDetail['remainingSplitUser']  = $resBookingDetail->total_guest - 1;
			}
			else
			{
				if($splitedUserCount > $resBookingDetail->total_guest)
				{
					$tmpBooking['remainingSplitUser']  = 0;
				}
				else
				{
					$bookingDetail['remainingSplitUser']  = $resBookingDetail->total_guest - $splitedUserCount;
				}

			}

			if($resBookingDetail->response_date_time != '0000-00-00 00:00:00'){
				$bookingDetail['remainingTime'] = strtotime($resBookingDetail->response_date_time);
			}else{
				$bookingDetail['remainingTime'] = '';
			}

			$querySplit = $db->getQuery(true);
			$querySplit->select('split.venue_table_booking_split_id,split.user_id,split.email,split.splitted_amount,split.split_payment_status')
				->from($db->quoteName('#__beseated_venue_table_booking_split','split'))
				->where($db->quoteName('split.venue_table_booking_id') . ' = ' . $db->quote($resBookingDetail->venue_table_booking_id));
			$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id')
				->order($db->quoteName('bu.full_name') . ' ASC');

			$db->setQuery($querySplit);
			$resSplits = $db->loadObjectList();

			if($resSplits)
			{
				$tempSplit = array();
				foreach ($resSplits as $key => $split)
				{
					$tempSingleSplit = array();
					$tempSingleSplit['invitationID']   = $split->venue_table_booking_split_id;
					$tempSingleSplit['fullName']       = ($split->full_name)?$split->full_name:$split->email;
					$tempSingleSplit['splittedAmount'] = $this->helper->currencyFormat('',$split->splitted_amount);
					$tempSingleSplit['statusCode']     = $split->split_payment_status;
					$tempSingleSplit['avatar']         = ($split->avatar)?$this->helper->getUserAvatar($split->avatar):'';
					$tempSingleSplit['thumbAvatar']    = ($split->thumb_avatar)?$this->helper->getUserAvatar($split->thumb_avatar):'';

					if($split->user_id == $resBookingDetail->user_id)
					{
						$bookingDetail['priceToPay'] = $this->helper->currencyFormat('',$split->splitted_amount);
						if($split->split_payment_status == 7)
						{
							$bookingDetail['isBookingUserPaid'] =  1;
						}
						else
						{
							//$bookingDetail['isBookingUserPaid'] =  1;
							$bookingDetail['isBookingUserPaid'] =  0;
							$bookingDetail['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$split->venue_table_booking_split_id.'&booking_type=venue.split';
						}

						//$bookingDetail['isBookingUserPaid'] =  0;
					}
					else
					{
						$tempSplit[] = $tempSingleSplit;
					}
				}
				$bookingDetail['splits'] = $tempSplit;
			}
			else{
				$bookingDetail['splits'] = array();
			}

			//if($resBookingDetail->user_status == 13 && $resBookingDetail->is_splitted == 0)

			if($resBookingDetail->user_status == 13 )
			{
				$bookingDetail['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$resBookingDetail->venue_table_booking_id.'&booking_type=venue';
			}
			else if(!isset($bookingDetail['paymentURL']))
			{
				$bookingDetail['paymentURL'] = '';
			}
		}
		else if(ucfirst($bookingType) == 'Yacht')
		{
			// Create the base select statement.
			$query->select('yb.yacht_booking_id,yb.yacht_id,yb.booking_date,yb.booking_time,yb.user_id,yb.total_hours,yb.price_per_hours,yb.capacity,yb.total_price,yb.user_status,yb.booking_currency_code,yb.is_splitted,yb.has_invitation,yb.each_person_pay,yb.splitted_count,yb.remaining_amount,yb.response_date_time,yb.total_split_count')
				->from($db->quoteName('#__beseated_yacht_booking') . ' AS yb')
				->where($db->quoteName('yb.yacht_booking_id') . ' = ' . $db->quote($bookingID));

			$query->select('y.yacht_name,y.location,y.city,y.deposit_per,y.refund_policy')
				->join('LEFT','#__beseated_yacht AS y ON y.yacht_id=yb.yacht_id');

			$query->select('ys.service_name,ys.service_type,ys.dock,ys.thumb_image,ys.image')
				->join('LEFT','#__beseated_yacht_services AS ys ON ys.service_id=yb.service_id');

			// Set the query and load the result.
			$db->setQuery($query);
			$resBookingDetail = $db->loadObject();
			if(!$resBookingDetail){
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$bookingDetail = array();
			$bookingDetail['elementType']      = "Yacht";
			$bookingDetail['elementBookingID'] = $resBookingDetail->yacht_booking_id;
			$bookingDetail['bookingDate']     = $this->helper->convertDateFormat($resBookingDetail->booking_date);
			$bookingDetail['bookingTime']     = $this->helper->convertToHM($resBookingDetail->booking_time);
			$bookingDetail['totalPrice']      = $this->helper->currencyFormat('',$resBookingDetail->total_price,0);
			$bookingDetail['priceToPay']      = $this->helper->currencyFormat('',$resBookingDetail->total_price,0);
			$bookingDetail['currencyCode']    = $resBookingDetail->booking_currency_code;
			$bookingDetail['elementsName']    = $resBookingDetail->yacht_name;
			$bookingDetail['depositPer']      = $resBookingDetail->deposit_per;
			$bookingDetail['serviceName']     = $resBookingDetail->service_name;
			$bookingDetail['statusCode']      = $resBookingDetail->user_status;
			$bookingDetail['totalHours']      = $resBookingDetail->total_hours;
			$bookingDetail['pricePerHours']   = $this->helper->currencyFormat('',$resBookingDetail->price_per_hours);
			$bookingDetail['capacity']        = $resBookingDetail->capacity;
			$bookingDetail['thumbImage']      = ($resBookingDetail->thumb_image)?JUri::base().'images/beseated/'.$resBookingDetail->thumb_image:'';
			$bookingDetail['image']           = ($resBookingDetail->image)?JUri::base().'images/beseated/'.$resBookingDetail->image:'';
			//$bookingDetail['bookedType']      = 'booking';
			$bookingDetail['isSplitted']      = $resBookingDetail->is_splitted;
			$bookingDetail['eachPersonPay']   = $this->helper->currencyFormat('',$resBookingDetail->each_person_pay);
			$bookingDetail['splittedCount']   = $resBookingDetail->splitted_count;
			$bookingDetail['remainingAmount'] = $this->helper->currencyFormat('',$resBookingDetail->remaining_amount);
			$bookingDetail['totalSplitCount'] = $resBookingDetail->total_split_count;
			$bookingDetail['totalGuard']      = '';
			$bookingDetail['hasDeposit']      = '';
			$bookingDetail['depositRate']     = $resBookingDetail->deposit_per;
            $bookingDetail['depositAmount']   = (string)($resBookingDetail->total_price*$resBookingDetail->deposit_per/100);
			$bookingDetail['refundPolicyHours']   = $resBookingDetail->refund_policy;
			//$bookingDetail['maxSplitCount']   = $resBookingDetail->total_split_count - 1 - (($resBookingDetail->splitted_count) ?  $resBookingDetail->splitted_count - 1 : 0);

			if($resBookingDetail->total_split_count)
			{
				$bookingDetail['maxSplitCount']       = $resBookingDetail->total_split_count - 1 - (($resBookingDetail->splitted_count) ?  $resBookingDetail->splitted_count - 1 : 0);
			}
			else
			{
				$bookingDetail['maxSplitCount']       = 0;
			}

			if($resBookingDetail->user_id == $this->IJUserID)
			{
				$bookingDetail['bookedType'] = 'booking';
			}
			else
			{
				$resYachtShareInvitations = $this->getBookedYachtShareInvitations($resBookingDetail->yacht_booking_id);

				foreach ($resYachtShareInvitations as $key => $shareInvitations)
				{
					if($shareInvitations->bookedType == 'share')
					{
						$bookingDetail['bookedType'] = 'share';
					}
					else
					{
						$bookingDetail['bookedType'] = 'invitation';
					}
				}
			}

			$splitedUserCount = $this->getSplitedUserCount($elementType = 'Yacht' , $resBookingDetail->yacht_booking_id);

			if(!$splitedUserCount)
			{
				$bookingDetail['remainingSplitUser']  = $resBookingDetail->capacity - 1;
			}
			else
			{
				if($splitedUserCount > $resBookingDetail->capacity)
				{
					$bookingDetail['remainingSplitUser']  = 0;
				}
				else
				{
					$bookingDetail['remainingSplitUser']  = $resBookingDetail->capacity - $splitedUserCount;
				}

			}

			if($resBookingDetail->response_date_time != '0000-00-00 00:00:00'){
				$bookingDetail['remainingTime'] = strtotime($resBookingDetail->response_date_time);
			}else{
				$bookingDetail['remainingTime'] = '';
			}

			$querySplit = $db->getQuery(true);
			$querySplit->select('split.*')
				->from($db->quoteName('#__beseated_yacht_booking_split','split'))
				->where($db->quoteName('split.yacht_booking_id') . ' = ' . $db->quote($resBookingDetail->yacht_booking_id));
			$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id')
				->order($db->quoteName('bu.full_name') . ' ASC');

			$db->setQuery($querySplit);
			$resSplits = $db->loadObjectList();

			if($resSplits)
			{
				$tempSplit = array();
				foreach ($resSplits as $key => $split)
				{
					$tempSingleSplit = array();
					if($split->user_id != $resBookingDetail->user_id)
					{
						$tempSingleSplit['invitationID']   = $split->yacht_booking_split_id;
						$tempSingleSplit['fullName']       = ($split->full_name)?$split->full_name:$split->email;
						$tempSingleSplit['splittedAmount'] = $this->helper->currencyFormat('',$split->splitted_amount);
						$tempSingleSplit['statusCode']     = $split->split_payment_status;
						$tempSingleSplit['avatar']         = ($split->avatar)?$this->helper->getUserAvatar($split->avatar):'';
						$tempSingleSplit['thumbAvatar']    = ($split->thumb_avatar)?$this->helper->getUserAvatar($split->thumb_avatar):'';
					}

					if($split->user_id == $resBookingDetail->user_id)
					{
						$bookingDetail['priceToPay'] = $this->helper->currencyFormat('',$split->splitted_amount);
						if($split->split_payment_status == 7)
						{
							$bookingDetail['isBookingUserPaid'] =  1;
						}
						else
						{
							//$bookingDetail['isBookingUserPaid'] =  1;
							$bookingDetail['isBookingUserPaid'] =  0;
							$bookingDetail['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$split->yacht_booking_split_id.'&booking_type=yacht.split';
						}
					}
					else
					{
						$tempSplit[] = $tempSingleSplit;
					}
				}
				$bookingDetail['splits'] = $tempSplit;
			}
			else{
				$bookingDetail['splits'] = array();
			}

			//if($resBookingDetail->user_status == 4 && $resBookingDetail->is_splitted == 0)

			if($resBookingDetail->user_status == 4)
			{
				$bookingDetail['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$resBookingDetail->yacht_booking_id.'&booking_type=yacht';
			}
			else if(!isset($bookingDetail['paymentURL']))
			{
				$bookingDetail['paymentURL'] = '';
			}
		}
		else if(ucfirst($bookingType) == 'Chauffeur')
		{
			// Create the base select statement.
			$query->select('cb.chauffeur_booking_id,cb.chauffeur_id,cb.user_id,cb.booking_date,cb.booking_time,cb.pickup_location,cb.dropoff_location,cb.capacity,cb.user_status,cb.total_price,cb.booking_currency_code,cb.is_splitted,cb.has_invitation,cb.each_person_pay,cb.splitted_count,cb.remaining_amount,cb.response_date_time,cb.total_split_count')
				->from($db->quoteName('#__beseated_chauffeur_booking') . ' AS cb')
				->where($db->quoteName('cb.chauffeur_booking_id') . ' = ' . $db->quote($bookingID));

			$query->select('c.chauffeur_name,c.location,c.city,c.deposit_per,c.refund_policy')
				->join('LEFT','#__beseated_chauffeur AS c ON c.chauffeur_id=cb.chauffeur_id');

			$query->select('cs.service_name,cs.service_type,cs.thumb_image,cs.image')
				->join('LEFT','#__beseated_chauffeur_services AS cs ON cs.service_id=cb.service_id');

			// Set the query and load the result.
			$db->setQuery($query);
			$resBookingDetail = $db->loadObject();
			if(!$resBookingDetail){
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$bookingDetail = array();
			$bookingDetail['elementType']      = "Chauffeur";
			$bookingDetail['elementBookingID']  = $resBookingDetail->chauffeur_booking_id;
			$bookingDetail['bookingDate']     = $this->helper->convertDateFormat($resBookingDetail->booking_date);
			$bookingDetail['bookingTime']     = $this->helper->convertToHM($resBookingDetail->booking_time);
			$bookingDetail['totalPrice']      = $this->helper->currencyFormat('',$resBookingDetail->total_price,0);
			$bookingDetail['priceToPay']      = $this->helper->currencyFormat('',$resBookingDetail->total_price,0);
			$bookingDetail['currencyCode']    = $resBookingDetail->booking_currency_code;
			$bookingDetail['elementsName']       = $resBookingDetail->chauffeur_name;
			$bookingDetail['depositPer']      = $resBookingDetail->deposit_per;
			$bookingDetail['serviceName']     = $resBookingDetail->service_name;
			$bookingDetail['statusCode']      = $resBookingDetail->user_status;
			//$bookingDetail['totalHours']      = $resBookingDetail->total_hours;
			//$bookingDetail['pricePerHours']   = $this->helper->currencyFormat('',$resBookingDetail->price_per_hours);
			$bookingDetail['capacity']         = $resBookingDetail->capacity;
			$bookingDetail['thumbImage']       = ($resBookingDetail->thumb_image)?JUri::base().'images/beseated/'.$resBookingDetail->thumb_image:'';
			$bookingDetail['image']            = ($resBookingDetail->image)?JUri::base().'images/beseated/'.$resBookingDetail->image:'';
			$bookingDetail['bookedType']       = 'booking';
			$bookingDetail['isSplitted']       = $resBookingDetail->is_splitted;
			$bookingDetail['eachPersonPay']    = $this->helper->currencyFormat('',$resBookingDetail->each_person_pay);
			$bookingDetail['splittedCount']    = $resBookingDetail->splitted_count;
			$bookingDetail['remainingAmount']  = $this->helper->currencyFormat('',$resBookingDetail->remaining_amount);
			$bookingDetail['dropoff_location'] = $resBookingDetail->dropoff_location;
			$bookingDetail['pickup_location']  = $resBookingDetail->pickup_location;
			$bookingDetail['totalSplitCount']  = $resBookingDetail->total_split_count;
			$bookingDetail['totalGuard']       = '';
			$bookingDetail['hasDeposit']       = '';
			$bookingDetail['depositRate']         = $resBookingDetail->deposit_per;
			$bookingDetail['depositAmount']       = (string)($resBookingDetail->total_price*$resBookingDetail->deposit_per/100);
			$bookingDetail['refundPolicyHours']   = $resBookingDetail->refund_policy;
			//$bookingDetail['maxSplitCount']    = $resBookingDetail->total_split_count - 1 - (($resBookingDetail->splitted_count) ?  $resBookingDetail->splitted_count - 1 : 0);

			if($resBookingDetail->total_split_count)
			{
				$bookingDetail['maxSplitCount']       = $resBookingDetail->total_split_count - 1 - (($resBookingDetail->splitted_count) ?  $resBookingDetail->splitted_count - 1 : 0);
			}
			else
			{
				$bookingDetail['maxSplitCount']       = 0;
			}

			if($resBookingDetail->user_id == $this->IJUserID)
			{
				$bookingDetail['bookedType'] = 'booking';
			}
			else
			{
				$resChauffeurShareInvitations = $this->getBookedChauffeurShareInvitations($resBookingDetail->chauffeur_booking_id);

				foreach ($resChauffeurShareInvitations as $key => $shareInvitations)
				{
					if($shareInvitations->bookedType == 'share')
					{
						$bookingDetail['bookedType'] = 'share';
					}
					else
					{
						$bookingDetail['bookedType'] = 'invitation';
					}
				}
			}


			$splitedUserCount = $this->getSplitedUserCount($elementType = 'Chauffeur' , $resBookingDetail->chauffeur_booking_id);

			if(!$splitedUserCount)
			{
				$bookingDetail['remainingSplitUser']  =  $resBookingDetail->capacity - 1;
			}
			else
			{
				if($splitedUserCount > $resBookingDetail->capacity)
				{
					$bookingDetail['remainingSplitUser']  = 0;
				}
				else
				{
					$bookingDetail['remainingSplitUser']  =  $resBookingDetail->capacity - $splitedUserCount;
				}

			}

			if($resBookingDetail->response_date_time != '0000-00-00 00:00:00'){
				$bookingDetail['remainingTime'] = strtotime($resBookingDetail->response_date_time);
			}else{
				$bookingDetail['remainingTime'] = '';
			}

			$querySplit = $db->getQuery(true);
			$querySplit->select('split.*')
				->from($db->quoteName('#__beseated_chauffeur_booking_split','split'))
				->where($db->quoteName('split.chauffeur_booking_id') . ' = ' . $db->quote($resBookingDetail->chauffeur_booking_id));
			$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id')
				->order($db->quoteName('bu.full_name') . ' ASC');

			$db->setQuery($querySplit);
			$resSplits = $db->loadObjectList();

			if($resSplits)
			{
				$tempSplit = array();
				foreach ($resSplits as $key => $split)
				{
					if($split->user_id != $resBookingDetail->user_id)
					{
						$tempSingleSplit = array();
						$tempSingleSplit['invitationID']   = $split->chauffeur_booking_split_id;
						$tempSingleSplit['fullName']       = ($split->full_name)?$split->full_name:$split->email;
						$tempSingleSplit['splittedAmount'] = $this->helper->currencyFormat('',$split->splitted_amount);
						$tempSingleSplit['statusCode']     = $split->split_payment_status;
						$tempSingleSplit['avatar']         = ($split->avatar)?$this->helper->getUserAvatar($split->avatar):'';
						$tempSingleSplit['thumbAvatar']    = ($split->thumb_avatar)?$this->helper->getUserAvatar($split->thumb_avatar):'';
					}

					if($split->user_id == $resBookingDetail->user_id)
					{
						$bookingDetail['priceToPay'] = $this->helper->currencyFormat('',$split->splitted_amount);
						if($split->split_payment_status == 7)
						{
							$bookingDetail['isBookingUserPaid'] =  1;
						}
						else
						{
							//$bookingDetail['isBookingUserPaid'] =  1;
							$bookingDetail['isBookingUserPaid'] =  0;
							$bookingDetail['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$split->chauffeur_booking_split_id.'&booking_type=chauffeur.split';
						}
					}
					else
					{
						$tempSplit[] = $tempSingleSplit;
					}
				}
				$bookingDetail['splits'] = $tempSplit;
			}
			else{
				$bookingDetail['splits'] = array();
			}

			//if($resBookingDetail->user_status == 4 && $resBookingDetail->is_splitted == 0)

			if($resBookingDetail->user_status == 4)
			{
				$bookingDetail['paymentURL'] =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$resBookingDetail->chauffeur_booking_id.'&booking_type=chauffeur';
			}
			else if(!isset($bookingDetail['paymentURL']))
			{
				$bookingDetail['paymentURL'] = '';
			}
		}

		$this->jsonarray['code']          = 200;
		$this->jsonarray['bookingType']   = $bookingType;
		$this->jsonarray['bookingDetail'] = $bookingDetail;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"getFavourites","taskData":{}}
	 *
	 */
	function getFavourites()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$latitude  = IJReq::getTaskData('latitude','','string');
		$longitude = IJReq::getTaskData('longitude','','string');


		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$favouriteSql = $db->getQuery(true);

		// Create the base select statement.
		$favouriteSql->select('element_id,element_type')
			->from($db->quoteName('#__beseated_favourite'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

		// Set the query and load the result.
		$db->setQuery($favouriteSql);

		$resFavourites = $db->loadObjectList();
		$favProtectionIDs = array();
		$favVenueIDs      = array();
		$favChauffeurIDs  = array();
		$favYachtIDs      = array();
		$favPrivateJetIDs = array();

		foreach ($resFavourites as $key => $favourite)
		{
			$tblElement = JTable::getInstance($favourite->element_type, 'BeseatedTable');

			$elementID = strtolower($favourite->element_type).'_id';
			$tblElement->load($favourite->element_id);

			if($tblElement->$elementID)
			{
				if(strtolower($favourite->element_type) == 'protection')
				{
					$favProtectionIDs[] = $favourite->element_id;
				}
				else if(strtolower($favourite->element_type) == 'venue')
				{
					$favVenueIDs[] = $favourite->element_id;
				}
				else if(strtolower($favourite->element_type) == 'yacht')
				{
					$favYachtIDs[] = $favourite->element_id;
				}
				else if(strtolower($favourite->element_type) == 'chauffeur')
				{
					$favChauffeurIDs[] = $favourite->element_id;
				}
			}
		}

		//echo "<pre/>";print_r($favVenueIDs);exit;

		// Strat of Venue Favourite
		$resultVenues = array();
		$venueCities = array();

		if(count($favVenueIDs)!=0)
		{
			$favVenueSql = $db->getQuery(true);
			$favVenueSql->select('venue_id,venue_name,location,city,avg_ratting,latitude,longitude')
				->from($db->quoteName('#__beseated_venue'))
				->where($db->quoteName('published') . ' = ' . $db->quote('1'))
				->where($db->quoteName('venue_id') . ' IN ('.implode(',', $favVenueIDs).')');
			$db->setQuery($favVenueSql);

			$resFavVenue = $db->loadObjectList();

			$resultVenues =  array();
			$resultVenueIDs = array();
			foreach ($resFavVenue as $key => $venue)
			{
				$temp              = array();
				$resultVenueIDs[]  = $venue->venue_id;
				$venueCities[]     = $venue->city;
				$temp['venueID']   = $venue->venue_id;
				$temp['venueName'] = $venue->venue_name;
				$temp['location']  = $venue->location;
				$temp['city']      = $venue->city;
				$temp['ratting']   = $venue->avg_ratting;
				$temp['latitude']  = $venue->latitude;
				$temp['longitude'] = $venue->longitude;
				$resultVenues[]    = $temp;
			}

			$venueImageSql = $db->getQuery(true);
			$venueImageSql->select('*')
				->from($db->quoteName('#__beseated_element_images'))
				->where($db->quoteName('element_id') . ' IN ('.implode(',', $resultVenueIDs).')')
				->where($db->quoteName('element_type') . ' = ' . $db->quote('Venue'))
				->order($db->quoteName('image_id') . ' ASC');

			$db->setQuery($venueImageSql);
			$resultVenueImages = $db->loadObjectList();

			$allVenueImages    = array();
			$corePath          = JUri::base().'/images/beseated/';

			foreach ($resultVenueImages as $key => $venueImage)
			{
				$tempImg               = array();
				$tempImg['thumbImage'] = ($venueImage->thumb_image)?$corePath.$venueImage->thumb_image:'';
				$tempImg['image']      = ($venueImage->image)?$corePath.$venueImage->image:'';
				$tempImg['isVideo']    = $venueImage->is_video;
				$tempImg['isDefault']  = $venueImage->is_default;
				$allVenueImages[$venueImage->element_id][] = $tempImg;
			}

			foreach ($resultVenues as $key => $venue)
			{
				if(isset($allVenueImages[$venue['venueID']]))
				{
					$resultVenues[$key]['images'] = $allVenueImages[$venue['venueID']];
				}
				else
				{
					$resultVenues[$key]['images'] = array();
				}
			}
		}
		// End of Venue Favourite

		// Strat of Protection Favourite
		$resultProtections = array();
		$protectionCities = array();
		$allLuxuryCities = array();
		if(count($favProtectionIDs)!=0)
		{
			$favProtectionSql = $db->getQuery(true);
			$favProtectionSql->select('protection_id,protection_name,location,city,avg_ratting,latitude,longitude')
				->from($db->quoteName('#__beseated_protection'))
				->where($db->quoteName('published') . ' = ' . $db->quote('1'))
				->where($db->quoteName('protection_id') . ' IN ('.implode(',', $favProtectionIDs).')');
			$db->setQuery($favProtectionSql);

			$resFavProtection = $db->loadObjectList();
			$resultProtections =  array();
			$resultProtectionIDs = array();
			foreach ($resFavProtection as $key => $protection)
			{
				$temp                  = array();
				$resultProtectionIDs[] = $protection->protection_id;
				//$protectionCities[]  = $protection->city;
				$allLuxuryCities[]     = $protection->city;

				$temp['elementID']     = $protection->protection_id;
				$temp['elementName']   = $protection->protection_name;
				$temp['elementType']   = 'Protection';
				$temp['location']      = $protection->location;
				$temp['city']          = $protection->city;
				$temp['ratting']       = $protection->avg_ratting;
				$temp['latitude']      = $protection->latitude;
				$temp['longitude']     = $protection->longitude;
				$resultProtections[]   = $temp;
			}

			$protectionImageSql = $db->getQuery(true);
			$protectionImageSql->select('*')
				->from($db->quoteName('#__beseated_element_images'))
				->where($db->quoteName('element_id') . ' IN ('.implode(',', $resultProtectionIDs).')')
				->where($db->quoteName('element_type') . ' = ' . $db->quote('Protection'))
				->order($db->quoteName('element_type') . ' ASC ,' . $db->quoteName('element_id') . ' ASC');
			$db->setQuery($protectionImageSql);

			$allProtectionImages    = array();
			$corePath               = JUri::base().'images/beseated/';
			$resultProtectionImages = $db->loadObjectList();
			foreach ($resultProtectionImages as $key => $protectionImage)
			{
				$tempImg               = array();
				$tempImg['thumbImage'] = ($protectionImage->thumb_image)?$corePath.$protectionImage->thumb_image:'';
				$tempImg['image']      = ($protectionImage->image)?$corePath.$protectionImage->image:'';
				$tempImg['isVideo']       = $protectionImage->is_video;
				$tempImg['isDefault']     = $protectionImage->is_default;
				$allProtectionImages[$protectionImage->element_id][] = $tempImg;
			}

			foreach ($resultProtections as $key => $protection)
			{
				if(isset($allProtectionImages[$protection['elementID']]))
				{
					$resultProtections[$key]['images'] = $allProtectionImages[$protection['elementID']];
				}
				else
				{
					$resultProtections[$key]['images'] = array();
				}
			}
		} // End of Protection Favourite



		// Strat of Yacht Favourite
		$resultYachts = array();
		$yachtCities = array();
		if(count($favYachtIDs)!=0)
		{
			$favYachtSql = $db->getQuery(true);
			$favYachtSql->select('yacht_id,yacht_name,location,city,avg_ratting,latitude,longitude')
				->from($db->quoteName('#__beseated_yacht'))
				->where($db->quoteName('published') . ' = ' . $db->quote('1'))
				->where($db->quoteName('yacht_id') . ' IN ('.implode(',', $favYachtIDs).')');
			$db->setQuery($favYachtSql);

			$resFavYacht = $db->loadObjectList();
			$resultYachts =  array();
			$resultYachtIDs = array();
			foreach ($resFavYacht as $key => $yacht)
			{
				$temp                = array();
				$resultYachtIDs[]    = $yacht->yacht_id;
				//$yachtCities[]     = $yacht->city;
				$allLuxuryCities[]   = $yacht->city;
				$temp['elementID']   = $yacht->yacht_id;
				$temp['elementName'] = $yacht->yacht_name;
				$temp['elementType'] = 'Yacht';
				$temp['location']    = $yacht->location;
				$temp['city']        = $yacht->city;
				$temp['ratting']     = $yacht->avg_ratting;
				$temp['latitude']    = $yacht->latitude;
				$temp['longitude']   = $yacht->longitude;
				$resultYachts[]      = $temp;
			}

			$yachtImageSql = $db->getQuery(true);
			$yachtImageSql->select('*')
				->from($db->quoteName('#__beseated_element_images'))
				->where($db->quoteName('element_id') . ' IN ('.implode(',', $resultYachtIDs).')')
				->where($db->quoteName('element_type') . ' = ' . $db->quote('Yacht'))
				->order($db->quoteName('element_type') . ' ASC ,' . $db->quoteName('element_id') . ' ASC');
			$db->setQuery($yachtImageSql);


			$allYachtImages    = array();
			$corePath               = JUri::base().'images/beseated/';
			$resultYachtImages = $db->loadObjectList();
			foreach ($resultYachtImages as $key => $yachtImage)
			{
				$tempImg               = array();
				$tempImg['thumbImage'] = ($yachtImage->thumb_image)?$corePath.$yachtImage->thumb_image:'';
				$tempImg['image']      = ($yachtImage->image)?$corePath.$yachtImage->image:'';
				$tempImg['isVideo']    = $yachtImage->is_video;
				$tempImg['isDefault']  = $yachtImage->is_default;
				$allYachtImages[$yachtImage->element_id][] = $tempImg;
			}

			foreach ($resultYachts as $key => $yacht)
			{
				if(isset($allYachtImages[$yacht['elementID']]))
				{
					$resultYachts[$key]['images'] = $allYachtImages[$yacht['elementID']];
				}
				else
				{
					$resultYachts[$key]['images'] = array();
				}
			}
		} // End of Protection Favourite

		// Strat of chauffeur Favourite
		$resultChauffeurs = array();
		$chauffeurCities = array();
		if(count($favChauffeurIDs)!=0)
		{
			$favChauffeurSql = $db->getQuery(true);
			$favChauffeurSql->select('chauffeur_id,chauffeur_name,location,city,avg_ratting,latitude,longitude')
				->from($db->quoteName('#__beseated_chauffeur'))
				->where($db->quoteName('published') . ' = ' . $db->quote('1'))
				->where($db->quoteName('chauffeur_id') . ' IN ('.implode(',', $favChauffeurIDs).')');
			$db->setQuery($favChauffeurSql);

			$resFavChauffeur = $db->loadObjectList();
			$resultChauffeurs =  array();
			$resultChauffeurIDs = array();
			foreach ($resFavChauffeur as $key => $chauffeur)
			{
				$temp                  = array();
				$resultChauffeurIDs[]  = $chauffeur->chauffeur_id;
				//$chauffeurCities[]     = $chauffeur->city;
				$allLuxuryCities[]     = $chauffeur->city;
				$temp['elementID']   = $chauffeur->chauffeur_id;
				$temp['elementName'] = $chauffeur->chauffeur_name;
				$temp['elementType'] = 'Chauffeur';
				$temp['location']      = $chauffeur->location;
				$temp['city']          = $chauffeur->city;
				$temp['ratting']       = $chauffeur->avg_ratting;
				$temp['latitude']      = $chauffeur->latitude;
				$temp['longitude']     = $chauffeur->longitude;
				$resultChauffeurs[]    = $temp;
			}

			$chauffeurImageSql = $db->getQuery(true);
			$chauffeurImageSql->select('*')
				->from($db->quoteName('#__beseated_element_images'))
				->where($db->quoteName('element_id') . ' IN ('.implode(',', $resultChauffeurIDs).')')
				->where($db->quoteName('element_type') . ' = ' . $db->quote('Chauffeur'))
				->order($db->quoteName('element_type') . ' ASC ,' . $db->quoteName('element_id') . ' ASC');
			$db->setQuery($chauffeurImageSql);


			$allChauffeurImages    = array();
			$corePath               = JUri::base().'images/beseated/';
			$resultChauffeurImages = $db->loadObjectList();

			//echo "<pre/>";print_r($resultChauffeurImages);exit;

			foreach ($resultChauffeurImages as $key => $chauffeurImage)
			{

				$tempImg               = array();
				$tempImg['thumbImage'] = ($chauffeurImage->thumb_image)? $corePath.$chauffeurImage->thumb_image:'';
				$tempImg['image']      = ($chauffeurImage->image)? $corePath.$chauffeurImage->image:'';
				$tempImg['isVideo']    = $chauffeurImage->is_video;
				$tempImg['isDefault']  = $chauffeurImage->is_default;
				$allChauffeurImages[$chauffeurImage->element_id][] = $tempImg;

			}

			foreach ($resultChauffeurs as $key => $chauffeur)
			{
				if(isset($allChauffeurImages[$chauffeur['elementID']]))
				{
					$resultChauffeurs[$key]['images'] = $allChauffeurImages[$chauffeur['elementID']];
				}
				else
				{
					$resultChauffeurs[$key]['images'] = array();
				}
			}
		} // End of Protection Favourite

		if(count($resultProtections) == 0 && count($resultYachts) == 0 && count($resultChauffeurs) ==0 && count($resultVenues) == 0)
		{
			$this->jsonarray['code'] = 204;
			return $this->jsonarray;
		}

		$this->jsonarray['code'] = 200;
		/*$this->jsonarray['favouriteProtection']['protections'] = $resultProtections;
		$this->jsonarray['favouriteProtection']['protectionCities'] = array_unique($protectionCities);

		$this->jsonarray['favouriteYacht']['yacht'] = $resultYachts;
		$this->jsonarray['favouriteYacht']['yachtCities'] = array_unique($yachtCities);

		$this->jsonarray['favouriteChauffeur']['chauffeur'] = $resultChauffeurs;
		$this->jsonarray['favouriteChauffeur']['chauffeurCities'] = array_unique($chauffeurCities);*/



		/*$this->jsonarray['favouriteLuxury']['yacht'] = $resultYachts;
		$this->jsonarray['favouriteLuxury']['yachtCities'] = array_unique($yachtCities);

		$this->jsonarray['favouriteLuxury']['chauffeur'] = $resultChauffeurs;
		$this->jsonarray['favouriteLuxury']['chauffeurCities'] = array_unique($chauffeurCities);*/

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

		$cityName = BeseatedHelper::getAddressFromLatlong($latitude,$longitude);

		$allLuxuryCities = $this->filterCities($cityName,$allLuxuryCities);
		$venueCities     = $this->filterCities($cityName,$venueCities);

		$this->jsonarray['favouriteLuxury']['luxuries'] = array_merge($resultProtections,$resultYachts,$resultChauffeurs);
		$this->jsonarray['favouriteLuxury']['cities'] = $allLuxuryCities;

		$this->jsonarray['favouriteVenue']['venues'] = $resultVenues;
		$this->jsonarray['favouriteVenue']['venueCities'] =  $venueCities;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"payByCash","taskData":{"bookingID":"0"}}
	 *
	 */
	function payByCash()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingID   = IJReq::getTaskData('bookingID', 0, 'int');
		$bookingType = IJReq::getTaskData('bookingType', '', 'string');

		if(!$bookingID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		$bookingType = strtolower($bookingType);
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblVenueBooking->load($bookingID);

		$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');
		$tblVenue->load($tblVenueBooking->venue_id);

		if(!$tblVenueBooking->venue_table_booking_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($tblVenueBooking->is_splitted)
		{

			$status = $this->helper->getStatusID('pending');
			$query = $this->db->getQuery(true);
			$query->select('*')
				->from($this->db->quoteName('#__beseated_venue_table_booking_split'))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($this->IJUserID))
				->where($this->db->quoteName('split_payment_status') . ' = ' . $this->db->quote($status))
				->where($this->db->quoteName('venue_table_booking_id') . ' = ' . $this->db->quote($tblVenueBooking->venue_table_booking_id));

			$this->db->setQuery($query);
			$resSplitDetail = $this->db->loadObject();
			if(!$resSplitDetail->venue_table_booking_split_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if($resSplitDetail->pay_by_cash_status >= 1){
				IJReq::setResponseCode(707);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PAY_BY_CASH_REQUEST_ALREADY_SENT'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$query = $this->db->getQuery(true);
			$query->update($this->db->quoteName('#__beseated_venue_table_booking_split'))
				->set($this->db->quoteName('pay_by_cash_status') . ' = ' . $this->db->quote(1))
				->where($this->db->quoteName('venue_table_booking_split_id') . ' = ' . $this->db->quote($resSplitDetail->venue_table_booking_split_id));
			$this->db->setQuery($query);
			$this->db->execute();

			/*echo "<pre>";
			print_r($query->dump());
			echo "</pre>";
			exit;*/

			$myProfile = $this->helper->guestUserDetail($this->IJUserID);
			$notificationType = "venue.split.paybycash.request";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_PAY_BY_CASH_REQUEST_VENUE',
									ucwords($myProfile->full_name)
								);
			$actor            = $this->IJUserID;
			$target           = $tblVenue->user_id;
			$elementID        = $tblVenue->venue_id;
			$elementType      = "Venue";
			$cid              = $resSplitDetail->venue_table_booking_split_id;
			$extraParams      = array();
			$extraParams["venueTableBookingSplitID"] = $resSplitDetail->venue_table_booking_split_id;

			if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$bookingID))
			{
				$this->jsonarray['pushNotificationData']['id']          = $tblVenueBooking->venue_table_booking_id;
				$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
				$this->jsonarray['pushNotificationData']['to']          = $target;
				$this->jsonarray['pushNotificationData']['message']     = $title;
				$this->jsonarray['pushNotificationData']['type']        = $notificationType;
				$this->jsonarray['pushNotificationData']['configtype']  = '';
			}

			$this->jsonarray['code'] = 200;
			return $this->jsonarray;
		}

		if($tblVenueBooking->pay_by_cash_status >= 1)
		{
			IJReq::setResponseCode(707);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PAY_BY_CASH_REQUEST_ALREADY_SENT'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblVenueBooking->pay_by_cash_status = 1;
		if($tblVenueBooking->store())
		{
			$tblVenue->load($tblVenueBooking->venue_id);
			$myProfile = $this->helper->guestUserDetail($this->IJUserID);
			$notificationType = "venue.paybycash.request";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_PAY_BY_CASH_REQUEST_VENUE',
									ucwords($myProfile->full_name)
								);
			$actor            = $this->IJUserID;
			$target           = $tblVenue->user_id;
			$elementID        = $tblVenue->venue_id;
			$elementType      = "Venue";
			$cid              = $tblVenueBooking->venue_table_booking_id;
			$extraParams      = array();
			$extraParams["venueTableBookingID"] = $tblVenueBooking->venue_table_booking_id;
			$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$cid);

			$this->jsonarray['pushNotificationData']['id']         = $tblVenueBooking->venue_table_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
			$this->jsonarray['pushNotificationData']['to']         = $target;
			$this->jsonarray['pushNotificationData']['message']    = $title;
			/*$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_NOTIFICATION_TYPE_TABLE_BOOKING_PAY_BY_CASH_REQUEST_VENUE');*/
			$this->jsonarray['pushNotificationData']['type']       = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype'] = '';
		}

		$this->jsonarray['code'] = 200;

		return $this->jsonarray;

		/*}else if($bookingType == 'venue.split'){

			$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
			$tblVenueBooking->load($tblVenueBookingSplit->venue_table_booking_id);
			$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');
			if(!$tblVenueBooking->venue_table_booking_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if($tblVenueBookingSplit->pay_by_cash_status >= 1){
				IJReq::setResponseCode(707);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PAY_BY_CASH_REQUEST_ALREADY_SENT'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$tblVenueBookingSplit->pay_by_cash_status = 1;
			if($tblVenueBookingSplit->store()){

			}
		}else if($bookingType == 'protection'){
			$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
			$tblProtection        = JTable::getInstance('Protection', 'BeseatedTable');
			$tblVenueBooking->load($bookingID);
			if(!$tblProtectionBooking->protection_booking_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_PROTECTION_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if($tblProtectionBooking->pay_by_cash_status >= 1){
				IJReq::setResponseCode(707);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PAY_BY_CASH_REQUEST_ALREADY_SENT'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$tblProtectionBooking->pay_by_cash_status = 1;
			if($tblProtectionBooking->store()){
				$tblProtection->load($tblProtectionBooking->protection_id);
				$myProfile = $this->helper->guestUserDetail($this->IJUserID);
				$notificationType = "protection.paybycash";
				$title            = JText::sprintf(
										'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_PAY_BY_CASH_REQUEST_PROTECTION',
										ucwords($myProfile->full_name)
									);
				$actor            = $this->IJUserID;
				$target           = $tblProtection->user_id;
				$elementID        = $tblProtection->protection_id;
				$elementType      = "Protection";
				$cid              = $tblProtectionBooking->protection_booking_id;
				$extraParams      = array();
				$extraParams["protectionBookingID"] = $tblProtectionBooking->protection_booking_id;
				$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);
			}
		}else if($bookingType == 'protection.split'){
			$tblProtectionBookingSplit      = JTable::getInstance('ProtectionBookingSplit', 'BeseatedTable');
			$tblProtectionBookingSplit->load($bookingID);
			if(!$tblProtectionBookingSplit->protection_booking_split_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
			$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
			$tblProtectionBooking->load($tblProtectionBookingSplit->venue_table_booking_id);
			$tblProtection        = JTable::getInstance('Protection', 'BeseatedTable');
			if(!$tblProtectionBooking->venue_table_booking_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if($tblProtectionBookingSplit->pay_by_cash_status >= 1){
				IJReq::setResponseCode(707);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PAY_BY_CASH_REQUEST_ALREADY_SENT'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$tblProtectionBookingSplit->pay_by_cash_status = 1;
			if($tblProtectionBookingSplit->store()){
				$tblProtection->load($tblProtectionBooking->protection_id);
				$myProfile = $this->helper->guestUserDetail($this->IJUserID);
				$notificationType = "protection.split.paybycash";
				$title            = JText::sprintf(
										'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_PAY_BY_CASH_REQUEST_PROTECTION',
										ucwords($myProfile->full_name)
									);
				$actor            = $this->IJUserID;
				$target           = $tblProtection->user_id;
				$elementID        = $tblProtection->protection_id;
				$elementType      = "Protection";
				$cid              = $tblProtectionBookingSplit->protection_booking_split_id;
				$extraParams      = array();
				$extraParams["protectionBookingSplitID"] = $tblProtectionBookingSplit->protection_booking_split_id;
				$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);
			}
		}else if($bookingType == 'chauffeur'){
			$tblChauffeurBooking = JTable::getInstance('ChauffeurBooking', 'BeseatedTable');
			$tblChauffeur        = JTable::getInstance('Chauffeur', 'BeseatedTable');
			$tblChauffeurBooking->load($bookingID);
			if(!$tblChauffeurBooking->chauffeur_booking_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_CHAUFFEUR_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if($tblChauffeurBooking->pay_by_cash_status >= 1){
				IJReq::setResponseCode(707);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PAY_BY_CASH_REQUEST_ALREADY_SENT'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$tblChauffeurBooking->pay_by_cash_status = 1;
			if($tblChauffeurBooking->store()){
				$tblChauffeur->load($tblChauffeurBooking->chauffeur_id);
				$myProfile = $this->helper->guestUserDetail($this->IJUserID);
				$notificationType = "chauffeur.paybycash";
				$title            = JText::sprintf(
										'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_PAY_BY_CASH_REQUEST_CHAUFFEUR',
										ucwords($myProfile->full_name)
									);
				$actor            = $this->IJUserID;
				$target           = $tblChauffeur->user_id;
				$elementID        = $tblChauffeur->chauffeur_id;
				$elementType      = "Protection";
				$cid              = $tblChauffeurBooking->chauffeur_booking_id;
				$extraParams      = array();
				$extraParams["chauffeurBookingID"] = $tblChauffeurBooking->chauffeur_booking_id;
				$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);
			}
		}else if($bookingType == 'chauffeur.split'){
			$tblChauffeurBookingSplit      = JTable::getInstance('ChauffeurBookingSplit', 'BeseatedTable');
			$tblChauffeurBookingSplit->load($bookingID);
			if(!$tblChauffeurBookingSplit->chauffeur_booking_split_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_CHAUFFEUR_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
			$tblChauffeurBooking = JTable::getInstance('ChauffeurBooking', 'BeseatedTable');
			$tblChauffeurBooking->load($tblChauffeurBookingSplit->chauffeur_booking_id);
			$tblChauffeur       = JTable::getInstance('Chauffeur', 'BeseatedTable');
			if(!$tblChauffeurBooking->chauffeur_booking_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_CHAUFFEUR_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if($tblChauffeurBookingSplit->pay_by_cash_status >= 1){
				IJReq::setResponseCode(707);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PAY_BY_CASH_REQUEST_ALREADY_SENT'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$tblChauffeurBookingSplit->pay_by_cash_status = 1;
			if($tblChauffeurBookingSplit->store()){
				$tblChauffeur->load($tblChauffeurBooking->chauffeur_id);
				$myProfile = $this->helper->guestUserDetail($this->IJUserID);
				$notificationType = "chauffeur.split.paybycash";
				$title            = JText::sprintf(
										'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_PAY_BY_CASH_REQUEST_CHAUFFEUR',
										ucwords($myProfile->full_name)
									);
				$actor            = $this->IJUserID;
				$target           = $tblChauffeur->user_id;
				$elementID        = $tblChauffeur->chauffeur_id;
				$elementType      = "Chauffeur";
				$cid              = $tblChauffeurBookingSplit->chauffeur_booking_split_id;
				$extraParams      = array();
				$extraParams["chauffeurBookingSplitID"] = $tblChauffeurBookingSplit->chauffeur_booking_split_id;
				$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);
			}
		}else if($bookingType == 'yacht'){
			$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
			$tblYacht        = JTable::getInstance('Yacht', 'BeseatedTable');
			$tblYachtBooking->load($bookingID);
			if(!$tblYachtBooking->yacht_booking_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_YACHT_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if($tblYachtBooking->pay_by_cash_status >= 1){
				IJReq::setResponseCode(707);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PAY_BY_CASH_REQUEST_ALREADY_SENT'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$tblYachtBooking->pay_by_cash_status = 1;
			if($tblYachtBooking->store()){
				$tblYacht->load($tblYachtBooking->yacht_id);
				$myProfile = $this->helper->guestUserDetail($this->IJUserID);
				$notificationType = "yacht.paybycash";
				$title            = JText::sprintf(
										'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_PAY_BY_CASH_REQUEST_YACHT',
										ucwords($myProfile->full_name)
									);
				$actor            = $this->IJUserID;
				$target           = $tblYacht->user_id;
				$elementID        = $tblYacht->yacht_id;
				$elementType      = "Yacht";
				$cid              = $tblYachtBooking->yacht_booking_id;
				$extraParams      = array();
				$extraParams["yachtBookingID"] = $tblYachtBooking->yacht_booking_id;
				$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);
			}
		}else if($bookingType == 'yacht.split'){
			$tblYachtBookingSplit      = JTable::getInstance('YachtBookingSplit', 'BeseatedTable');
			$tblYachtBookingSplit->load($bookingID);
			if(!$tblYachtBookingSplit->yacht_booking_split_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_YACHT_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
			$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
			$tblYachtBooking->load($tblYachtBookingSplit->yacht_booking_id);
			$tblYacht        = JTable::getInstance('Yacht', 'BeseatedTable');
			if(!$tblYachtBooking->yacht_booking_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_YACHT_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if($tblYachtBookingSplit->pay_by_cash_status >= 1){
				IJReq::setResponseCode(707);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PAY_BY_CASH_REQUEST_ALREADY_SENT'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$tblYachtBookingSplit->pay_by_cash_status = 1;
			if($tblYachtBookingSplit->store()){
				$tblYacht->load($tblYachtBooking->yacht_id);
				$myProfile = $this->helper->guestUserDetail($this->IJUserID);
				$notificationType = "yacht.split.paybycash";
				$title            = JText::sprintf(
										'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_PAY_BY_CASH_REQUEST_YACHT',
										ucwords($myProfile->full_name)
									);
				$actor            = $this->IJUserID;
				$target           = $tblYacht->user_id;
				$elementID        = $tblYacht->yacht_id;
				$elementType      = "Yacht";
				$cid              = $tblYachtBookingSplit->yacht_booking_split_id;
				$extraParams      = array();
				$extraParams["yachtBookingSplitID"] = $tblYachtBookingSplit->yacht_booking_split_id;
				$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);
			}
		}else{
			$this->jsonarray['code'] = '400';
			return $this->jsonarray;
		}*/

		/*$this->jsonarray['code'] = 200;

		return $this->jsonarray;*/
	}


	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"getNotification","taskData":{"pageNO":"0"}}
	 *
	 */
	function getNotification()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$pageNO    = IJReq::getTaskData('pageNO',0);
		$pageLimit = BESEATED_GENERAL_LIST_LIMIT;
		if($pageNO==0 || $pageNO==1){
			$startFrom=0;
		}else{
			$startFrom = $pageLimit*($pageNO-1);
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_notification').'AS a')
			->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
			->join('INNER', '#__users AS b ON b.id = a.actor')
			->order($db->quoteName('notification_id') . ' DESC');


		// Set the query and load the result.
		$db->setQuery($query);

		$resNotifi = $db->loadObjectList();

		if(count($resNotifi) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_NOTIFICATION_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$resultNotifications = array();
		foreach ($resNotifi as $key => $notification)
		{
			$userType =  $this->helper->getUserType($notification->actor);

			$temp                     = array();
			$temp['id']               = $notification->booking_id;
			$temp['notificationID']   = $notification->notification_id;
			$temp['title']            = $notification->title;
			$temp['notificationType'] = $notification->notification_type;
			$temp['timestamp']        = $notification->time_stamp;
			$temp['isRead']           = $notification->is_read;
			$temp['userType']         = ($userType)?$userType:"";

			$element_type             = explode('.', $notification->element_type);

			$temp['elementType']      = $element_type[0];

			$otherUserProfile = $this->helper->guestUserDetail($notification->actor);
			if($otherUserProfile->user_type == 'yacht'){
				$elementDetail       = $this->helper->yachtUserDetail($notification->actor);
				$elementType         = "Yacht";
				$temp['elementName'] = $elementDetail->yacht_name;
				$temp['location']    = $elementDetail->location;
				$temp['city']        = $elementDetail->city;
				$imageDetail         = $this->helper->getElementDefaultImage($elementDetail->yacht_id,'Yacht');
				$temp['avatar']      = ($imageDetail->image)?JUri::root().'images/beseated/'.$imageDetail->image:'';
				$temp['thumbAvatar'] = ($imageDetail->thumb_image)?JUri::root().'images/beseated/'.$imageDetail->thumb_image:'';
			}else if($otherUserProfile->user_type == 'venue'){
				$elementDetail       = $this->helper->venueUserDetail($notification->actor);
				$elementType         = "Venue";
				$temp['elementName'] = $elementDetail->venue_name;
				$temp['location']    = $elementDetail->location;
				$temp['city']        = $elementDetail->city;
				$imageDetail         = $this->helper->getElementDefaultImage($elementDetail->venue_id,'Venue');
				$temp['avatar']      = ($imageDetail->image)?JUri::root().'images/beseated/'.$imageDetail->image:'';
				$temp['thumbAvatar'] = ($imageDetail->thumb_image)?JUri::root().'images/beseated/'.$imageDetail->thumb_image:'';

				//echo "<pre/>";print_r($temp);exit;
			}else if($otherUserProfile->user_type == 'protection'){
				$elementDetail       = $this->helper->protectionUserDetail($notification->actor);
				$elementType         = "Protection";
				$temp['elementName'] = $elementDetail->protection_name;
				$temp['location']    = $elementDetail->location;
				$temp['city']        = $elementDetail->city;
				$imageDetail         = $this->helper->getElementDefaultImage($elementDetail->protection_id,'Protection');
				$temp['avatar']      = ($imageDetail->image)?JUri::root().'images/beseated/'.$imageDetail->image:'';
				$temp['thumbAvatar'] = ($imageDetail->thumb_image)?JUri::root().'images/beseated/'.$imageDetail->thumb_image:'';
			}else if($otherUserProfile->user_type == 'chauffeur'){
				$elementDetail       = $this->helper->chauffeurUserDetail($notification->actor);
				$elementType         = "Chauffeur";
				$temp['elementName'] = $elementDetail->chauffeur_name;
				$temp['location']    = $elementDetail->location;
				$temp['city']        = $elementDetail->city;
				$imageDetail         = $this->helper->getElementDefaultImage($elementDetail->chauffeur_id,'Chauffeur');
				$temp['avatar']      = ($imageDetail->image)?JUri::root().'images/beseated/'.$imageDetail->image:'';
				$temp['thumbAvatar'] = ($imageDetail->thumb_image)?JUri::root().'images/beseated/'.$imageDetail->thumb_image:'';
			}else if($otherUserProfile->user_type == 'beseated_guest'){
				$elementDetail       = $otherUserProfile;
				$elementType         = "BeseatedGuest";
				$temp['elementName'] = $elementDetail->full_name;
				$temp['location']    = $elementDetail->location;
				$temp['city']        = $elementDetail->city;
				$temp['avatar']      = ($elementDetail->avatar)?$this->helper->getUserAvatar($elementDetail->avatar):'';
				$temp['thumbAvatar'] = ($elementDetail->thumb_avatar)?$this->helper->getUserAvatar($elementDetail->thumb_avatar):'';
			}

			$resultNotifications[] = $temp;
		}

		if(count($resultNotifications) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_NOTIFICATION_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray['code']              = 200;
		$this->jsonarray['notificationCount'] = count($resultNotifications);
		$this->jsonarray['notification']      = $resultNotifications;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"getLoyalty","taskData":{"pageNO":"0"}}
	 *
	 */
	function getLoyalty()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		//sleep(120);

		$pageNO    = IJReq::getTaskData('pageNO',0);
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_loyalty_point'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('is_valid') . ' = ' . $db->quote(1))
			->order($db->quoteName('time_stamp') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$resLoyalty = $db->loadObjectList();
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$loyaltyResult = array();
		$totalLoyalty = 0;

		//echo "<pre/>";print_r($resLoyalty);exit;

		foreach ($resLoyalty as $key => $loyalty)
		{
			$tempLoyalty          = array();
			$tempLoyalty['money'] = $this->helper->currencyFormat('',$loyalty->money_usd);
			$tempLoyalty['point'] = $this->helper->currencyFormat('',$loyalty->earn_point,2);
			$totalLoyalty         = $totalLoyalty + $loyalty->earn_point;
			$tempLoyalty['date']  = $this->helper->convertDateFormat($loyalty->created);

			$tempLoyalty['type'] = $loyalty->title;
			$loyaltyResult[]     = $tempLoyalty;

		}

		$this->jsonarray['rewards'] = $this->getRewards();
		if(count($loyaltyResult) == 0)
		{
			$this->jsonarray['code'] = 204;

			return $this->jsonarray;
		}

		$this->jsonarray['code'] = 200;
		$this->jsonarray['loyalty'] = $loyaltyResult;
		$this->jsonarray['totalLoyalty'] = $this->helper->currencyFormat('',$totalLoyalty);
		//$this->jsonarray['rewards'] = $this->getRewards();
		return $this->jsonarray;
	}

	function getRewards()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__beseated_rewards'))
			->where($db->quoteName('published') . ' = ' . $db->quote('1'))
			->order($db->quoteName('reward_coin') . ' ASC');
		$db->setQuery($query);
	    $resRewards = $db->loadObjectList();
	    $resultRewards = array();
		foreach ($resRewards as $key => $reward)
		{
			$tmpReward                = array();
			$tmpReward['rewardID']    = $reward->reward_id;
			$tmpReward['rewardName']  = $reward->reward_name;
			$tmpReward['rewardDesc']  = $reward->reward_desc;
			$tmpReward['rewardCoin']  = $this->helper->currencyFormat('',$reward->reward_coin);
			$tmpReward['rewardImage'] = ($reward->image) ? JURi::base().$reward->image : '';
			$resultRewards[]          = $tmpReward;
		}

	   return $resultRewards;

	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"addRating","taskData":{"elementType":"protection","elementID":"3","foodRating":"0","serviceRating":"5","atmosphereRating":"0","valueRating":"4","ratingCount":"2","comment":"This is testing rating"}}
	 *
	 */
	function addRating()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$elementType      = IJReq::getTaskData('elementType','','string');
		$elementID        = IJReq::getTaskData('elementID', 0, 'int');
		$foodRating       = IJReq::getTaskData('foodRating', 0, 'int');
		$serviceRating    = IJReq::getTaskData('serviceRating', 0, 'int');
		$atmosphereRating = IJReq::getTaskData('atmosphereRating', 0, 'int');
		$valueRating      = IJReq::getTaskData('valueRating', 0, 'int');
		$ratingCount      = IJReq::getTaskData('ratingCount', 0, 'int');
		$comment          = IJReq::getTaskData('comment','','string');

		$allElement  = array('venue','protection','yacht','chauffeur','private jet');
		$elementType = strtolower($elementType);

		if(!in_array($elementType, $allElement))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_RATING_ELEMENT_TYPE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!$elementID || !$ratingCount)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_RATING_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		//BeseatedHelper::calculateAvgRating($elementType,$elementID);
		$isRated = BeseatedHelper::isAlreadyRated($this->IJUserID,$elementType, $elementID);
		if($isRated)
		{
			IJReq::setResponseCode(707);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_ALREADY_RATED_ELEMENT_TYPE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblRating = JTable::getInstance('Rating','BeseatedTable');
		$tblRating->load(0);
		$totalRating = 0.00;
		$ratingPost['user_id']      = $this->IJUserID;
		$ratingPost['element_id']   = $elementID;
		$ratingPost['element_type'] = $elementType;
		$ratingPost['published']    = 1;
		$ratingPost['time_stamp']   = time();
		if($foodRating)
		{
			$ratingPost['food_rating'] = $foodRating;
			$totalRating = $totalRating + $foodRating;
		}

		if($serviceRating)
		{
			$ratingPost['service_rating'] = $serviceRating;
			$totalRating = $totalRating + $serviceRating;
		}
		if($atmosphereRating)
		{
			$ratingPost['atmosphere_rating'] = $atmosphereRating;
			$totalRating = $totalRating + $atmosphereRating;
		}
		if($valueRating)
		{
			$ratingPost['value_rating'] = $valueRating;
			$totalRating = $totalRating + $valueRating;
		}

		$avgRating                    = $totalRating / $ratingCount;
		$ratingPost['avg_rating']     = round($avgRating,2);
		$ratingPost['rating_comment'] = $comment;
		$ratingPost['rating_count']   = $ratingCount;
		$tblRating->bind($ratingPost);
		if(!$tblRating->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$tblElement = JTable::getInstance(ucfirst($elementType),'BeseatedTable');
		$tblElement->load($elementID);
		$tblElement->avg_ratting = $this->getAverageRatingOfElement($elementType, $elementID);

		if($tblElement->store())
		{
			$userDetail    = JFactory::getUser();
			$managerDetail = JFactory::getUser($tblElement->user_id);

			$this->emailHelper->bookingUserReviewRatingMail($userDetail->name,$userDetail->email);
			$this->emailHelper->managerReviewRatingMail($userDetail->name,$managerDetail->name,$managerDetail->email);

			$tblRating = JTable::getInstance('Rating','BeseatedTable');
			$tblRating->load(0);
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	function getAverageRatingOfElement($elementType, $elementID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('avg_rating')
			->from($db->quoteName('#__beseated_rating'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
			->where($db->quoteName('published') . ' = ' . $db->quote(1));

		// Set the query and load the result.
		$db->setQuery($query);

		$ratings      = $db->loadColumn();
		$totalRatings = array_sum($ratings);
		$countRatings = count($ratings);
		$avgRating    = $totalRatings / $countRatings;
		/*echo $avgRating;
		exit;*/
		return $avgRating;

	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"contact","taskData":{"subject":"Testing Subject","message":"This is Testing Message from protectin manager"}}
	 *
	 */
	function contact()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();
		$subject        = IJReq::getTaskData('subject','', 'string');
		$message        = IJReq::getTaskData('message','', 'string');

		if(empty($subject) || empty($message))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_CONTACT_MESSAGE_DETAIL_INVALID'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//$response = $this->helper->sendEmail($beseatedParams->contact_email,$subject,$message);
		$response   = true;
		if(!$response)
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$userDetail       = $this->helper->guestUserDetail($this->IJUserID);
		$userID           = $userDetail->user_id;
		$elementID        = '0';
		$elementType      = 'user';
		$this->helper->storeContactRequest($userID,$elementID,$elementType,$subject,$message);
		$this->emailHelper->contactAdmin($subject, $message);
		$this->emailHelper->contactThankYouEmail();

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"testingPushnotification","taskData":{"message":"This is Testing Message from protectin manager"}}
	 *
	 */
	function testingPushnotification($value='')
	{
		$message        = IJReq::getTaskData('message','This is Detault message', 'string');
		$this->jsonarray['code'] = 200;
		$this->jsonarray['pushNotificationData']['id']         = 1;
		$this->jsonarray['pushNotificationData']['to']         = 330;
		$this->jsonarray['pushNotificationData']['message']    = $message;
		$this->jsonarray['pushNotificationData']['type']       = 'PUSHNOTIFICATION_TYPE_NEWREQUESTRECEIVED';
		$this->jsonarray['pushNotificationData']['configtype'] = '';

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"sendFriendAttendingRequest","taskData":{"bookingID":""}}
	 *
	 */
	function sendFriendAttendingRequest()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingID           = IJReq::getTaskData('bookingID',0, 'int');
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenueBooking     = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblFriendsAttending = JTable::getInstance('FriendsAttending', 'BeseatedTable');


		$tblVenueTable     = JTable::getInstance('Table', 'BeseatedTable');
		$tblVenue          = JTable::getInstance('Venue', 'BeseatedTable');

		$tblVenueBooking->load($bookingID);
		if(!$tblVenueBooking->venue_table_booking_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_FRIENDS_ATTENDING_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$checkForAlreadyAttending = $this->getSingleFriendsAttendingStatus($bookingID);
		if($checkForAlreadyAttending)
		{
			IJReq::setResponseCode(707);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_FRIENDS_ATTENDING_REQUEST_ALREADY_SENT'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$postData                           = array();
		$postData['venue_table_booking_id'] = $tblVenueBooking->venue_table_booking_id;
		$postData['user_id']                = $this->IJUserID;
		$postData['booking_user_id']        = $tblVenueBooking->user_id;
		$postData['venue_id']               = $tblVenueBooking->venue_id;
		$postData['table_id']               = $tblVenueBooking->table_id;
		$postData['user_status']            = 1;
		$postData['booking_user_status']    = 1;

		$tblFriendsAttending->load(0);
		$tblFriendsAttending->bind($postData);

		if(!$tblFriendsAttending->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_FRIENDS_ATTENDING_REQUEST_NOT_SENT'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$FriendsAttendingData = array();

		$tblReadElementBooking = JTable::getInstance('ReadElementBooking', 'BeseatedTable');
		$tblReadElementBooking->load(0);

		$FriendsAttendingData['booked_type']      = 'friendAttending';
		$FriendsAttendingData['element_type']     = 'venue';
		$FriendsAttendingData['booking_id']       = $tblFriendsAttending->friends_attending_id;
		$FriendsAttendingData['from_user_id']     = $tblVenueBooking->user_id;
		$FriendsAttendingData['to_user_id']       = $this->IJUserID;
		$FriendsAttendingData['to_user_email_id'] = '';

		$tblReadElementBooking->bind($FriendsAttendingData);
		$tblReadElementBooking->store();

		$tblReadElementRsvp = JTable::getInstance('ReadElementRsvp', 'BeseatedTable');
		$tblReadElementRsvp->load(0);
		$tblReadElementRsvp->bind($FriendsAttendingData);
		$tblReadElementRsvp->store();

		$tblVenueTable->load($tblVenueBooking->table_id);
		$tblVenue->load($tblVenueBooking->venue_id);

		$bookingUserDetail = JFactory::getUser($tblVenueBooking->user_id);
		$loginUserDetail   = JFactory::getUser();
		$table_name        = $tblVenueTable->table_name;
		$venue_name        = $tblVenue->venue_name;
		$booking_date      = $this->helper->convertDateFormat($tblVenueBooking->booking_date);
		$booking_time      = $this->helper->convertToHM($tblVenueBooking->booking_time);

		$notificationType = "venue.service.attend.request";

		if($tblVenue->is_day_club)
		{
			$isNightVenue = 0;

			$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_VENUE_DAY_TABLE_BOOKING_ATTEND_REQUEST',
								$loginUserDetail->name,
								$tblVenue->venue_name,
								//$tblVenueTable->table_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date),
								$this->helper->convertToHM($tblVenueBooking->booking_time)
							);

			$db_title       = JText::sprintf(
								'COM_BESEATED_DB_NOTIFICATION_VENUE_DAY_TABLE_BOOKING_ATTEND_REQUEST',
								$tblVenueTable->table_name,
								$tblVenue->venue_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date),
								$this->helper->convertToHM($tblVenueBooking->booking_time)
							);
		}
		else
		{
			$isNightVenue = 1;

			$title           = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_VENUE_NIGHT_TABLE_BOOKING_ATTEND_REQUEST',
								$loginUserDetail->name,
								$tblVenue->venue_name,
								//$tblVenueTable->table_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date)
							);

			$db_title        = JText::sprintf(
								'COM_BESEATED_DB_NOTIFICATION_VENUE_NIGHT_TABLE_BOOKING_ATTEND_REQUEST',
								$tblVenueTable->table_name,
								$tblVenue->venue_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date)
							);


		}

		$actor       = $this->IJUserID;
		$target      = $bookingUserDetail->id;
		$elementID   = $tblVenue->venue_id;
		$elementType = "Venue";
		$cid         = $tblFriendsAttending->friends_attending_id;
		$extraParams = array();
		$extraParams["venueBookingID"]           = $tblVenueBooking->venue_table_booking_id;
		$extraParams["friendAttendingID"]        = $tblFriendsAttending->friends_attending_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$db_title,$cid,$extraParams,$tblVenueBooking->venue_table_booking_id))
		{
			$this->jsonarray['pushNotificationData']['id']         = $tblVenueBooking->venue_table_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
			$this->jsonarray['pushNotificationData']['to']         = $target;
			$this->jsonarray['pushNotificationData']['message']    = $title;
			$this->jsonarray['pushNotificationData']['type']       = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype'] = '';
		}

		$this->emailHelper->friendRequestJoinTableMail($bookingUserDetail->name,$loginUserDetail->name,$table_name,$venue_name,$booking_date,$booking_time,$isNightVenue,$bookingUserDetail->email);

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"sendFriendAttendingRequest","taskData":{"bookingID":""}}
	 *
	 */
	function cancelFriendAttendingRequest()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingID           = IJReq::getTaskData('bookingID',0, 'int');
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenueBooking     = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblFriendsAttending = JTable::getInstance('FriendsAttending', 'BeseatedTable');

		$tblVenueTable     = JTable::getInstance('Table', 'BeseatedTable');
		$tblVenue          = JTable::getInstance('Venue', 'BeseatedTable');

		$tblVenueBooking->load($bookingID);
		if(!$tblVenueBooking->venue_table_booking_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_FRIENDS_ATTENDING_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$checkForAlreadyAttending = $this->getSingleFriendsAttendingStatus($bookingID);

		if(!$checkForAlreadyAttending)
		{
			IJReq::setResponseCode(707);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_FRIENDS_ATTENDING_REQUEST_NOT_SENT'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->delete()
			->from($db->quoteName('#__beseated_venue_friends_attending'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($this->IJUserID))
			->where($this->db->quoteName('venue_table_booking_id') . ' = ' . $this->db->quote($bookingID));

		// Set the query and execute the delete.
		$db->setQuery($query);

		if(!$db->execute())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_FRIENDS_ATTENDING_REQUEST_NOT_CANCEL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblVenueTable->load($tblVenueBooking->table_id);
		$tblVenue->load($tblVenueBooking->venue_id);

		$bookingUserDetail = JFactory::getUser($tblVenueBooking->user_id);
		$loginUserDetail   = JFactory::getUser();
		$table_name        = $tblVenueTable->table_name;
		$venue_name        = $tblVenue->venue_name;
		$booking_date      = $this->helper->convertDateFormat($tblVenueBooking->booking_date);
		$booking_time      = $this->helper->convertToHM($tblVenueBooking->booking_time);

		$notificationType = "venue.service.attend.request.canceled";

		if($tblVenue->is_day_club)
		{
			$isNightVenue = 0;
			$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_VENUE_DAY_TABLE_BOOKING_ATTEND_REQUEST_CANCELED',
								$loginUserDetail->name,
								$tblVenue->venue_name,
								$tblVenueTable->table_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date),
								$this->helper->convertToHM($tblVenueBooking->booking_time)
							);
		}
		else
		{
			$isNightVenue = 1;
			$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_VENUE_NIGHT_TABLE_BOOKING_ATTEND_REQUEST_CANCELED',
								$loginUserDetail->name,
								$tblVenue->venue_name,
								$tblVenueTable->table_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date)
							);
		}

		$actor       = $this->IJUserID;
		$target      = $bookingUserDetail->id;
		$elementID   = $tblVenue->venue_id;
		$elementType = "Venue";
		$cid         = $tblFriendsAttending->friends_attending_id;
		$extraParams = array();
		$extraParams["venueBookingID"]           = $tblVenueBooking->venue_table_booking_id;
		$extraParams["friendAttendingID"]        = $tblFriendsAttending->friends_attending_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblVenueBooking->venue_table_booking_id))
		{
			$this->jsonarray['pushNotificationData']['id']         = $tblVenueBooking->venue_table_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
			$this->jsonarray['pushNotificationData']['to']         = $target;
			$this->jsonarray['pushNotificationData']['message']    = $title;
			$this->jsonarray['pushNotificationData']['type']       = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype'] = '';
		}

		$this->emailHelper->friendRequestJoinTableMail($bookingUserDetail->name,$loginUserDetail->name,$table_name,$venue_name,$booking_date,$booking_time,$isNightVenue,$bookingUserDetail->email);

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"changeFriendAttendingRequestStatus","taskData":{"friendsAttendingID":"number"}}
	 *
	 */
	function changeFriendAttendingRequestStatus()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$friendsAttendingID = IJReq::getTaskData('friendsAttendingID',0, 'int');
		$action             = IJReq::getTaskData('action','', 'string');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblFriendsAttending = JTable::getInstance('FriendsAttending', 'BeseatedTable');
		$tblFriendsAttending->load($friendsAttendingID);

		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblVenueBooking->load($tblFriendsAttending->venue_table_booking_id);

		$tblVenueTable = JTable::getInstance('Table', 'BeseatedTable');
		$tblVenueTable->load($tblFriendsAttending->table_id);

		$tblElement = JTable::getInstance('Venue', 'BeseatedTable');
		$tblElement->load($tblVenueTable->venue_id);

		$booking_owner  = JFactory::getUser();
		$attending_user = JFactory::getUser($tblFriendsAttending->user_id);

		if(!$tblFriendsAttending->friends_attending_id || empty($action))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_FRIENDS_ATTENDING_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(strtolower($action) == 'accept')
		{

			$this->addInvitationForFriendAttending($tblFriendsAttending);

			$tblFriendsAttending->user_status = 2;
			$tblFriendsAttending->booking_user_status = 2;
		}
		else if(strtolower($action) == 'decline')
		{
			$tblFriendsAttending->user_status = 3;
			$tblFriendsAttending->booking_user_status = 3;
		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_FRIENDS_ATTENDING_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!$tblFriendsAttending->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_FRIENDS_ATTENDING_REQUEST_STATUS_NOT_CHANGED'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(strtolower($action) == 'accept')
		{
			$notificationType = "venue.service.attend.request.accepted";
			$statusName = "accepted";



			if($tblElement->is_day_club)
			{
				$isNightVenue = 0;

				$title        = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_VENUE_DAY_TABLE_BOOKING_ATTEND_REQUEST_ACCEPTED',
									$booking_owner->name,
									$tblVenueTable->table_name,
									$tblElement->venue_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$this->helper->convertToHM($tblVenueBooking->booking_time)
								);

				$db_title        = JText::sprintf(
									'COM_BESEATED_DB_NOTIFICATION_VENUE_DAY_TABLE_BOOKING_ATTEND_REQUEST_ACCEPTED',
									$tblVenueTable->table_name,
									$tblElement->venue_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$this->helper->convertToHM($tblVenueBooking->booking_time)
								);
			}
			else
			{
				$isNightVenue = 1;

				$title        = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_VENUE_NIGHT_TABLE_BOOKING_ATTEND_REQUEST_ACCEPTED',
									$booking_owner->name,
									$tblVenueTable->table_name,
									$tblElement->venue_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date)
								);

				$db_title            = JText::sprintf(
									'COM_BESEATED_DB_NOTIFICATION_VENUE_NIGHT_TABLE_BOOKING_ATTEND_REQUEST_ACCEPTED',
									$tblVenueTable->table_name,
									$tblElement->venue_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date)
								);
			}



		}
		else if(strtolower($action) == 'decline')
		{
			$notificationType = "venue.service.attend.request.declined";
			$statusName       = "declined";

			if($tblElement->is_day_club)
			{
				$isNightVenue = 0;
				$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_VENUE_DAY_TABLE_BOOKING_ATTEND_REQUEST_DELCLINED'
									//$booking_owner->name
									/*$tblVenueTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$this->helper->convertToHM($tblVenueBooking->booking_time)*/
								);


			}
			else
			{
				$isNightVenue = 1;
				$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_VENUE_NIGHT_TABLE_BOOKING_ATTEND_REQUEST_DELCLINED'
									//$booking_owner->name
									/*$tblVenueTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date)*/
								);
			}

			$db_title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_VENUE_DAY_TABLE_BOOKING_ATTEND_REQUEST_DELCLINED'
									//$booking_owner->name
									/*$tblVenueTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$this->helper->convertToHM($tblVenueBooking->booking_time)*/
								);

		}

		//echo "<pre>";print_r($title);echo "</pre>";exit;

		$this->emailHelper->friendRequestAcceptedDeclinedJoinTableMail($attending_user->name,$booking_owner->name,$tblVenueTable->table_name,$this->helper->convertDateFormat($tblVenueBooking->booking_date),$this->helper->convertToHM($tblVenueBooking->booking_time),$statusName,$isNightVenue,$attending_user->email);

		$actor       = $this->IJUserID;
		$target      = $attending_user->id;
		$elementID   = $tblElement->venue_id;
		$elementType = "Venue";
		$cid         = $tblFriendsAttending->friends_attending_id;
		$extraParams = array();
		$extraParams["venueBookingID"]           = $tblVenueBooking->venue_table_booking_id;
		$extraParams["friendAttendingID"]        = $tblFriendsAttending->friends_attending_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$db_title,$cid,$extraParams,$tblVenueBooking->venue_table_booking_id))
		{
			$this->jsonarray['pushNotificationData']['id']         = $tblVenueBooking->venue_table_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
			$this->jsonarray['pushNotificationData']['to']         = $target;
			$this->jsonarray['pushNotificationData']['message']    = $title;
			$this->jsonarray['pushNotificationData']['type']       = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype'] = '';
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"getFriendsAttendingVenue","taskData":{"fbIDs":"","venueID":""}}
	 *
	 */
	function getFriendsAttendingVenue()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$fbIDs        = IJReq::getTaskData('fbIDs','', 'string');
		$venueID        = IJReq::getTaskData('venueID',0, 'int');

		$guestUserDetail = $this->helper->guestUserDetail($this->IJUserID);


		if(!$venueID){
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$filterFbFrndEmails   = BeseatedHelper::filterFbIdsToUserIDs($fbIDs);

		/*if(count($filterFbFrndEmails['guest']) == 0){
			$this->jsonarray['code'] = 204;
			return $this->jsonarray;
		}*/

		//$userIDs = implode(",", $filterFbFrndEmails['guest']);
		/*echo "<pre>";
		print_r($userIDs);
		echo "</pre>";
		exit;*/

		$statusArray = array();
		$statusArray[] = $this->helper->getStatusID('confirmed');
		//$statusArray[] = $this->helper->getStatusID('booked');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$firstDate = date('Y-m-d');
		$date = strtotime("+7 day");
		$lastDate =  date('Y-m-d', $date);
		// Create the base select statement.
		$query->select('vb.venue_table_booking_id,vb.user_id,vb.venue_id,vb.table_id,vb.booking_date,vb.booking_time,vb.privacy,vb.passkey,vb.total_guest,vb.male_guest,vb.female_guest,vb.total_hours,vb.total_price,vb.user_status,vb.has_invitation,vb.each_person_pay,vb.is_splitted,vb.splitted_count,vb.remaining_amount,vb.booking_currency_code,vb.booking_currency_sign,vb.is_bill_posted,vb.response_date_time')
			->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vb')
			//->where($db->quoteName('vb.user_id') . ' IN ('.$userIDs.')' )
			->where($db->quoteName('vb.user_id') . ' <> ' . $db->quote($this->IJUserID))
			->where($db->quoteName('vb.user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('vb.deleted_by_user') . ' = ' . $db->quote(0))
			->where($db->quoteName('vb.venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('vb.booking_date') . ' >= ' . $db->quote($firstDate))
			->where($db->quoteName('vb.booking_date') . ' <= ' . $db->quote($lastDate))
			->where($db->quoteName('usr.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('vb.booking_date') . ' ASC');

		$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

		$query->select('vt.table_name,vt.premium_table_id,vt.min_price,vt.capacity,vt.thumb_image,vt.image')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

		$query->select('vpt.premium_table_name')
			->join('LEFT','#__beseated_venue_premium_table AS vpt ON vpt.premium_id=vt.premium_table_id');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar,usr.show_friends_only,usr.show_public_table')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=vb.user_id');

		/*$query->select('users.id')
			->join('LEFT','#__users AS users ON users.id=usr.user_id');*/

		/*echo $query->dump();
		exit;*/

		// Set the query and load the result.
		$db->setQuery($query);
		$resVenueBookings = $db->loadObjectList();

		$resultFriendsVenueBookings = array();
		$resultPublicVenueBookings = array();
		$bookingIDs = array();
		foreach ($resVenueBookings as $key => $booking)
		{
			$alreadyInvited       = $this->helper->checkForAlreadyInvited($booking->venue_id,$booking->venue_table_booking_id);

			if($alreadyInvited)
			{
				continue;
			}

			$tmpBooking = array();
			$tmpBooking['venueBookingID'] = $booking->venue_table_booking_id;
			$tmpBooking['bookingDate']    = $this->helper->convertDateFormat($booking->booking_date);
			$tmpBooking['bookingTime']    = $this->helper->convertToHM($booking->booking_time);
			$tmpBooking['tableName']      = $booking->table_name;
			$tmpBooking['fullName']       = $booking->full_name;
			$tmpBooking['phone']          = $booking->phone;
			$tmpBooking['avatar']         = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$tmpBooking['thumbAvatar']    = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
			$tmpBooking['requestStatus']  = "0";
			$bookingIDs[] = $booking->venue_table_booking_id;

			if(in_array($booking->user_id, $filterFbFrndEmails['guest']))
			{
				$resultFriendsVenueBookings[] = $tmpBooking;
			}
			else if($booking->show_friends_only == 0 && $booking->privacy == 0)
			{
				if($guestUserDetail->show_public_table == '1')
				{
					$resultPublicVenueBookings[] = $tmpBooking;
				}
			}

		}




		/*echo "<pre>";
		print_r($resultFriendsVenueBookings);
		echo "</pre>";
		echo "<pre>";
		print_r($resultFriendsVenueBookings);
		echo "</pre>";*/

		if(count($resultFriendsVenueBookings) == 0 && count($resultPublicVenueBookings) == 0)
		{
			$this->jsonarray['code'] = 204;
			return $this->jsonarray;
		}

		$resStatus = $this->getFriendsAttendingStatus($bookingIDs);
		$friendsBooking = array();

		foreach ($resultFriendsVenueBookings as $key => $tmpBooking)
		{
			if(isset($resStatus[$tmpBooking['venueBookingID']]))
			{
				$tmpBooking['requestStatus']  = $resStatus[$tmpBooking['venueBookingID']];
			}
			$friendsBooking[] = $tmpBooking;
		}

		$publicBooking = array();
		foreach ($resultPublicVenueBookings as $key => $tmpBooking)
		{
			if(isset($resStatus[$tmpBooking['venueBookingID']]))
			{

				$tmpBooking['requestStatus']  = $resStatus[$tmpBooking['venueBookingID']];
			}
			$publicBooking[] = $tmpBooking;
		}

		$this->jsonarray['code']=200;
		$this->jsonarray['friendsBooking'] = $friendsBooking;
		$this->jsonarray['publicBooking'] = $publicBooking;

		return $this->jsonarray;
	}

	function getFriendsAttendingStatus($bookingIDs,$userType = 'user'){
		$query = $this->db->getQuery(true);
		$query->select('venue_table_booking_id,user_status,booking_user_status')
			->from($this->db->quoteName('#__beseated_venue_friends_attending'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($this->IJUserID))
			->where($this->db->quoteName('venue_table_booking_id') . ' IN ('.implode(",", $bookingIDs).')');
		$this->db->setQuery($query);
		$resFA = $this->db->loadObjectList();

		$responseStatus = array();
		foreach ($resFA as $key => $fa)
		{
			$responseStatus[$fa->venue_table_booking_id] = $fa->user_status;
		}

		/*echo "<pre>";
		print_r($responseStatus);
		echo "</pre>";
		exit;*/

		return $responseStatus;
	}

	function getSingleFriendsAttendingStatus($bookingID){
		$query = $this->db->getQuery(true);
		$query->select('venue_table_booking_id,user_status,booking_user_status')
			->from($this->db->quoteName('#__beseated_venue_friends_attending'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($this->IJUserID))
			->where($this->db->quoteName('venue_table_booking_id') . ' = ' . $this->db->quote($bookingID));
		$this->db->setQuery($query);
		$friendAttending = $this->db->loadObject();

		return $friendAttending;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"guest","extTask":"testing"}
	 *
	 */
	function testing($value='')
	{

		$body = 'html body';


		require_once("helper/tcpdf/tcpdf.php");
		$pdf = new TCPDF();
		// add a page
		$pdf->AddPage();

		//$pdf->SetFont("", "b", 16);
		$pdf->writeHTML($body, true, false, true, false);
		$storePDF = JPATH_BASE.'/images/beseated/Ticket/test.pdf';
		$pdf->Output($storePDF, 'F');


		// Initialise variables.
		$app     = JFactory::getApplication();
		$config  = JFactory::getConfig();

		// Clean the email data.
		$sender  = JMailHelper::cleanAddress($sender);
		$subject = JMailHelper::cleanSubject("test sub1");
		$body    = JMailHelper::cleanBody("test body1");

		$mailfrom = $app->get('mailfrom');
		$fromname = $app->get('fromname');
		$sitename = $app->get('sitename');

		$email = 'web-XIVoCo@mail-tester.com';

		$name    = "from_name";
		$email   = JStringPunycode::emailToPunycode($email);

		$mail = JFactory::getMailer();
		$mail->addRecipient($email);
		$mail->addReplyTo($email);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($sitename . ': ' . $subject);
		//$mail->isHTML(true);
		//$mail->Encoding = 'base64';

		$mail->setBody($body);
		$return = $mail->Send();

		echo "<pre>";print_r($return);echo "</pre>";exit;
		//$mail = JFactory::getMailer();
		//$mail->sendMail("jamal@tasolglobal.com","jamal", "jamaltasol@gmail.com", "test","test body");

		//require("class.phpmailer.php");

		/*require 'PHPMailerAutoload.php';
		require 'class.phpmailer.php';
		require 'class.smtp.php';

		$mail = new PHPMailer();
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host       = 'c13803.sgvps.net';                 // Specify main and backup SMTP servers
		$mail->SMTPAuth   = true;                            // Enable SMTP authentication
		$mail->Username   = 'jamal@tasolglobal.com';      // SMTP username
		$mail->Password   = 'o5ELGZ!v6_Kt';                      // SMTP password
		$mail->SMTPSecure = 'ssl';
		$mail->Port       = '465';                                // Enable encryption, 'ssl' also accepted
		$mail->From       = 'from@example.com';
		$mail->FromName   = 'jamal';
		$mail->addAddress("bcted.developer@gmail.com", 'Joe User');     // Add a recipient

		// Set email format to HTML
		$mail->Subject = "test sub";
		$mail->Body    = "test body";

		echo "<pre>";print_r($mail->send());echo "</pre>";exit;

		if(!$mail->send())
		{
		    echo 'Message could not be sent.';
		    echo 'Mailer Error: ' . $mail->ErrorInfo;
		}
*/
	}

	function getRatings()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$elementType = IJReq::getTaskData('elementType','','string');
		$elementID   = IJReq::getTaskData('elementID', 0, 'int');
		$allElement  = array('venue','protection','yacht','chauffeur','private jet');
		$elementType = strtolower($elementType);

		if(!in_array($elementType, $allElement))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_RATING_ELEMENT_TYPE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.avg_rating,a.created,a.rating_comment,a.user_id,b.name,c.avatar,c.thumb_avatar')
			->from($db->quoteName('#__beseated_rating') . ' AS a')
			->where($db->quoteName('b.block') . ' = ' . $db->quote('0'))
			->where($db->quoteName('a.published') . ' = ' . $db->quote('1'))
			->where($db->quoteName('a.element_type') . ' = ' . $db->quote($elementType))
			->where($db->quoteName('a.element_id') . ' = ' . $db->quote($elementID))
			->join('INNER', '#__users AS b ON b.id=a.user_id')
			->join('INNER', '#__beseated_user_profile AS c ON c.user_id=b.id')
			->order($db->quoteName('a.created') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$userRatingsDetail = $db->loadObjectList();

		if(count($userRatingsDetail) == 0)
		{
			$this->jsonarray['code'] = 204;
			return $this->jsonarray;
		}

		foreach ($userRatingsDetail as $key => $ratingDetail)
		{
			$this->jsonarray['ratings'][$key]['user_id']      = $ratingDetail->user_id;
			$this->jsonarray['ratings'][$key]['user_name']    = $ratingDetail->name;
			$this->jsonarray['ratings'][$key]['avatar']       = ($ratingDetail->avatar) ? $this->helper->getUserAvatar($ratingDetail->avatar) : "";
			$this->jsonarray['ratings'][$key]['thumb_avatar'] = ($ratingDetail->thumb_avatar) ? $this->helper->getUserAvatar($ratingDetail->thumb_avatar): "";;
			$this->jsonarray['ratings'][$key]['rating']       = $ratingDetail->avg_rating;
			$this->jsonarray['ratings'][$key]['comment']      = $ratingDetail->rating_comment;
			$this->jsonarray['ratings'][$key]['date']         = date("d-m-Y", strtotime($ratingDetail->created));
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;


	}

	function cancelShareInvitation()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingID   = IJReq::getTaskData('bookingID', 0, 'int');
		$bookingType = IJReq::getTaskData('bookingType', '', 'string');
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$lowerBookingType = strtolower($bookingType);
		//require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

		$bookingTypeValue          = $bookingType;
		$bookingElementIDField     = strtolower($bookingType.'_id');
		$bookingUserStatusField    = 'user_status';
		$bookingElementStatusField = strtolower($bookingType.'_status');

		if(ucfirst($bookingType) == "Venue")
		{
			$lowerBookingType    = "venue_table";
			$bookingTypeIDField  = strtolower($bookingType.'_table_booking_id');
			$elementName         = 'venueTableBookingID';
			$subElement          = 'Table';
			$subElementID        = 'table_id';
			$subElementNameField = 'table_name';
			$notificationType    = strtolower($bookingType).'.share.invitation.cancel';
		}
		elseif(ucfirst($bookingType) == "Yacht")
		{
			$bookingTypeIDField  = strtolower($bookingType.'_booking_id');
			$elementName         = 'yachtBookingID';
			$subElement          = $bookingType.'Service';
			$subElementID        = 'service_id';
			$subElementNameField = 'service_name';
			$notificationType    = strtolower($bookingType).'.share.invitation.cancel';
		}
		elseif(ucfirst($bookingType) == "Chauffeur")
		{
			$bookingTypeIDField  = strtolower($bookingType.'_booking_id');
			$elementName         = 'chauffeurBookingID';
			$subElement          = $bookingType.'Service';
			$subElementID        = 'service_id';
			$subElementNameField = 'service_name';
			$notificationType    = strtolower($bookingType).'.share.invitation.cancel';
		}
		elseif(ucfirst($bookingType) == "Protection")
		{
			$bookingTypeIDField  = strtolower($bookingType.'_booking_id');
			$elementName         = 'protectionBookingID';
			$subElement          = $bookingType.'Service';
			$subElementID        = 'service_id';
			$subElementNameField = 'service_name';
			$notificationType    = strtolower($bookingType).'.share.invitation.cancel';
		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_BOOKING_TYPE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblElementBooking = JTable::getInstance($bookingTypeValue.'Booking', 'BeseatedTable');
		$tblElementBooking->load($bookingID);
		$tblElement        = JTable::getInstance($bookingTypeValue, 'BeseatedTable');
		$tblElement->load($tblElementBooking->$bookingElementIDField);

		if(!$tblElementBooking->$bookingTypeIDField)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$statusID = $this->helper->getStatusID('decline');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		if(ucfirst($bookingType) == "Venue")
		{
			$query->select('b.'.$lowerBookingType.'_booking_id,b.user_id as bookingOwnerID,a.'.$lowerBookingType.'_booking_split_id as elementSplitID,a.'.$subElementID);
		}
		else
		{
			$query->select('c.user_id,b.user_id as bookingOwnerID,b.'.$lowerBookingType.'_booking_id,a.'.$lowerBookingType.'_booking_split_id as elementSplitID,a.'.$subElementID);
		}

		$query->from($db->quoteName('#__beseated_'.$lowerBookingType.'_booking_split'). ' AS a')
			->where($db->quoteName('a.'.$lowerBookingType.'_booking_id') . ' = ' . $db->quote($bookingID))
			->where($db->quoteName('a.user_id') . ' = ' . $db->quote($this->IJUserID))
			->join('INNER', '#__beseated_'.$lowerBookingType.'_booking AS b ON b.'.$lowerBookingType.'_booking_id=a.'.$lowerBookingType.'_booking_id');

		if(ucfirst($bookingType) == "Venue")
		{
			$query->join('INNER', '#__beseated_'.$lowerBookingType.' AS c ON c.table_id = a.table_id');
		}
		else
		{
			$query->join('INNER', '#__beseated_'.$lowerBookingType.' AS c ON c.'.$lowerBookingType.'_id=a.'.$lowerBookingType.'_id');
		}

		// Set the query and load the result.
		$db->setQuery($query);

		$splitDetail = $db->loadObject();

		if(!$splitDetail->elementSplitID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_INVITATION_CANCEL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblService = JTable::getInstance($subElement, 'BeseatedTable');
		$tblService->load($splitDetail->$subElementID);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_'.$lowerBookingType.'_booking_split'))
			->set($db->quoteName('split_payment_status') . ' = ' . $db->quote($statusID))
			->where($db->quoteName(''.$lowerBookingType.'_booking_split_id') . ' = ' . $db->quote($splitDetail->elementSplitID))
			->where($db->quoteName(''.$lowerBookingType.'_booking_id') . ' = ' . $db->quote($bookingID))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

		// Set the query and execute the update.
		$db->setQuery($query);

	    $db->execute();

		$guestUserDetail = $this->helper->guestUserDetail($this->IJUserID);


		if(isset($tblElement->is_day_club) && $tblElement->is_day_club == 0)
		{
			$title = JText::sprintf(
				'COM_IJOOMERADV_BESEATED_CANCEL_SHARE_INVITATON_NIGHT_BOOKING_BY_INVITEE_FOR_'.strtoupper($bookingType),
				$guestUserDetail->full_name,
				$tblService->$subElementNameField,
				$this->helper->convertDateFormat($tblElementBooking->booking_date)
				);
		}
		else
		{
			$title = JText::sprintf(
				'COM_IJOOMERADV_BESEATED_CANCEL_SHARE_INVITATON_BOOKING_BY_INVITEE_FOR_'.strtoupper($bookingType),
				$guestUserDetail->full_name,
				$tblService->$subElementNameField,
				$this->helper->convertDateFormat($tblElementBooking->booking_date),
				$this->helper->convertToHM($tblElementBooking->booking_time)
				);
		}

		$actor            = $this->IJUserID;
		$target           = $splitDetail->bookingOwnerID;
		$elementID        = $tblElementBooking->$bookingElementIDField;
		$elementType      = $bookingTypeValue;
		$cid              = $splitDetail->elementSplitID;
		$extraParams      = array();
		$extraParams[$elementName]   = $tblElementBooking->$bookingTypeIDField;
		$extraParams['invitationID'] = $splitDetail->elementSplitID;

		$this->deleteNotifOfCanceledInvitee($bookingID,$splitDetail->elementSplitID,$bookingType,$elementName);

		$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblElementBooking->$bookingTypeIDField);

		$this->jsonarray['pushNotificationData']['id']         = $tblElementBooking->$bookingElementIDField;
		$this->jsonarray['pushNotificationData']['elementType'] = ucfirst($elementType);
		$this->jsonarray['pushNotificationData']['to']         = $splitDetail->bookingOwnerID;
		$this->jsonarray['pushNotificationData']['message']    = $title;
		/*$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_SHARE_BOOKING_REQUEST_CANCELED_BY_USER');*/
		$this->jsonarray['pushNotificationData']['type']       = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype'] = '';

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	function deleteNotifOfCanceledInvitee($bookingID,$invitationID,$bookingType,$elementName)
	{
		//echo "<pre/>";print_r($bookingType.'--'.$elementName);exit;

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->select('extra_pramas,notification_id')
			->from($db->quoteName('#__beseated_notification'))
			->where($db->quoteName('notification_type') . ' = ' . $db->quote(strtolower($bookingType).'.share.invitation.request'))
			->where($db->quoteName('target') . ' = ' . $db->quote((int) $this->IJUserID))
			->where($db->quoteName('cid') . ' = ' . $db->quote((int) $invitationID));

		// Set the query and execute the delete.
		$db->setQuery($query);

		$notif_data = $db->loadObjectList();

		foreach ($notif_data as $key => $value)
		{
			$extra_pramas = $value->extra_pramas;

			if(json_decode($extra_pramas)->$elementName == $bookingID)
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();

				$query = $db->getQuery(true);

				// Create the base delete statement.
				$query->delete()
					->from($db->quoteName('#__beseated_notification'))
					->where($db->quoteName('notification_id') . ' = ' . $db->quote((int) $value->notification_id));

				// Set the query and execute the delete.
				$db->setQuery($query);

				$db->execute();
			}

		}

		return true;
	}

	function concierge()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

		$address   = IJReq::getTaskData('address', '', 'string');

		$cityName = BeseatedHelper::getAddressDetail($address);

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_concierge'))
			->where($db->quoteName('city') . ' = ' . $db->quote($cityName));

		// Set the query and load the result.
		$db->setQuery($query);
		$cityDetail = $db->loadObject();

		if(empty($cityDetail))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_ADRESS'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}


		if(!empty($cityDetail))
		{
			$this->jsonarray['concierge']['phoneNo']      = $cityDetail->phone_no;
		}
		else
		{
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($db->quoteName('#__beseated_concierge'))
				->where($db->quoteName('is_default') . ' = ' . $db->quote('1'));

			// Set the query and load the result.
			$db->setQuery($query);
			$cityDetail = $db->loadObject();

			$this->jsonarray['concierge']['phoneNo']      = $cityDetail->phone_no;
		}


        $this->jsonarray['code'] = 200;
		return $this->jsonarray;

	}

	function beseatedRefer()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse( 704 );
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$emails = IJReq::getTaskData('emails','','string');
		$loginUser = JFactory::getUser();

		//$this->helper->filterOnlyGuestEmail($emails);

		if(empty($emails))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BCTED_USER_INVALID_USERLIST_DATA'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$bctRegiEmails       = BeseatedHelper::filterEmails($emails);

		//echo "<pre/>";print_r($bctRegiEmails);exit;

		$emailsArray = explode(",", $emails);
		$filterEmails = array();

		foreach ($emailsArray as $key => $singleEmail)
		{
			if(in_array($singleEmail, $bctRegiEmails['allRegEmail']))
			{
				if(in_array($singleEmail, $bctRegiEmails['company'])){ continue; }
				if(in_array($singleEmail, $bctRegiEmails['venue'])){ continue; }
				if(in_array($singleEmail, $bctRegiEmails['guest']) || $this->my->email == $singleEmail){ continue; }
			}

			$filterEmails[] = $singleEmail;
		}

		if(count($filterEmails)==0)
		{
			IJReq::setResponseCode(300);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_REFER_USERS_ALREDY_REGISTERED'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$notRegisEmailsArray = $filterEmails;

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('email')
			->from($db->quoteName('#__users'));

		// Set the query and load the result.
		$db->setQuery($query);

		$registeredEmails = $db->loadColumn();

		$query2 = $db->getQuery(true);

		// Create the base select statement.
		$query2->select('refer_email')
			->from($db->quoteName('#__beseated_user_refer'))
			->where($db->quoteName('userid') . ' = ' . $db->quote($this->IJUserID));

		// Set the query and load the result.
		$db->setQuery($query2);

		$alreadyReferEmails = $db->loadColumn();

		$emailsAfterRegi = array();

		foreach ($notRegisEmailsArray as $key => $singleEmail)
		{
			if(!in_array($singleEmail, $registeredEmails))
			{
				$emailsAfterRegi[] = $singleEmail;
			}
		}

		$referEmails = array();
		foreach ($emailsAfterRegi as $key => $singleEmail)
		{
			if(!in_array($singleEmail, $alreadyReferEmails))
			{
				$referEmails[] = $singleEmail;
			}
		}

		//$referEmails = array_diff($emailsArray, $registeredEmails);

		//$referEmails = array_diff($referEmails, $alreadyReferEmails);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		foreach ($referEmails as $key => $value)
		{
			$tblRefer = JTable::getInstance('Refer', 'BeseatedTable');
			$tblRefer->load(0);
			$referData['userid']        = $this->IJUserID;
			$referData['refer_email']   = $value;
			$referData['is_registered'] = 0;
			$referData['ref_user_id']   = 0;
			$referData['is_fp_done']    = 0;
			//$referData['fp_date']       = $this->IJUserID;
			$referData['created']       = date('Y-m-d H:i:s');
			$referData['time_stamp']    = time();

			$tblRefer->bind($referData);

			if($tblRefer->store())
			{
				$this->emailHelper->beseatedInvitationMail($value,$loginUser->name);
			}
		}

		$this->jsonarray['code'] = 200;

		return $this->jsonarray;

	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"event","extTask":"confirmTicket","taskData":{"eventID":"58","ticketQty":"2"}}
	 */
	function refundElementBooking()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$elementBookingID = IJReq::getTaskData('elementBookingID',0,'int');
		$elementType   = IJReq::getTaskData('elementType','','string');

		$allElement  = array('venue','protection','yacht','chauffeur','private jet');
		$elementType = strtolower($elementType);

		if(!in_array($elementType, $allElement))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_REFUND_ELEMENT_TYPE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!$elementBookingID || !$elementType)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_REFUND_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		if($elementType == 'yacht')
		{
			$tblElementBooking = 'YachtBooking';
			$elementBookingID  = 'yacht_booking_id';
		}
		elseif($elementType == 'chauffeur')
		{
			$tblElementBooking = 'ChauffeurBooking';
			$elementBookingID  = 'chauffeur_booking_id';
		}
		elseif($elementType == 'private jet')
		{
			$tblElementBooking = 'PrivateJetBooking';
			$elementBookingID  = 'private_jet_booking_id';
		}
		elseif($elementType == 'protection')
		{
			$tblElementBooking = 'ProtectionBooking';
			$elementBookingID  = 'protection_booking_id';
		}
		elseif($elementType == 'venue')
		{
			$tblElementBooking = 'venueBooking';
			$elementBookingID  = 'venue_table_booking_id';
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$tblElementBooking        = JTable::getInstance($tblElementBooking, 'BeseatedTable');
		$tblElementBooking->load($elementBookingID);

		if(!$tblElementBooking->$elementBookingID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_ELEMENT_BOOKING_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}


		$this->jsonarray['code']           = 200;
		$this->jsonarray['eventBookingID'] = $tblTicketBooking->ticket_booking_id;
		$this->jsonarray['paymentURL']     = JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$tblTicketBooking->ticket_booking_id.'&booking_type=event';
		return $this->jsonarray;
	}

	function hasVenueBottle($venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('COUNT(bottle_id) bottleCount')
			->from($db->quoteName('#__beseated_venue_bottle'))
			->where($db->quoteName('published') . ' = ' . $db->quote('1'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID));

		// Set the query and load the result.
		$db->setQuery($query);
		$bottleCount = $db->loadResult();

		return $bottleCount;

	}

	function getSplitedUserCount($bookingType,$element_booking_id)
	{
		$newbookingType = strtolower($bookingType);

		if($bookingType == 'Venue')
		{
			$bookingType = "venue_table";
		}

		$lowerBookingType = strtolower($bookingType);

		// Initialiase variables.
		$db    = JFactory::getDbo();

		$querySplit = $db->getQuery(true);
		$querySplit->select('COUNT(split.'.$lowerBookingType.'_booking_split_id) as splitedUser')
					->from($db->quoteName('#__beseated_'.$lowerBookingType.'_booking_split','split'))
					->where($db->quoteName('split.'.$lowerBookingType.'_booking_id') . ' = ' . $db->quote($element_booking_id));

		$db->setQuery($querySplit);

		$splitedUserCount = $db->loadResult();

		$queryInvited = $db->getQuery(true);
		$queryInvited->select('COUNT(invitation_id) as invitedUser')
					->from($db->quoteName('#__beseated_invitation'))
					->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($element_booking_id))
					->where($db->quoteName('element_type') . ' = ' . $db->quote($newbookingType));

		$db->setQuery($queryInvited);

		$invitedUserDetail = $db->loadResult();

		$invitedSplittedUserDetail = $splitedUserCount + $invitedUserDetail;

		//echo "<pre/>";print_r($invitedSplittedUserDetail);exit;

		if($splitedUserCount == 0 && $invitedUserDetail >= 1)
		{
			$invitedSplittedUserDetail = $invitedSplittedUserDetail + 1;
		}

		//echo "<pre/>";print_r($invitedSplittedUserDetail);exit;

		return $invitedSplittedUserDetail;

	}


	function getBookedEventInvitations()
	{
		$db    = JFactory::getDbo();

		$eventStatusArray[] = $this->helper->getStatusID('accept');

		// Start of Get Event RSVP
		$eventInvitesql = $db->getQuery(true);
		$eventInvitesql->select('tbi.invite_id,tbi.ticket_booking_id,tbi.ticket_booking_detail_id,tbi.ticket_id,tbi.event_id,tbi.user_id,tbi.invited_user_id,tbi.email,tbi.fbid,tbi.invited_user_status')
			->from($db->quoteName('#__beseated_event_ticket_booking_invite','tbi'))
			->where($db->quoteName('tbi.invited_user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('tbi.deleted_by_user') . ' = ' . $db->quote('0'))
		    ->where($db->quoteName('tbi.invited_user_status') . ' IN ('.implode(",", $eventStatusArray).')')
			->where($db->quoteName('tbi.user_id') . ' <> ' . $db->quote($this->IJUserID));

		$eventInvitesql->select('e.event_name,e.event_desc,e.image as event_image,e.thumb_image,e.location,e.city,e.event_date,e.event_time,e.latitude,e.longitude')
			->join('INNER','#__beseated_event AS e ON e.event_id=tbi.event_id')
			//->where('STR_TO_DATE(CONCAT('.$this->db->quoteName('e.event_date').', " ", '.$this->db->quoteName('e.event_time').'),"%Y-%m-%d %H:%i:%s")' . ' >= ' . $this->db->quote(date('Y-m-d H:i:s')));
			->where('CONCAT('.$db->quoteName('e.event_date').', " ", '.$db->quoteName('e.event_time') . ') >= ' . $db->quote(date('Y-m-d H:i:s')));

		$eventInvitesql->select('etb.ticket_price,etb.booking_currency_code,etb.booking_currency_sign,etb.total_price')
			->join('INNER','#__beseated_event_ticket_booking AS etb ON etb.ticket_booking_id=tbi.ticket_booking_id');

		$eventInvitesql->select('timg.thumb_image AS ticket_thumb_image,timg.image AS ticket_image')
			->join('INNER','#__beseated_element_images AS timg ON timg.image_id=tbi.ticket_id');

		$eventInvitesql->select('bu.full_name,bu.avatar,bu.thumb_avatar')
			->join('INNER','#__beseated_user_profile AS bu ON bu.user_id=tbi.user_id');

		$eventInvitesql->order('CONCAT('.$this->db->quoteName('e.event_date').', " ", '.$this->db->quoteName('e.event_time').')' . ' ASC');

		$db->setQuery($eventInvitesql);
		$resEventInvitations = $db->loadObjectList();

		foreach ($resEventInvitations as $key => $eventInvitations)
		{
			$eventInvitations->bookedType = 'invitation';
		}

		return $resEventInvitations;
	}

	function getBookedVenueShareInvitations($bookingID = null)
	{
		$statusArray    = array();
		$statusArray[] = $this->helper->getStatusID('confirmed');

		$cancelStatusArray    = array();
		$cancelStatusArray[] = $this->helper->getStatusID('canceled');

		$invitedUserStatus   = array();
		$invitedUserStatus[] = $this->helper->getStatusID('going');

		$splitedUserStatus   = array();
		$splitedUserStatus[] = $this->helper->getStatusID('paid');
		//$splitedUserStatus[] = $this->helper->getStatusID('confirmed');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('vb.venue_table_booking_id,vb.user_id,vb.venue_id,vb.table_id,vb.booking_date,vb.booking_time,vb.privacy,vb.passkey,vb.total_guest,vb.male_guest,vb.female_guest,vb.total_hours,vb.total_price,vb.user_status,vb.has_invitation,vb.each_person_pay,vb.is_splitted,vb.splitted_count,vb.remaining_amount,vb.booking_currency_code,vb.booking_currency_sign,vb.pay_by_cash_status,vb.is_bill_posted,vb.response_date_time,vb.has_bottle,vb.total_split_count,vb.hasCMSBooking,vb.is_noshow')
			->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vb')
			->where($db->quoteName('vb.user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('vb.deleted_by_user') . ' = ' . $db->quote(0));

			if($bookingID)
			{
				$query->where($db->quoteName('vb.venue_table_booking_id') . ' = ' . $db->quote($bookingID));
			}

			$query->order($db->quoteName('vb.booking_date') . ' ASC');

		$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club,v.deposit_per,v.active_payments,v.refund_policy')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

		$query->select('invt.invitation_id')
		    ->where($db->quoteName('invt.element_type') . ' = ' . $db->quote('venue'))
			->where($db->quoteName('invt.user_id') . ' = ' . $db->quote($this->IJUserID));

			if(!$bookingID)
			{
				$query->where($db->quoteName('invt.user_action') . ' IN ('.implode(",", $invitedUserStatus).')');
			}

			$query->join('LEFT','#__beseated_invitation AS invt ON invt.element_booking_id=vb.venue_table_booking_id');

		$query->select('vt.table_name,vt.premium_table_id,vt.min_price,vt.capacity,vt.thumb_image,vt.image')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

		$query->select('vpt.premium_table_name')
			->join('LEFT','#__beseated_venue_premium_table AS vpt ON vpt.premium_id=vt.premium_table_id');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=vb.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resVenueInvitationBookings = $db->loadObjectList();

		foreach ($resVenueInvitationBookings as $key => $resVenueInvitationBooking)
		{
			$resVenueInvitationBooking->bookedType = 'invitation';
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('vb.venue_table_booking_id,vb.user_id,vb.venue_id,vb.table_id,vb.booking_date,vb.booking_time,vb.privacy,vb.passkey,vb.total_guest,vb.male_guest,vb.female_guest,vb.total_hours,vb.total_price,vb.user_status,vb.has_invitation,vb.each_person_pay,vb.is_splitted,vb.splitted_count,vb.remaining_amount,vb.booking_currency_code,vb.booking_currency_sign,vb.pay_by_cash_status,vb.is_bill_posted,vb.response_date_time,vb.has_bottle,vb.total_split_count,vb.hasCMSBooking,vb.is_noshow')
			->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vb')
			->where($db->quoteName('vb.deleted_by_user') . ' = ' . $db->quote(0))
			->where($db->quoteName('vb.user_status') . 'NOT IN ('.implode(",", $cancelStatusArray).')');

			if($bookingID)
			{
				$query->where($db->quoteName('vb.venue_table_booking_id') . ' = ' . $db->quote($bookingID));
			}

			$query->order($db->quoteName('vb.booking_date') . ' ASC');

		$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club,v.deposit_per,v.active_payments,v.refund_policy')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

		$query->select('split.venue_table_booking_split_id,split.user_id as splited_user_id,split.paid_by_owner,split.split_payment_status')
			->where($db->quoteName('split.user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('split.is_owner') . ' = ' . $db->quote('0'));

			if(!$bookingID)
            {
            	$query->where($db->quoteName('split.split_payment_status') . ' IN ('.implode(",", $splitedUserStatus).')');
            }

			$query->join('LEFT','#__beseated_venue_table_booking_split AS split ON split.venue_table_booking_id=vb.venue_table_booking_id');

		$query->select('vt.table_name,vt.premium_table_id,vt.min_price,vt.capacity,vt.thumb_image,vt.image')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

		$query->select('vpt.premium_table_name')
			->join('LEFT','#__beseated_venue_premium_table AS vpt ON vpt.premium_id=vt.premium_table_id');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=vb.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resVenueShareBookings = $db->loadObjectList();

		foreach ($resVenueShareBookings as $key => $resVenueShareBooking)
		{
			if($resVenueShareBooking->splited_user_id == $this->IJUserID && $resVenueShareBooking->split_payment_status == 7 &&  $resVenueShareBooking->paid_by_owner==1)
			{
				$resVenueShareBooking->paidByOwner  = 1;
			}

			$resVenueShareBooking->bookedType = 'share';
		}

		$resVenueShareInvitationBookings = (object) array_merge((array) $resVenueShareBookings, (array) $resVenueInvitationBookings);

		return $resVenueShareInvitationBookings;

	}

	function getBookedProtectionShareInvitations($bookingID = null)
	{
		$statusArray    = array();
		$statusArray[] = $this->helper->getStatusID('booked');

		$cancelStatusArray    = array();
		$cancelStatusArray[] = $this->helper->getStatusID('canceled');

		$invitedUserStatus   = array();
		$invitedUserStatus[] = $this->helper->getStatusID('going');

		$splitedUserStatus   = array();
		$splitedUserStatus[] = $this->helper->getStatusID('paid');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('pb.protection_booking_id,pb.protection_id,pb.service_id,pb.user_id,pb.booking_date,pb.booking_time,pb.meetup_location,pb.total_guard,pb.total_hours,pb.price_per_hours,pb.total_price,pb.user_status,pb.is_splitted,pb.has_invitation,pb.each_person_pay,pb.splitted_count,pb.remaining_amount,pb.total_split_count,pb.is_noshow')
			->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
			->where($db->quoteName('pb.deleted_by_user') . ' = ' . $db->quote(0))
			->where($db->quoteName('pb.user_status') . ' IN ('.implode(",", $statusArray).')');

			if($bookingID)
			{
				$query->where($db->quoteName('pb.protection_booking_id') . ' = ' . $db->quote($bookingID));
			}

			$query->order($db->quoteName('pb.booking_date') . ' ASC');

		$query->select('p.protection_name,p.location,p.city,p.currency_code,p.refund_policy,p.deposit_per')
			->join('LEFT','#__beseated_protection AS p ON p.protection_id=pb.protection_id');

		$query->select('invt.invitation_id')
		    ->where($db->quoteName('invt.element_type') . ' = ' . $db->quote('protection'))
			->where($db->quoteName('invt.user_id') . ' = ' . $db->quote($this->IJUserID));

			if(!$bookingID)
			{
				$query->where($db->quoteName('invt.user_action') . ' IN ('.implode(",", $invitedUserStatus).')');
			}

			$query->join('LEFT','#__beseated_invitation AS invt ON invt.element_booking_id=pb.protection_booking_id');

		$query->select('ps.service_name,ps.thumb_image,ps.image')
			->join('LEFT','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=pb.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resProtectionInvitationBookings = $db->loadObjectList();

		foreach ($resProtectionInvitationBookings as $key => $resProtectionInvitationBooking)
		{
			$resProtectionInvitationBooking->bookedType = 'invitation';
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('pb.protection_booking_id,pb.protection_id,pb.service_id,pb.user_id,pb.booking_date,pb.booking_time,pb.meetup_location,pb.total_guard,pb.total_hours,pb.price_per_hours,pb.total_price,pb.user_status,pb.is_splitted,pb.has_invitation,pb.each_person_pay,pb.splitted_count,pb.remaining_amount,pb.total_split_count,pb.is_noshow')
			->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
			->where($db->quoteName('pb.deleted_by_user') . ' = ' . $db->quote(0))
			->where($db->quoteName('pb.user_status') . 'NOT IN ('.implode(",", $cancelStatusArray).')');

			if($bookingID)
			{
				$query->where($db->quoteName('pb.protection_booking_id') . ' = ' . $db->quote($bookingID));
			}

			$query->order($db->quoteName('pb.booking_date') . ' ASC');

		$query->select('p.protection_name,p.location,p.city,p.currency_code,p.refund_policy,p.deposit_per')
			->join('LEFT','#__beseated_protection AS p ON p.protection_id=pb.protection_id');

		$query->select('split.protection_booking_split_id,split.user_id as splited_user_id,split.paid_by_owner,split.split_payment_status')
            ->where($db->quoteName('split.user_id') . ' = ' . $db->quote($this->IJUserID));

            if(!$bookingID)
            {
            	$query->where($db->quoteName('split.split_payment_status') . ' IN ('.implode(",", $splitedUserStatus).')');
            }
           // ->where($db->quoteName('split.split_payment_status') . ' IN ('.implode(",", $splitedUserStatus).')')
        $query->where($db->quoteName('split.is_owner') . ' = ' . $db->quote('0'))
            ->join('LEFT','#__beseated_protection_booking_split AS split ON split.protection_booking_id=pb.protection_booking_id');

		$query->select('ps.service_name,ps.thumb_image,ps.image')
			->join('LEFT','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=pb.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resProtectionShareBookings = $db->loadObjectList();

		//echo "<pre/>";print_r($resProtectionShareBookings);exit;

		foreach ($resProtectionShareBookings as $key => $resProtectionShareBooking)
		{
			if($resProtectionShareBooking->splited_user_id == $this->IJUserID && $resProtectionShareBooking->split_payment_status == 7 &&  $resProtectionShareBooking->paid_by_owner==1)
			{
				$resProtectionShareBooking->paidByOwner  = 1;
			}
			$resProtectionShareBooking->bookedType = 'share';
		}

		$resProtectionShareInvitationBookings = (object) array_merge((array) $resProtectionShareBookings, (array) $resProtectionInvitationBookings);

		return $resProtectionShareInvitationBookings;


	}

	function getBookedYachtShareInvitations($bookingID = null)
	{
		$statusArray    = array();
		$statusArray[] = $this->helper->getStatusID('booked');

		$cancelStatusArray    = array();
		$cancelStatusArray[] = $this->helper->getStatusID('canceled');

		$invitedUserStatus   = array();
		$invitedUserStatus[] = $this->helper->getStatusID('going');

		$splitedUserStatus   = array();
		$splitedUserStatus[] = $this->helper->getStatusID('paid');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('yb.*')
			->from($db->quoteName('#__beseated_yacht_booking') . ' AS yb')
			->where($db->quoteName('yb.deleted_by_user') . ' = ' . $db->quote(0))
			->where($db->quoteName('yb.user_status') . ' IN ('.implode(",", $statusArray).')');

			if($bookingID)
			{
				$query->where($db->quoteName('yb.yacht_booking_id') . ' = ' . $db->quote($bookingID));
			}

			$query->order($db->quoteName('yb.booking_date') . ' ASC');

		$query->select('y.yacht_name,y.location,y.city,y.deposit_per,y.refund_policy')
			->join('LEFT','#__beseated_yacht AS y ON y.yacht_id=yb.yacht_id');

		$query->select('invt.invitation_id')
		    ->where($db->quoteName('invt.element_type') . ' = ' . $db->quote('yacht'))
			->where($db->quoteName('invt.user_id') . ' = ' . $db->quote($this->IJUserID));

			if(!$bookingID)
			{
				$query->where($db->quoteName('invt.user_action') . ' IN ('.implode(",", $invitedUserStatus).')');
			}

			$query->join('LEFT','#__beseated_invitation AS invt ON invt.element_booking_id=yb.yacht_booking_id');

		$query->select('ys.service_name,ys.service_type,ys.dock,ys.thumb_image,ys.image')
			->join('LEFT','#__beseated_yacht_services AS ys ON ys.service_id=yb.service_id');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=yb.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resYachtInvitationBookings = $db->loadObjectList();

		foreach ($resYachtInvitationBookings as $key => $resYachtInvitationBooking)
		{
			$resYachtInvitationBooking->bookedType = 'invitation';
		}


		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('yb.*')
			->from($db->quoteName('#__beseated_yacht_booking') . ' AS yb')
			->where($db->quoteName('yb.deleted_by_user') . ' = ' . $db->quote(0))
			->where($db->quoteName('yb.user_status') . 'NOT IN ('.implode(",", $cancelStatusArray).')');

			if($bookingID)
			{
				$query->where($db->quoteName('yb.yacht_booking_id') . ' = ' . $db->quote($bookingID));
			}

			$query->order($db->quoteName('yb.booking_date') . ' ASC');

		$query->select('y.yacht_name,y.location,y.city,y.deposit_per,y.refund_policy')
			->join('LEFT','#__beseated_yacht AS y ON y.yacht_id=yb.yacht_id');

		$query->select('split.yacht_booking_split_id,split.user_id as splited_user_id,split.paid_by_owner,split.split_payment_status')
            ->where($db->quoteName('split.user_id') . ' = ' . $db->quote($this->IJUserID))
            ->where($db->quoteName('split.is_owner') . ' = ' . $db->quote('0'));

            if(!$bookingID)
            {
            	$query->where($db->quoteName('split.split_payment_status') . ' IN ('.implode(",", $splitedUserStatus).')');
            }

        $query->join('LEFT','#__beseated_yacht_booking_split AS split ON split.yacht_booking_id=yb.yacht_booking_id');

		$query->select('ys.service_name,ys.service_type,ys.dock,ys.thumb_image,ys.image')
			->join('LEFT','#__beseated_yacht_services AS ys ON ys.service_id=yb.service_id');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=yb.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resYachtShareBookings = $db->loadObjectList();

		foreach ($resYachtShareBookings as $key => $resYachtShareBooking)
		{
			if($resYachtShareBooking->splited_user_id == $this->IJUserID && $resYachtShareBooking->split_payment_status == 7 &&  $resYachtShareBooking->paid_by_owner==1)
			{
				$resYachtShareBooking->paidByOwner  = 1;
			}

			$resYachtShareBooking->bookedType = 'share';
		}

		$resYachtShareInvitationBookings = (object) array_merge((array) $resYachtShareBookings, (array) $resYachtInvitationBookings);

		return $resYachtShareInvitationBookings;

	}

	function getBookedChauffeurShareInvitations($bookingID = null)
	{
		$statusArray    = array();
		$statusArray[] = $this->helper->getStatusID('booked');

		$cancelStatusArray    = array();
		$cancelStatusArray[] = $this->helper->getStatusID('canceled');

		$invitedUserStatus   = array();
		$invitedUserStatus[] = $this->helper->getStatusID('going');

		$splitedUserStatus   = array();
		$splitedUserStatus[] = $this->helper->getStatusID('paid');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('cb.*')
			->from($db->quoteName('#__beseated_chauffeur_booking') . ' AS cb')
			->where($db->quoteName('cb.deleted_by_user') . ' = ' . $db->quote(0))
			->where($db->quoteName('cb.user_status') . ' IN ('.implode(",", $statusArray).')');

			if($bookingID)
			{
				$query->where($db->quoteName('cb.chauffeur_booking_id') . ' = ' . $db->quote($bookingID));
			}

			$query->order($db->quoteName('cb.booking_date') . ' ASC');

		$query->select('c.chauffeur_name,c.location,c.city,c.deposit_per,c.refund_policy')
				->join('LEFT','#__beseated_chauffeur AS c ON c.chauffeur_id=cb.chauffeur_id');

		$query->select('invt.invitation_id')
		    ->where($db->quoteName('invt.element_type') . ' = ' . $db->quote('chauffeur'))
			->where($db->quoteName('invt.user_id') . ' = ' . $db->quote($this->IJUserID));

			if(!$bookingID)
			{
				$query->where($db->quoteName('invt.user_action') . ' IN ('.implode(",", $invitedUserStatus).')');
			}

			$query->join('LEFT','#__beseated_invitation AS invt ON invt.element_booking_id=cb.chauffeur_booking_id');

		$query->select('cs.service_name,cs.service_type,cs.thumb_image,cs.image')
				->join('LEFT','#__beseated_chauffeur_services AS cs ON cs.service_id=cb.service_id');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=cb.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resChauffeurInvitationBookings = $db->loadObjectList();

		foreach ($resChauffeurInvitationBookings as $key => $resChauffeurInvitationBooking)
		{
			$resChauffeurInvitationBooking->bookedType = 'invitation';
		}


		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('cb.*')
			->from($db->quoteName('#__beseated_chauffeur_booking') . ' AS cb')
			->where($db->quoteName('cb.deleted_by_user') . ' = ' . $db->quote(0))
			->where($db->quoteName('cb.user_status') . 'NOT IN ('.implode(",", $cancelStatusArray).')');

			if($bookingID)
			{
				$query->where($db->quoteName('cb.chauffeur_booking_id') . ' = ' . $db->quote($bookingID));
			}

			$query->order($db->quoteName('cb.booking_date') . ' ASC');

		$query->select('c.chauffeur_name,c.location,c.city,c.deposit_per,c.refund_policy')
				->join('LEFT','#__beseated_chauffeur AS c ON c.chauffeur_id=cb.chauffeur_id');

		$query->select('split.chauffeur_booking_split_id,split.user_id as splited_user_id,split.paid_by_owner,split.split_payment_status')
            ->where($db->quoteName('split.user_id') . ' = ' . $db->quote($this->IJUserID));

            if(!$bookingID)
            {
            	$query->where($db->quoteName('split.split_payment_status') . ' IN ('.implode(",", $splitedUserStatus).')');
            }

        $query->where($db->quoteName('split.is_owner') . ' = ' . $db->quote('0'))
            ->join('LEFT','#__beseated_chauffeur_booking_split AS split ON split.chauffeur_booking_id=cb.chauffeur_booking_id');

		$query->select('cs.service_name,cs.service_type,cs.thumb_image,cs.image')
			->join('LEFT','#__beseated_chauffeur_services AS cs ON cs.service_id=cb.service_id');

		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=cb.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resChauffeurShareBookings = $db->loadObjectList();

		foreach ($resChauffeurShareBookings as $key => $resChauffeurShareBooking)
		{
			if($resChauffeurShareBooking->splited_user_id == $this->IJUserID && $resChauffeurShareBooking->split_payment_status == 7 &&  $resChauffeurShareBooking->paid_by_owner==1)
			{
				$resChauffeurShareBooking->paidByOwner  = 1;
			}

			$resChauffeurShareBooking->bookedType = 'share';
		}

		$resChauffeurShareInvitationBookings = (object) array_merge((array) $resChauffeurShareBookings, (array) $resChauffeurInvitationBookings);

		return $resChauffeurShareInvitationBookings;

	}

	function filterCities($cityName,$allLuxuryCities)
	{
		$allLuxuryCities = array_map('ucfirst', array_values(array_unique($allLuxuryCities)));

		if(in_array($cityName, $allLuxuryCities))
		{
			for ($i = 0; $i < count($allLuxuryCities); $i++)
			{
				if($allLuxuryCities[$i] == $cityName)
				{
					unset($allLuxuryCities[$i]);
				}

			}

			$cityName = array(0 =>$cityName);
			sort($allLuxuryCities);
			$allLuxuryCities = array_merge($cityName,$allLuxuryCities);

			return $allLuxuryCities;
		}
		else
		{
			sort($allLuxuryCities);

			return $allLuxuryCities;
		}
	}

	function addSplitUserCountOnBooking()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingType       = IJReq::getTaskData('bookingType','', 'string');
		$bookingID         = IJReq::getTaskData('bookingID','', 'string');
		$splittedUserCount = IJReq::getTaskData('splittedUserCount','', 'string');
		$invitations = array();

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$bookingTypeValue          = ucfirst($bookingType);
		$tblElementBooking = JTable::getInstance($bookingTypeValue.'Booking', 'BeseatedTable');
		$tblElementBooking->load($bookingID);

		if(ucfirst($bookingType) == "Venue")
		{
			$bookingTypeIDField  = strtolower($bookingType.'_table_booking_id');
		}
		else
		{
			$bookingTypeIDField  = strtolower($bookingType.'_booking_id');
		}

		if(!$tblElementBooking->$bookingTypeIDField)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_BOOKING_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblElementBooking->total_split_count = $splittedUserCount;
		$tblElementBooking->each_person_pay   = $tblElementBooking->total_price / $splittedUserCount;

		if(!$tblElementBooking->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_SPLIT_USER_COUNT_NOT_CHANGED'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray['code']           = 200;

		return $this->jsonarray;


	}

	function addInvitationForFriendAttending($tblFriendsAttending)
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$invitedUserDetail = $this->helper->guestUserDetail($tblFriendsAttending->user_id);

		$tblInvitation = JTable::getInstance('Invitation', 'BeseatedTable');
		$tblInvitation->load(0);
		$invitationPost = array();
		$invitationPost['element_booking_id'] = $tblFriendsAttending->venue_table_booking_id;
		$invitationPost['element_id']         = $tblFriendsAttending->venue_id;
		$invitationPost['element_type']       = 'venue';
		$invitationPost['user_id']            = $tblFriendsAttending->user_id;
		$invitationPost['email']              = $invitedUserDetail->email;

		$userDetail = $this->helper->guestUserDetail($userID);

		if($invitedUserDetail->is_fb_user == '1' && !empty($invitedUserDetail->fb_id))
		{
			$invitationPost['fbid']               = $invitedUserDetail->fb_id;
		}
		else
		{
			$invitationPost['fbid']               = '';
		}

		$invitationPost['user_action']        = 9;
		$invitationPost['time_stamp']         = time();

		$tblInvitation->bind($invitationPost);
		$tblInvitation->store();

	}


	function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
	{
		//echo "<pre>";print_r($arr);echo "</pre>";exit;

		$sort_col = array();

		foreach ($arr as $key=> $row)
		{
			$sort_col[$key] = strtotime($row[$col]);
		}

		//echo "<pre/>";print_r($dir);
		array_multisort($sort_col, $dir, $arr);

		//echo "<pre>";print_r(date('d-m-Y',));echo "</pre>";exit;
	}

	function getOfflineBookings()
	{
		//echo "<pre>";print_r($this->jsonarray);echo "</pre>";exit;

		$luxuryBookings = $this->getBookings('Luxury');
		$eventBookings  = $this->getBookings('Event');
		$venueBookings  = $this->getBookings('Venue');

		$allElementBookings = array();
		$allElementBookings['globalNotifications'] = $this->jsonarray['globalNotifications'];
		$allElementBookings['luxuryBookings']      = ($luxuryBookings['luxuryBookings']) ? $luxuryBookings['luxuryBookings'] : array();
		$allElementBookings['venueBookings']       = ($venueBookings['venueBookings']) ? $venueBookings['venueBookings'] : array();
		$allElementBookings['eventBookings']       = ($eventBookings['eventBookings']) ? $eventBookings['eventBookings']: array() ;
		$allElementBookings['code']                = 200;

		if(empty($allElementBookings['luxuryBookings']) && empty($allElementBookings['venueBookings']) && empty($allElementBookings['eventBookings']))
		{
			$this->jsonarray['code'] = 204;
		}
		else
		{
			if(empty($allElementBookings['eventBookings']))
			{
				$allElementBookings['eventBookings']['history'] = array();
				$allElementBookings['eventBookings']['upcoming'] = array();
			}
			if(empty($allElementBookings['luxuryBookings']))
			{
				$allElementBookings['luxuryBookings']['history'] = array();
				$allElementBookings['luxuryBookings']['upcoming'] = array();
			}
			if(empty($allElementBookings['venueBookings']))
			{
				$allElementBookings['venueBookings']['history'] = array();
				$allElementBookings['venueBookings']['upcoming'] = array();
			}

			$this->jsonarray = $allElementBookings;
		}

		return $this->jsonarray;

	}

	function ccAvenueRefund($refund)
	{
		 require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();

        $encryptionKey = $beseatedParams->encryptionKey;
		$access_code   = $beseatedParams->accessCode;

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblPayment = JTable::getInstance('Payment', 'BeseatedTable');
		$tblPayment->load($refund->payment_id);

		$customer_info  = '{
						  "reference_no": '.$refund->txn_id.',
						  "refund_amount": '.$refund->amount.',
						  "refund_ref_no": '.$refund->payment_id.'
						}';

		$encrypted_data = $this->helper->encrypt($customer_info,$encryptionKey);

		// Define URL where the form resides
		$form_url = "https://login.ccavenue.ae/apis/servlet/DoWebTrans";

		// This is the data to POST to the form. The KEY of the array is the name of the field. The value is the value posted.
		$data_to_post = array();
		$customer_info_array = array();
		$data_to_post['enc_request']  = $encrypted_data;
		$data_to_post['access_code']  = $access_code;
		$data_to_post['command']      = 'refundOrder';
		$data_to_post['request_type'] = 'JSON';

		foreach ($data_to_post as $key => $value)
		{
			$customer_info_array[] = $key.'='.urlencode($value);
		}

		//echo "<pre>";print_r($customer_info_array);echo "</pre>";exit;
		$customer_info  = implode("&",$customer_info_array);

	    //create cURL connection
		$curl_connection = curl_init($form_url);

		//set options

		curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($curl_connection, CURLOPT_POST, 4);

		//set data to be posted
		curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $customer_info);

		//perform our request
		$result = curl_exec($curl_connection);

		$response_data = explode('&',$result);

		$response = explode('=',$response_data[1]);
		$status   = explode('=',$response_data[0]);
		$result   = $this->helper->decrypt($response[1], $encryptionKey);

		$result = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
								 '|[\x00-\x7F][\x80-\xBF]+'.
								 '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
								 '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
								 '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
								 '', $result );

		$refundOrderResponse =  json_decode($result);

		$refundOrderResponse->status = $status [1];

		$tblOrderRefund = JTable::getInstance('OrderRefund', 'BeseatedTable');
		$tblOrderRefund->load($refund->order_refund_id);

		$refund_result = $refundOrderResponse->Refund_Order_Result;

		$tblOrderRefund->api_call_status = $refundOrderResponse->status;
		$tblOrderRefund->is_order_refund = 1;
		$tblOrderRefund->refund_status   = $refund_result->refund_status;
		$tblOrderRefund->is_failed       = ($refund_result->reason) ? 1 : 0;
		$tblOrderRefund->reason          = ($refund_result->reason) ? $refund_result->reason : '';
		$tblOrderRefund->reference_no    = $refund->txn_id;
		$tblOrderRefund->api_response    = json_encode($refundOrderResponse);
		$tblOrderRefund->store();

		$tblPayment->order_refund_id = $refund->order_refund_id;
		$tblPayment->store();

		$emailSubject  = JText::sprintf('COM_BESEATED_ORDER_NOT_REFUNDED_CCAVENUE');

		if($tblOrderRefund->is_failed == '1' && $tblOrderRefund->refund_status == '1')
		{
			//$this->emailHelper->orderConfirmRefundFailedMail($emailSubject,$tblOrderRefund->payment_id,$tblOrderRefund->reference_no,$tblOrderRefund->reason,$refund->amount,$tblPayment->cc_billing_email,$refund->created_date);
		}
		else
		{
			//$this->emailHelper->orderConfirmRefundedMail($refund->payment_id,$tblOrderRefund->reference_no,$refund->amount,$tblPayment->cc_billing_email,$refund->created_date);
		}

		//close the connection
		curl_close($curl_connection);

	}



}
