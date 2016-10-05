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
 * The Beseated Club Bookings Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerClubBookingsDetail extends JControllerAdmin
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
	public function getModel($name = 'ClubBookingDetail', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function postBill()
	{
		$app             = JFactory::getApplication();
		$postBillAmount  = $app->input->getInt('postbill_amount');
		$bookingID       = $app->input->getInt('booking_id');
		$user            = JFactory::getUser();
		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');
		$tblTable        = JTable::getInstance('Table', 'BeseatedTable');

		$tblVenueBooking->load($bookingID);

		if(!$tblVenueBooking->venue_table_booking_id)
		{
			$result = '400';
			echo  $result;
			exit;
		}

		$tblVenue->load($tblVenueBooking->venue_id);
		$tblTable->load($tblVenueBooking->table_id);
		if(!$postBillAmount)
		{
			$postBillAmount = $tblVenueBooking->total_price;
		}

		$tblVenueBooking->bill_post_amount = $postBillAmount;
		$tblVenueBooking->final_price      = $postBillAmount;
		$tblVenueBooking->total_price      = $postBillAmount;
		$tblVenueBooking->remaining_amount = $postBillAmount;
		$tblVenueBooking->is_bill_posted   = 1;


		if(!$tblVenueBooking->store())
		{
			$result = '400';
			echo $result;
			exit;
		}

		$notificationType = "venue.postbill";

		if($tblVenue->is_day_club)
		{
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_VENUE_DAY_TABLE_BOOKING_POSTBILL',
									$tblVenue->venue_name,
									$tblTable->table_name,
									BeseatedHelper::convertDateFormat($tblVenueBooking->booking_date),
									BeseatedHelper::convertToHM($tblVenueBooking->booking_time)
								);
		}
		else
		{
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_VENUE_NIGHT_TABLE_BOOKING_POSTBILL',
									$tblVenue->venue_name,
									$tblTable->table_name,
									BeseatedHelper::convertDateFormat($tblVenueBooking->booking_date)
								);
		}

		$actor                              = $user->id;
		$target                             = $tblVenueBooking->user_id;
		$elementID                          = $tblVenue->venue_id;
		$elementType                        = "venue";
		$cid                                = $tblVenueBooking->venue_table_booking_id;
		$extraParams                        = array();
		$extraParams["venueTableBookingID"] = $tblVenueBooking->venue_table_booking_id;

		BeseatedHelper::storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);
		$result = 200;
		echo $result;
		die;
	}
}
