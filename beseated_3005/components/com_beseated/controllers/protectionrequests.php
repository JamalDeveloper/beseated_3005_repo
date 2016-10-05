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
 * The Beseated Guest List Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerProtectionRequests extends JControllerAdmin
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
	public function getModel($name = 'ProtectionRequests', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	

	function deleteBookingRequest()
	{
		$user = JFactory::getUser();

		if(!$user->id)
		{
			echo 704;
			exit();
		} // End of login Condition


		$input      = JFactory::getApplication()->input;
		$bookingID  = $input->getInt('protectionBookingID',0);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
		$tblProtectionBooking->load($bookingID);

		$tblElement = JTable::getInstance('Protection', 'BeseatedTable');
		$tblElement->load($tblProtectionBooking->protection_id);

		if(!$tblProtectionBooking->protection_booking_id)
		{
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_DETAIL
			exit();
			
		}

		if($tblElement->user_id != $user->id)
		{
		
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_DETAIL
			exit();
		}

		$tblProtectionBooking->deleted_by_protection = 1;

		if(!$tblProtectionBooking->store())
		{
			echo 500;
			exit();
		}

		echo 200;
		exit();
	}

	function changeBookingStatus()
	{

		$user =	JFactory::getUser();

		if(!$user->id)
		{
			echo 704;
			exit();
		} // End of login Condition

		$input = JFactory::getApplication()->input;

		$protectionBookingID = $input->getInt('protectionBookingID',0);
		$statusCode          = $input->getInt('statusCode',0);

		
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
		$tblProtectionBooking->load($protectionBookingID);

		$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
		$tblService->load($tblProtectionBooking->service_id);

		$tblElement = JTable::getInstance('Protection', 'BeseatedTable');
		$tblElement->load($tblProtectionBooking->protection_id);

		if(!$tblProtectionBooking->protection_booking_id || !$statusCode)
		{
			echo 400; // COM_IJOOMERADV_BESEATED_PROTECTION_SERVICE_CHANGE_STATUS_INVALID_DETAIL
			exit();
		}

		if($tblElement->user_id != $user->id)
		{
			echo 401; // COM_IJOOMERADV_BESEATED_PROTECTION_SERVICE_OR_COMPANY_IN_BOOKING
			exit();
		}

		if($statusCode == 3)
		{
			$tblProtectionBooking->user_status = BeseatedHelper::getStatusID('available');
			$tblProtectionBooking->protection_status = BeseatedHelper::getStatusID('awaiting-payment');

			$totalPrice = $tblProtectionBooking->total_price;

			$tblProtectionBooking->remaining_amount     = ($tblElement->deposit_per == '0') ? $totalPrice: $totalPrice*$tblElement->deposit_per/100;
			$tblProtectionBooking->deposite_price       = ($tblElement->deposit_per == '0') ? $totalPrice: $totalPrice*$tblElement->deposit_per/100;
			$tblProtectionBooking->org_remaining_amount = $totalPrice;

			$notificationType = "service.request.accepted";
			$title = JText::sprintf(
						'COM_BESEATED_PUSHNOTIFICATION_SERVICE_BOOKING_REQUEST_ACCEPTED_BY_PROTECTION',
						$tblElement->protection_name,
						$tblService->service_name,
						BeseatedHelper::convertDateFormat($tblProtectionBooking->booking_date),
						BeseatedHelper::convertToHM($tblProtectionBooking->booking_time)
					);

			$dbTitle = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_SERVICE_BOOKING_REQUEST_ACCEPTED_BY_PROTECTION',
						$tblService->service_name,
						BeseatedHelper::convertDateFormat($tblProtectionBooking->booking_date),
						BeseatedHelper::convertToHM($tblProtectionBooking->booking_time),
						$tblProtectionBooking->total_guard
					);

		}
		else if($statusCode == 6)
		{
			$tblProtectionBooking->user_status = BeseatedHelper::getStatusID('decline');
			$tblProtectionBooking->protection_status = BeseatedHelper::getStatusID('decline');
			$notificationType = "service.request.declined";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_REQUEST_DECLINED_BY_PROTECTION',
									$tblElement->protection_name,
									$tblService->service_name,
									BeseatedHelper::convertDateFormat($tblProtectionBooking->booking_date),
									BeseatedHelper::convertToHM($tblProtectionBooking->booking_time)
								);

			$dbTitle = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_SERVICE_BOOKING_REQUEST_DECLINED_BY_PROTECTION',
						$tblService->service_name,
						BeseatedHelper::convertDateFormat($tblProtectionBooking->booking_date),
						BeseatedHelper::convertToHM($tblProtectionBooking->booking_time),
						$tblProtectionBooking->total_guard
					);

		}
		

		$tblProtectionBooking->response_date_time = date('Y-m-d H:i:s');
		if(!$tblProtectionBooking->store())
		{ 
			echo 500;  // COM_IJOOMERADV_BESEATED_PROTECTION_ERROR_IN_BOOKING
			exit();
		}

		/*$actor            = $this->IJUserID;
		$target           = $tblProtectionBooking->user_id;
		$elementID        = $tblElement->protection_id;
		$elementType      = "Protection";
		$cid              = $tblProtectionBooking->protection_booking_id;
		$extraParams      = array();
		$extraParams["protectionBookingID"] = $tblProtectionBooking->protection_booking_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$dbTitle,$cid,$extraParams,$cid))
		{
			$protectionDefaultImage = $this->helper->getElementDefaultImage($tblElement->protection_id,'Protection');
			$bookingDate = date('d F Y',strtotime($tblProtectionBooking->booking_date));
			$thumb = Juri::base().'images/beseated/'.$protectionDefaultImage->thumb_image;

			$companyDetail = JFactory::getUser($tblElement->user_id);
			$userDetail    = JFactory::getUser($target);

			if($statusCode == 3)
			{
				$this->emailHelper->protectionBookingAvailableUserMail($userDetail->name,$companyDetail->name,$thumb,$bookingDate,$this->helper->convertToHM($tblProtectionBooking->booking_time),$tblProtectionBooking->total_hours,$tblProtectionBooking->total_guard,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblProtectionBooking->total_price,0),$userDetail->email);
			}
			else if ($statusCode == 6)
			{
				$this->emailHelper->protectionBookingNotAvailableUserMail($userDetail->name,$companyDetail->name,$thumb,$bookingDate,$this->helper->convertToHM($tblProtectionBooking->booking_time),$tblProtectionBooking->total_hours,$tblProtectionBooking->total_guard,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblProtectionBooking->total_price,0),$userDetail->email);
			}

		}*/

		
		/*$this->jsonarray['pushNotificationData']['id']          = $tblProtectionBooking->protection_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Protection';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		//$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_PROTECTION_BOOKING_REQUEST_STATUS_CHANGED');
		$this->jsonarray['pushNotificationData']['type']        = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype']  = '';*/

		echo 200;
		exit();
	}
}
