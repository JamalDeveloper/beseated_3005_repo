<?php
/**
 * @package     The Beseated.Administrator
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * The Beseated ClubTableBooking Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerClubTableBooking extends JControllerAdmin
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
	public function getModel($name = 'ClubTableBooking', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function bookVenueTable()
	{
		$app              = JFactory::getApplication();
		$input            = $app->input;
		$tableID          = $input->get('table_id',0,'int');
		$clubID           = $input->get('club_id',0,'int');
		$vanueDetail      = BeseatedHelper::getVenueDetail($clubID);
		$booking_date     = $input->get('booking_date','','string');
		if ($vanueDetail->is_day_club == 1){
			$booking_time = $input->get('booking_time','','string');
		}
		$total_guest      = $input->get('total_guest',0,'int');
		$male_guest       = $input->get('male_guest',0,'int');
		$female_guest     = $input->get('female_guest',0,'int');
		$privacy          = $input->get('privacy',0,'int');
		$passkey          = $input->get('passkey',0,'int');

		$user     = JFactory::getUser();
		$menu     = $app->getMenu();
		$menuItem = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$itemid   = $menuItem->id;
		$link     = $menuItem->link.'&Itemid='.$itemid;

		if(!$user->id)
		{
			$msg = JText::_('COM_BCTED_USER_SESSTION_NOT_FOUND');
			$app->redirect($link,$msg);
		}

		$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubtables', true );
		$itemid = $menuItem->id;
		$link = 'index.php?option=com_beseated&view=clubtables'.'&club_id='.$clubID.'&Itemid='.$itemid;


		if(empty($tableID) || empty($clubID) || empty($booking_date) || empty($total_guest))
		{
			$msg = JText::_('COM_BCTED_VENUE_TABLE_BOOKING_REQUST_INVALID_DATA');
			$app->redirect($link,$msg);
		}


		$venueTableDetail = BeseatedHelper::getVenueTableDetail($tableID);
		$total_price      = $venueTableDetail->min_price;
		$currency_code    = $vanueDetail->currency_code;
		$currency_sign    = $vanueDetail->currency_sign;
		$beseatedParams   = BeseatedHelper::getExtensionParam();
		$total_hours      = $beseatedParams->table_booking_hours;

		if (isset($booking_time)){
			$time = BeseatedHelper::convertToHMS($booking_time);
		}else{
			$time = "";
		}


		$postData['venue_id']              = $clubID;
		$postData['table_id']              = $tableID;
		$postData['user_id']               = $user->id;
		$postData['booking_date']          = BeseatedHelper::convertToYYYYMMDD($booking_date);
		$postData['booking_time']          = $time;
		$postData['privacy']               = $privacy;
		$postData['passkey']               = $passkey;
		$postData['total_guest']           = $total_guest;
		$postData['male_guest']            = $male_guest;
		$postData['female_guest']          = $female_guest;
		$postData['total_hours']           = $total_hours;
		$postData['total_price']           = $total_price;
		$postData['venue_status']          = '1';
		$postData['user_status']           = '2';
		$postData['booking_currency_code'] = $currency_code;
		$postData['booking_currency_sign'] = $currency_sign;
		$postData['total_hours']           = $total_hours;
		$postData['request_date_time']     = date('Y-m-d H:i:s');;
		$postData['created']               = date('Y-m-d H:i:s');
		$postData['time_stamp']            = time();


		$model       = $this->getModel();
		$response    = $model->bookVenueTable($postData);
		$bctParams   = BeseatedHelper::getExtensionParam();
		$accessLevel = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
		$access      = array('access','link');
		$property    = array($accessLevel,'index.php?option=com_beseated&view=guestrequests');
		$menuItem    = $menu->getItems( $access, $property, true );
		$itemid      = $menuItem->id;
		$link        = 'index.php?option=com_beseated&view=guestrequests&Itemid='.$itemid;

		if($response)
		{
			$msg = JText::_('COM_BCTED_VENUE_TABLE_BOOKING_REQUST_SUCCESS');
			$app->redirect($link,$msg);
		}
		else
		{
			$msg = JText::_('COM_BCTED_VENUE_TABLE_BOOKING_REQUST_ERRORS');
			$app->redirect($link,$msg);
		}

		if(empty($reqDate) || empty($reqTime) || empty($additionalInfo))
		{
			$erMsg = JText::_('COM_BCTED_INVALIED_GUEST_LIST_REQUST_PARAMETERS');
			$this->setRedirect($link,$erMsg);

			return true;
		}

		if(!$clubID || !$guestCount)
		{
			$erMsg = JText::_('COM_BCTED_INVALIED_GUEST_LIST_REQUST_PARAMETERS');
			$this->setRedirect($link,$erMsg);

			return true;
		}

		$arrayData['venue_id']        = $clubID;
		$dateTime                     = date('Y-m-d H:i:s', strtotime($reqDate . ' ' . $reqTime));
		$arrayData['request_date']    = $dateTime;
		$arrayData['user_id']         = $user->id;
		$arrayData['number_of_guest'] = $guestCount;
		$arrayData['male_count']      = $maleCount;
		$arrayData['female_count']    = $femaleCount;
		$arrayData['additional_info'] = $additionalInfo;
		$arrayData['time_stamp']      = time();
		$arrayData['created']         = date('Y-m-d H:i:s');

		$result = $model->sendGuestListRequest($arrayData);

		if($result)
		{
			$msg = JText::_('COM_BCTED_GUEST_LIST_REQUST_SUCCESS');
			$this->setRedirect($link,$msg);

			return true;
		}

		$msg = JText::_('COM_BCTED_GUEST_LIST_REQUST_ERROR');
		$this->setRedirect($link,$msg);

		return true;
	}

	public function getTableDetail()
	{
		$input  = JFactory::getApplication()->input;
		$tableID    = $input->get('table_id', 0, 'int');

		if(!$tableID)
		{
			echo "";
			exit;
		}

		$tableDetail = BctedHelper::getVenueTableDetail($tableID);

		if($tableDetail->premium_table_id)
		{
			$tableName = ucfirst($tableDetail->venue_table_name);
		}
		else
		{
			$tableName = ucfirst($tableDetail->custom_table_name);
		}

		$tableCapacity = $tableDetail->venue_table_capacity;

		echo $tableName."|".$tableCapacity;
		exit;
	}

	public function checkForTableAvaibility()
	{
		$input          = JFactory::getApplication()->input;
		$tableID        = $input->get('table_id', 0, 'int');
		$requested_date = $input->get('requested_date', '', 'string');
		$booking_time   = $input->get('booking_time', '', 'string');
		/*$to_time        = $input->get('to_time', '', 'string');*/

		if(!$tableID)
		{
			echo "602";
			exit;
		}

		$tableDetail = BeseatedHelper::getVenueTableDetail($tableID);

		if(!$tableDetail->table_id)
		{
			echo "601";
			exit;
		}

		$result = $this->checkForVenueTableAvaibility($tableDetail->venue_id,$tableDetail->table_id,$requested_date);

		echo $result;
		exit;
	}

	public function checkForVenueTableAvaibility($venueID,$tableID,$date)
	{
		if(!$tableID)
		{
			return 601;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('table_id') . ' = ' . $db->quote($tableID))
			->where($db->quoteName('venue_status') . ' = ' . $db->quote(13))
			->where($db->quoteName('booking_date') . ' = ' . $db->quote($date));

		// Set the query and load the result.
		$db->setQuery($query);

		$slotBooked = 0;
		$bookingsOnSameDate = $db->loadObjectList();
		if (count($bookingsOnSameDate) > 0)
		{
			return 601;
		}

		return 200;
	}
}
