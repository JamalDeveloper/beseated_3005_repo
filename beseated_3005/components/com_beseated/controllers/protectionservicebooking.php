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
class BeseatedControllerProtectionServiceBooking extends JControllerAdmin
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
	public function getModel($name = 'ProtectionServiceBooking', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function bookProtectionService()
		{
		$app            = JFactory::getApplication();
		$input          = $app->input;
		$itemId         = $input->getInt('Itemid');
		$serviceId      = $input->getInt('service_id');
		$protectionId   = $input->getInt('protection_id');
		$bookingDate    = $input->getString('booking_date');
		$bookingTime    = $input->getString('booking_time');
		$meetupLocation = $input->getString('meetup_location');
		$currency_code  = $input->getString('booking_currency_code');
		$currency_sign  = $input->getString('booking_currency_sign');
		$total_guard    = $input->getInt('total_guard');
		$total_hours    = $input->getInt('total_hours');
		$pricePerHours  = $input->getInt('price_per_hours');

		$user           = JFactory::getUser();
		$menu           = $app->getMenu();
		$menuItem       = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginItemid    = $menuItem->id;
		$link           = $menuItem->link.'&Itemid='.$loginItemid;

		$guestRsvpMenuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=guestrequests', true );
		$guestRsvpItemid   = $guestRsvpMenuItem->id;

		if(!$user->id)
		{
			$msg = JText::_('COM_BCTED_USER_SESSTION_NOT_FOUND');
			$app->redirect($link,$msg);
		}

		$price = $pricePerHours * $total_guard;

		$postData['protection_id']         = $protectionId;
		$postData['service_id']            = $serviceId;
		$postData['user_id']               = $user->id;
		$postData['booking_date']          = BeseatedHelper::convertToYYYYMMDD($bookingDate);
		$postData['booking_time']          = BeseatedHelper::convertToHMS($bookingTime);
		$postData['meetup_location']       = $meetupLocation;
		$postData['total_guard']           = $total_guard;
		$postData['total_hours']           = $total_hours;
		$postData['price_per_hours']       = $pricePerHours;
		$postData['total_price']           = $price * $total_hours;
		$postData['user_status']           = '2';
		$postData['protection_status']     = '1';
		$postData['is_show']               = 1;
		$postData['is_noshow']             = 0;
		$postData['remaining_amount']      = $price * $total_hours;
		$postData['booking_currency_code'] = $currency_code;
		$postData['booking_currency_sign'] = $currency_sign;
		$postData['request_date_time']     = date('Y-m-d H:i:s');;
		$postData['created']               = date('Y-m-d H:i:s');
		$postData['time_stamp']            = time();

		$model    = $this->getModel();
		$response = $model->bookProtectionService($postData);
		$link     = 'index.php?option=com_beseated&view=guestrequests&comp=luxury&Itemid='.$guestRsvpItemid;

		if($response)
		{
			$msg = JText::_('COM_BCTED_SERVICE_BOOKING_REQUEST_SUCCESS');
			$app->redirect($link,$msg);
		}
		else
		{
			$msg = JText::_('COM_BCTED_PROTECTION_SERVICE_BOOKING_REQUST_ERRORS');
			$app->redirect($link,$msg);
		}

		return true;
	}
}
