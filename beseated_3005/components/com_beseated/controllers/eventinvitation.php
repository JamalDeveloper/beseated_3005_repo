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
 * The Beseated Profile Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerEventInvitation extends JControllerAdmin
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
	public function getModel($name = 'EventInvitation', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function inviteUser()
	{
		$app                  = JFactory::getApplication();
		$input                = $app->input;
		$eventID              = $input->getInt('event_id');
		$eventBookingID       = $input->getInt('booking_id');
		$remainingTicket      = $input->getInt('remaining_ticket');
		$fbids                = $input->getString('fb_id');
		$emails               = $input->getString('invite_user');
		$inviteType           = $input->getString('invite_type');
		$menu                 = $app->getMenu();
		$menuItem             = $menu->getItems('link','index.php?option=com_beseated&view=userbookings',true);
		$itemid               = $menuItem->id;

		if($inviteType == 'email')
		{
			$email = explode(',', $emails);

			for ($i=0; $i < count($email); $i++) 
			{ 
				if (!filter_var($email[$i], FILTER_VALIDATE_EMAIL) === false) 
				{
				    //echo("is a valid email address");
				} 
				else 
				{
				    $link = JURI::root().'index.php?option=com_beseated&view=eventinvitation&event_id='.$eventID.'&booking_id='.$eventBookingID.'&remaining_ticket='.$remainingTicket.'&Itemid='.$itemid;
		 	        $app->redirect($link, 'Invalid email you have entered');
					return false;
				}
			}
		}

		if(empty($fbids) && $inviteType == 'facebook')
		{
			$link = JURI::root().'index.php?option=com_beseated&view=eventinvitation&event_id='.$eventID.'&booking_id='.$eventBookingID.'&remaining_ticket='.$remainingTicket.'&Itemid='.$itemid;
 	        $app->redirect($link, 'Please select any friend to invite');
			return false;
		}

		$user                 = JFactory::getUser();
		$filterEmails         = BeseatedHelper::filterEmails($emails);
		$filterFbFrndEmails   = BeseatedHelper::filterFbIds($fbids);

		$alreadyInvited       = BeseatedHelper::getInvitationDetail($eventBookingID,'event');
		$alreadyInvitedEmails = array();

		if($alreadyInvited)
		{
			foreach ($alreadyInvited as $key => $invitation)
			{
				if(!empty($invitation->email))
				{
					$alreadyInvitedEmails[] = $invitation->email;
				}
			}
		}

		$newInvitedEmails = array();
		$emails           = $emails;
		$emailsArray      = explode(",", $emails);

		$notRegiEmail     = array_udiff($emailsArray, $filterEmails['allRegEmail'],'strcasecmp'); // not registered
		$notRegiEmail     = array_udiff($notRegiEmail, $alreadyInvitedEmails,'strcasecmp');   // not registered and not invited emails

		//echo "<pre>";print_r($notRegiEmail);echo "<pre/>";exit();

		if(!empty($emails))
		{
			foreach ($filterEmails['guest'] as $key => $guest)
			{
				if($user->email == $guest)
				{
					continue;
				}
				else if(!in_array($guest, $alreadyInvitedEmails))
				{
					$newInvitedEmails[] = $guest; // registered but not invited emails
				}
			}
		}
		else
		{
			foreach ($filterFbFrndEmails['guest'] as $key => $guest)
			{
				if($user->email == $guest)
				{
					continue;
				}
				else if(!in_array($guest, $alreadyInvitedEmails))
				{
					$newInvitedEmails[] = $guest; // registered but not invited emails
				}
			}
		}

		$notRegiEmail     = array_filter($notRegiEmail);

		if(!empty($notRegiEmail) && !empty($newInvitedEmails))
		{
			$newInvitedEmails = array_merge($notRegiEmail,$newInvitedEmails);
		}
		else if (empty($notRegiEmail) && !empty($newInvitedEmails))
		{
			$newInvitedEmails = $newInvitedEmails;
		}
		else if (!empty($notRegiEmail) && empty($newInvitedEmails))
		{
			$newInvitedEmails = $notRegiEmail;
		}

		if(count($newInvitedEmails) == 0)
		{
			$link = JURI::root().'index.php?option=com_beseated&view=eventinvitation&event_id='.$eventID.'&booking_id='.$eventBookingID.'&remaining_ticket='.$remainingTicket.'&Itemid='.$itemid;
 	        $app->redirect($link, 'User Already Invited');
			return $false;
		}

		$model  = $this->getModel();
		$result = $model->save($eventBookingID,$newInvitedEmails);

		if($result == 500)
		{
			$link = JURI::root().'index.php?option=com_beseated&view=eventinvitation&event_id='.$eventID.'&booking_id='.$eventBookingID.'&remaining_ticket='.$remainingTicket.'&Itemid='.$itemid;
 	        $app->redirect($link, 'Event Tickets Not Available');
			return $false;
		}
		else
		{
			if(!empty($result))
			{
				$newInvited = $result;
				$remainingTicket =	$remainingTicket - $newInvited;

				if($remainingTicket == 0)
				{
					$link = JURI::root().'index.php?option=com_beseated&view=eventinviteduserstatus&event_id='.$eventID.'&ticket_booking_id='.$eventBookingID.'&remaining_ticket='.$remainingTicket.'&Itemid='.$itemid;
				}
				else
				{
					$link = JURI::root().'index.php?option=com_beseated&view=eventbookingdetail&event_id='.$eventID.'&ticket_booking_id='.$eventBookingID.'&remaining_ticket='.$remainingTicket.'&Itemid='.$itemid;
				}
				
	 			$app->redirect($link, 'User Successfully Invited');
			}

		
		}
	
	}
}
