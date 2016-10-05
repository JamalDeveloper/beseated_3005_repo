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
 * The Beseated Messages Model
 *
 * @since  0.0.1
 */
class BeseatedModelProtectionshareinvitation extends JModelList
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
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array();
		}

		parent::__construct($config);
	}

	public function getProtectionBookingDetail()
	{
		$input         = JFactory::getApplication()->input;
		$bookingID     = $input->get('booking_id', 0, 'int');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_protection_booking'))
			->where($db->quoteName('protection_booking_id') . ' = ' . $db->quote($bookingID));
		
		// Set the query and load the result.
		$db->setQuery($query);
		
		$protectionBookingDetail = $db->loadObject();

		return $protectionBookingDetail;

	}

	public function save($bookingID,$newSplitedEmails,$alreadySplited)
	{
		$user                 = JFactory::getUser();

		$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');

		$tblProtectionBooking->load($bookingID);

		
		
		$totalAmountToSplit = $tblProtectionBooking->total_price;
		$totalSplitCount    = count($newSplitedEmails) + count($alreadySplited);
		$splittedAmount     = $totalAmountToSplit / $totalSplitCount;

		$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
		$tblService->load($tblProtectionBooking->service_id);

		$tblElement = JTable::getInstance('Protection', 'BeseatedTable');
		$tblElement->load($tblProtectionBooking->protection_id);

		$userIDs = array();

		foreach ($newSplitedEmails as $key => $email)
		{
			$userID = BeseatedHelper::getUserForSplit($email);
			$tblProtectionBookingSplit = JTable::getInstance('ProtectionBookingSplit', 'BeseatedTable');
			$tblProtectionBookingSplit->load(0);
			$splitPost = array();
			$splitPost['protection_booking_id'] = $bookingID;
			$splitPost['protection_id']         = $tblProtectionBooking->protection_id;
			$splitPost['service_id']            = $tblProtectionBooking->service_id;
			$splitPost['user_id']               = $userID;
			$splitPost['is_owner']              = ($userID==$user->id)?1:0;
			$splitPost['email']                 = $email;

			$userDetail = BeseatedHelper::guestUserDetail($userID);

			if($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id))
			{
				$splitPost['fbid']               = $userDetail->fb_id;
			}
			else
			{
				$splitPost['fbid']               = '';
			}

			$splitPost['split_payment_status']  = 2;
			$splitPost['time_stamp']            = time();
			$tblProtectionBookingSplit->bind($splitPost);
			$tblProtectionBookingSplit->store();

			if($userID !== $user->id)
			{
				$invitationData = array();

				$tblReadElementBooking = JTable::getInstance('ReadElementBooking', 'BeseatedTable');
				$tblReadElementBooking->load(0);

				$invitationData['booked_type']      = 'share';
				$invitationData['element_type']     = 'protection';
				$invitationData['booking_id']       = $tblProtectionBookingSplit->protection_booking_split_id;
				$invitationData['from_user_id']     = $user->id;
				$invitationData['to_user_id']       = ($userID) ? $userID : 0;
				$invitationData['to_user_email_id'] =  $email;

				$tblReadElementBooking->bind($invitationData);
				$tblReadElementBooking->store();

				$tblReadElementRsvp = JTable::getInstance('ReadElementRsvp', 'BeseatedTable');
				$tblReadElementRsvp->load(0);
				$tblReadElementRsvp->bind($invitationData);
				$tblReadElementRsvp->store();
			}

			if($userID !== $user->id)
			{
				/*$userIDs[]        = $userID;
				$userDetail       = BeseatedHelper::guestUserDetail($user->id);
				$actor            = $user->id;
				$target           = $userID;
				$elementID        = $tblElement->protection_id;
				$elementType      = "Protection";
				$notificationType = "protection.share.invitation.request";
				$title            = JText::sprintf(
										'COM_BESEATED_NOTIFICATION_SHARE_BOOKING_REQUEST_TO_INVITEE_FOR_PROTECTION'
									);

				$dbTitle            = JText::sprintf(
										'COM_BESEATED_DB_NOTIFICATION_SHARE_BOOKING_REQUEST_TO_INVITEE_FOR_PROTECTION',
										$tblService->service_name,
										$this->helper->convertDateFormat($tblProtectionBooking->booking_date),
									    $this->helper->convertToHM($tblProtectionBooking->booking_time)
									);


				$cid              = $tblProtectionBookingSplit->protection_booking_split_id;
				$extraParams      = array();
				$extraParams["protectionBookingID"] = $tblProtectionBooking->protection_booking_id;
				$extraParams["invitationID"]        = $tblProtectionBookingSplit->protection_booking_split_id;
				$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$dbTitle,$cid,$extraParams,$tblProtectionBooking->protection_booking_id,$email);*/
			}
		}

		/*if(!empty($userIDs))
		{
			$this->jsonarray['pushNotificationData']['id']         = $tblProtectionBooking->protection_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Protection';
			$this->jsonarray['pushNotificationData']['to']         = implode(',', $userIDs);
			$this->jsonarray['pushNotificationData']['message']    = $title;
			//$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_PROTECTION_SHARE_BOOKING_REQUEST_RECEIVED');
			$this->jsonarray['pushNotificationData']['type']       = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype'] = '';
		}*/

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_protection_booking_split'))
			->set($db->quoteName('splitted_amount') . ' = ' . $db->quote($tblProtectionBooking->each_person_pay))
			->where($db->quoteName('protection_booking_id') . ' = ' . $db->quote($bookingID));

		// Set the query and execute the update.
		$db->setQuery($query);
		$db->execute();

		$tblProtectionBooking->is_splitted      = 1;
		//$tblProtectionBooking->each_person_pay  = $splittedAmount;
		$tblProtectionBooking->splitted_count   = $totalSplitCount;
		//$tblProtectionBooking->remaining_amount = $totalAmountToSplit;
		$tblProtectionBooking->store();

		return 200;

	}

	public function saveReplaceInvitee($bookingID,$newInvitedEmails,$alreadyInvitedEmail,$invitationID)
	{
		$user = JFactory::getUser();
		
		$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
		$tblProtectionBooking->load($bookingID);

		$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
		$tblService->load($tblProtectionBooking->service_id);

		$tblElement = JTable::getInstance('Protection', 'BeseatedTable');
		$tblElement->load($tblProtectionBooking->protection_id);

		$email= $newInvitedEmails[0];

		$userID = BeseatedHelper::getUserForSplit($email);
		$tblProtectionBookingSplit = JTable::getInstance('ProtectionBookingSplit', 'BeseatedTable');
		$tblProtectionBookingSplit->load($invitationID);

		$splitPost = array();
		$splitPost['user_id']               = $userID;
		$splitPost['is_owner']              = ($userID==$user->id)?1:0;
		$splitPost['email']                 = $email;

		$userDetail = BeseatedHelper::guestUserDetail($userID);

		if($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id))
		{
			$splitPost['fbid']               = $userDetail->fb_id;
		}
		else
		{
			$splitPost['fbid']               = '';
		}

		$splitPost['split_payment_status']  = 2;
		$splitPost['time_stamp']            = time();
		$tblProtectionBookingSplit->bind($splitPost);
		$tblProtectionBookingSplit->store();
		$this->deleteReplaceInvitee($bookingID,$invitationID);

		/*$userDetail       = $this->helper->guestUserDetail($this->IJUserID);
		$actor            = $this->IJUserID;
		$target           = $userID;
		$elementID        = $tblElement->protection_id;
		$elementType      = "Protection";
		$notificationType = "protection.share.invitation.request";
		$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_SHARE_BOOKING_REQUEST_TO_INVITEE_FOR_PROTECTION',
								$userDetail->full_name,
								$tblService->service_name,
								$this->helper->convertDateFormat($tblProtectionBooking->booking_date),
							    $this->helper->convertToHM($tblProtectionBooking->booking_time)
							);

		$cid              = $tblProtectionBookingSplit->protection_booking_split_id;
		$extraParams      = array();
		$extraParams["protectionBookingID"] = $tblProtectionBooking->protection_booking_id;
		$extraParams["invitationID"]        = $tblProtectionBookingSplit->protection_booking_split_id;

		$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblProtectionBooking->protection_booking_id,$email);

		$this->jsonarray['pushNotificationData']['id']          = $tblProtectionBooking->protection_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Protection';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		$this->jsonarray['pushNotificationData']['type']        = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype']  = '';*/

		return 200;
	}

	function deleteReplaceInvitee($bookingID,$invitationID)
	{
		$user = JFactory::getUser();
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->select('extra_pramas,notification_id')
			->from($db->quoteName('#__beseated_notification'))
			->where($db->quoteName('notification_type') . ' = ' . $db->quote('protection.share.invitation.request'))
			->where($db->quoteName('actor') . ' = ' . $db->quote((int) $user->id))
			->where($db->quoteName('cid') . ' = ' . $db->quote((int) $invitationID));

		// Set the query and execute the delete.
		$db->setQuery($query);

		$notif_data = $db->loadObjectList();

		foreach ($notif_data as $key => $value)
		{
			$extra_pramas = $value->extra_pramas;

			if(json_decode($extra_pramas)->protectionBookingID == $bookingID)
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();

				// $db    = $this->getDbo();
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


}
