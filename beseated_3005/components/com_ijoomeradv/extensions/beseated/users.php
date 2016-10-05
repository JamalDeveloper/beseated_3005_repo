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
class users
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
		$this->jsonarray         = array();

		$notificationDetail = $this->helper->getNotificationCount();

		//$allNotificationCount =  $notificationDetail['allNotificationCount']+$notificationDetail['messageCount'];
		$allNotificationCount =  $notificationDetail['allNotificationCount'];

		$this->jsonarray['globalNotifications']['bookingsCount']        = ($notificationDetail['bookingCount']) ? (string)$notificationDetail['bookingCount']:"0";
		$this->jsonarray['globalNotifications']['requestsCount']        = ($notificationDetail['requestCount']) ?  (string)$notificationDetail['requestCount']:"0";
		$this->jsonarray['globalNotifications']['messagesCount']        = ($notificationDetail['messageCount']) ? (string)$notificationDetail['messageCount']:"0";
		$this->jsonarray['globalNotifications']['notificationsCount']   = ($allNotificationCount) ? (string)$allNotificationCount:"0";
	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"users","extTask":"getProfile","taskData":{"userID":"number"}}
	 */
	function getProfile()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$userID = IJReq::getTaskData('userID',$this->IJUserID,'int');

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();
		$groups         = $this->my->get('groups');
		$managerGroups = array("Chauffeur","Protection","Venue","Yacht");
		$userProfile                = $this->helper->guestUserDetail($userID);

		if(in_array($beseatedParams->beseated_guest, $groups))
		{
			$userType                       = "Guest";
			$userProfile                    = ($this->helper->guestUserDetail($userID))?$this->helper->guestUserDetail($userID):"";
			$elementID                      = ($userProfile->user_id)?$userProfile->user_id:"";
			$userDetail                     = array();
			$userDetail['userID']           = ($userProfile->user_id)?$userProfile->user_id:"";
			$userDetail['fullName']         = ($userProfile->full_name)?$userProfile->full_name:"";
			$userDetail['email']            = ($userProfile->email)?$userProfile->email:"";
			$userDetail['phone']            = ($userProfile->phone)?$userProfile->phone:"";
			$userDetail['birthdate']        = ($userProfile->birthdate == '0000-00-00') ? "" : $userProfile->birthdate;
			$userDetail['location']         = ($userProfile->location)?$userProfile->location:"";
			$userDetail['city']             = ($userProfile->city)?$userProfile->city:"";
			$userDetail['fbUser']           = ($userProfile->is_fb_user)?$userProfile->is_fb_user:"";
			$userDetail['fbID']             = ($userProfile->fb_id)?$userProfile->fb_id:"";
			$userDetail['notification']     = ($userProfile->notification)?$userProfile->notification:"0";
			$userDetail['avatar']           = ($userProfile->avatar)?$this->helper->getUserAvatar($userProfile->avatar):'';
			$userDetail['thumbAvatar']      = ($userProfile->thumb_avatar)?$this->helper->getUserAvatar($userProfile->thumb_avatar):'';
			$userDetail['showInBigSpender'] = ($userProfile->show_in_biggest_spender)?$userProfile->show_in_biggest_spender:"";
			$userDetail['showFriendsOnly']  = ($userProfile->show_friends_only)?$userProfile->show_friends_only:"";
		}
		else if(in_array($beseatedParams->chauffeur, $groups))
		{

			$userType                    = "Chauffeur";
			$chauffeurDetail             = ($this->helper->chauffeurUserDetail($userID))?$this->helper->chauffeurUserDetail($userID):"";
			$elementID                   = ($chauffeurDetail->chauffeur_id)?$chauffeurDetail->chauffeur_id:"";
			$userDetail                  = array();
			$userDetail['chauffeurID']   = ($chauffeurDetail->chauffeur_id)?$chauffeurDetail->chauffeur_id:"";
			$userDetail['userID']           = ($userProfile->user_id)?$userProfile->user_id:"";
			$userDetail['chauffeurName'] = ($chauffeurDetail->chauffeur_name)?$chauffeurDetail->chauffeur_name:"";
			$userDetail['location']      = ($chauffeurDetail->location)?$chauffeurDetail->location:"";
			$userDetail['city']          = ($chauffeurDetail->city)?$chauffeurDetail->city:"";
			$userDetail['currencyCode']  = ($chauffeurDetail->currency_code)?$chauffeurDetail->currency_code:"";
			$userDetail['currencyCign']  = ($chauffeurDetail->currency_sign)?$chauffeurDetail->currency_sign:"";
			$userDetail['avgRatting']    = ($chauffeurDetail->avg_ratting)?$chauffeurDetail->avg_ratting:"";
			$userDetail['latitude']      = ($chauffeurDetail->latitude)?$chauffeurDetail->latitude:"";
			$userDetail['longitude']     = ($chauffeurDetail->longitude)?$chauffeurDetail->longitude:"";
			$userDetail['notification']  = ($userProfile->notification)?$userProfile->notification:"";

			$bookingCount = $this->isConfirmedBooking($userType);

			$userDetail['isConfirmedBooking']  = ($bookingCount) ? '1':'0';

		}
		else if(in_array($beseatedParams->protection, $groups))
		{
			$userType                     = "Protection";
			$protectionDetail             = ($this->helper->protectionUserDetail($userID))?$this->helper->protectionUserDetail($userID):"";
			$elementID                    = ($protectionDetail->protection_id)?$protectionDetail->protection_id:"";
			$userDetail['protectionID']   = ($protectionDetail->protection_id)?$protectionDetail->protection_id:"";
			$userDetail['userID']           = ($userProfile->user_id)?$userProfile->user_id:"";
			$userDetail['protectionName'] = ($protectionDetail->protection_name)?$protectionDetail->protection_name:"";
			$userDetail['location']       = ($protectionDetail->location)?$protectionDetail->location:"";
			$userDetail['city']           = ($protectionDetail->city)?$protectionDetail->city:"";
			$userDetail['currencyCode']   = ($protectionDetail->currency_code)?$protectionDetail->currency_code:"";
			$userDetail['currencyCign']   = ($protectionDetail->currency_sign)?$protectionDetail->currency_sign:"";
			$userDetail['avgRatting']     = ($protectionDetail->avg_ratting)?$protectionDetail->avg_ratting:"";
			$userDetail['latitude']       = ($protectionDetail->latitude)?$protectionDetail->latitude:"";
			$userDetail['longitude']      = ($protectionDetail->longitude)?$protectionDetail->longitude:"";
			$userDetail['notification']   = ($userProfile->notification)?$userProfile->notification:"";

			$bookingCount = $this->isConfirmedBooking($userType);

			$userDetail['isConfirmedBooking']  = ($bookingCount) ? '1':'0';
		}
		else if(in_array($beseatedParams->venue, $groups))
		{
			$userType                   = "Venue";
			$venueDetail                = ($this->helper->venueUserDetail($userID))?$this->helper->venueUserDetail($userID):"";
			$elementID                  = ($venueDetail->venue_id)?$venueDetail->venue_id:"";
			$userDetail['userID']       = ($venueDetail->user_id)?$venueDetail->user_id:"";
			$userDetail['venueID']      = ($venueDetail->venue_id)?$venueDetail->venue_id:"";
			$userDetail['venueName']    = ($venueDetail->venue_name)?$venueDetail->venue_name:"";
			$userDetail['description']  = ($venueDetail->description)?$venueDetail->description:"";
			$userDetail['workingDays']  = ($venueDetail->working_days)?$venueDetail->working_days:"";
			$userDetail['music']        = ($venueDetail->music)?$venueDetail->music:"";
			$userDetail['atmosphere']   = ($venueDetail->atmosphere)?$venueDetail->atmosphere:"";
			$userDetail['location']     = ($venueDetail->location)?$venueDetail->location:"";
			$userDetail['city']         = ($venueDetail->city)?$venueDetail->city:"";
			$userDetail['currencyCode'] = ($venueDetail->currency_code)?$venueDetail->currency_code:"";
			$userDetail['currencyCign'] = ($venueDetail->currency_sign)?$venueDetail->currency_sign:"";
			$userDetail['avgRatting']   = ($venueDetail->avg_ratting)?$venueDetail->avg_ratting:"";
			$userDetail['latitude']     = ($venueDetail->latitude)?$venueDetail->latitude:"";
			$userDetail['longitude']    = ($venueDetail->longitude)?$venueDetail->longitude:"";
			$userDetail['notification'] = ($userProfile->notification)?$userProfile->notification:"";
			$userDetail['venueType']    = ($venueDetail->venue_type)?$venueDetail->venue_type:"";
			$userDetail['dayClub']      = ($venueDetail->is_day_club)?$venueDetail->is_day_club:"";

			$bookingCount = $this->isConfirmedBooking($userType);

			$userDetail['isConfirmedBooking']  = ($bookingCount) ? '1':'0';
		}
		else if(in_array($beseatedParams->yacht, $groups))
		{
			$userType                   = "Yacht";
			$yachtDetail                = ($this->helper->yachtUserDetail($userID))?$this->helper->yachtUserDetail($userID):"";
			$elementID                  = ($yachtDetail->yacht_id)?$yachtDetail->yacht_id:"";
			$userDetail['yachtID']      = ($yachtDetail->yacht_id)?$yachtDetail->yacht_id:"";
			$userDetail['userID']       = ($userProfile->user_id)?$userProfile->user_id:"";
			$userDetail['yachtName']    = ($yachtDetail->yacht_name)?$yachtDetail->yacht_name:"";
			$userDetail['location']     = ($yachtDetail->location)?$yachtDetail->location:"";
			$userDetail['city']         = ($yachtDetail->city)?$yachtDetail->city:"";
			$userDetail['currencyCode'] = ($yachtDetail->currency_code)?$yachtDetail->currency_code:"";
			$userDetail['currencyCign'] = ($yachtDetail->currency_sign)?$yachtDetail->currency_sign:"";
			$userDetail['avgRatting']   = ($yachtDetail->avg_ratting)?$yachtDetail->avg_ratting:"";
			$userDetail['latitude']     = ($yachtDetail->latitude)?$yachtDetail->latitude:"";
			$userDetail['longitude']    = ($yachtDetail->longitude)?$yachtDetail->longitude:"";
			$userDetail['notification'] = ($userProfile->notification)?$userProfile->notification:"";

			$bookingCount = $this->isConfirmedBooking($userType);

			$userDetail['isConfirmedBooking']  = ($bookingCount) ? '1':'0';
		}

		//$userDetail = $this->helper->formatProfileData($userProfile,$hasSetting);

		$this->jsonarray['code']        = 200;
		$this->jsonarray['elementType'] = $userType;
		$this->jsonarray['elementID']   = $elementID;


		if(in_array($userType, $managerGroups))
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $this->db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($this->db->quoteName('#__beseated_element_images'))
				->where($this->db->quoteName('element_id') . ' = ' . $this->db->quote($elementID))
				->where($this->db->quoteName('element_type') . ' = ' . $this->db->quote($userType));

			// Set the query and load the result.
			$this->db->setQuery($query);

			$resultImages = $this->db->loadObjectList();
			$elementImages = array();
			$corePath = JUri::base().'images/beseated/';
			foreach ($resultImages as $key => $image)
			{
				$temp = array();
				$temp['imageID']    = ($image->image_id) ?$image->image_id:"";
				$temp['thumbImage'] = ($image->thumb_image)?$corePath.$image->thumb_image:'';
				$temp['image']      = ($image->image)?$corePath.$image->image:'';
				$temp['isVideo']    = ($image->is_video) ?$image->is_video:"0";
				$temp['isDefault']  = ($image->is_default) ?$image->is_default:"0";
				$elementImages[]    = ($temp)?$temp:"";
			}

			$userDetail['elementImages'] = $elementImages;
		}

		$this->jsonarray['elementDetail'] = $userDetail;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * 	{
		  "extName": "beseated",
		  "extView": "users",
		  "extTask": "updateProfile",
		  "taskData": {
		    "city": "string",
		    "location": "string",
		    "latitude": "string",
		    "longitude": "string",
		    "fullName": "string",
		    "birthdate": "string",
		    "mobile": "string",
		    "chauffeurName": "string",
		    "protectionName": "string",
		    "venueName": "string",
		    "description": "string",
		    "workingDays": "string",
		    "music": "string",
		    "atmosphere": "string",
		    "yachtName": "string",
		    "currency": "EUR/GBP/AED/USD/CAD/AUD",
		    "password": "123456",
		    "oldPassword": "159756",
		    "notification": "Yes/No",
		    "showInBigSpender": "Yes/No",
		    "defaultImageKey": "videoorig1",
		    "defaultImageID": "",
		    "deletedImageIDs": "40,41"
		  }
		}
	 */
	function updateProfile()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$userType = $this->helper->getUserType($this->IJUserID);

		$user = new JUser;
		$user->load($this->IJUserID);
		$userPost = array();

		$city            = IJReq::getTaskData('city','','string');
		$location        = IJReq::getTaskData('location','','string');
		$latitude        = IJReq::getTaskData('latitude','','string');
		$longitude       = IJReq::getTaskData('longitude','','string');
		$password        = IJReq::getTaskData('password','','string');
		$oldPassword     = IJReq::getTaskData('oldPassword','','string');
		$notification    = IJReq::getTaskData('notification','','string');
		$currency        = IJReq::getTaskData('currency','','string');
		$defaultImageKey = IJReq::getTaskData('defaultImageKey','','string');
		$defaultImageID  = IJReq::getTaskData('defaultImageID',0,'int');
		$deletedImageIDs = IJReq::getTaskData('deletedImageIDs','','string');
		$showInBigSpender = IJReq::getTaskData('showInBigSpender','Yes','string');
		$showFriendsOnly = IJReq::getTaskData('showFriendsOnly','Yes','string');

		//$userDetail['showFriendsOnly']     = $userProfile->show_friends_only;

		$currencyCode = "";
		$currencySign = "";

		if(!empty($currency))
		{
			switch (strtoupper($currency)) {
				case 'EUR':
					$currencyCode = 'EUR';
					$currencySign = '€';
					break;

				case 'GBP':
					$currencyCode = 'GBP';
					$currencySign = '£';
					break;

				case 'AED':
					$currencyCode = 'AED';
					$currencySign = 'AED';
					break;

				case 'USD':
					$currencyCode = 'USD';
					$currencySign = '$';
					break;

				case 'CAD':
					$currencyCode = 'CAD';
					$currencySign = '$';
					break;

				case 'AUD':
					$currencyCode = 'AUD';
					$currencySign = '$';
					break;

				default:
					$currencyCode = '';
					$currencySign = '';
					break;
			}
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblProfile       = JTable::getInstance('Profile', 'BeseatedTable');

		$elementType = "";
		$elementID = 0;

		if($userType == "Guest")
		{
			$fullName  = IJReq::getTaskData('fullName','','string');
			$birthdate = IJReq::getTaskData('birthdate','','string');
			$mobile    = IJReq::getTaskData('mobile','','string');

			$tblBeseatedProfile       = JTable::getInstance('Profile', 'BeseatedTable');
			$tblBeseatedProfile->load($this->IJUserID);
			$elementType     = "Guest";
			$elementID       = $this->IJUserID;
			if(!$tblBeseatedProfile->user_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_BESEATED_GUEST_USER_NOT_VALID'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if(!empty($notification))
			{
				if(strtolower($notification) == 'yes'){
					$tblBeseatedProfile->notification = 1;
				}
				else{
					$tblBeseatedProfile->notification = 0;
				}
			}

			if(!empty($showFriendsOnly))
			{
				if(strtolower($showFriendsOnly) == 'yes'){
					$tblBeseatedProfile->show_friends_only = 1;
				}
				else{
					$tblBeseatedProfile->show_friends_only = 0;
				}
			}


			if(!empty($showInBigSpender))
			{
				if(strtolower($showInBigSpender) == 'yes'){
					$tblBeseatedProfile->show_in_biggest_spender = 1;
				}
				else{
					$tblBeseatedProfile->show_in_biggest_spender = 0;
				}
			}

			if(!empty($fullName)){
				$tblBeseatedProfile->full_name = trim($fullName);
				$userPost['name']              = trim($fullName);
			}
			if(!empty($birthdate))
				$tblBeseatedProfile->birthdate = date('Y-m-d',strtotime($birthdate));
			if(!empty($mobile))
				$tblBeseatedProfile->phone = trim($mobile);
			if(!empty($city))
			{
				$tblBeseatedProfile->city     = trim($city);
				$tblBeseatedProfile->location = trim($city);
			}

			if(!$tblBeseatedProfile->store())
			{
				IJReq::setResponseCode(500);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_BESEATED_GUEST_USER_PROFILE_NOT_UPDATED'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}
		else if($userType == "Chauffeur")
		{
			$chauffeurName   = IJReq::getTaskData('chauffeurName','','string');
			$chauffeurDetail = $this->helper->chauffeurUserDetail($this->IJUserID);
			$tblChauffeur    = JTable::getInstance('Chauffeur','BeseatedTable');
			$elementType     = "Chauffeur";
			$elementID       = $chauffeurDetail->chauffeur_id;
			$tblChauffeur->load($chauffeurDetail->chauffeur_id);
			$tblProfile->load($this->IJUserID);

			if(!$tblChauffeur->chauffeur_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_CHAUFFEUR_USER_NOT_VALID'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if(!empty($chauffeurName)){
				$tblChauffeur->chauffeur_name = $chauffeurName;
				$tblProfile->full_name        = $chauffeurName;
				$userPost['name']             = trim($chauffeurName);
			}
			if(!empty($location)){
				$tblChauffeur->location = $location;
				$tblProfile->location   = $location;
			}
			if(!empty($city)){
				$tblChauffeur->city = $city;
				$tblProfile->city   = $city;
			}
			if(!empty($latitude)){
				$tblChauffeur->latitude = $latitude;
				$tblProfile->latitude   = $latitude;
			}
			if(!empty($longitude)){
				$tblChauffeur->longitude = $longitude;
				$tblProfile->longitude   = $longitude;
			}
			if(!empty($currencyCode) && !empty($currencySign)){
				$tblChauffeur->currency_code = $currencyCode;
				$tblChauffeur->currency_sign = $currencySign;
			}


			$tblChauffeur->time_stamp = time();
			$tblProfile->time_stamp   = time();
			if(!$tblChauffeur->store())
			{
				IJReq::setResponseCode(500);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_CHAUFFEUR_PROFILE_NOT_UPDATED'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$tblProfile->store();
		}
		else if($userType == "Protection")
		{
			$protectionName   = IJReq::getTaskData('protectionName','','string');
			$protectionDetail = $this->helper->protectionUserDetail($this->IJUserID);
			$tblProtection    = JTable::getInstance('Protection','BeseatedTable');
			$elementType     = "Protection";
			$elementID       = $protectionDetail->protection_id;
			$tblProtection->load($protectionDetail->protection_id);
			$tblProfile->load($this->IJUserID);
			if(!$tblProtection->protection_id){
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_CHAUFFEUR_USER_NOT_VALID'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if(!empty($protectionName)){
				$tblProtection->protection_name = $protectionName;
				$tblProfile->full_name          = $protectionName;
				$userPost['name']               = trim($protectionName);
			}
			if(!empty($location)){
				$tblProtection->location = $location;
				$tblProfile->location    = $location;
			}
			if(!empty($city)){
				$tblProtection->city = $city;
				$tblProfile->city    = $city;
			}
			if(!empty($latitude)){
				$tblProtection->latitude = $latitude;
				$tblProfile->latitude    = $latitude;
			}
			if(!empty($longitude)){
				$tblProtection->longitude = $longitude;
				$tblProfile->longitude    = $longitude;
			}
			if(!empty($currencyCode) && !empty($currencySign)){
				$tblProtection->currency_code = $currencyCode;
				$tblProtection->currency_sign = $currencySign;
			}
			$tblProtection->time_stamp = time();
			$tblProfile->time_stamp    = time();
			if(!$tblProtection->store())
			{
				IJReq::setResponseCode(500);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_PROTECTION_PROFILE_NOT_UPDATED'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
			$tblProfile->store();
		}
		else if($userType == "Venue")
		{
			$venueName   = IJReq::getTaskData('venueName','','string');
			$description = IJReq::getTaskData('description','','string');
			$workingDays = IJReq::getTaskData('workingDays','','string');
			$music       = IJReq::getTaskData('music','','string');
			$atmosphere  = IJReq::getTaskData('atmosphere','','string');
			$venueType   = IJReq::getTaskData('venueType','','string');
			$dayClub     = IJReq::getTaskData('dayClub','Yes','string');


			$venueDetail = $this->helper->venueUserDetail($this->IJUserID);
			$tblVenue    = JTable::getInstance('Venue','BeseatedTable');
			$elementType = "Venue";
			$elementID   = $venueDetail->venue_id;
			$tblVenue->load($venueDetail->venue_id);
			$tblProfile->load($this->IJUserID);

			if(!$tblVenue->venue_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_VENUE_USER_NOT_VALID'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if(!empty($venueName)){
				$tblVenue->venue_name  = $venueName;
				$tblProfile->full_name = $venueName;
				$userPost['name']      = trim($venueName);
			}
			if(!empty($description))
				$tblVenue->description = $description;
			/*if(!empty($workingDays))
				$tblVenue->working_days = $workingDays;*/
			if(!empty($music))
				$tblVenue->music = $music;
			if(!empty($atmosphere))
				$tblVenue->atmosphere = $atmosphere;
			if(!empty($location)){
				$tblVenue->location   = $location;
				$tblProfile->location = $location;
			}
			if(!empty($city)){
				$tblVenue->city   = $city;
				$tblProfile->city = $city;
			}
			if(!empty($latitude)){
				$tblVenue->latitude   = $latitude;
				$tblProfile->latitude = $latitude;
			}
			if(!empty($longitude)){
				$tblVenue->longitude   = $longitude;
				$tblProfile->longitude = $longitude;
			}
			if(!empty($currencyCode) && !empty($currencySign)){
				$tblVenue->currency_code = $currencyCode;
				$tblVenue->currency_sign = $currencySign;
			}

			if(!empty($venueType)){
				$tblVenue->venue_type = $venueType;
			}

			if(!empty($dayClub)){
				if(strtolower($dayClub) == 'yes'){
					$tblVenue->is_day_club = 1;
				}else{
					$tblVenue->is_day_club = 0;
				}
			}

			$tblVenue->working_days = $workingDays;
			$tblVenue->time_stamp   = time();
			$tblProfile->time_stamp = time();

			if(!$tblVenue->store())
			{
				IJReq::setResponseCode(500);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_VENUE_PROFILE_NOT_UPDATED'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$tblProfile->store();
		}
		else if($userType == "Yacht")
		{
			$yachtName   = IJReq::getTaskData('yachtName','','string');
			$yachtDetail = $this->helper->yachtUserDetail($this->IJUserID);
			$tblYacht    = JTable::getInstance('Yacht','BeseatedTable');
			$elementType = "Yacht";
			$elementID   = $yachtDetail->yacht_id;
			$tblYacht->load($yachtDetail->yacht_id);
			$tblProfile->load($this->IJUserID);
			if(!$tblYacht->yacht_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_YACHT_USER_NOT_VALID'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			if(!empty($yachtName)){
				$tblYacht->yacht_name  = $yachtName;
				$tblProfile->full_name = $yachtName;
				$userPost['name']      = trim($yachtName);
			}
			if(!empty($location)){
				$tblYacht->location   = $location;
				$tblProfile->location = $location;
			}
			if(!empty($city)){
				$tblYacht->city   = $city;
				$tblProfile->city = $city;
			}
			if(!empty($latitude)){
				$tblYacht->latitude   = $latitude;
				$tblProfile->latitude = $latitude;
			}
			if(!empty($longitude)){
				$tblYacht->longitude   = $longitude;
				$tblProfile->longitude = $longitude;
			}
			if(!empty($currencyCode) && !empty($currencySign)){
				$tblYacht->currency_code = $currencyCode;
				$tblYacht->currency_sign = $currencySign;
			}

			$tblYacht->time_stamp   = time();
			$tblProfile->time_stamp = time();

			if(!$tblYacht->store())
			{
				IJReq::setResponseCode(500);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_YACHT_PROFILE_NOT_UPDATED'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$tblProfile->store();
		}
		else
		{
			$this->jsonarray['code'] = 400;
			return $this->jsonarray;
		}

		if(!empty($password))
		{
			// Get a database object
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('id, password')
				->from('#__users')
				->where('id=' . $db->quote($this->IJUserID));

			$db->setQuery($query);
			$result = $db->loadObject();

			if ($result)
			{
				jimport('joomla.user.helper');
				$match = JUserHelper::verifyPassword($oldPassword, $result->password, $result->id);

				if ($match === true)
				{
					$userPost['password'] = $password;
					$userPost['password2'] = $password;
				}
				else
				{
					IJReq::setResponseCode(501);
					IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_BESEATED_OLD_PASSWORD_NOT_VALID'));
					IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
					return false;
				}
			}
			else
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_BESEATED_OLD_PASSWORD_NOT_VALID'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
		}

		if(count($userPost) != 0)
		{
			if(!empty($password))
			{
				if(file_exists(JPATH_SITE.'/components/com_beseated/helpers/email.php'))
				{
					require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
					$emailAppHelper = new BeseatedEmailHelper();
					$emailAppHelper->updatePassword($userID,$password_set);
				}
			}

			$user->bind($userPost);
			$user->save();
		}

		$defualtPath = JPATH_ROOT . '/images/beseated/';
		/*echo "<pre>";
		print_r($_FILES);
		echo "</pre>";
		exit;*/
		$usedKeys = array();
		foreach ($_FILES as $key => $file)
		{

			/*echo $key."<pre>";
			print_r($file);
			echo "</pre>";*/
			if(in_array($key, $usedKeys)){ continue; }
			if($elementType == 'Guest')
			{
				if(is_array($file) && isset($file['size']) && $file['size']>0)
				{
					$imagePath = $this->helper->uplaodFile($file,$elementType,$elementID);
					if(!empty($imagePath))
					{
						$storeThumbPath = "";
						if(!empty($imagePath))
						{
							if(!JFolder::exists($defualtPath.$elementType.'/'. $elementID . '/thumb'))
							{
								JFolder::create($defualtPath.$elementType.'/'. $elementID . '/thumb');
							}

							$pathInfo       = pathinfo($defualtPath.$imagePath);
							$thumbPath      =  $pathInfo['dirname'].'/thumb/thumb_'.$pathInfo['basename'];
							$storeThumbPath = $elementType."/". $elementID . "/thumb/thumb_".$pathInfo['basename'];

							$this->helper->createThumb($defualtPath.$imagePath,$thumbPath);
						}
						$tblBeseatedProfile       = JTable::getInstance('Profile', 'BeseatedTable');
						$tblBeseatedProfile->load($this->IJUserID);
						$tblBeseatedProfile->avatar = $imagePath;
						$tblBeseatedProfile->thumb_avatar = $storeThumbPath;
						$tblBeseatedProfile->is_use_fb_image = '0';
						$tblBeseatedProfile->store();
					}
				}
			}
			else
			{
				if(is_array($file) && isset($file['size']) && $file['size']>0)
				{
					$imagePath      = $this->helper->uplaodFile($file,$elementType,$elementID);
					$fileType       = "";
					$storeThumbPath = "";
					$isVideo        = 0;
					$storeImage     = 0;
					if(file_exists($defualtPath.$imagePath)){
						$storeImage = 1;
						$pathInfo   = pathinfo($defualtPath.$imagePath);
						$fileType   = $pathInfo['extension'];
						$mime       = mime_content_type($defualtPath.$imagePath);

						if(strstr($mime, "video/")){
							$isVideo = 1;
						}else if(strstr($mime, "image/")){
							$isVideo = 0;
						}
					}

					if(!empty($imagePath) && !$isVideo)
					{

						if(!JFolder::exists($defualtPath.$elementType.'/'. $elementID . '/thumb'))
						{
							JFolder::create($defualtPath.$elementType.'/'. $elementID . '/thumb');
						}

						$pathInfo       = pathinfo($defualtPath.$imagePath);
						$thumbPath      =  $pathInfo['dirname'].'/thumb/thumb_'.$pathInfo['basename'];
						$storeThumbPath = $elementType."/". $elementID . "/thumb/thumb_".$pathInfo['basename'];
						$this->helper->createThumb($defualtPath.$imagePath,$thumbPath);
					}

					if($isVideo)
					{
						$usedKeys[] = $key.'thumb';
						$storeThumbPath      = $this->helper->uplaodFile($_FILES[$key.'thumb'],$elementType,$elementID);
					}

					if($storeImage)
					{
						$tblImages               = JTable::getInstance('Images','BeseatedTable');
						$tblImages->load(0);
						$tblImages->element_id   = $elementID;
						$tblImages->element_type = $elementType;
						$tblImages->thumb_image  = $storeThumbPath;
						$tblImages->image        = $imagePath;
						$tblImages->is_video     = $isVideo;
						$tblImages->file_type    = $fileType;
						$tblImages->time_stamp   = time();
						if(strtolower($key)  == strtolower($defaultImageKey))
						{
							// Initialiase variables.
							$db    = JFactory::getDbo();
							$query = $db->getQuery(true);

							// Create the base update statement.
							$query->update($db->quoteName('#__beseated_element_images'))
								->set($db->quoteName('is_default') . ' = ' . $db->quote(0))
								->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
								->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType));

							// Set the query and execute the update.
							$db->setQuery($query);
							$db->execute();

							$tblImages->is_default = 1;
						}

						$tblImages->store();
					}
				}
			}
		}
		//exit;

		if($defaultImageID)
		{
			$tblImages               = JTable::getInstance('Images','BeseatedTable');
			$tblImages->load($defaultImageID);
			if($tblImages->image_id)
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base update statement.
				$query->update($db->quoteName('#__beseated_element_images'))
					->set($db->quoteName('is_default') . ' = ' . $db->quote(0))
					->where($db->quoteName('element_id') . ' = ' . $db->quote($tblImages->element_id))
					->where($db->quoteName('element_type') . ' = ' . $db->quote($tblImages->element_type));

				// Set the query and execute the update.
				$db->setQuery($query);
				$db->execute();

				$tblImages->is_default = 1;
				$tblImages->store();
			}
		}

		if(!empty($deletedImageIDs))
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($db->quoteName('#__beseated_element_images'))
				->where($db->quoteName('image_id') . ' IN ('.$deletedImageIDs.') ');

			// Set the query and load the result.
			$db->setQuery($query);

			$deleteImages = $db->loadObjectList();
			$defaultPath = JPATH_SITE.'/images/beseated/';
			foreach ($deleteImages as $key => $image)
			{
				if($image->thumb_image && !empty($image->thumb_image) && file_exists($defaultPath.$image->thumb_image))
				{
					@unlink($defaultPath.$image->thumb_image);
				}

				if($image->image && !empty($image->image) && file_exists($defaultPath.$image->image))
				{
					@unlink($defaultPath.$image->image);
				}
			}

			$query = $db->getQuery(true);

			// Create the base delete statement.
			$query->delete()
				->from($db->quoteName('#__beseated_element_images'))
				->where($db->quoteName('image_id') . ' IN ('.$deletedImageIDs.') ');

			// Set the query and execute the delete.
			$db->setQuery($query);
			$db->execute();
		}

		$this->jsonarray['code'] = 200;
		$this->jsonarray = $this->getProfile();
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"users","extTask":"addFavourite","taskData":{"elementID":"1","elementType":"Venue"}}
	 */
	function addFavourite()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$elementID   = IJReq::getTaskData('elementID',0,'int');
		$elementType = IJReq::getTaskData('elementType','','string');
		$allElementType = array('Venue','Yacht','Chauffeur','Private Jet','Protection');

		if(!$elementID || empty($elementType) || !in_array($elementType, $allElementType))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_FAVOURITE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblFavourite = JTable::getInstance('Favourite', 'BeseatedTable');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('count(1)')
			->from($db->quoteName('#__beseated_favourite'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadResult();
		if ($result)
		{
			IJReq::setResponseCode(707);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_FAVOURITE_ALREADY_ADDED'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$favouritePost                 = array();
		$favouritePost['element_id']   = $elementID;
		$favouritePost['element_type'] = $elementType;
		$favouritePost['user_id']      = $this->IJUserID;
		$favouritePost['time_stamp']   = time();

		$tblFavourite->load(0);
		$tblFavourite->bind($favouritePost);
		if(!$tblFavourite->store())
		{
			IJReq::setResponseCode(707);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_FAVOURITE_NOT_ADDED'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray['code'] = 200;
		$this->jsonarray['message'] = JText::_('COM_IJOOMERADV_BESEATED_FAVOURITE_ADDED_SUCCESSFULLY');

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"users","extTask":"removeFavourite","taskData":{"elementID":"1","elementType":"Venue"}}
	 */
	function removeFavourite()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$elementID   = IJReq::getTaskData('elementID',0,'int');
		$elementType = IJReq::getTaskData('elementType','','string');
		$allElementType = array('Venue','Yacht','Chauffeur','Private Jet','Protection');

		if(!$elementID || empty($elementType) || !in_array($elementType, $allElementType))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_USER_FAVOURITE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->delete()
			->from($db->quoteName('#__beseated_favourite'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote((int) $elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
			->where($db->quoteName('user_id') . ' = ' . $db->quote((int) $this->IJUserID));


		// Set the query and execute the delete.
		$db->setQuery($query);
		$db->execute();

		$this->jsonarray['code'] = 200;
		$this->jsonarray['message'] = JText::_('COM_IJOOMERADV_BESEATED_FAVOURITE_REMOVED_SUCCESSFULLY');

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"users","extTask":"getCity","taskData":{"cityOf":""}}
	 */
	function getCity()
	{
		$cityOf    = IJReq::getTaskData('cityOf','','string');
		$latitude  = IJReq::getTaskData('latitude','','string');
		$longitude = IJReq::getTaskData('longitude','','string');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		if($cityOf == 'Venue')
		{
			$city_sql = "SELECT DISTINCT(city) AS city FROM `#__beseated_venue` WHERE city<>'' AND published=1 AND has_table=1";
		}
		else if($cityOf == 'Chauffeur')
		{
			$city_sql = "SELECT DISTINCT(city) AS city FROM `#__beseated_chauffeur` WHERE city<>'' AND published=1 AND has_service=1";
		}
		else if($cityOf == 'Protection')
		{
			$city_sql = "SELECT DISTINCT(city) AS city FROM `#__beseated_protection` WHERE city<>'' AND published=1 AND has_service=1";
		}
		else if($cityOf == 'Yacht')
		{
			$city_sql = "SELECT DISTINCT(city) AS city FROM `#__beseated_yacht` WHERE city<>'' AND published=1 AND has_service=1";
		}
		else if($cityOf == 'Private Jet')
		{
			$city_sql = "SELECT DISTINCT(city) AS city FROM `#__beseated_private_jet` WHERE city<>'' AND published=1 ";
		}
		else if($cityOf == 'Event')
		{
			//$city_sql = "SELECT DISTINCT(city) AS city FROM `#__beseated_event` WHERE city<>'' AND published=1 AND available_ticket>=1 AND STR_TO_DATE(CONCAT(event_date,'',event_time),'%Y-%m-%d %h:%i:%s') >= '".date('Y-m-d H:i:s')."'";

			/*$query->select('*')
			->from($this->db->quoteName('#__beseated_event'))
			->where($this->db->quoteName('published') . ' = ' . $this->db->quote('1'))
			->where($this->db->quoteName('is_deleted') . ' = ' . $this->db->quote('0'))
			->where(''.$this->db->quoteName('').', " ", '.$this->db->quoteName('').'),"")' . ' >= ' . $this->db->quote());*/

			$db    = JFactory::getDbo();
			$city_sql = $this->db->getQuery(true);

			// Create the base select statement.
			$city_sql->select('DISTINCT(city)')
				->from($this->db->quoteName('#__beseated_event'))
				->where($this->db->quoteName('published') . ' = ' . $this->db->quote('1'))
				->where($this->db->quoteName('city') . ' <> ' . $this->db->quote(''))
				//->where($this->db->quoteName('available_ticket') . ' >= ' . $this->db->quote('1'))
				->where($this->db->quoteName('is_deleted') . ' = ' . $this->db->quote('0'))
				->where('CONCAT('.$this->db->quoteName('event_date').', " ", '.$this->db->quoteName('event_time') . ') >= ' . $this->db->quote(date('Y-m-d H:i:s')));


		}
		else
		{
			$city_sql = "SELECT DISTINCT(city) AS city FROM `#__beseated_user_profile` WHERE city<>'' AND is_deleted=0";
		}

		$db->setQuery($city_sql);
		$city = $db->loadColumn();

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

		$cityName = BeseatedHelper::getAddressFromLatlong($latitude,$longitude);

		$city = array_map('ucfirst',$city);

		if(count($city) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_CITY_NOT_AVAILABLE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray['code'] = 200;
		$city = array_map('ucfirst',array_unique($city));


		if(in_array($cityName, $city))
		{
			for ($i = 0; $i < count($city); $i++)
			{
				if($city[$i] == $cityName)
				{
					unset($city[$i]);
				}

			}

			$cityName = array(0 =>$cityName);
			sort($city);
			$city = array_merge($cityName,$city);
		}
		else
		{
			sort($city);
		}


		$this->jsonarray['city'] = $city;


		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"users","extTask":"getRegisteredFBUser","taskData":{"fbIDs":"967077526644929,1525073147805756,1406085503054237,127394494268271"}}
	 */
	function getRegisteredFBUser()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$fbIDs = IJReq::getTaskData('fbIDs','','string');

		if(empty($fbIDs))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_FBIDS_TO_FIND_USERS_OF_FACEBOOK'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$fbIDsArray = explode(',', $fbIDs);
		$fbIDsComma = implode('","', $fbIDsArray);
		$fbIDsComma = '"'.$fbIDsComma.'"';

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('fb_id') . ' IN ('.$fbIDsComma.')')
			->where($db->quoteName('user_id') . ' IN ( SELECT `id` FROM `#__users` WHERE block=0)')
			->where($db->quoteName('user_type') . ' = '.$db->quote('beseated_guest'))
			->order($db->quoteName('user_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$resUsers = $db->loadObjectList();
		$resultUser = array();
		foreach ($resUsers as $key => $user)
		{
			if($user->user_id == $this->IJUserID)
				continue;

			$tempUser = array();
			$tempUser['userID']       = $user->user_id;
			$tempUser['fullName']    = $user->full_name;
			$tempUser['email']        = $user->email;
			$tempUser['thumbAvatar'] = ($user->thumb_avatar)?$this->helper->getUserAvatar($user->thumb_avatar):'';
			$tempUser['avatar']       = ($user->avatar)?$this->helper->getUserAvatar($user->avatar):'';

			$resultUser[] = $tempUser;
		}

		if(count($resultUser)==0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_NOT_FIND_USERS_OF_FACEBOOK'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$this->jsonarray['code'] = 200;
		$this->jsonarray['total'] = count($resultUser);
		$this->jsonarray['users'] = $resultUser;

		return $this->jsonarray;

	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"users","extTask":"testingVideo","taskData":{}}
	 */
	function testingVideo()
	{
		/*$defualtPath = JPATH_ROOT . '/images/beseated/';
		$elementType = "";
		foreach ($_FILES as $key => $file)
		{
			if(is_array($file) && isset($file['size']) && $file['size']>0)
			{
				$imagePath = $this->helper->uplaodFile($file,'Testing','0');

				$fileType   = "";
				$storeThumbPath = "";
				$isVideo    = 0;
				$storeImage = 0;
				if(file_exists($defualtPath.$imagePath)){
					$storeImage = 1;
					$pathInfo   = pathinfo($defualtPath.$imagePath);
					$fileType   = $pathInfo['extension'];
					$mime       = mime_content_type($defualtPath.$imagePath);

					if(strstr($mime, "video/")){
						$isVideo = 1;
					}else if(strstr($mime, "image/")){
						$isVideo = 0;
					}
				}

				if(!empty($imagePath) && !$isVideo)
				{
					if(!JFolder::exists($defualtPath.$elementType.'/'. $elementID . '/thumb'))
					{
						JFolder::create($defualtPath.$elementType.'/'. $elementID . '/thumb');
					}

					$pathInfo       = pathinfo($defualtPath.$imagePath);
					$thumbPath      =  $pathInfo['dirname'].'/thumb/thumb_'.$pathInfo['basename'];
					$storeThumbPath = $elementType."/". $elementID . "/thumb/thumb_".$pathInfo['basename'];

					$this->helper->createThumb($defualtPath.$imagePath,$thumbPath);
				}

				if($isVideo)
				{
					$source        = $defualtPath.$imagePath;
					$orignalFile   = pathinfo($source);
					$videoExtAllow = array('mov','mp4');
					$storeFlv      = "";
					$storeMp4      = "";
					$storeWebm     = "";
					$user = JFactory::getUser();

					if(file_exists($source) && in_array(strtolower($orignalFile['extension']), $videoExtAllow))
					{
						$destFlv = $orignalFile['dirname'].'/'.$orignalFile['filename'].'_flv.flv';
						$storeFlv =   $elementType."/". $elementID .'/'.$orignalFile['filename'].'_flv.flv';
						$command = "/usr/bin/ffmpeg -y -i $source -g 30 -vcodec copy -acodec copy $destFlv 2>".JPATH_SITE."/ffmpeg_test1.txt";
						$output = shell_exec($command);

						$destMp4 = $orignalFile['dirname'].'/'.$orignalFile['filename'].'_mp4.mp4';
						$storeMp4 =  'images/bcted/venue/'.$user->id.'/'.$orignalFile['filename'].'_mp4.mp4';
						$command = "/usr/bin/ffmpeg -i ".$destFlv." -ar 22050 -vf \"transpose=1\" ".$destMp4; //Wroking
						$output = shell_exec($command);

						$destPng = $orignalFile['dirname'].'/'.$orignalFile['filename'].'_png.png';
						$storeImg =  'images/bcted/venue/'.$user->id.'/'.$orignalFile['filename'].'_png.png';
						$command = "/usr/bin/ffmpeg -i $destMp4 -r 1 -s 700x600 -f image2 $destPng";
						$output = shell_exec($command);

						$destWebm = $orignalFile['dirname'].'/'.$orignalFile['filename'].'_webm.webm';
						$storeWebm =  'images/bcted/venue/'.$user->id.'/'.$orignalFile['filename'].'_webm.webm';
						$command = "/usr/bin/ffmpeg -i ".$source." -acodec libvorbis -ac 2 -ab 96k -ar 44100 -b 345k -s 640x360 -vf \"transpose=1\" ".$destWebm; //Wroking
						$output = shell_exec($command);


						$this->jsonarray['mp4'] = $storeMp4;
						$this->jsonarray['img'] = $storeImg;
						$this->jsonarray['webm'] = $storeWebm;
						$this->jsonarray['flv'] = $storeFlv;
					}
				}
			}
		}*/

		echo JPATH_SITE.'/images/beseated/';
		exit;
		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	function concierge()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

		$lat    = IJReq::getTaskData('lat', '', 'string');
		$long   = IJReq::getTaskData('long', '', 'string');

		$address   = IJReq::getTaskData('address', '', 'string');

		$cityName = BeseatedHelper::getAddressFromLatlong($lat,$long);

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_concierge'))
			->where($db->quoteName('city') . ' = ' . $db->quote($cityName));

		// Set the query and load the result.
		$db->setQuery($query);
		$cityDetail = $db->loadObject();

		/*if(empty($cityDetail))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_ADRESS'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}*/


		if(!empty($cityDetail))
		{
			$this->jsonarray['phoneNo']      = $cityDetail->phone_no;
		}
		else
		{
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($db->quoteName('#__beseated_concierge'))
				->where($db->quoteName('is_default') . ' = ' . $db->quote('1'));

			// Set the query and load the result.
			$db->setQuery($query);
			$cityDetail = $db->loadObject();

			$this->jsonarray['phoneNo']      = $cityDetail->phone_no;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('SUM(earn_point)')
			->from($db->quoteName('#__beseated_loyalty_point'))
			->where($db->quoteName('is_valid') . ' = ' . $db->quote('1'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID))
			->order($db->quoteName('time_stamp') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$totalPoints = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		$this->jsonarray['totalLoyaltyPoints'] = ($totalPoints) ? $totalPoints : 0;
		$this->jsonarray['code']               = 200;
		return $this->jsonarray;

	}

	function conciergeCall()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$tblLoyaltyPoint = JTable::getInstance('LoyaltyPoint', 'BeseatedTable');

		$tblLoyaltyPoint->load(0);

		$data['user_id']    = $this->IJUserID;
		$data['earn_point'] = '-50';
		$data['point_app']  = 'call.concierge';
		$data['cid']        = '0';
		$data['title']      = 'Concierge Call';
		$data['is_valid']   = '1';
		$data['time_stamp'] = time();

		$tblLoyaltyPoint->bind($data);
		$tblLoyaltyPoint->store();

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	function readNotification()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$notificationID    = IJReq::getTaskData('notificationID', 0, 'int');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$tblNotification = JTable::getInstance('Notification', 'BeseatedTable');

		$tblNotification->load($notificationID);

		if(!$tblNotification->notification_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_NOTIFACATION_DETAIL'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$tblNotification->is_read = '1';
		$tblNotification->store();

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;

	}

	function isConfirmedBooking($userType)
	{
		//$bookingTypeField = strtolower($userType).'_id';
		$bookingType      = strtolower($userType);

		if($userType == 'Venue')
		{
			$userType = 'venue_table';
		}

		$lowerBookingType = strtolower($userType).'_booking';

		$statusArray[] = $this->helper->getStatusID('booked');

		// Initialiase variables.
		$db    = JFactory::getDbo();

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('COUNT(*) as booking')
			->from($db->quoteName('#__beseated_'.$lowerBookingType) .' AS a')
			->where($db->quoteName('a.user_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('b.user_id') . ' = ' . $db->quote($this->IJUserID))
			->join('INNER', '#__beseated_'.$bookingType.' AS b ON b.'.$bookingType.'_id=a.'.$bookingType.'_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$bookingCount = $db->loadResult();

		return $bookingCount;

	}

	function filterSharedInvitedUser()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$emails      = IJReq::getTaskData('emails', '', 'string');
		$fbIDs       = IJReq::getTaskData('fbIDs', '', 'string');
		$bookingType = IJReq::getTaskData('bookingType', '', 'string');
		$bookedType  = IJReq::getTaskData('bookedType', '', 'string');
		$bookingID   = IJReq::getTaskData('bookingID', 0, 'int');

		$splitedInvitedUserDetails = array();
		$splitedUserDetail         = array();
		$invitedUserDetail         = array();

		//echo "<pre/>";print_r($emails);exit;

		if(ucfirst($bookingType) == 'Event')
		{
			$bookingType = 'Ticket';
		}

		//$emails = explode(',', $emails);
		//$fbIDs = explode(',', $fbIDs);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$tblElementBooking = JTable::getInstance(ucfirst($bookingType).'Booking', 'BeseatedTable');
		$tblElementBooking->load($bookingID);

		$lowerBookingType = strtolower($bookingType);

		if($lowerBookingType == 'venue')
		{
			$elementBookingIdField = 'venue_table_booking_id';
			$lowerBookingType      = 'venue_table';
		}
		else
		{
			$elementBookingIdField = $lowerBookingType.'_booking_id';
		}

		//echo "<pre/>";print_r($elementBookingIdField);exit;

		if(!$tblElementBooking->$elementBookingIdField)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_ELEMENT_BOOKING_DETAIL'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$db    = JFactory::getDbo();

		if(strtolower($bookingType) != 'ticket')
		{
			if(strtolower($bookedType) == 'share')
			{
				$querySplit = $db->getQuery(true);
				$querySplit->select('email,fbid')
							->from($db->quoteName('#__beseated_'.$lowerBookingType.'_booking_split','split'))
							->where($db->quoteName('split.'.$lowerBookingType.'_booking_id') . ' = ' . $db->quote($bookingID));

				$db->setQuery($querySplit);

				$splitedUserDetail = $db->loadObjectList();

			}
			else if (strtolower($bookedType) == 'invitation')
			{
				$queryInvited = $db->getQuery(true);
				$queryInvited->select('email,fbid')
							->from($db->quoteName('#__beseated_invitation'))
							->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($bookingID))
							->where($db->quoteName('element_type') . ' = ' . $db->quote(strtolower($bookingType)));

				$db->setQuery($queryInvited);

				$invitedUserDetail = $db->loadObjectList();

			}
			else if(empty($bookedType))
			{
				$querySplit = $db->getQuery(true);
				$querySplit->select('email,fbid')
							->from($db->quoteName('#__beseated_'.$lowerBookingType.'_booking_split','split'))
							->where($db->quoteName('split.'.$lowerBookingType.'_booking_id') . ' = ' . $db->quote($bookingID));

				$db->setQuery($querySplit);

				$splitedUserDetail = $db->loadObjectList();

				$queryInvited = $db->getQuery(true);
				$queryInvited->select('email,fbid')
							->from($db->quoteName('#__beseated_invitation'))
							->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($bookingID))
							->where($db->quoteName('element_type') . ' = ' . $db->quote(strtolower($bookingType)));

				$db->setQuery($queryInvited);

				$invitedUserDetail = $db->loadObjectList();
			}
			else
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_ELEMENT_INVALID_BOOKED_TYPE'));
				IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

				return false;
			}


			//$splitedInvitedUserDetails = array_merge($splitedUserDetail,$invitedUserDetail);

			if(!empty($splitedUserDetail) && (!empty($invitedUserDetail)))
			{
				$splitedInvitedUserDetails = array_merge($splitedUserDetail,$invitedUserDetail);
			}
			elseif(empty($splitedUserDetail) && (!empty($invitedUserDetail)))
			{
				$splitedInvitedUserDetails = $invitedUserDetail;
			}
			elseif(!empty($splitedUserDetail) && (empty($invitedUserDetail)))
			{
				$splitedInvitedUserDetails = $splitedUserDetail;
			}
		}
		else
		{
			if(strtolower($bookedType) == 'invitation' || empty($bookedType))
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();

				// $db    = $this->getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('email,fbid')
					->from($db->quoteName('#__beseated_event_ticket_booking_invite'))
					->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($bookingID));

				// Set the query and load the result.
				$db->setQuery($query);
				$splitedInvitedUserDetails = $db->loadObjectList();
			}
			else
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_ELEMENT_INVALID_BOOKED_TYPE'));
				IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

				return false;
			}

		}

		//echo "<pre/>";print_r($splitedInvitedUserDetails);exit;

		$fbIDss  = array();
		$emailss = array();

		$filterdEmail = array();
		$filterdFbIds = array();

		foreach ($splitedInvitedUserDetails as $key => $userDetails)
		{
			if($userDetails->email && !empty($emails))
			{
				$emailss[] = $userDetails->email;
			}
			else
			{
				$fbIDss[] = $userDetails->fbid;
			}
		}

		$uniqueEmails = array_unique($emailss);
		$uniquefbIds  = array_unique($fbIDss);
		$emails       = json_decode($emails);
		$fbIDs        = json_decode($fbIDs);

		$i1= 0;
		$i2= 0;

		if(!empty($emails))
		{
			for ($i = 0; $i < count($emails); $i++)
			{
				if(in_array(strtolower($emails[$i]->email), array_map('strtolower',$uniqueEmails)))
				{
					continue;
				}
				else
				{
					$filterdEmail[$i1]['username'] = $emails[$i]->username;
					$filterdEmail[$i1]['email'] = $emails[$i]->email;

					$i1++;
				}
			}

			$this->helper->array_sort_by_column($filterdEmail,'username');
		}
		elseif(!empty($fbIDs))
		{
			$registeredFBUser =  $this->getRegisteredFacebookUser($fbIDs);

			for ($i = 0; $i < count($fbIDs); $i++)
			{
				if(in_array($fbIDs[$i]->id, $uniquefbIds))
				{
					continue;
				}
				else
				{
					if(in_array($fbIDs[$i]->id, $registeredFBUser))
					{
						$filterdFbIds[$i2]['name'] = $fbIDs[$i]->name;
						$filterdFbIds[$i2]['id'] = $fbIDs[$i]->id;
						$i2++;
					}
				}
			}

			$this->helper->array_sort_by_column($filterdFbIds,'name');
		}



		if($emails)
		{
			$this->jsonarray['filterEmails'] = (count($filterdEmail)) ? $filterdEmail : array();
			$this->jsonarray['filterdFbIds'] = array();
		}
		else if ($fbIDs)
		{
			$this->jsonarray['filterEmails'] =  array();
			$this->jsonarray['filterdFbIds'] = (count($filterdFbIds)) ? $filterdFbIds : array();
		}
		else
		{
			$this->jsonarray['filterEmails'] = array();
			$this->jsonarray['filterdFbIds'] = array();
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;

	}

	function getRegisteredFacebookUser($fbids)
	{
		$fbIDs = array();

		foreach ($fbids as $key => $fbid)
		{
			$fbIDs[] = $fbid->id;
		}

		$fbIDsComma = implode(',', $fbIDs);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('fb_id')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('fb_id') . ' IN ('.$fbIDsComma.')')
			->where($db->quoteName('user_id') . ' IN ( SELECT `id` FROM `#__users` WHERE block=0)')
			->where($db->quoteName('user_type') . ' = '.$db->quote('beseated_guest'))
			->where($db->quoteName('is_deleted') . ' = '.$db->quote('0'))
			->order($db->quoteName('user_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$registeredFBUser = $db->loadColumn();

		return $registeredFBUser;


	}

	public 	function encrypt($plainText,$key)
	{
		$secretKey = $this->hextobin(md5($key));

		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);

		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');

		$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');

		$plainPad = $this->pkcs5_pad($plainText, $blockSize);
		if (mcrypt_generic_init($openMode, $secretKey, $initVector) != -1)
		{
			$encryptedText = mcrypt_generic($openMode, $plainPad);
			mcrypt_generic_deinit($openMode);
		}
		return bin2hex($encryptedText);

	}

	public function pkcs5_pad($plainText, $blockSize)
	{
		$pad = $blockSize - (strlen($plainText) % $blockSize);
		return $plainText . str_repeat(chr($pad), $pad);
	}

	public function hextobin($hexString)
	{
		$length = strlen($hexString);
		$binString="";
		$count=0;
		while($count<$length)
		{
			$subString =substr($hexString,$count,2);
			$packedString = pack("H*",$subString);
			if ($count==0)
			{
				$binString=$packedString;
			}
			else
			{
				$binString.=$packedString;
			}
			$count+=2;
		}
		return $binString;
	}

	public function convertCurrencyGoogle($amount = 1, $from, $to)
	{
		$url  = "https://www.google.com/finance/converter?a=$amount&from=$from&to=$to";
		$data = file_get_contents($url);
		preg_match("/<span class=bld>(.*)<\/span>/",$data, $converted);
		if(count($converted) == 0){
			return 1;
		}
		$converted = preg_replace("/[^0-9.]/", "", $converted[1]);

		return round($converted, 2);
	}

	function readElementBooking()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$elementType      = IJReq::getTaskData('elementType', '', 'string');
		$bookedType       = IJReq::getTaskData('bookedType', '', 'string');
		$elementBookingID = IJReq::getTaskData('elementBookingID', 0, 'int');

		if(!$elementBookingID || !$elementType || !$bookedType)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_BOOKING_DETAIL'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('element_read_id,from_user_id,to_user_id')
			->from($db->quoteName('#__beseated_element_read_booking'))
			->where($db->quoteName('element_type') . ' = ' . $db->quote(strtolower($elementType)))
			->where($db->quoteName('booked_type') . ' = ' . $db->quote(strtolower($bookedType)))
			->where($db->quoteName('booking_id') . ' = ' . $db->quote($elementBookingID));

		// Set the query and load the result.
		$db->setQuery($query);
		$elementBookingDetail = $db->loadObject();

		if(empty($elementBookingDetail))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_BOOKING_DETAIL'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$tblReadElementBooking = JTable::getInstance('ReadElementBooking', 'BeseatedTable');

		$tblReadElementBooking->load($elementBookingDetail->element_read_id);

		if($elementBookingDetail->to_user_id == $this->IJUserID)
		{
			$tblReadElementBooking->is_read_to_user = '1';
			$tblReadElementBooking->store();
		}
		else if ($elementBookingDetail->from_user_id == $this->IJUserID)
		{
			$tblReadElementBooking->is_read_from_user = '1';
			$tblReadElementBooking->store();
		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_USER'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;

	}

	function readElementRSVP()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$elementType      = IJReq::getTaskData('elementType', '', 'string');
		$bookedType       = IJReq::getTaskData('bookedType', '', 'string');
		$elementBookingID = IJReq::getTaskData('elementBookingID', 0, 'int');

		if(!$elementBookingID || !$elementType || !$bookedType)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_BOOKING_DETAIL'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('element_readID,from_user_id,to_user_id')
			->from($db->quoteName('#__beseated_element_read_rsvp'))
			->where($db->quoteName('element_type') . ' = ' . $db->quote(strtolower($elementType)))
			->where($db->quoteName('booked_type') . ' = ' . $db->quote(strtolower($bookedType)))
			->where($db->quoteName('booking_id') . ' = ' . $db->quote($elementBookingID));

		// Set the query and load the result.
		$db->setQuery($query);
		$elementBookingDetail = $db->loadObject();

		if(empty($elementBookingDetail))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_BOOKING_DETAIL'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$tblReadElementRsvp = JTable::getInstance('ReadElementRsvp', 'BeseatedTable');

		$tblReadElementRsvp->load($elementBookingDetail->element_readID);

		if($elementBookingDetail->to_user_id == $this->IJUserID)
		{
			$tblReadElementRsvp->is_read_to_user = '1';
			$tblReadElementRsvp->store();
		}
		else if ($elementBookingDetail->from_user_id == $this->IJUserID)
		{
			$tblReadElementRsvp->is_read_from_user = '1';
			$tblReadElementRsvp->store();
		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_USER'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;

	}

	function readMessage()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$messageID    = IJReq::getTaskData('messageID', 0, 'int');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$tblMessage = JTable::getInstance('Message', 'BeseatedTable');

		$tblMessage->load($messageID);

		if(!$tblMessage->message_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_MESSAGE_DETAIL'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$tblMessage->is_read = '1';
		$tblMessage->store();

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;

	}




}
