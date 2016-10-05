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
class BeseatedControllerYachtshareinvitation extends JControllerAdmin
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
	public function getModel($name = 'YachtShareInvitation', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function inviteUser()
	{
		$app                  = JFactory::getApplication();
		$input                = $app->input;
		$bookingID            = $input->getInt('booking_id');

		$Itemid               = $input->getInt('Itemid');
		$fbids                = $input->getString('fb_id');
		$emails               = $input->getString('invite_user');
		$inviteType           = $input->getString('invite_type');
		$menu                 = $app->getMenu();
		//$menuItem             = $menu->getItems('link','index.php?option=com_beseated&view=userbookings',true);
		//$itemid               = $menuItem->id;

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
				    $link = JURI::root().'index.php?option=com_beseated&view=yachtshareinvitation&booking_id='.$bookingID.'&Itemid='.$Itemid;
		 	        $app->redirect($link, 'Invalid email you have entered');
					return false;
				}
			}
		}

		if(empty($fbids) && $inviteType == 'facebook')
		{
			 $link = JURI::root().'index.php?option=com_beseated&view=yachtshareinvitation&booking_id='.$bookingID.'&Itemid='.$Itemid;
 	         $app->redirect($link, 'Please select any friend to invite');
			 return false;
		}

		$user                 = JFactory::getUser();
		$filterEmails         = BeseatedHelper::filterEmails($emails);
		$filterFbFrndEmails   = BeseatedHelper::filterFbIds($fbids);

		$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
		$alreadySplited       = BeseatedHelper::getSplitedDetail($bookingID,'Yacht');

		$alreadySplitedEmails = array();
		$alreadySplitedFbids  = array();

		if($alreadySplited)
		{
			foreach ($alreadySplited as $key => $split)
			{
				if(!empty($split->email))
				{
					$alreadySplitedEmails[] = $split->email;
				}
			}
		}

		$newSplitedEmails = array();
		$emails           = $emails;
		$emailsArray      = explode(",", $emails);
		$notRegiEmail     = array_udiff($emailsArray, $filterEmails['allRegEmail'],'strcasecmp');
		$notRegiEmail     = array_udiff($notRegiEmail, $alreadySplitedEmails,'strcasecmp');

		if(!empty($emails))
		{
			foreach ($filterEmails['guest'] as $key => $guest)
			{
				if($user->email == $guest)
				{
					continue;
				}
				else if(!in_array($guest, $alreadySplitedEmails))
				{
					$newSplitedEmails[] = $guest; // registered but not splited emails
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
				else if(!in_array($guest, $alreadySplitedEmails))
				{
					$newSplitedEmails[] = $guest; // registered but not invited emails
				}
			}
		}

		$notRegiEmail     = array_filter($notRegiEmail);
		//$newSplitedEmails = array_merge($notRegiEmail,$newSplitedEmails);

		if(!empty($notRegiEmail) && !empty($newSplitedEmails))
		{
			$newSplitedEmails = array_merge($notRegiEmail,$newSplitedEmails);
		}
		else if (empty($notRegiEmail) && !empty($newSplitedEmails))
		{
			$newSplitedEmails = $newSplitedEmails;
		}
		else if (!empty($notRegiEmail) && empty($newSplitedEmails))
		{
			$newSplitedEmails = $notRegiEmail;
		}

		if(count($newSplitedEmails) == 0)
		{
			 $link = JURI::root().'index.php?option=com_beseated&view=yachtshareinvitation&booking_id='.$bookingID.'&Itemid='.$Itemid;
 	        $app->redirect($link, 'User Already Invited');
			return $false;
		}

		if(!in_array($user->email, $newSplitedEmails) && !in_array($user->email, $alreadySplitedEmails))
		{
			$newSplitedEmails[] = $user->email;
		}

		$model  = $this->getModel();
		$result = $model->save($bookingID,$newSplitedEmails,$alreadySplited);

		if($result == 500)
		{
			 $link = JURI::root().'index.php?option=com_beseated&view=yachtshareinvitation&booking_id='.$bookingID.'&Itemid='.$Itemid;
 	        $app->redirect($link, 'Error occur while invite user');
			return $false;
		}
		else
		{
			$link = JURI::root().'index.php?option=com_beseated&view=yachtrequestpay&yacht_booking_id='.$bookingID.'&Itemid='.$Itemid;
 			$app->redirect($link, 'User(s) Successfully Invited');
		}
	
	}

	function replaceShareInvitee()
	{
		$app                  = JFactory::getApplication();
		$input                = $app->input;
		$bookingID            = $input->getInt('booking_id');

		$invitationID         = $input->getInt('invitationID');
		$Itemid               = $input->getInt('Itemid');
		$fbid                 = $input->getString('fb_id');
		$email                = $input->getString('invite_user');
		$inviteType           = $input->getString('invite_type');
		$menu                 = $app->getMenu();
		
		if($inviteType == 'email')
		{
			$email = explode(',', $email);

			for ($i=0; $i < count($email); $i++) 
			{ 
				if (!filter_var($email[$i], FILTER_VALIDATE_EMAIL) === false) 
				{
				    //echo("is a valid email address");
				} 
				else 
				{
				    $link = JURI::root().'index.php?option=com_beseated&view=yachtshareinvitation&invitation_id='.$invitationID.'&booking_id='.$bookingID.'&Itemid='.$Itemid;
		 	        $app->redirect($link, 'Invalid email you have entered');
					return false;
				}
			}
		}

		if(empty($fbid) && $inviteType == 'facebook')
		{
			 $link = JURI::root().'index.php?option=com_beseated&view=yachtshareinvitation&invitation_id='.$invitationID.'&booking_id='.$bookingID.'&Itemid='.$Itemid;
 	         $app->redirect($link, 'Please select any friend to invite');
			 return false;
		}

		$user = JFactory::getUser();
		$filterEmail        = BeseatedHelper::filterEmails($email);
		$filterFbFrndEmail  = BeseatedHelper::filterFbIds($fbid);

		$alreadyInvited       = BeseatedHelper::getSplitedDetail($bookingID,'Yacht');

		$alreadyInvitedEmail = array();

		if($alreadyInvited)
		{
			foreach ($alreadyInvited as $key => $invitation)
			{
				if(!empty($invitation->email))
				{
					$alreadyInvitedEmail[] = $invitation->email;
				}
			}
		}

		$newInvitedEmail  = array();
		$email            = $email;
		$emailsArray      = $email;

		$notRegiEmail     = array_udiff($emailsArray, $filterEmail['allRegEmail'],'strcasecmp'); // not registered
		$notRegiEmail     = array_udiff($notRegiEmail, $alreadyInvitedEmail,'strcasecmp');   // not registered and not invited emails

		if(!empty($email))
		{
			foreach ($filterEmail['guest'] as $key => $guest)
			{
				if($user->email == $guest)
				{
					continue;
				}
				else if(!in_array($guest, $alreadyInvitedEmail))
				{

					$newInvitedEmail[] = $guest; // registered but not invited emails
				}
			}
		}
		else
		{
			foreach ($filterFbFrndEmail['guest'] as $key => $guest)
			{
				if($user->email == $guest)
				{
					continue;
				}
				else if(!in_array($guest, $alreadyInvitedEmails))
				{
					$newInvitedEmail[] = $guest; // registered but not invited emails
				}
			}
		}


		$notRegiEmail     = array_filter($notRegiEmail);
		//$newInvitedEmails = array_merge($notRegiEmail,$newInvitedEmail);

		if(!empty($notRegiEmail) && !empty($newInvitedEmail))
		{
			$newInvitedEmails = array_merge($notRegiEmail,$newInvitedEmail);
		}
		else if (empty($notRegiEmail) && !empty($newInvitedEmail))
		{
			$newInvitedEmails = $newInvitedEmail;
		}
		else if (!empty($notRegiEmail) && empty($newInvitedEmail))
		{
			$newInvitedEmails = $notRegiEmail;
		}

		if(count($newInvitedEmails) == 0)
		{
			$link = JURI::root().'index.php?option=com_beseated&view=yachtshareinvitation&invitation_id='.$invitationID.'&booking_id='.$bookingID.'&Itemid='.$Itemid;
 	        $app->redirect($link, 'User Already Invited');
			return $false;
		}

		$model  = $this->getModel();
		$result = $model->saveReplaceInvitee($bookingID,$newInvitedEmails,$alreadyInvitedEmail,$invitationID);

		if($result == 500)
		{
			$link = JURI::root().'index.php?option=com_beseated&view=yachtshareinvitation&invitation_id='.$invitationID.'&booking_id='.$bookingID.'&Itemid='.$Itemid;
 	        $app->redirect($link, 'Error occur while invite user');
			return $false;
		}
		else
		{

			$link = JURI::root().'index.php?option=com_beseated&view=yachtrequestpay&yacht_booking_id='.$bookingID.'&Itemid='.$Itemid;
 			$app->redirect($link, 'User(s) Successfully Invited');
		}
	}

}
