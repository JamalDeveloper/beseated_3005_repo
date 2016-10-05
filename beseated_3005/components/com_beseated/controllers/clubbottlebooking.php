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
class BeseatedControllerClubBottleBooking extends JControllerAdmin
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
	public function getModel($name = 'ClubBottleBooking', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function bookVenueBottle()
	{
		$app                    = JFactory::getApplication();
		$input                  = $app->input;
		$user                   = JFactory::getUser();
		$bottleID               = $input->getString('bottle_id');
		$venue_table_booking_id = $input->getInt('venue_table_booking_id');
		$venueID                = $input->get('club_id',0,'int');
		$tableID                = $input->get('table_id',0,'int');
		$userID                 = $user->id;
		$qty                    = $input->getString('qty');
		$price                  = $input->getString('price');
		$total_price            = $input->getString('total_price');

		$menu     = $app->getMenu();
		$menuItem = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$itemid   = $menuItem->id;
		$link     = $menuItem->link.'&Itemid='.$itemid;

		if(!$user->id)
		{
			$msg = JText::_('COM_BCTED_USER_SESSTION_NOT_FOUND');
			$app->redirect($link,$msg);
		}

		if(empty($bottleID) || empty($venueID) || empty($price) || empty($qty) || empty($total_price))
		{
			$msg = JText::_('COM_BCTED_VENUE_TABLE_BOOKING_REQUST_INVALID_DATA');
			$app->redirect($link,$msg);
		}

		$venueTableDetail = BeseatedHelper::getVenueTableDetail($tableID);

		$postData['bottle_id']              = $bottleID;
		$postData['venue_table_booking_id'] = $venue_table_booking_id;
		$postData['venue_id']               = $venueID;
		$postData['table_id']               = $tableID;
		$postData['user_id']                = $userID;
		$postData['qty']                    = $qty;
		$postData['price']                  = $price;
		$postData['total_price']            = $total_price;
		$postData['created']                = date('Y-m-d H:i:s');
		$postData['time_stamp']             = time();

		$model       = $this->getModel();
		$response    = $model->bookVenueBottle($postData);
		$bctParams   = BeseatedHelper::getExtensionParam();
		$accessLevel = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
		$access      = array('access','link');
		$property    = array($accessLevel,'index.php?option=com_beseated&view=userbookings');
		$menuItem    = $menu->getItems( $access, $property, true );
		$itemid      = $menuItem->id;
		$link        = 'index.php?option=com_beseated&view=userbookings&Itemid='.$itemid;
		$app->redirect($link);

	}
}
