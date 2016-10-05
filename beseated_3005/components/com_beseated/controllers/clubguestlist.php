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
 * The Beseated Club Guest List Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerClubGuestList extends JControllerAdmin
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
	public function getModel($name = 'ClubGuestList', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function updateremainingguest()
	{
		$input      = JFactory::getApplication()->input;
		$requestID  = $input->get('guestlist_request_id',0,'int');
		$guestCount = $input->get('updated_guest',0,'int');

		if(!$guestCount)
		{
			echo "0";
			exit;
		}

		$tblVenueguestlist = JTable::getInstance('GuestBooking','BeseatedTable',array());
		$tblVenueguestlist->load($requestID);
		$tblVenueguestlist->remaining_guest  = $tblVenueguestlist->remaining_guest - $guestCount;

		if(!$tblVenueguestlist->store())
		{
			echo "0";
			exit;
		}

		echo "1";
		exit;
	}

	public function addGuestListRequest()
	{
		$app            = JFactory::getApplication();
		$input          = $app->input;
		$model          = $this->getModel();
		$user           = JFactory::getUser();

		$clubID         = $input->get('club_id', 0, 'int');
		$reqDate        = $input->get('request_date_time', '', 'string');
		$reqFromTime    = $input->get('requested_from_time', '', 'string');
		$reqToTime      = $input->get('requested_to_time', '', 'string');
		$guestCount     = $input->get('total_guest', 0, 'int');
		$maleCount      = $input->get('male_guest', 0, 'int');
		$femaleCount    = $input->get('female_guest', 0, 'int');
		$additionalInfo = $input->get('additional_information', '', 'string');
		$menu           = $app->getMenu();
		$menuItem       = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubguestlist', true );
		$itemid         = $menuItem->id;
		$link           = $menuItem->link.'&club_id='.$clubID.'&Itemid='.$itemid;

		if(empty($reqDate))
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

		$arrayData['venue_id']           = $clubID;
		$arrayData['user_id']            = $user->id;
		$arrayData['booking_date']       = date('Y-m-d',strtotime($reqDate));
		$arrayData['male_guest']         = $maleCount;
		$arrayData['female_guest']       = $femaleCount;
		$arrayData['total_guest']        = $guestCount;
		$arrayData['remaining_guest']    = $guestCount;
		$arrayData['guest_status']       = 2;
		$arrayData['venue_status']       = 1;
		$arrayData['remaining_total']    = $guestCount;
		$arrayData['request_date_time']  = date('Y-m-d H:i:s');
		$arrayData['response_date_time'] = date('Y-m-d H:i:s');
		$arrayData['time_stamp']         = time();
		$arrayData['created']            = date('Y-m-d H:i:s');

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
