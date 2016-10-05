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
 * The Beseated Message Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerMessage extends JControllerAdmin
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
	public function getModel($name = 'Messages', $prefix = 'BeseatedModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function send_message()
	{
		$app           = JFactory::getApplication();
		$input         = $app->input;
		$otherUserID   = $input->get('other_user_id',0,'int');
		$connectionID  = $input->get('connection_id',0,'int');
		$message       = $input->get('message','','string');
		$otherUserType = BeseatedHelper::getUserType($otherUserID);
		$loginUser     = JFactory::getUser();

		if(!$loginUser->id)
		{
			echo "704";
			exit;
		}

		if((!$otherUserID && !$connectionID)|| empty($message))
		{
			echo 400; // COM_IJOOMERADV_MESSAGE_INVALID_DETAIL_FOR_SEND_MESSAGE
			exit();
		}

		if(!$connectionID){
			require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
			$connectionID = BeseatedHelper::getMessageConnection($loginUser->id,$otherUserID);
		}

		if(!$connectionID){
			echo 400; // COM_IJOOMERADV_MESSAGE_INVALID_DETAIL_FOR_SEND_MESSAGE
			exit();
		}

		//$beseatedParams = BeseatedHelper::getExtensionParam();
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblMessage = JTable::getInstance('Message', 'BeseatedTable');

		$messagePost = array();
		$messagePost['connection_id'] = $connectionID;
		$messagePost['from_user_id']  = $loginUser->id;
		$messagePost['to_user_id']    = $otherUserID;
		$messagePost['message_type']  = "text";
		$messagePost['message_body']  = $message;
		$messagePost['extra_params']  = "";
		$messagePost['time_stamp']    = time();

		$tblMessage->load(0);
		$tblMessage->bind($messagePost);

		if(!$tblMessage->store())
		{
			echo 500; //COM_IJOOMERADV_MESSAGE_ERROR_WHILE_SENDING_MESSAGE
			exit();
		}

		$created = date('d-M-Y',$tblMessage->time_stamp);
		$time    = gmdate('H:i',$tblMessage->time_stamp);

		echo '<div class="message-detail-venue right">
				<div class="message-detail-venue-inner">
					<div class="message-detail-body-venue">
						<p>'.$message.'</p>
					</div>
					<div class="message-detail-date-time-venue">
						<p class="message-detail-date-venue">'.$created.'</p>
						<p class="message-detail-time-venue">'.$time.'</p>
					</div>
				</div>
			  </div>';
		exit;
	}

	public function delete_message()
	{
		$input        = JFactory::getApplication()->input;
		$connectionID = $input->getInt('connection_id');
		
		$deleteThread  = BeseatedHelper::deleteMessageThread($connectionID);

		echo $deleteThread;
		exit;

	}

	public function send_promotion_message()
	{
		$loginUser     = JFactory::getUser();

		if(!$loginUser->id)
		{
			echo 704;
			exit;
		}

		require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

	    $this->emailHelper =  new BeseatedEmailHelper;

		$beseatedParams = BeseatedHelper::getExtensionParam();
		
		$input        = JFactory::getApplication()->input;
		$subject      = $input->getstring('subject');
		$message      = $input->getstring('message');

		$userDetail =BeseatedHelper::guestUserDetail($loginUser->id);

		if(empty($userDetail->city) || empty($userDetail->location))
		{
			echo 400; // COM_IJOOMERADV_BESEATED_EMPTY_USER_CITY
			exit;
		}

		if(strtolower($userDetail->user_type) == 'yacht')
		{
			$managerDetail = BeseatedHelper::yachtUserDetail($loginUser->id);
	    }
	    elseif(strtolower($userDetail->user_type) == 'protection') 
	    {
	    	$managerDetail = BeseatedHelper::protectionUserDetail($loginUser->id);
	    }
	    elseif(strtolower($userDetail->user_type) == 'venue') 
	    {
	    	$managerDetail = BeseatedHelper::venueUserDetail($loginUser->id);
	    }
	    elseif(strtolower($userDetail->user_type) == 'chauffeur') 
	    {
	    	$managerDetail = BeseatedHelper::chauffeurUserDetail($loginUser->id);
	    }

		$company_name = strtolower($userDetail->user_type).'_name';
		$company_id   = strtolower($userDetail->user_type).'_id';

		$companyName = $managerDetail->$company_name;
		$companyID   = $managerDetail->$company_id;

		// Initialiase variables.
		$db    = JFactory::getDbo();

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('user_id')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('user_id') . ' != ' . $db->quote($loginUser->id))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'))
			->where($db->quoteName('user_type') . ' = ' . $db->quote('beseated_guest'))
			->where(
				'('.
					$db->quoteName('location') .' LIKE ' . $db->quote('%'.$userDetail->city.'%'). ' OR '.
					$db->quoteName('city') .' LIKE ' . $db->quote('%'.$userDetail->city.'%').
				')'
			);

		// Set the query and load the result.
		$db->setQuery($query);
		$user_ids = $db->loadColumn();

		foreach ($user_ids as $user_id)
		{
			$connectionID = BeseatedHelper::getMessageConnection($loginUser->id,$user_id);

			if($connectionID)
			{
				$tblMessage = JTable::getInstance('Message','BeseatedTable',array());
				$tblMessage->load(0);
				$msgData = array();
				$msgData['connection_id'] = $connectionID;
				$msgData['from_user_id']  = $loginUser->id;
				$msgData['to_user_id']    = $user_id;
				$msgData['message_type']  = strtolower($userDetail->user_type).'Promotion';
				$msgData['message_body']  = $subject."\n".$message;
				$msgData['extra_params']  = "";
				$msgData['time_stamp']    = time();

				$elementType                = ucfirst($userDetail->user_type);
				$elementID                  = $companyID;
				$cid                        = $companyID;
				$extraParams                = array();
				$extraParams["yachtID"]     = $cid;
				$notificationType           = strtolower($userDetail->user_type)."promotion.message";
				$title                      = JText::sprintf(
												'COM_BESEATED_NOTIFICATION_'.strtoupper($userDetail->user_type).'_PROMOTION_MESSAGE_TO_USER',
												$companyName);
				
				$tblMessage->bind($msgData);
				if($tblMessage->store())
				{
					//$this->helper->storeNotification($this->IJUserID,$user_id,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);

					// add this user id to send push notification;
					$push_user_ids[] = $user_id;

					$guestDetail = JFactory::getUser($user_id);

					//$this->emailHelper->userNewMessageMail($guestDetail->name,$yachtDetail->yacht_name,$message,$subject,$guestDetail->email);
				}

			}
		}

		$response   = true;

		if(!$response)
		{
			echo 500;
			exit();
		}

		$userID          = $managerDetail->user_id;
		$elementID       = $companyID;
		$elementType     = strtolower($userDetail->user_type);
		BeseatedHelper::storePromotionRequest($userID,$elementID,$elementType,$subject,$message,$userDetail->city,count($user_ids));

		echo 200;
		exit();
	}
}
