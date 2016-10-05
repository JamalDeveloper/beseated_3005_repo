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
 * The Beseated User Bookings Controller
 *
 * @since  0.0.1
 */
class BctedControllerUserbookings extends JControllerAdmin
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
	public function getModel($name = 'UserBookings', $prefix = 'BctedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function inviteUserInPackage()
	{
		$app               = JFactory::getApplication();
		$input             = $app->input;
		$packagePurchaseID = $input->get('package_purchase_id',0,'int');
		$usersEmail        = $input->get('invite_user','','string');
		$fbUsersEmail      = $input->get('fbids','','string');
		$Itemid            = $input->get('Itemid',0,'int');
		$menu              = $app->getMenu();
		$menuItem          = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userbookingdetail', true );

		if(!$packagePurchaseID)
		{
			$app->redirect('index.php?option=com_beseated&view=userbookingdetail&booking_type=package&purchase_id='.$packagePurchaseID.'&Itemid='.$Itemid,JText::_('COM_BCTED_INVITE_USER_LIST_EMPTY'));
			$app->close();
		}

		if(empty($fbUsersEmail) && empty($usersEmail))
		{
			$app->redirect('index.php?option=com_beseated&view=userbookingdetail&booking_type=package&purchase_id='.$packagePurchaseID.'&Itemid='.$Itemid,JText::_('COM_BCTED_INVITE_USER_LIST_EMPTY'));
			$app->close();
		}

		$proccessToEmail = "";
		if(!empty($fbUsersEmail))
		{
			$proccessToEmail = $fbUsersEmail;
		}

		if(!empty($usersEmail) && !empty($proccessToEmail))
		{
			$proccessToEmail = $proccessToEmail.",".$usersEmail;
		}
		else if (!empty($usersEmail) && empty($proccessToEmail))
		{
			$proccessToEmail = $usersEmail;
		}

		$proccessToEmailArray = explode(",", $proccessToEmail);
		$uniqueEmail          = array_unique($proccessToEmailArray);
		$usersEmail           = implode(',', $uniqueEmail);
		$model                = $this->getModel();
		$result               = $model->invite_user_in_package($packagePurchaseID,$usersEmail);
		$result               = explode("||", $result);

		if($result[0] == 101)
		{
			$app->redirect('index.php?option=com_beseated&view=userbookingdetail&booking_type=package&purchase_id='.$packagePurchaseID.'&Itemid='.$Itemid,JText::sprintf('COM_BCTED_INVITE_USER_GREATER_THEN_NUMBER_OF_GUEST',$result[1]));
			$app->close();
		}

		if($result[0] == 501)
		{
			$app->redirect('index.php?option=com_beseated&view=userbookingdetail&booking_type=package&purchase_id='.$packagePurchaseID.'&Itemid='.$Itemid,JText::_('COM_BESEATED_PACKAGE_INVITATION_TIME_IS_OVER'));
			$app->close();
		}

		if($result[0] == 200)
		{
			$app->redirect('index.php?option=com_beseated&view=userbookingdetail&booking_type=package&purchase_id='.$packagePurchaseID.'&Itemid='.$Itemid,JText::_('COM_BCTED_INVITE_USER_IN_MY_PURCHASED_PACKAGE_SUCCESS'));
			$app->close();
		}

		$app->redirect('index.php?option=com_beseated&view=userbookingdetail&booking_type=package&purchase_id='.$packagePurchaseID.'&Itemid='.$Itemid,JText::_('COM_BCTED_INVITE_USER_IN_MY_PURCHASED_PACKAGE_FAIL'));
		$app->close();
	}

	public function send_request_refund()
	{
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$Itemid    = $input->get('Itemid', 0, 'int');
		$bookingID = $input->get('booking_id', 0, 'int');
		$loginUser = JFactory::getUser();

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblPackagePurchased = JTable::getInstance('PackagePurchased', 'BctedTable');
		$tblPackagePurchased->load($bookingID);

		$tblVenue   = JTable::getInstance('Venue', 'BctedTable');
		$tblPackage = JTable::getInstance('Package', 'BctedTable');

		$tblVenue->load($tblPackagePurchased->venue_id);
		$tblPackage->load($tblPackagePurchased->package_id);
		$tblPackagePurchased->status = 10;
		$tblPackagePurchased->user_status = 10;

		$message     = JText::sprintf('PUSHNOTIFICATION_TYPE_PACKAGEREQUESTREFUND_MESSAGE',$loginUser->username,$tblPackage->package_name);
		$messageType = JText::_('PUSHNOTIFICATION_TYPE_PACKAGEREQUESTREFUND'); //'PackageRequestRefund';
		$link        = 'index.php?option=com_bcted&view=userbookings&type=packages&Itemid='.$Itemid;

		$tblPackagePurchased->is_request_refund = 1;

		if(!$tblPackagePurchased->store())
		{
			$app->redirect($link,JText::_('COM_BCTED_REQUEST_REFUND_NOT_DONE'));
			$app->close();
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_payment_status'))
			->where($db->quoteName('booked_element_id') . ' = ' . $db->quote($tblPackagePurchased->package_purchase_id))
			->where($db->quoteName('booked_element_type') . ' = ' . $db->quote('package'))
			->where($db->quoteName('paid_status') . ' = ' . $db->quote('1'));

		// Set the query and load the result.
		$db->setQuery($query);

		$paymentStatus = $db->loadObject();
		if($paymentStatus)
		{
			$queryLP = $db->getQuery(true);
			$queryLP->select('*')
				->from($db->quoteName('#__bcted_loyalty_point'))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($tblPackagePurchased->user_id))
				->where($db->quoteName('point_app') . ' = ' . $db->quote('purchase.package'))
				->where($db->quoteName('cid') . ' = ' . $db->quote($paymentStatus->payment_id));

			// Set the query and load the result.
			$db->setQuery($queryLP);

			$loyaltyPointDetail = $db->loadObject();

			if($loyaltyPointDetail)
			{
				$query = $db->getQuery(true);

				// Create the base insert statement.
				$query->insert($db->quoteName('#__bcted_loyalty_point'))
					->columns(
						array(
							$db->quoteName('user_id'),
							$db->quoteName('earn_point'),
							$db->quoteName('point_app'),
							$db->quoteName('cid'),
							$db->quoteName('is_valid'),
							$db->quoteName('created'),
							$db->quoteName('time_stamp')
						)
					)
					->values(
						$db->quote($tblPackagePurchased->user_id) . ', ' .
						$db->quote(($loyaltyPointDetail->earn_point * (-1))) . ', ' .
						$db->quote('package.refund') . ', ' .
						$db->quote($loyaltyPointDetail->cid) . ', ' .
						$db->quote(1) . ', ' .
						$db->quote(date('Y-m-d H:i:s')) . ', ' .
						$db->quote(time())
					);

				// Set the query and execute the insert.
				$db->setQuery($query);

				$db->execute();

			}
		}

		$queryPckgInvite = $db->getQuery(true);

		// Create the base select statement.
		$queryPckgInvite->select('*')
			->from($db->quoteName('#__bcted_package_invite'))
			->where($db->quoteName('package_purchase_id') . ' = ' . $db->quote($tblPackagePurchased->package_purchase_id));

		// Set the query and load the result.
		$db->setQuery($queryPckgInvite);
		$packageInvitations = $db->loadObjectList();
		foreach ($packageInvitations as $key => $invitation)
		{
			$sqlInvitePayment = $db->getQuery(true);
			$sqlInvitePayment->select('*')
				->from($db->quoteName('#__bcted_payment_status'))
				->where($db->quoteName('booked_element_id') . ' = ' . $db->quote($invitation->package_invite_id))
				->where($db->quoteName('booked_element_type') . ' = ' . $db->quote('packageinvitation'));

			// Set the query and load the result.
			$db->setQuery($sqlInvitePayment);

			$paymentInviteStatus = $db->loadObject();

			if($paymentInviteStatus)
			{
				$queryLP = $db->getQuery(true);
				$queryLP->select('*')
					->from($db->quoteName('#__bcted_loyalty_point'))
					->where($db->quoteName('user_id') . ' = ' . $db->quote($invitation->invited_user_id))
					->where($db->quoteName('point_app') . ' = ' . $db->quote('purchase.packageinvitation'))
					->where($db->quoteName('cid') . ' = ' . $db->quote($paymentInviteStatus->payment_id));

				// Set the query and load the result.
				$db->setQuery($queryLP);

				$loyaltyPointDetail = $db->loadObject();
				if($loyaltyPointDetail)
				{
					$query = $db->getQuery(true);

					// Create the base insert statement.
					$query->insert($db->quoteName('#__bcted_loyalty_point'))
						->columns(
							array(
								$db->quoteName('user_id'),
								$db->quoteName('earn_point'),
								$db->quoteName('point_app'),
								$db->quoteName('cid'),
								$db->quoteName('is_valid'),
								$db->quoteName('created'),
								$db->quoteName('time_stamp')
							)
						)
						->values(
							$db->quote($invitation->invited_user_id) . ', ' .
							$db->quote(($loyaltyPointDetail->earn_point * (-1))) . ', ' .
							$db->quote('packageinvitation.refund') . ', ' .
							$db->quote($loyaltyPointDetail->cid) . ', ' .
							$db->quote(1) . ', ' .
							$db->quote(date('Y-m-d H:i:s')) . ', ' .
							$db->quote(time())
						);

					// Set the query and execute the insert.
					$db->setQuery($query);
					$db->execute();
				}
			}
		}

		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/bcted/helper.php';
		$appHelper            = new bctedAppHelper;
		$bookingData = $appHelper->getBookingDetailForPackage($tblPackagePurchased->package_purchase_id,'Club');
		$pushcontentdata=array();
		$pushcontentdata['data'] = $bookingData;
		$pushcontentdata['elementType'] = "package";
		$pushOptions['detail']['content_data'] = $pushcontentdata;
		$pushOptions = gzcompress(json_encode($pushOptions));

		$db = JFactory::getDbo();

		$obj          = new stdClass();
		$obj->id      = null;
		$obj->detail  = $pushOptions;
		$obj->tocount = 1;
		$db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');

		if($packagePurchased->venue_id)
		{
			$pushUserID=$tblVenue->userid;
		}
		else if($packagePurchased->company_id)
		{
			$pushUserID=$tblCompany->userid;
		}

		$jsonarray['pushNotificationData']['id']         = $obj->id;
		$jsonarray['pushNotificationData']['to']         = $pushUserID;
		$jsonarray['pushNotificationData']['message']    = $message;
		$jsonarray['pushNotificationData']['type']       = $messageType;
		$jsonarray['pushNotificationData']['configtype'] = '';

		BctedHelper::sendPushNotification($jsonarray);

		$app->redirect($link,JText::_('COM_BCTED_REQUEST_REFUND_DONE'));
		$app->close();

	}

	public function send_invited_request_refund()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$packageInviteID = $input->get('package_invite_id',0,'int');
		$Itemid = $input->get('Itemid',0,'int');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblPackageInvite = JTable::getInstance('PackageInvite', 'BctedTable');
		$tblPackagePurchased = JTable::getInstance('PackagePurchased', 'BctedTable');
		$tblPackageInvite->load($packageInviteID);

		$link = "index.php?option=com_bcted&view=userpackageinvitedetail&package_invite_id=".$packageInviteID."&Itemid=".$Itemid;

		if(!$tblPackageInvite->package_invite_id)
		{
			$app->redirect($link,JText::_('COM_BCTED_REQUEST_REFUND_NOT_DONE'));
			$app->close();
		}

		$tblPackagePurchased->load($tblPackageInvite->package_purchase_id);

		if(!$tblPackagePurchased->package_purchase_id)
		{
			$app->redirect($link,JText::_('COM_BCTED_REQUEST_REFUND_NOT_DONE'));
			$app->close();
		}

		if($tblPackageInvite->status==10)
		{
			$app->redirect($link,JText::_('COM_BCTED_REQUEST_REFUND_NOT_DONE'));
			$app->close();
		}
		$bookingDT   = $tblPackagePurchased->package_datetime . ' ' . $tblPackagePurchased->package_time;
		$bookingTS   = strtotime($bookingDT);
		$before24    = strtotime("-24 hours", strtotime($bookingDT));
		$currentTime = time();

		if($currentTime<=$before24)
		{

		}
		else
		{
			$app->redirect($link,JText::_('COM_BCTED_REQUEST_REFUND_NOT_DONE'));
			$app->close();
		}

		$tblVenue                 = JTable::getInstance('Venue', 'BctedTable');
		$tblCompany               = JTable::getInstance('Company', 'BctedTable');
		$tblPackage               = JTable::getInstance('Package', 'BctedTable');
		$tblVenue->load($tblPackagePurchased->venue_id);
		$tblCompany->load($tblPackagePurchased->company_id);
		$tblPackage->load($tblPackagePurchased->package_id);
		$loginUser                = JFactory::getUser();
		$tblPackageInvite->status = 10;
		$message                  = JText::sprintf('PUSHNOTIFICATION_TYPE_PACKAGEREQUESTREFUND_MESSAGE',$loginUser->username,$tblPackage->package_name);
		$messageType              = JText::_('PUSHNOTIFICATION_TYPE_PACKAGEREQUESTREFUND'); //'PackageRequestRefund';

		if(!$tblPackageInvite->store())
		{
			$app->redirect($link,JText::_('COM_BCTED_REQUEST_REFUND_NOT_DONE'));
			$app->close();
		}

		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/bcted/helper.php';
		$appHelper            = new bctedAppHelper;

		$bookingData = $appHelper->getBookingDetailForPackage($tblPackagePurchased->package_purchase_id,'Club');
		$pushcontentdata=array();
		$pushcontentdata['data'] = $bookingData;
		$pushcontentdata['elementType'] = "package";
		$pushOptions['detail']['content_data'] = $pushcontentdata;
		$pushOptions = gzcompress(json_encode($pushOptions));
		$db = JFactory::getDbo();
		$obj          = new stdClass();
		$obj->id      = null;
		$obj->detail  = $pushOptions;
		$obj->tocount = 1;
		$db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');

		if($packagePurchased->venue_id)
		{
			$pushUserID=$tblVenue->userid;
		}
		else if($packagePurchased->company_id)
		{
			$pushUserID=$tblCompany->userid;
		}

		$jsonarray['pushNotificationData']['id']         = $obj->id;
		$jsonarray['pushNotificationData']['to']         = $pushUserID;
		$jsonarray['pushNotificationData']['message']    = $message;
		$jsonarray['pushNotificationData']['type']       = $messageType;
		$jsonarray['pushNotificationData']['configtype'] = '';

		BctedHelper::sendPushNotification($jsonarray);

		$app->redirect($link,JText::_('COM_BCTED_REQUEST_REFUND_DONE'));
		$app->close();
	}

	public function refundBooking()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$bookingID   = $input->get('booking_id',0,'int');
		$bookingType = $input->get('bookingType','','string');
		$Itemid      = $input->get('Itemid',0,'int');

		if($bookingType == 'venue')
		{
			$tblBooking = JTable::getInstance('Venuebooking','BctedTable');
			$tblBooking->load($bookingID);

			if(!$tblBooking->venue_booking_id)
			{
				$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid);
			}
		}
		else if($bookingType == 'service')
		{
			$tblBooking = JTable::getInstance('ServiceBooking','BctedTable');
			$tblBooking->load($bookingID);

			if(!$tblBooking->service_booking_id)
			{
				$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid);
			}
		}
		else
		{
			$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid);
		}

		$tblBooking->status      = 10;
		$tblBooking->user_status = 10;
		$tblBooking->is_new      = 0;
		$userID                  = $tblBooking->user_id;

		$tblBooking->store();
		$this->decreasLoyalty($bookingID,$bookingType,$userID,'refund');

		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/bcted/helper.php';
		$appHelper            = new bctedAppHelper;
		$user = JFactory::getUser();

		if($bookingType == 'venue')
		{
			$newBookingID  = $tblBooking->venue_booking_id;
			$bookingData   = $appHelper->getBookingDetailForVenueTable($newBookingID,'Venue');
			$tblTable      = JTable::getInstance('Table', 'BctedTable');
			$tblTable->load($tblBooking->venue_table_id);
			$tblVenue      = JTable::getInstance('Venue', 'BctedTable');
			$tblVenue->load($tblBooking->venue_id);
			$venueID       = $tblVenue->venue_id;
			$tableID       = $tblTable->venue_table_id;
			$companyID     = 0;
			$serviceID     = 0;
			$toUserID      = $tblVenue->userid;
			$messageType   = "tablebookingcancel";
			$refundMessage = JText::sprintf('COM_BESEATED_COMPANY_VENUE_BOOKING_REFUND_MESSAGE',$user->name,($tblTable->premium_table_id)?$tblTable->venue_table_name:$tblTable->custom_table_name);
			$messageType   = JText::_('PUSHNOTIFICATION_TYPE_TABLEBOOKINGREFUND');
		}
		else if($bookingType == 'service')
		{
			$newBookingID = $tblBooking->service_booking_id;
			$bookingData = $appHelper->getBookingDetailForServices($newBookingID,'Company');

			$tblService = JTable::getInstance('Service', 'BctedTable');
			$tblService->load($tblBooking->service_id);

			$tblCompany = JTable::getInstance('Company', 'BctedTable');
			$tblCompany->load($tblBooking->company_id);

			$venueID       = 0;
			$tableID       = 0;
			$companyID     = $tblCompany->company_id;
			$serviceID     = $tblService->service_id;
			$toUserID      = $tblCompany->userid;
			$messageType   = "servicebookingcancel";
			$refundMessage = JText::sprintf('COM_BESEATED_COMPANY_SERVICE_BOOKING_REFUND_MESSAGE',$user->name,$tblService->service_name);
			$messageType   = JText::_('PUSHNOTIFICATION_TYPE_SERVICEBOOKINGREFUND');
		}

		$appHelper->sendMessage($venueID,$companyID,$serviceID,$tableID,$toUserID,$refundMessage,$bookingData,$messageType);
		$pushcontentdata=array();
		$pushcontentdata['data'] = $bookingData;

		if($bookingType == 'venue')
		{
			$pushcontentdata['elementType'] = "venue";
		}
		else if($bookingType == 'service')
		{
			$pushcontentdata['elementType'] = "company";
		}

		$pushOptions['detail']['content_data'] = $pushcontentdata;
		$pushOptions = gzcompress(json_encode($pushOptions));

		$db = JFactory::getDbo();

		$obj          = new stdClass();
		$obj->id      = null;
		$obj->detail  = $pushOptions;
		$obj->tocount = 1;
		$db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');

		if($obj->id)
		{
			$userProfile    = $appHelper->getUserProfile($toUserID);
			$receiveRequest = 0;

			if($userProfile)
			{
				$params         = json_decode($userProfile->params);
				$receiveRequest = $params->settings->pushNotification->receiveRequest;
			}

			$receiveRequest = 1;

			if($receiveRequest)
			{
				$message = $refundMessage;
				$jsonarray['pushNotificationData']['id']         = $obj->id;
				$jsonarray['pushNotificationData']['to']         = $toUserID;
				$jsonarray['pushNotificationData']['message']    = $message;
				$jsonarray['pushNotificationData']['type']       = $messageType;
				$jsonarray['pushNotificationData']['configtype'] = '';

				BctedHelper::sendPushNotification($jsonarray);
			}
		}

		$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid);
	}

	public function refundBooking_ajax()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$bookingID   = $input->get('booking_id',0,'int');
		$bookingType = $input->get('booking_type','','string');

		if($bookingType == 'venue')
		{
			$tblBooking = JTable::getInstance('Venuebooking','BctedTable');
			$tblBooking->load($bookingID);

			if(!$tblBooking->venue_booking_id)
			{
				echo "400";
				exit;
			}
		}
		else if($bookingType == 'service')
		{
			$tblBooking = JTable::getInstance('ServiceBooking','BctedTable');
			$tblBooking->load($bookingID);

			if(!$tblBooking->service_booking_id)
			{
				echo "400";
				exit;
			}
		}
		else
		{
			echo "400";
			exit;
		}

		$tblBooking->status      = 10;
		$tblBooking->user_status = 10;
		$tblBooking->is_new      = 0;
		$userID                  = $tblBooking->user_id;

		$tblBooking->store();
		$this->decreasLoyalty($bookingID,$bookingType,$userID,'refund');

		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/bcted/helper.php';
		$appHelper            = new bctedAppHelper;
		$user = JFactory::getUser();

		if($bookingType == 'venue')
		{
			$newBookingID  = $tblBooking->venue_booking_id;
			$bookingData   = $appHelper->getBookingDetailForVenueTable($newBookingID,'Venue');
			$tblTable      = JTable::getInstance('Table', 'BctedTable');
			$tblTable->load($tblBooking->venue_table_id);
			$tblVenue      = JTable::getInstance('Venue', 'BctedTable');
			$tblVenue->load($tblBooking->venue_id);
			$venueID       = $tblVenue->venue_id;
			$tableID       = $tblTable->venue_table_id;
			$companyID     = 0;
			$serviceID     = 0;
			$toUserID      = $tblVenue->userid;
			$messageType   = "tablebookingcancel";
			$refundMessage = JText::sprintf('COM_BESEATED_COMPANY_VENUE_BOOKING_REFUND_MESSAGE',$user->name,($tblTable->premium_table_id)?$tblTable->venue_table_name:$tblTable->custom_table_name);
			$messageType   = JText::_('PUSHNOTIFICATION_TYPE_TABLEBOOKINGREFUND');
		}
		else if($bookingType == 'service')
		{
			$newBookingID = $tblBooking->service_booking_id;
			$bookingData = $appHelper->getBookingDetailForServices($newBookingID,'Company');

			$tblService = JTable::getInstance('Service', 'BctedTable');
			$tblService->load($tblBooking->service_id);

			$tblCompany = JTable::getInstance('Company', 'BctedTable');
			$tblCompany->load($tblBooking->company_id);

			$venueID       = 0;
			$tableID       = 0;
			$companyID     = $tblCompany->company_id;
			$serviceID     = $tblService->service_id;
			$toUserID      = $tblCompany->userid;
			$messageType   = "servicebookingcancel";
			$refundMessage = JText::sprintf('COM_BESEATED_COMPANY_SERVICE_BOOKING_REFUND_MESSAGE',$user->name,$tblService->service_name);
			$messageType   = JText::_('PUSHNOTIFICATION_TYPE_SERVICEBOOKINGREFUND');
		}

		$appHelper->sendMessage($venueID,$companyID,$serviceID,$tableID,$toUserID,$refundMessage,$bookingData,$messageType);

		$pushcontentdata=array();
		$pushcontentdata['data'] = $bookingData;

		if($bookingType == 'venue')
		{
			$pushcontentdata['elementType'] = "venue";
		}
		else if($bookingType == 'service')
		{
			$pushcontentdata['elementType'] = "company";
		}

		$pushOptions['detail']['content_data'] = $pushcontentdata;
		$pushOptions = gzcompress(json_encode($pushOptions));

		$db = JFactory::getDbo();

		$obj          = new stdClass();
		$obj->id      = null;
		$obj->detail  = $pushOptions;
		$obj->tocount = 1;
		$db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');

		if($obj->id)
		{
			$userProfile    = $appHelper->getUserProfile($toUserID);
			$receiveRequest = 0;

			if($userProfile)
			{
				$params         = json_decode($userProfile->params);
				$receiveRequest = $params->settings->pushNotification->receiveRequest;
			}

			$receiveRequest = 1;

			if($receiveRequest)
			{
				$message = $refundMessage;
				$jsonarray['pushNotificationData']['id']         = $obj->id;
				$jsonarray['pushNotificationData']['to']         = $toUserID;
				$jsonarray['pushNotificationData']['message']    = $message;
				$jsonarray['pushNotificationData']['type']       = $messageType;
				$jsonarray['pushNotificationData']['configtype'] = '';

				BctedHelper::sendPushNotification($jsonarray);
			}
		}
		echo "200";
		exit;
	}

	public function cancelBooking()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$bookingID   = $input->get('booking_id',0,'int');
		$bookingType = $input->get('bookingType','','string');
		$Itemid      = $input->get('Itemid',0,'int');

		if($bookingType == 'venue')
		{
			$tblBooking = JTable::getInstance('Venuebooking','BctedTable');
			$tblBooking->load($bookingID);

			if(!$tblBooking->venue_booking_id)
			{
				$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid);
			}
		}
		else if($bookingType == 'service')
		{
			$tblBooking = JTable::getInstance('ServiceBooking','BctedTable');
			$tblBooking->load($bookingID);

			if(!$tblBooking->service_booking_id)
			{
				$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid);
			}
		}
		else
		{
			$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid);
		}

		$tblBooking->status      = 11;
		$tblBooking->user_status = 11;
		$tblBooking->is_new      = 0;
		$userID                  = $tblBooking->user_id;

		$tblBooking->store();
		$this->decreasLoyalty($bookingID,$bookingType,$userID,'cancelbooking');

		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/bcted/helper.php';
		$appHelper            = new bctedAppHelper;
		$user = JFactory::getUser();

		if($bookingType == 'venue')
		{
			$newBookingID  = $tblBooking->venue_booking_id;
			$bookingData   = $appHelper->getBookingDetailForVenueTable($newBookingID,'Venue');
			$tblTable      = JTable::getInstance('Table', 'BctedTable');
			$tblTable->load($tblBooking->venue_table_id);
			$tblVenue      = JTable::getInstance('Venue', 'BctedTable');
			$tblVenue->load($tblBooking->venue_id);
			$venueID       = $tblVenue->venue_id;
			$tableID       = $tblTable->venue_table_id;
			$companyID     = 0;
			$serviceID     = 0;
			$toUserID      = $tblVenue->userid;
			$messageType   = "tablebookingcancel";
			$cancelMessage = JText::sprintf('COM_BESEATED_COMPANY_VENUE_BOOKING_CANCEL_MESSAGE',$user->name,($tblTable->premium_table_id)?$tblTable->venue_table_name:$tblTable->custom_table_name);
			$messageType   = JText::_('PUSHNOTIFICATION_TYPE_TABLEBOOKINGCANCEL');

		}
		else if($bookingType == 'service')
		{
			$newBookingID = $tblBooking->service_booking_id;
			$bookingData = $appHelper->getBookingDetailForServices($newBookingID,'Company');

			$tblService = JTable::getInstance('Service', 'BctedTable');
			$tblService->load($tblBooking->service_id);

			$tblCompany = JTable::getInstance('Company', 'BctedTable');
			$tblCompany->load($tblBooking->company_id);

			$venueID       = 0;
			$tableID       = 0;
			$companyID     = $tblCompany->company_id;
			$serviceID     = $tblService->service_id;
			$toUserID      = $tblCompany->userid;
			$messageType   = "servicebookingcancel";
			$cancelMessage = JText::sprintf('COM_BESEATED_COMPANY_SERVICE_BOOKING_CANCEL_MESSAGE',$user->name,$tblService->service_name);
			$messageType   = JText::_('PUSHNOTIFICATION_TYPE_SERVICEBOOKINGCANCEL');
		}

		$appHelper->sendMessage($venueID,$companyID,$serviceID,$tableID,$toUserID,$cancelMessage,$bookingData,$messageType);

		$pushcontentdata=array();
		$pushcontentdata['data'] = $bookingData;

		if($bookingType == 'venue')
		{
			$pushcontentdata['elementType'] = "venue";
		}
		else if($bookingType == 'service')
		{
			$pushcontentdata['elementType'] = "company";
		}

		$pushOptions['detail']['content_data'] = $pushcontentdata;
		$pushOptions = gzcompress(json_encode($pushOptions));

		$db = JFactory::getDbo();

		$obj          = new stdClass();
		$obj->id      = null;
		$obj->detail  = $pushOptions;
		$obj->tocount = 1;
		$db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');

		if($obj->id)
		{
			$userProfile    = $appHelper->getUserProfile($toUserID);
			$receiveRequest = 0;

			if($userProfile)
			{
				$params         = json_decode($userProfile->params);
				$receiveRequest = $params->settings->pushNotification->receiveRequest;
			}

			$receiveRequest = 1;

			if($receiveRequest)
			{
				$message = $cancelMessage;
				$jsonarray['pushNotificationData']['id']         = $obj->id;
				$jsonarray['pushNotificationData']['to']         = $toUserID;
				$jsonarray['pushNotificationData']['message']    = $message;
				$jsonarray['pushNotificationData']['type']       = $messageType;
				$jsonarray['pushNotificationData']['configtype'] = '';

				BctedHelper::sendPushNotification($jsonarray);
			}
		}

		$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid);
	}

	public function cancelBooking_ajax()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		$bookingID   = $input->get('booking_id',0,'int');
		$bookingType = $input->get('booking_type','','string');

		if($bookingType == 'venue')
		{
			$tblBooking = JTable::getInstance('Venuebooking','BctedTable');
			$tblBooking->load($bookingID);

			if(!$tblBooking->venue_booking_id)
			{
				echo "400";
				exit;
			}
		}
		else if($bookingType == 'service')
		{
			$tblBooking = JTable::getInstance('ServiceBooking','BctedTable');
			$tblBooking->load($bookingID);

			if(!$tblBooking->service_booking_id)
			{
				echo "400";
				exit;
			}
		}
		else
		{
			echo "400";
			exit;
		}

		$tblBooking->status      = 11;
		$tblBooking->user_status = 11;
		$tblBooking->is_new      = 0;
		$userID                  = $tblBooking->user_id;

		$tblBooking->store();
		$this->decreasLoyalty($bookingID,$bookingType,$userID,'cancelbooking');

		$message = 'This booking has been cancelled.';
		$title = 'Success';
		BctedHelper::setBeseatedSessionMessage($message,$title);

		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/bcted/helper.php';
		$appHelper            = new bctedAppHelper;
		$user = JFactory::getUser();

		if($bookingType == 'venue')
		{
			$newBookingID  = $tblBooking->venue_booking_id;
			$bookingData   = $appHelper->getBookingDetailForVenueTable($newBookingID,'Venue');
			$tblTable      = JTable::getInstance('Table', 'BctedTable');
			$tblTable->load($tblBooking->venue_table_id);
			$tblVenue      = JTable::getInstance('Venue', 'BctedTable');
			$tblVenue->load($tblBooking->venue_id);
			$venueID       = $tblVenue->venue_id;
			$tableID       = $tblTable->venue_table_id;
			$companyID     = 0;
			$serviceID     = 0;
			$toUserID      = $tblVenue->userid;
			$messageType   = "tablebookingcancel";
			$cancelMessage = JText::sprintf('COM_BESEATED_COMPANY_VENUE_BOOKING_CANCEL_MESSAGE',$user->name,($tblTable->premium_table_id)?$tblTable->venue_table_name:$tblTable->custom_table_name);
			$messageType   = JText::_('PUSHNOTIFICATION_TYPE_TABLEBOOKINGCANCEL');

		}
		else if($bookingType == 'service')
		{
			$newBookingID = $tblBooking->service_booking_id;
			$bookingData = $appHelper->getBookingDetailForServices($newBookingID,'Company');

			$tblService = JTable::getInstance('Service', 'BctedTable');
			$tblService->load($tblBooking->service_id);

			$tblCompany = JTable::getInstance('Company', 'BctedTable');
			$tblCompany->load($tblBooking->company_id);

			$venueID       = 0;
			$tableID       = 0;
			$companyID     = $tblCompany->company_id;
			$serviceID     = $tblService->service_id;
			$toUserID      = $tblCompany->userid;
			$messageType   = "servicebookingcancel";
			$cancelMessage = JText::sprintf('COM_BESEATED_COMPANY_SERVICE_BOOKING_CANCEL_MESSAGE',$user->name,$tblService->service_name);
			$messageType   = JText::_('PUSHNOTIFICATION_TYPE_SERVICEBOOKINGCANCEL');
		}

		$appHelper->sendMessage($venueID,$companyID,$serviceID,$tableID,$toUserID,$cancelMessage,$bookingData,$messageType);

		$pushcontentdata=array();
		$pushcontentdata['data'] = $bookingData;

		if($bookingType == 'venue')
		{
			$pushcontentdata['elementType'] = "venue";
		}
		else if($bookingType == 'service')
		{
			$pushcontentdata['elementType'] = "company";
		}

		$pushOptions['detail']['content_data'] = $pushcontentdata;
		$pushOptions = gzcompress(json_encode($pushOptions));

		$db = JFactory::getDbo();

		$obj          = new stdClass();
		$obj->id      = null;
		$obj->detail  = $pushOptions;
		$obj->tocount = 1;
		$db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');

		if($obj->id)
		{
			$userProfile    = $appHelper->getUserProfile($toUserID);
			$receiveRequest = 0;

			if($userProfile)
			{
				$params         = json_decode($userProfile->params);
				$receiveRequest = $params->settings->pushNotification->receiveRequest;
			}

			$receiveRequest = 1;

			if($receiveRequest)
			{
				$message = $cancelMessage;
				$jsonarray['pushNotificationData']['id']         = $obj->id;
				$jsonarray['pushNotificationData']['to']         = $toUserID;
				$jsonarray['pushNotificationData']['message']    = $message;
				$jsonarray['pushNotificationData']['type']       = $messageType;
				$jsonarray['pushNotificationData']['configtype'] = '';

				BctedHelper::sendPushNotification($jsonarray);
			}
		}

		echo "200";
		exit;
	}

	public function cancelPacakgeBooking()
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$input = $app->input;

		$packageBookingID = $input->get('package_booking_id',0,'int');
		$Itemid = $input->get('Itemid',0,'int');

		$tblPackagePurchased = JTable::getInstance('PackagePurchased', 'BctedTable');
		$tblPackage = JTable::getInstance('Package', 'BctedTable');
		$tblPackagePurchased->load($packageBookingID);

		if(!$tblPackagePurchased->package_purchase_id)
		{
			$app->redirect('index.php?option=com_bcted&view=userbookingdetail&booking_type=package&purchase_id='.$tblPackagePurchased->package_purchase_id.'&Itemid='.$Itemid);
		}

		$tblPackage->load($tblPackagePurchased->package_id);

		$tblPackagePurchased->status      = 11;
		$tblPackagePurchased->user_status = 11;

		if(!$tblPackagePurchased->store())
		{
			$app->redirect('index.php?option=com_bcted&view=userbookingdetail&booking_type=package&purchase_id='.$tblPackagePurchased->package_purchase_id.'&Itemid='.$Itemid);
		}

		$this->sendMessageForPackageServiceCancelled($tblPackagePurchased->package_id,$tblPackagePurchased->package_purchase_id);

		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/bcted/helper.php';
		$appHelper            = new bctedAppHelper;

		$bookingData                           = $appHelper->getBookingDetailForPackage($tblPackagePurchased->package_purchase_id,'Club');
		$pushcontentdata                       = array();
		$pushcontentdata['data']               = $bookingData;
		$pushcontentdata['elementType']        = "package";
		$pushOptions['detail']['content_data'] = $pushcontentdata;
		$pushOptions                           = gzcompress(json_encode($pushOptions));
		$pushUserID                            = 0;
		$obj                                   = new stdClass();
		$obj->id                               = null;
		$obj->detail                           = $pushOptions;
		$obj->tocount                          = 1;
		$db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');

		if($obj->id)
		{
			if($tblPackagePurchased->venue_id)
			{
				$tblVenue      = JTable::getInstance('Venue', 'BctedTable');
				$tblVenue->load($tblPackagePurchased->venue_id);
				$userProfile   = $appHelper->getUserProfile($tblVenue->userid);
				$userIDForPush = $tblVenue->userid;
			}

			$receiveRequest = 0;

			if($userProfile)
			{
				$params = json_decode($userProfile->params);
				$receiveRequest = $params->settings->pushNotification->receiveRequest;
			}

			$message     = JText::sprintf('PUSHNOTIFICATION_TYPE_PACKAGECANELLED_MESSAGE',$tblPackage->package_name,$userProfile->first_name.' '.$userProfile->last_name);
			$messageType = JText::_('PUSHNOTIFICATION_TYPE_PACKAGECANELLED'); //'PackageRequestRefund';

			$appHelper->sendMessage($tblVenue->venue_id,0,0,0,$tblVenue->userid,$message,array(),'packageservicebookingrecancelled');

			if($receiveRequest)
			{
				$jsonarray['pushNotificationData']['id']         = $obj->id;
				$jsonarray['pushNotificationData']['to']         = $userIDForPush;
				$jsonarray['pushNotificationData']['message']    = $message;
				$jsonarray['pushNotificationData']['type']       = $messageType;
				$jsonarray['pushNotificationData']['configtype'] = '';

				BctedHelper::sendPushNotification($jsonarray);
			}
		}

		$app->redirect('index.php?option=com_bcted&view=userbookingdetail&booking_type=package&purchase_id='.$tblPackagePurchased->package_purchase_id.'&Itemid='.$Itemid);
	}

	public function cancelPacakgeBooking_ajax()
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$input = $app->input;

		$packageBookingID = $input->get('package_booking_id',0,'int');
		$Itemid = $input->get('Itemid',0,'int');

		$tblPackagePurchased = JTable::getInstance('PackagePurchased', 'BctedTable');
		$tblPackage = JTable::getInstance('Package', 'BctedTable');
		$tblPackagePurchased->load($packageBookingID);

		if(!$tblPackagePurchased->package_purchase_id)
		{
			echo "400";
			exit;
		}

		$tblPackage->load($tblPackagePurchased->package_id);

		$tblPackagePurchased->status      = 11;
		$tblPackagePurchased->user_status = 11;

		if(!$tblPackagePurchased->store())
		{
			echo "500";
			exit;
		}

		$this->sendMessageForPackageServiceCancelled($tblPackagePurchased->package_id,$tblPackagePurchased->package_purchase_id);

		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/bcted/helper.php';
		$appHelper            = new bctedAppHelper;

		$bookingData                           = $appHelper->getBookingDetailForPackage($tblPackagePurchased->package_purchase_id,'Club');
		$pushcontentdata                       = array();
		$pushcontentdata['data']               = $bookingData;
		$pushcontentdata['elementType']        = "package";
		$pushOptions['detail']['content_data'] = $pushcontentdata;
		$pushOptions                           = gzcompress(json_encode($pushOptions));
		$pushUserID                            = 0;
		$obj                                   = new stdClass();
		$obj->id                               = null;
		$obj->detail                           = $pushOptions;
		$obj->tocount                          = 1;
		$db->insertObject('#__ijoomeradv_push_notification_data',$obj,'id');

		if($obj->id)
		{
			if($tblPackagePurchased->venue_id)
			{
				$tblVenue      = JTable::getInstance('Venue', 'BctedTable');
				$tblVenue->load($tblPackagePurchased->venue_id);
				$userProfile   = $appHelper->getUserProfile($tblVenue->userid);
				$userIDForPush = $tblVenue->userid;
			}

			$receiveRequest = 0;

			if($userProfile)
			{
				$params = json_decode($userProfile->params);
				$receiveRequest = $params->settings->pushNotification->receiveRequest;
			}

			$message     = JText::sprintf('PUSHNOTIFICATION_TYPE_PACKAGECANELLED_MESSAGE',$tblPackage->package_name,$userProfile->first_name.' '.$userProfile->last_name);
			$messageType = JText::_('PUSHNOTIFICATION_TYPE_PACKAGECANELLED'); //'PackageRequestRefund';

			$appHelper->sendMessage($tblVenue->venue_id,0,0,0,$tblVenue->userid,$message,array(),'packageservicebookingrecancelled');

			if($receiveRequest)
			{
				$jsonarray['pushNotificationData']['id']         = $obj->id;
				$jsonarray['pushNotificationData']['to']         = $userIDForPush;
				$jsonarray['pushNotificationData']['message']    = $message;
				$jsonarray['pushNotificationData']['type']       = $messageType;
				$jsonarray['pushNotificationData']['configtype'] = '';

				BctedHelper::sendPushNotification($jsonarray);
			}
		}

		$message = 'This booking has been Cancelled';
		$title = 'Success';
		BctedHelper::setBeseatedSessionMessage($message,$title);
		echo "200";
		exit;
	}

	public function sendMessageForPackageServiceCancelled($packageID,$BookedID)
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/bcted/helper.php';
		$appHelper            = new bctedAppHelper;

		$tblPackagePurchased = JTable::getInstance('PackagePurchased', 'BctedTable',array());
		$tblPackagePurchased->load($BookedID);
		$tblPackage = JTable::getInstance('Package', 'BctedTable',array());
		$tblPackage->load($packageID);

		$serviceIDs = $tblPackage->company_ids;
		$companyArray= array();
		if(!empty($serviceIDs))
		{
			$serviceArray = explode(",", $serviceIDs);
			foreach ($serviceArray as $key => $service)
			{
				$tblService = JTable::getInstance('Service', 'BctedTable',array());
				$tblService->load($service);
				if($tblService->service_id)
				{
					$tmpArray['serviceName'] = $tblService->service_name;
					$companyArray[$tblService->company_id][] = $tmpArray;
				}
			}

			if(count($companyArray)!=0)
			{
				foreach ($companyArray as $companyID => $servicesArray)
				{
					$servicesNames = array();
					foreach ($servicesArray as $key => $services)
					{
						$servicesNames[] = $services['serviceName'];
					}

					$tblCompany = JTable::getInstance('Company', 'BctedTable',array());
					$tblCompany->load($companyID);
					$user = JFactory::getUser();
					$message = $user->name . ' has cancelled their package for ' . implode(",", $servicesNames) . ' on ' . date('d-m-Y',strtotime($tblPackagePurchased->package_datetime));

					$appHelper->sendMessage(0,$tblCompany->company_id,0,0,$tblCompany->userid,$message,array(),'packageservicebookingrecancelled');

					$pushData = array();
					$pushData['pushNotificationData']['id']      = "1";
					$pushData['pushNotificationData']['to']      = $tblCompany->userid;
					$pushData['pushNotificationData']['message'] = $message;
					$pushData['pushNotificationData']['type']    = JText::_('PUSHNOTIFICATION_TYPE_PACKAGECANELLED_FOR_SERVICE');
					BctedHelper::sendPushNotification($pushData);
				}
			}
		}
	}

	public function inviteToMyBookedTable()
	{
		$user = JFactory::getUser();

		$app             = JFactory::getApplication();
		$input           = $app->input;
		$bookingID       = $input->get('booking_id',0,'int');
		$bookingType     = $input->get('bookingType','','string');
		$Itemid          = $input->get('Itemid',0,'int');
		$usersEmail      = $input->get('invite_user','','string');
		$fbUsersEmail    = $input->get('fbids','','string');
		$proccessToEmail = "";
		if(!empty($fbUsersEmail))
		{
			$proccessToEmail = $fbUsersEmail;
		}

		if($bookingType == 'venue')
		{
			$bookingType = 'table';
		}

		if(!empty($usersEmail) && !empty($proccessToEmail))
		{
			$proccessToEmail = $proccessToEmail.",".$usersEmail;
		}
		else if (!empty($usersEmail) && empty($proccessToEmail))
		{
			$proccessToEmail = $usersEmail;
		}

		$proccessToEmailArray = explode(",", $proccessToEmail);
		$uniqueEmail = array_unique($proccessToEmailArray);
		$emails = implode(',', $uniqueEmail);

		$enterEmails=$emails;
		$seletedEmails=$emails;

		if(!$bookingID)
		{
			$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid,JText::_('COM_BESEATED_VENUE_TABLE_INVITE_DETAIL_NOT_VALID'));
			$app->close();
		}

		if(empty($emails))
		{
			$app->redirect('index.php?option=com_bcted&view=userbookingdetail&booking_type='.$bookingType.'&booking_id='.$bookingID.'&Itemid='.$Itemid,JText::_('COM_BESEATED_VENUE_TABLE_INVITE_DETAIL_NOT_VALID'));
			$app->close();
		}

		$bctRegiEmails = BctedHelper::filterEmails($emails);

		$emailsArray = explode(",", $emails);
		$filterEmails = array();

		foreach ($emailsArray as $key => $singleEmail)
		{
			if(in_array($singleEmail, $bctRegiEmails['allRegEmail']))
			{
				if(in_array($singleEmail, $bctRegiEmails['service'])){ continue; }
				if(in_array($singleEmail, $bctRegiEmails['venue'])){ continue; }
				if(in_array($singleEmail, $bctRegiEmails['guest']) && $user->email == $singleEmail){ continue; }
			}

			$filterEmails[] = $singleEmail;
		}

		if(count($filterEmails)==0)
		{
			$app->redirect('index.php?option=com_bcted&view=userbookingdetail&booking_type='.$bookingType.'&booking_id='.$bookingID.'&Itemid='.$Itemid,JText::_('COM_BESEATED_VENUE_TABLE_INVITE_NOT_VALID_SELF_EMAIL_OR_CLUB_SERVICE'));
			$app->close();
		}

		$seletedEmails = implode(',', $filterEmails);
		$emails        = $seletedEmails;
		$enterEmails   = $emails;

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblVenue            = JTable::getInstance('Venue', 'BctedTable');
		$tblTable            = JTable::getInstance('Table', 'BctedTable');
		$tblVenuebooking     = JTable::getInstance('Venuebooking', 'BctedTable');
		$tblVenuetableinvite = JTable::getInstance('Venuetableinvite','BctedTable');

		$loginUser = JFactory::getUser();
		$tblVenuebooking->load($bookingID);
		$tblVenue->load($tblVenuebooking->venue_id);
		$tblTable->load($tblVenuebooking->venue_table_id);

		if(!$tblVenuebooking->venue_booking_id || !$tblVenue->venue_id || !$tblTable->venue_table_id)
		{
			$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid,JText::_('COM_BESEATED_VENUE_TABLE_INVITE_DETAIL_NOT_VALID'));
			$app->close();
		}

		if($tblTable->premium_table_id)
		{
			$tableName = $tblTable->venue_table_name;
		}
		else
		{
			$tableName = $tblTable->custom_table_name;
		}

		if(!empty($emails))
		{
			$emails = explode(",", $emails);
			$emails = implode("','", $emails);
			$emails = "'".$emails."'";

			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('id')
				->from($db->quoteName('#__users'))
				->where($db->quoteName('email') . ' IN (' . $emails . ')')
				->where($db->quoteName('block') . ' = ' . $db->quote(0));

			// Set the query and load the result.
			$db->setQuery($query);

			$foundUsers = $db->loadColumn();

			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('email')
				->from($db->quoteName('#__users'))
				->where($db->quoteName('email') . ' IN (' . $emails . ')')
				->where($db->quoteName('block') . ' = ' . $db->quote(0));

			// Set the query and load the result.
			$db->setQuery($query);

			$foundEmails = $db->loadColumn();
		}

		if(count($foundUsers)!=0)
		{
			$bookingDateTime = date('d-m-Y H:i',strtotime($tblVenuebooking->venue_booking_datetime.' '.$tblVenuebooking->booking_from_time));
			$message = JText::sprintf('COM_BESEATED_VENUE_INVITE_TO_MY_BOOKING_TABLE_PUSHNOTIFICATION_MESSAGE',$user->name,$tblVenue->venue_name,$bookingDateTime);
			$jsonarray['pushNotificationData']['id']         = $bookingID;
			$jsonarray['pushNotificationData']['to']         = implode(",", $foundUsers);;
			$jsonarray['pushNotificationData']['message']    = $message;
			$jsonarray['pushNotificationData']['type']       = JText::_('PUSHNOTIFICATION_TYPE_BOOKEDTABLEINVITETOUSER');
			$jsonarray['pushNotificationData']['configtype'] = '';
			BctedHelper::sendPushNotification($jsonarray);
		}

		$imgPath        = JUri::base().'images/email-footer-logo.png';
		$imgApplePath   = JUri::base().'images/bcted/apple-play-store-icon.png';
		$imgAndroidPath = JUri::base().'images/bcted/google-play-store-icon.png';
		$imageLink      = '<img title="Beseated" alt="Beseated" src="'.$imgPath.'"/>';
		$androidLink    = '<a href="https://play.google.com/store"><img height="80" width="200" title="Beseated" alt="Beseated" src="'.$imgAndroidPath.'"/></a>';
		$appleLink      = '<a href="https://www.apple.com/itunes/"><img height="80" width="200" title="Beseated" alt="Beseated" src="'.$imgApplePath.'"/></a>';
		$siteLink       = '<a href="'.JUri::base().'">Website</a>';

		if(count($foundEmails) != 0)
		{
			$seletedEmails = explode(",", $seletedEmails);
			$notRegEmails = array_diff($seletedEmails, $foundEmails);

			if(count($notRegEmails) != 0)
			{
				// Initialise variables.
				$app     = JFactory::getApplication();
				$config  = JFactory::getConfig();

				$site    = $config->get('sitename');
				$from    = $config->get('mailfrom');
				$sender  = $config->get('fromname');
				$email   = $notRegEmails;

				$subject = JText::_('COM_BESEATED_BOOKED_VENUE_TABLE_INVITATION_EMAIL_SUBJECT');

				if($tblTable->premium_table_id)
				{

					$body    = JText::sprintf('COM_BESEATED_BOOKED_VENUE_TABLE_INVITATION_EMAIL_BODY',"",$loginUser->name,$tblVenue->venue_name,$tblTable->venue_table_name,date('d-m-Y',strtotime($tblVenuebooking->venue_booking_datetime)),BctedHelper::convertToHM($tblVenuebooking->booking_from_time),$siteLink,$androidLink,$appleLink,$imageLink);
				}
				else
				{
					$body    = JText::sprintf('COM_BESEATED_BOOKED_VENUE_TABLE_INVITATION_EMAIL_BODY',"",$loginUser->name,$tblVenue->venue_name,$tblTable->custom_table_name,date('d-m-Y',strtotime($tblVenuebooking->venue_booking_datetime)),BctedHelper::convertToHM($tblVenuebooking->booking_from_time),$siteLink,$androidLink,$appleLink,$imageLink);
				}

				// Clean the email data.
				$sender  = JMailHelper::cleanAddress($sender);
				$subject = JMailHelper::cleanSubject($subject);
				$body    = JMailHelper::cleanBody($body);
				$return  = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body, true);
			}
		}

		if(!empty($enterEmails))
		{
			$enterEmailArray = explode(",", $enterEmails);

			foreach ($enterEmailArray as $key => $singleEmail)
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('*')
					->from($db->quoteName('#__users'))
					->where($db->quoteName('email') . ' = ' . $db->quote($singleEmail));

				// Set the query and load the result.
				$db->setQuery($query);

				$userData = $db->loadObject();

				$guestUsername = "User";

				if($userData)
				{
					$message = $user->name .' wants to invite you to ' . $tblVenue->venue_name . " for their " . $tableName.' on '. date('d-m-Y',strtotime($tblVenuebooking->venue_booking_datetime)) .' ' . BctedHelper::convertToHM($tblVenuebooking->booking_from_time).".";
					$extraParam = array();
					$extraParam['venueBookingID'] = $tblVenuebooking->venue_booking_id;
					$this->sendMessageForInviteUserByOwner($tblVenue->venue_id,0,0,0,$userData->id,$message,$extraParam,$messageType='inviteToTable');

					$guestUsername = $userData->name;
				}

				$app     = JFactory::getApplication();
				$config  = JFactory::getConfig();

				$site    = $config->get('sitename');
				$from    = $config->get('mailfrom');
				$sender  = $config->get('fromname');
				$email   = $singleEmail;

				$sender  = JMailHelper::cleanAddress($sender);
				$subject = JMailHelper::cleanSubject($subject);
				$body    = JMailHelper::cleanBody($body);

				$subject = JText::_('COM_BESEATED_BOOKED_VENUE_TABLE_INVITATION_EMAIL_SUBJECT');

				if($tblTable->premium_table_id)
				{
					$body    = JText::sprintf('COM_BESEATED_BOOKED_VENUE_TABLE_INVITATION_EMAIL_BODY',$guestUsername,$loginUser->name,$tblVenue->venue_name,$tblTable->venue_table_name,date('d-m-Y',strtotime($tblVenuebooking->venue_booking_datetime)),BctedHelper::convertToHM($tblVenuebooking->booking_from_time),$siteLink,$androidLink,$appleLink,$imageLink);
				}
				else
				{
					$body    = JText::sprintf('COM_BESEATED_BOOKED_VENUE_TABLE_INVITATION_EMAIL_BODY',$guestUsername,$loginUser->name,$tblVenue->venue_name,$tblTable->custom_table_name,date('d-m-Y',strtotime($tblVenuebooking->venue_booking_datetime)),BctedHelper::convertToHM($tblVenuebooking->booking_from_time),$siteLink,$androidLink,$appleLink,$imageLink);
				}

				// Send the email.
				$return = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body, true);
			}
		}

		if($bookingType == 'venue')
		{
			$bookingType = 'table';
		}

		$app->redirect('index.php?option=com_bcted&view=userbookingdetail&booking_type='.$bookingType.'&booking_id='.$bookingID.'&Itemid='.$Itemid,JText::_('COM_BESEATED_VENUE_TABLE_INVITATION_SEND_SUCCESSFULLY'));
		$app->close();
	}

	public function inviteToMyBookedService()
	{
		$user = JFactory::getUser();
		$app             = JFactory::getApplication();
		$input           = $app->input;
		$bookingID       = $input->get('booking_id',0,'int');
		$bookingType     = $input->get('bookingType','','string');
		$Itemid          = $input->get('Itemid',0,'int');
		$usersEmail      = $input->get('invite_user','','string');
		$fbUsersEmail    = $input->get('fbids','','string');
		$proccessToEmail = "";
		if(!empty($fbUsersEmail))
		{
			$proccessToEmail = $fbUsersEmail;
		}

		if(!empty($usersEmail) && !empty($proccessToEmail))
		{
			$proccessToEmail = $proccessToEmail.",".$usersEmail;
		}
		else if (!empty($usersEmail) && empty($proccessToEmail))
		{
			$proccessToEmail = $usersEmail;
		}
		$proccessToEmailArray = explode(",", $proccessToEmail);
		$uniqueEmail          = array_unique($proccessToEmailArray);
		$emails               = implode(',', $uniqueEmail);
		$enterEmails          = $emails;
		$seletedEmails        = $emails;
		if(empty($emails))
		{
			$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid,JText::_('COM_BESEATED_VENUE_TABLE_INVITE_DETAIL_NOT_VALID'));
			$app->close();
		}

		$bctRegiEmails = BctedHelper::filterEmails($emails);
		$emailsArray   = explode(",", $emails);
		$filterEmails  = array();

		foreach ($emailsArray as $key => $singleEmail)
		{
			if(in_array($singleEmail, $bctRegiEmails['allRegEmail']))
			{
				if(in_array($singleEmail, $bctRegiEmails['service'])){ continue; }
				if(in_array($singleEmail, $bctRegiEmails['venue'])){ continue; }
				if(in_array($singleEmail, $bctRegiEmails['guest']) && $user->email == $singleEmail){ continue; }
			}

			$filterEmails[] = $singleEmail;
		}

		if(count($filterEmails)==0)
		{
			$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid,JText::_('COM_BESEATED_COMPANY_SERVICE_INVITE_NOT_VALID_SELF_EMAIL_OR_CLUB_SERVICE'));
			$app->close();
		}

		$seletedEmails     = implode(',', $filterEmails);
		$emails            = $seletedEmails;
		$enterEmails       = $emails;
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblCompany        = JTable::getInstance('Company', 'BctedTable');
		$tblService        = JTable::getInstance('Service', 'BctedTable');
		$tblServiceBooking = JTable::getInstance('ServiceBooking', 'BctedTable');

		$loginUser = JFactory::getUser();

		$tblServiceBooking->load($bookingID);
		$tblCompany->load($tblServiceBooking->company_id);
		$tblService->load($tblServiceBooking->service_id);

		if(!$tblServiceBooking->service_booking_id || !$tblCompany->company_id || !$tblService->service_id)
		{
			$app->redirect('index.php?option=com_bcted&view=userbookings&Itemid='.$Itemid,JText::_('COM_BESEATED_VENUE_TABLE_INVITE_DETAIL_NOT_VALID'));
			$app->close();
		}

		if(!empty($emails))
		{
			$emails = explode(",", $emails);
			$emails = implode("','", $emails);
			$emails = "'".$emails."'";

			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('id')
				->from($db->quoteName('#__users'))
				->where($db->quoteName('email') . ' IN (' . $emails . ')')
				->where($db->quoteName('block') . ' = ' . $db->quote(0));

			// Set the query and load the result.
			$db->setQuery($query);

			$foundUsers = $db->loadColumn();

			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('email')
				->from($db->quoteName('#__users'))
				->where($db->quoteName('email') . ' IN (' . $emails . ')')
				->where($db->quoteName('block') . ' = ' . $db->quote(0));

			// Set the query and load the result.
			$db->setQuery($query);

			$foundEmails = $db->loadColumn();
		}

		if(count($foundUsers)!=0)
		{
			$bookingDateTime = date('d-m-Y H:i',strtotime($tblServiceBooking->service_booking_datetime.' '.$tblServiceBooking->booking_from_time));
			$message = JText::sprintf('COM_BESEATED_COMPANY_INVITE_TO_MY_BOOKING_SERVICE',$user->name,$tblCompany->company_name,$bookingDateTime);
			$jsonarray['pushNotificationData']['id']         = $bookingID;
			$jsonarray['pushNotificationData']['to']         = implode(",", $foundUsers);
			$jsonarray['pushNotificationData']['message']    = $message;
			$jsonarray['pushNotificationData']['type']       = JText::_('PUSHNOTIFICATION_TYPE_BOOKEDSERVICEINVITETOUSER'); //'bookedTableInviteToUser';
			$jsonarray['pushNotificationData']['configtype'] = '';

			BctedHelper::sendPushNotification($jsonarray);
		}

		$imgPath        = JUri::base().'images/email-footer-logo.png';
		$imgApplePath   = JUri::base().'images/bcted/apple-play-store-icon.png';
		$imgAndroidPath = JUri::base().'images/bcted/google-play-store-icon.png';
		$imageLink      = '<img title="Beseated" alt="Beseated" src="'.$imgPath.'"/>';
		$androidLink    = '<a href="https://play.google.com/store"><img height="80" width="200" title="Beseated" alt="Beseated" src="'.$imgAndroidPath.'"/></a>';
		$appleLink      = '<a href="https://www.apple.com/itunes/"><img height="80" width="200" title="Beseated" alt="Beseated" src="'.$imgApplePath.'"/></a>';
		$siteLink       = '<a href="'.JUri::base().'">Website</a>';

		if(count($foundEmails) != 0)
		{
			$notRegEmails = array_diff($seletedEmails, $foundEmails);

			if(count($notRegEmails) != 0)
			{
				// Initialise variables.
				$app     = JFactory::getApplication();
				$config  = JFactory::getConfig();
				$site    = $config->get('sitename');
				$from    = $config->get('mailfrom');
				$sender  = $config->get('fromname');
				$email   = $notRegEmails; //implode(",",$notRegEmails); //$app->input->get('mailto');
				$subject = JText::_('COM_BESEATED_BOOKED_COMPANY_SERVICE_INVITATION_EMAIL_SUBJECT'); // $app->input->get('subject');
				$body    = JText::sprintf('COM_BESEATED_BOOKED_COMPANY_SERVICE_INVITATION_EMAIL_BODY',"",$loginUser->name,$tblCompany->company_name,$tblService->service_name,date('d-m-Y',strtotime($tblServiceBooking->service_booking_datetime)),BctedHelper::convertToHM($tblServiceBooking->booking_from_time),$siteLink,$androidLink,$appleLink,$imageLink);
				// Clean the email data.
				$sender  = JMailHelper::cleanAddress($sender);
				$subject = JMailHelper::cleanSubject($subject);
				$body    = JMailHelper::cleanBody($body);
				// Send the email.
				$return = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body, true);
			}
		}

		if(!empty($enterEmails))
		{
			$enterEmailArray = explode(",", $enterEmails);
			foreach ($enterEmailArray as $key => $singleEmail)
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('*')
					->from($db->quoteName('#__users'))
					->where($db->quoteName('email') . ' = ' . $db->quote($singleEmail));

				// Set the query and load the result.
				$db->setQuery($query);

				$userData = $db->loadObject();

				if($userData)
				{
					$fromTime = explode(":", $tblServiceBooking->booking_from_time);

					$message = $user->name .' wants to invite you for ' . $tblCompany->company_name.' at '.$tblServiceBooking->service_location.' on '.date('d-m-Y', strtotime($tblServiceBooking->service_booking_datetime)).' ' .$fromTime[0].":".$fromTime[1];
					$extraParam = array();
					$extraParam['serviceBookingID'] = $tblService->service_booking_id;
					$this->sendMessageForInviteUserByOwner(0,$tblCompany->company_id,$tblService->service_id,0,$userData->id,$message,$extraParam,$messageType='inviteToService');

					$userName = $userData->name;
				}
				else
				{
					$userName = "";
				}

				$app       = JFactory::getApplication();
				$config    = JFactory::getConfig();
				$site      = $config->get('sitename');
				$from      = $config->get('mailfrom');
				$sender    = $config->get('fromname');
				$email     = $singleEmail; //implode(",",$notRegEmails); //$app->input->get('mailto');
				$subject   = JText::_('COM_BESEATED_BOOKED_COMPANY_SERVICE_INVITATION_EMAIL_SUBJECT'); // $app->input->get('subject');
				$imgPath   = JUri::base().'images/email-footer-logo.png';
				$imageLink = '<img title="Beseated" alt="Beseated" src="'.$imgPath.'" />';
				$fromTime  = explode(":", $tblServiceBooking->booking_from_time);
				$body      = JText::sprintf('COM_BESEATED_BOOKED_COMPANY_SERVICE_INVITATION_EMAIL_BODY',$userName,$loginUser->name,$tblCompany->company_name,$tblService->service_name,date('d-m-Y',strtotime($tblServiceBooking->service_booking_datetime)),BctedHelper::convertToHM($tblServiceBooking->booking_from_time),$siteLink,$androidLink,$appleLink,$imageLink);
				// Clean the email data.
				$sender    = JMailHelper::cleanAddress($sender);
				$subject   = JMailHelper::cleanSubject($subject);
				$body      = JMailHelper::cleanBody($body);
				$return    = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body, true);
			}
		}

		if($bookingType == 'venue')
		{
			$bookingType = 'table';
		}
		else if($bookingType == 'company')
		{
			$bookingType = 'service';
		}

		$app->redirect('index.php?option=com_bcted&view=userbookingdetail&booking_type='.$bookingType.'&booking_id='.$bookingID.'&Itemid='.$Itemid,JText::_('COM_BESEATED_VENUE_TABLE_INVITATION_SEND_SUCCESSFULLY'));
		$app->close();
	}

	public function sendMessageForInviteUserByOwner($venueID,$companyID,$serviceID,$tableID,$TouserID,$message,$extraParam = array(),$messageType='tableaddme')
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblMessage = JTable::getInstance('Message', 'BctedTable');
		$tblMessage->load(0);

		$data = array();

		$data['venue_id']   = $venueID;
		$data['company_id'] = $companyID;
		$data['table_id']   = $tableID;
		$data['service_id'] = $serviceID;
		$data['userid']     = $TouserID;
		$data['to_userid']  = $TouserID;
		$data['message']    = $message;
		if(count($extraParam)!=0)
		{
			$data['extra_params'] = json_encode($extraParam);
		}
		else
		{
			$data['extra_params'] = "";
		}

		$data['message_type']    = $messageType;
		$data['created']    = date('Y-m-d H:i:s');
		$data['time_stamp'] = time();
		$user = JFactory::getUser();
		$data['from_userid'] = $user->id;
		$connectionID = BctedHelper::getMessageConnection($data['from_userid'],$data['to_userid']);

		if(!$connectionID)
		{
			return 0;
		}

		$data['connection_id'] = $connectionID;
		$tblMessage->bind($data);
		$tblMessage->store();
	}

	public function decreasLoyalty($bookingID,$bookingType,$userID,$appType = "cancelbooking")
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_payment_status'))
			->where($db->quoteName('booked_element_id') . ' = ' . $db->quote($bookingID));

		if($bookingType == 'venue')
		{
			$query->where($db->quoteName('booked_element_type') . ' = ' . $db->quote('venue'));
		}
		else if($bookingType == 'service')
		{
			$query->where($db->quoteName('booked_element_type') . ' = ' . $db->quote('service'));
		}
		else
		{
			return false;
		}

		$query->where($db->quoteName('paid_status') . ' = ' . $db->quote('1'));

		// Set the query and load the result.
		$db->setQuery($query);

		$paymentStatus = $db->loadObject();

		if($paymentStatus)
		{
			$queryLP = $db->getQuery(true);
			$queryLP->select('*')
				->from($db->quoteName('#__bcted_loyalty_point'))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

			if($bookingType == 'venue')
			{
				$queryLP->where($db->quoteName('point_app') . ' = ' . $db->quote('purchase.venue'));
			}
			else if($bookingType == 'service')
			{
				$queryLP->where($db->quoteName('point_app') . ' = ' . $db->quote('purchase.service'));
			}
			else
			{
				return false;
			}

			$queryLP->where($db->quoteName('cid') . ' = ' . $db->quote($paymentStatus->payment_id));

			// Set the query and load the result.
			$db->setQuery($queryLP);

			$loyaltyPointDetail = $db->loadObject();

			if($loyaltyPointDetail)
			{
				$query = $db->getQuery(true);

				if($bookingType == 'venue')
				{
					$point_app = 'venue';
				}
				else if($bookingType == 'service')
				{
					$point_app = 'service';
				}
				else
				{
					return false;
				}

				$point_app = $point_app.'.'.$appType;

				// Create the base insert statement.
				$query->insert($db->quoteName('#__bcted_loyalty_point'))
					->columns(
						array(
							$db->quoteName('user_id'),
							$db->quoteName('earn_point'),
							$db->quoteName('point_app'),
							$db->quoteName('cid'),
							$db->quoteName('is_valid'),
							$db->quoteName('created'),
							$db->quoteName('time_stamp')
						)
					)
					->values(
						$db->quote($userID) . ', ' .
						$db->quote(($loyaltyPointDetail->earn_point * (-1))) . ', ' .
						$db->quote($point_app) . ', ' .
						$db->quote($loyaltyPointDetail->cid) . ', ' .
						$db->quote(1) . ', ' .
						$db->quote(date('Y-m-d H:i:s')) . ', ' .
						$db->quote(time())
					);

				// Set the query and execute the insert.
				$db->setQuery($query);

				$db->execute();
			}
		}
	}
}
