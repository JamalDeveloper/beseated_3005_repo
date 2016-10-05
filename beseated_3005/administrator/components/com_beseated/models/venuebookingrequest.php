<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * Beseated Event Model
 *
 * @since  0.0.1
 */
class BeseatedModelVenueBookingRequest extends JModelAdmin
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'VenueBooking', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   0.0.1
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_beseated.venuebookingrequest',
			'venuebookingrequest',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   0.0.1
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState(
			'com_beseated.edit.VenueBookingRequest.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}


	public function save($data)
	{
		require_once JPATH_SITE . '/components/com_ijoomeradv/extensions/beseated/helper.php';
		require_once JPATH_SITE . '/components/com_beseated/controllers/payment.php';
		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

        $beseatedParams  = BeseatedHelper::getExtensionParam();

		$input = JFactory::getApplication()->input;


		$formData = new JRegistry($input->get('jform', '', 'array'));
		$venue_table_booking_id = $formData->get('venue_table_booking_id', 0);
		$table_id               = $formData->get('table_id', 0);
		$user_id                = $formData->get('user_id', 0);
		$venue_id               = $formData->get('venue_id', 0);
		$serviceDetail          = $this->serviceDetailById($table_id);

		$currencyCode = $this->getCurrencyCode($venue_id);

		$tblElement = JTable::getInstance('Venue', 'BeseatedTable');
		$tblElement->load($venue_id);

		$data['booking_currency_code'] = $currencyCode;

		$dispatcher = JEventDispatcher::getInstance();

		if(isset($data['booking_currency_code']) && !empty($data['booking_currency_code']))
		{
			if($data['booking_currency_code'] == 'AED')
			{
					$data['booking_currency_sign'] = 'AED';
		    }
			else if($data['booking_currency_code'] == 'USD' || $data['booking_currency_code'] == 'CAD' || $data['booking_currency_code'] == 'AUD')
			{
					$data['booking_currency_sign'] = '$';
			}
			else if($data['booking_currency_sign'] == 'EUR')
			{
					$data['booking_currency_sign'] = '€';
			}
			else if($data['booking_currency_code'] == 'GBP')
			{
					$data['booking_currency_sign'] = '£';
			}
		}

		$booking_date = date('Y-m-d',strtotime($data['booking_date']));
		$booking_time = ($data['booking_time']) ? $data['booking_time'] : '00:01:01';

		$data['booking_date']          = $booking_date;
		$data['booking_time']          = $booking_time;
		$data['total_guest']           = $data['male_guest'] + $data['female_guest'];
		//$data['price_per_hours']       = $serviceDetail->min_price;
		$data['total_price']           = $serviceDetail->min_price ;
		$data['table_id']              = $formData->get('table_id', 0);
		$data['total_hours']           = ($tblElement->is_day_club) ? $beseatedParams->table_booking_hours : 23;
		$data['user_status']           = '2';
		$data['venue_status']          = '1';
		$data['is_rated']              = '0';
		$data['pay_by_cash_status']    = '0';
		$data['is_splitted']           = '0';
		$data['has_invitation']        = '0';
		$data['each_person_pay']       = '0.00';
		$data['splitted_count']        = '0';
		$data['remaining_amount']      = $serviceDetail->min_price ;
		$data['request_date_time']     = date('Y-m-d H:i:m');
		$data['deleted_by_venue']      = '0';
		$data['deleted_by_user']       = '0';
		$data['is_noshow']             = '0';
		$data['time_stamp']            = time();
		$data['privacy']               = '0';

		//echo "<pre/>";print_r($data);exit;

		$isNew = true;

		$table                = $this->getTable();

		if ($venue_table_booking_id > 0)
		{
			$isNew = false;
		}

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		$this->helper = new beseatedAppHelper;
	    $this->payment = new BeseatedControllerPayment;

	    $userDetail       = $this->helper->guestUserDetail($user_id);
		$actor            = $user_id;
		$target           = $tblElement->user_id;
		$elementID        = $venue_id;
		$elementType      = "Venue";
		$notificationType = "service.request";

		if($tblElement->is_day_club)
		{
			$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_TO_DAY_VENUE',
								$userDetail->full_name,
								$serviceDetail->table_name,
								$this->helper->convertDateFormat($data['booking_date']),
								$this->helper->convertToHM($data['booking_time'])
							);
		}
		else
		{
			$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_TO_NIGHT_VENUE',
								$userDetail->full_name,
								$serviceDetail->table_name,
								$this->helper->convertDateFormat($data['booking_date'])
							);
		}

		$cid              = $table->venue_table_booking_id;
		$extraParams      = array();
		$extraParams["venueTableBookingID"] = $cid;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$cid))
		{
			$this->payment->sendPushNotication($target,$title,$notificationType,'Venue',$table->venue_table_booking_id);

		}

		return true;
	}

	public function serviceDetailById($table_id)
	{

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_table'))
			->where($db->quoteName('table_id') . ' = ' . $db->quote($table_id));

		// Set the query and load the result.
		$db->setQuery($query);
		$serviceDetail = $db->loadObject();

		return $serviceDetail;

	}

	public function getCurrencyCode($venue_id)
	{

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('currency_code')
			->from($db->quoteName('#__beseated_venue'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venue_id));

		// Set the query and load the result.
		$db->setQuery($query);
		$currency_code = $db->loadResult();

		return $currency_code;

	}

}
