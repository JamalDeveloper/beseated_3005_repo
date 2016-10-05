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
 * The Beseated ClubGuestList Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerGuestRequests extends JControllerAdmin
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config	An optional associative array of configuration settings.
	 * @return  ContentControllerArticles
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		//$this->registerTask('unfeatured',	'featured');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   0.0.1
	 */
	public function getModel($name = 'GuestRequests', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	
	function deleteBooking()
	{
		$user = JFactory::getUser();

		if(!$user->id)
		{
			echo 704;
			exit();
		} // End of login Condition

		$input = JFactory::getApplication()->input;

		$bookingID   = $input->getInt('bookingID',0);
		$bookingType = $input->getstring('bookingType','');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$bookingTypeValue          = ucfirst($bookingType);

		if($bookingTypeValue == 'Event')
		{
			$tblElementBooking = JTable::getInstance('TicketInvitation', 'BeseatedTable');
			$tblElementBooking->load($bookingID);

			if(!$tblElementBooking->invite_id)
			{
				echo 400; // COM_IJOOMERADV_BESEATED_INVALID_DETAIL
				exit();
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
				echo 400; // COM_IJOOMERADV_BESEATED_INVALID_DETAIL
				exit();
			}
		}

		$tblElementBooking->deleted_by_user = 1;

		if(!$tblElementBooking->store())
		{
			echo 500; // COM_IJOOMERADV_BESEATED_ERROR_WHILE_DELETE
			exit(); 
		}

		echo 200;
		exit(); 
	}

	function changeBookingStatus()
	{
		$app             = JFactory::getApplication();
		$user            = JFactory::getUser();
		$bookingID       = $app->input->getInt('booking_id');
		$bookingStatus   = $app->input->getInt('booking_status');
		$userDetail      = BeseatedHelper::guestUserDetail($user->id);
		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');
		$tblTable        = JTable::getInstance('Table', 'BeseatedTable');

		$tblVenueBooking->load($bookingID);
		$tblVenue->load($tblVenueBooking->venue_id);
		$tblTable->load($tblVenueBooking->table_id);

		if($bookingStatus == 3)
		{
			$tblVenueBooking->user_status      = BeseatedHelper::getStatusID('available');
			$tblVenueBooking->venue_status     = BeseatedHelper::getStatusID('awaiting-payment');
			$tblVenueBooking->remaining_amount = $tblVenueBooking->total_price;
			$notificationType                  = "venue.request.accepted";

			if($tblVenue->is_day_club)
			{
				$formatedBookingTime = BeseatedHelper::convertToHM($tblVenueBooking->booking_time);
				$title               = JText::sprintf(
					'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_ACCEPTED_BY_DAY_VENUE',
					$tblVenue->venue_name,
					$tblTable->table_name,
					BeseatedHelper::convertDateFormat($tblVenueBooking->booking_date),
					$formatedBookingTime
				);
			}
			else
			{
				$formatedBookingTime = "-";
				$title               = JText::sprintf(
					'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_ACCEPTED_BY_NIGHT_VENUE',
					$tblVenue->venue_name,
					$tblTable->table_name,
					BeseatedHelper::convertDateFormat($tblVenueBooking->booking_date)
				);
			}
		}
		else if($bookingStatus == 6)
		{
			$tblVenueBooking->user_status  = BeseatedHelper::getStatusID('decline');
			$tblVenueBooking->venue_status = BeseatedHelper::getStatusID('decline');
			$notificationType              = "table.request.declined";

			if($tblVenue->is_day_club)
			{
				$formatedBookingTime = BeseatedHelper::convertToHM($tblVenueBooking->booking_time);
				$title               = JText::sprintf(
						'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_DECLINED_BY_DAY_VENUE',
						$tblVenue->venue_name,
						$tblTable->table_name,
						BeseatedHelper::convertDateFormat($tblVenueBooking->booking_date),
						$formatedBookingTime
					);
			}
			else
			{
				$formatedBookingTime = "-";
				$title               = JText::sprintf(
						'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_DECLINED_BY_NIGHT_VENUE',
						$tblVenue->venue_name,
						$tblTable->table_name,
						BeseatedHelper::convertDateFormat($tblVenueBooking->booking_date)
					);
			}

		}

		$tblVenueBooking->response_date_time = date('Y-m-d H:i:s');

		if(!$tblVenueBooking->store())
		{
			echo 400;
			exit;
		}

		$actor                              = $user->id;
		$target                             = $tblVenueBooking->user_id;
		$elementID                          = $tblVenue->venue_id;
		$elementType                        = "Venue";
		$cid                                = $tblVenueBooking->venue_table_booking_id;
		$extraParams                        = array();
		$extraParams["venueTableBookingID"] = $tblVenueBooking->venue_table_booking_id;

		if(BeseatedHelper::storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams))
		{
			$venueDefaultImage = BeseatedHelper::getElementDefaultImage($tblVenue->venue_id,'Venue');
			$venueThumb        = Juri::base().'images/beseated/'.$venueDefaultImage->thumb_image;
			$bookingDate       = date('d F Y',strtotime($tblVenueBooking->booking_date));
			$companyDetail     = JFactory::getUser($tblVenue->user_id);
			$userDetail        = JFactory::getUser($target);
			require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
			$emailAppHelper = new BeseatedEmailHelper();

			if($bookingStatus == 3)
			{
				$emailAppHelper->venueBookingAvailableUserMail($tblVenue->venue_name,$venueThumb,$bookingDate,$formatedBookingTime,$tblTable->table_name,$tblVenue->currency_code,number_format($tblVenueBooking->total_price,0),$tblVenueBooking->male_guest,$tblVenueBooking->female_guest,$userDetail->name,$userDetail->email,$companyDetail->email);
			}
			elseif ($bookingStatus == 6)
			{
				$emailAppHelper->venueBookingNotAvailableUserMail($tblVenue->venue_name,$venueThumb,$bookingDate,$formatedBookingTime,$tblTable->table_name,$tblVenue->currency_code,number_format($tblVenueBooking->total_price,0),$tblVenueBooking->male_guest,$tblVenueBooking->female_guest,$userDetail->name,$userDetail->email,$companyDetail->email);
			}
		}

		echo 200;
		exit;
	}

	function changeEventInvitationStatus()
	{
		$user = JFactory::getUser();

		if(!$user->id)
		{
			echo 704;
			exit();
		} // End of login Condition


		$input = JFactory::getApplication()->input;

		$invitationID = $input->getInt('invitationID',0);
		$statusCode   = $input->getInt('statusCode',0);

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

			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_INVITATION_DETAIL
			exit();
		}

		if(!in_array($statusCode, $possibleCode))
		{
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_INVITATION_DETAIL
			exit();
		}

		$tblInvitation->invited_user_status = $statusCode;

		if(!$tblInvitation->store())
		{
			echo 500; // COM_IJOOMERADV_BESEATED_INVITATION_STATUS_NOT_CHANGED
			exit();
		}

		$invitedUserDetail = JFactory::getUser();

		if($statusCode == 11)
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			// Create the base select statement.
			$query->select('ticket_booking_detail_id ')
				->from($db->quoteName('#__beseated_event_ticket_booking_detail'))
				->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($tblTicketBookingDetail->ticket_booking_id))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($tblTicketBookingDetail->booking_user_id));
			
			// Set the query and load the result.
			$db->setQuery($query);
			
			$remainingTicket = $db->loadColumn();

			if(empty($remainingTicket))
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				
				// Create the base select statement.
				$query->select('invite_id')
					->from($db->quoteName('#__beseated_event_ticket_booking_invite'))
					->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($tblTicketBookingDetail->ticket_booking_id))
					->where($db->quoteName('invited_user_status') . ' != ' . $db->quote($statusCode));
				
				// Set the query and load the result.
				$db->setQuery($query);
				
				$pendingInvitation = $db->loadColumn();

				if(empty($pendingInvitation))
				{
					$tblTicketBooking = JTable::getInstance('TicketBooking', 'BeseatedTable');
					$tblTicketBooking->load($tblTicketBookingDetail->ticket_booking_id);

					$tblTicketBooking->user_id = 0;
					$tblTicketBooking->store();
				}
			}
		}

		if($statusCode == 11)
		{
			$notificationType = "event.invitation.accepted";

			/*$title = JText::sprintf(
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
					);*/

		}
		else if($statusCode == 6)
		{
			$tblTicketBookingDetail->user_id = $tblInvitation->user_id;
			$tblTicketBookingDetail->store();

			$notificationType = "event.invitation.declined";

			/*$title = JText::sprintf(
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
					);*/
		}
		

		/*$actor                         = $this->IJUserID;
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
		}*/

		echo 200;
		exit();
	}

	function cancelBooking()
	{
		$user = JFactory::getUser();

		if(!$user->id)
		{
			echo 704;
			exit();
		} // End of login Condition

		$input = JFactory::getApplication()->input;

		$bookingID   = $input->getInt('bookingID', 0);
		$bookingType = $input->getstring('bookingType', '');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		
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
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_DETAIL
			exit();
		}

		$tblService = JTable::getInstance($subElement, 'BeseatedTable');
		$tblService->load($tblElementBooking->$subElementID);
		$tblElement->load($tblElementBooking->$bookingElementIDField);

		$tblElementBooking->$bookingElementStatusField = BeseatedHelper::getStatusID('canceled');
		$tblElementBooking->$bookingUserStatusField    = BeseatedHelper::getStatusID('canceled');

		if($tblElementBooking->response_date_time == '0000-00-00 00:00:00')
		{
			$tblElementBooking->response_date_time = date('Y-m-d H:i:s');
		}

		if(!$tblElementBooking->store())
		{
			echo 500; // COM_IJOOMERADV_BESEATED_ERROR_WHILE_CANCEL_BOOKING
			exit();
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
					->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));

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

					// $this->ccAvenueRefund($tblOrderRefund); // jamal
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

		// new 28-09-2016
		/*if(strtolower($bookingType) == 'protection')
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

		$guestUserDetail = BeseatedHelper::guestUserDetail($user->id);

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
		$this->jsonarray['pushNotificationData']['configtype'] = '';*/

		echo 200;
		exit();
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

		$encrypted_data = BeseatedHelper::encrypt($customer_info,$encryptionKey);

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
		$result   = BeseatedHelper::decrypt($response[1], $encryptionKey);

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

	function cancelShareInvitation()
	{
		$user = JFactory::getUser();

		if(!$user->id)
		{
			echo 704;
			exit();
		} // End of login Condition

		$input = JFactory::getApplication()->input;

		$bookingID   = $input->getInt('bookingID', 0);
		$bookingType = $input->getstring('bookingType', '');

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
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_BOOKING_TYPE
			exit();
		}

		$tblElementBooking = JTable::getInstance($bookingTypeValue.'Booking', 'BeseatedTable');
		$tblElementBooking->load($bookingID);
		$tblElement        = JTable::getInstance($bookingTypeValue, 'BeseatedTable');
		$tblElement->load($tblElementBooking->$bookingElementIDField);

		if(!$tblElementBooking->$bookingTypeIDField)
		{
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_BOOKING_TYPE
			exit();
		}

		$statusID = BeseatedHelper::getStatusID('decline');

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
			->where($db->quoteName('a.user_id') . ' = ' . $db->quote($user->id))
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
			echo 400;  // COM_IJOOMERADV_BESEATED_INVALID_INVITATION_CANCEL
			exit();
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
			->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));

		// Set the query and execute the update.
		$db->setQuery($query);

	    if(!$db->execute())
	    {
	    	echo 500;
	    	exit();
	    }

		$guestUserDetail = BeseatedHelper::guestUserDetail($user->id);

		$this->deleteNotifOfCanceledInvitee($bookingID,$splitDetail->elementSplitID,$bookingType,$elementName);


		/*if(isset($tblElement->is_day_club) && $tblElement->is_day_club == 0)
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

		

		$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblElementBooking->$bookingTypeIDField);

		$this->jsonarray['pushNotificationData']['id']         = $tblElementBooking->$bookingElementIDField;
		$this->jsonarray['pushNotificationData']['elementType'] = ucfirst($elementType);
		$this->jsonarray['pushNotificationData']['to']         = $splitDetail->bookingOwnerID;
		$this->jsonarray['pushNotificationData']['message']    = $title;
		$this->jsonarray['pushNotificationData']['type']       = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype'] = '';

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;*/

		echo 200;
		exit();
	}

	function deleteNotifOfCanceledInvitee($bookingID,$invitationID,$bookingType,$elementName)
	{
		//echo "<pre/>";print_r($bookingType.'--'.$elementName);exit;
		$user = JFactory::getUser();

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->select('extra_pramas,notification_id')
			->from($db->quoteName('#__beseated_notification'))
			->where($db->quoteName('notification_type') . ' = ' . $db->quote(strtolower($bookingType).'.share.invitation.request'))
			->where($db->quoteName('target') . ' = ' . $db->quote((int) $user->id))
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

	function changeInvitationStatus()
	{
		$user = JFactory::getUser();

		if(!$user->id)
		{
			echo 704;
			exit();
		} // End of login Condition

		$input = JFactory::getApplication()->input;

		$invitationID   = $input->getInt('invitationID', 0);
		$statusCode     = $input->getInt('statusCode', 0);

		$possibleCode = array(6,9,10,11,12);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblInvitation = JTable::getInstance('Invitation', 'BeseatedTable');
		$tblInvitation->load($invitationID);

		if(!$tblInvitation->invitation_id)
		{
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_INVITATION_DETAIL
			exit();

		}

		if(!in_array($statusCode, $possibleCode))
		{
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_INVITATION_DETAIL
			exit();
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
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_INVITATION_DETAIL
			exit();
		}
		if(!$tblInvitation->store())
		{
			echo 500; // COM_IJOOMERADV_BESEATED_INVITATION_STATUS_NOT_CHANGED
			exit();
		}

		$tblElement = JTable::getInstance($bookingTypeValue, 'BeseatedTable');
		$tblElement->load($tblInvitation->element_id);

		$elementName      = strtolower($bookingTypeValue).'_name';
		$notificationType = strtolower($bookingType).".invitation.status.changed";
		$statusName       = BeseatedHelper::getStatusName($statusCode);
		$bookingType      = strtolower($bookingTypeValue);

		/*$elementManagerDetail = $this->helper->guestUserDetail($tblElement->user_id);

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
		}*/

		echo 200; 
		exit();

	}



}
