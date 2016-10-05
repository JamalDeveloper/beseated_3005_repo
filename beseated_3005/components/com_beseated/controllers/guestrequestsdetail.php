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
class BeseatedControllerGuestRequestsDetail extends JControllerAdmin
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
	public function getModel($name = 'GuestRequestsDetails', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function confirmBooking()
	{
		$app         = JFactory::getApplication();
		$bookingID   = $app->input->getInt('booking_id');
		$menu        = $app->getMenu();
		$user        = JFactory::getUser();
		$bctParams   = BeseatedHelper::getExtensionParam();
		$accessLevel = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
		$access      = array('access','link');
		$property    = array($accessLevel,'index.php?option=com_beseated&view=userbookings');
		$menuItem    = $menu->getItems( $access, $property, true );
		$itemid      = $menuItem->id;

		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');
		$tblTable        = JTable::getInstance('Table', 'BeseatedTable');

		$tblVenueBooking->load($bookingID);
		$tblVenue->load($tblVenueBooking->venue_id);
		$tblTable->load($tblVenueBooking->table_id);

		$bottlePrice = 0;

		$tblVenueBooking->user_status        = BeseatedHelper::getStatusID('confirmed');
		$tblVenueBooking->venue_status       = BeseatedHelper::getStatusID('confirmed');
		$tblVenueBooking->has_bottle         = ($bottlePrice)?1:0;
		$tblVenueBooking->has_booked         =  1;
		$tblVenueBooking->total_bottle_price = $bottlePrice;
		$tblVenueBooking->final_price        = $tblVenueBooking->total_price + $bottlePrice;
		$tblVenueBooking->total_price        = $tblVenueBooking->total_price + $bottlePrice;

		$finalPriceCommision  = $tblVenueBooking->final_price * $tblVenue->deposit_per /100;
		$totalPriceCommision  = $tblVenueBooking->total_price * $tblVenue->deposit_per /100;

		$tblVenueBooking->final_price = $tblVenueBooking->final_price - $finalPriceCommision;
		$tblVenueBooking->total_price = $tblVenueBooking->total_price - $totalPriceCommision;

		if(!$tblVenueBooking->store())
		{
			$link        = 'index.php?option=com_beseated&view=userbookings&Itemid='.$itemid;
			$app->redirect($link, 'oops. something went wrong, Your table not booked.');
		}

		$userDetail = BeseatedHelper::guestUserDetail($user->id);

		$tblVenueBooking->user_status  = BeseatedHelper::getStatusID('confirmed');
		$tblVenueBooking->venue_status = BeseatedHelper::getStatusID('confirmed');

		$notificationType = "venue.booking.confirm";

		if($tblVenue->is_day_club)
		{
			$formatedBookingTime = BeseatedHelper::convertToHM($tblVenueBooking->booking_time);

			$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_DAY_VENUE_TABLE_BOOKING_CONFIRMED',
								$userDetail->full_name,
								$tblTable->table_name,
								BeseatedHelper::convertDateFormat($tblVenueBooking->booking_date),
								$formatedBookingTime
							);
		}
		else
		{
			$formatedBookingTime = '-';

			$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_NIGHT_VENUE_TABLE_BOOKING_CONFIRMED',
								$userDetail->full_name,
								$tblTable->table_name,
								BeseatedHelper::convertDateFormat($tblVenueBooking->booking_date)
							);
		}

		$actor                              = $user->id;
		$target                             = $tblVenue->user_id;
		$elementID                          = $tblVenue->venue_id;
		$elementType                        = "Venue";
		$cid                                = $tblVenueBooking->venue_table_booking_id;
		$extraParams                        = array();
		$extraParams["venueTableBookingID"] = $tblVenueBooking->venue_table_booking_id;

		$showDirection = "Show Directions";

		if(BeseatedHelper::storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams))
		{
			$venueDefaultImage = BeseatedHelper::getElementDefaultImage($tblVenue->venue_id,'Venue');
			$venueThumb        = Juri::base().'images/beseated/'.$venueDefaultImage->thumb_image;
			$bookingDate       = date('d F Y',strtotime($tblVenueBooking->booking_date));
			$companyDetail     = JFactory::getUser($tblVenue->user_id);

			$passkey = ($tblVenueBooking->passkey) ? $tblVenueBooking->passkey : '-';

			require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
			$emailAppHelper = new BeseatedEmailHelper();

			$emailAppHelper->venueBookingconfirmedUserMail($userDetail->full_name,$companyDetail->name,$venueThumb,$tblVenue->location,$companyDetail->phone,$showDirection,$cid,$bookingDate,$formatedBookingTime,$tblTable->table_name,$tblVenue->currency_code,number_format($tblTable->min_price,0),$tblVenueBooking->male_guest,$tblVenueBooking->female_guest,$passkey,$userDetail->full_name,$userDetail->email,$bottleRow,number_format($tblVenueBooking->total_bottle_price,0),$userDetail->email);

			$this->jsonarray['pushNotificationData']['id']         = $tblVenueBooking->venue_table_booking_id;
			$this->jsonarray['pushNotificationData']['to']         = $target;
			$this->jsonarray['pushNotificationData']['message']    = $title;
			$this->jsonarray['pushNotificationData']['type']       = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype'] = '';
			BeseatedHelper::sendPushNotification($this->jsonarray);
		}

		$link = 'index.php?option=com_beseated&view=userbookings&Itemid='.$itemid;
		$app->redirect($link, 'Booking succesfully.');
	}
}
