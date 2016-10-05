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
 * The Beseated Clubs Model
 *
 * @since  0.0.1
 */
class BeseatedModelEventInvitation extends JModelList
{
	public $db = null;
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
		$this->db = JFactory::getDbo();
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array();
		}

		parent::__construct($config);
	}

	public function save($eventBookingID,$newInvitedEmails)
	{
		require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';

		$user = JFactory::getUser();

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_event_ticket_booking_detail'))
			->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($eventBookingID))
			->where($db->quoteName('booking_user_id') . ' = ' . $db->quote($user->id))
			->order($db->quoteName('ticket_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$resAllBookedTicket = $db->loadObjectList();

		// Initialiase variables.
		$emailIndex         = 0;
		$hasNewInvited      = 0;
		$newInvited         = 0;

		$tblTicketBooking = JTable::getInstance('TicketBooking', 'BeseatedTable');
		$tblTicketBooking->load($eventBookingID);

		// Initialiase variables.

		foreach ($resAllBookedTicket as $key => $tbDetail)
		{
			//if($key == 0){ continue; }
			if($tbDetail->user_id !== $user->id){ continue; }

			$tblTicketBookingDetail = JTable::getInstance('TicketBookingDetail', 'BeseatedTable');
			$tblTicketInvitation    = JTable::getInstance('TicketInvitation', 'BeseatedTable');

			if(isset($newInvitedEmails[$emailIndex]) && !empty($newInvitedEmails[$emailIndex]))
			{
				$invitedUserDetail = BeseatedHelper::guestUserDetailFromEmail($newInvitedEmails[$emailIndex]);
				$tblTicketInvitation->load(0);
				$tbiPost['ticket_booking_id']        = $tbDetail->ticket_booking_id;
				$tbiPost['ticket_booking_detail_id'] = $tbDetail->ticket_booking_detail_id;
				$tbiPost['ticket_id']                = $tbDetail->ticket_id;
				$tbiPost['event_id']                 = $tbDetail->event_id;
				$tbiPost['user_id']                  = $tbDetail->booking_user_id;
				$tbiPost['invited_user_id']          = ($invitedUserDetail && $invitedUserDetail->user_id)?$invitedUserDetail->user_id:0;
				$tbiPost['invited_user_status']      = BeseatedHelper::getStatusID('request');
				$tbiPost['email']                    = $newInvitedEmails[$emailIndex];
				$tbiPost['fbid']                     = "";
				$tbiPost['time_stamp']               = time();
				$tblTicketInvitation->bind($tbiPost);

				if($tblTicketInvitation->store())
				{
					// Initialiase variables.
				    $tblEvent = JTable::getInstance('Event', 'BeseatedTable');
					$tblEvent->load($tbDetail->event_id);

					$userDetail       = BeseatedHelper::guestUserDetail($user->id);

					$event_date = date('d F Y',strtotime($tblEvent->event_date));
					$event_time = date('H:i',strtotime($tblEvent->event_time));

					$ticketImage = $this->getTicketImage($tbiPost['ticket_id']);

					$ticketImage = JUri::base().'images/beseated/'.$ticketImage;

					//BeseatedEmailHelper::eventInvitationMailUser($tblEvent->image,$tblEvent->event_name,$event_date,$event_time,$tblEvent->location,$userDetail->full_name,$userDetail->email,$tblTicketBooking->booking_currency_code,number_format($tblTicketBooking->ticket_price,0),$ticketImage,$tbiPost['email']);

					$emailIndex    = $emailIndex + 1;
					$tblTicketBookingDetail->load($tbDetail->ticket_booking_detail_id);
					$tblTicketBookingDetail->user_id = ($invitedUserDetail && $invitedUserDetail->user_id)?$invitedUserDetail->user_id:0;
					$tblTicketBookingDetail->store();

					$hasNewInvited = 1;
					$newInvited++;

					$userID = BeseatedHelper::getUserForSplit($tbiPost['email']);

					if($userID)
					{
						$userIDs[]        = $userID;
					}

					$actor            = $user->id;
					$target           = ($userID) ? $userID :"0";
					$elementID        = $tbDetail->event_id;
					$elementType      = "Event";
					$notificationType = "event.invitation";
					$title            = JText::sprintf(
											'COM_BESEATED_NOTIFICATION_INVITATION_TO_INVITEE_FOR_EVENT',
											$userDetail->full_name,
											$tblEvent->event_name,
										    BeseatedHelper::convertDateFormat($tblEvent->event_date),
										    BeseatedHelper::convertToHM($tblEvent->event_time)
										);

					$cid              = $tbDetail->ticket_booking_detail_id;
					$extraParams      = array();
					$extraParams["eventBookingID"]      = $tblTicketInvitation->ticket_booking_id;
					$extraParams["invitationID"]        = $tblTicketInvitation->invite_id;
					BeseatedHelper::storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblTicketInvitation->ticket_booking_id,$newInvitedEmails[$emailIndex]);

				}
			}
		}

		if(!$hasNewInvited)
		{
			return 500;
		}
		else
		{
			/*$this->jsonarray['pushNotificationData']['id']          = $tblTicketInvitation->ticket_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Event';
			$this->jsonarray['pushNotificationData']['to']          = implode(',', $userIDs);
			$this->jsonarray['pushNotificationData']['message']     = $title;
			$this->jsonarray['pushNotificationData']['type']        = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype']  = '';*/
			return $newInvited;
		}
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

	public function sendEmail($emailAddress,$subject,$body,$ticketImage)
	{
		require_once(JPATH_BASE."/components/com_ijoomeradv/extensions/beseated/helper/tcpdf/tcpdf.php");
		$pdf = new TCPDF();

		error_reporting ( E_ALL );

		// add a page
		$pdf->AddPage();

		$pdf->SetFont("", "b", 16);
		$pdf->Write(16, "event ticket booking\n", "", 0, 'C');

		$txt = "";
		$pdf->Write ( 0, $txt );
		//$pdf->setImageScale ( PDF_IMAGE_SCALE_RATIO );
		//$pdf->setJPEGQuality ( 90 );
		$pdf->Image ($ticketImage);
		$pdf->WriteHTML ( $txt );

		$storePDF = JPATH_BASE.'/images/beseated/Ticket/eventTicket.pdf';

		//$pdf->Output('/var/www/sendMail/filename.pdf', 'I');
		$pdf->Output($storePDF, 'F');

		// Initialise variables.
		$app     = JFactory::getApplication();
		$config  = JFactory::getConfig();

		$site    = $config->get('sitename');
		$from    = $config->get('mailfrom');
		$sender  = $config->get('fromname');
		$email   = $emailAddress;
		$subject = $subject;

		// Build the message to send.
		$msg     = $body;
		$body    = sprintf($msg, $site, $sender, $from);

		// Clean the email data.
		$sender  = JMailHelper::cleanAddress($sender);
		$subject = JMailHelper::cleanSubject($subject);
		$body    = JMailHelper::cleanBody($body);

		// Send the email.
		$return = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body,true,$cc = null, $bcc = null, $storePDF);
		// Check for an error.
		if ($return !== true)
		{
			return new JException(JText::_('COM__SEND_MAIL_FAILED'), 500);
		}

		unlink($storePDF);
	}
}
