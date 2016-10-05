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
class BeseatedModelYachtsendinvitation extends JModelList
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

	

	public function save($bookingID,$newInvitedEmails)
	{
		$user                 = JFactory::getUser();

		$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
		$tblYachtBooking->load($bookingID);

		$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
		$tblService->load($tblYachtBooking->service_id);

		$tblElement = JTable::getInstance('Yacht', 'BeseatedTable');
		$tblElement->load($tblYachtBooking->yacht_id);

		foreach ($newInvitedEmails as $key => $email)
		{
			$userID = BeseatedHelper::getUserForSplit($email);

			$tblInvitation = JTable::getInstance('Invitation', 'BeseatedTable');
			$tblInvitation->load(0);
			$invitationPost = array();
			$invitationPost['element_booking_id'] = $bookingID;
			$invitationPost['element_id']         = $tblYachtBooking->yacht_id;
			$invitationPost['element_type']       = 'yacht';
			$invitationPost['user_id']            = $userID;
			$invitationPost['email']              = $email;

			$userDetail = BeseatedHelper::guestUserDetail($userID);

			if($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id))
			{
				$invitationPost['fbid']               = $userDetail->fb_id;
			}
			else
			{
				$invitationPost['fbid']               = '';
			}

			$invitationPost['user_action']        = 2;
			$invitationPost['time_stamp']         = time();
			$tblInvitation->bind($invitationPost);
			$tblInvitation->store();

			$invitationData = array();

			/*$tblReadElementBooking = JTable::getInstance('ReadElementBooking', 'BeseatedTable');
			$tblReadElementBooking->load(0);

			$invitationData['booked_type']      = 'invitation';
			$invitationData['element_type']     = 'yacht';
			$invitationData['booking_id']       = $tblInvitation->invitation_id;
			$invitationData['from_user_id']     = $this->IJUserID;
			$invitationData['to_user_id']       = ($userID) ? $userID : 0;
			$invitationData['to_user_email_id'] = $email;


			$tblReadElementBooking->bind($invitationData);
			$tblReadElementBooking->store();

			$tblReadElementRsvp = JTable::getInstance('ReadElementRsvp', 'BeseatedTable');
			$tblReadElementRsvp->load(0);
			$tblReadElementRsvp->bind($invitationData);
			$tblReadElementRsvp->store();


			$notificationType = "yacht.service.invitation";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_PROTECTION_SERVICE_BOOKING_INVITATION',
									$tblService->service_name,
									$this->helper->convertDateFormat($tblYachtBooking->booking_date),
									$this->helper->convertToHM($tblYachtBooking->booking_time)
								);

			$actor       = $this->IJUserID;
			$target      = $userID;
			$elementID   = $tblElement->yacht_id;
			$elementType = "Yacht";
			$cid         = $tblInvitation->invitation_id;
			$extraParams = array();
			$extraParams["yachtBookingID"] = $tblYachtBooking->yacht_booking_id;
			$extraParams["invitationID"]        = $tblInvitation->invitation_id;
			$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblYachtBooking->yacht_booking_id,$email);

			$this->jsonarray['pushNotificationData']['id']          = $tblYachtBooking->yacht_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Yacht';
			$this->jsonarray['pushNotificationData']['to']          = $target;
			$this->jsonarray['pushNotificationData']['message']     = $title;
			$this->jsonarray['pushNotificationData']['type']        = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype']  = '';


			$loginUser   = JFactory::getUser();
			$inviteeName = $loginUser->name;

			$invitedUserName = ($userDetail)?$userDetail->full_name :$email;

			$this->emailHelper->invitationMailUser($invitedUserName,$inviteeName,$tblElement->yacht_name,$tblService->service_name,$this->helper->convertDateFormat($tblYachtBooking->booking_date),$this->helper->convertToHM($tblYachtBooking->booking_time),$isNightVenue = 0,$email);*/

		}

		$tblYachtBooking->has_invitation = 1;
		$tblYachtBooking->store();

		return 200;

	}

	


}
