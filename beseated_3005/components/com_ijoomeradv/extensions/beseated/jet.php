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
class jet
{

	private $db;
	private $IJUserID;
	private $helper;
	private $jsonarray;
	private $emailHelper;
	private $my;

	function __construct()
	{
		$this->db                = JFactory::getDBO();
		$this->mainframe         =  JFactory::getApplication ();
		$this->IJUserID          = $this->mainframe->getUserState ( 'com_ijoomeradv.IJUserID', 0 ); //get login user id
		$this->my                = JFactory::getUser ( $this->IJUserID ); // set the login user object
		$this->helper            = new beseatedAppHelper;

		$this->jsonarray         = array();

		require_once JPATH_SITE . '/components/com_beseated/helpers/beseated.php';
		require_once JPATH_SITE . '/components/com_beseated/helpers/email.php';

		$this->emailHelper            = new BeseatedEmailHelper;

		$notificationDetail = $this->helper->getNotificationCount();

		$allNotificationCount =  $notificationDetail['allNotificationCount'];
		//$allNotificationCount =  $notificationDetail['allNotificationCount']+$notificationDetail['messageCount'];

		$this->jsonarray['globalNotifications']['bookingsCount']        = ($notificationDetail['bookingCount']) ? (string)$notificationDetail['bookingCount']:"0";
		$this->jsonarray['globalNotifications']['requestsCount']        = ($notificationDetail['requestCount']) ?  (string)$notificationDetail['requestCount']:"0";
		$this->jsonarray['globalNotifications']['messagesCount']        = ($notificationDetail['messageCount']) ?  (string)$notificationDetail['messageCount']:"0";
		$this->jsonarray['globalNotifications']['notificationsCount']   = ($allNotificationCount) ? (string)$allNotificationCount:"0";
	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"jet","extTask":"getPrivateJets","taskData":{"pageNO":"0"}}
	 */
	function getPrivateJets()
	{
		$city        = IJReq::getTaskData('city','','string');
		$searchQuery = IJReq::getTaskData('query','','string');
		$pageNO      = IJReq::getTaskData('pageNO',0);
		$pageLimit   = BESEATED_GENERAL_LIST_LIMIT;
		if($pageNO==0 || $pageNO==1){
			$startFrom=0;
		}else{
			$startFrom = $pageLimit*($pageNO-1);
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__beseated_private_jet'))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'))
			->where($db->quoteName('published') . ' = ' . $db->quote('1'))
			->order($db->quoteName('company_name') . ' ASC');

		if(!empty($city))
		{
			$query->where(
				'('.
					$db->quoteName('location') .' LIKE ' . $db->quote('%'.$city.'%'). ' OR '.
					$db->quoteName('city') .' LIKE ' . $db->quote('%'.$city.'%').
				')'
			);
		}

		if(!empty($searchQuery)){
			$query->where($db->quoteName('company_name') .' LIKE ' . $db->quote('%'.$searchQuery.'%'));
		}

		$db->setQuery($query,$startFrom,$pageLimit);
		$resJets = $db->loadObjectList();

		if(count($resJets) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PRIVATE_JETS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$resultJets =  array();
		foreach ($resJets as $key => $jet)
		{
			$temp                 = array();
			$temp['privateJetID'] = $jet->private_jet_id;
			$temp['companyName']  = $jet->company_name;
			$temp['location']     = $jet->location;
			$temp['city']         = $jet->city;
			$temp['image']        = ($jet->image)?JUri::root().$jet->image:'';
			$temp['ratting']      = $jet->avg_ratting;
			$temp['latitude']     = $jet->latitude;
			$temp['longitude']    = $jet->longitude;
			$resultJets[]         = $temp;
		}

		$this->jsonarray['code']        = 200;
		$this->jsonarray['totalInPage'] = count($resultJets);
		$this->jsonarray['pageLimit']   = BESEATED_GENERAL_LIST_LIMIT;
		$this->jsonarray['privateJets'] = $resultJets;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"jet","extTask":"privateJetBooking","taskData":{"privateJetID":"1","flightDate":"2001-10-15","flightTime":"15:30:00","fromLocation":"Ahmedabad","toLocation":"Rajkot","maleGuest":"3","femaleGuest":"0","personName":"Nilesh","email":"nilesh@tasolglobal.com","phone":"9033366241","extraInformation":"this is extra information"}}
	 */
	function privateJetBooking()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$userProfile = $this->helper->guestUserDetail($this->IJUserID);

		$privateJetID     = IJReq::getTaskData('privateJetID',0,'int');
		$flightDate       = IJReq::getTaskData('flightDate','','string');
		$flightTime       = IJReq::getTaskData('flightTime','','string');
		$returnFlightDate = IJReq::getTaskData('returnFlightDate','','string');
		$returnFlightTime = IJReq::getTaskData('returnFlightTime','','string');
		$fromLocation     = IJReq::getTaskData('fromLocation','','string');
		$toLocation       = IJReq::getTaskData('toLocation','','string');
		$totalGuest       = IJReq::getTaskData('totalGuest','','string');
		//$maleGuest      = IJReq::getTaskData('maleGuest','','string');
		//$femaleGuest    = IJReq::getTaskData('femaleGuest','','string');
		$email            = IJReq::getTaskData('email','','string');
		$phone            = IJReq::getTaskData('phone','','string');
		$contactVia       = ($email) ? $email : $phone;

		$personName       = IJReq::getTaskData('personName',$userProfile->full_name,'string');
		$email            = IJReq::getTaskData('email',$userProfile->email,'string');
		$phone            = IJReq::getTaskData('phone',$userProfile->phone,'string');
		$extraInformation = IJReq::getTaskData('extraInformation','','string');




		if(!$privateJetID || empty($flightTime) || empty($flightDate) || empty($fromLocation) || empty($toLocation) /*|| (!$maleGuest && !$femaleGuest)*/)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PRIVATE_JET_INVALID_DETAIL_FOR_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}


		if($this->helper->isPastDate($flightDate))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PRIVATE_JET_INVALID_DATE_FOR_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!$this->helper->isTime($flightTime))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PRIVATE_JET_INVALID_TIME_FOR_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblJetBooking = JTable::getInstance('PrivateJetBooking', 'BeseatedTable');
		$tblJetBooking->load(0);

		$jetPost['private_jet_id']    = $privateJetID;
		$jetPost['flight_date']       = $this->helper->convertToYYYYMMDD($flightDate);

		$flight_date = date('d F Y',strtotime($jetPost['flight_date']));

		$jetPost['flight_time']        = $this->helper->convertToHMS($flightTime);
		$jetPost['return_flight_date'] = $this->helper->convertToYYYYMMDD($returnFlightDate);
		$jetPost['return_flight_time'] = $this->helper->convertToHMS($returnFlightTime);
		$jetPost['from_location']      = $fromLocation;
		$jetPost['to_location']        = $toLocation;
		$jetPost['total_guest']        = (int)$totalGuest;
		/*$jetPost['total_guest']      = (int)($maleGuest + $femaleGuest);
		$jetPost['male_guest']         = $maleGuest;
		$jetPost['female_guest']       = $femaleGuest;*/
		$jetPost['person_name']        = $personName;
		$jetPost['email']              = $email;
		$jetPost['phone']              = $phone;
		$jetPost['extra_information']  = $extraInformation;
		$jetPost['time_stamp']         = time();
		$jetPost['user_id']            = $this->IJUserID;

		$returnFlightDate = ($returnFlightDate) ? date('d F Y',strtotime($jetPost['return_flight_date'])) : '-';

		$tblJetBooking->bind($jetPost);
		if(!$tblJetBooking->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PRIVATE_JET_ERROR_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray['code'] = 200;

		//BeseatedEmailHelper::jetServiceBookingEmail($tblJetBooking->private_jet_booking_id);
		//$this->emailHelper->jetServiceBookingEmail($tblJetBooking->private_jet_booking_id);
		$this->emailHelper->jetBookingThankYouEmail($privateJetID,$personName,$email,$phone,$flight_date,$fromLocation,$toLocation,$totalGuest,$extraInformation,$returnFlightDate,$contactVia);
		return $this->jsonarray;

	}

	function getAirpotList()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('airport')
			->from($db->quoteName('#__beseated_airport_codes'))
			->order($db->quoteName('airport') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$airportList = $db->loadColumn();

		/*foreach ($airportList as $key => $airport)
		{
			$this->jsonarray ['airport']
		}*/

		$this->jsonarray['airportList'] = $airportList;
		$this->jsonarray['code']        = 200;

		return $this->jsonarray;
	}
}