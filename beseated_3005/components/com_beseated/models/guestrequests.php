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
 * The Beseated Club Requests Model
 *
 * @since  0.0.1
 */
class BeseatedModelGuestRequests extends JModelList
{
	protected $liveUsers;
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
		$this->liveUsers = BeseatedHelper::getLiveBeseatedGuests();
		parent::__construct($config);
	}

	public function getVenueRsvp()
	{
		$user = JFactory::getUser();
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$resultVenueBookings = array();

		$statusArray   = array();
		$statusArray[] = BeseatedHelper::getStatusID('pending');
		$statusArray[] = BeseatedHelper::getStatusID('available');
		$statusArray[] = BeseatedHelper::getStatusID('decline');
		$statusArray[] = BeseatedHelper::getStatusID('canceled');

		// Create the base select statement.
		$query->select('venue_id')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('deleted_by_user') . ' = ' . $db->quote(0))
			->group($db->quoteName('venue_id'))
			->order($db->quoteName('booking_date') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$resVenueIDs   = $db->loadColumn();

		$invitationStatus   = array();
		$invitationStatus[] = BeseatedHelper::getStatusID('pending');

		$venuesSplitsql = $db->getQuery(true);
		$venuesSplitsql->select('venue_table_booking_id,venue_id')
			->from($db->quoteName('#__beseated_venue_table_booking_split'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
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

		$invitedUserStatus   = array();
		$invitedUserStatus[] = BeseatedHelper::getStatusID('not-going');
		$invitedUserStatus[] = BeseatedHelper::getStatusID('maybe');
		$invitedUserStatus[] = BeseatedHelper::getStatusID('pending');

		$venuesInvitesql = $db->getQuery(true);
		$venuesInvitesql->select('element_booking_id,element_id')
			->from($db->quoteName('#__beseated_invitation'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('user_action') . ' IN ('.implode(",", $invitedUserStatus).')')  // by jamal
			->where($db->quoteName('element_type') . ' = ' . $db->quote('venue'));
		$db->setQuery($venuesInvitesql);
		$resVenueInvites = $db->loadObjectList();
		$invitationBookingIDs = array();

		foreach ($resVenueInvites as $key => $invitation){
			if(!in_array($invitation->element_id, $allVenueID)){
				$allVenueID[] = $invitation->element_id;
			}
			$otherBookingIDs[]      = $invitation->element_booking_id;
			$invitationBookingIDs[] = $invitation->element_booking_id;
		}

		$venuesFAsql = $db->getQuery(true);
		$venuesFAsql->select('friends_attending_id,venue_table_booking_id,venue_id')
			->from($db->quoteName('#__beseated_venue_friends_attending'))
			->where($db->quoteName('booking_user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('booking_user_status') . ' = ' . $db->quote('1'));
		$db->setQuery($venuesFAsql);
		$resVenueFriendsAttending = $db->loadObjectList();

		$FABookingIDs = array();
		$FAVenueIDs   = array();

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
		$proccessIDs         = array();

		foreach ($allVenueID as $key => $venueID)
		{
			$temp  = array();
			$query = $db->getQuery(true);
			// Create the base select statement.
			$query->select('vb.venue_table_booking_id,vb.venue_id,vb.table_id,vb.user_id,vb.booking_date,vb.booking_time,vb.privacy,vb.passkey,vb.total_guest,vb.male_guest,vb.female_guest,vb.total_hours,vb.total_price,vb.user_status,has_invitation,vb.is_show,vb.is_noshow,vb.each_person_pay,vb.is_splitted,vb.splitted_count,vb.remaining_amount,vb.booking_currency_code,vb.booking_currency_sign,vb.pay_by_cash_status,vb.response_date_time')
				->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vb');
				if(count($otherBookingIDs)!= 0)
				{
					$query->where(
						'('.
							$db->quoteName('vb.venue_table_booking_id') . ' IN ('.implode(",", $otherBookingIDs).') OR '.
							'('.
								$db->quoteName('vb.user_id') . ' = ' . $db->quote($user->id) .' AND '.
								$db->quoteName('vb.user_status') . ' IN ('.implode(",", $statusArray).')' .' AND '.
								$db->quoteName('vb.deleted_by_user') . ' = ' . $db->quote(0) . ' AND '.
								$db->quoteName('vb.has_booked') . ' = ' . $db->quote(0) .
							')'.
						')'
					);
				}
				else{
					$query->where($db->quoteName('vb.user_id') . ' = ' . $db->quote($user->id) );
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

			$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club,v.has_bottle')
				->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

			$query->select('vt.table_name,vt.premium_table_id,vt.min_price,vt.thumb_image,vt.image')
				->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

			$query->select('vpt.premium_table_name')
				->join('LEFT','#__beseated_venue_premium_table AS vpt ON vpt.premium_id=vt.premium_table_id');

			$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
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
				$tmpBooking['bookingDate']      = BeseatedHelper::convertDateFormat($booking->booking_date);
				$tmpBooking['bookingTime']      = BeseatedHelper::convertToHM($booking->booking_time);
				$tmpBooking['totalPrice']       = BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price,0);
				$tmpBooking['currencyCode']     = $booking->booking_currency_code;
				$tmpBooking['currencySign']     = $booking->booking_currency_sign;
				$tmpBooking['venueName']        = $booking->venue_name;
				$tmpBooking['venueType']        = $booking->venue_type;
				$tmpBooking['dayClub']          = $booking->is_day_club;
				$tmpBooking['tableName']        = $booking->table_name;
				$tmpBooking['privacy']          = $booking->privacy;
				$tmpBooking['passkey']          = $booking->passkey;
				$tmpBooking['statusCode']       = $booking->user_status;
				$tmpBooking['totalHours']       = $booking->total_hours;
				$tmpBooking['fullName']         = $booking->full_name;
				$tmpBooking['avatar']           = ($booking->avatar)?BeseatedHelper::getUserAvatar($booking->avatar):'';
				$tmpBooking['thumbAvatar']      = ($booking->thumb_avatar)?BeseatedHelper::getUserAvatar($booking->thumb_avatar):'';
				$tmpBooking['totalGuest']       = $booking->total_guest;
				$tmpBooking['maleGuest']        = $booking->male_guest;
				$tmpBooking['femaleGuest']      = $booking->female_guest;
				$tmpBooking['hasBottle']        = ($this->hasVenueBottle($booking->venue_id))? '1':'0';
				$tmpBooking['minSpend']         = BeseatedHelper::currencyFormat('','',$booking->min_price);
				$tmpBooking['thumbImage']       = ($booking->thumb_image)?JUri::base().'images/beseated/'.$booking->thumb_image:'';
				$tmpBooking['image']            = ($booking->image)?JUri::base().'images/beseated/'.$booking->image:'';

				$splitedUserCount = $this->getSplitedUserCount($elementType = 'Venue' , $booking->venue_table_booking_id);

				if(!$splitedUserCount)
				{
					$bookingDetail['remainingSplitUser']  = $booking->total_guest - 1;
				}
				else
				{
					if($splitedUserCount > $booking->total_guest)
					{
						$bookingDetail['remainingSplitUser']  = 0;
					}
					else
					{
						$bookingDetail['remainingSplitUser']  = $booking->total_guest - $splitedUserCount;
					}

				}



				$tmpBooking['paidByOwner']    = 0;

				if(in_array($booking->venue_table_booking_id, $splitedBookigIDs))
				{
					$tmpBooking['bookedType']         = 'share';

					$querySplitDetail = $db->getQuery(true);

					// Create the base select statement.
					$querySplitDetail->select('*')
						->from($db->quoteName('#__beseated_venue_table_booking_split'))
						->where($db->quoteName('venue_table_booking_id') . ' = ' . $db->quote($booking->venue_table_booking_id))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));

					// Set the query and load the result.
					$db->setQuery($querySplitDetail);

					$resSplitDetail = $db->loadObject();

					$tmpBooking['statusCode'] = $resSplitDetail->split_payment_status;
					$tmpBooking['params']     = array("splitID" => $resSplitDetail->venue_table_booking_split_id);

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
					}

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
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));

					// Set the query and load the result.
					$db->setQuery($queryInviteDetail);

					$resInviteDetail = $db->loadObject();
					$tmpBooking['statusCode']          = $resInviteDetail->user_action;
					$tmpBooking['params'] = array("invitationID" => $resInviteDetail->invitation_id);

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
				$tmpBooking['eachPersonPay']       = BeseatedHelper::currencyFormat('','',$booking->each_person_pay);
				$tmpBooking['splittedCount']       = $booking->splitted_count;
				$tmpBooking['remainingAmount']     = BeseatedHelper::currencyFormat('','',$booking->remaining_amount);
				if($booking->response_date_time != '0000-00-00 00:00:00'){
					$tmpBooking['remainingTime'] = strtotime($booking->response_date_time);
				}else{
					$tmpBooking['remainingTime'] = '';
				}

				$querySplit = $db->getQuery(true);
				$querySplit->select('split.venue_table_booking_split_id,split.user_id,split.is_owner,split.email,split.splitted_amount,split.split_payment_status,split.paid_by_owner')
					->from($db->quoteName('#__beseated_venue_table_booking_split','split'))
					->where($db->quoteName('split.venue_table_booking_id') . ' = ' . $db->quote($booking->venue_table_booking_id))
					->order($db->quoteName('split.time_stamp') . ' ASC');
				$querySplit->select('bu.full_name,bu.avatar,bu.thumb_avatar')
					->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=split.user_id');

				$db->setQuery($querySplit);
				$resSplits = $db->loadObjectList();

				if($resSplits)
				{
					$tempSplit = array();
					foreach ($resSplits as $key => $split)
					{
						if($user->id == $split->user_id &&  $split->is_owner == '1')
						{
							continue;
						}

						$tempSingleSplit = array();
						$tempSingleSplit['invitationID']   = $split->venue_table_booking_split_id;
						$tempSingleSplit['fullName']       = ($split->full_name)?$split->full_name:$split->email;
						$tempSingleSplit['splittedAmount'] = BeseatedHelper::currencyFormat('','',$split->splitted_amount);
						$tempSingleSplit['statusCode']     = $split->split_payment_status;
						$tempSingleSplit['avatar']         = ($split->avatar)?BeseatedHelper::getUserAvatar($split->avatar):'';
						$tempSingleSplit['thumbAvatar']    = ($split->thumb_avatar)?BeseatedHelper::getUserAvatar($split->thumb_avatar):'';

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

						if($user->id == $user->id && $split->split_payment_status == 7 && $split->is_owner == 0 && $split->paid_by_owner==1)
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
						->where($db->quoteName('fa.booking_user_id') . ' = ' . $db->quote($user->id))
						->where($db->quoteName('fa.booking_user_status') . ' = ' . $db->quote('1'));

					$venuesFAsql->select('bu.full_name,bu.avatar,bu.thumb_avatar')
						->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=fa.user_id');
					$db->setQuery($venuesFAsql);
					$resVenueFriendsAttending = $db->loadObjectList();

					foreach ($resVenueFriendsAttending as $key => $friendAttending)
					{
						$faBooking = $tmpBooking;
						$faBooking['bookedType']         = 'friendAttending';
						$faBooking['statusCode']          = $friendAttending->booking_user_status;
						$faBooking['params'] = array("friendsAttendingID" => $friendAttending->friends_attending_id);
						$faBooking['paymentURL'] = "";
						$faBooking['payByCashStatus'] = 0;

						//$faBooking['venueBookingID'] = $friendAttending->friends_attending_id;

						$faBooking['fullName']       = $friendAttending->full_name;
						$faBooking['avatar']         = ($friendAttending->avatar)?BeseatedHelper::getUserAvatar($friendAttending->avatar):'';
						$faBooking['thumbAvatar']    = ($friendAttending->thumb_avatar)?BeseatedHelper::getUserAvatar($friendAttending->thumb_avatar):'';

						$temp['bookings'][$bookingINDX]    = $faBooking;
						$bookingINDX++;
					}
				}else
				{
					$temp['bookings'][$bookingINDX]    = $tmpBooking;
					$bookingINDX++;
				}
			}


			if(count($temp) != 0){
				$resultVenueBookings[] = $temp;
			}
		}

		//echo "<pre/>";print_r($resultVenueBookings);exit;

		$resultVenueBookings = array_filter($resultVenueBookings);
		return $resultVenueBookings;
	}


	public function getProtectionRsvp()
	{
		$user          = JFactory::getUser();
		$statusArray   = array();
		$statusArray[] = BeseatedHelper::getStatusID('pending');
		$statusArray[] = BeseatedHelper::getStatusID('available');
		$statusArray[] = BeseatedHelper::getStatusID('decline');
		$statusArray[] = BeseatedHelper::getStatusID('canceled');

		$invitedUserStatus   = array();
		$invitedUserStatus[] = BeseatedHelper::getStatusID('not-going');
		$invitedUserStatus[] = BeseatedHelper::getStatusID('maybe');
		$invitedUserStatus[] = BeseatedHelper::getStatusID('pending');

		// Initialiase variables.
		$db = JFactory::getDbo();
		$protectionsIDsql = $db->getQuery(true);
		$protectionsIDsql->select('protection_id')
			->from($db->quoteName('#__beseated_protection_booking'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('deleted_by_user') . ' = ' . $db->quote(0))
			->group($db->quoteName('protection_id'))
			->order($db->quoteName('booking_date') . ' ASC');
		$db->setQuery($protectionsIDsql);
		$resProtectionIDs = $db->loadColumn();

	
		$invitationStatus = array();
		$invitationStatus[] = BeseatedHelper::getStatusID('pending');
		//$invitationStatus[] = $this->helper->getStatusID('paid');
		$protectionsSplitsql = $db->getQuery(true);
		$protectionsSplitsql->select('protection_booking_id,protection_id')
			->from($db->quoteName('#__beseated_protection_booking_split'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
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
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('user_action') . ' IN ('.implode(",", $invitedUserStatus).')') 
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
			$query->select('pb.protection_booking_id,pb.protection_id,pb.service_id,pb.user_id,pb.booking_date,pb.booking_time,pb.total_hours,pb.price_per_hours,pb.total_price,pb.user_status,pb.is_splitted,pb.each_person_pay,pb.splitted_count,pb.remaining_amount,pb.response_date_time,pb.total_split_count,pb.total_guard,pb.booking_currency_sign')
				->from($db->quoteName('#__beseated_protection_booking') . ' AS pb');
				if(count($otherBookingIDs)!= 0)
				{
					$query->where(
						'('.$db->quoteName('pb.protection_booking_id') . ' IN ('.implode(",", $otherBookingIDs).') OR '.
						'('.
							$db->quoteName('pb.user_id') . ' = ' . $db->quote($user->id) .' AND '.
							$db->quoteName('pb.user_status') . ' IN ('.implode(",", $statusArray).')' .' AND '.
							$db->quoteName('pb.deleted_by_user') . ' = ' . $db->quote(0) .
						')'.
						')'
					);
				}
				else{
					$query->where($db->quoteName('pb.user_id') . ' = ' . $db->quote($user->id) );
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


			$protectionIDs = array();
			$bookingINDX = 0;

			foreach ($resProtectionBookings as $key => $booking)
			{
				if(in_array($booking->protection_booking_id, $splitedBookigIDs))
				{
					$booking->bookedType         = 'share';

					$querySplitDetail = $db->getQuery(true);

					// Create the base select statement.
					$querySplitDetail->select('*')
						->from($db->quoteName('#__beseated_protection_booking_split'))
						->where($db->quoteName('protection_booking_id') . ' = ' . $db->quote($booking->protection_booking_id))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));

					// Set the query and load the result.
					$db->setQuery($querySplitDetail);

					$resSplitDetail = $db->loadObject();
					$booking->statusCode          = $resSplitDetail->split_payment_status;
					$booking->protection_booking_split_id          = $resSplitDetail->protection_booking_split_id;
					//$booking->params = array("splitID" => $resSplitDetail->protection_booking_split_id); // new

					$booking_invitation_id = $resSplitDetail->protection_booking_split_id;

					if($resSplitDetail->split_payment_status == 7)
					{
						$booking->paymentURL = "";
					}
					else
					{
						$booking->paymentURL =  JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$resSplitDetail->protection_booking_split_id.'&booking_type=protection.split';
					}
				}
				else if (in_array($booking->protection_booking_id, $invitationBookingIDs))
				{
					$booking->bookedType         = 'invitation';

					$queryInviteDetail = $db->getQuery(true);
					$queryInviteDetail->select('*')
						->from($db->quoteName('#__beseated_invitation'))
						->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($booking->protection_booking_id))
						->where($db->quoteName('element_id') . ' = ' . $db->quote($booking->protection_id))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('protection'))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));

					// Set the query and load the result.
					$db->setQuery($queryInviteDetail);

					$resInviteDetail = $db->loadObject();
					$booking->statusCode        = $resInviteDetail->user_action;
					$booking->invitation_id     = $resInviteDetail->invitation_id;

					// $booking->params = array("invitationID" => $resInviteDetail->invitation_id); // new
					$booking->paymentURL = "";
					$booking_invitation_id = $resInviteDetail->invitation_id;
				}
				else
				{
					$booking->bookedType         = 'booking';
					$booking->statusCode         = $booking->user_status;


					$booking_invitation_id       = $booking->protection_booking_id;

					if($booking->user_status == 4)
					{
						$booking->paymentURL = JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$booking->protection_booking_id.'&booking_type=protection';
					}
					else
					{
						$booking->paymentURL = "";
					}
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
						if($user->id == $split->user_id &&  $split->is_owner == '1')
						{
							continue;
						}

						$tempSingleSplit = array();
					
						if($split->user_id == $booking->user_id)
						{
							if($split->split_payment_status == 7)    // if is paid
							{
								// $bookingDetail->isBookingUserPaid =  1;  // new
								
							}
							else
							{
								// $bookingDetail->isBookingUserPaid =  0; // new
							}

						}
						
						if($split->user_id == $user->id && $split->split_payment_status == 7 && $split->is_owner == 0 && $split->paid_by_owner==1)
						{
							$booking->paidByOwner = 1;
						}
						
					}
					
				}

				$resultProtectionBookings[] = $booking;
			}

			//$resultProtectionBookings[] = $booking;
		}

		$resultProtectionBookings = json_decode(json_encode($resultProtectionBookings),TRUE);

		//echo "<pre>";print_r($resultProtectionBookings);echo "<pre/>";exit();

		return $resultProtectionBookings;
	
	}

	public function getChauffeurRsvp()
	{
		$user          = JFactory::getUser();
		$statusArray   = array();
		$statusArray[] = BeseatedHelper::getStatusID('pending');
		$statusArray[] = BeseatedHelper::getStatusID('available');
		$statusArray[] = BeseatedHelper::getStatusID('decline');
		$statusArray[] = BeseatedHelper::getStatusID('canceled');

		$invitationStatus = array();
		$invitationStatus[] = BeseatedHelper::getStatusID('pending');

		$invitedUserStatus   = array();
		$invitedUserStatus[] = BeseatedHelper::getStatusID('not-going');
		$invitedUserStatus[] = BeseatedHelper::getStatusID('maybe');
		$invitedUserStatus[] = BeseatedHelper::getStatusID('pending');

		$db = JFactory::getDbo();
		$chauffeurIDsql = $db->getQuery(true);
		$chauffeurIDsql->select('chauffeur_id')
			->from($db->quoteName('#__beseated_chauffeur_booking'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
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
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
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
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
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
							$db->quoteName('cb.user_id') . ' = ' . $db->quote($user->id) .' AND '.
							$db->quoteName('cb.user_status') . ' IN ('.implode(",", $statusArray).')' .' AND '.
							$db->quoteName('cb.deleted_by_user') . ' = ' . $db->quote(0) .
						')'.
						')'
					);
				}
				else{
					$query->where($db->quoteName('cb.user_id') . ' = ' . $db->quote($user->id) );
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
				if(in_array($booking->chauffeur_booking_id, $splitedBookigIDs))
				{
					$booking->bookedType  = 'share';

					$querySplitDetail = $db->getQuery(true);

					// Create the base select statement.
					$querySplitDetail->select('*')
						->from($db->quoteName('#__beseated_chauffeur_booking_split'))
						->where($db->quoteName('chauffeur_booking_id') . ' = ' . $db->quote($booking->chauffeur_booking_id))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));

					// Set the query and load the result.
					$db->setQuery($querySplitDetail);

					$resSplitDetail = $db->loadObject();
					$booking->statusCode   = $resSplitDetail->split_payment_status;
					$booking->chauffeur_booking_split_id = $resSplitDetail->chauffeur_booking_split_id;

				}
				else if (in_array($booking->chauffeur_booking_id, $invitationBookingIDs))
				{
					$booking->bookedType         = 'invitation';

					$queryInviteDetail = $db->getQuery(true);
					$queryInviteDetail->select('*')
						->from($db->quoteName('#__beseated_invitation'))
						->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($booking->chauffeur_booking_id))
						->where($db->quoteName('element_id') . ' = ' . $db->quote($booking->chauffeur_id))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('chauffeur'))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));

					// Set the query and load the result.
					$db->setQuery($queryInviteDetail);

					$resInviteDetail = $db->loadObject();

					$booking->statusCode   = $resInviteDetail->user_action;
					$booking->invitation_id = $resInviteDetail->invitation_id;
				}
				else
				{
					$booking->bookedType         = 'booking';
					$booking->statusCode         =  $booking->user_status;
					$booking_invitation_id = $booking->chauffeur_booking_id;
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

						if($split->user_id == $booking->user_id)
						{
							//$bookingDetail['priceToPay'] = $this->helper->currencyFormat('',$split->splitted_amount);
							if($split->split_payment_status == 7)    // if is paid
							{
								//$bookingDetail['isBookingUserPaid'] =  1; // by jamal
							}
							else
							{
								//$bookingDetail['isBookingUserPaid'] =  0; // by jamal
							}

						}
						else
						{
							//$tempSplit[] = $tempSingleSplit;
						}

						if($split->user_id == $user->id && $split->split_payment_status == 7 && $split->is_owner == 0 && $split->paid_by_owner==1)
						{
							$booking->paidByOwner = 1;
						}
					}
				}

				$resultChauffeurBookings[] = $booking;
				
			}
		}

		//$resultChauffeurBookings = array_map("unserialize", array_unique(array_map("serialize", $resultChauffeurBookings)));
		
		//$resultChauffeurBookings = array_filter($resultChauffeurBookings);

		$resultChauffeurBookings = json_decode(json_encode($resultChauffeurBookings),TRUE);

		return $resultChauffeurBookings;
	}

	public function getYachtRsvp()
	{
		$user          = JFactory::getUser();
		$statusArray   = array();
		$statusArray[] = BeseatedHelper::getStatusID('pending');
		$statusArray[] = BeseatedHelper::getStatusID('available');
		$statusArray[] = BeseatedHelper::getStatusID('decline');
		$statusArray[] = BeseatedHelper::getStatusID('canceled');

		$invitedUserStatus   = array();
		$invitedUserStatus[] = BeseatedHelper::getStatusID('not-going');
		$invitedUserStatus[] = BeseatedHelper::getStatusID('maybe');
		$invitedUserStatus[] = BeseatedHelper::getStatusID('pending');

		$db = JFactory::getDbo();

		$yachtsIDsql = $db->getQuery(true);
		$yachtsIDsql->select('yacht_id')
			->from($db->quoteName('#__beseated_yacht_booking'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('deleted_by_user') . ' = ' . $db->quote(0))
			->group($db->quoteName('yacht_id'))
			->order($db->quoteName('booking_date') . ' ASC');
		$db->setQuery($yachtsIDsql);
		$resYachtIDs = $db->loadColumn();

		$invitationStatus = array();
		$invitationStatus[] = BeseatedHelper::getStatusID('pending');
		//$invitationStatus[] = $this->helper->getStatusID('paid');

		$yachtsSplitsql = $db->getQuery(true);
		$yachtsSplitsql->select('yacht_booking_id,yacht_id')
			->from($db->quoteName('#__beseated_yacht_booking_split'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
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
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id))
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

		$resultYachtBookings      = array();
		$proccessIDs              = array();

		foreach ($allYachtID as $key => $yachtID)
		{
			$temp  = array();
			$query = $db->getQuery(true);
			// Create the base select statement.
			$query->select('yb.yacht_booking_id,yb.yacht_id,yb.user_id,yb.booking_date,yb.booking_time,yb.booking_currency_sign,yb.total_hours,yb.price_per_hours,yb.capacity,yb.total_price,yb.user_status,yb.booking_currency_code,yb.is_splitted,yb.has_invitation,yb.each_person_pay,yb.splitted_count,yb.remaining_amount,yb.response_date_time,yb.total_split_count,yb.splitted_count')
				->from($db->quoteName('#__beseated_yacht_booking') . ' AS yb');
				if(count($otherBookingIDs)!= 0)
				{
					$query->where(
						'('.$db->quoteName('yb.yacht_booking_id') . ' IN ('.implode(",", $otherBookingIDs).') OR '.
						'('.
							$db->quoteName('yb.user_id') . ' = ' . $db->quote($user->id) .' AND '.
							$db->quoteName('yb.user_status') . ' IN ('.implode(",", $statusArray).')' .' AND '.
							$db->quoteName('yb.deleted_by_user') . ' = ' . $db->quote(0) .
						')'.
						')'
					);
				}
				else{
					$query->where($db->quoteName('yb.user_id') . ' = ' . $db->quote($user->id) );
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
				if(in_array($booking->yacht_booking_id, $splitedBookigIDs))
				{
					$booking->bookedType  = 'share';

					$querySplitDetail = $db->getQuery(true);

					// Create the base select statement.
					$querySplitDetail->select('*')
						->from($db->quoteName('#__beseated_yacht_booking_split'))
						->where($db->quoteName('yacht_booking_id') . ' = ' . $db->quote($booking->yacht_booking_id))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));

					// Set the query and load the result.
					$db->setQuery($querySplitDetail);

					$resSplitDetail = $db->loadObject();

					$booking->statusCode                 = $resSplitDetail->split_payment_status;
					$booking->yacht_booking_split_id     = $resSplitDetail->yacht_booking_split_id;
				}
				else if (in_array($booking->yacht_booking_id, $invitationBookingIDs))
				{
					
					$queryInviteDetail = $db->getQuery(true);
					$queryInviteDetail->select('*')
						->from($db->quoteName('#__beseated_invitation'))
						->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($booking->yacht_booking_id))
						->where($db->quoteName('element_id') . ' = ' . $db->quote($booking->yacht_id))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('yacht'))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));

					// Set the query and load the result.
					$db->setQuery($queryInviteDetail);

					$resInviteDetail           = $db->loadObject();
					$booking->statusCode       = $resInviteDetail->user_action;
					$booking->invitation_id    = $resInviteDetail->invitation_id;
					$booking->bookedType         = 'invitation';
				}
				else
				{
					$booking->bookedType        = 'booking';
					$booking->statusCode         = $booking->user_status;	
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
						if($user->id == $split->user_id &&  $split->is_owner == '1')
						{
							continue;
						}

						if($split->user_id == $booking->user_id)
						{
							if($split->split_payment_status == 7)    // if is paid
							{
								//$bookingDetail['isBookingUserPaid'] =  1; // jamal
							}
							else
							{
								//$bookingDetail['isBookingUserPaid'] =  0; // jamal
							}

						}
						else
						{
							//$tempSplit[] = $tempSingleSplit;
						}

						if($split->user_id == $user->id && $split->split_payment_status == 7 && $split->is_owner == 0 && $split->paid_by_owner==1)
						{
							$booking->paidByOwner = 1;
						}
					}
				}

				$resultYachtBookings[] = $booking;
			}
		}

		$resultYachtBookings = json_decode(json_encode($resultYachtBookings),TRUE);

		return $resultYachtBookings;
	}

	public function getEventRsvp()
	{
		$user               = JFactory::getUser();
		$eventStatusArray[] = BeseatedHelper::getStatusID('decline');
		$eventStatusArray[] = BeseatedHelper::getStatusID('request');

		// Initialiase variables.
		$db             = JFactory::getDbo();
		$eventInvitesql = $db->getQuery(true);
		$eventInvitesql->select('tbi.invite_id,tbi.ticket_booking_id,tbi.ticket_booking_detail_id,tbi.ticket_id,tbi.event_id,tbi.user_id,tbi.invited_user_id,tbi.email,tbi.fbid,tbi.invited_user_status')
			->from($db->quoteName('#__beseated_event_ticket_booking_invite','tbi'))
			->where($db->quoteName('tbi.invited_user_id') . ' = ' . $db->quote($user->id))
			->where($db->quoteName('tbi.deleted_by_user') . ' = ' . $db->quote('0'))
		    ->where($db->quoteName('tbi.invited_user_status') . ' IN ('.implode(",", $eventStatusArray).')')
			->where($db->quoteName('tbi.user_id') . ' <> ' . $db->quote($user->id));

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

		$eventInvitesql->order('CONCAT('.$db->quoteName('e.event_date').', " ", '.$db->quoteName('e.event_time').')' . ' ASC');

		$db->setQuery($eventInvitesql);
		$resEventInvitations = $db->loadObjectList();

		$resEventInvitations = json_decode(json_encode($resEventInvitations),TRUE); 

		////echo "<pre>";print_r($resEventInvitations);echo "<pre/>";exit();

		return $resEventInvitations;
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

	public function changePrice($bookingID, $amount)
	{
		$bookingTable                     = $this->getTable();
		$bookingTable->load($bookingID);
		$bookingTable->total_price        = $amount;
		$bookingTable->user_status        = 4;
		$bookingTable->chauffeur_status   = 3;
		$bookingTable->response_date_time = date('Y-m-d H:i:s');

		if($bookingTable->store())
		{
			return 200;
		}

		return 400;
	}

	public function changeStatus($statusID,$bookingID)
	{
		$bookingTable                     = $this->getTable();
		$bookingTable->load($bookingID);
		$bookingTable->user_status        = 6;
		$bookingTable->yacht_status       = 6;
		$bookingTable->response_date_time = date('Y-m-d H:i:s');

		if($bookingTable->store())
		{
			return 200;
		}

		return 400;

	}

	public function deleteBooking($bookingID)
	{
		$bookingTable                   = $this->getTable();
		$bookingTable->load($bookingID);
		$bookingTable->deleted_by_yacht = 1;

		if($bookingTable->store())
		{
			return 200;
		}

		return 400;

	}

	public function acceptBooking($bookingID)
	{
		$bookingTable                     = $this->getTable();
		$bookingTable->load($bookingID);
		$bookingTable->yacht_status       = 3;
		$bookingTable->user_status        = 4;
		$bookingTable->response_date_time = date('Y-m-d H:i:s');

		if($bookingTable->store())
		{
			return 200;
		}

		return 400;

	}
}
