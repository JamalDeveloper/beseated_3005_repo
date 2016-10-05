`<?php
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
class BeseatedControllerClubbookings extends JControllerAdmin
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
	public function getModel($name = 'ClubBookings', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function deletePastBooking()
	{
		$app          = JFactory::getApplication();
		$input        = $app->input;
		$bookingID    = $input->get('booking_id',0,'int');
		$user_type    = $input->get('user_type','','string');
		$booking_type = $input->get('booking_type','','string');

		if(empty($user_type) || empty($booking_type))
		{
			echo "400";
			exit;
		}

		$model = $this->getModel();

		if($booking_type == 'package')
		{
			$response = $model->deleteBookingOfPackage($bookingID,$user_type);
			$message = 'This booking has been deleted.';
			$title = 'Success';
			BeseatedHelper::setBeseatedSessionMessage($message,$title);
		}
		else
		{
			$response = $model->deleteBooking($bookingID,$user_type);
			$message = 'This booking has been deleted.';
			$title = 'Success';
			BeseatedHelper::setBeseatedSessionMessage($message,$title);
		}

		echo $response;
		exit;
	}

	public function sendnoshowmessage()
	{
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$bookingID = $input->get('booking_id',0,'int');
		$userID    = $input->get('user_id',0,'int');

		$model    = $this->getModel();
		$response = $model->sendnoshowmessage($bookingID,$userID);

		echo $response;
		exit;
	}

	public function addGuestListRequest()
	{
		$app            = JFactory::getApplication();
		$input          = $app->input;
		$model          = $this->getModel();
		$user           = JFactory::getUser();
		$clubID         = $input->get('club_id', 0, 'int');
		$reqDate        = $input->get('requested_date', '', 'string');
		$reqTime        = $input->get('requested_time', '', 'string');
		$guestCount     = $input->get('guest_count', 0, 'int');
		$maleCount      = $input->get('male_count', 0, 'int');
		$femaleCount    = $input->get('female_count', 0, 'int');
		$additionalInfo = $input->get('additional_information', '', 'string');

		$menuItem = BctedHelper::getBctedMenuItem('user-clubguestlist');
		$Itemid   = $menuItem->id;
		$link     = $menuItem->link.'&club_id='.$clubID.'&Itemid='.$Itemid;

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
}
