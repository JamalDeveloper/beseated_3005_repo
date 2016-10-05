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
class BeseatedControllerChauffeurServiceBooking extends JControllerAdmin
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
	public function getModel($name = 'ChauffeurServiceBooking', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function bookChauffeurService()
	{
		$app             = JFactory::getApplication();
		$input           = $app->input;
		$itemId          = $input->getInt('Itemid');
		$serviceId       = $input->getInt('service_id');
		$chauffeurId     = $input->getInt('chauffeur_id');
		$capacity        = $input->getInt('capacity');
		$bookingDate     = $input->getString('booking_date');
		$bookingTime     = $input->getString('booking_time');
		$pickupLocation  = $input->getString('pickup_location');
		$dropoffLocation = $input->getString('dropoff_location');
		$currency_code   = $input->getString('booking_currency_code');
		$currency_sign   = $input->getString('booking_currency_sign');

		$user        = JFactory::getUser();
		$menu        = $app->getMenu();
		$menuItem    = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$loginItemid = $menuItem->id;
		$link        = $menuItem->link.'&Itemid='.$loginItemid;

		$guestRsvpMenuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=guestrequests', true );
		$guestRsvpItemid   = $guestRsvpMenuItem->id;

		if(!$user->id)
		{
			$msg = JText::_('COM_BCTED_USER_SESSTION_NOT_FOUND');
			$app->redirect($link,$msg);
		}

		$postData['chauffeur_id']          = $chauffeurId;
		$postData['service_id']            = $serviceId;
		$postData['user_id']               = $user->id;
		$postData['capacity']              = $capacity;
		$postData['booking_date']          = BeseatedHelper::convertToYYYYMMDD($bookingDate);
		$postData['booking_time']          = BeseatedHelper::convertToHMS($bookingTime);
		$postData['pickup_location']       = $pickupLocation;
		$postData['dropoff_location']      = $dropoffLocation;
		$postData['is_show']               = 1;
		$postData['is_noshow']             = 0;
		$postData['chauffeur_status']      = '1';
		$postData['user_status']           = '2';
		$postData['booking_currency_code'] = $currency_code;
		$postData['booking_currency_sign'] = $currency_sign;
		$postData['request_date_time']     = date('Y-m-d H:i:s');;
		$postData['created']               = date('Y-m-d H:i:s');
		$postData['time_stamp']            = time();

		$model    = $this->getModel();
		$response = $model->bookChauffeurService($postData);
		$link     = 'index.php?option=com_beseated&view=guestrequests&comp=luxury&Itemid='.$guestRsvpItemid;

		if($response)
		{
			$msg = JText::_('COM_BCTED_SERVICE_BOOKING_REQUEST_SUCCESS');
			$app->redirect($link,$msg);
		}
		else
		{
			$msg = JText::_('COM_BCTED_CHAUFFEUR_SERVICE_BOOKING_REQUST_ERRORS');
			$app->redirect($link,$msg);
		}

		return true;
	}
}
