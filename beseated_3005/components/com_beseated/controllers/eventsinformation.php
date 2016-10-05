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
class BeseatedControllerEventsInformation extends JControllerAdmin
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
	public function getModel($name = 'EventsInformation', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function bookEventTicket()
	{
		$app          = JFactory::getApplication();
		$eventId      = $app->input->getInt('event_id');
		$ticketPrice  = $app->input->getInt('ticket_price');
		$currencyCode = $app->input->getString('booking_currency_code');
		$currencySign = $app->input->getString('booking_currency_sign');
		$currencySign = $app->input->getString('booking_currency_sign');
		$ticketTypeID = $app->input->getString('ticket_type_id');
		$ticketQty    = $app->input->getInt('total_ticket');
		$totalPrice   = $app->input->getInt('total_price');
		$menu         = $app->getMenu();
		$bctParams    = BeseatedHelper::getExtensionParam();
		$accessLevel  = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
		$access       = array('access','link');
		$property     = array($accessLevel,'index.php?option=com_beseated&view=eventsinformation');
		$menuItem2    = $menu->getItems( $access, $property, true );
		$link2        = 'index.php?option=com_beseated&view=eventsinformation&event_id='.$eventId.'&Itemid='.$menuItem2->id;

		$guestBookingsMenuItem     = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userbookings', true );
		$guestBookingsItemid       = $guestBookingsMenuItem->id;

		$tblEventtickettypezone  = JTable::getInstance('Eventtickettypezone', 'BeseatedTable');
		$tblEventtickettypezone->load($ticketTypeID);

		if(!$ticketQty || !$eventId || !$ticketTypeID)
		{
			$msg = JText::_('COM_BCTED_EVENTS_BOOKING_REQUST_INVALID_DATA');
			$app->redirect($link2,$msg);
		}

		if($ticketQty > $tblEventtickettypezone->available_tickets)
		{
			$msg = JText::_('COM_BCTED_EVENTS_TYPE_TICKETS_NOT_AVAILABLE');
			$app->redirect($link2,$msg);
		}

		$user     = JFactory::getUser();
		$menuItem = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
		$itemid   = $menuItem->id;
		$link     = $menuItem->link.'&Itemid='.$itemid;

		if(!$user->id)
		{
			$msg = JText::_('COM_BCTED_USER_SESSTION_NOT_FOUND');
			$app->redirect($link,$msg);
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Event'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($eventId))
			->where($db->quoteName('ticket_type_id') . ' = ' . $db->quote($ticketTypeID))
			->where($db->quoteName('image_id') . ' NOT IN (SELECT `ticket_id` FROM `#__beseated_event_ticket_booking_detail` WHERE `event_id`='.$eventId.')')
			->order($db->quoteName('image_id') . ' ASC');
		$db->setQuery($query,0,$ticketQty);
		$resTicketsToBooked = $db->loadObjectList();
		$ticketsID          = array();

		foreach ($resTicketsToBooked as $key => $ticketToBooked)
		{
			$ticketsID[] = $ticketToBooked->image_id;
		}

		$postData['event_id']              = $eventId;
		$postData['user_id']               = $user->id;
		$postData['ticket_type_id']        = $ticketTypeID;
		$postData['tickets_id']            = json_encode($ticketsID);
		$postData['total_ticket']          = $ticketQty;
		$postData['ticket_price']          = $ticketPrice;
		$postData['total_price']           = $totalPrice;
		$postData['booking_currency_sign'] = $currencyCode;
		$postData['booking_currency_code'] = $currencySign;
		$postData['status']                = 1;

		$model    = $this->getModel();
		$ticket_booking_id = $model->bookEventTicket($postData);

		if(!empty($ticket_booking_id))
		{
			//$msg = JText::_('COM_BCTED_EVENT_TICKET_BOOKING_REQUST_SUCCESS');
			$msg = '';
			//$link     =  JUri::root().'index.php?option=com_beseated&view=userbookings&booking_type=event&Itemid='.$guestBookingsItemid;
			$link = JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$ticket_booking_id.'&booking_type=event';
			$app->redirect($link,$msg);
		}
		else
		{
			$msg = JText::_('COM_BCTED_EVENT_TICKET_BOOKING_REQUST_ERRORS');
			$app->redirect($link,$msg);
		}

		return true;
	}
}
