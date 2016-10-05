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
 * Beseated System Message Model
 *
 * @since  0.0.1
 */
class BeseatedModelSystemMessage extends JModelAdmin
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
	public function getTable($type = 'SystemMessage', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function save($data)
	{
		require_once JPATH_SITE . '/components/com_ijoomeradv/extensions/beseated/helper.php';
		require_once JPATH_SITE . '/components/com_beseated/controllers/payment.php';

		$this->helper = new beseatedAppHelper;
	    $this->payment = new BeseatedControllerPayment;

	    $notificationType           = "admin.message";
	    $title  = JText::sprintf('COM_IJOOMERADV_BESEATED_ADMIN_SENT_MESSAGE');

		$tblSystemMessage = $this->getTable();
		if(isset($data['message_id']) && $data['message_id']){
			$message_id = $data['message_id'];
		}else{
			$message_id = 0;
		}
		$loginUser = JFactory::getUser();

		$user_ids = array();

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('city') . ' = ' . $db->quote($data['city']))
			->order($db->quoteName('user_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$resUsersInCity = $db->loadObjectList();

		foreach ($resUsersInCity as $key => $user)
		{
			$connectionID = $this->getConnectionID($loginUser->id,$user->user_id);
			/*echo $connectionID;
			exit;*/
			if($connectionID){
				$tblMessage = JTable::getInstance('Message','BeseatedTable',array());
				$tblMessage->load(0);
				$msgData = array();
				$msgData['connection_id'] = $connectionID;
				$msgData['from_user_id']  = $loginUser->id;
				$msgData['to_user_id']    = $user->user_id;
				$msgData['message_type']  = 'systemmessage';
				$msgData['message_body']  = $data['message'];
				$msgData['extra_params']  = "";
				$msgData['time_stamp']    = time();
				$tblMessage->bind($msgData);

				if($tblMessage->store())
				{
					// add this user id to send push notification;
					$this->payment->sendPushNotication($user->user_id,$title,$notificationType);
				}
			}
		}

		$this->helper->updateNotification('messages');

		$tblSystemMessage->load($message_id);
		$tblSystemMessage->bind($data);
		$tblSystemMessage->store();


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
			'com_beseated.systemmessage',
			'systemmessage',
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
			'com_beseated.edit.SystemMessage.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function getConnectionID($userID1,$userID2)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__beseated_message_connection'))
			->where(
				'(' .
					$db->quoteName('from_user_id') . ' = ' . $db->quote($userID1) . ' AND ' .
					$db->quoteName('to_user_id') . ' = ' . $db->quote($userID2) .
				') OR ('.
					$db->quoteName('from_user_id') . ' = ' . $db->quote($userID2) . ' AND ' .
					$db->quoteName('to_user_id') . ' = ' . $db->quote($userID1) .
				')'
			);
		$db->setQuery($query);
		$hasConnection = $db->loadObject();
		if($hasConnection){
			return $hasConnection->connection_id;
		}else{
			$tblConnection = JTable::getInstance('Connection','BeseatedTable',array());
			$connData = array();
			$connData['from_user_id'] = $userID1;
			$connData['to_user_id']   = $userID2;
			$connData['time_stamp']   = time();
			$tblConnection->load(0);
			$tblConnection->bind($connData);
			if($tblConnection->store())
			{
				return $tblConnection->connection_id;
			}
		}
		return false;
	}
}
