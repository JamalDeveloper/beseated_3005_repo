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
 * The Beseated Club Bookings Model
 *
 * @since  0.0.1
 */
class BeseatedModelProtectionBookings extends JModelList
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
		parent::__construct($config);
	}

	public function getProtectionBookings()
	{
		$user          = JFactory::getUser();
		$elementType   = BeseatedHelper::getUserType($user->id);
		$elementDetail = BeseatedHelper::getUserElementID($user->id);

		if($elementType != 'Protection')
		{
			return array();
		}

		if(!$elementDetail->protection_id)
		{
			return array();
		}

		$protection = BeseatedHelper::protectionUserDetail($user->id);

		$bookingStatus = array();
		$bookingStatus[] = BeseatedHelper::getStatusID('booked');
		$bookingStatus[] = BeseatedHelper::getStatusID('confirmed');
		$bookingStatus[] = BeseatedHelper::getStatusID('canceled');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('pb.*')
			->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
			->where($db->quoteName('pb.protection_id') . ' = ' . $db->quote($protection->protection_id))
			->where($db->quoteName('pb.protection_status') . ' IN ('.implode(',', $bookingStatus).')')
			->where($db->quoteName('pb.deleted_by_protection') . ' = ' . $db->quote(0))
			->where($db->quoteName('usr.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('pb.booking_date') . ' ASC,'.$db->quoteName('pb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=pb.user_id');

		$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=pb.protection_status');

		$query->select('ps.service_name,ps.thumb_image,ps.image')
			->join('LEFT','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');


		// Set the query and load the result.
		//$db->setQuery($query,$startFrom,$pageLimit);
		$db->setQuery($query);

		$resBookings = $db->loadObjectList();
		
		$resultBookings = array();
		$resultUpcomingBookings = array();
		$resultHistoryBookings = array();

		foreach ($resBookings as $key => $booking)
		{
			if(BeseatedHelper::isPastDate($booking->booking_date) || $booking->is_noshow == 1)
			{
				$resultHistoryBookings[] = $booking;
			}
			else
			{
				$resultUpcomingBookings[] = $booking;
			}
		}

		$resultBookings['upcomming'] = $resultUpcomingBookings;
		$resultBookings['history'] = $resultHistoryBookings;

		return $resultBookings;

	}

	public function summaryForVenue($venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('count(status) as total_count,status')
			->from($db->quoteName('#__bcted_venue_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'))
			->group($db->quoteName('status'));


		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	public function getRevenueForVenue($venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('sum(amount_payable) as revenue')
			->from($db->quoteName('#__bcted_venue_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'));


		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadResult();

		return $result;
	}

	public function deleteBooking($bookingID,$userType)
	{
		$tblVenuebooking = JTable::getInstance('Venuebooking', 'BeseatedTable',array());
		$user            = JFactory::getUser();

		$tblVenuebooking->load($bookingID);

		if(!$tblVenuebooking->venue_table_booking_id)
		{
			return 400;
		}

		$status = array();
		$status[] = BeseatedHelper::getStatusIDFromStatusName('Booked');
		$status[] = BeseatedHelper::getStatusIDFromStatusName('Canceled');
		$status[] = BeseatedHelper::getStatusIDFromStatusName('Decline');
		$status[] = BeseatedHelper::getStatusIDFromStatusName('Awaiting Payment');
		//$status[] = BeseatedHelper::getStatusIDFromStatusName('Unavailable');


		if(!in_array($tblVenuebooking->venue_status, $status))
		{
			return 400;
		}

		$tblVenue = JTable::getInstance('Venue', 'BeseatedTable',array());
		$tblVenue->load($tblVenuebooking->venue_id);

		if($userType == 'venue')
		{
			$tblVenuebooking->deleted_by_venue = 1;
		}
		else
		{
			$tblVenuebooking->deleted_by_user = 1;
		}

		if(BeseatedHelper::getStatusIDFromStatusName('Waiting List')==$tblVenuebooking->venue_status)
		{
			$tblVenuebooking->deleted_by_user = 1;
			$tblVenuebooking->deleted_by_venue = 1;
		}

		if(!$tblVenuebooking->store())
		{
			return 500;
		}

		return 200;
	}

	public function deleteBookingOfPackage($bookingID,$userType)
	{
		$tblPackagePurchased = JTable::getInstance('PackagePurchased', 'BctedTable');
		$tblPackagePurchased->load($bookingID);
		$user = JFactory::getUser();

		if(!$tblPackagePurchased->package_purchase_id)
		{
			return 400;
		}

		$status = array();
		$status[] = BctedHelper::getStatusIDFromStatusName('Booked');
		$status[] = BctedHelper::getStatusIDFromStatusName('Cancelled');
		$status[] = BctedHelper::getStatusIDFromStatusName('Declined');
		$status[] = BctedHelper::getStatusIDFromStatusName('Waiting List');
		$status[] = BctedHelper::getStatusIDFromStatusName('Unavailable');

		if(!in_array($tblPackagePurchased->status, $status))
		{
			return 400;
		}

		$tblVenue = JTable::getInstance('Venue', 'BctedTable',array());
		$tblVenue->load($tblPackagePurchased->venue_id);
		if($userType == 'venue')
		{
			if($tblVenue->userid != $user->id)
			{
				return 706;
			}

			$tblPackagePurchased->deleted_by_venue = 1;
		}
		else
		{
			$tblPackagePurchased->deleted_by_user = 1;
		}

		if(!$tblPackagePurchased->store())
		{
			return 500;
		}

		return 200;
	}

	public function sendnoshowmessage($bookingID,$userID)
	{
		$tblVenuebooking = JTable::getInstance('Venuebooking', 'BctedTable');
		$tblVenuebooking->load($bookingID);

		if(!$tblVenuebooking->venue_booking_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BCTED_INVALID_BOOKING_DATA'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$tblVenuebooking->is_noshow = 1;
		$tblVenuebooking->store();

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_payment_status'))
			->where($db->quoteName('booked_element_id') . ' = ' . $db->quote($tblVenuebooking->venue_booking_id))
			->where($db->quoteName('booked_element_type') . ' = ' . $db->quote('venue'))
			->where($db->quoteName('paid_status') . ' = ' . $db->quote('1'));

		// Set the query and load the result.
		$db->setQuery($query);

		$paymentStatus = $db->loadObject();
		if($paymentStatus)
		{
			$queryLP = $db->getQuery(true);
			$queryLP->select('*')
				->from($db->quoteName('#__bcted_loyalty_point'))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($tblVenuebooking->user_id))
				->where($db->quoteName('point_app') . ' = ' . $db->quote('purchase.venue'))
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
						$db->quote($tblVenuebooking->user_id) . ', ' .
						$db->quote(($loyaltyPointDetail->earn_point * (-1))) . ', ' .
						$db->quote('venue.noshow') . ', ' .
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

		$tblVenue = JTable::getInstance('Venue', 'BctedTable');
		$tblVenue->load($tblVenuebooking->venue_id);

		$tblTable = JTable::getInstance('Table', 'BctedTable');
		$tblTable->load($tblVenuebooking->venue_table_id);

		$bookingUserID = $tblVenuebooking->user_id;
		$bookingUserDetail = JFactory::getUser($bookingUserID);
		if(!$bookingUserDetail->id)
		{
			return 500;
		}

		// Initialise variables.
		$app     = JFactory::getApplication();
		$config  = JFactory::getConfig();


		$imgPath = JUri::base().'images/footer-logo.png';
		$imageLink = '<img src="'.$imgPath.'" />';

		$site    = $config->get('sitename');
		$from    = $config->get('mailfrom');
		$sender  = $config->get('fromname');
		$email   = $bookingUserDetail->email;
		$subject =  JText::sprintf('COM_BCTED_VENUE_NOSHOW_EMAIL_SUBJECT',$tblVenue->venue_name);//$app->input->get('subject');
		$noshowPushMessage = $subject;
		$timeArray = explode(":",$tblVenuebooking->booking_from_time);
		$timeHM = $timeArray[0].":".$timeArray[1];
		$body     = JText::sprintf('COM_BCTED_VENUE_NOSHOW_EMAIL_BODY',$bookingUserDetail->name,$tblVenue->venue_name,date('d-m-Y',strtotime($tblVenuebooking->venue_booking_datetime)),$timeHM,$imageLink);

		// Clean the email data.
		$sender  = JMailHelper::cleanAddress($sender);
		$subject = JMailHelper::cleanSubject($subject);

		// Send the email.
		$return = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body,true);

		/*************** Send no Show email to site administrator *******/

		require_once JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/helper.php';
		$appHelper            = new bctedAppHelper;


		$email   = $appHelper->getAdministratorUsersEmail();
		$subject =  JText::_('COM_BESEATED_VENUE_NOSHOW_EMAIL_TO_ADMINISTRATOR_SUBJECT');//$app->input->get('subject');

		// Build the message to send.
		$msg     = JText::_('COM_BESEATED_VENUE_NOSHOW_EMAIL_TO_ADMINISTRATOR_BODY');
		$body    = sprintf($msg, $site, $sender, $from);

		// Clean the email data.
		$sender  = JMailHelper::cleanAddress($sender);
		$subject = JMailHelper::cleanSubject($subject);
		$body    = JMailHelper::cleanBody($body);

		// Send the email.
		$return = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body,true);

		/*************** End no show email to site administrator ************/

		$message     = JText::_('COM_BCTED_VENUE_NOSHOW_EMAIL_BODY');

		$text_message = JText::sprintf('COM_BCTED_VENUE_NOSHOW_TEXT_MESSAGE',$tblVenue->venue_name,date('d-m-Y',strtotime($tblVenuebooking->venue_booking_datetime)),$timeHM);

		$connectionID = BctedHelper::sendMessage($tblVenue->venue_id,0,0,$tblTable->venue_table_id,$bookingUserID,$text_message,array(),'noshow');

		$pushID = $tblVenue->venue_name.';'.$connectionID.';venue';
		$jsonarray['pushNotificationData']['id']         = $pushID;
		$jsonarray['pushNotificationData']['to']         = $bookingUserID;
		$jsonarray['pushNotificationData']['message']    = $noshowPushMessage;
		$jsonarray['pushNotificationData']['type']       = JText::_('PUSHNOTIFICATION_TYPE_NOSHOW');
		$jsonarray['pushNotificationData']['configtype'] = '';

		BctedHelper::sendPushNotification($jsonarray);

		return 200;
	}
}
