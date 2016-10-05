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
 * The Beseated ClubGuestList Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerClubRequests extends JControllerAdmin
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

		//$this->registerTask('unfeatured',	'featured');
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
	public function getModel($name = 'ClubRequests', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function changeGuestRequestStatus()
	{
		$app             = JFactory::getApplication();
		$user            = JFactory::getUser();
		$bookingID       = $app->input->getInt('booking_id');
		$bookingStatus   = $app->input->getInt('booking_status');
		$userDetail      = BeseatedHelper::guestUserDetail($user->id);
		$tblGuestBooking = JTable::getInstance('GuestBooking', 'BeseatedTable');
		$tblGuestBooking->load($bookingID);
		$tblElement      = JTable::getInstance('Venue', 'BeseatedTable');
		$tblElement->load($tblGuestBooking->venue_id);

		if($bookingStatus == 11)
		{
			$tblGuestBooking->guest_status = BeseatedHelper::getStatusID('available');
			$tblGuestBooking->venue_status = BeseatedHelper::getStatusID('accept');

			$notificationType = "guest.request.accepted";
			$title = JText::sprintf(
						'COM_BESEATED_PUSHNOTIFICATION_GUEST_BOOKING_REQUEST_ACCEPTED_BY_VENUE',
						$userDetail->full_name,
						$tblElement->venue_name,
						BeseatedHelper::convertDateFormat($tblGuestBooking->booking_date)
					);
		}
		else if($bookingStatus == 6)
		{

			$tblGuestBooking->guest_status = BeseatedHelper::getStatusID('decline');
			$tblGuestBooking->venue_status = BeseatedHelper::getStatusID('decline');

			$notificationType = "guest.request.declined";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_GUEST_BOOKING_REQUEST_DECLINED_BY_VENUE',
									$userDetail->full_name,
									$tblElement->venue_name,
									BeseatedHelper::convertDateFormat($tblGuestBooking->booking_date)
								);
		}

		$tblGuestBooking->response_date_time = date('Y-m-d H:i:s');
		if(!$tblGuestBooking->store())
		{
			echo 400;
			exit;
		}

		$actor                         = $user->id;
		$target                        = $tblGuestBooking->user_id;
		$elementID                     = $tblElement->venue_id;
		$elementType                   = "Venue";
		$cid                           = $tblGuestBooking->guest_booking_id;
		$extraParams                   = array();
		$extraParams["guestBookingID"] = $cid;

		if(BeseatedHelper::storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams))
		{
			$guestDetail = JFactory::getUser($tblGuestBooking->user_id);
			require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
			$emailAppHelper = new BeseatedEmailHelper();

			if($bookingStatus == 11)
			{
				$emailAppHelper->guestlistRequestAcceptedMail($guestDetail->name,$tblElement->venue_name,BeseatedHelper::convertDateFormat($tblGuestBooking->booking_date),$guestDetail->email);
			}
			else if ($bookingStatus == 6)
			{
				$emailAppHelper->guestlistRequestDeclinedMail($guestDetail->name,$tblElement->venue_name,BeseatedHelper::convertDateFormat($tblGuestBooking->booking_date),$guestDetail->email);
			}
		}
		echo 200;
		exit;
	}

	function changeBookingStatus()
	{
		$app             = JFactory::getApplication();
		$user            = JFactory::getUser();
		$bookingID       = $app->input->getInt('booking_id');
		$bookingStatus   = $app->input->getInt('booking_status');
		$userDetail      = BeseatedHelper::guestUserDetail($user->id);
		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');
		$tblTable        = JTable::getInstance('Table', 'BeseatedTable');

		$tblVenueBooking->load($bookingID);
		$tblVenue->load($tblVenueBooking->venue_id);
		$tblTable->load($tblVenueBooking->table_id);

		if($bookingStatus == 3)
		{
			$tblVenueBooking->user_status      = BeseatedHelper::getStatusID('available');
			$tblVenueBooking->venue_status     = BeseatedHelper::getStatusID('awaiting-payment');
			$tblVenueBooking->remaining_amount = $tblVenueBooking->total_price;
			$notificationType                  = "venue.request.accepted";

			if($tblVenue->is_day_club)
			{
				$formatedBookingTime = BeseatedHelper::convertToHM($tblVenueBooking->booking_time);
				$title               = JText::sprintf(
					'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_ACCEPTED_BY_DAY_VENUE',
					$tblVenue->venue_name,
					$tblTable->table_name,
					BeseatedHelper::convertDateFormat($tblVenueBooking->booking_date),
					$formatedBookingTime
				);
			}
			else
			{
				$formatedBookingTime = "-";
				$title               = JText::sprintf(
					'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_ACCEPTED_BY_NIGHT_VENUE',
					$tblVenue->venue_name,
					$tblTable->table_name,
					BeseatedHelper::convertDateFormat($tblVenueBooking->booking_date)
				);
			}
		}
		else if($bookingStatus == 6)
		{
			$tblVenueBooking->user_status  = BeseatedHelper::getStatusID('decline');
			$tblVenueBooking->venue_status = BeseatedHelper::getStatusID('decline');
			$notificationType              = "table.request.declined";

			if($tblVenue->is_day_club)
			{
				$formatedBookingTime = BeseatedHelper::convertToHM($tblVenueBooking->booking_time);
				$title               = JText::sprintf(
						'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_DECLINED_BY_DAY_VENUE',
						$tblVenue->venue_name,
						$tblTable->table_name,
						BeseatedHelper::convertDateFormat($tblVenueBooking->booking_date),
						$formatedBookingTime
					);
			}
			else
			{
				$formatedBookingTime = "-";
				$title               = JText::sprintf(
						'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_DECLINED_BY_NIGHT_VENUE',
						$tblVenue->venue_name,
						$tblTable->table_name,
						BeseatedHelper::convertDateFormat($tblVenueBooking->booking_date)
					);
			}

		}

		$tblVenueBooking->response_date_time = date('Y-m-d H:i:s');

		if(!$tblVenueBooking->store())
		{
			echo 400;
			exit;
		}

		$actor                              = $user->id;
		$target                             = $tblVenueBooking->user_id;
		$elementID                          = $tblVenue->venue_id;
		$elementType                        = "Venue";
		$cid                                = $tblVenueBooking->venue_table_booking_id;
		$extraParams                        = array();
		$extraParams["venueTableBookingID"] = $tblVenueBooking->venue_table_booking_id;

		if(BeseatedHelper::storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams))
		{
			$venueDefaultImage = BeseatedHelper::getElementDefaultImage($tblVenue->venue_id,'Venue');
			$venueThumb        = Juri::base().'images/beseated/'.$venueDefaultImage->thumb_image;
			$bookingDate       = date('d F Y',strtotime($tblVenueBooking->booking_date));
			$companyDetail     = JFactory::getUser($tblVenue->user_id);
			$userDetail        = JFactory::getUser($target);
			require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
			$emailAppHelper = new BeseatedEmailHelper();

			if($bookingStatus == 3)
			{
				$emailAppHelper->venueBookingAvailableUserMail($tblVenue->venue_name,$venueThumb,$bookingDate,$formatedBookingTime,$tblTable->table_name,$tblVenue->currency_code,number_format($tblVenueBooking->total_price,0),$tblVenueBooking->male_guest,$tblVenueBooking->female_guest,$userDetail->name,$userDetail->email,$companyDetail->email);
			}
			elseif ($bookingStatus == 6)
			{
				$emailAppHelper->venueBookingNotAvailableUserMail($tblVenue->venue_name,$venueThumb,$bookingDate,$formatedBookingTime,$tblTable->table_name,$tblVenue->currency_code,number_format($tblVenueBooking->total_price,0),$tblVenueBooking->male_guest,$tblVenueBooking->female_guest,$userDetail->name,$userDetail->email,$companyDetail->email);
			}
		}

		echo 200;
		exit;
	}

	public function deleteBooking()
	{
		$app             = JFactory::getApplication();
		$bookingID       = $app->input->getInt('booking_id');
		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblVenueBooking->load($bookingID);
		$tblVenueBooking->deleted_by_venue = 1;

		if(!$tblVenueBooking->store()){
			echo 400;
			exit;
		}

		echo 200;
		exit;
	}
}
