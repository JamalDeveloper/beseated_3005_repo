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
class BeseatedControllerChauffeursendinvitation extends JControllerAdmin
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
	public function getModel($name = 'ChauffeurSendInvitation', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	function sendInvitation()
	{
		$app                  = JFactory::getApplication();
		$input                = $app->input;
		$bookingID            = $input->getInt('booking_id');
		$Itemid               = $input->getInt('Itemid');
		$fbids                = $input->getString('fb_id');
		$emails               = $input->getString('invite_user');
		$inviteType           = $input->getString('invite_type');
		$menu                 = $app->getMenu();
		
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
				    $link = JURI::root().'index.php?option=com_beseated&view=chauffeursendinvitation&booking_id='.$bookingID.'&Itemid='.$Itemid;
		 	        $app->redirect($link, 'Invalid email you have entered');
					return false;
				}
			}
		}

		if(empty($fbids) && $inviteType == 'facebook')
		{
			 $link = JURI::root().'index.php?option=com_beseated&view=chauffeursendinvitation&booking_id='.$bookingID.'&Itemid='.$Itemid;
 	         $app->redirect($link, 'Please select any friend to invite');
			 return false;
		}

		$user                 = JFactory::getUser();
		$filterEmails         = BeseatedHelper::filterEmails($emails);
		$filterFbFrndEmails   = BeseatedHelper::filterFbIds($fbids);
		$alreadyInvited       = BeseatedHelper::getInvitationDetail($bookingID,'chauffeur');
		$alreadySplitted      = BeseatedHelper::getBookedElementShareInvitations('chauffeur',$bookingID);

		$alreadyInvited       = (object)array_merge((array)$alreadyInvited, (array)$alreadySplitted);

		$alreadyInvitedEmails = array();
		$alreadyInvitedFbids  = array();

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

		//echo "<pre>";print_r($alreadyInvitedEmails);echo "<pre/>";exit();



		$newInvitedEmails = array();
		$emails           = $emails;
		$emailsArray      = explode(",", $emails);

		$notRegiEmail     = array_udiff($emailsArray, $filterEmails['allRegEmail'],'strcasecmp'); // not registered
		$notRegiEmail     = array_udiff($notRegiEmail, $alreadyInvitedEmails,'strcasecmp');   // not registered and not invited emails

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
		///$newInvitedEmails = array_merge($notRegiEmail,$newInvitedEmails);

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
			$link = JURI::root().'index.php?option=com_beseated&view=chauffeursendinvitation&booking_id='.$bookingID.'&Itemid='.$Itemid;
 	        $app->redirect($link, 'User Already Invited');
			return $false;
		}

		$model  = $this->getModel();
		$result = $model->save($bookingID,$newInvitedEmails);

		if($result == 500)
		{
			$link = JURI::root().'index.php?option=com_beseated&view=chauffeursendinvitation&booking_id='.$bookingID.'&Itemid='.$Itemid;
 	        $app->redirect($link, 'Error occur while invite user');
			return $false;
		}
		else
		{
			$link = JURI::root().'index.php?option=com_beseated&view=chauffeurinviteduserstatus&booking_id='.$bookingID.'&Itemid='.$Itemid;
 			$app->redirect($link, 'User(s) Successfully Invited');
		}

	}


	
}
