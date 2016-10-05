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
 * The Beseated Club Table Booking Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubBottleBooking extends JModelList
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

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   0.0.1
	 */
	public function getTable($type = 'VenueBottleBooking', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function bookVenueBottle($data = array())
	{
		$user            = JFactory::getUser();
		$tblVenuebooking = $this->getTable();
		$tblVenuebooking->load(0);
		$bottle_id       = $data['bottle_id'];

		if (strpos($bottle_id, ',') !== false) {

			$bottleIdArray   = explode(',', $bottle_id);
			$qtyArray        = explode(',', $data['qty']);
			$priceArray      = explode(',', $data['price']);
			$totalPriceArray = explode(',', $data['total_price']);

			$key = array_search('0', $qtyArray);

			$bottleIdArrayCount = count($bottleIdArray);

			for ($i = 0; $i < $bottleIdArrayCount; $i++)
			{

				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base insert statement.
				$query->insert($db->quoteName('#__beseated_venue_bottle_booking'))
					->columns(array($db->quoteName('bottle_id'),
						$db->quoteName('venue_table_booking_id'),
						$db->quoteName('venue_id'),
						$db->quoteName('table_id'),
						$db->quoteName('user_id'),
						$db->quoteName('qty'),
						$db->quoteName('price'),
						$db->quoteName('total_price'),
						$db->quoteName('time_stamp'),
						$db->quoteName('created')))

					->values($db->quote($bottleIdArray[$i]) . ', ' .
						    $db->quote($data['venue_table_booking_id']) . ', ' .
						    $db->quote($data['venue_id']) . ', ' .
						    $db->quote($data['table_id']) . ', ' .
						    $db->quote($data['user_id']) . ', ' .
						    $db->quote($qtyArray[$i]) . ', ' .
							$db->quote($priceArray[$i]) . ', ' .
						    $db->quote($totalPriceArray[$i]) . ', ' .
						    $db->quote($data['time_stamp']) . ', ' .
							$db->quote($data['created']));

				// Set the query and execute the insert.
				$db->setQuery($query);

				if(!$db->execute()){
					return 0;
				}
			}

		}else{

			$tblVenuebooking->bind($data);
			if(!$tblVenuebooking->store())
			{
				return 0;
			}
		}

		$tableBooking = JTable::getInstance('Venuebooking', 'BeseatedTable');
		$tableBooking->load($data['venue_table_booking_id']);
		$min_price    = $tableBooking->total_price;

		$tblVenue = JTable::getInstance('Venue', 'BeseatedTable');
		$tblVenue->load($tableBooking->venue_id);

		$tblTable = JTable::getInstance('Table', 'BeseatedTable');
		$tblTable->load($tableBooking->table_id);

		if (strpos($data['total_price'], ',') !== false) {

			$finalPriceArray = explode(',', $data['total_price']);
			$total           = array_sum($finalPriceArray);

			$tableBooking->user_status        = BeseatedHelper::getStatusID('confirmed');
			$tableBooking->venue_status       = BeseatedHelper::getStatusID('confirmed');
			$tableBooking->total_price        = $tableBooking->total_price + $total;
			$tableBooking->final_price        = $min_price + $total;
			$tableBooking->total_bottle_price = $total;
			$tableBooking->has_bottle         = 1;
			$finalPriceCommision              = $tableBooking->final_price * $tblVenue->deposit_per /100;
			$totalPriceCommision              = $tableBooking->total_price * $tblVenue->deposit_per /100;
			$tableBooking->final_price        = $tableBooking->final_price - $finalPriceCommision;
			$tableBooking->total_price        = $tableBooking->total_price - $totalPriceCommision;

			if(!$tableBooking->store()){
				return 0;
			}

		}else{
			$tableBooking->user_status        = BeseatedHelper::getStatusID('confirmed');
			$tableBooking->venue_status       = BeseatedHelper::getStatusID('confirmed');
			$tableBooking->total_price        = $tableBooking->total_price + $data['total_price'];
			$tableBooking->final_price        = $min_price + $data['total_price'];
			$tableBooking->total_bottle_price = $data['total_price'];
			$tableBooking->has_bottle         = 1;
			$finalPriceCommision              = $tableBooking->final_price * $tblVenue->deposit_per /100;
			$totalPriceCommision              = $tableBooking->total_price * $tblVenue->deposit_per /100;
			$tableBooking->final_price        = $tableBooking->final_price - $finalPriceCommision;
			$tableBooking->total_price        = $tableBooking->total_price - $totalPriceCommision;

			if(!$tableBooking->store())
			{
				return 0;
			}
		}

		$userDetail                 = BeseatedHelper::guestUserDetail($user->id);
		$tableBooking->user_status  = BeseatedHelper::getStatusID('confirmed');
		$tableBooking->venue_status = BeseatedHelper::getStatusID('confirmed');

		$notificationType = "venue.booking.confirm";

		if($tblVenue->is_day_club)
		{
			$formatedBookingTime = BeseatedHelper::convertToHM($tableBooking->booking_time);

			$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_DAY_VENUE_TABLE_BOOKING_CONFIRMED',
								$userDetail->full_name,
								$tblTable->table_name,
								BeseatedHelper::convertDateFormat($tableBooking->booking_date),
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
								BeseatedHelper::convertDateFormat($tableBooking->booking_date)
							);
		}

		$actor                              = $user->id;
		$target                             = $tblVenue->user_id;
		$elementID                          = $tblVenue->venue_id;
		$elementType                        = "Venue";
		$cid                                = $tableBooking->venue_table_booking_id;
		$extraParams                        = array();
		$extraParams["venueTableBookingID"] = $tableBooking->venue_table_booking_id;

		$showDirection = "Show Directions";

		if(BeseatedHelper::storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams))
		{
			$venueDefaultImage = BeseatedHelper::getElementDefaultImage($tblVenue->venue_id,'Venue');
			$venueThumb        = Juri::base().'images/beseated/'.$venueDefaultImage->thumb_image;
			$bookingDate       = date('d F Y',strtotime($tableBooking->booking_date));
			$companyDetail     = JFactory::getUser($tblVenue->user_id);
			$passkey           = ($tableBooking->passkey) ? $tableBooking->passkey : '-';
			require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
			$emailAppHelper = new BeseatedEmailHelper();
			$emailAppHelper->venueBookingconfirmedUserMail($userDetail->full_name,$companyDetail->name,$venueThumb,$tblVenue->location,$companyDetail->phone,$showDirection,$cid,$bookingDate,$formatedBookingTime,$tblTable->table_name,$tblVenue->currency_code,number_format($tblTable->min_price,0),$tableBooking->male_guest,$tableBooking->female_guest,$passkey,$userDetail->full_name,$userDetail->email,$bottleRow,number_format($tableBooking->total_bottle_price,0),$userDetail->email);

			$this->jsonarray['pushNotificationData']['id']         = $tableBooking->venue_table_booking_id;
			$this->jsonarray['pushNotificationData']['to']         = $target;
			$this->jsonarray['pushNotificationData']['message']    = $title;
			//$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_VENUE_BOOKING_CONFIRMED');
			$this->jsonarray['pushNotificationData']['type']       = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype'] = '';
		}

		return 1;
	}

	public function getTableBooking()
	{
		$input   = JFactory::getApplication()->input;
		$venueId = $input->get('venue_id');
		$user    = JFactory::getUser();
		$userID  = $user->id;

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('venue_table_booking_id, table_id')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueId))
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('venue_status') . ' = ' . $db->quote('11'))
			->where($db->quoteName('user_status') . ' = ' . $db->quote('4'))
			->order($db->quoteName('venue_table_booking_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $result;
	}
}
