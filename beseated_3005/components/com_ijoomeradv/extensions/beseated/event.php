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
class event
{

	private $db;
	private $IJUserID;
	private $helper;
	private $jsonarray;
	private $emailHelper;
	private $my;

	function __construct()
	{
		require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';

		$this->db                = JFactory::getDBO();
		$this->emailHelper       = new BeseatedEmailHelper;
		$this->mainframe         =  JFactory::getApplication ();
		$this->IJUserID          = $this->mainframe->getUserState ( 'com_ijoomeradv.IJUserID', 0 ); //get login user id
		$this->my                = JFactory::getUser ( $this->IJUserID ); // set the login user object
		$this->helper            = new beseatedAppHelper;
		$this->jsonarray         = array();

		$notificationDetail = $this->helper->getNotificationCount();

		//$allNotificationCount =  $notificationDetail['allNotificationCount']+$notificationDetail['messageCount'];
		$allNotificationCount =  $notificationDetail['allNotificationCount'];

		$this->jsonarray['globalNotifications']['bookingsCount']        = ($notificationDetail['bookingCount']) ? (string)$notificationDetail['bookingCount']:"0";
		$this->jsonarray['globalNotifications']['requestsCount']        = ($notificationDetail['requestCount']) ?  (string)$notificationDetail['requestCount']:"0";
		$this->jsonarray['globalNotifications']['messagesCount']        = ($notificationDetail['messageCount']) ?  (string)$notificationDetail['messageCount']:"0";
		$this->jsonarray['globalNotifications']['notificationsCount']   = ($allNotificationCount) ? (string)$allNotificationCount:"0";
	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"event","extTask":"getEvents","taskData":{"city":""}}
	 */
	function getEvents()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$event    = array();
		$city      = IJReq::getTaskData('city','','string');
		/*$pageNO    = IJReq::getTaskData('pageNO',0);
		$pageLimit = BESEATED_EVENT_LIST_LIMIT;
		if($pageNO==0 || $pageNO==1){
			$startFrom=0;
		}else{
			$startFrom = $pageLimit*($pageNO-1);
		}*/

		// Initialiase variables.
		$this->db    = JFactory::getDbo();
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->quoteName('#__beseated_event'))
			->where($this->db->quoteName('published') . ' = ' . $this->db->quote('1'))
			//->where($this->db->quoteName('available_ticket') . ' >= ' . $this->db->quote('1'))
			->where($this->db->quoteName('is_deleted') . ' = ' . $this->db->quote('0'))
			->where('CONCAT('.$this->db->quoteName('event_date').', " ", '.$this->db->quoteName('event_time') . ') >= ' . $this->db->quote(date('Y-m-d H:i:s')));

		if(!empty($city))
		{
			$query->where('('.
					$this->db->quoteName('location') .' LIKE ' . $this->db->quote('%'.$city.'%'). ' OR '.
					$this->db->quoteName('city') .' LIKE ' . $this->db->quote('%'.$city.'%').
				')');
		}

		$query->order($this->db->quoteName('event_date') . ' ASC');

		/*echo "<pre>";
		print_r($query->dump());
		echo "</pre>";*/

		// Set the query and load the result.
		$this->db->setQuery($query);
		$events = $this->db->loadObjectList();

		foreach ($events as $key => $eventDetail)
		{
			if($this->checkEventTypeExist($eventDetail->event_id))
			{
				$ticketType = $this->getTicketTypeInfo($eventDetail->event_id);

				//echo "<pre>";print_r($ticketType);echo "<pre/>";exit();

				$event[$key]['eventID']         = $eventDetail->event_id;
				$event[$key]['eventName']       = $eventDetail->event_name;
				$event[$key]['image']           = ($eventDetail->image)?JUri::root().'images/beseated/'.$eventDetail->image:'';
				$event[$key]['thumbImage']      = ($eventDetail->thumb_image)?JUri::root().'images/beseated/'.$eventDetail->thumb_image:'';
				//$event[$key]['eventName']     = $eventDetail->event_name;
				$event[$key]['location']        = $eventDetail->location;
				$event[$key]['city']            = $eventDetail->city;
				$event[$key]['eventDate']       = $this->helper->convertDateFormat($eventDetail->event_date);
				$event[$key]['eventTime']       = $this->helper->convertToHM($eventDetail->event_time);
				$event[$key]['ticketPrice']     = $ticketType->min_ticket_price;
				$event[$key]['currencyCode']    = $eventDetail->currency_code;
				$event[$key]['currencySign']    = $eventDetail->currency_sign;
				$event[$key]['totalTicket']     = $ticketType->total_tickets;
				$event[$key]['availableTicket'] = $ticketType->available_tickets;
			}


			//$booking->booking_date);
			//$booking->booking_time);$this->helper->convertDateFormat($booking->booking_date);
			//$this->helper->convertToHM($booking->booking_time);
		}

		if(count($event) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_EVENTS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray['code']        = 200;
		$this->jsonarray['totalInPage'] = count($event);
		$this->jsonarray['events']      = $event;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"event","extTask":"confirmTicket","taskData":{"eventID":"58","ticketQty":"2"}}
	 */
	function confirmTicket()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$ticketQty = IJReq::getTaskData('ticketQty',0,'int');
		$eventID   = IJReq::getTaskData('eventID',0,'int');
		$ticketTypeID   = IJReq::getTaskData('ticketTypeID',0,'int');

		if(!$ticketQty || !$eventID || !$ticketTypeID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_EVENT_TICKET_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblEvent         = JTable::getInstance('Event', 'BeseatedTable');
		$tblEvent->load($eventID);

		$tblEventtickettypezone  = JTable::getInstance('Eventtickettypezone', 'BeseatedTable');
		$tblEventtickettypezone->load($ticketTypeID);

		if(!$tblEvent->event_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_EVENT_TICKET_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		if(!$tblEventtickettypezone->ticket_type_zone_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_EVENT_TICKET_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		if($tblEventtickettypezone->available_tickets < $ticketQty)
		{
			$this->jsonarray['code'] = 706;
			$this->jsonarray['message '] = JText::_('COM_IJOOMERADV_EVENT_TICKETS_NOT_AVAILABLE');
			$this->jsonarray['availableTicket'] = $tblEventtickettypezone->available_tickets;
			
			return $this->jsonarray;
		}

		$query = $this->db->getQuery(true); 
		$query->select('*')
			->from($this->db->quoteName('#__beseated_element_images'))
			->where($this->db->quoteName('element_type') . ' = ' . $this->db->quote('Event'))
			->where($this->db->quoteName('ticket_type_id') . ' = ' . $this->db->quote($ticketTypeID))
			->where($this->db->quoteName('element_id') . ' = ' . $this->db->quote($eventID))
			->where($this->db->quoteName('image_id') . ' NOT IN (SELECT `ticket_id` FROM `#__beseated_event_ticket_booking_detail` WHERE `event_id`='.$eventID.')')
			->order($this->db->quoteName('image_id') . ' ASC');
		$this->db->setQuery($query,0,$ticketQty);
		$resTicketsToBooked = $this->db->loadObjectList();
		$ticketBooked = 0;

		$ticketsID = array();

		foreach ($resTicketsToBooked as $key => $ticketToBooked)
		{
			$ticketsID[] = $ticketToBooked->image_id;
		}

		$totalTickets = count($ticketsID);
		$totalPrice = $totalTickets * $tblEventtickettypezone->ticket_price;

		$tblTicketBooking = JTable::getInstance('TicketBooking', 'BeseatedTable');
		$tblTicketBooking->load(0);
		$tbPost = array();
		$tbPost['event_id']              = $tblEvent->event_id;
		$tbPost['user_id']               = $this->IJUserID;
		$tbPost['tickets_id']            = json_encode($ticketsID);
		$tbPost['ticket_type_id']        = $ticketTypeID;
		$tbPost['total_ticket']          = $totalTickets;
		$tbPost['ticket_price']          = $tblEventtickettypezone->ticket_price;
		$tbPost['total_price']           = $totalPrice;
		$tbPost['booking_currency_sign'] = $tblEvent->currency_sign;
		$tbPost['booking_currency_code'] = $tblEvent->currency_code;
		$tbPost['status']                = 1;
		$tblTicketBooking->bind($tbPost);

		if(!$tblTicketBooking->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_EVENT_TICKET_BOOKING_FAILED_PLEASE_TRY_AGAIN'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$this->jsonarray['code']           = 200;
		$this->jsonarray['eventBookingID'] = $tblTicketBooking->ticket_booking_id;
		$this->jsonarray['availableTicket'] = $tblTicketBooking->ticket_booking_id;
		$this->jsonarray['paymentURL']     = JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$tblTicketBooking->ticket_booking_id.'&booking_type=event';
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"event","extTask":"checkTicketAvaibility","taskData":{"eventID":"58"}}
	 */
	function checkTicketAvaibility()
	{
		//$eventID   = IJReq::getTaskData('eventID',0,'int');
		$ticketTypeID   = IJReq::getTaskData('ticketTypeID',0,'int');

		if(!$ticketTypeID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_EVENT_TICKET_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblEventtickettypezone         = JTable::getInstance('Eventtickettypezone', 'BeseatedTable');
		$tblEventtickettypezone->load($ticketTypeID);

		if(!$tblEventtickettypezone->ticket_type_zone_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_EVENT_TICKET_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$this->jsonarray['code'] = 200;
		$this->jsonarray['availableTicket'] = $tblEventtickettypezone->available_tickets;

		return $this->jsonarray;

	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"event","extTask":"sendInvitation","taskData":{"eventBookingID":"58","emails":"","fbids":""}}
	 */
	function sendInvitation()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$emails         = IJReq::getTaskData('emails', '', 'string');
		$fbids          = IJReq::getTaskData('fbids', '', 'string');
		$bookingID      = IJReq::getTaskData('eventID', 0, 'int');
		$eventBookingID = IJReq::getTaskData('eventBookingID', 0, 'int');

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$filterEmails         = BeseatedHelper::filterEmails($emails);
		$filterFbFrndEmails   = BeseatedHelper::filterFbIds($fbids);
		$alreadyInvited       = $this->helper->getInvitationDetail($eventBookingID,'event');
		$alreadyInvitedEmails = array();

		if($alreadyInvited)
		{
			foreach ($alreadyInvited as $key => $invitation)
			{
				if(!empty($invitation->email))
				{
					$alreadyInvitedEmails[] = $invitation->email;
				}
			}
		}

		$newInvitedEmails = array();
		$emails           = IJReq::getTaskData('emails', '', 'string');
		$emailsArray      = explode(",", $emails);

		$notRegiEmail     = array_udiff($emailsArray, $filterEmails['allRegEmail'],'strcasecmp'); // not registered
		$notRegiEmail     = array_udiff($notRegiEmail, $alreadyInvitedEmails,'strcasecmp');   // not registered and not invited emails

		if(!empty($emails))
		{
			foreach ($filterEmails['guest'] as $key => $guest)
			{
				if($this->my->email == $guest)
				{
					continue;
				}
				else if(!in_array($guest, $alreadyInvitedEmails))
				{
					$newInvitedEmails[] = $guest; // registered but not invited emails
				}
			}
		}
		else
		{
			foreach ($filterFbFrndEmails['guest'] as $key => $guest)
			{
				if($this->my->email == $guest)
				{
					continue;
				}
				else if(!in_array($guest, $alreadyInvitedEmails))
				{
					$newInvitedEmails[] = $guest; // registered but not invited emails
				}
			}
		}

		$notRegiEmail     = array_filter($notRegiEmail);

		if(!empty($notRegiEmail) && !empty($newInvitedEmails))
		{
			$newInvitedEmails = array_merge($notRegiEmail,$newInvitedEmails);
		}
		else if (empty($notRegiEmail) && !empty($newInvitedEmails))
		{
			$newInvitedEmails = $newInvitedEmails;
		}
		else if (!empty($notRegiEmail) && empty($newInvitedEmails))
		{
			$newInvitedEmails = $notRegiEmail;
		}


		if(count($newInvitedEmails) == 0)
		{
			$this->jsonarray['message'] = JText::_('COM_IJOOMERADV_BESEATED_ELEMENT_USERS_ALREADY_INVITED');
			$this->jsonarray['code'] = 301;
			return $this->jsonarray;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_event_ticket_booking_detail'))
			->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($eventBookingID))
			->where($db->quoteName('booking_user_id') . ' = ' . $db->quote($this->IJUserID))
			->order($db->quoteName('ticket_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$resAllBookedTicket = $db->loadObjectList();
		
		$emailIndex         = 0;
		$hasNewInvited      = 0;

		$tblTicketBooking = JTable::getInstance('TicketBooking', 'BeseatedTable');
		$tblTicketBooking->load($eventBookingID);

		foreach ($resAllBookedTicket as $key => $tbDetail)
		{
			if($key == 0){ continue; }
			if($tbDetail->user_id !== $this->IJUserID){ continue; }

			$tblTicketBookingDetail = JTable::getInstance('TicketBookingDetail', 'BeseatedTable');
			$tblTicketInvitation    = JTable::getInstance('TicketInvitation', 'BeseatedTable');

			if(isset($newInvitedEmails[$emailIndex]) && !empty($newInvitedEmails[$emailIndex]))
			{
				$invitedUserDetail = $this->helper->guestUserDetailFromEmail($newInvitedEmails[$emailIndex]);
				$tblTicketInvitation->load(0);
				$tbiPost['ticket_booking_id']        = $tbDetail->ticket_booking_id;
				$tbiPost['ticket_booking_detail_id'] = $tbDetail->ticket_booking_detail_id;
				$tbiPost['ticket_id']                = $tbDetail->ticket_id;
				$tbiPost['event_id']                 = $tbDetail->event_id;
				$tbiPost['user_id']                  = $tbDetail->booking_user_id;
				$tbiPost['invited_user_id']          = ($invitedUserDetail && $invitedUserDetail->user_id)?$invitedUserDetail->user_id:0;
				$tbiPost['invited_user_status']      = $this->helper->getStatusID('request');
				$tbiPost['email']                    = $newInvitedEmails[$emailIndex];
				$tbiPost['fbid']                     = "";
				$tbiPost['time_stamp']               = time();
				$tblTicketInvitation->bind($tbiPost);

				if($tblTicketInvitation->store())
				{
				    $tblEvent = JTable::getInstance('Event', 'BeseatedTable');
					$tblEvent->load($tbDetail->event_id);

					$userDetail       = $this->helper->guestUserDetail($this->IJUserID);

					$event_date = date('d F Y',strtotime($tblEvent->event_date));
					$event_time = date('H:i',strtotime($tblEvent->event_time));

					$ticketImage = $this->getTicketImage($tbiPost['ticket_id']);

					$ticketImage = JUri::base().'images/beseated/'.$ticketImage;

					$this->emailHelper->eventInvitationMailUser($tblEvent->image,$tblEvent->event_name,$event_date,$event_time,$tblEvent->location,$userDetail->full_name,$userDetail->email,$tblTicketBooking->booking_currency_code,number_format($tblTicketBooking->ticket_price,0),$ticketImage,$tbiPost['email']);

					$emailIndex    = $emailIndex + 1;
					$tblTicketBookingDetail->load($tbDetail->ticket_booking_detail_id);
					$tblTicketBookingDetail->user_id = ($invitedUserDetail && $invitedUserDetail->user_id)?$invitedUserDetail->user_id:0;
					$tblTicketBookingDetail->store();

					$hasNewInvited = 1;

					$userID = BeseatedHelper::getUserForSplit($tbiPost['email']);

						if($userID)
						{
							$userIDs[]        = $userID;
						}

						$actor            = $this->IJUserID;
						$target           = ($userID) ? $userID :"0";
						$elementID        = $tbDetail->event_id;
						$elementType      = "Event";
						$notificationType = "event.invitation";
						$title            = JText::sprintf(
												'COM_BESEATED_NOTIFICATION_INVITATION_TO_INVITEE_FOR_EVENT',
												$userDetail->full_name,
												$tblEvent->event_name,
												$this->helper->convertDateFormat($tblEvent->event_date),
											    $this->helper->convertToHM($tblEvent->event_time)
											);

						$cid              = $tbDetail->ticket_booking_detail_id;
						$extraParams      = array();
						$extraParams["eventBookingID"]      = $tblTicketInvitation->ticket_booking_id;
						$extraParams["invitationID"]        = $tblTicketInvitation->invite_id;
						$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblTicketInvitation->ticket_booking_id,$newInvitedEmails[$emailIndex]);


				}
			}
		}

		if(!$hasNewInvited)
		{
			$this->jsonarray['code'] = 500;
			$this->jsonarray['message'] = JText::_('COM_IJOOMERADV_BESEATED_NOT_AVAILABLE_EVENT_TICKETS');
			return $this->jsonarray;
		}

		if(!empty($userIDs))
		{
			$this->jsonarray['pushNotificationData']['id']          = $tblTicketInvitation->ticket_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Event';
			$this->jsonarray['pushNotificationData']['to']          = implode(',', $userIDs);
			$this->jsonarray['pushNotificationData']['message']     = $title;
			/*$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_EVENT_INVITATION_RECEIVED');*/
			$this->jsonarray['pushNotificationData']['type']        = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype']  = '';
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}


	public function getTicketImage($ticket_id)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('image')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('image_id') . ' = ' . $db->quote($ticket_id));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	function getEventBookingDetail()
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$eventBookingID   = IJReq::getTaskData('eventBookingID',0,'int');

		$tblTicketBookingDetail = JTable::getInstance('TicketBooking', 'BeseatedTable');

		$tblTicketBookingDetail->load($eventBookingID);

		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		if(!$eventBookingID || !$tblTicketBookingDetail->ticket_booking_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_EVENT_BOOKING_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$statusArray = array();
		$statusArray[] = $this->helper->getStatusID('booked');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('tb.*')
			->from($db->quoteName('#__beseated_event_ticket_booking','tb'))
			->where($db->quoteName('tb.user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('tb.ticket_booking_id') . ' = ' . $db->quote($eventBookingID))
			->where($db->quoteName('tb.status') . ' IN ('.implode(",", $statusArray).')');

		$query->select('e.event_name,e.event_desc,e.image AS event_image,e.thumb_image AS event_thumb_image,e.event_date,e.event_time,e.location,e.city')
			->join('LEFT','#__beseated_event AS e ON e.event_id=tb.event_id');
		$query->select('bu.full_name,bu.avatar,bu.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=tb.user_id');

		$query->order($db->quoteName('e.event_date') . ' ASC,'.$db->quoteName('e.event_time').' ASC');
		$db->setQuery($query);
		$resTicketBooking = $db->loadObject();

		//echo "<pre/>";print_r($tickets);exit;

		$tickets = json_decode($resTicketBooking->tickets_id);


		$query = $db->getQuery(true);
		$query->select('ticket_id')
			->from($db->quoteName('#__beseated_event_ticket_booking_detail'))
			->where($db->quoteName('ticket_id') . ' IN ('.implode(",", $tickets).')')
			->where($db->quoteName('event_id') . ' = ' . $db->quote($resTicketBooking->event_id))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($resTicketBooking->user_id))
			->where($db->quoteName('booking_user_id') . ' = ' . $db->quote($resTicketBooking->user_id))
			->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($resTicketBooking->ticket_booking_id));

		$db->setQuery($query);
		$tickets = $db->loadColumn();

		$ticketImgs = $this->getTicketsImages($tickets);

		$tmpBooking = array();
		$tmpBooking['eventBookingID'] = $resTicketBooking->ticket_booking_id;
		$tmpBooking['eventID']        = $resTicketBooking->event_id;
		$tmpBooking['totalTicket']    = $resTicketBooking->total_ticket;
		$tmpBooking['eventName']      = $resTicketBooking->event_name;
		$tmpBooking['image']          = ($resTicketBooking->event_image)?JUri::root().'images/beseated/'.$resTicketBooking->event_image:'';
		$tmpBooking['thumbImage']     = ($resTicketBooking->event_thumb_image)?JUri::root().'images/beseated/'.$resTicketBooking->event_thumb_image:'';
		$tmpBooking['eventName']      = $resTicketBooking->event_name;
		$tmpBooking['location']       = $resTicketBooking->location;
		$tmpBooking['city']           = $resTicketBooking->city;
		$tmpBooking['eventDate']      = $this->helper->convertDateFormat($resTicketBooking->event_date);
		$tmpBooking['eventTime']      = $this->helper->convertToHM($resTicketBooking->event_time);
		$tmpBooking['ticketPrice']    = $this->helper->currencyFormat('',$resTicketBooking->ticket_price);
		$tmpBooking['currencyCode']   = $resTicketBooking->booking_currency_code;
		$tmpBooking['currencySign']   = $resTicketBooking->booking_currency_sign;
		$tmpBooking['totalPrice']     = $this->helper->currencyFormat('',$resTicketBooking->total_price);
		$tmpBooking['fullName']       = $resTicketBooking->full_name;
		$tmpBooking['avatar']         = ($resTicketBooking->avatar)?$this->helper->getUserAvatar($resTicketBooking->avatar):'';
		$tmpBooking['thumbAvatar']    = ($resTicketBooking->thumb_avatar)?$this->helper->getUserAvatar($resTicketBooking->thumb_avatar):'';
		$tmpBooking['ticketImages'] = $ticketImgs;

		$resultEventBookings = array();
		$resultEventBookings['eventTicketBookingDetail'] = $tmpBooking;
		$resultEventBookings['code'] = 200;

		return $resultEventBookings;
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

	function getTicketTypeInfo($event_id)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		// Create the base select statement.
		$query->select('MIN(ticket_price) as min_ticket_price ,SUM(total_tickets) as total_tickets,SUM(available_tickets) as available_tickets')
			->from($db->quoteName('#__beseated_event_ticket_type_zone'))
			->where($db->quoteName('event_id') . ' = ' . $db->quote($event_id))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'));
		
		// Set the query and load the result.
		$db->setQuery($query);
		
		$ticket_type_info = $db->loadObject();

		return $ticket_type_info; 
		
	}

	function checkEventTypeExist($event_id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		// Create the base select statement.
		$query->select('ticket_type_zone_id')
			->from($db->quoteName('#__beseated_event_ticket_type_zone'))
			->where($db->quoteName('event_id') . ' = ' . $db->quote($event_id))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'));
		
		// Set the query and load the result.
		$db->setQuery($query);
		
		$ticket_type_exist = $db->loadResult();

		return $ticket_type_exist; 
	}

	function getTicketTypesDetail()
	{
		/*if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} */// End of login Condition

		$eventID   = IJReq::getTaskData('eventID',0,'int');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		// Create the base select statement.
		$query->select('a.*,e.image as event_image,e.event_name,e.currency_sign,e.currency_code')
			->from($db->quoteName('#__beseated_event_ticket_type_zone').' AS a')
			->where($db->quoteName('a.event_id') . ' = ' . $db->quote($eventID))
			->where($db->quoteName('a.is_deleted') . ' = ' . $db->quote('0'))
			->join('LEFT','#__beseated_event AS e ON e.event_id=a.event_id')
			->order($this->db->quoteName('a.available_tickets') . ' DESC');
		
		
		// Set the query and load the result.
		$db->setQuery($query);
		
		
		$ticketTypes = $db->loadObjectList();

		$type = array();

		foreach ($ticketTypes as $key => $ticketType)
		{
			$type[$key]['ticketTypeID']    = $ticketType->ticket_type_zone_id;
			$type[$key]['ticketTypeName']  = $ticketType->ticket_type;
			$type[$key]['ticketTypeZone']  = $ticketType->ticket_zone;
			$type[$key]['ticketTypePrice'] = $this->helper->currencyFormat($ticketType->currency_sign,$ticketType->ticket_price);
			$type[$key]['eventID']         = $ticketType->event_id;
			$type[$key]['currencyCode']    = $ticketType->currency_code;
			$type[$key]['currencySign']    = $ticketType->currency_sign;
			$type[$key]['ticketPrice']     = $ticketType->ticket_price;
			$type[$key]['eventName']       = $ticketType->event_name;
			$type[$key]['image']           = ($ticketType->ticket_type_image)?JUri::root().'images/beseated/'.$ticketType->ticket_type_image:JUri::root().'images/beseated/'.$ticketType->event_image;
			//$event[$key]['thumbImage']      = ($eventDetail->thumb_image)?JUri::root().'images/beseated/'.$eventDetail->thumb_image:'';
			$type[$key]['totalTicket']     = $ticketType->total_tickets;
			$type[$key]['availableTicket'] = $ticketType->available_tickets;

		}

		if(count($type) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_EVENT_TICKET_TYPE_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray['code']        = 200;
		$this->jsonarray['totalInPage'] = count($type);
		$this->jsonarray['events']      = $type;

		return $this->jsonarray;

	}
}
