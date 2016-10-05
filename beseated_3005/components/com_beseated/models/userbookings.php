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
 * The Beseated User Bookings Model
 *
 * @since  0.0.1
 */
class BeseatedModelUserBookings extends JModelList
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

	public function getVenueBooking()
	{
		$statusArray   = array();
		$statusArray[] = BeseatedHelper::getStatusID('confirmed');
		$statusArray[] = BeseatedHelper::getStatusID('booked');
		$statusArray[] = BeseatedHelper::getStatusID('canceled');
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user  = JFactory::getUser();
		// Create the base select statement.
		$query->select('vb.venue_table_booking_id,vb.user_id,vb.venue_id,vb.table_id,vb.booking_date,vb.booking_time,vb.privacy,vb.passkey,vb.total_guest,vb.male_guest,vb.female_guest,vb.total_hours,vb.total_price,vb.user_status,vb.has_invitation,vb.each_person_pay,vb.is_splitted,vb.splitted_count,vb.remaining_amount,vb.booking_currency_code,vb.booking_currency_sign,vb.pay_by_cash_status,vb.is_bill_posted,vb.response_date_time')
			->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vb')
			->where($db->quoteName('vb.user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('vb.user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('vb.deleted_by_user') . ' = ' . $db->quote(0))
			->order($db->quoteName('vb.booking_date') . ' ASC');

		$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

		$query->select('vt.table_name,vt.premium_table_id,vt.min_price,vt.capacity,vt.thumb_image,vt.image')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

		$query->select('vpt.premium_table_name')
			->join('LEFT','#__beseated_venue_premium_table AS vpt ON vpt.premium_id=vt.premium_table_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$resVenueBookings = $db->loadObjectList();

		$venueBookingHistory  = array();
		$venueBookingUpcoming = array();
		$resultVenueBookings  = array();

		foreach ($resVenueBookings as $key => $booking)
		{
			$splitedUserCount = $this->getSplitedUserCount('Venue', $booking->venue_table_booking_id);

			if(!$splitedUserCount)
			{
				$booking->remainingSplitUser  = $booking->total_guest - 1;
			}
			else
			{
				if($splitedUserCount > $booking->total_guest)
				{
					$booking->remainingSplitUser  = 0;
				}
				else
				{
					$booking->remainingSplitUser  = $booking->total_guest - $splitedUserCount;
				}
			}

			if($booking->response_date_time != '0000-00-00 00:00:00'){
				$booking->remainingTime = strtotime($booking->response_date_time);
			}else{
				$booking->remainingTime = '';
			}

			if($booking->user_status !=  BeseatedHelper::getStatusID('booked') && $booking->user_id == $user->id)
			{
				$booking->paymentURL =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$booking->venue_table_booking_id.'&booking_type=venue';
			}
			else{
				$booking->paymentURL = "";
			}

			$querySplit = $db->getQuery(true);
			$querySplit->select('split.venue_table_booking_split_id,split.user_id,split.email,split.splitted_amount,split.split_payment_status')
				->from($db->quoteName('#__beseated_venue_table_booking_split','split'))
				->where($db->quoteName('split.venue_table_booking_id') . ' = ' . $db->quote($booking->venue_table_booking_id))
				->order($db->quoteName('split.time_stamp') . ' ASC');
			$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar')
				->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id');

			$db->setQuery($querySplit);
			$resSplits = $db->loadObjectList();

			if(count($resSplits) > 0)
			{
				$tempSplit = array();
				foreach ($resSplits as $key => $split)
				{
					if($split->user_id == $booking->user_id)
					{
						$booking->priceToPay = $split->splitted_amount;
						if($split->split_payment_status == 7 && $booking->user_status != BeseatedHelper::getStatusID('booked'))
						{
							$booking->isBookingUserPaid =  1;
							$booking->paymentURL        = JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$booking->venue_table_booking_id.'&booking_type=venue';
						}
						else if($split->split_payment_status == 7)
						{
							$booking->isBookingUserPaid =  1;
							$booking->paymentURL        = "";
						}
						else
						{
							$booking->isBookingUserPaid =  0;
							$booking->paymentURL        =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$split->venue_table_booking_split_id.'&booking_type=venue.split';
						}
					}
					else
					{
						$tempSplit[] = $split;
					}
				}
				$booking->splits = $tempSplit;
			}
			else{
				$booking->splits= array();
			}

			if(BeseatedHelper::isPastDate($booking->booking_date))
			{
				$venueBookingHistory[] = $booking;
			}
			else
			{
				$venueBookingUpcoming[] = $booking;
			}
		}

		$resultVenueBookings['history'] = $venueBookingHistory;
		$resultVenueBookings['upcoming'] = $venueBookingUpcoming;

		return $resultVenueBookings;

	}

	public function getSplitedUserCount($bookingType,$element_booking_id)
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

	public function getChauffeurBookings()
	{
		$user = JFactory::getUser();

		$statusArray = array();
		$statusArray[] = BeseatedHelper::getStatusID('booked');
		$statusArray[] =BeseatedHelper::getStatusID('canceled');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('cb.*')
			->from($db->quoteName('#__beseated_chauffeur_booking') . ' AS cb')
			->where($db->quoteName('cb.user_id') . ' = ' . $db->quote($user->id))
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
			if($booking->bookedType == 'share')
			{
				$booking->bookedType   =  $booking->bookedType;
				$booking_invitation_id =  $booking->chauffeur_booking_split_id;
			}
			else if ($booking->bookedType == 'invitation')
			{
				$booking->bookedType        = $booking->bookedType;
				$booking_invitation_id      =  $booking->invitation_id;
			}
			else
			{
				$booking->bookedType      = 'booking';
				$booking_invitation_id    =  $booking->chauffeur_booking_id;
			}

			if(BeseatedHelper::isPastDate($booking->booking_date))
			{
				$chauffeurBookingHistory[] = $booking;
			}
			else
			{
				$chauffeurBookingUpcoming[] = $booking;
			}
		}

		$resultChauffeurBookings['history'] = $chauffeurBookingHistory;
		$resultChauffeurBookings['upcoming'] = $chauffeurBookingUpcoming;

		return $resultChauffeurBookings;
	}

	public function getProtectionBookings()
	{
		$user = JFactory::getUser();

		$statusArray = array();
		$statusArray[] = BeseatedHelper::getStatusID('booked');
		$statusArray[] = BeseatedHelper::getStatusID('canceled');
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('pb.protection_booking_id,pb.protection_id,pb.service_id,pb.user_id,pb.booking_date,pb.booking_time,pb.meetup_location,pb.total_guard,pb.total_hours,pb.price_per_hours,pb.total_price,pb.user_status,pb.is_splitted,pb.has_invitation,pb.each_person_pay,pb.splitted_count,pb.remaining_amount,pb.total_split_count,pb.is_noshow')
			->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
			->where($db->quoteName('pb.user_id') . ' = ' . $db->quote($user->id))
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
			if($booking->bookedType == 'share')
			{
				$booking->bookedType        = $booking->bookedType;
				$booking_invitation_id      =  $booking->protection_booking_split_id;
			}
			else if ($booking->bookedType == 'invitation')
			{
				$booking->bookedType        = $booking->bookedType;
				$booking_invitation_id      =  $booking->invitation_id;
			}
			else
			{
				$booking->bookedType      = 'booking';
				$booking_invitation_id    =  $booking->protection_booking_id;
				
			}

			if(BeseatedHelper::isPastDate($booking->booking_date))
			{
				$protectionBookingHistory[] = $booking;
			}
			else
			{
				$protectionBookingUpcoming[] = $booking;
			}
		}

		$resultProtectionBookings['history'] = $protectionBookingHistory;
		$resultProtectionBookings['upcoming'] = $protectionBookingUpcoming;

		//echo "<pre/>";print_r($resultProtectionBookings);exit;

		return $resultProtectionBookings;
	}

	public function getYachtBookings()
	{
		$user = JFactory::getUser();

		$statusArray = array();
		$statusArray[] = BeseatedHelper::getStatusID('booked');
		$statusArray[] = BeseatedHelper::getStatusID('canceled');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('yb.*')
			->from($db->quoteName('#__beseated_yacht_booking') . ' AS yb')
			->where($db->quoteName('yb.user_id') . ' = ' . $db->quote($user->id))
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
			if($booking->bookedType == 'share')
			{
				$booking->bookedType        = $booking->bookedType;
				$booking_invitation_id      =  $booking->yacht_booking_split_id;
			}
			else if ($booking->bookedType == 'invitation')
			{
				$booking->bookedType        = $booking->bookedType;
				$booking_invitation_id      = $booking->invitation_id;
			}
			else
			{
				$booking->bookedType      = 'booking';
				$booking_invitation_id    =  $booking->yacht_booking_id;
			}

			if(BeseatedHelper::isPastDate($booking->booking_date))
			{
				$yachtBookingHistory[] = $booking;
			}
			else
			{
				$yachtBookingUpcoming[] = $booking;
			}
		}

		$resultYachtBookings['history'] = $yachtBookingHistory;
		$resultYachtBookings['upcoming'] = $yachtBookingUpcoming;

		return $resultYachtBookings;
	}

	function getEventBookings()
	{
		$user = JFactory::getUser();
		$bookedInvitations  = $this->getBookedEventInvitations();

		$statusArray = array();
		$statusArray[] = BeseatedHelper::getStatusID('booked');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('tb.ticket_price,tb.booking_currency_sign,tb.total_price,tb.created,tb.ticket_booking_id,tb.total_ticket')
			->from($db->quoteName('#__beseated_event_ticket_booking','tb'))
			->where($db->quoteName('tb.user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('e.is_deleted') . ' = ' . $db->quote('0'))
			->where($db->quoteName('e.published') . ' = ' . $db->quote('1'))
			->where($db->quoteName('tb.status') . ' IN ('.implode(",", $statusArray).')');

		$query->select('e.event_id,e.event_name,e.event_date,e.event_time')
			->join('LEFT','#__beseated_event AS e ON e.event_id=tb.event_id');
		$query->select('bu.full_name,bu.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=tb.user_id');

		$query->order($db->quoteName('e.event_date') . ' ASC,'.$db->quoteName('e.event_time').' ASC');
		$db->setQuery($query);
		$resTicketBookings = $db->loadObjectList();

		$processIDs = array();
		$eventBookingHistory = array();
		$eventBookingUpcoming = array();

		$resTicketBookings = (object) array_merge((array) $resTicketBookings, (array) $bookedInvitations);

		//echo "<pre>";print_r($booking->event_id);echo "<pre/>";exit();

		foreach ($resTicketBookings as $key => $booking)
		{
			$booking->bookingDate = BeseatedHelper::convertDateFormat($booking->created);

			$availableTickets = $this->InvitationTicketBookingDetails($booking->event_id,$booking->ticket_booking_id);

			if($booking->event_id)
			{
				if(@$booking->bookedType == 'invitation')
				{
					$booking->bookedType       = 'invitation';
				}
				else
				{
					$booking->bookedType   = 'booking';	
				}

				$booking->availableTickets = $availableTickets;

				$checkDateTime = date('Y-m-d H:i:s',strtotime($booking->event_date.' '.$booking->event_time));

				$booking = json_decode(json_encode($booking),TRUE); 

				if(BeseatedHelper::isPastDateTime($checkDateTime))
				{

					$eventBookingHistory[] = $booking;
				}
				else
				{
					$eventBookingUpcoming[] = $booking;
				}

			}	
		}

		BeseatedHelper::array_sort_by_column(array_values($eventBookingHistory),'bookingDate',$dir = SORT_ASC);
		BeseatedHelper::array_sort_by_column(array_values($eventBookingUpcoming),'bookingDate',$dir = SORT_ASC);

		$resultEventBookings = array();
		$resultEventBookings['history'] = $eventBookingHistory;
		$resultEventBookings['upcoming'] = $eventBookingUpcoming;

		return $resultEventBookings;
	}

	function getBookedEventInvitations()
	{
		$user = JFactory::getUser();
		
		$eventStatusArray[] = BeseatedHelper::getStatusID('accept');

		$db    = JFactory::getDbo();

		// Start of Get Event RSVP
		$eventInvitesql = $db->getQuery(true);
		$eventInvitesql->select('tbi.invite_id,tbi.ticket_booking_id,tbi.ticket_booking_detail_id,tbi.invited_user_status')
			->from($db->quoteName('#__beseated_event_ticket_booking_invite','tbi'))
			->where($db->quoteName('tbi.invited_user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('tbi.deleted_by_user') . ' = ' . $db->quote('0'))
		    ->where($db->quoteName('tbi.invited_user_status') . ' IN ('.implode(",", $eventStatusArray).')')
			->where($db->quoteName('tbi.user_id') . ' <> ' . $db->quote($user->id));

		$eventInvitesql->select('e.event_id,e.event_name,e.event_date,e.event_time')
			->join('INNER','#__beseated_event AS e ON e.event_id=tbi.event_id')
			//->where('STR_TO_DATE(CONCAT('.$this->db->quoteName('e.event_date').', " ", '.$this->db->quoteName('e.event_time').'),"%Y-%m-%d %H:%i:%s")' . ' >= ' . $this->db->quote(date('Y-m-d H:i:s')));
			->where('CONCAT('.$db->quoteName('e.event_date').', " ", '.$db->quoteName('e.event_time') . ') >= ' . $db->quote(date('Y-m-d H:i:s')));

		$eventInvitesql->select('etb.ticket_price,etb.booking_currency_sign,etb.total_price,etb.created')
			->join('INNER','#__beseated_event_ticket_booking AS etb ON etb.ticket_booking_id=tbi.ticket_booking_id');

		$eventInvitesql->select('bu.full_name,bu.thumb_avatar')
			->join('INNER','#__beseated_user_profile AS bu ON bu.user_id=tbi.user_id');

		$eventInvitesql->order('CONCAT('.$db->quoteName('e.event_date').', " ", '.$db->quoteName('e.event_time').')' . ' ASC');

		$db->setQuery($eventInvitesql);
		$resEventInvitations = $db->loadObjectList();

		foreach ($resEventInvitations as $key => $eventInvitations)
		{
			$eventInvitations->bookedType = 'invitation';
		}

		return $resEventInvitations;
	}

	function getBookedProtectionShareInvitations($bookingID = null)
	{
		$user = JFactory::getUser();

		$statusArray    = array();
		$statusArray[]  = BeseatedHelper::getStatusID('booked');

		$cancelStatusArray    = array();
		$cancelStatusArray[]  = BeseatedHelper::getStatusID('canceled');

		$invitedUserStatus   = array();
		$invitedUserStatus[] = BeseatedHelper::getStatusID('going');

		$splitedUserStatus   = array();
		$splitedUserStatus[] = BeseatedHelper::getStatusID('paid');

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
			->where($db->quoteName('invt.user_id') . ' = ' . $db->quote($user->id));

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
            ->where($db->quoteName('split.user_id') . ' = ' . $db->quote($user->id));

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
			if($resProtectionShareBooking->splited_user_id == $user->id && $resProtectionShareBooking->split_payment_status == 7 &&  $resProtectionShareBooking->paid_by_owner==1)
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
		$user = JFactory::getUser();
		$statusArray    = array();
		$statusArray[] = BeseatedHelper::getStatusID('booked');

		$cancelStatusArray    = array();
		$cancelStatusArray[] = BeseatedHelper::getStatusID('canceled');

		$invitedUserStatus   = array();
		$invitedUserStatus[] = BeseatedHelper::getStatusID('going');

		$splitedUserStatus   = array();
		$splitedUserStatus[] = BeseatedHelper::getStatusID('paid');

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
			->where($db->quoteName('invt.user_id') . ' = ' . $db->quote($user->id));

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
            ->where($db->quoteName('split.user_id') . ' = ' . $db->quote($user->id))
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
			if($resYachtShareBooking->splited_user_id == $user->id && $resYachtShareBooking->split_payment_status == 7 &&  $resYachtShareBooking->paid_by_owner==1)
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
		$user = JFactory::getUser();

		$statusArray    = array();
		$statusArray[]  = BeseatedHelper::getStatusID('booked');

		$cancelStatusArray    = array();
		$cancelStatusArray[]  = BeseatedHelper::getStatusID('canceled');

		$invitedUserStatus   = array();
		$invitedUserStatus[] = BeseatedHelper::getStatusID('going');

		$splitedUserStatus   = array();
		$splitedUserStatus[] = BeseatedHelper::getStatusID('paid');

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
			->where($db->quoteName('invt.user_id') . ' = ' . $db->quote($user->id));

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
            ->where($db->quoteName('split.user_id') . ' = ' . $db->quote($user->id));

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
			if($resChauffeurShareBooking->splited_user_id == $user->id && $resChauffeurShareBooking->split_payment_status == 7 &&  $resChauffeurShareBooking->paid_by_owner==1)
			{
				$resChauffeurShareBooking->paidByOwner  = 1;
			}

			$resChauffeurShareBooking->bookedType = 'share';
		}

		$resChauffeurShareInvitationBookings = (object) array_merge((array) $resChauffeurShareBookings, (array) $resChauffeurInvitationBookings);

		return $resChauffeurShareInvitationBookings;

	}


	public function InvitationTicketBookingDetails($eventId,$eventBookingID)
	{
		$user = JFactory::getUser();
		$app            = JFactory::getApplication();
		
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblEventTicketBooking = JTable::getInstance('TicketBooking', 'BeseatedTable',array());
		$tblEventTicketBooking->load($eventBookingID);

		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);

		$query->select('ticket_id')
			->from($db->quoteName('#__beseated_event_ticket_booking_detail'))
			->where($db->quoteName('event_id') . ' = ' . $db->quote($eventId))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('booking_user_id') . ' = ' . $db->quote($tblEventTicketBooking->user_id))
			->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($eventBookingID));

		$db->setQuery($query);
		$ticketIDs = $db->loadColumn();

		return count($ticketIDs);
	}

}
