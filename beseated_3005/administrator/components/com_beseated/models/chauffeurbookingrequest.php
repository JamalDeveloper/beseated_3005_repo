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
class BeseatedModelChauffeurBookingRequest extends JModelAdmin
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
	public function getTable($type = 'ChauffeurBooking', $prefix = 'BeseatedTable', $config = array())
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
			'com_beseated.chauffeurbookingrequest',
			'chauffeurbookingrequest',
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
			'com_beseated.edit.ChauffeurBookingRequest.data',
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

		$input = JFactory::getApplication()->input;

		$formData = new JRegistry($input->get('jform', '', 'array'));

		$chauffeur_booking_id = $formData->get('chauffeur_booking_id', 0);
		$user_id              = $formData->get('user_id', 0);
		$service_id           = $formData->get('service_id', 0);
		$serviceDetail        = $this->serviceDetailById($service_id);

		$chauffeur_id         = $formData->get('chauffeur_id', 0);
		$currencyCode         = $this->getCurrencyCode($chauffeur_id);

		$tblElement = JTable::getInstance('Chauffeur', 'BeseatedTable');
		$tblElement->load($chauffeur_id);

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

		$data['booking_date']         = $booking_date;
		$data['capacity']             = $serviceDetail->capacity;
		$data['service_id']           = $formData->get('service_id', 0);
		$data['user_status']          = '2';
		$data['chauffeur_status']     = '1';
		$data['is_rated']             = '0';
		$data['pay_by_cash_status']   = '0';
		$data['is_splitted']          = '0';
		$data['has_invitation']       = '0';
		$data['each_person_pay']      = '0.00';
		$data['splitted_count']       = '0';
		$data['remaining_amount']     = '0';
		$data['request_date_time']    = date('Y-m-d H:i:m');
		//$data['response_date_time']   = date('Y-m-d H:i:m');
		$data['deleted_by_chauffeur'] = '0';
		$data['deleted_by_user']      = '0';
		$data['is_noshow']            = '0';
		$data['time_stamp']           = time();
		unset($data['currency_code']);

		$isNew = true;

		$table                = $this->getTable();

		if ($chauffeur_booking_id > 0)
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
		$elementID        = $chauffeur_id;
		$elementType      = "Chauffeur";
		$notificationType = "service.request";
		$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_REQUEST_TO_CHAUFFEUR',
								$userDetail->full_name,
								$serviceDetail->service_name,
								$this->helper->convertDateFormat($data['booking_date']),
								$this->helper->convertToHM($data['booking_time'])
							);

		$cid              = $table->chauffeur_booking_id;
		$extraParams      = array();
		$extraParams["chauffeurBookingID"] = $cid;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$cid))
		{
			$this->payment->sendPushNotication($target,$title,$notificationType,'Chauffeur',$table->chauffeur_booking_id);
		}


		return true;
	}

	public function serviceDetailById($service_id)
	{

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_chauffeur_services'))
			->where($db->quoteName('service_id') . ' = ' . $db->quote($service_id));

		// Set the query and load the result.
		$db->setQuery($query);
		$serviceDetail = $db->loadObject();

		return $serviceDetail;

	}

	public function getCurrencyCode($chauffeur_id)
	{

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('currency_code')
			->from($db->quoteName('#__beseated_chauffeur'))
			->where($db->quoteName('chauffeur_id') . ' = ' . $db->quote($chauffeur_id));

		// Set the query and load the result.
		$db->setQuery($query);
		$currency_code = $db->loadResult();

		return $currency_code;

	}



}
