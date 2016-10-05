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
class reward
{

	private $db;
	private $IJUserID;
	private $helper;
	private $defaultUserAvatar;
	private $defaultUserCover;
	private $jsonarray;

	function __construct()
	{
		$this->db                = JFactory::getDBO();
		$this->mainframe         =  JFactory::getApplication ();
		$this->IJUserID          = $this->mainframe->getUserState ( 'com_ijoomeradv.IJUserID', 0 ); //get login user id
		$this->my                = JFactory::getUser ( $this->IJUserID ); // set the login user object
		$this->helper            = new beseatedAppHelper;
		$this->defaultUserAvatar = JUri::root().'components/com_beseated/assets/images/user-png.png';
		$this->defaultUserCover = JUri::root().'components/com_beseated/assets/images/hd_background.png';

		require_once JPATH_SITE . '/components/com_beseated/helpers/email.php';

		$this->emailHelper            = new BeseatedEmailHelper;

		$this->jsonarray         = array();

		$notificationDetail = $this->helper->getNotificationCount();

		//$allNotificationCount =  $notificationDetail['allNotificationCount']+$notificationDetail['messageCount'];
		$allNotificationCount =  $notificationDetail['allNotificationCount'];

		$this->jsonarray['globalNotifications']['bookingsCount']        = ($notificationDetail['bookingCount']) ? (string)$notificationDetail['bookingCount']:"0";
		$this->jsonarray['globalNotifications']['requestsCount']        = ($notificationDetail['requestCount']) ?  (string)$notificationDetail['requestCount']:"0";
		$this->jsonarray['globalNotifications']['messagesCount']        = ($notificationDetail['messageCount']) ?  (string)$notificationDetail['messageCount']:"0";
		$this->jsonarray['globalNotifications']['notificationsCount']   = ($allNotificationCount) ? (string)$allNotificationCount:"0";
	}

	function rewardBooking()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$userDetail = $this->helper->guestUserDetail($this->IJUserID);

		$rewardID   = IJReq::getTaskData('rewardID','','string');

		if(!$rewardID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_REWARD_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblReward = JTable::getInstance('Rewards', 'BeseatedTable');
		$tblReward->load($rewardID);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblLoyaltyPoint = JTable::getInstance('LoyaltyPoint', 'BeseatedTable');

		if(!$tblReward->reward_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_REWARD_NOT_VALID'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('SUM(earn_point)')
			->from($db->quoteName('#__beseated_loyalty_point'))
			->where($db->quoteName('is_valid') . ' = ' . $db->quote('1'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

		// Set the query and load the result.
		$db->setQuery($query);
		$total_earn_point = $db->loadResult();

		if(!$total_earn_point || $tblReward->reward_coin > $total_earn_point)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_REWARD_COIN_REQUEST_NOT_VALID'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base insert statement.
		$query->insert($db->quoteName('#__beseated_rewards_bookings'))
			->columns(array($db->quoteName('reward_id'), $db->quoteName('user_id'), $db->quoteName('reward_coin'), $db->quoteName('time_stamp'), $db->quoteName('booking_date')))
			->values($db->quote($rewardID) . ', ' . $db->quote($this->IJUserID). ', ' . $db->quote($tblReward->reward_coin). ', ' . $db->quote(time()). ', ' . $db->quote(date('Y-m-d')));

		// Set the query and execute the insert.
		$db->setQuery($query);

		$db->execute();

		$reward_bookingID = $db->insertid();

		$tblLoyaltyPoint->load(0);

		$data['user_id']    = $this->IJUserID;
		$data['earn_point'] = '-'.$tblReward->reward_coin;
		$data['point_app']  = 'purchase.reward';
		$data['title']      = 'REWARDS - '.$tblReward->reward_name ;
		$data['cid']        = $rewardID;
		$data['is_valid']   = '1';
		$data['time_stamp'] = time();

		$tblLoyaltyPoint->bind($data);

		if($tblLoyaltyPoint->store())
		{
			$totalLoyaltyPoint = $this->get_user_sum_of_loyalty_point();

			$this->emailHelper->rewardBookingMail($userDetail->full_name,$userDetail->email);
			$this->emailHelper->rewardBookingAdminMail($userDetail->full_name,$userDetail->email,$userDetail->phone,$tblReward->reward_name,$tblReward->reward_name,$tblReward->image,$tblReward->reward_desc,$tblReward->reward_coin,$totalLoyaltyPoint);
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	function getRewards()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_rewards'))
			->where($db->quoteName('published') . ' = ' . $db->quote('1'))
			->order($db->quoteName('created') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);

	    $rewards = $db->loadObjectList();

	    if (count($rewards) == 0)
		{
			$this->jsonarray['code'] = 204;

			return $this->jsonarray;
		}

	   foreach ($rewards as $key => $reward)
	   {
			$this->jsonarray['Rewards'][$key]['rewardID']    = $reward->reward_id;
			$this->jsonarray['Rewards'][$key]['rewardName']  = $reward->reward_name;
			$this->jsonarray['Rewards'][$key]['rewardDesc']  = $reward->reward_desc;
			$this->jsonarray['Rewards'][$key]['rewardCoin']  = $reward->reward_coin;
			$this->jsonarray['Rewards'][$key]['rewardImage'] = ($reward->image) ? JURi::base().$reward->image : '';
	   }

	   $this->jsonarray['code'] = 200;
	   $this->jsonarray['total'] = count($this->jsonarray['Rewards']);
	   return $this->jsonarray;

	}

	public function get_user_sum_of_loyalty_point()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('sum(earn_point) AS loyalty_points')
			->from($db->quoteName('#__beseated_loyalty_point'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->where($db->quoteName('is_valid') . ' = ' . $db->quote(1));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadResult();

		if(!$result)
		{
			$result = 0.00;
		}

		return $result;
	}

}
