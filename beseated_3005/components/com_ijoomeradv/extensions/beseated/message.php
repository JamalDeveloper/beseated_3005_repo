<?php
/**
 * @package     iJoomerAdv.Site
 * @subpackage  com_ijoomeradv
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.helper' );
class message
{

	private $db;
	private $IJUserID;
	private $helper;
	private $jsonarray;
	private $emailHelper;
	private $my;

	function __construct()
	{
		require_once JPATH_SITE . '/components/com_beseated/helpers/email.php';

		$this->emailHelper       = new BeseatedEmailHelper;
		$this->db                = JFactory::getDBO();
		$this->mainframe         =  JFactory::getApplication ();
		$this->IJUserID          = $this->mainframe->getUserState ( 'com_ijoomeradv.IJUserID', 0 ); //get login user id
		$this->my                = JFactory::getUser ( $this->IJUserID ); // set the login user object
		$this->helper            = new beseatedAppHelper;
		$this->jsonarray         = array();

		$task        = IJReq::getExtTask();

		if($task == 'getMessageThreads')
		{
			$this->helper->updateNotification('messages');
		}

		$notificationDetail = $this->helper->getNotificationCount();

		//$allNotificationCount =  $notificationDetail['allNotificationCount']+$notificationDetail['messageCount'];
		$allNotificationCount =  $notificationDetail['allNotificationCount'];

		$this->jsonarray['globalNotifications']['bookingsCount']        = ($notificationDetail['bookingCount']) ? (string)$notificationDetail['bookingCount']:"0";
		$this->jsonarray['globalNotifications']['requestsCount']        = ($notificationDetail['requestCount']) ?  (string)$notificationDetail['requestCount']:"0";
		$this->jsonarray['globalNotifications']['messagesCount']        = ($notificationDetail['messageCount']) ?  (string)$notificationDetail['messageCount']:"0";
		$this->jsonarray['globalNotifications']['notificationsCount']   = ($allNotificationCount) ? (string)$allNotificationCount:"0";
	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"message","extTask":"getMessageThreads","taskData":{"pageNO":"0"}}
	 */
	function getMessageThreads()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$corePath            = JUri::base().'images/beseated/';


		$pageNO    = IJReq::getTaskData('pageNO',0);
		$pageLimit = BESEATED_GENERAL_LIST_LIMIT;
		if($pageNO==0 || $pageNO==1){
			$startFrom=0;
		}else{
			$startFrom = $pageLimit*($pageNO-1);
		}

		/*// Initialiase variables.
		$db    = JFactory::getDbo();

		$query = "SELECT * FROM `#__beseated_message` WHERE message_id IN (SELECT max(a.message_id) AS max_msg_id FROM axet6_beseated_message a , axet6_beseated_message_connection b WHERE (`a`.`from_user_id` = '".$this->IJUserID."' OR `a`.`to_user_id` = '".$this->IJUserID."' AND `b`.`deleted_by_from_user` = 0) GROUP BY `a`.`connection_id`) ORDER BY `time_stamp` DESC";
		echo $query;
		exit;*/

		$db    = JFactory::getDbo();

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('MAX(a.message_id)')
			->from($db->quoteName('#__beseated_message') .' AS a')
			//->where('(('.$db->quoteName('a.from_user_id') . ' = '. $db->quote($this->IJUserID) . ' OR '.$db->qn('a.to_user_id') . ' = '. $db->quote($this->IJUserID).'))')
			//->where($db->quoteName('b.deleted_by_from_user') . ' = ' . $db->quote('0'))
			->where('(('.$db->quoteName('a.from_user_id') . ' = '. $db->quote($this->IJUserID) .' AND '. $db->quoteName('a.deleted_by_from_user') .' = ' . $db->quote('0').')' . ' OR ' .'('.$db->qn('a.to_user_id') . ' = '. $db->quote($this->IJUserID) . ' AND ' . $db->quoteName('a.deleted_by_to_user') .' = ' . $db->quote('0').'))')
			->join('INNER','#__beseated_message_connection AS b ON b.connection_id = a.connection_id')
			->group($db->quoteName('a.connection_id'))
			->order($db->quoteName('a.time_stamp') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$MaxIds = $db->loadColumn();

		if(count($MaxIds) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_MESSAGE_NO_MESSAGE_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$MaxIds = implode(',', $MaxIds);

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_message'))
			->where(('message_id IN ('.$MaxIds.')'))
			->order($db->quoteName('time_stamp') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$resThreads = $db->loadObjectList();

		$resultThreads = array();

		foreach ($resThreads as $key => $thread)
		{
			$temp                   = array();
			$temp['messageID']      = $thread->message_id;
			$temp['connectionID']   = $thread->connection_id;
			$temp['isRead']         = $thread->is_read;

			if($this->IJUserID == $thread->from_user_id)
			{
				$otherUserID = $thread->to_user_id;
			}
			else if ($this->IJUserID == $thread->to_user_id)
			{
				$otherUserID = $thread->from_user_id;
			}

			$temp['userID']   = $otherUserID;

			$otherUserProfile = $this->helper->guestUserDetail($otherUserID);

			//echo "<pre/>";print_r($otherUserProfile);exit;

			if($otherUserProfile->user_type == 'yacht')
			{
				$elementDetail       = $this->helper->yachtUserDetail($otherUserID);
				$elementType         = "Yacht";
				$temp['threadTitle'] = ($elementDetail->yacht_name)? $elementDetail->yacht_name :"";
				$temp['location']    = ($elementDetail->location)?$elementDetail->location :"";
				$temp['city']        = ($elementDetail->city) ? $elementDetail->city :"";


				$images              = $this->helper->getElementDefaultImage($elementDetail->yacht_id,'Yacht');

				//echo "<pre/>";print_r($images->thumb_image);exit;
				$temp['thumbImage']  = ($images->thumb_image)? $corePath.$images->thumb_image : '';
				$temp['image']       = ($images->image)? $corePath.$images->image:'';
				$temp['elementID']   = ($elementDetail->yacht_id) ? $elementDetail->yacht_id :"";
				$temp['fbid']        = '';


			}
			else if($otherUserProfile->user_type == 'venue')
			{
				$elementDetail       = $this->helper->venueUserDetail($otherUserID);
				$elementType         = "Venue";
				$temp['threadTitle'] = ($elementDetail->venue_name) ? $elementDetail->venue_name :"";
				$temp['location']    = ($elementDetail->location)? $elementDetail->location :"";
				$temp['city']        = ($elementDetail->city) ? $elementDetail->city :"";

				$images              = $this->helper->getElementDefaultImage($elementDetail->venue_id,'Venue');
				$temp['thumbImage']  = ($images->thumb_image)? $corePath.$images->thumb_image : '';
				$temp['image']       = ($images->image)? $corePath.$images->image:'';
				$temp['elementID']   = ($elementDetail->venue_id) ? $elementDetail->venue_id :"";
				$temp['fbid']        = '';
			}
			else if($otherUserProfile->user_type == 'protection')
			{
				$elementDetail       = $this->helper->protectionUserDetail($otherUserID);
				$elementType         = "Protection";
				$temp['threadTitle'] = ($elementDetail->protection_name)?$elementDetail->protection_name:"";
				$temp['location']    = ($elementDetail->location)?$elementDetail->location:"";
				$temp['city']        = ($elementDetail->city)?$elementDetail->city:"";

				$images              = $this->helper->getElementDefaultImage($elementDetail->protection_id,'Protection');
				$temp['thumbImage']  = ($images->thumb_image)? $corePath.$images->thumb_image : '';
				$temp['image']       = ($images->image)? $corePath.$images->image:'';
				$temp['elementID']   = ($elementDetail->protection_id)?$elementDetail->protection_id:"";
				$temp['fbid']        = '';
			}
			else if($otherUserProfile->user_type == 'chauffeur')
			{
				$elementDetail       = $this->helper->chauffeurUserDetail($otherUserID);

				$elementType         = "Chauffeur";
				$temp['threadTitle'] = ($elementDetail->chauffeur_name)?$elementDetail->chauffeur_name:"";
				$temp['location']    = ($elementDetail->location)?$elementDetail->location:"";
				$temp['city']        = ($elementDetail->city)?$elementDetail->city:"";

				$images              = $this->helper->getElementDefaultImage($elementDetail->chauffeur_id,'Chauffeur');

				$temp['thumbImage']  = ($images->thumb_image)? $corePath.$images->thumb_image : '';
				$temp['image']       = ($images->image)? $corePath.$images->image:'';
				$temp['elementID']   = ($elementDetail->chauffeur_id)?$elementDetail->chauffeur_id:"";
				$temp['fbid']        = '';
			}
			else if($otherUserProfile->user_type == 'beseated_guest')
			{
				$elementDetail       = $otherUserProfile;

				$elementType         = "BeseatedGuest";
				$temp['threadTitle'] = ($elementDetail->full_name)?$elementDetail->full_name:"";
				$temp['location']    = ($elementDetail->location)?$elementDetail->location:"";
				$temp['city']        = ($elementDetail->city)?$elementDetail->city:"";

				$corePath            = JUri::base().'images/beseated/';
				$temp['thumbImage']  = ($otherUserProfile->thumb_avatar)?$this->helper->getUserAvatar($otherUserProfile->thumb_avatar):'';
				$temp['image']       = ($otherUserProfile->avatar)?$this->helper->getUserAvatar($otherUserProfile->avatar):'';
				$temp['elementID']   = ($otherUserProfile->user_id)?$otherUserProfile->user_id:"";
				$temp['fbid']        = ($elementDetail->is_fb_user == '1' && !empty($elementDetail->fb_id)) ? $elementDetail->fb_id : '';
			}
			else if($otherUserProfile->user_type == 'administrator')
			{
				$elementDetail       = $otherUserProfile;
				$elementType         = "Administrator";
				$temp['threadTitle'] = $elementType;
				$temp['location']    = "";
				$temp['city']        = "";

				$temp['thumbImage']  = ($otherUserProfile->thumb_avatar)?$this->helper->getUserAvatar($otherUserProfile->thumb_avatar):'';
				$temp['image']       = ($otherUserProfile->avatar)?$this->helper->getUserAvatar($otherUserProfile->avatar):'';
				//$temp['thumbImage']  = '';
				//$temp['image']       = '';
				$temp['elementID']   = '';
				$temp['fbid']        = '';
			}


			//$temp['fromUserID']   = $thread->from_user_id;
			//$temp['toUserID']     = $thread->to_user_id;
			//$temp['message_type'] = $thread->message_type;
			$temp['messageBody']    = $thread->message_body;
			$temp['created']        = $thread->time_stamp;
			$temp['elementType']    = $elementType;
			//$temp['message_body'] = $thread->time_stamp;

			$resultThreads[] = $temp;
		}

		if(count($resultThreads) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_MESSAGE_NO_MESSAGE_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}


		$this->jsonarray['code']          = 200;
		$this->jsonarray['threads']       = $resultThreads;
		$this->jsonarray['threadsInPage'] = count($resultThreads);
		//$this->jsonarray['pageLimit']     = $pageLimit;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"message","extTask":"getMessages","taskData":{"pageNO":"0","connectionID":"1"}}
	 */
	function getMessages()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$pageNO       = IJReq::getTaskData('pageNO',0);
		$connectionID = IJReq::getTaskData('connectionID',0);
		$pageLimit    = BESEATED_GENERAL_LIST_LIMIT;

		if($pageNO==0 || $pageNO==1)
		{
			$startFrom=0;
		}
		else
		{
			$startFrom = $pageLimit*($pageNO-1);
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_message'))
			->where($db->quoteName('connection_id') . ' = ' . $db->quote($connectionID))
			->where('(('.$db->quoteName('from_user_id') . ' = '. $db->quote($this->IJUserID) .' AND '. $db->quoteName('deleted_by_from_user') .' = ' . $db->quote('0').')' . ' OR ' .'('.$db->qn('to_user_id') . ' = '. $db->quote($this->IJUserID) . ' AND ' . $db->quoteName('deleted_by_to_user') .' = ' . $db->quote('0').'))')
			->order($db->quoteName('time_stamp') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$resMessages = $db->loadObjectList();

		$resultMessages = array();

		foreach ($resMessages as $key => $message)
		{
			$temp                   = array();
			$temp['messageID']      = $message->message_id;
			$temp['connectionID']   = $message->connection_id;

			if($this->IJUserID == $message->from_user_id)
			{
				$otherUserID = $message->to_user_id;
			}
			else if ($this->IJUserID == $message->to_user_id)
			{
				$otherUserID = $message->from_user_id;
			}

			$otherUserProfile = $this->helper->guestUserDetail($otherUserID);

			if($otherUserProfile->user_type == 'yacht')
			{
				$elementDetail = $this->helper->yachtUserDetail($otherUserID);
				$elementType   = "Yacht";
			}
			else if($otherUserProfile->user_type == 'venue')
			{
				$elementDetail = $this->helper->venueUserDetail($otherUserID);
				$elementType   = "Venue";
			}
			else if($otherUserProfile->user_type == 'protection')
			{
				$elementDetail = $this->helper->protectionUserDetail($otherUserID);
				$elementType   = "Protection";
			}
			else if($otherUserProfile->user_type == 'chauffeur')
			{
				$elementDetail = $this->helper->chauffeurUserDetail($otherUserID);
				$elementType   = "Chauffeur";
			}
			else if($otherUserProfile->user_type == 'beseated_guest')
			{
				$elementDetail = $otherUserProfile;
				$elementType   = "BeseatedGuest";
			}

			$temp['fromUserID']  = $message->from_user_id;
			$temp['toUserID']    = $message->to_user_id;
			$temp['messageBody'] = $message->message_body;
			$temp['messageType'] = $message->message_type;
			$temp['created']     = $message->time_stamp;
			$resultMessages[]    = $temp;
		}

		if(count($resultMessages) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_MESSAGE_NO_MESSAGE_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray['code']          = 200;
		$this->jsonarray['threads']       = $resultMessages;
		$this->jsonarray['threadsInPage'] = count($resultMessages);
		//$this->jsonarray['pageLimit']     = $pageLimit;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"message","extTask":"sendMessage","taskData":{"message":"Message 2","connectionID":"0","userID":"292"}}
	 */
	function sendMessage()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$message      = IJReq::getTaskData('message','','string');
		$userID       = IJReq::getTaskData('userID',0,'int');
		$connectionID = IJReq::getTaskData('connectionID',0,'int');

		$userDetail = JFactory::getUser($userID);

		//$this->emailHelper->userNewMessageMail($message);

		$userGroup = $this->helper->getUserType($this->IJUserID);

		if($userGroup == 'Chauffeur')
		{
			$elementDetail = $this->helper->chauffeurUserDetail($this->IJUserID);
		}
		elseif($userGroup == 'Yacht')
		{
			$elementDetail = $this->helper->yachtUserDetail($this->IJUserID);
		}
		elseif($userGroup == 'Protection')
		{
			$elementDetail = $this->helper->protectionUserDetail($this->IJUserID);
		}
		elseif($userGroup == 'Venue')
		{
			$elementDetail = $this->helper->venueUserDetail($this->IJUserID);
		}

		if((!$userID && !$connectionID)|| empty($message))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_MESSAGE_INVALID_DETAIL_FOR_SEND_MESSAGE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!$connectionID){
			require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
			$connectionID = BeseatedHelper::getMessageConnection($this->IJUserID,$userID);
		}

		if(!$connectionID){
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_MESSAGE_INVALID_DETAIL_FOR_SEND_MESSAGE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//$beseatedParams = BeseatedHelper::getExtensionParam();
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblMessage = JTable::getInstance('Message', 'BeseatedTable');


		$messagePost = array();
		$messagePost['connection_id'] = $connectionID;
		$messagePost['from_user_id']  = $this->IJUserID;
		$messagePost['to_user_id']    = $userID;
		$messagePost['message_type']  = "text";
		$messagePost['message_body']  = $message;
		$messagePost['extra_params']  = "";
		$messagePost['time_stamp']    = time();

		$tblMessage->load(0);
		$tblMessage->bind($messagePost);
		if(!$tblMessage->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_MESSAGE_ERROR_WHILE_SENDING_MESSAGE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$element_id_field   = strtolower($userGroup).'_id';
		$element_name_field = strtolower($userGroup).'_name';

		$elementType                = $userGroup;
		$elementID                  = $elementDetail->$element_id_field;
		$cid                        = $elementDetail->$element_id_field;

		$extraParams                = array();
		$extraParams[strtolower($userGroup)."ID"]= $cid;
		$notificationType           = strtolower($userGroup).".message";
		$title                      = JText::sprintf(
										'COM_BESEATED_NOTIFICATION_NORMAL_MESSAGE_TO_USER',
										$elementDetail->$element_name_field);

		//if($this->helper->storeNotification($this->IJUserID,$userID,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblMessage->message_id))
		//{
			$this->jsonarray['pushNotificationData']['id']         = '1';
			$this->jsonarray['pushNotificationData']['to']         = $userID;
			$this->jsonarray['pushNotificationData']['message']    = $title;
			/*$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_MANAGER_MSG_RECEIVED');*/
			$this->jsonarray['pushNotificationData']['type']       = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype'] = '';
		//}



		$this->emailHelper->userNewMessageMail($userDetail->name,$elementDetail->$element_name_field,$message,$subject= NULL,$userDetail->email);

		$this->jsonarray['code']         = 200;
		$this->jsonarray['connectionID'] = $connectionID;
		return $this->jsonarray;
	}

	function deleteMessageThread()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$connectionID = IJReq::getTaskData('connectionID',0,'int');

		if(!$connectionID){
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_MESSAGE_INVALID_DETAIL_FOR_DELETE_MESSAGE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblMessageConnection = JTable::getInstance('MessageConnection', 'BeseatedTable');

		$tblMessageConnection->load($connectionID);

		$messagePost = array();

		/*if($tblMessageConnection->from_user_id == $this->IJUserID)
		{
			$messagePost['deleted_by_from_user'] = '1';
		}
		else
		{
			$messagePost['deleted_by_to_user'] = '1';
		}

		$tblMessageConnection->bind($messagePost);*/

		if(!$tblMessageConnection->connection_id)
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_MESSAGE_ERROR_WHILE_DELETING_MESSAGE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$db    = JFactory::getDbo();

		$msgquery = $db->getQuery(true);

		// Create the base select statement.
		$msgquery->select('*')
			->from($db->quoteName('#__beseated_message'))
			->where($db->quoteName('connection_id') . ' = ' . $db->quote($connectionID));

		// Set the query and load the result.
		$db->setQuery($msgquery);
		$messageDetails = $db->loadObjectList();

		if(empty($messageDetails))
		{
			IJReq::setResponse(204);
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}


    	foreach ($messageDetails as $key => $message)
    	{
	    	 if($message->from_user_id == $this->IJUserID)
	    	 {
	    	 	$query1 = $db->getQuery(true);

	    	 	// Create the base update statement.
	    	 	$query1->update($db->quoteName('#__beseated_message'))
	    	 		->set($db->quoteName('deleted_by_from_user') . ' = ' . $db->quote('1'))
	    	 		->where($db->quoteName('message_id') . ' = ' . $db->quote($message->message_id));

	    	 	// Set the query and execute the update.
	    	 	$db->setQuery($query1);

	    	 	$db->execute();

	    	 }
	    	 else
	    	 {
	    	 	$query1 = $db->getQuery(true);

	    	 	// Create the base update statement.
	    	 	$query1->update($db->quoteName('#__beseated_message'))
	    	 		->set($db->quoteName('deleted_by_to_user') . ' = ' . $db->quote('1'))
	    	 		->where($db->quoteName('message_id') . ' = ' . $db->quote($message->message_id));

	    	 	// Set the query and execute the update.
	    	 	$db->setQuery($query1);

	    	 	$db->execute();
	    	 }
    	}

		$this->jsonarray['code']         = 200;
		return $this->jsonarray;

	}

}