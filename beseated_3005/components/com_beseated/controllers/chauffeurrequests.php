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
class BeseatedControllerChauffeurRequests extends JControllerAdmin
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
	public function getModel($name = 'ChauffeurRequests', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
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
		$bookingID  = $input->getInt('chauffeurBookingID',0);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$tblChauffeurBooking = JTable::getInstance('ChauffeurBooking', 'BeseatedTable');
		$tblChauffeurBooking->load($bookingID);

		$tblElement = JTable::getInstance('Chauffeur', 'BeseatedTable');
		$tblElement->load($tblChauffeurBooking->chauffeur_id);

		if(!$tblChauffeurBooking->chauffeur_booking_id)
		{
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_DETAIL
			exit();
			
		}

		if($tblElement->user_id != $user->id)
		{
		
			echo 400; // COM_IJOOMERADV_BESEATED_INVALID_DETAIL
			exit();
		}

		$tblChauffeurBooking->deleted_by_chauffeur = 1;

		if(!$tblChauffeurBooking->store())
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

		$chauffeurBookingID = $input->getInt('chauffeurBookingID',0);
		$statusCode          = $input->getInt('statusCode',0);
		$totalPrice          = $input->getInt('amount',0);

		
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblChauffeurBooking = JTable::getInstance('ChauffeurBooking', 'BeseatedTable');
		$tblChauffeurBooking->load($chauffeurBookingID);

		$tblService = JTable::getInstance('ChauffeurService', 'BeseatedTable');
		$tblService->load($tblChauffeurBooking->service_id);

		$tblElement = JTable::getInstance('Chauffeur', 'BeseatedTable');
		$tblElement->load($tblChauffeurBooking->chauffeur_id);

		if(!$tblChauffeurBooking->chauffeur_booking_id || !$statusCode)
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
			$tblChauffeurBooking->user_status      = BeseatedHelper::getStatusID('available');
			$tblChauffeurBooking->chauffeur_status = BeseatedHelper::getStatusID('awaiting-payment');
			$tblChauffeurBooking->total_price      = $totalPrice;

			$tblChauffeurBooking->remaining_amount     = ($tblElement->deposit_per == '0') ? $totalPrice : $totalPrice*$tblElement->deposit_per/100;
			$tblChauffeurBooking->deposite_price       = ($tblElement->deposit_per == '0') ? $totalPrice : $totalPrice*$tblElement->deposit_per/100;
			$tblChauffeurBooking->org_remaining_amount = $totalPrice;

			$notificationType = "service.request.accepted";
			$title = JText::sprintf(
						'COM_BESEATED_PUSHNOTIFICATION_SERVICE_BOOKING_REQUEST_ACCEPTED_BY_CHAUFFUER',
						$tblElement->chauffeur_name,
						$tblService->service_name,
						BeseatedHelper::convertDateFormat($tblChauffeurBooking->booking_date),
						BeseatedHelper::convertToHM($tblChauffeurBooking->booking_time)
					);

			$dbTitle = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_SERVICE_BOOKING_REQUEST_ACCEPTED_BY_CHAUFFUER',
						$tblService->service_name,
						BeseatedHelper::convertDateFormat($tblChauffeurBooking->booking_date),
						BeseatedHelper::convertToHM($tblChauffeurBooking->booking_time),
						$tblChauffeurBooking->total_guard
					);

		}
		else if($statusCode == 6)
		{
			$tblChauffeurBooking->user_status = BeseatedHelper::getStatusID('decline');
			$tblChauffeurBooking->chauffeur_status = BeseatedHelper::getStatusID('decline');
			$notificationType = "service.request.declined";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_REQUEST_DECLINED_BY_CHAUFFUER',
									$tblElement->chauffeur_name,
									$tblService->service_name,
									BeseatedHelper::convertDateFormat($tblChauffeurBooking->booking_date),
									BeseatedHelper::convertToHM($tblChauffeurBooking->booking_time)
								);

			$dbTitle = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_SERVICE_BOOKING_REQUEST_DECLINED_BY_CHAUFFUER',
						$tblService->service_name,
						BeseatedHelper::convertDateFormat($tblChauffeurBooking->booking_date),
						BeseatedHelper::convertToHM($tblChauffeurBooking->booking_time),
						$tblChauffeurBooking->total_guard
					);

		}
		

		$tblChauffeurBooking->response_date_time = date('Y-m-d H:i:s');
		if(!$tblChauffeurBooking->store())
		{ 
			echo 500;  // COM_IJOOMERADV_BESEATED_PROTECTION_ERROR_IN_BOOKING
			exit();
		}

		/*$actor            = $this->IJUserID;
		$target           = $tblChauffeurBooking->user_id;
		$elementID        = $tblElement->chauffeur_id;
		$elementType      = "Chauffeur";
		$cid              = $tblChauffeurBooking->chauffeur_booking_id;
		$extraParams      = array();
		$extraParams["chauffeurBookingID"] = $tblChauffeurBooking->chauffeur_booking_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$dbTitle,$cid,$extraParams,$cid))
		{
			$chauffeurDefaultImage = $this->helper->getElementDefaultImage($tblElement->chauffeur_id,'Chauffeur');
			$bookingDate = date('d F Y',strtotime($tblChauffeurBooking->booking_date));
			$thumb = Juri::base().'images/beseated/'.$chauffeurDefaultImage->thumb_image;

			$companyDetail = JFactory::getUser($tblElement->user_id);
			$userDetail    = JFactory::getUser($target);

			if($statusCode == 3)
			{
				$this->emailHelper->chauffeurBookingAvailableUserMail($userDetail->name,$companyDetail->name,$thumb,$bookingDate,$this->helper->convertToHM($tblChauffeurBooking->booking_time),$tblChauffeurBooking->total_hours,$tblChauffeurBooking->total_guard,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblChauffeurBooking->total_price,0),$userDetail->email);
			}
			else if ($statusCode == 6)
			{
				$this->emailHelper->chauffeurBookingNotAvailableUserMail($userDetail->name,$companyDetail->name,$thumb,$bookingDate,$this->helper->convertToHM($tblChauffeurBooking->booking_time),$tblChauffeurBooking->total_hours,$tblChauffeurBooking->total_guard,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblChauffeurBooking->total_price,0),$userDetail->email);
			}

		}*/

		
		/*$this->jsonarray['pushNotificationData']['id']          = $tblChauffeurBooking->chauffeur_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Chauffeur';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		//$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_PROTECTION_BOOKING_REQUEST_STATUS_CHANGED');
		$this->jsonarray['pushNotificationData']['type']        = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype']  = '';*/

		echo 200;
		exit();
	}



}
