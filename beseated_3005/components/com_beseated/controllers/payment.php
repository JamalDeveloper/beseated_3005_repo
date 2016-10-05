<?php
/**
 * @package     Beseated.Site
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * The Beseated Payment Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerPayment extends JControllerForm
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name  	 The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array  	$config  Configuration array for model. Optional.
	 *
	 * @return object The model.
	 *
	 * @since   0.0.1
	 */
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function pay()
	{
		JPluginHelper::importPlugin('beseated');
		$dispatcher = JDispatcher::getInstance('ccavenue');
		$results = $dispatcher->trigger('onCCAvenuePayment', array());
	}

	public function ipn()
	{
		$app           = JFactory::getApplication();
		$input         = $app->input;
		$extraParams      = array();

		$menu                  = $app->getMenu();
		$guestBookingsMenuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userbookings', true );
		$guestBookingsItemid   = $guestBookingsMenuItem->id;
		/*echo "<pre>";
		print_r($input);
		echo "</pre>";
		exit;*/
		// http://istage.website/beseated-ii/index.php?option=com_beseated&task=payment.pay&booking_id=49&booking_type=venue.split&pay_balance=1
		$response_data = $input->get('encResp');
		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();
		$auto_approve  = $input->get('auto_approve',0,'int');
		$source        = $input->get('source','','string');


		if($auto_approve == 1)
		{
			$beseatedParams->encryptionKey = '23D39F9BCB8D07538E81F375479AAB37';
			$rcvdString = $this->decrypt($response_data, $beseatedParams->encryptionKey);
		}
		else
		{
			$rcvdString = $this->decrypt($response_data, $beseatedParams->encryptionKey);
		}

		$decryptValues  = explode('&', $rcvdString);
		$dataSize       = sizeof($decryptValues);
		$response_array = array();
		for($i = 0; $i < count($decryptValues); $i++)
		{
	  		$information	= explode('=',$decryptValues[$i]);
			if(count($information)==2)
			{
				$response_array[$information[0]] = urldecode($information[1]);
			}
		}

		$app             = JFactory::getApplication();
		$input           = $app->input;
		$paymentID       = $input->get('payment_id',0,'int');
		$org_paid_amount = $input->get('org_pay_price','','string');
		$full_payment    = $input->get('full_payment',0,'int');
		$pay_balance     = $input->get('pay_balance',0,'int');
		$tblPayment      = JTable::getInstance('Payment', 'BeseatedTable',array());
		$tblPayment->load($paymentID);

		if(!$tblPayment->payment_id)
		{
			return;
		}

		$elementID         = $tblPayment->booking_id;
		$elements_booking_ID  = $tblPayment->booking_id;
		$elementType       = $tblPayment->booking_type;
		$paymentGross      = $response_array['amount'];
		$paymentFee        = 0;
		$txnID             = $response_array['tracking_id'];
		$paymentStatusText = $response_array['order_status'];
		$tblPayment->payment_fee         = $paymentFee;
		$tblPayment->txn_id              = $txnID;
		$tblPayment->payment_status      = $paymentStatusText;
		$tblPayment->cc_tracking_id      = $response_array['tracking_id'];
		$tblPayment->cc_bank_ref_no      = $response_array['bank_ref_no'];
		$tblPayment->cc_order_status     = $response_array['order_status'];
		$tblPayment->cc_failure_message  = $response_array['failure_message'];
		$tblPayment->cc_payment_mode     = $response_array['payment_mode'];
		$tblPayment->cc_card_name        = $response_array['card_name'];
		$tblPayment->cc_status_code      = $response_array['status_code'];
		$tblPayment->cc_status_message   = $response_array['status_message'];
		$tblPayment->cc_currency         = $response_array['currency'];
		$tblPayment->cc_amount           = $response_array['amount'];
		$tblPayment->cc_billing_name     = $response_array['billing_name'];
		$tblPayment->cc_billing_address  = $response_array['billing_address'];
		$tblPayment->cc_billing_city     = $response_array['billing_city'];
		$tblPayment->cc_billing_state    = $response_array['billing_state'];
		$tblPayment->cc_billing_zip      = $response_array['billing_zip'];
		$tblPayment->cc_billing_country  = $response_array['billing_country'];
		$tblPayment->cc_billing_tel      = $response_array['billing_tel'];
		$tblPayment->cc_billing_email    = $response_array['billing_email'];
		$tblPayment->cc_delivery_name    = $response_array['delivery_name'];
		$tblPayment->cc_delivery_address = $response_array['delivery_address'];
		$tblPayment->cc_delivery_city    = $response_array['delivery_city'];
		$tblPayment->cc_delivery_state   = $response_array['delivery_state'];
		$tblPayment->cc_delivery_zip     = $response_array['delivery_zip'];
		$tblPayment->cc_delivery_country = $response_array['delivery_country'];
		$tblPayment->cc_delivery_tel     = $response_array['delivery_tel'];
		$tblPayment->cc_merchant_param1  = $response_array['merchant_param1'];
		$tblPayment->cc_merchant_param2  = $response_array['merchant_param2'];
		$tblPayment->cc_merchant_param3  = $response_array['merchant_param3'];
		$tblPayment->cc_merchant_param4  = $response_array['merchant_param4'];
		$tblPayment->cc_merchant_param5  = $response_array['merchant_param5'];
		$tblPayment->cc_vault            = $response_array['vault'];
		$tblPayment->cc_offer_type       = $response_array['offer_type'];
		$tblPayment->cc_offer_code       = $response_array['offer_code'];
		$tblPayment->cc_discount_value   = $response_array['discount_value'];

		if($paymentStatusText == 'Completed' || $response_array['order_status'] == 'Success')
		{
			$tblOrderConfirm = JTable::getInstance('OrderConfirm', 'BeseatedTable');
			$tblOrderConfirm->load(0);

			$tblOrderConfirm->api_call_status = 0;
			$tblOrderConfirm->success_count   = 0;
			$tblOrderConfirm->is_failed       = 1;
			$tblOrderConfirm->payment_id      = $tblPayment->payment_id;
			$tblOrderConfirm->created_date    = date('Y-m-d H:i:s');
			$tblOrderConfirm->store();

			$tblPayment->paid_status = 1;

			if($elementType == 'protection')
			{
				$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
				$tblProtectionBooking->load($elementID);

				if($pay_balance)
				{
					$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
					$tblService->load($tblProtectionBooking->service_id);

					$this->sendPNtoSplitedUsers('protection',$tblProtectionBooking->user_id,$elementID,$tblService->service_name,$tblProtectionBooking->booking_date);

					$tblProtectionBooking->user_status          = 5;
					$tblProtectionBooking->protection_status    = 5;
					$tblProtectionBooking->remaining_amount     = '0.00';
					$tblProtectionBooking->org_remaining_amount = '0.00';

					// Initialiase variables.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__beseated_protection_booking_split'))
						->set($db->quoteName('split_payment_status') . ' = ' . $db->quote(7))
						->set($db->quoteName('paid_by_owner') . ' = ' . $db->quote(1))
						->where($db->quoteName('split_payment_status') . ' = ' . $db->quote(2))
						->where($db->quoteName('protection_booking_id') . ' = ' . $db->quote($tblProtectionBooking->protection_booking_id));

					// Set the query and execute the update.
					$db->setQuery($query);
					$db->execute();
				}
				else if($tblProtectionBooking->is_splitted)
				{
					$userPaidAmount = $tblPayment->amount;
					$tblProtectionBooking->remaining_amount     = $tblProtectionBooking->remaining_amount - $userPaidAmount;
					$tblProtectionBooking->org_remaining_amount = $tblProtectionBooking->org_remaining_amount - $org_paid_amount;
					//echo "Remaining Amount : " . $tblProtectionBooking->remaining_amount . "<br />";
					//echo "User paid Amount : " . $userPaidAmount;
					//exit;
					$tblProtectionBooking->store();
					$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
					$tblProtectionBooking->load($elementID);
					$remainingAmount      = $this->getSplitedRemainingAmount('protection',$tblProtectionBooking->protection_booking_id);

					if($remainingAmount == 0)
					{
						$tblProtectionBooking->user_status       = 5;
						$tblProtectionBooking->protection_status = 5;
					}
					//$tblProtectionBooking->remaining_amount = $remainingAmount;
				}
				else
				{
					$tblProtectionBooking->protection_status = 5;
					$tblProtectionBooking->user_status       = 5;
				}

				$this->checkForFirstPurchase($tblProtectionBooking->user_id);
				$tblProtectionBooking->store();

				$tblProtection = JTable::getInstance('Protection', 'BeseatedTable');
				$tblProtection->load($tblProtectionBooking->protection_id);
				$ownerID = $tblProtection->user_id;
				$cid     = $tblProtectionBooking->protection_booking_id;
				$extraParams['protectionBookingID'] = $tblProtectionBooking->protection_booking_id;

				$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
				$tblService->load($tblProtectionBooking->service_id);

				$service_name      = $tblService->service_name;
				$booking_date      = $tblProtectionBooking->booking_date;
				$booking_time      = $tblProtectionBooking->booking_time;
				$element_ID        = $tblProtectionBooking->protection_id;
				$notification_type = 'service.booking.paid';

				$from_user_id = $tblProtection->user_id;
				$to_user_id   = $tblPayment->user_id;

			}
			else if($elementType == 'protection.split')
			{
				$tblProtectionBookingSplit = JTable::getInstance('ProtectionBookingSplit', 'BeseatedTable');
				$tblProtectionBookingSplit->load($elementID);
				$tblProtectionBookingSplit->split_payment_status = 7;
				$tblProtectionBookingSplit->store();

				$userPaidAmount       = $tblPayment->amount;
				$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
				$tblProtectionBooking->load($tblProtectionBookingSplit->protection_booking_id);
				$tblProtectionBooking->remaining_amount     = $tblProtectionBooking->remaining_amount - $userPaidAmount;
				$tblProtectionBooking->org_remaining_amount = $tblProtectionBooking->org_remaining_amount - $org_paid_amount;
				$tblProtectionBooking->store();

				$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
				$tblProtectionBooking->load($tblProtectionBookingSplit->protection_booking_id);

				$remainingAmount      = $this->getSplitedRemainingAmount('protection',$tblProtectionBookingSplit->protection_booking_id);
				/*$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
				$tblProtectionBooking->load($tblProtectionBookingSplit->protection_booking_id);
				$tblProtectionBooking->remaining_amount = $remainingAmount;*/

				if($remainingAmount == 0)
				{
					$tblProtectionBooking->user_status       = 5;
					$tblProtectionBooking->protection_status = 5;
				}

				$this->checkForFirstPurchase($tblProtectionBookingSplit->user_id);
				$tblProtectionBooking->store();

				if($tblProtectionBooking->remaining_amount == '0.00')
				{
					$tblProtection = JTable::getInstance('Protection', 'BeseatedTable');
					$tblProtection->load($tblProtectionBooking->protection_id);
					$ownerID = $tblProtection->user_id;
				}
				else
				{
					$ownerID = $tblProtectionBooking->user_id;
				}

				$cid     = $tblProtectionBookingSplit->protection_booking_split_id;
				$extraParams['protectionBookingID'] = $tblProtectionBooking->protection_booking_id;
				$extraParams['invitationID']        = $tblProtectionBookingSplit->protection_booking_split_id;

				$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
				$tblService->load($tblProtectionBooking->service_id);

				$service_name      = $tblService->service_name;
				$booking_date      = $tblProtectionBooking->booking_date;
				$booking_time      = $tblProtectionBooking->booking_time;
				$element_ID        = $tblProtectionBooking->protection_id;
				$notification_type = 'protection.share.user.paid';

				$from_user_id = $tblProtectionBooking->user_id;
				$to_user_id   = $tblPayment->user_id;
			}
			else if($elementType == 'venue')
			{

				$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
				$tblVenueBooking->load($elementID);

				require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
				require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';

				//$biggestSpender =  BeseatedHelper::getBiggestSpender($tblVenueBooking->venue_id);

				$loginUser = JFactory::getUser();

				/*echo $elementID . ' | ' . $elementType . ' | ' . $pay_balance;
				exit;*/
				/*echo "<pre>";
				print_r($tblVenueBooking);
				echo "</pre>";
				exit;*/
				if($pay_balance)
				{
					$tblService = JTable::getInstance('Table', 'BeseatedTable');
					$tblService->load($tblVenueBooking->table_id);

					$this->sendPNtoSplitedUsers('venue',$tblVenueBooking->user_id,$elementID,$tblService->table_name,$tblVenueBooking->booking_date);

					$tblVenueBooking->user_status  = 5;
					$tblVenueBooking->venue_status = 5;
					$tblVenueBooking->remaining_amount  = '0.00';

					// Initialiase variables.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__beseated_venue_table_booking_split'))
						->set($db->quoteName('split_payment_status') . ' = ' . $db->quote(7))
						->set($db->quoteName('paid_by_owner') . ' = ' . $db->quote(1))
						->where($db->quoteName('split_payment_status') . ' = ' . $db->quote(2))
						->where($db->quoteName('venue_table_booking_id') . ' = ' . $db->quote($tblVenueBooking->venue_table_booking_id));

					// Set the query and execute the update.
					$db->setQuery($query);
					$db->execute();
				}
				else if($tblVenueBooking->is_splitted)
				{
					$userPaidAmount = $tblPayment->amount;
					$tblVenueBooking->remaining_amount = $tblVenueBooking->remaining_amount - $userPaidAmount;
					//echo "Remaining Amount : " . $tblProtectionBooking->remaining_amount . "<br />";
					//echo "User paid Amount : " . $userPaidAmount;
					//exit;
					$tblVenueBooking->store();
					$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
					$tblVenueBooking->load($elementID);
					$remainingAmount      = $this->getSplitedRemainingAmount('venue',$tblVenueBooking->venue_table_booking_id);

					if($remainingAmount == 0)
					{
						$tblVenueBooking->user_status       = 5;
						$tblVenueBooking->venue_status = 5;
					}
					//$tblProtectionBooking->remaining_amount = $remainingAmount;
				}
				else
				{
					$tblVenueBooking->venue_status = 5;
					$tblVenueBooking->user_status       = 5;
				}

				$this->checkForFirstPurchase($tblVenueBooking->user_id);
				$tblVenueBooking->store();

				$tblVenue = JTable::getInstance('Venue', 'BeseatedTable');
				$tblVenue->load($tblVenueBooking->venue_id);

				$is_day_club = $tblVenue->is_day_club;
				$ownerID = $tblVenue->user_id;
				$cid     = $tblVenueBooking->venue_table_booking_id;
				$extraParams['venueBookingID'] = $tblVenueBooking->venue_table_booking_id;

				$tblService = JTable::getInstance('VenueTable', 'BeseatedTable');
				$tblService->load($tblVenueBooking->table_id);

				$service_name      = $tblService->table_name;
				$booking_date      = $tblVenueBooking->booking_date;
				$booking_time      = $tblVenueBooking->booking_time;
				$element_ID        = $tblVenueBooking->venue_id;
				$notification_type = 'service.booking.paid';

				$from_user_id = $tblVenue->user_id;
				$to_user_id   = $tblPayment->user_id;

			}
			else if($elementType == 'venue.split')
			{
				$tblVenueBookingSplit = JTable::getInstance('VenueBookingSplit', 'BeseatedTable');
				$tblVenueBookingSplit->load($elementID);
				$tblVenueBookingSplit->split_payment_status = 7;
				$tblVenueBookingSplit->store();

				if($pay_balance)
				{
					$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
					$tblVenueBooking->load($tblVenueBookingSplit->venue_table_booking_id);
					$tblVenueBooking->user_status  = 5;
					$tblVenueBooking->venue_status = 5;

					// Initialiase variables.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__beseated_venue_table_booking_split'))
						->set($db->quoteName('split_payment_status') . ' = ' . $db->quote(7))
						->set($db->quoteName('paid_by_owner') . ' = ' . $db->quote(1))
						->where($db->quoteName('split_payment_status') . ' = ' . $db->quote(2))
						->where($db->quoteName('venue_table_booking_id') . ' = ' . $db->quote($tblVenueBooking->venue_table_booking_id));
					/*echo "<pre>";
					print_r($query->dump());
					echo "</pre>";
					exit;*/
					// Set the query and execute the update.
					$db->setQuery($query);
					$db->execute();
				}

				$userPaidAmount       = $tblPayment->amount;
				$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
				$tblVenueBooking->load($tblVenueBookingSplit->venue_table_booking_id);
				$tblVenueBooking->remaining_amount = $tblVenueBooking->remaining_amount - $userPaidAmount;
				if($pay_balance)
				{
					$tblVenueBooking->remaining_amount = 0;
				}
				$tblVenueBooking->store();

				$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
				$tblVenueBooking->load($tblVenueBookingSplit->venue_table_booking_id);

				$remainingAmount      = $this->getSplitedRemainingAmount('venue',$tblVenueBookingSplit->venue_table_booking_id);
				/*$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
				$tblProtectionBooking->load($tblProtectionBookingSplit->protection_booking_id);
				$tblProtectionBooking->remaining_amount = $remainingAmount;*/
				/*echo $remainingAmount;
				echo "<pre>";
				print_r($remainingAmount);
				echo "</pre>";
				exit;*/
				if($remainingAmount == 0)
				{
					$tblVenueBooking->user_status       = 5;
					$tblVenueBooking->venue_status = 5;
				}

				$this->checkForFirstPurchase($tblVenueBookingSplit->user_id);
				$tblVenueBooking->store();

				if($tblVenueBooking->remaining_amount == '0.00')
				{
					$tblVenue = JTable::getInstance('Venue', 'BeseatedTable');
					$tblVenue->load($tblVenueBooking->venue_id);
					$ownerID = $tblVenue->user_id;
				}
				else
				{
					$ownerID = $tblVenueBooking->user_id;
				}

				$cid     = $tblVenueBookingSplit->venue_table_booking_split_id;
				$extraParams['venueBookingID'] = $tblVenueBooking->venue_table_booking_id;
				$extraParams['invitationID']   = $tblVenueBookingSplit->venue_booking_split_id;

				$tblService = JTable::getInstance('VenueTable', 'BeseatedTable');
				$tblService->load($tblVenueBooking->table_id);

				$tblVenue = JTable::getInstance('Venue', 'BeseatedTable');
				$tblVenue->load($tblVenueBooking->venue_id);
				$is_day_club = $tblVenue->is_day_club;

				$service_name      = $tblService->table_name;
				$booking_date      = $tblVenueBooking->booking_date;
				$booking_time      = $tblVenueBooking->booking_time;
				$element_ID        = $tblVenueBooking->venue_id;
				$notification_type = 'venue.share.user.paid';

				$from_user_id = $tblVenueBooking->user_id;
				$to_user_id   = $tblPayment->user_id;
			}
			else if($elementType == 'venue.confirm')
			{
				$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
				$tblVenueBooking->load($elementID);

				require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
				require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
				require_once JPATH_SITE . '/components/com_ijoomeradv/extensions/beseated/helper.php';

				$loginUser = JFactory::getUser();

				$this->helper = new beseatedAppHelper;

				$tblVenueBooking->user_status        = $this->helper->getStatusID('confirmed');
				$tblVenueBooking->venue_status       = $this->helper->getStatusID('confirmed');
				$tblVenueBooking->has_booked         =  1;
				$tblVenueBooking->is_paid_deposite   =  1;

				$this->checkForFirstPurchase($tblVenueBooking->user_id);

				$tblVenueBooking->store();

				$tblVenue = JTable::getInstance('Venue', 'BeseatedTable');
				$tblVenue->load($tblVenueBooking->venue_id);

				$is_day_club = $tblVenue->is_day_club;
				$ownerID = $tblVenue->user_id;
				$cid     = $tblVenueBooking->venue_table_booking_id;
				$extraParams['venueBookingID'] = $tblVenueBooking->venue_table_booking_id;

				$tblService = JTable::getInstance('VenueTable', 'BeseatedTable');
				$tblService->load($tblVenueBooking->table_id);

				$service_name      = $tblService->table_name;
				$booking_date      = $tblVenueBooking->booking_date;
				$booking_time      = $tblVenueBooking->booking_time;
				$element_ID        = $tblVenueBooking->venue_id;
				$notification_type = 'venue.booking.confirm';

				$from_user_id = $tblVenue->user_id;
				$to_user_id   = $tblPayment->user_id;
			}
			else if($elementType == 'chauffeur')
			{
				$tblChauffeurBooking = JTable::getInstance('ChauffeurBooking', 'BeseatedTable');
				$tblChauffeurBooking->load($elementID);

				if($pay_balance)
				{
					$tblService = JTable::getInstance('ChauffeurService', 'BeseatedTable');
					$tblService->load($tblChauffeurBooking->service_id);

					$this->sendPNtoSplitedUsers('chauffeur',$tblChauffeurBooking->user_id,$elementID,$tblService->service_name,$tblChauffeurBooking->booking_date);

					$tblChauffeurBooking->user_status          = 5;
					$tblChauffeurBooking->chauffeur_status     = 5;
					$tblChauffeurBooking->remaining_amount     = '0.00';
					$tblChauffeurBooking->org_remaining_amount = '0.00';

					// Initialiase variables.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__beseated_chauffeur_booking_split'))
						->set($db->quoteName('split_payment_status') . ' = ' . $db->quote(7))
						->set($db->quoteName('paid_by_owner') . ' = ' . $db->quote(1))
						->where($db->quoteName('split_payment_status') . ' = ' . $db->quote(2))
						->where($db->quoteName('chauffeur_booking_id') . ' = ' . $db->quote($tblChauffeurBooking->chauffeur_booking_id));

					// Set the query and execute the update.
					$db->setQuery($query);
					$db->execute();
				}
				else if($tblChauffeurBooking->is_splitted)
				{
					$userPaidAmount = $tblPayment->amount;
					$tblChauffeurBooking->remaining_amount     = $tblChauffeurBooking->remaining_amount - $userPaidAmount;
					$tblChauffeurBooking->org_remaining_amount = $tblChauffeurBooking->org_remaining_amount - $org_paid_amount;
					//echo "Remaining Amount : " . $tblProtectionBooking->remaining_amount . "<br />";
					//echo "User paid Amount : " . $userPaidAmount;
					//exit;
					$tblChauffeurBooking->store();
					$tblChauffeurBooking = JTable::getInstance('ChauffeurBooking', 'BeseatedTable');
					$tblChauffeurBooking->load($elementID);
					$remainingAmount      = $this->getSplitedRemainingAmount('chauffeur',$tblChauffeurBooking->chauffeur_booking_id);

					if($remainingAmount == 0)
					{
						$tblChauffeurBooking->user_status      = 5;
						$tblChauffeurBooking->chauffeur_status = 5;
					}
					//$tblProtectionBooking->remaining_amount = $remainingAmount;
				}
				else
				{
					$tblChauffeurBooking->chauffeur_status = 5;
					$tblChauffeurBooking->user_status       = 5;
				}

				$this->checkForFirstPurchase($tblChauffeurBooking->user_id);
				$tblChauffeurBooking->store();

				$tblChauffeur = JTable::getInstance('Chauffeur', 'BeseatedTable');
				$tblChauffeur->load($tblChauffeurBooking->chauffeur_id);
				$ownerID = $tblChauffeur->user_id;
				$cid     = $tblChauffeurBooking->chauffeur_booking_id;
				$extraParams['chauffeurBookingID'] = $tblChauffeurBooking->chauffeur_booking_id;

				$tblService = JTable::getInstance('ChauffeurService', 'BeseatedTable');
				$tblService->load($tblChauffeurBooking->service_id);

				$service_name      = $tblService->service_name;
				$booking_date      = $tblChauffeurBooking->booking_date;
				$booking_time      = $tblChauffeurBooking->booking_time;
				$element_ID        = $tblChauffeurBooking->chauffeur_id;
				$notification_type = 'service.booking.paid';

				$from_user_id = $tblChauffeur->user_id;
				$to_user_id   = $tblPayment->user_id;
			}
			else if($elementType == 'chauffeur.split')
			{
				$tblChauffeurBookingSplit = JTable::getInstance('ChauffeurBookingSplit', 'BeseatedTable');
				$tblChauffeurBookingSplit->load($elementID);
				$tblChauffeurBookingSplit->split_payment_status = 7;
				$tblChauffeurBookingSplit->store();

				$userPaidAmount       = $tblPayment->amount;
				$tblChauffeurBooking = JTable::getInstance('ChauffeurBooking', 'BeseatedTable');
				$tblChauffeurBooking->load($tblChauffeurBookingSplit->chauffeur_booking_id);
				$tblChauffeurBooking->remaining_amount     = $tblChauffeurBooking->remaining_amount - $userPaidAmount;
				$tblChauffeurBooking->org_remaining_amount = $tblChauffeurBooking->org_remaining_amount - $org_paid_amount;
				$tblChauffeurBooking->store();

				$tblChauffeurBooking = JTable::getInstance('ChauffeurBooking', 'BeseatedTable');
				$tblChauffeurBooking->load($tblChauffeurBookingSplit->chauffeur_booking_id);

				$remainingAmount      = $this->getSplitedRemainingAmount('chauffeur',$tblChauffeurBookingSplit->chauffeur_booking_id);

				/*$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
				$tblProtectionBooking->load($tblProtectionBookingSplit->protection_booking_id);
				$tblProtectionBooking->remaining_amount = $remainingAmount;*/

				if($remainingAmount == 0)
				{
					$tblChauffeurBooking->user_status      = 5;
					$tblChauffeurBooking->chauffeur_status = 5;
				}

				$this->checkForFirstPurchase($tblChauffeurBookingSplit->user_id);
				$tblChauffeurBooking->store();

				if($tblChauffeurBooking->remaining_amount == '0.00')
				{
					$tblChauffeur = JTable::getInstance('Chauffeur', 'BeseatedTable');
					$tblChauffeur->load($tblChauffeurBooking->chauffeur_id);
					$ownerID = $tblChauffeur->user_id;
				}
				else
				{
					$ownerID = $tblChauffeurBooking->user_id;
				}


				$cid     = $tblChauffeurBookingSplit->chauffeur_booking_split_id;
				$extraParams['chauffeurBookingID'] = $tblChauffeurBooking->chauffeur_booking_id;
				$extraParams['invitationID']       = $tblChauffeurBookingSplit->chauffeur_booking_split_id;

				$tblService = JTable::getInstance('ChauffeurService', 'BeseatedTable');
				$tblService->load($tblChauffeurBooking->service_id);

				$service_name      = $tblService->service_name;
				$booking_date      = $tblChauffeurBooking->booking_date;
				$booking_time      = $tblChauffeurBooking->booking_time;
				$element_ID        = $tblChauffeurBooking->chauffeur_id;
				$notification_type = 'chauffeur.share.user.paid';

				$from_user_id = $tblChauffeurBooking->user_id;
				$to_user_id   = $tblPayment->user_id;

			}
			else if($elementType == 'yacht')
			{
				$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
				$tblYachtBooking->load($elementID);

				if($pay_balance)
				{
					$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
					$tblService->load($tblYachtBooking->service_id);

					$this->sendPNtoSplitedUsers('yacht',$tblYachtBooking->user_id,$elementID,$tblService->service_name,$tblYachtBooking->booking_date);

					$tblYachtBooking->user_status      = 5;
					$tblYachtBooking->yacht_status     = 5;
					$tblYachtBooking->remaining_amount = '0.00';
					$tblYachtBooking->org_remaining_amount = '0.00';

					// Initialiase variables.
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base update statement.
					$query->update($db->quoteName('#__beseated_yacht_booking_split'))
						->set($db->quoteName('split_payment_status') . ' = ' . $db->quote(7))
						->set($db->quoteName('paid_by_owner') . ' = ' . $db->quote(1))
						->where($db->quoteName('split_payment_status') . ' = ' . $db->quote(2))
						->where($db->quoteName('yacht_booking_id') . ' = ' . $db->quote($tblYachtBooking->yacht_booking_id));

					// Set the query and execute the update.
					$db->setQuery($query);
					$db->execute();
				}
				else if($tblYachtBooking->is_splitted)
				{
					$userPaidAmount = $tblPayment->amount;
					$tblYachtBooking->remaining_amount = $tblYachtBooking->remaining_amount - $userPaidAmount;
					$tblYachtBooking->org_remaining_amount = $tblYachtBooking->org_remaining_amount - $org_paid_amount;
					//echo "Remaining Amount : " . $tblProtectionBooking->remaining_amount . "<br />";
					//echo "User paid Amount : " . $userPaidAmount;
					//exit;
					$tblYachtBooking->store();
					$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
					$tblYachtBooking->load($elementID);
					$remainingAmount      = $this->getSplitedRemainingAmount('yacht',$tblYachtBooking->yacht_booking_id);

					if($remainingAmount == 0)
					{
						$tblYachtBooking->user_status      = 5;
						$tblYachtBooking->yacht_status = 5;
					}
					//$tblProtectionBooking->remaining_amount = $remainingAmount;
				}
				else
				{
					$tblYachtBooking->yacht_status = 5;
					$tblYachtBooking->user_status       = 5;
				}

				$this->checkForFirstPurchase($tblYachtBooking->user_id);
				$tblYachtBooking->store();

				$tblYacht = JTable::getInstance('Yacht', 'BeseatedTable');
				$tblYacht->load($tblYachtBooking->yacht_id);
				$ownerID = $tblYacht->user_id;
				$cid     = $tblYachtBooking->yacht_booking_id;

				$extraParams['yachtBookingID'] = $tblYachtBooking->yacht_booking_id;

				$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
				$tblService->load($tblYachtBooking->service_id);

				$service_name      = $tblService->service_name;
				$booking_date      = $tblYachtBooking->booking_date;
				$booking_time      = $tblYachtBooking->booking_time;
				$element_ID        = $tblYachtBooking->yacht_id;
				$notification_type = 'service.booking.paid';

				$from_user_id = $tblYacht->user_id;
				$to_user_id   = $tblPayment->user_id;

			}
			else if($elementType == 'yacht.split')
			{
				$tblYachtBookingSplit = JTable::getInstance('YachtBookingSplit', 'BeseatedTable');
				$tblYachtBookingSplit->load($elementID);
				$tblYachtBookingSplit->split_payment_status = 7;
				$tblYachtBookingSplit->store();

				$userPaidAmount       = $tblPayment->amount;
				$YachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
				$YachtBooking->load($tblYachtBookingSplit->yacht_booking_id);
				$YachtBooking->remaining_amount     = $YachtBooking->remaining_amount - $userPaidAmount;
				$YachtBooking->org_remaining_amount = $YachtBooking->org_remaining_amount - $org_paid_amount;
				$YachtBooking->store();

				$YachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
				$YachtBooking->load($tblYachtBookingSplit->yacht_booking_id);

				$remainingAmount      = $this->getSplitedRemainingAmount('yacht',$tblYachtBookingSplit->yacht_booking_id);

				/*$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
				$tblProtectionBooking->load($tblProtectionBookingSplit->protection_booking_id);
				$tblProtectionBooking->remaining_amount = $remainingAmount;*/

				if($remainingAmount == 0)
				{
					$YachtBooking->user_status      = 5;
					$YachtBooking->yacht_status = 5;
				}

				$this->checkForFirstPurchase($tblYachtBookingSplit->user_id);
				$YachtBooking->store();

				if($YachtBooking->remaining_amount == '0.00')
				{
					$tblYacht = JTable::getInstance('Yacht', 'BeseatedTable');
					$tblYacht->load($YachtBooking->yacht_id);
					$ownerID = $tblYacht->user_id;
				}
				else
				{
					$ownerID = $YachtBooking->user_id;
				}

				$cid     = $tblYachtBookingSplit->yacht_booking_split_id;

				$extraParams['yachtBookingID']      = $YachtBooking->yacht_booking_id;
				$extraParams['invitationID']        = $tblYachtBookingSplit->yacht_booking_split_id;

				$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
				$tblService->load($YachtBooking->service_id);

				$service_name      = $tblService->service_name;
				$booking_date      = $YachtBooking->booking_date;
				$booking_time      = $YachtBooking->booking_time;
				$element_ID        = $YachtBooking->yacht_id;
				$notification_type = 'yacht.share.user.paid';

				$from_user_id = $YachtBooking->user_id;
				$to_user_id   = $tblPayment->user_id;

			}
			else if($elementType == 'event')
			{
				$tblTicketBooking = JTable::getInstance('TicketBooking', 'BeseatedTable');
				$tblTicketBooking->load($elementID);

				$tblEvent = JTable::getInstance('Event', 'BeseatedTable');
				$tblEvent->load($tblTicketBooking->event_id);

				$tblEventtickettypezone  = JTable::getInstance('Eventtickettypezone', 'BeseatedTable');
		        $tblEventtickettypezone->load($tblTicketBooking->ticket_type_id);

				$tblTicketBooking->status = 5;

				$tblTicketBooking->store();

				$ticketsIDs = json_decode($tblTicketBooking->tickets_id);
				foreach ($ticketsIDs as $key => $ticketID)
				{
					$tblTicketBookingDetail = JTable::getInstance('TicketBookingDetail', 'BeseatedTable');
					$tblTicketBookingDetail->load(0);
					$tbdPost                      = array();
					$tbdPost['ticket_booking_id'] = $tblTicketBooking->ticket_booking_id;
					$tbdPost['ticket_id']         = $ticketID;
					$tbdPost['event_id']          = $tblTicketBooking->event_id;
					$tbdPost['user_id']           = $tblTicketBooking->user_id;
					$tbdPost['booking_user_id']   = $tblTicketBooking->user_id;
					$tbdPost['ticket_price']      = $tblTicketBooking->ticket_price;
					$tbdPost['time_stamp']        = time();
					$tblTicketBookingDetail->bind($tbdPost);
					$tblTicketBookingDetail->store();
				}

				$tblEvent->available_ticket                 = $tblEvent->available_ticket - $tblTicketBooking->total_ticket;
				$tblEventtickettypezone->available_tickets  = $tblEventtickettypezone->available_tickets - $tblTicketBooking->total_ticket;
				$tblEvent->has_ticket_booked = '1';
				$tblEvent->store();
				$tblEventtickettypezone->store();

				$this->checkForFirstPurchase($tblTicketBooking->user_id);
				$ownerID = $tblTicketBooking->user_id;
				$cid = $tblTicketBooking->ticket_booking_id;
				$extraParams['ticketBookingID']      =  $tblTicketBooking->ticket_booking_id;

				$service_name      = $tblEvent->event_name;
				$booking_date      = $tblEvent->event_date;
				$booking_time      = $tblEvent->event_time;
				$element_ID        = $tblEvent->event_id;
				$notification_type = 'ticket.booking.paid';

				$from_user_id = $tblEvent->user_id;
				$to_user_id   = $tblPayment->user_id;
			}

			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base update statement.
			$query->update($db->quoteName('#__beseated_loyalty_point'))
				->set($db->quoteName('is_valid') . ' = ' . $db->quote(1))
				->where($db->quoteName('cid') . ' = ' . $db->quote($paymentID));

			// Set the query and execute the update.
			$db->setQuery($query);
			$db->execute();
		}

		$tblPayment->store();

		if(strtolower($elementType) == 'protection' || strtolower($elementType) == 'venue' || strtolower($elementType) == 'chauffeur' || strtolower($elementType) == 'yacht' || strtolower($elementType) == 'event'  || strtolower($elementType) == 'venue.confirm')
		{
			$bookedType = 'booking';
			$element_Type = $elementType;

			if(strtolower($elementType) == 'venue.confirm')
			{
				$element_Type = 'venue';
			}
			else
			{
				$element_Type = $elementType;
			}

			if($source == 'com_ijoomeradv')
			{
				$returnRedirectLink = "index.php?success=true&payment_id={$paymentID}";
				$bookedSuccessMsg   = JText::_('Your payment has been done successfully');
			}
			else
			{
				$returnRedirectLink     =  JUri::root().'index.php?option=com_beseated&view=userbookings&booking_type='.$element_Type.'&Itemid='.$guestBookingsItemid;
				//$returnRedirectLink = "index.php?success=true&payment_id={$paymentID}";
				$bookedSuccessMsg   = JText::_('Your payment has been done successfully');
			}
		}



		if(strtolower($elementType) == 'protection.split' || strtolower($elementType) == 'venue.split' || strtolower($elementType) == 'chauffeur.split' || strtolower($elementType) == 'yacht.split')
		{
			$bookedType = 'share';

			$element_Type = explode('.', $elementType);
			$element_Type = $element_Type[0];

			if($source == 'com_ijoomeradv')
			{
				$returnRedirectLink = "index.php?success=true&payment_id={$paymentID}";
				$bookedSuccessMsg   = JText::_('Your payment has been done successfully');
			}
			else
			{
				$returnRedirectLink     =  JUri::root().'index.php?option=com_beseated&view=userbookings&booking_type=event&Itemid='.$guestBookingsItemid;
				//$returnRedirectLink = "index.php?success=true&payment_id={$paymentID}";
				$bookedSuccessMsg   = JText::_('Your payment has been done successfully');
			}
		}


//is_day_club
		/*echo $returnRedirectLink;
		exit;*/

		if($response_array['order_status'] == 'Success' || $paymentStatusText == 'Completed')
		{
			require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/helper.php';

			$guestUserDetail  = JFactory::getUser($tblPayment->user_id);
			$this->helper = new beseatedAppHelper();

			//$guestUserDetail = $this->helper->guestUserDetail($this->IJUserID);

			if($elementType == 'venue' || $elementType == 'venue.split')
			{
				if($is_day_club == '1')
				{
					$title = JText::sprintf(
						'COM_IJOOMERADV_BESEATED_PAID_BY_USER_FOR_DAY_VENUE',
						$guestUserDetail->name,
						$service_name,
						$this->helper->convertDateFormat($booking_date),
						$this->helper->convertToHM($booking_time)
						);
				}
				else
				{
					$title = JText::sprintf(
						'COM_IJOOMERADV_BESEATED_PAID_BY_USER_FOR_NIGHT_VENUE',
						$guestUserDetail->name,
						$service_name,
						$this->helper->convertDateFormat($booking_date)
						);
				}

			}
			elseif($elementType == 'venue.confirm')
			{
				if($is_day_club == '1')
				{
					$formatedBookingTime = $this->helper->convertToHM($tblVenueBooking->booking_time);

					$title = JText::sprintf(
						'COM_BESEATED_NOTIFICATION_DAY_VENUE_TABLE_BOOKING_CONFIRMED',
						$guestUserDetail->name,
						$service_name,
						$this->helper->convertDateFormat($booking_date),
						$this->helper->convertToHM($booking_time)
						);
				}
				else
				{
					$formatedBookingTime = '-';
					$title = JText::sprintf(
						'COM_BESEATED_NOTIFICATION_NIGHT_VENUE_TABLE_BOOKING_CONFIRMED',
						$guestUserDetail->name,
						$service_name,
						$this->helper->convertDateFormat($booking_date)
						);
				}

			}
			else
			{
				$element_type = str_replace('.', '_', $elementType);

				$title = JText::sprintf(
						'COM_IJOOMERADV_BESEATED_PAID_BY_USER_FOR_'.strtoupper($element_type),
						$guestUserDetail->name,
						$service_name,
						$this->helper->convertDateFormat($booking_date),
						$this->helper->convertToHM($booking_time)
						);
			}


			$actor            = $guestUserDetail->id;
			$target           = $ownerID;
			$elementID        = $element_ID;
			$elementType      = ucfirst($elementType);
			$cid              = $cid;


			$loyaltyPoint = self::getLoyaltyPoint($paymentID,$actor);

			//$extraParams[$paramsID] = $cid;

			require_once JPATH_SITE . '/components/com_ijoomeradv/extensions/beseated/helper.php';

			$this->helper = new beseatedAppHelper;


				require_once JPATH_SITE . '/components/com_beseated/helpers/email.php';
				$this->emailHelper       = new BeseatedEmailHelper;

				if(strtolower($elementType) == 'protection' || strtolower($elementType) == 'protection.split')
				{
					if($tblProtectionBooking->user_status == '5' && $tblProtectionBooking->protection_status = '5')
					{
						$protectionDefaultImage = $this->helper->getElementDefaultImage($tblProtectionBooking->protection_id,'Protection');

						$thumb = Juri::base().'images/beseated/'.$protectionDefaultImage->thumb_image;
						$bookingDate = date('d F Y',strtotime($tblProtectionBooking->booking_date));
						$bookingTime = $this->helper->convertToHM($tblProtectionBooking->booking_time);

						$tblProtection = JTable::getInstance('Protection', 'BeseatedTable');
						$tblProtection->load($tblProtectionBooking->protection_id);

						$protectionManager = JFactory::getUser($tblProtection->user_id);

						$this->emailHelper->PaymentReceivedManagerMail($tblProtection->protection_name,$guestUserDetail->name,$service_name,$this->helper->convertDateFormat($booking_date),$this->helper->convertToHM($booking_time),$is_day = 1,$protectionManager->email);
						$this->emailHelper->protectionBookingconfirmedUserMail($guestUserDetail->name,$protectionManager->name,$thumb,$tblProtectionBooking->protection_booking_id,$bookingDate,$bookingTime,$protectionManager->name,$tblProtectionBooking->total_hours,$tblProtectionBooking->total_guard,$guestUserDetail->name,$guestUserDetail->email,$tblProtectionBooking->booking_currency_code,number_format($tblProtectionBooking->price_per_hours,0),number_format($tblProtectionBooking->total_price,0),$loyaltyPoint,$tblProtection->refund_policy,$guestUserDetail->email);
						$tblProtectionBooking->has_booked = 1;
						$tblProtectionBooking->store();
					}
				}
				elseif(strtolower($elementType) == 'yacht' || strtolower($elementType) == 'yacht.split')
				{
					if($tblYachtBooking->user_status == '5' && $tblYachtBooking->yacht_status = '5')
					{
						$yachtDefaultImage = $this->helper->getElementDefaultImage($tblYachtBooking->yacht_id,'Yacht');
						$thumb             = Juri::base().'images/beseated/'.$yachtDefaultImage->thumb_image;
						$bookingDate       = date('d F Y',strtotime($tblYachtBooking->booking_date));
						$bookingTime       = $this->helper->convertToHM($tblYachtBooking->booking_time);

						$tblYacht = JTable::getInstance('Yacht', 'BeseatedTable');
						$tblYacht->load($tblYachtBooking->yacht_id);

						$yachtManager = $this->helper->guestUserDetail($tblYacht->user_id);

						$this->emailHelper->PaymentReceivedManagerMail($tblYacht->yacht_name,$guestUserDetail->name,$service_name,$this->helper->convertDateFormat($booking_date),$this->helper->convertToHM($booking_time),$is_day = 1,$yachtManager->email);
						$this->emailHelper->yachtBookingconfirmedUserMail($guestUserDetail->name,$yachtManager->full_name,$thumb,$tblYachtBooking->yacht_booking_id,$bookingDate,$bookingTime,$yachtManager->full_name,$tblService->service_name,$tblService->dock,$tblService->capacity,$tblYachtBooking->total_hours,$guestUserDetail->name,$guestUserDetail->email,$tblYachtBooking->booking_currency_code,number_format($tblYachtBooking->price_per_hours,0),$tblYachtBooking->total_hours,number_format($tblYachtBooking->total_price,0),$loyaltyPoint,$tblYacht->refund_policy,$guestUserDetail->email);
						$tblYachtBooking->has_booked = 1;
						$tblYachtBooking->store();
					}
				}
				elseif(strtolower($elementType) == 'chauffeur' || strtolower($elementType) == 'chauffeur.split')
				{
					if($tblChauffeurBooking->user_status == '5' && $tblChauffeurBooking->chauffeur_status = '5')
					{
						$chauffeurDefaultImage = $this->helper->getElementDefaultImage($tblChauffeurBooking->chauffeur_id,'Chauffeur');
						$thumb             = Juri::base().'images/beseated/'.$chauffeurDefaultImage->thumb_image;
						$bookingDate       = date('d F Y',strtotime($tblChauffeurBooking->booking_date));
						$bookingTime       = $this->helper->convertToHM($tblChauffeurBooking->booking_time);

						$tblChauffeur = JTable::getInstance('Chauffeur', 'BeseatedTable');
						$tblChauffeur->load($tblChauffeurBooking->chauffeur_id);

						$chauffeurManager = JFactory::getUser($tblChauffeur->user_id);

						//echo "<pre>";print_r($tblChauffeur->chauffeur_name.'='.$guestUserDetail->name.'='.$service_name.'='.$booking_date.'='.$booking_time.'='.$chauffeurManager->email);echo "</pre>";exit;

						$this->emailHelper->PaymentReceivedManagerMail($tblChauffeur->chauffeur_name,$guestUserDetail->name,$service_name,$this->helper->convertDateFormat($booking_date),$this->helper->convertToHM($booking_time),$is_day = 1,$chauffeurManager->email);
						$this->emailHelper->chauffeurBookingconfirmedUserMail($guestUserDetail->name,$chauffeurManager->name,$thumb,$tblChauffeurBooking->chauffeur_booking_id,$bookingDate,$bookingTime,$chauffeurManager->name,$tblService->service_name,$tblChauffeurBooking->pickup_location,$tblChauffeurBooking->dropoff_location,$tblChauffeurBooking->capacity,$guestUserDetail->name,$tblChauffeurBooking->booking_currency_code,number_format($tblChauffeurBooking->total_price,0),$guestUserDetail->email,$loyaltyPoint,$tblChauffeur->refund_policy,$guestUserDetail->email);
						$tblChauffeurBooking->has_booked = 1;
						$tblChauffeurBooking->store();
					}
				}
				elseif(strtolower($elementType) == 'venue' || strtolower($elementType) == 'venue.split')
				{
					if($tblVenueBooking->user_status == '5' && $tblVenueBooking->venue_status = '5')
					{
						$tblVenue = JTable::getInstance('Venue', 'BeseatedTable');
						$tblVenue->load($tblVenueBooking->venue_id);

						$venueManager = JFactory::getUser($tblVenue->user_id);

						$this->emailHelper->PaymentReceivedManagerMail($tblVenue->venue_name,$guestUserDetail->name,$service_name,$this->helper->convertDateFormat($booking_date),$this->helper->convertToHM($booking_time),$tblVenue->is_day_club,$venueManager->email);
						$tblVenueBooking->has_booked = 1;
						$tblVenueBooking->store();

						$biggestSpender =  BeseatedHelper::getBiggestSpender($tblVenueBooking->venue_id);

						$loginUser = JFactory::getUser();

						if(in_array($loginUser->id, $biggestSpender))
						{
							BeseatedEmailHelper::biggestSpenderMail($loginUser->name,$tblVenue->venue_name,$loginUser->email);
						}
					}
				}
				elseif(strtolower($elementType) == 'venue.confirm')
				{
					if($tblVenueBooking->user_status == '13' && $tblVenueBooking->venue_status = '13')
					{
						$venueDefaultImage = $this->helper->getElementDefaultImage($tblVenue->venue_id,'Venue');

						$showDirection = "Show Directions";
						$venueThumb    = Juri::base().'images/beseated/'.$venueDefaultImage->thumb_image;
						$bookingDate   = date('d F Y',strtotime($tblVenueBooking->booking_date));
						$companyDetail = $this->helper->guestUserDetail($tblVenue->user_id);

						$passkey = ($tblVenueBooking->passkey) ? $tblVenueBooking->passkey : '-';

						$bottleRow = $this->getVenueBookingBottels($tblVenueBooking->venue_table_booking_id);

						$this->emailHelper->venueBookingconfirmedUserMail($guestUserDetail->name,$companyDetail->full_name,$venueThumb,$tblVenue->location,$companyDetail->phone,$showDirection,$cid,$bookingDate,$formatedBookingTime,$service_name,$tblVenue->currency_code,number_format($tblService->min_price,0),$tblVenueBooking->male_guest,$tblVenueBooking->female_guest,$passkey,$guestUserDetail->name,$guestUserDetail->email,$bottleRow,number_format($tblVenueBooking->total_bottle_price,0),$tblVenue->refund_policy,$guestUserDetail->email);

					}
				}
				elseif(strtolower($elementType) == 'event')
				{
					$tickets = json_decode($tblTicketBooking->tickets_id);

					$ticketsRow = $this->getTicketsImages($tickets);

					$tblEvent = JTable::getInstance('Event', 'BeseatedTable');
					$tblEvent->load($tblTicketBooking->event_id);
					$event_date = date('d F Y',strtotime($tblEvent->event_date));

					//$venueManager = JFactory::getUser($tblVenue->user_id);
					$query = "SELECT user_id FROM `#__user_usergroup_map` WHERE group_id = 8";
					$db = JFactory::getDbo();
					$db->setQuery($query);
					$adminsIds = $db->loadColumn();

					$query_users = "SELECT name,email FROM `#__users` WHERE id IN (".implode(",", $adminsIds).")";
					$db->setQuery($query_users);
					$adminDetails = $db->loadObjectList();

					foreach ($adminDetails as $key => $detail)
					{
						$this->emailHelper->eventBookingconfirmedUserMail($guestUserDetail->name,$tblEvent->event_name,$tblTicketBooking->ticket_booking_id,$tblEvent->image,$event_date,$tblEvent->location,$tblTicketBooking->total_ticket,$ticketsRow,$guestUserDetail->email,$tblTicketBooking->booking_currency_code,number_format($tblTicketBooking->ticket_price,0),number_format($tblTicketBooking->total_price,0),$loyaltyPoint,$guestUserDetail->email);
						$this->emailHelper->PaymentReceivedManagerMail($detail->name,$guestUserDetail->name,$tblEvent->event_name,$this->helper->convertDateFormat($tblEvent->event_date),$this->helper->convertToHM($tblEvent->event_time),$is_day = 1,$detail->email);
					}
				}

				if(strtolower($elementType) != 'event')
				{
					if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notification_type,$title,$cid,$extraParams,$extraParams[strtolower($element_Type).'BookingID']))
					{
						$this->sendPushNotication($target,$title,$notification_type,ucfirst($element_Type),$extraParams[strtolower($element_Type).'BookingID']);
					}
				}

				$tblReadElementBooking = JTable::getInstance('ReadElementBooking', 'BeseatedTable');
				$tblReadElementBooking->load(0);
				$tblReadElementBooking->booked_type  = $bookedType;
				$tblReadElementBooking->element_type = $element_Type;
				$tblReadElementBooking->booking_id   = $elements_booking_ID;
				$tblReadElementBooking->from_user_id = $from_user_id;
				$tblReadElementBooking->to_user_id   = $to_user_id;
				$tblReadElementBooking->store();


			$app = JFactory::getApplication();
			$app->redirect($returnRedirectLink,$bookedSuccessMsg);
			$app->close();

		}else
		{
			$app = JFactory::getApplication();
			$app->redirect("index.php?success=false");
			$app->close();
		}
	}

	public 	function decrypt($encryptedText,$key)
	{
		$secretKey = $this->hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$encryptedText=$this->hextobin($encryptedText);
		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
		mcrypt_generic_init($openMode, $secretKey, $initVector);
		$decryptedText = mdecrypt_generic($openMode, $encryptedText);
		$decryptedText = rtrim($decryptedText, "\0");
		mcrypt_generic_deinit($openMode);
		return $decryptedText;
	}

	public function hextobin($hexString)
	{
		$length = strlen($hexString);
		$binString="";
		$count=0;
		while($count<$length)
		{
			$subString =substr($hexString,$count,2);

			$packedString = pack("H*",trim($subString));

			if ($count==0)
			{
				$binString=$packedString;
			}
			else
			{
				$binString.=$packedString;
			}
			$count+=2;
		}
		return $binString;
	}

	public function getSplitedRemainingAmount($bookingType,$bookingID)
	{
		/*echo $bookingType . ' | ' . $bookingID;
		exit;*/
		// Initialiase variables.
		$db                 = JFactory::getDbo();
		$query              = $db->getQuery(true);
		$tableName          = "";
		$bookingIDFieldName = "";
		if(strtolower($bookingType) == 'protection')
		{
			$tableName          = "#__beseated_protection_booking_split";
			$bookingIDFieldName = "protection_booking_id";
		}
		else if(strtolower($bookingType) == 'venue')
		{
			$tableName          = "#__beseated_venue_table_booking_split";
			$bookingIDFieldName = "venue_table_booking_id";
		}
		else if(strtolower($bookingType) == 'chauffeur')
		{
			$tableName          = "#__beseated_chauffeur_booking_split";
			$bookingIDFieldName = "chauffeur_booking_id";
		}
		else if(strtolower($bookingType) == 'yacht')
		{
			$tableName          = "#__beseated_yacht_booking_split";
			$bookingIDFieldName = "yacht_booking_id";
		}
		else{
			return array();
		}

		// Create the base select statement.
		$query->select('sum(splitted_amount)')
			->from($db->quoteName($tableName))
			->where($db->quoteName($bookingIDFieldName) . ' = ' . $db->quote($bookingID))
			->where($db->quoteName('split_payment_status') . ' <> ' . $db->quote(7));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadResult();
		/*echo "Sum of Rem : <pre>";
		print_r($result);
		echo "</pre>";
		exit;*/

		return $result;
	}

	public function checkForFirstPurchase($userID)
	{
		require_once JPATH_SITE . '/components/com_beseated/helpers/email.php';

		// Initialiase variables.
		$fpUser = JFactory::getUser($userID);
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$this->emailHelper       = new BeseatedEmailHelper;

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_user_refer'))
			->where($db->quoteName('is_registered') . ' = ' . $db->quote('1'))
			->where($db->quoteName('is_fp_done') . ' = ' . $db->quote('0'))
			->where($db->quoteName('ref_user_id') . ' = ' . $db->quote($fpUser->id))
			->where($db->quoteName('refer_email') . ' = ' . $db->quote($fpUser->email))
			->order($db->quoteName('time_stamp') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$userReferObj = $db->loadObject();

		if($userReferObj)
		{
			JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
			$tblRefer = JTable::getInstance('Refer', 'BeseatedTable');
			$tblRefer->load($userReferObj->refer_id);

			$points = 50;

			$tblLoyaltyPoint = JTable::getInstance('LoyaltyPoint', 'BeseatedTable');
			$tblLoyaltyPoint->load(0);

			$lpPost['user_id']    = $userReferObj->userid;
			$lpPost['earn_point'] = $points;
			$lpPost['point_app']  = 'inviteduserfp';
			$lpPost['title']      = 'REFER A FRIEND';
			$lpPost['cid']        = $userReferObj->refer_id;
			$lpPost['is_valid']   = '1';
			$lpPost['created']    = date('Y-m-d H:i:s');
			$lpPost['time_stamp'] = time();

			$tblLoyaltyPoint->bind($lpPost);

			if($tblLoyaltyPoint->store())
			{
				$tblRefer->is_fp_done = 1;
				$tblRefer->fp_date = date('Y-m-d');
				if($tblRefer->store())
				{
					$refByUser   = JFactory::getUser($userReferObj->userid);
					//$bctedConfig = $this->getExtensionParam();
					//$earnPoint   = $bctedConfig->friends_referral;
					$this->emailHelper->referedFriendMail($refByUser->name,$refByUser->email,$points,$fpUser->name);
				}
			}
		}
	}

	public 	function sendPushNotication($user_id,$title,$type,$elementType,$bookingID)
	{
		require_once JPATH_SITE.'/components/com_ijoomeradv/helpers/helper.php';

		$this->helper = new IjoomeradvHelper();

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('value')
			->from($db->quoteName('#__ijoomeradv_config'))
			->where($db->quoteName('name') . ' = ' . $db->quote('IJOOMER_PUSH_DEPLOYMENT_IPHONE'));

		// Set the query and load the result.
		$db->setQuery($query);
		$depMode = $db->loadResult();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseWarning(500, $db->getErrorMsg());

			return null;
		}



		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.userid, a.jomsocial_params, a.device_token, a.device_type')
			->from($db->qn('#__ijoomeradv_users','a'))
			->where($db->qn('a.userid') . ' = ' . $db->q($user_id));
		$query->select('b.user_type')
			->join('LEFT','#__beseated_user_profile AS b ON b.user_id=a.userid');

		// Set the query and load the result.
		$db->setQuery($query);

		$notificationDetail = $db->loadObject();


		if($notificationDetail->device_type == 'iphone')
		{
			$options                       = array();
			$options['device_token']       = $notificationDetail->device_token;
			$options['live']               = intval($depMode);
			$options['aps']['alert']       = $title;
			$options['aps']['type']        = $type;
			$options['aps']['elementType'] = $elementType;
			$options['aps']['id']          = $bookingID;

			$this->sendIphonePushNotification($options,$notificationDetail->user_type);


		}
		else if ($notificationDetail->device_type == 'android')
		{
			$options                        = array();
			$options['registration_ids']    = array($notificationDetail->device_token);
			$options['data']['message']     = $title;
			$options['data']['type']        = $type;
			$options['data']['elementType'] = $elementType;
			$options['data']['id']          = $bookingID;

			$this->sendAndroidPushNotification($options,$notificationDetail->user_type);

		}

	}

	function sendIphonePushNotification($options,$userType)
	{
		$server = ($options['live']) ? 'ssl://gateway.push.apple.com:2195' : 'ssl://gateway.sandbox.push.apple.com:2195';

		if($userType == 'beseated_guest')
		{
			$keyCertFilePath = JPATH_SITE . '/components/com_ijoomeradv/certificates/certificates_guest.pem';
		}
		else
		{
			$keyCertFilePath = JPATH_SITE . '/components/com_ijoomeradv/certificates/certificates_manager.pem';
		}

		// Construct the notification payload
		$body = array();
		$body['aps'] = $options['aps'];
		$body['aps']['badge'] = (isset($options['aps']['badge']) && !empty($options['aps']['badge'])) ? $options['aps']['badge'] : 0;
		$body['aps']['sound'] = (isset($options['aps']['sound']) && !empty($options['aps']['sound'])) ? $options['aps']['sound'] : 'default';

		$payload = json_encode($body);

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $keyCertFilePath);
		$fp = stream_socket_client($server, $error, $errorString, 60, STREAM_CLIENT_CONNECT, $ctx);

		/*echo "<pre>";print_r($fp);echo "</pre>";
		echo "<pre>";print_r($error);echo "</pre>";
		echo "<pre>";print_r($errorString);echo "</pre>";exit;*/

		if (!$fp)
		{
			// Global mainframe;
			print "Failed to connect " . $error . " " . $errorString;

			return;
		}

		$msg = chr(0) . pack("n", 32) . pack('H*', str_replace(' ', '', $options['device_token'])) . pack("n", strlen($payload)) . $payload;
		fwrite($fp, $msg);
		fclose($fp);
	}

	function sendAndroidPushNotification($options,$userType)
	{
		$url = 'https://android.googleapis.com/gcm/send';
		$options['data']['badge'] = (isset($options['data']['badge']) && !empty($options['data']['badge'])) ? $options['data']['badge'] : 1;
		$fields['registration_ids'] = $options['registration_ids'];
		$fields['data'] = $options['data'];

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();

		$guest_push_api    = $beseatedParams->guest_push_api;   //AIzaSyD2YFfFFCgh_bplt5RMCidGgMOXdP8bC9c
		$mananger_push_api = $beseatedParams->mananger_push_api;  // AIzaSyAlKVAis7GlKBIFOdF1JdaFeAPc0bu1Hdw

		if($userType == 'beseated_guest')
		{
			$headers = array(
			'Authorization: key='.$guest_push_api,
			'Content-Type: application/json');
		}
		else
		{
			$headers = array(
			'Authorization: key='.$mananger_push_api,
			'Content-Type: application/json');
		}

		// Open connection
		$ch = curl_init();

		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Disabling SSL Certificate support temporarly
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		// Execute post
		$result = curl_exec($ch);

		/*echo "<pre>";print_r($result);echo "</pre>";
		echo "<pre>";print_r($ch);echo "</pre>";exit;*/

		if ($result === false)
		{
			die('Curl failed: ' . curl_error($ch));
		}

		// Close connection
		curl_close($ch);
	}

	function getLoyaltyPoint($paymentID,$userID)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('earn_point')
			->from($db->quoteName('#__beseated_loyalty_point'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('cid') . ' = ' . $db->quote($paymentID))
			->order($db->quoteName('time_stamp') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$resLoyalty = $db->loadResult();

		return $resLoyalty;

	}

	function sendPNtoSplitedUsers($bookingType,$bookingOwner,$element_booking_id,$serviceName,$bookingDate)
	{
		require_once JPATH_SITE . '/components/com_ijoomeradv/extensions/beseated/helper.php';

		$helper = new beseatedAppHelper();

		$newbookingType = strtolower($bookingType);

		if($bookingType == 'venue')
		{
			$bookingType = "venue_table";
		}

		$lowerBookingType = strtolower($bookingType);

		// Initialiase variables.
		$db    = JFactory::getDbo();

		$querySplit = $db->getQuery(true);
		$querySplit->select('split.user_id,split.'.$lowerBookingType.'_booking_split_id as splitedID,'.$newbookingType.'_id as elementID,split.email')
					->from($db->quoteName('#__beseated_'.$lowerBookingType.'_booking_split','split'))
					->where($db->quoteName('is_owner') . ' = ' . $db->quote('0'))
					->where($db->quoteName('split_payment_status') . ' = ' . $db->quote('2'))
					->where($db->quoteName('split.'.$lowerBookingType.'_booking_id') . ' = ' . $db->quote($element_booking_id));

		$db->setQuery($querySplit);

		$splitedUsers = $db->loadObjectList();

		foreach ($splitedUsers as $key => $splitedUser)
		{
			//if($splitedUser->user_id)
			//{
				$guestUserDetail  = JFactory::getUser($bookingOwner);

				$actor                                      = $bookingOwner;
				$target                                     = $splitedUser->user_id;
				$elementID                                  = $splitedUser->elementID;
				$elementType                                = ucfirst($newbookingType);
				$cid                                        = $splitedUser->splitedID;
				$extraParams                                = array();
				$extraParams[$newbookingType."BookingID"]   = $element_booking_id;
				$extraParams["invitationID"]                = $splitedUser->splitedID;
				$notification_type = strtolower($newbookingType).'.booking.paidByOwner';

				$title = JText::sprintf(
						'COM_IJOOMERADV_BESEATED_PAID_BY_BOOKING_OWNER_FOR_'.strtoupper($newbookingType),
						$guestUserDetail->name,
						$serviceName,
						$helper->convertDateFormat($bookingDate)
						);


				if($helper->storeNotification($actor,$target,$elementID,$elementType,$notification_type,$title,$cid,$extraParams,$element_booking_id,$splitedUser->email))
				{
					$this->sendPushNotication($target,$title,$notification_type,ucfirst($bookingType),$element_booking_id);
				}
			//}

		}

		return $splitedUsers;

	}

	function ccAvenuevault()
	{
	    require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();

		$encryptionKey = $beseatedParams->encryptionKey;
		$access_code   = $beseatedParams->accessCode;

		$customer_info  = '{
							  "customer_id": "jamal@tasolglobal.com"
							}';


		$encrypted_data = $this->encrypt($customer_info,$encryptionKey);

		// Define URL where the form resides
		$form_url = "https://login.ccavenue.ae/apis/servlet/DoWebTrans";

		// This is the data to POST to the form. The KEY of the array is the name of the field. The value is the value posted.
		$data_to_post = array();
		$data_to_post['enc_request']  = $encrypted_data;
		$data_to_post['access_code']  = $access_code;
		$data_to_post['command']      = 'getCustomerPaymentOptions';
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
		$result   = $this->decrypt($response[1], $encryptionKey);

		$result = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
								 '|[\x00-\x7F][\x80-\xBF]+'.
								 '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
								 '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
								 '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
								 '', $result );

		$vaultResponse         =  json_decode($result);
		$vaultResponse->status = $status [1];

		echo "<pre>";print_r($vaultResponse);echo "</pre>";exit;

		//close the connection
		curl_close($curl_connection);

	}

	function ccAvenueRefund()
	{
		jimport('joomla.filesystem.file');
		$filePath = getcwd();

		$myFile = $filePath. "/components/com_beseated/controllers/refund.txt";

		$size = filesize($myFile);
		$rh = fopen($myFile, 'r');
		$existingData = fread($rh, $size) ;
		fclose($rh);

		$fh = fopen($myFile, 'w+') or die("can't open file");
		fwrite($fh,$existingData."\n\n".date('d:m:Y H:i:s'));
		fclose($fh);

	    require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();

		require_once JPATH_SITE . '/components/com_beseated/helpers/email.php';
		$this->emailHelper       = new BeseatedEmailHelper;

		$encryptionKey = $beseatedParams->encryptionKey;
		$access_code   = $beseatedParams->accessCode;
		$app           = JFactory::getApplication();
		$input         = $app->input;
		$payment_id    = $input->get('payment_id',0,'int');
		$emailSubject  = JText::sprintf('COM_BESEATED_ORDER_NOT_REFUNDED_CCAVENUE');

		// Initialiase variables.
		$db    = JFactory::getDbo();

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_order_refund_status').'AS a')
			->where($db->quoteName('a.refund_status') . ' = ' . $db->quote('1'))
			->where($db->quoteName('a.is_failed') . ' = ' . $db->quote('1'))
			->join('INNER', '#__beseated_payment_status AS b ON b.payment_id=a.payment_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$refund_details = $db->loadObjectList();

		/*if($tblPayment->payment_status !== 'Success' || $tblPayment->paid_status == '0')
		{
			$reason = 'Payment Failed';

			$this->emailHelper->orderConfirmRefundFailedMail($emailSubject,$tblPayment->payment_id,$tblPayment->txn_id,$reason,$tblPayment->amount,$tblPayment->cc_billing_email,$tblPayment->created);
		}*/

		foreach ($refund_details as $key => $refund)
		{
			JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
			$tblPayment = JTable::getInstance('Payment', 'BeseatedTable');
			$tblPayment->load($refund->payment_id);

			$customer_info  = '{
							  "reference_no": '.$refund->txn_id.',
							  "refund_amount": '.$refund->amount.',
							  "refund_ref_no": '.$refund->payment_id.'
							}';

			$encrypted_data = $this->encrypt($customer_info,$encryptionKey);

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
			$result   = $this->decrypt($response[1], $encryptionKey);

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

			if($tblOrderRefund->is_failed == '1' && $tblOrderRefund->refund_status == '1')
			{
				//$this->emailHelper->orderConfirmRefundFailedMail($emailSubject,$tblOrderRefund->payment_id,$tblOrderRefund->reference_no,$tblOrderRefund->reason,$refund->amount,$tblPayment->cc_billing_email,$refund->created_date);
			}
			else
			{
				//$this->emailHelper->orderConfirmRefundedMail($refund->payment_id,$tblOrderRefund->reference_no,$refund->amount,$tblPayment->cc_billing_email,$refund->created_date);
			}
		}

		//close the connection
		curl_close($curl_connection);

	}

	function ccAvenueConfirmOrder()
	{
		jimport('joomla.filesystem.file');
		$filePath = getcwd();

		$myFile = $filePath. "/components/com_beseated/controllers/cron.txt";

		$size = filesize($myFile);
		$rh = fopen($myFile, 'r');
		$existingData = fread($rh, $size) ;
		fclose($rh);

		$fh = fopen($myFile, 'w+') or die("can't open file");
		fwrite($fh,$existingData."\n\n".date('d:m:Y H:i:s'));
		fclose($fh);

		require_once JPATH_SITE . '/components/com_beseated/helpers/email.php';
		$this->emailHelper       = new BeseatedEmailHelper;

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();

		$encryptionKey = $beseatedParams->encryptionKey;
		$access_code   = $beseatedParams->accessCode;
		$app           = JFactory::getApplication();
		$input         = $app->input;
		//$payment_id    = $input->get('payment_id',0,'int');
		$emailSubject  = JText::sprintf('COM_BESEATED_ORDER_NOT_CONFIRMED_CCAVENUE');

		// Initialiase variables.
		$db    = JFactory::getDbo();

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.payment_id,a.created,b.order_confirm_id')
			->from($db->quoteName('#__beseated_payment_status').' AS a')
			->where($db->quoteName('a.payment_status') . ' = ' . $db->quote('Success'))
			->where($db->quoteName('a.paid_status') . ' = ' . $db->quote('1'))
			->where($db->quoteName('b.success_count') . ' = ' . $db->quote('0'))
			->where($db->quoteName('b.is_failed') . ' = ' . $db->quote('1'))
			->join('INNER', '#__beseated_order_confirm_status AS b ON b.payment_id=a.payment_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$payment_details = $db->loadObjectList();

		$payment_ids = array();
		$payment_detail = array();

		foreach ($payment_details as $key => $payment)
		{
			$difference = time() - strtotime($payment->created);

			if(($difference/3600) >= 2)
			{
				$payment_detail[] = $payment;

			}

		}

		foreach ($payment_detail as $key => $payment)
		{
			JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
			$tblPayment = JTable::getInstance('Payment', 'BeseatedTable');
			$tblPayment->load($payment->payment_id);

			/*if($tblPayment->payment_status !== 'Success' || $tblPayment->paid_status == '0')
			{
				$reason = 'Payment Failed';

				$this->emailHelper->orderConfirmRefundFailedMail($emailSubject,$tblPayment->payment_id,$tblPayment->txn_id,$reason,$tblPayment->amount,$tblPayment->cc_billing_email,$tblPayment->created);
			}*/

			$tblOrderConfirm = JTable::getInstance('OrderConfirm', 'BeseatedTable');
			$tblOrderConfirm->load($payment->order_confirm_id);

			$customer_info  = '{
								  "order_List": [
								    {
								      "reference_no": '.$tblPayment->txn_id.',
								      "amount": '.$tblPayment->cc_amount.'
								    }
								  ]
								}';


			$encrypted_data = $this->encrypt($customer_info,$encryptionKey);

			// Define URL where the form resides
			$form_url = "https://login.ccavenue.ae/apis/servlet/DoWebTrans";

			// This is the data to POST to the form. The KEY of the array is the name of the field. The value is the value posted.
			$data_to_post = array();
			$customer_info_array = array();

			$data_to_post['enc_request']  = $encrypted_data;
			$data_to_post['access_code']  = $access_code;
			$data_to_post['command']      = 'confirmOrder';
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
			$result   = $this->decrypt($response[1], $encryptionKey);

			$result = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
									 '|[\x00-\x7F][\x80-\xBF]+'.
									 '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
									 '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
									 '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
									 '', $result );

			$confirmOrderResponse =  json_decode($result);

			$confirmOrderResponse->status = $status [1];

			$order_Result = $confirmOrderResponse->Order_Result;

			$tblOrderConfirm->api_call_status = $confirmOrderResponse->status;
			$tblOrderConfirm->success_count   = $order_Result->success_count;
			$tblOrderConfirm->is_failed       = ($order_Result->failed_List) ? 1 : 0;
			$tblOrderConfirm->reason          = ($order_Result->failed_List) ? $order_Result->failed_List->failed_order->reason : '';
			$tblOrderConfirm->reference_no    = ($order_Result->failed_List) ? $order_Result->failed_List->failed_order->reference_no : '';
			$tblOrderConfirm->payment_id      = $payment->payment_id;
			$tblOrderConfirm->api_response    = json_encode($confirmOrderResponse);
			$tblOrderConfirm->created_date    = date('Y-m-d H:i:s');
			$tblOrderConfirm->store();

			$tblPayment->order_confirm_id = $tblOrderConfirm->order_confirm_id;
			$tblPayment->is_order_confirm = 1;
			$tblPayment->store();

			if($tblOrderConfirm->is_failed == '1' && $tblOrderConfirm->success_count == '0')
			{
				//$this->emailHelper->orderConfirmRefundFailedMail($emailSubject,$tblOrderConfirm->payment_id,$tblOrderConfirm->reference_no,$tblOrderConfirm->reason,$tblPayment->amount,$tblPayment->cc_billing_email,$tblPayment->created);
			}

			//close the connection
			curl_close($curl_connection);
		}

	}

	public 	function encrypt($plainText,$key)
	{
		$secretKey = $this->hextobin(md5($key));

		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);

		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');

		$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');

		$plainPad = $this->pkcs5_pad($plainText, $blockSize);
		if (mcrypt_generic_init($openMode, $secretKey, $initVector) != -1)
		{
			$encryptedText = mcrypt_generic($openMode, $plainPad);
			mcrypt_generic_deinit($openMode);
		}
		return bin2hex($encryptedText);

	}

	public function pkcs5_pad($plainText, $blockSize)
	{
		$pad = $blockSize - (strlen($plainText) % $blockSize);
		return $plainText . str_repeat(chr($pad), $pad);
	}

	public function convertCurrencyGoogle($amount = 1, $from, $to)
	{
		$url  = "https://www.google.com/finance/converter?a=$amount&from=$from&to=$to";
		$data = file_get_contents($url);
		preg_match("/<span class=bld>(.*)<\/span>/",$data, $converted);
		if(count($converted) == 0){
			return 1;
		}
		$converted = preg_replace("/[^0-9.]/", "", $converted[1]);

		return round($converted, 2);
	}

	public function getVenueBookingBottels($bookingID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*,b.brand_name,c.booking_currency_code')
			->from($db->quoteName('#__beseated_venue_bottle_booking').' AS a')
			->where($db->quoteName('a.venue_table_booking_id') . ' = ' . $db->quote($bookingID))
			->join('INNER', '#__beseated_venue_bottle AS b ON b.bottle_id=a.bottle_id')
			->join('INNER', '#__beseated_venue_table_booking AS c ON c.venue_table_booking_id=a.venue_table_booking_id')
			->order($db->quoteName('venue_bottle_booking_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$bottles = $db->loadObjectList();

		$bottleRow = '';

		foreach ($bottles as $key => $bottle)
		{
			if($key == 0)
			{
				$bottleRow .= ' <tr>
		                    	<td width="260" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bottle->brand_name.' x '.$bottle->qty.'</td>
		                        <td width="220" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bottle->booking_currency_code.' '.number_format($bottle->price,0).'</td>
		                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bottle->booking_currency_code.' '.number_format(($bottle->price * $bottle->qty),0).'</td>
		                        </tr>';
			}
			else
			{
				$bottleRow .= ' <tr>
		                    	<td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bottle->brand_name.' x '.$bottle->qty.'</td>
		                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$bottle->booking_currency_code.' '.number_format($bottle->price,0).'</td>
		                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;">'.$bottle->booking_currency_code.' '.number_format(($bottle->price * $bottle->qty),0).'</td>
		                        </tr>';
			}

		}

		//$bottleRow = htmlspecialchars($bottleRow);

		return $bottleRow;


	}

	function getTicketsImages($ticketIDs)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('thumb_image')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('image_id') . ' IN ('.implode(",", $ticketIDs).')');
		$db->setQuery($query);
		$resTicketImgs = $db->loadColumn();
		//$resultImages = array();

		$ticketRow = '';

		$len = count( $resTicketImgs );

		foreach ($resTicketImgs as $key => $image)
		{
			$key1 = $key + 1;

            if( $key === $len - 1 )
            {
            	$ticketRow .=' <tr>
            	                    <td style="border-bottom:1px solid #b78f40;font-family:Arial, Helvetica, sans-serif;text-align:cetnrt;padding-left:35px;"><a href="'.JUri::base().'images/beseated/'.$image.'" target="_blank">Ticket  '.$key1.'</a></td>
                                    <td style="border-bottom:1px solid #b78f40;">&nbsp;</td>
                                </tr>';
            }
            else
            {
            	$ticketRow .= ' <tr>
		                            <td style="font-family:Arial, Helvetica, sans-serif;text-align:cetnrt;padding-left:35px;"><a href="'.JUri::base().'images/beseated/'.$image.'" target="_blank">Ticket '.$key1.'</a></td>
		                            <td>&nbsp;</td>
		                        </tr>';
            }
		}

		//echo "<pre>";print_r($ticketsRow);echo "</pre>";exit;

		return $ticketRow;

	}



}
