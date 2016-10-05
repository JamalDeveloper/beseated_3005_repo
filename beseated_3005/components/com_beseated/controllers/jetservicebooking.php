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
 * The Beseated Jet Service Booking Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerJetServiceBooking extends JControllerAdmin
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
	public function getModel($name = 'JetServiceBooking', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}


	public function bookJetService()
	{
		$app              = JFactory::getApplication();
		$input            = $app->input;

		$menu               = $app->getMenu();
		$from_location      = $input->get('from_location','','string');
		$to_location        = $input->get('to_location','','string');
		$flying_date        = $input->get('flight_date','','string');
		$flight_time        = $input->get('flight_time','','string');
		$return_flying_date = $input->get('return_flight_date','','string');
		$return_flight_time = $input->get('return_flight_time','','string');

		$number_of_people = $input->get('total_guest',0,'int');
		$additional_info  = $input->get('extra_information','','string');
		$contact          = $input->get('contact','','string');

		$user             = JFactory::getUser();
		$menuItem         = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$Itemid           = $menuItem->id;
		$link             = $menuItem->link.'&Itemid='.$Itemid;

		if(!$user->id)
		{
			$msg = JText::_('COM_BCTED_USER_SESSTION_NOT_FOUND');
			$app->redirect($link,$msg);
		}

		$itemId = $input->getInt('Itemid');
		$link   = 'index.php?option=com_beseated&view=jetservicebooking&Itemid='.$itemId;

		if(empty($flight_time) || empty($flying_date) || !$number_of_people || !$contact)
		{
			$msg = JText::_('COM_BCTED_VENUE_JET_BOOKING_REQUST_INVALID_DATA');
			$app->redirect($link,$msg);
		}

		$model      = $this->getModel();
		$userDetail = $this->getUserDetail();

		if($contactVia == 'Email')
		{
			$contactVia = $userDetail->email;
		}
		else
		{
			$contactVia = $userDetail->phone;
		}

		$postData['private_jet_id']     = 1;
		$postData['user_id']            = $user->id;
		$postData['flight_date']        = BeseatedHelper::convertDateFormat($flying_date, 'd F Y');
		$postData['flight_time']        = BeseatedHelper::convertToHMS($flight_time);
		$postData['return_flight_date'] = ($return_flying_date) ? BeseatedHelper::convertDateFormat($return_flying_date, 'd F Y') : '-';
		$postData['return_flight_time'] = BeseatedHelper::convertToHMS($return_flight_time);
		$postData['from_location']      = $from_location;
		$postData['to_location']        = $to_location;
		$postData['total_guest']        = $number_of_people;
		$postData['male_guest']         = $number_of_people;
		$postData['female_guest']       = 0;
		$postData['person_name']        = $userDetail->name;
		$postData['email']              = $userDetail->email;
		$postData['phone']              = $userDetail->phone;
		$postData['extra_information']  = $additional_info;
		$postData['time_stamp']         = time();
		$postData['created']            = date('Y-m-d H:i:s');

		$response = $model->bookJetService($postData);

		if($response)
		{
			require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
			$emailAppHelper = new BeseatedEmailHelper();
			$emailAppHelper->jetBookingThankYouEmail($postData['private_jet_id'],$postData['person_name'],$postData['email'],$postData['phone'],$postData['flight_date'],$postData['from_location'],$postData['to_location'],$postData['total_guest'],$postData['extra_information'],$postData['return_flight_date'],$contactVia);

			$msg = JText::_('COM_BCTED_CLUB_SERVICE_BOOKING_REQUST_SUCCESS');
			$app->redirect($link,$msg);
		}
		else
		{
			$msg = JText::_('COM_BCTED_CLUB_SERVICE_BOOKING_REQUST_ERRORS');
			$app->redirect($link,$msg);
		}

			return true;
	}

	public function getUserDetail()
	{
		$user = JFactory::getUser();

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('u.id,u.name,u.username,u.email')
			->from($db->quoteName('#__users','u'))
			->where($db->quoteName('u.id') . ' = ' . $db->quote($user->id));

		$query->select('full_name,phone')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=u.id');

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}
}
