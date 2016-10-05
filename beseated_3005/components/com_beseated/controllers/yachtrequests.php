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
class BeseatedControllerYachtRequests extends JControllerAdmin
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
	public function getModel($name = 'YachtRequests', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
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
		$bookingID  = $input->getInt('yachtBookingID',0);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
		$tblYachtBooking->load($bookingID);

		$tblElement = JTable::getInstance('Yacht', 'BeseatedTable');
		$tblElement->load($tblYachtBooking->yacht_id);

		if(!$tblYachtBooking->yacht_booking_id)
		{
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_DETAIL
			exit();
			
		}

		if($tblElement->user_id != $user->id)
		{
		
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_DETAIL
			exit();
		}

		$tblYachtBooking->deleted_by_yacht = 1;

		if(!$tblYachtBooking->store())
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

		$yachtBookingID = $input->getInt('yachtBookingID',0);
		$statusCode          = $input->getInt('statusCode',0);

		
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
		$tblYachtBooking->load($yachtBookingID);

		$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
		$tblService->load($tblYachtBooking->service_id);

		$tblElement = JTable::getInstance('Yacht', 'BeseatedTable');
		$tblElement->load($tblYachtBooking->yacht_id);

		if(!$tblYachtBooking->yacht_booking_id || !$statusCode)
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
			$tblYachtBooking->user_status = BeseatedHelper::getStatusID('available');
			$tblYachtBooking->yacht_status = BeseatedHelper::getStatusID('awaiting-payment');

			$totalPrice = $tblYachtBooking->total_price;

			$tblYachtBooking->remaining_amount     = ($tblElement->deposit_per == '0') ? $totalPrice: $totalPrice*$tblElement->deposit_per/100;
			$tblYachtBooking->deposite_price       = ($tblElement->deposit_per == '0') ? $totalPrice: $totalPrice*$tblElement->deposit_per/100;
			$tblYachtBooking->org_remaining_amount = $totalPrice;

			$notificationType = "service.request.accepted";
			$title = JText::sprintf(
						'COM_BESEATED_PUSHNOTIFICATION_SERVICE_BOOKING_REQUEST_ACCEPTED_BY_YACHT',
						$tblElement->yacht_name,
						$tblService->service_name,
						BeseatedHelper::convertDateFormat($tblYachtBooking->booking_date),
						BeseatedHelper::convertToHM($tblYachtBooking->booking_time)
					);

			$dbTitle = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_SERVICE_BOOKING_REQUEST_ACCEPTED_BY_YACHT',
						$tblService->service_name,
						BeseatedHelper::convertDateFormat($tblYachtBooking->booking_date),
						BeseatedHelper::convertToHM($tblYachtBooking->booking_time),
						$tblYachtBooking->total_guard
					);

		}
		else if($statusCode == 6)
		{
			$tblYachtBooking->user_status = BeseatedHelper::getStatusID('decline');
			$tblYachtBooking->yacht_status = BeseatedHelper::getStatusID('decline');
			$notificationType = "service.request.declined";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_REQUEST_DECLINED_BY_YACHT',
									$tblElement->yacht_name,
									$tblService->service_name,
									BeseatedHelper::convertDateFormat($tblYachtBooking->booking_date),
									BeseatedHelper::convertToHM($tblYachtBooking->booking_time)
								);

			$dbTitle = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_SERVICE_BOOKING_REQUEST_DECLINED_BY_YACHT',
						$tblService->service_name,
						BeseatedHelper::convertDateFormat($tblYachtBooking->booking_date),
						BeseatedHelper::convertToHM($tblYachtBooking->booking_time),
						$tblYachtBooking->total_guard
					);

		}
		

		$tblYachtBooking->response_date_time = date('Y-m-d H:i:s');
		if(!$tblYachtBooking->store())
		{ 
			echo 500;  // COM_IJOOMERADV_BESEATED_PROTECTION_ERROR_IN_BOOKING
			exit();
		}

		/*$actor            = $this->IJUserID;
		$target           = $tblYachtBooking->user_id;
		$elementID        = $tblElement->yacht_id;
		$elementType      = "Yacht";
		$cid              = $tblYachtBooking->yacht_booking_id;
		$extraParams      = array();
		$extraParams["yachtBookingID"] = $tblYachtBooking->yacht_booking_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$dbTitle,$cid,$extraParams,$cid))
		{
			$yachtDefaultImage = $this->helper->getElementDefaultImage($tblElement->yacht_id,'Yacht');
			$bookingDate = date('d F Y',strtotime($tblYachtBooking->booking_date));
			$thumb = Juri::base().'images/beseated/'.$yachtDefaultImage->thumb_image;

			$companyDetail = JFactory::getUser($tblElement->user_id);
			$userDetail    = JFactory::getUser($target);

			if($statusCode == 3)
			{
				$this->emailHelper->yachtBookingAvailableUserMail($userDetail->name,$companyDetail->name,$thumb,$bookingDate,$this->helper->convertToHM($tblYachtBooking->booking_time),$tblYachtBooking->total_hours,$tblYachtBooking->total_guard,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblYachtBooking->total_price,0),$userDetail->email);
			}
			else if ($statusCode == 6)
			{
				$this->emailHelper->yachtBookingNotAvailableUserMail($userDetail->name,$companyDetail->name,$thumb,$bookingDate,$this->helper->convertToHM($tblYachtBooking->booking_time),$tblYachtBooking->total_hours,$tblYachtBooking->total_guard,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblYachtBooking->total_price,0),$userDetail->email);
			}

		}*/

		
		/*$this->jsonarray['pushNotificationData']['id']          = $tblYachtBooking->yacht_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Yacht';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		//$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_PROTECTION_BOOKING_REQUEST_STATUS_CHANGED');
		$this->jsonarray['pushNotificationData']['type']        = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype']  = '';*/

		echo 200;
		exit();
	}

	

	

	
}
