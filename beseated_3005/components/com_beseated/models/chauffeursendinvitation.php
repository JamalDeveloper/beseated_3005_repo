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
class BeseatedModelChauffeursendinvitation extends JModelList
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

		$tblChauffeurBooking = JTable::getInstance('ChauffeurBooking', 'BeseatedTable');
		$tblChauffeurBooking->load($bookingID);

		$tblService = JTable::getInstance('ChauffeurService', 'BeseatedTable');
		$tblService->load($tblChauffeurBooking->service_id);

		$tblElement = JTable::getInstance('Chauffeur', 'BeseatedTable');
		$tblElement->load($tblChauffeurBooking->chauffeur_id);

		foreach ($newInvitedEmails as $key => $email)
		{
			$userID = BeseatedHelper::getUserForSplit($email);

			$tblInvitation = JTable::getInstance('Invitation', 'BeseatedTable');
			$tblInvitation->load(0);
			$invitationPost = array();
			$invitationPost['element_booking_id'] = $bookingID;
			$invitationPost['element_id']         = $tblChauffeurBooking->chauffeur_id;
			$invitationPost['element_type']       = 'chauffeur';
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
			$invitationData['element_type']     = 'chauffeur';
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


			$notificationType = "chauffeur.service.invitation";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_PROTECTION_SERVICE_BOOKING_INVITATION',
									$tblService->service_name,
									$this->helper->convertDateFormat($tblChauffeurBooking->booking_date),
									$this->helper->convertToHM($tblChauffeurBooking->booking_time)
								);

			$actor       = $this->IJUserID;
			$target      = $userID;
			$elementID   = $tblElement->chauffeur_id;
			$elementType = "Chauffeur";
			$cid         = $tblInvitation->invitation_id;
			$extraParams = array();
			$extraParams["chauffeurBookingID"] = $tblChauffeurBooking->chauffeur_booking_id;
			$extraParams["invitationID"]        = $tblInvitation->invitation_id;
			$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblChauffeurBooking->chauffeur_booking_id,$email);

			$this->jsonarray['pushNotificationData']['id']          = $tblChauffeurBooking->chauffeur_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Chauffeur';
			$this->jsonarray['pushNotificationData']['to']          = $target;
			$this->jsonarray['pushNotificationData']['message']     = $title;
			$this->jsonarray['pushNotificationData']['type']        = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype']  = '';


			$loginUser   = JFactory::getUser();
			$inviteeName = $loginUser->name;

			$invitedUserName = ($userDetail)?$userDetail->full_name :$email;

			$this->emailHelper->invitationMailUser($invitedUserName,$inviteeName,$tblElement->chauffeur_name,$tblService->service_name,$this->helper->convertDateFormat($tblChauffeurBooking->booking_date),$this->helper->convertToHM($tblChauffeurBooking->booking_time),$isNightVenue = 0,$email);*/

		}

		$tblChauffeurBooking->has_invitation = 1;
		$tblChauffeurBooking->store();

		return 200;

	}

	


}
