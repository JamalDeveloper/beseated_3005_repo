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
class BeseatedModelGuestlistBooking extends JModelAdmin
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
	public function getTable($type = 'GuestlistBooking', $prefix = 'BeseatedTable', $config = array())
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
			'com_beseated.guestlistbooking ',
			'guestlistbooking',
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
			'com_beseated.edit.GuestlistBooking.data',
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

		$guest_booking_id = $formData->get('guest_booking_id', 0);
		$venue_id         = $formData->get('venue_id', 0);
		$user_id          = $formData->get('user_id', 0);

		/*$guest_booking_id = $data['guest_booking_id'];
		$venue_id         = $data['venue_id'];
		$user_id          = $data['user_id'];*/

		$dispatcher = JEventDispatcher::getInstance();

		$tblElement = JTable::getInstance('Venue', 'BeseatedTable');
		$tblElement->load($venue_id);

		$booking_date = date('Y-m-d',strtotime($data['booking_date']));

		$data['venue_id']          = $data['venue_id'];
		$data['user_id']           = $data['user_id'];
		$data['booking_date']      = $booking_date;
		$data['total_guest']       = $data['male_guest'] + $data['female_guest'];
		$data['male_guest']        = $data['male_guest'];
		$data['female_guest']      = $data['female_guest'];
		$data['guest_status']      = '2';
		$data['venue_status']      = '1';
		$data['request_date_time'] = date('Y-m-d H:i:m');
		$data['time_stamp']        = time();
		$data['remaining_guest']   = $data['total_guest'];


		//echo "<pre/>";print_r($data);exit;

		$isNew = true;

		$table                = $this->getTable();

		if ($guest_booking_id > 0)
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
		$notificationType = "guestlist.request";
		$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_GUEST_BOOKING_REQUEST_TO_VENUE',
								$userDetail->full_name,
								$tblElement->venue_name,
								$this->helper->convertDateFormat($data['booking_date'])
							);

		$cid              = $table->guest_booking_id;
		$extraParams      = array();
		$extraParams["guestlistID"] = $cid;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$cid))
		{
			$this->payment->sendPushNotication($target,$title,$notificationType,'Venue',$table->guest_booking_id);
		}

		//echo "<pre>";print_r("hi");echo "</pre>";exit;

		return true;
	}


}
