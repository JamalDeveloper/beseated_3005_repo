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
class venue
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

		$this->db                = JFactory::getDBO();
		$this->mainframe         =  JFactory::getApplication ();
		$this->IJUserID          = $this->mainframe->getUserState ( 'com_ijoomeradv.IJUserID', 0 ); //get login user id
		$this->my                = JFactory::getUser ( $this->IJUserID ); // set the login user object
		$this->helper            = new beseatedAppHelper;
		$this->emailHelper       = new BeseatedEmailHelper;
		$this->jsonarray         = array();

		$task        = IJReq::getExtTask();

		if($task == 'getNotification')
		{
			$this->helper->updateNotification('notifications');
		}
		if($task == 'getRSVP')
		{
			$this->helper->updateNotification('requests');
		}
		if($task == 'getBookings')
		{
			$this->helper->updateNotification('bookings');
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
	 * 	{"extName":"beseated","extView":"venue","extTask":"getVenues","taskData":{"query":"","city":"","latitude":"1","longitude":"1","pageNO":"0","friendsAttending":"1"}}
	 */
	function getVenues()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$searchQuery      = IJReq::getTaskData('query','','string');
		$city             = IJReq::getTaskData('city','','string');
		$latitude         = IJReq::getTaskData('latitude','','string');
		$longitude        = IJReq::getTaskData('longitude','','string');
		$friendsAttending = IJReq::getTaskData('friendsAttending',0,'int');
		$pageNO           = IJReq::getTaskData('pageNO',0);
		$pageLimit        = BESEATED_VENUE_LIST_LIMIT;

		if($pageNO==0 || $pageNO==1){
			$startFrom=0;
		}else{
			$startFrom = $pageLimit*($pageNO-1);
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		if(!empty($latitude) && !empty($longitude))
		{
			$query->select('*,( 3959 * acos( cos( radians('.$latitude.') )
	              * cos( radians( latitude ) )
	              * cos( radians( longitude ) - radians('.$longitude.') )
	              + sin( radians('.$latitude.') )
	              * sin( radians( latitude ) ) ) ) AS distance');
		}
		else
		{
			$query->select('*');
		}

		$query->from($db->quoteName('#__beseated_venue'))
			->where($db->quoteName('has_table') . ' = ' . $db->quote('1'))
			->where($db->quoteName('published') . ' = ' . $db->quote('1'))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'));
			//->order($db->quoteName('venue_name') . ' ASC');

		if($this->IJUserID)
		{
			$venueIDs = $this->helper->getBlackListedElementOfUser($this->IJUserID,'Venue');
			if(count($venueIDs) != 0)
			{
				$query->where($db->quoteName('venue_id') .' NOT IN ('.implode(",", $venueIDs).')');
			}
		}

		if($friendsAttending && $this->IJUserID)
		{
			$statusArray = array();
			//$statusArray[] = $this->helper->getStatusID('booked');
			$statusArray[] = $this->helper->getStatusID('confirmed');
			$guestUserDetail = $this->helper->guestUserDetail($this->IJUserID);
			if(empty($guestUserDetail->fb_friends_id))
			{
				IJReq::setResponseCode(204);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_NOT_FOUND'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
			$filterFbFrndEmails = BeseatedHelper::filterFbIdsToUserIDs($guestUserDetail->fb_friends_id);

			if(count($filterFbFrndEmails['guest']) == 0)
			{
				IJReq::setResponseCode(204);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_NOT_FOUND'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$invitedBookngIDs = $this->helper->getBookingIDForInvited();

			//echo "<pre>";print_r(implode(',', invitedBookngIDs));echo "</pre>";exit;

			$userIDs = implode(",", $filterFbFrndEmails['guest']);
			$sqlFAV             = $db->getQuery(true);
			$firstDate          = date('Y-m-d');
			$date               = strtotime("+7 day");
			$lastDate           =  date('Y-m-d', $date);

			$sqlFAV->select('venue_id')
				->from($db->quoteName('#__beseated_venue_table_booking'))
				->where($db->quoteName('user_id') . ' IN ('.$userIDs.')' )
				->where($db->quoteName('user_status') . ' IN ('.implode(",", $statusArray).')')
				->where($db->quoteName('deleted_by_user') . ' = ' . $db->quote(0))
				->where($db->quoteName('booking_date') . ' >= ' . $db->quote($firstDate))
				->where($db->quoteName('booking_date') . ' <= ' . $db->quote($lastDate));

				if(!empty($invitedBookngIDs))
				{
					$sqlFAV->where($db->quoteName('venue_table_booking_id') . ' NOT IN ('.implode(",", $invitedBookngIDs).')');
				}

				$sqlFAV->order($db->quoteName('booking_date') . ' ASC');

			$venueIDs = $this->helper->getBlackListedElementOfUser($this->IJUserID,'Venue');
			if(count($venueIDs) != 0)
			{
				$sqlFAV->where($db->quoteName('venue_id') .' NOT IN ('.implode(",", $venueIDs).')');
			}


			$db->setQuery($sqlFAV);
			$friendsAttendingVenues = $db->loadColumn();

			if(count($friendsAttendingVenues) == 0)
			{
				IJReq::setResponseCode(204);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_NOT_FOUND'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$query->where($db->quoteName('venue_id') .' IN ('.implode(",", $friendsAttendingVenues).')');
		}

		/*if($venueID)
		{
			$query->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID));
		}*/

		if(!empty($city))
		{
			$query->where(
				'('.
					$db->quoteName('location') .' LIKE ' . $db->quote('%'.$city.'%'). ' OR '.
					$db->quoteName('city') .' LIKE ' . $db->quote('%'.$city.'%').
				')'
			);
		}

		if(!empty($searchQuery))
		{
			$query->where($db->quoteName('venue_name') .' LIKE ' . $db->quote('%'.$searchQuery.'%'));
		}

		if(!empty($latitude) && !empty($longitude))
		{
			$sqlString = $query;
			//$sqlString .= ' GROUP BY venue_id HAVING distance<'.COM_IJOOMERADV_BESEATED_RADIOUS;
			$sqlString .= ' GROUP BY venue_id HAVING distance<50';
			$sqlString .= ' ORDER BY venue_name ASC';
			$query = $sqlString;

		}
		else
		{
			$query->order($db->quoteName('venue_name') . ' ASC');
		}

		/*echo "<pre>";
		print_r($query);
		echo "</pre>";
		exit;*/

		$db->setQuery($query,$startFrom,$pageLimit);
		$resVenues = $db->loadObjectList();

		if(count($resVenues) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$resultVenues =  array();
		$resultVenueIDs = array();
		foreach ($resVenues as $key => $venue)
		{
			$temp = array();
			$resultVenueIDs[]  = $venue->venue_id;
			$temp['venueID']   = $venue->venue_id;
			$temp['venueName'] = $venue->venue_name;
			$temp['location']  = $venue->location;
			$temp['city']      = $venue->city;
			$temp['ratting']   = $venue->avg_ratting;
			$temp['latitude']  = $venue->latitude;
			$temp['longitude'] = $venue->longitude;
			$temp['venueType'] = $venue->venue_type;
			$temp['dayClub']   = $venue->is_day_club;
			$resultVenues[]    = $temp;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('element_id') . ' IN ('.implode(',', $resultVenueIDs).')')
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Venue'))
			->order($db->quoteName('image_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$resultVenueImages = $db->loadObjectList();
		$allVenueImages    = array();
		$corePath          = JUri::base().'images/beseated/';
		foreach ($resultVenueImages as $key => $venueImage)
		{
			$tempImg               = array();

			if($venueImage->is_video)
			{
				$tempImg['thumbImage'] = ($venueImage->thumb_image)?$corePath.$venueImage->thumb_image:"";
			}
			else
			{
				$tempImg['thumbImage'] = ($venueImage->image)?$corePath.$venueImage->image:"";
			}
			//$tempImg['thumbImage'] = ($venueImage->thumb_image)?$corePath.$venueImage->thumb_image:'';
			$tempImg['image']      = ($venueImage->image)?$corePath.$venueImage->image:'';
			$tempImg['isVideo']    = $venueImage->is_video;
			$tempImg['isDefault']  = $venueImage->is_default;
			$allVenueImages[$venueImage->element_id][] = $tempImg;
			//$allVenueImages[$venueImage->element_id][$key]['thumb_image'] = ($venueImage->thumb_image)?$corePath.$venueImage->thumb_image:'';
			//$allVenueImages[$venueImage->element_id][$key]['image']       = ($venueImage->image)?$corePath.$venueImage->image:'';
		}

		foreach ($resultVenues as $key => $venue)
		{
			if(isset($allVenueImages[$venue['venueID']]))
			{
				$resultVenues[$key]['images'] = $allVenueImages[$venue['venueID']];
			}
			else
			{
				$resultVenues[$key]['images'] = array();
			}
		}

		if($venueID)
		{
			$resultVenues = $resultVenues[0];
		}

		$this->jsonarray['code']        = 200;
		$this->jsonarray['totalInPage'] = count($resultVenues);
		$this->jsonarray['pageLimit']   = BESEATED_VENUE_LIST_LIMIT;
		$this->jsonarray['venues']      = $resultVenues;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"getVenueDetail","taskData":{"venueID":"1"}}
	 */
	function getVenueDetail()
	{
		$venueID   = IJReq::getTaskData('venueID','','string');
		if(!$venueID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$defaultImage = JUri::base().'images/beseated/elementDefaultimage.png';

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__beseated_venue'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID));

		$db->setQuery($query,0,1);
		$resVenues = $db->loadObjectList();
		if(count($resVenues) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$userType = $this->helper->getUserType($this->IJUserID);
		if($userType == 'Guest')
		{
			$favouritIDs = $this->helper->getUserFavourites($this->IJUserID,'Venue');
		}

		$resultVenues =  array();
		$resultVenueIDs = array();

		foreach ($resVenues as $key => $venue)
		{
			$temp                 = array();
			$resultVenueIDs[]     = $venue->venue_id;
			$temp['venueID']      = $venue->venue_id;
			$temp['venueName']    = $venue->venue_name;
			$temp['venueDesc']    = $venue->description;
			$temp['venueType']    = $venue->venue_type;
			$temp['hasGuestList'] = $venue->has_guestlist;
			$temp['currencyCode'] = $venue->currency_code;
			$temp['workingDays']  = $venue->working_days;
			$temp['music']        = $venue->music;
			$temp['atmosphere']   = $venue->atmosphere;
			$temp['location']     = $venue->location;
			$temp['location']     = $venue->location;
			$temp['city']         = $venue->city;
			$temp['ratting']      = $venue->avg_ratting;
			$temp['latitude']     = $venue->latitude;
			$temp['longitude']    = $venue->longitude;
			$temp['venueType']    = $venue->venue_type;
			$temp['dayClub']      = $venue->is_day_club;

			if($userType == 'Guest' && in_array($venue->venue_id, $favouritIDs))
			{
				$temp['isFavourite'] = 1;
			}
			else if($userType == 'Guest')
			{
				$temp['isFavourite'] = 0;
			}
			$resultVenues[]    = $temp;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('element_id') . ' IN ('.implode(',', $resultVenueIDs).')')
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Venue'))
			->order($db->quoteName('image_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$resultVenueImages = $db->loadObjectList();

		$this->helper->array_sort_by_column($resultVenueImages,'is_default',3);

		$allVenueImages    = array();
		$corePath          = JUri::base().'images/beseated/';
		foreach ($resultVenueImages as $key => $venueImage)
		{
			$tempImg = array();

			if($venueImage->is_video)
			{
				$tempImg['thumb_image'] = ($venueImage->thumb_image)?$corePath.$venueImage->thumb_image:$defaultImage;
			}
			else
			{
				$tempImg['thumb_image'] = ($venueImage->image)?$corePath.$venueImage->image:$defaultImage;
			}

			$tempImg['image']       = ($venueImage->image)?$corePath.$venueImage->image:$defaultImage;
			$tempImg['isVideo']    = $venueImage->is_video;
			$tempImg['isDefault']  = $venueImage->is_default;
			$allVenueImages[$venueImage->element_id][] = $tempImg;
		}

		$resultVenues[$key]['images'] = array();

		foreach ($resultVenues as $key => $venue)
		{
			if(isset($allVenueImages[$venue['venueID']]))
			{
				$resultVenues[$key]['images'] = $allVenueImages[$venue['venueID']];
			}
			else
			{
				$resultVenues[$key]['images'] = array();
				$resultVenues[$key]['images'][$key]['thumbImage']    = $defaultImage;
				$resultVenues[$key]['images'][$key]['image']         = $defaultImage;
				$resultVenues[$key]['images'][$key]['isVideo']       = "0";
				$resultVenues[$key]['images'][$key]['isDefault']     = "1";
			}
		}

		//echo "<pre/>";print_r($resultVenues);exit;
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('vt.*')
			->from($db->quoteName('#__beseated_venue_table','vt'))
			->where($db->quoteName('vt.venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('vt.published') . ' = ' . $db->quote(1))
			->order($db->quoteName('vt.min_price') . ' ASC');

		$query->select('vpt.premium_table_name')
			->join('LEFT','#__beseated_venue_premium_table AS vpt ON vpt.premium_id=vt.premium_table_id');

		// Set the query and load the result.
		$db->setQuery($query);

		$resTables = $db->loadObjectList();
		$resultTables = array();
		foreach ($resTables as $key => $table)
		{
			$temp                   = array();
			$temp['tableID']        = $table->table_id;
			$temp['venueID']        = $table->venue_id;
			$temp['tableName']      = $table->table_name;
			$temp['premiumTableID'] = $table->premium_table_id;
			$temp['tableType']      = ($table->premium_table_name)?$table->premium_table_name:'';
			$temp['minSpend']       = $this->helper->currencyFormat('',$table->min_price);
			$temp['capacity']       = $table->capacity;
			$temp['thumbImage']     = ($table->thumb_image)?$corePath.$table->thumb_image:'';
			$temp['image']          = ($table->image)?$corePath.$table->image:'';
			$resultTables[]         = $temp;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_bottle'))
			->where($db->quoteName('published') . ' = ' . $db->quote(1))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->order($db->quoteName('brand_name') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$resBottles    = $db->loadObjectList();
		$resultBottles = array();

		foreach ($resBottles as $key => $bottle)
		{
			$temp               = array();
			$temp['bottleID']   = $bottle->bottle_id;
			$temp['venueID']    = $bottle->venue_id;
			$temp['brandName']  = $bottle->brand_name;
			$temp['size']       = $bottle->size;
			$temp['bottleType'] = $bottle->bottle_type;
			$temp['price']      = $this->helper->currencyFormat('',$bottle->price);
			$temp['thumbImage'] = ($bottle->image)?$corePath.$bottle->thumb_image:'';
			$temp['image']      = ($bottle->image)?$corePath.$bottle->image:'';
			$resultBottles[]    = $temp;
		}

		if($venueID)
		{
			$resultVenues            = $resultVenues[0];
			$resultVenues['tables']  = $resultTables;
			$resultVenues['bottles'] = $resultBottles;
		}

		$queryRatings = $db->getQuery(true);

		// Create the base select statement.
		$queryRatings->select('r.rating_id,r.user_id,r.avg_rating,r.food_rating,r.service_rating,r.atmosphere_rating,r.value_rating,r.rating_count,r.rating_comment,r.created')
			->from($db->quoteName('#__beseated_rating','r'))
			->where($db->quoteName('r.element_id') . ' = '.$db->quote($venueID))
			->where($db->quoteName('r.element_type') . ' = ' . $db->quote('venue'))
			->order($db->quoteName('r.time_stamp') . ' ASC');

		$queryRatings->select('bu.full_name,bu.avatar,bu.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=r.user_id');

		// Set the query and load the result.
		$db->setQuery($queryRatings);
		$resRatings = $db->loadObjectList();
		$allRatings    = array();
		foreach ($resRatings as $key => $rating)
		{
			$tempRating                     = array();
			$tempRating['comment']          = $rating->rating_comment;
			$tempRating['avgrating']        = $rating->avg_rating;
			$tempRating['foodRating']       = $rating->food_rating;
			$tempRating['serviceRating']    = $rating->service_rating;
			$tempRating['atmosphereRating'] = $rating->atmosphere_rating;
			$tempRating['valueRating']      = $rating->value_rating;
			$tempRating['created']          = $this->helper->convertDateFormat($rating->created);
			$tempRating['avatar']           = ($rating->avatar)?$this->helper->getUserAvatar($rating->avatar):'';
			$tempRating['thumbAvatar']      = ($rating->thumb_avatar)?$this->helper->getUserAvatar($rating->thumb_avatar):'';
			$tempRating['fullName']         = $rating->full_name;
			$allRatings[] = $tempRating;
		}

		$resultVenues['ratings'] = $allRatings;

		if($userType == 'Guest'){
			$resultVenues['biggestSpenders'] = $this->helper->getBiggestSpender($venueID);
		}

		$this->jsonarray['code']      = 200;
		$this->jsonarray['venueDetail']    = $resultVenues;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"getVenueBottle","taskData":{"venueID":"1"}}
	 */
	function getVenueBottle()
	{
		$venueID  = IJReq::getTaskData('venueID','','string');
		$corePath = JUri::base().'images/beseated/';
		if(!$venueID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_bottle'))
			->where($db->quoteName('published') . ' = ' . $db->quote(1))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->order($db->quoteName('brand_name') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$resBottles    = $db->loadObjectList();
		$resultBottles = array();
		$bottleTypes   = array();

		foreach ($resBottles as $key => $bottle)
		{
			$temp               = array();
			$temp['bottleID']   = $bottle->bottle_id;
			$temp['venueID']    = $bottle->venue_id;
			$temp['brandName']  = $bottle->brand_name;
			$temp['size']       = $bottle->size;
			$temp['bottleType'] = $bottle->bottle_type;
			$bottleTypes[]      = $bottle->bottle_type;
			$temp['price']      = $this->helper->currencyFormat('',$bottle->price);
			$temp['quantity']   = "0";
			$temp['thumbImage'] = ($bottle->image)?$corePath.$bottle->thumb_image:'';
			$temp['image']      = ($bottle->image)?$corePath.$bottle->image:'';
			$resultBottles[]    = $temp;
		}

		if(count($resultBottles) == 0){
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_BOTTLES_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		sort($bottleTypes);

		//echo "<pre/>";print_r($bottleTypes);exit;

		$this->jsonarray['code'] = 200;
		$this->jsonarray['bottleTypes'] =  array_values(array_unique($bottleTypes));
		$this->jsonarray['bottles'] = $resultBottles;
		return $this->jsonarray;

	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"venue","extTask":"getPremiumTables","taskData":{}}
	 *
	 */
	function getPremiumTables()
	{

		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_premium_table'))
			->where($db->quoteName('published') . ' = ' . $db->quote('1'))
			->order($db->quoteName('premium_table_name') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$resultPremiumTables = $db->loadObjectList();
		if(count($resultPremiumTables) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_VENUE_PREMIUM_TABLE_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$premiumTables = array();
		foreach ($resultPremiumTables as $key => $value)
		{
			$temp = array();
			$temp['premiumTableID'] = $value->premium_id;
			$temp['premiumTableName'] = $value->premium_table_name;
			$premiumTables[] = $temp;
		}

		$this->jsonarray['code'] = 200;
		$this->jsonarray['premiumTables'] = $premiumTables;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"addVenueTables","taskData":{"tableID":"number","tableName":"string","premiumTableID":"string","minSpend":"string","capacity":"number","venueID":"string"}}
	 *
	 */
	function addVenueTables()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$tableID        = IJReq::getTaskData('tableID',0,'int');
		$tableName      = IJReq::getTaskData('tableName','','string');
		$minSpend       = IJReq::getTaskData('minSpend',0,'int');
		$premiumTableID = IJReq::getTaskData('premiumTableID',0,'int');
		$capacity       = IJReq::getTaskData('capacity',0,'int');
		$venueID        = IJReq::getTaskData('venueID',0,'int');

		if(!$venueID || !$minSpend || !$capacity)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_VENUE_TABLE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		if(empty($tableName) && !$premiumTableID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_VENUE_TABLE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}


		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblTable = JTable::getInstance('Table', 'BeseatedTable');
		$tblTable->load($tableID);

		$data['venue_id']         = $venueID;
		$data['table_name']       = $tableName;
		$data['premium_table_id'] = $premiumTableID;
		$data['user_id']          = $this->IJUserID;
		$data['min_price']        = $minSpend;
		$data['capacity']         = $capacity;
		$data['published']        = 1;
		$data['time_stamp']       = time();


		$file = JRequest::getVar('image','','FILES','array');

		if(is_array($file) && isset($file['size']) && $file['size']>0)
		{
			$defualtPath = JPATH_ROOT . '/images/beseated/';
			$tableImage = $this->helper->uplaodServiceImage($file,'Venue',$venueID);

			if(!empty($tableImage))
			{
				if(!empty($tblTable->image) && file_exists($defualtPath.$tblTable->image))
				{
					unlink($defualtPath.$tblTable->image);
				}

				if(!JFolder::exists($defualtPath.'Venue/'.$venueID.'/Tables/thumb'))
				{
					JFolder::create($defualtPath.'Venue/'.$venueID .'/Tables/thumb');
				}

				$pathInfo            = pathinfo($defualtPath.$tableImage);
				$thumbPath           =  $pathInfo['dirname'].'/thumb/thumb_'.$pathInfo['basename'];
				$storeThumbPath      = 'Venue/'. $venueID . '/Tables/thumb/thumb_'.$pathInfo['basename'];
				$this->helper->createThumb($defualtPath.$tableImage,$thumbPath);
				$data['image']       = $tableImage;
				$data['thumb_image'] = $storeThumbPath;

				if(!empty($tblTable->thumb_image) && file_exists($defualtPath.$tblTable->thumb_image))
				{
					unlink($defualtPath.$tblTable->thumb_image);
				}
			}
		}

		$tblTable->bind($data);

		if(!$tblTable->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$tblVenue = JTable::getInstance('Venue','BeseatedTable');
		$tblVenue->load($venueID);
		$tblVenue->has_table = 1;
		$tblVenue->store();

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"addVenueBottles","taskData":{"bottleID":"1","venueID":"1","brandName":"Bottle One","size":"1 Litter","bottleType":"1 Litter","price":"150"}}
	 */
	function addVenueBottles()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bottleID   = IJReq::getTaskData('bottleID',0,'int');
		$venueID    = IJReq::getTaskData('venueID',0,'int');
		$brandName  = IJReq::getTaskData('brandName','','string');
		$size       = IJReq::getTaskData('size','','string');
		$price      = IJReq::getTaskData('price',0,'int');
		$bottleType = IJReq::getTaskData('bottleType','','string');

		if(!$venueID || empty($brandName) || !$price)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_VENUE_BOTTLE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblBottle = JTable::getInstance('Bottle', 'BeseatedTable');
		$tblBottle->load($bottleID);

		$data['venue_id']    = $venueID;
		$data['brand_name']  = $brandName;
		$data['size']        = $size;
		$data['price']       = $price;
		$data['bottle_type'] = $bottleType;
		$data['published']   = 1;
		$data['time_stamp']  = time();

		$file = JRequest::getVar('image','','FILES','array');
		if(is_array($file) && isset($file['size']) && $file['size']>0)
		{
			$defualtPath = JPATH_ROOT . '/images/beseated/';
			$tableImage = $this->helper->uplaodServiceImage($file,'Venue',$venueID);
			if(!empty($tableImage))
			{
				if(!empty($tblBottle->image) && file_exists($defualtPath.$tblBottle->image))
				{
					unlink($defualtPath.$tblBottle->image);
				}

				if(!JFolder::exists($defualtPath.'Venue/'.$venueID.'/Tables/thumb'))
				{
					JFolder::create($defualtPath.'Venue/'.$venueID .'/Tables/thumb');
				}

				$pathInfo            = pathinfo($defualtPath.$tableImage);
				$thumbPath           =  $pathInfo['dirname'].'/thumb/thumb_'.$pathInfo['basename'];
				$storeThumbPath      = 'Venue/'. $venueID . '/Tables/thumb/thumb_'.$pathInfo['basename'];
				$this->helper->createThumb($defualtPath.$tableImage,$thumbPath);
				$data['image']       = $tableImage;
				$data['thumb_image'] = $storeThumbPath;

				if(!empty($tblBottle->thumb_image) && file_exists($defualtPath.$tblBottle->thumb_image))
				{
					unlink($defualtPath.$tblBottle->thumb_image);
				}
			}
		}

		$tblBottle->bind($data);

		if(!$tblBottle->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}


		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"deleteTable","taskData":{"tableID":"0","venueID":"1"}}
	 *
	 */
	function deleteTable()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$tableID   = IJReq::getTaskData('tableID',0,'int');
		$venueID = IJReq::getTaskData('venueID',0,'int');
		$elementID   = $venueID;

		if(!$venueID || !$tableID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_VENUE_TABLE_DELETE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblTable = JTable::getInstance('Table', 'BeseatedTable');
		$tblTable->load($tableID);

		if(!$tblTable->table_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_VENUE_TABLE_DELETE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$tblTable->published = 0;

		if(!$tblTable->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$this->helper->checkForActiveSubElement($elementID, 'Venue');

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"deleteBottle","taskData":{"bottleID":"0","venueID":"1"}}
	 *
	 */
	function deleteBottle()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bottleID   = IJReq::getTaskData('bottleID',0,'int');
		$venueID = IJReq::getTaskData('venueID',0,'int');
		$elementID   = $venueID;

		if(!$venueID || !$bottleID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_VENUE_BOTTLE_DELETE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblBottle = JTable::getInstance('Bottle', 'BeseatedTable');
		$tblBottle->load($bottleID);

		if(!$tblBottle->bottle_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_VENUE_BOTTLE_DELETE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$tblBottle->published = 0;

		if(!$tblBottle->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		//$this->helper->checkForActiveSubElement($elementID, 'Venue');

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"getNotification","taskData":{"pageNO":"0"}}
	 *
	 */
	function getNotification()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition


		$pageNO    = IJReq::getTaskData('pageNO',0);
		$pageLimit = BESEATED_GENERAL_LIST_LIMIT;
		if($pageNO==0 || $pageNO==1){
			$startFrom=0;
		}else{
			$startFrom = $pageLimit*($pageNO-1);
		}


		$protection = $this->helper->protectionUserDetail($this->IJUserID);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_notification'))
			->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
			->order($db->quoteName('notification_id') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);

		$resNotifi = $db->loadObjectList();

		if(count($resNotifi) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_NOTIFICATION_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$resultNotifications = array();
		foreach ($resNotifi as $key => $notification)
		{
			$temp                     = array();
			$temp['id']               = $notification->booking_id;
			$temp['notificationID']   = $notification->notification_id;
			$temp['title']            = $notification->title;
			$temp['isRead']           = $notification->is_read;
			$temp['notificationType'] = $notification->notification_type;
			$actorDetail              = $this->helper->guestUserDetail($notification->actor);
			$temp['avatar']           = ($actorDetail->avatar)?$this->helper->getUserAvatar($actorDetail->avatar):'';
			$temp['thumbAvatar']      = ($actorDetail->thumb_avatar)?$this->helper->getUserAvatar($actorDetail->thumb_avatar):'';
            $temp['fbid']             = ($actorDetail->is_fb_user == '1' && !empty($actorDetail->fb_id)) ? $actorDetail->fb_id : '';
            $temp['timeStamp']        = $notification->time_stamp;
            $temp['userType']         = "";
			$temp['params'] = json_decode($notification->extra_pramas);

			$resultNotifications[] = $temp;
		}

		if(count($resultNotifications) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_NOTIFICATION_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray['code']              = 200;
		$this->jsonarray['notificationCount'] = count($resultNotifications);
		$this->jsonarray['notification']      = $resultNotifications;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"contact","taskData":{"subject":"Testing Subject","message":"This is Testing Message from protectin manager"}}
	 *
	 */
	function contact()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();
		$subject        = IJReq::getTaskData('subject','', 'string');
		$message        = IJReq::getTaskData('message','', 'string');

		if(empty($subject) || empty($message))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_CONTACT_MESSAGE_DETAIL_INVALID'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//$response = $this->helper->sendEmail($beseatedParams->contact_email,$subject,$message);
		$response   = true;
		if(!$response)
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}
		$venueDetail = $this->helper->venueUserDetail($this->IJUserID);
		$userID           = $venueDetail->user_id;
		$elementID        = $venueDetail->venue_id;
		$elementType      = 'venue';
		$this->helper->storeContactRequest($userID,$elementID,$elementType,$subject,$message);
		$this->emailHelper->contactAdmin($subject, $message);
		$this->emailHelper->contactThankYouEmail();

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}
	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"promotion","taskData":{"subject":"Testing Subject","message":"This is Testing Message from protectin manager"}}
	 *
	 */
	function promotion()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		require_once JPATH_SITE.'/components/com_beseated/helpers/email.php';
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

	    $this->emailHelper =  new BeseatedEmailHelper;

		$beseatedParams = BeseatedHelper::getExtensionParam();
		$subject        = IJReq::getTaskData('subject','', 'string');
		$message        = IJReq::getTaskData('message','', 'string');

		$userDetail = $this->helper->guestUserDetail($this->IJUserID);

		if(empty($subject) || empty($message))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_CONTACT_MESSAGE_DETAIL_INVALID'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(empty($userDetail->city) || empty($userDetail->location))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_EMPTY_USER_CITY'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$venueDetail = $this->helper->venueUserDetail($this->IJUserID);

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('user_id')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('user_id') . ' != ' . $db->quote($this->IJUserID))
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
			$connectionID = $this->helper->getConnectionID($this->IJUserID,$user_id);

			if($connectionID)
			{
				$tblMessage = JTable::getInstance('Message','BeseatedTable',array());
				$tblMessage->load(0);
				$msgData = array();
				$msgData['connection_id'] = $connectionID;
				$msgData['from_user_id']  = $this->IJUserID;
				$msgData['to_user_id']    = $user_id;
				$msgData['message_type']  = 'venuePromotion';
				$msgData['message_body']  = $subject."\n".$message;
				$msgData['extra_params']  = "";
				$msgData['time_stamp']    = time();

				$elementType                = "Venue";
				$elementID                  = $venueDetail->venue_id;
				$cid                        = $venueDetail->venue_id;
				$extraParams                = array();
				$extraParams["venueID"]     = $cid;
				$notificationType           = "venue.promotion.message";
				$title                      = JText::sprintf(
												'COM_BESEATED_NOTIFICATION_VENUE_PROMOTION_MESSAGE_TO_USER',
												$venueDetail->venue_name);

				$tblMessage->bind($msgData);
				if($tblMessage->store())
				{
					//$this->helper->storeNotification($this->IJUserID,$user_id,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);

					// add this user id to send push notification;
					$push_user_ids[] = $user_id;

					$guestDetail = JFactory::getUser($user_id);

					$this->emailHelper->userNewMessageMail($guestDetail->name,$venueDetail->venue_name,$message,$subject,$guestDetail->email);
				}

			}
		}

		//$response = $this->helper->sendEmail($beseatedParams->contact_email,$subject,$message);
		$response   = true;

		if(!$response)
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		//$this->emailHelper->contactAdmin($subject, $message);
		//$this->emailHelper->contactThankYouEmail();

		$userID          = $venueDetail->user_id;
		$elementID       = $venueDetail->venue_id;
		$elementType     = 'venue';
		$this->helper->storePromotionRequest($userID,$elementID,$elementType,$subject,$message,$userDetail->city,count($user_ids));

		$this->jsonarray['code'] = 200;

		$this->jsonarray['pushNotificationData']['id']         = '1';
		$this->jsonarray['pushNotificationData']['to']         = implode(',', $push_user_ids);
		$this->jsonarray['pushNotificationData']['message']    = $message;
		//$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_VENUE_PROMOTION_MSG_RECEIVED');
		$this->jsonarray['pushNotificationData']['type']       = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype'] = '';

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"bookVenueTable","taskData":{"tableID":"1","venueID":"1","bookingDate":"2015-11-20","bookingTime":"20:00","maleGuest":"5","femaleGuest":"2","privacy":"1","passkey":"1"}}
	 *
	 */
	function bookVenueTable()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$beseatedParams  = BeseatedHelper::getExtensionParam();
		$tableID         = IJReq::getTaskData('tableID','', 'string');
		$venueID         = IJReq::getTaskData('venueID','', 'string');
		$bookingDate     = IJReq::getTaskData('bookingDate','','string');
		$bookingTime     = IJReq::getTaskData('bookingTime','','string');
		$maleGuest       = IJReq::getTaskData('maleGuest',0,'int');
		$femaleGuest     = IJReq::getTaskData('femaleGuest',0,'int');
		$privacy         = IJReq::getTaskData('privacy',0,'int');
		$passkey         = IJReq::getTaskData('passkey',0,'int');
		$totalHours      = IJReq::getTaskData('totalHours',$beseatedParams->table_booking_hours,'int');
		$totalGuest      = $maleGuest + $femaleGuest;
		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblTable        = JTable::getInstance('Table', 'BeseatedTable');
		$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');
		$date            = date('d F Y',strtotime($bookingDate));

		$tblVenue->load($venueID);
		$tblTable->load($tableID);

		if($tblVenue->venue_id != $tblTable->venue_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_SERVICE_OR_COMPANY_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!$totalGuest)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_GUEST_COUNT_IN_BOOKING_REQUEST'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}



		if(!$this->helper->isTime($bookingTime) && $tblVenue->is_day_club)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_INVALID_TIME_FOR_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}



		if(!$tblVenue->is_day_club)
		{
			$tmpBookingTime = $this->helper->convertToHMS('23:59:00');
			$bookingTime = $this->helper->convertToHMS('00:01:01');
			$totalHours = 23;
		}

		if($this->helper->isPastDate($bookingDate.' '.$tmpBookingTime) && !$tblVenue->is_day_club)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_BOOKING_DATE_NOT_VALID_FOR_NIGHT_CLUB'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}
		else if($this->helper->isPastDate($bookingDate))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_BOOKING_DATE_NOT_VALID_FOR_DAY_CLUB'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$workingDays = $tblVenue->working_days;
		if(empty($workingDays)){
			IJReq::setResponseCode(901);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_WORKING_DAYS_NOT_SET'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$workingDaysArray = explode(",", $workingDays);
		$dayNum = date('N', strtotime($this->helper->convertToYYYYMMDD($bookingDate)));
		if(!in_array($dayNum, $workingDaysArray)){
			IJReq::setResponseCode(902);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_CLOSED_ON_DATE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$isTableAvailable = $this->helper->checkForVenueTableAvaibility($venueID,$tableID,$tblVenue->is_day_club,$bookingDate,$bookingTime,'');

		if(!$isTableAvailable)
		{
			IJReq::setResponseCode(706);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_TABLE_IS_ALREADY_BOOKED_FOR_THIS_DATE_AND_TIME'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($passkey){
			$passkey = mt_rand(1000, 9999);
		}else{
			$passkey = "";
		}

		$venueBookingPost                          = array();
		$venueBookingPost['venue_id']              = $tblVenue->venue_id;
		$venueBookingPost['table_id']              = $tblTable->table_id;
		$venueBookingPost['user_id']               = $this->IJUserID;
		$venueBookingPost['booking_date']          = $this->helper->convertToYYYYMMDD($bookingDate);
		$venueBookingPost['booking_time']          = $this->helper->convertToHMS($bookingTime);
		$venueBookingPost['privacy']               = $privacy;
		$venueBookingPost['passkey']               = $passkey;
		$venueBookingPost['total_guest']           = $totalGuest;
		$venueBookingPost['male_guest']            = $maleGuest;
		$venueBookingPost['female_guest']          = $femaleGuest;
		$venueBookingPost['total_hours']           = $totalHours;
		$venueBookingPost['total_price']           = $tblTable->min_price;
		$venueBookingPost['user_status']           = $this->helper->getStatusID('pending');
		$venueBookingPost['venue_status']          = $this->helper->getStatusID('request');
		$venueBookingPost['booking_currency_code'] = $tblVenue->currency_code;
		$venueBookingPost['booking_currency_sign'] = $tblVenue->currency_sign;
		$venueBookingPost['request_date_time']     = date('Y-m-d H:i:s');
		$venueBookingPost['time_stamp']            = time();

		$tblVenueBooking->load(0);
		$tblVenueBooking->bind($venueBookingPost);

		if(!$tblVenueBooking->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$tblReadElementRsvp = JTable::getInstance('ReadElementRsvp', 'BeseatedTable');
		$tblReadElementRsvp->load(0);
		$tblReadElementRsvp->booked_type  = 'booking';
		$tblReadElementRsvp->element_type = 'venue';
		$tblReadElementRsvp->booking_id   = $tblVenueBooking->venue_table_booking_id;
		$tblReadElementRsvp->from_user_id = $tblVenue->user_id;
		$tblReadElementRsvp->to_user_id   = $this->IJUserID;
		$tblReadElementRsvp->store();

		$userDetail       = $this->helper->guestUserDetail($this->IJUserID);
		$actor            = $this->IJUserID;
		$target           = $tblVenue->user_id;
		$elementID        = $tblVenue->venue_id;
		$elementType      = "Venue";
		$notificationType = "table.request";
		if($tblVenue->is_day_club){
			$formatedBookingTime = $this->helper->convertToHM($bookingTime);
			$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_TO_DAY_VENUE',
								$userDetail->full_name,
								$tblTable->table_name,
								$this->helper->convertDateFormat($bookingDate),
								$formatedBookingTime
							);
		}else{
			$formatedBookingTime = "-";
			$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_TO_NIGHT_VENUE',
								$userDetail->full_name,
								$tblTable->table_name,
								$this->helper->convertDateFormat($bookingDate)
							);
		}

		$cid              = $tblVenueBooking->venue_table_booking_id;
		$extraParams      = array();
		$extraParams["venueTableBookingID"] = $tblVenueBooking->venue_table_booking_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$cid))
		{
			$venueDefaultImage = $this->helper->getElementDefaultImage($tblVenue->venue_id,'Venue');
			$venueThumb = Juri::base().'images/beseated/'.$venueDefaultImage->thumb_image;
			$bookingDate = date('d F Y',strtotime($bookingDate));
			$companyDetail = JFactory::getUser($tblVenue->user_id);

			$this->emailHelper->venueBookingRequestUserMail($tblVenue->venue_name,$venueThumb,$bookingDate,$formatedBookingTime,$tblTable->table_name,$tblVenue->currency_code,number_format($tblVenueBooking->total_price,0),$maleGuest,$femaleGuest,$userDetail->full_name,$userDetail->email,$userDetail->email);
			$this->emailHelper->venueBookingRequestManagerMail($tblVenue->venue_name,$venueThumb,$bookingDate,$formatedBookingTime,$tblTable->table_name,$tblVenue->currency_code,number_format($tblVenueBooking->total_price,0),$maleGuest,$femaleGuest,$userDetail->full_name,$userDetail->email,$companyDetail->email);
		}

		$this->jsonarray['code'] = 200;
		$this->jsonarray['pushNotificationData']['id']          = $tblVenueBooking->venue_table_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		$this->jsonarray['pushNotificationData']['type']        = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype']  = '';

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"getRSVP","taskData":{"pageNO":"0"}}
	 *
	 */
	function getRSVP()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$pageNO    = IJReq::getTaskData('pageNO',0);
		$pageLimit = BESEATED_GENERAL_LIST_LIMIT;
		if($pageNO==0 || $pageNO==1){
			$startFrom=0;
		}else{
			$startFrom = $pageLimit*($pageNO-1);
		}

		$venue = $this->helper->venueUserDetail($this->IJUserID);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$rsvpStatus = array();
		$rsvpStatus[] = $this->helper->getStatusID('request');
		$rsvpStatus[] = $this->helper->getStatusID('awaiting-payment');
		$rsvpStatus[] = $this->helper->getStatusID('decline');

		// Create the base select statement.
		$query->select('vb.*')
			->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vb')
			->where($db->quoteName('vb.venue_id') . ' = ' . $db->quote($venue->venue_id))
			->where($db->quoteName('vb.venue_status') . ' IN ('.implode(',', $rsvpStatus).')')
			->where($db->quoteName('vb.deleted_by_venue') . ' = ' . $db->quote(0))
			->where($db->quoteName('vb.booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('usr.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('vb.booking_date') . ' ASC,'.$db->quoteName('vb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=vb.user_id');

		/*$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=pb.protection_status');*/

		$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

		$query->select('vt.table_name,min_price')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vb.table_id');

		$query->select('vpt.premium_table_name')
			->join('LEFT','#__beseated_venue_premium_table AS vpt ON vpt.premium_id=vt.premium_table_id');

		/*echo "<pre>";
		print_r($query->dump());
		echo "</pre>";
		exit;*/

		/*echo "<pre>";
		print_r($query->dump());
		echo "</pre>";
		exit;*/
		/*$query = $db->getQuery(true);

		$query->select('vgb.*')
			->from($db->quoteName('#__beseated_venue_guest_booking') . ' AS vgb')
			->where($db->quoteName('vgb.venue_id') . ' = ' . $db->quote($venue->venue_id))
			//->where($db->quoteName('vgb.venue_status') . ' IN ('.implode(',', $rsvpStatus).')')
			//->where($db->quoteName('vgb.deleted_by_venue') . ' = ' . $db->quote(0))
			->order($db->quoteName('vgb.booking_date') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=vgb.user_id');

		$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vgb.venue_id');*/

		/*echo "<pre>";
		print_r($query->dump());
		echo "</pre>";
		exit;*/

		// Set the query and load the result.
		//$db->setQuery($query,$startFrom,$pageLimit);
		$db->setQuery($query);

		$resBookings = $db->loadObjectList();


		/*
		[guest_booking_id] => 25
        [venue_id] => 1
        [user_id] => 364
        [booking_date] => 2015-10-15
        [male_guest] => 2
        [female_guest] => 3
        [total_guest] => 5
        [remaining_guest] => 5
        [guest_status] => 2
        [venue_status] => 0
        [request_date_time] => 0000-00-00 00:00:00
        [response_date_time] => 0000-00-00 00:00:00
        [time_stamp] => 1448003959
        [created] => 2015-11-20 01:19:19
        [full_name] => Darshit Zalavadiya
        [phone] => 2147483647
        [avatar] => Guest/364/036e95ce4f5f71cb83224f7e.jpg
        [thumb_avatar] => Guest/364/thumb/thumb_036e95ce4f5f71cb83224f7e.jpg
        [venue_name] => Venue One
        [location] => Ahmedabad
        [city] => Ahmedabad
        [venue_type] => Restaurant
        [is_day_club] => 0
		exit;*/
		/*if(count($resBookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}*/

		$resultBookings = array();
		//$resultUpcomingBookings = array();
		//$resultHistoryBookings = array();
		foreach ($resBookings as $key => $booking)
		{
			$temp = array();
			$temp['venueBookingID']      = $booking->venue_table_booking_id;
			$temp['fullName']            = $booking->full_name;
			$temp['phone']               = $booking->phone;
			$temp['avatar']              = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$temp['thumbAvatar']         = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';

			$userDetail                  = $this->helper->guestUserDetail($booking->user_id);
			$temp['fbid']                = ($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id)) ? $userDetail->fb_id : '';
			$temp['tableName']           = $booking->table_name;
			$temp['tableType']           = ($booking->premium_table_name)?$booking->premium_table_name:'';
			$temp['bookingDate']         = $this->helper->convertDateFormat($booking->booking_date);
			$temp['bookingTime']         = $this->helper->convertToHM($booking->booking_time);
			$temp['formatedCurrency']    = $this->helper->currencyFormat($booking->booking_currency_sign,$booking->total_price);
			$temp['minSpend']            = $this->helper->currencyFormat('',$booking->min_price	);
			$temp['hoursRquested']       = $booking->total_hours;
			$temp['dayClub']             = $booking->is_day_club;
			$temp['statusCode']          = $booking->venue_status;
			$temp['totalGuest']          = $booking->total_guest;
			$temp['maleGuest']           = $booking->male_guest;
			$temp['femaleGuest']         = $booking->female_guest;
			$temp['bookingCurrencyCode'] = $booking->booking_currency_code;
			$temp['totalAmount']         = $this->helper->currencyFormat('',$booking->total_price);
			$temp['type']                = 'booking';
			//$temp['bookingCurrencySign'] = $booking->booking_currency_sign;
			//$temp['bookingCurrencyCode'] = $booking->booking_currency_code;

			if(!$this->helper->isPastDate($booking->booking_date))
			{
				$resultBookings[] = $temp;
			}
		}

		$glStatus = array();
		//$glStatus[] = $this->helper->getStatusID('pending');
		$glStatus[] = $this->helper->getStatusID('request');

		$query = $db->getQuery(true);
		$query->select('vgb.*')
			->from($db->quoteName('#__beseated_venue_guest_booking') . ' AS vgb')
			->where($db->quoteName('vgb.venue_status') . ' IN ('.implode(",", $glStatus).')')
			->where($db->quoteName('vgb.venue_id') . ' = ' . $db->quote($venue->venue_id))
			->where($db->quoteName('vgb.booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->order($db->quoteName('vgb.booking_date') . ' ASC');
		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=vgb.user_id');
		$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vgb.venue_id');
		$db->setQuery($query);

		$resGuestBookings = $db->loadObjectList();
		$resultGuestBookings = array();
		foreach ($resGuestBookings as $key => $booking)
		{
			$temp                   = array();
			$temp['guestBookingID'] = $booking->guest_booking_id;
			$temp['fullName']       = $booking->full_name;
			$temp['phone']          = $booking->phone;
			$temp['avatar']         = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$temp['thumbAvatar']    = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
			$temp['bookingDate']    = $this->helper->convertDateFormat($booking->booking_date);
			$temp['statusCode']     = $booking->venue_status;
			$temp['totalGuest']     = $booking->total_guest;
			$temp['maleGuest']      = $booking->male_guest;
			$temp['femaleGuest']    = $booking->female_guest;
			$temp['type']           = 'guestList';

			$userDetail                  = $this->helper->guestUserDetail($booking->user_id);
			$temp['fbid']                = ($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id)) ? $userDetail->fb_id : '';

			//$temp['bookingCurrencyCode'] = $booking->booking_currency_code;
			//$temp['totalAmount']         = $this->helper->currencyFormat('',$booking->total_price);
			//$temp['bookingCurrencySign'] = $booking->booking_currency_sign;
			//$temp['bookingCurrencyCode'] = $booking->booking_currency_code;
			//$temp['bookingTime']         = $this->helper->convertToHM($booking->booking_time);
			//$temp['formatedCurrency']    = $this->helper->currencyFormat($booking->booking_currency_sign,$booking->total_price);
			//$temp['minSpend']            = $this->helper->currencyFormat('',$booking->min_price	);
			//$temp['hoursRquested']       = $booking->total_hours;
			//$temp['dayClub']             = $booking->is_day_club;
			//$temp['tableName']           = $booking->table_name;
			//$temp['tableType']           = ($booking->premium_table_name)?$booking->premium_table_name:'';

			$resultGuestBookings[] = $temp;
		}

		$resultBookings = array_merge($resultGuestBookings,$resultBookings);
		//$resultBookings = $this->array_sort($resultBookings,'bookingDate');
		if(count($resultBookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_RSVP_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray['code']          = 200;
		$this->jsonarray['totalRSVP'] = count($resultBookings);
		$this->jsonarray['rsvp']  = array_values($resultBookings);

		return $this->jsonarray;
	}

	/*function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
	{
		$sort_col = array();

		foreach ($arr as $key=> $row)
		{
			$sort_col[$key] = $row->$col;
		}

		//echo "<pre/>";print_r($dir);
		array_multisort($sort_col, $dir, $arr);
	}*/

	function array_sort($array, $on, $order=SORT_ASC)
	{
		$new_array = array();
		$sortable_array = array();

		if (count($array) > 0)
		{
			foreach ($array as $k => $v)
			{
				if (is_array($v))
				{
					foreach ($v as $k2 => $v2)
					{
						if ($k2 == $on)
						{
							$sortable_array[$k] = $v2;
						}
					}
				}
				else
				{
					$sortable_array[$k] = $v;
				}
			}

			switch ($order)
			{
				case SORT_ASC:
					asort($sortable_array);
					break;
				case SORT_DESC:
					arsort($sortable_array);
					break;
			}

			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}

	    return $new_array;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"postBill","taskData":{"venueBookingID":"2"}}
	 *
	 *
	 */
	function postBill()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$venueBookingID = IJReq::getTaskData('venueBookingID',0);
		$billAmount     = IJReq::getTaskData('billAmount','','string');
		$billAmount     = trim($billAmount);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

	    $beseatedParams  = BeseatedHelper::getExtensionParam();
		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');
		$tblTable        = JTable::getInstance('Table', 'BeseatedTable');

		$tblVenueBooking->load($venueBookingID);

		if(!$tblVenueBooking->venue_table_booking_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblVenue->load($tblVenueBooking->venue_id);
		$tblTable->load($tblVenueBooking->table_id);
		if(!$billAmount)
		{
			$billAmount = $tblVenueBooking->total_price;
		}

		$tblVenueBooking->bill_post_amount = $billAmount;
		$tblVenueBooking->is_bill_posted   = 1;

		if($tblVenueBooking->is_paid_deposite)
		{
			$tblVenueBooking->remaining_amount = $billAmount - $tblVenueBooking->pay_deposite;
			$tblVenueBooking->total_price      = $billAmount  - $tblVenueBooking->pay_deposite;
			$tblVenueBooking->final_price      =  $billAmount  - $tblVenueBooking->pay_deposite;
		}
		else
		{
			$tblVenueBooking->remaining_amount = $billAmount;
			$tblVenueBooking->total_price      = $billAmount;
			$tblVenueBooking->final_price      = $billAmount;
		}

		//$tblVenueBooking->venue_status     = $this->helper->getStatusID('awaiting-payment');
		//$tblVenueBooking->user_status      = $this->helper->getStatusID('available');
		//$tblVenueBooking->respone_date_time = date('Y-m-d H:i:s');

		if(!$tblVenue->active_payments)
		{
			$tblVenueBooking->user_status       = $this->helper->getStatusID('booked');
			$tblVenueBooking->venue_status      = $this->helper->getStatusID('booked');
			$this->calculateLoyaltyPoint($tblVenueBooking->total_price,$tblVenueBooking->booking_currency_code,$beseatedParams->loyalty,'purchase.venue',$tblVenueBooking->venue_table_booking_id,1,$tblVenueBooking->user_id,$tblVenue->venue_name,$tblTable->table_name);
		}

		if(!$tblVenueBooking->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$notificationType = "venue.postbill";

		if($tblVenue->is_day_club)
		{
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_VENUE_DAY_TABLE_BOOKING_POSTBILL',
									$tblVenue->venue_name,
									$tblTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$this->helper->convertToHM($tblVenueBooking->booking_time)
								);
		}
		else
		{
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_VENUE_NIGHT_TABLE_BOOKING_POSTBILL',
									$tblVenue->venue_name,
									$tblTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date)
								);
		}

		$actor            = $this->IJUserID;
		$target           = $tblVenueBooking->user_id;
		$elementID        = $tblVenue->venue_id;
		$elementType      = "venue";
		$cid              = $tblVenueBooking->venue_table_booking_id;
		$extraParams      = array();
		$extraParams["venueTableBookingID"] = $tblVenueBooking->venue_table_booking_id;

		if($tblVenue->active_payments)
		{
			if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$cid))
			{
				$this->jsonarray['pushNotificationData']['id']         = $tblVenueBooking->venue_table_booking_id;
				$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
				$this->jsonarray['pushNotificationData']['to']         = $target;
				$this->jsonarray['pushNotificationData']['message']    = $title;
				//$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_VENUE_BOOKING_POSTBILL');
				$this->jsonarray['pushNotificationData']['type']       = $notificationType;
				$this->jsonarray['pushNotificationData']['configtype'] = '';
			}

		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"changeBookingStatus","taskData":{"venueBookingID":"2","statusCode":"1"}}
	 *
	 */
	function changeBookingStatus()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$venueBookingID = IJReq::getTaskData('venueBookingID',0);
		$statusCode     = IJReq::getTaskData('statusCode',0,'int');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');
		$tblTable        = JTable::getInstance('Table', 'BeseatedTable');

		$tblVenueBooking->load($venueBookingID);

		if(!$tblVenueBooking->venue_table_booking_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblVenue->load($tblVenueBooking->venue_id);
		$tblTable->load($tblVenueBooking->table_id);

		//$billAmount = $tblVenueBooking->total_price;

		if($statusCode == 3)
		{
			$tblVenueBooking->user_status = $this->helper->getStatusID('available');
			$tblVenueBooking->venue_status = $this->helper->getStatusID('awaiting-payment');
			$tblVenueBooking->remaining_amount  = $tblVenueBooking->total_price;
			$notificationType = "venue.request.accepted";

			if($tblVenue->is_day_club)
			{
				$formatedBookingTime = $this->helper->convertToHM($tblVenueBooking->booking_time);
				$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_ACCEPTED_BY_DAY_VENUE',
									$tblVenue->venue_name,
									$tblTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$formatedBookingTime
								);

				$dbTitle          = JText::sprintf(
									'COM_BESEATED_DB_NOTIFICATION_TABLE_BOOKING_REQUEST_ACCEPTED_BY_VENUE',
									$tblTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$this->helper->convertToHM($tblVenueBooking->booking_time),
									$tblVenueBooking->total_guest
								);
			}
			else
			{
				$formatedBookingTime = "-";
				$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_ACCEPTED_BY_NIGHT_VENUE',
									$tblVenue->venue_name,
									$tblTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date)

								);

				$dbTitle            = JText::sprintf(
									'COM_BESEATED_DB_NOTIFICATION_TABLE_BOOKING_REQUEST_ACCEPTED_BY_NIGHT_VENUE',
									$tblTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$tblVenueBooking->total_guest
								);
			}

			/*$actor            = $this->IJUserID;
			$target           = $tblVenueBooking->user_id;
			$elementID        = $tblVenue->venue_id;
			$elementType      = "Protection";
			$cid              = $tblVenueBooking->venue_table_booking_id;
			$extraParams      = array();
			$extraParams["venueTableBookingID"] = $tblVenueBooking->venue_table_booking_id;
			$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);*/
		}
		else if($statusCode == 6)
		{
			$tblVenueBooking->user_status = $this->helper->getStatusID('decline');
			$tblVenueBooking->venue_status = $this->helper->getStatusID('decline');
			$notificationType = "table.request.declined";

			if($tblVenue->is_day_club)
			{
				$formatedBookingTime = $this->helper->convertToHM($tblVenueBooking->booking_time);
				$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_DECLINED_BY_DAY_VENUE',
									$tblVenue->venue_name,
									$tblTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$formatedBookingTime
								);

				$dbTitle            = JText::sprintf(
									'COM_BESEATED_DB_NOTIFICATION_TABLE_BOOKING_REQUEST_DECLINED_BY_DAY_VENUE',
									$tblTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$formatedBookingTime,
									$tblVenueBooking->total_guest
								);
			}
			else
			{
				$formatedBookingTime = "-";
				$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_TABLE_BOOKING_REQUEST_DECLINED_BY_NIGHT_VENUE',
									$tblVenue->venue_name,
									$tblTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date)

								);

				$dbTitle            = JText::sprintf(
									'COM_BESEATED_DB_NOTIFICATION_TABLE_BOOKING_REQUEST_DECLINED_BY_NIGHT_VENUE',
									$tblTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$tblVenueBooking->total_guest
								);
			}


		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_TABLE_INVALID_STATUS'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//$tblVenueBooking->bill_post_amount  = $billAmount;
		//$tblVenueBooking->venue_status      = $this->helper->getStatusID('awaiting-payment');
		//$tblVenueBooking->user_status       = $this->helper->getStatusID('available');

		$tblVenueBooking->response_date_time = date('Y-m-d H:i:s');

		if(!$tblVenueBooking->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$actor            = $this->IJUserID;
		$target           = $tblVenueBooking->user_id;
		$elementID        = $tblVenue->venue_id;
		$elementType      = "Venue";
		$cid              = $tblVenueBooking->venue_table_booking_id;
		$extraParams      = array();
		$extraParams["venueTableBookingID"] = $tblVenueBooking->venue_table_booking_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$dbTitle,$cid,$extraParams,$cid))
		{
			$venueDefaultImage = $this->helper->getElementDefaultImage($tblVenue->venue_id,'Venue');
			$venueThumb = Juri::base().'images/beseated/'.$venueDefaultImage->thumb_image;
			$bookingDate = date('d F Y',strtotime($tblVenueBooking->booking_date));
			$companyDetail = JFactory::getUser($tblVenue->user_id);

			$userDetail = JFactory::getUser($target);

			if($statusCode == 3)
			{
				$this->emailHelper->venueBookingAvailableUserMail($tblVenue->venue_name,$venueThumb,$bookingDate,$formatedBookingTime,$tblTable->table_name,$tblVenue->currency_code,number_format($tblVenueBooking->total_price,0),$tblVenueBooking->male_guest,$tblVenueBooking->female_guest,$userDetail->name,$userDetail->email,$userDetail->email);
			}
			elseif ($statusCode == 6)
			{
				$this->emailHelper->venueBookingNotAvailableUserMail($tblVenue->venue_name,$venueThumb,$bookingDate,$formatedBookingTime,$tblTable->table_name,$tblVenue->currency_code,number_format($tblVenueBooking->total_price,0),$tblVenueBooking->male_guest,$tblVenueBooking->female_guest,$userDetail->name,$userDetail->email,$userDetail->email);
			}
		}

		//$tblProtectionBooking->user_status = $this->helper->getStatusID('available');
		//$tblProtectionBooking->protection_status = $this->helper->getStatusID('awaiting-payment');

		$this->jsonarray['code'] = 200;

		$this->jsonarray['pushNotificationData']['id']         = $tblVenueBooking->venue_table_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
		$this->jsonarray['pushNotificationData']['to']         = $target;
		$this->jsonarray['pushNotificationData']['message']    = $title;
		//$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_VENUE_BOOKING_REQUEST_STATUS_CHANGED');
		$this->jsonarray['pushNotificationData']['type']       = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype'] = '';

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"confirmBooking","taskData":{"venueBookingID":"2","bottles":[{"bottleID":"1","qty":"3"},{"bottleID":"4","qty":"2"},{"bottleID":"5","qty":"3"}]}}
	 *
	 * {
		    "code": "200",
		    "php_server_error": ""
		}
	 *
	 */
	function confirmBooking()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$venueBookingID = IJReq::getTaskData('venueBookingID',0);
		$bottles        = IJReq::getTaskData('bottles','','form');

        $bottles = json_decode($bottles);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenueBooking       = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblBottle             = JTable::getInstance('Bottle', 'BeseatedTable');
		$tblVenue              = JTable::getInstance('Venue', 'BeseatedTable');
		$tblTable              = JTable::getInstance('Table', 'BeseatedTable');

		$tblVenueBooking->load($venueBookingID);
		$tblVenue->load($tblVenueBooking->venue_id);
		$tblTable->load($tblVenueBooking->table_id);

		$bottlePrice = 0;
		$bottleRow = '';
		//$comission = $tblVenue->deposit_per

		foreach ($bottles as $key => $bottle)
		{
			$tblBottle->load($bottle->bottleID);
			$bottle->bottleID;
			$bottle->qty;

			$bottleBookingPost = array();
			$bottleBookingPost['bottle_id']              = $bottle->bottleID;
			$bottleBookingPost['venue_table_booking_id'] = $tblVenueBooking->venue_table_booking_id;
			$bottleBookingPost['venue_id']               = $tblVenueBooking->venue_id;
			$bottleBookingPost['table_id']               = $tblVenueBooking->table_id;
			$bottleBookingPost['user_id']                = $tblVenueBooking->user_id;
			$bottleBookingPost['qty']                    = $bottle->qty;
			$bottleBookingPost['price']                  = $tblBottle->price;
			$bottleBookingPost['total_price']            = $tblBottle->price * $bottle->qty;
			$bottleBookingPost['time_stamp']             = time();

			$tblVenueBottleBooking = JTable::getInstance('VenueBottleBooking', 'BeseatedTable');
			$tblVenueBottleBooking->load(0);
			$tblVenueBottleBooking->bind($bottleBookingPost);

			if($tblVenueBottleBooking->store())
			{
				$bottlePrice = $bottlePrice + $tblBottle->price * $bottle->qty;
			}

			if($key == 0)
			{

				$bottleRow .= ' <tr>
		                    	<td width="260" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$tblBottle->brand_name.' x '.$bottle->qty.'</td>
		                        <td width="220" style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$tblVenueBooking->booking_currency_code.' '.number_format($tblBottle->price,0).'</td>
		                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;">'.$tblVenueBooking->booking_currency_code.' '.number_format(($tblBottle->price * $bottle->qty),0).'</td>
		                       </tr>';
	        }
	        else
	        {

				$bottleRow .= ' <tr>
		                    	<td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$tblBottle->brand_name.' x '.$bottle->qty.'</td>
		                        <td style="font-family:Arial, Helvetica, sans-serif; color:#000;">'.$tblVenueBooking->booking_currency_code.' '.number_format($tblBottle->price,0).'</td>
		                        <td style="font-family:Arial, Helvetica, sans-serif;color:#000;">'.$tblVenueBooking->booking_currency_code.' '.number_format(($tblBottle->price * $bottle->qty),0).'</td>
		                       </tr>';
	        }

		}


		if($tblVenue->deposit_per == '0')
		{
			$tblVenueBooking->user_status        = $this->helper->getStatusID('confirmed');
			$tblVenueBooking->venue_status       = $this->helper->getStatusID('confirmed');
			$tblVenueBooking->has_booked         =  1;

			$tblReadElementBooking = JTable::getInstance('ReadElementBooking', 'BeseatedTable');
			$tblReadElementBooking->load(0);
			$tblReadElementBooking->booked_type  = 'booking';
			$tblReadElementBooking->element_type = 'venue';
			$tblReadElementBooking->booking_id   = $venueBookingID;
			$tblReadElementBooking->from_user_id = $tblVenue->user_id;
			$tblReadElementBooking->to_user_id   = $this->IJUserID;
			$tblReadElementBooking->store();
		}

		$tblVenueBooking->has_bottle         = ($bottlePrice)?1:0;
		$tblVenueBooking->total_bottle_price = $bottlePrice;
		$tblVenueBooking->final_price        = $tblTable->min_price + $bottlePrice;
		$tblVenueBooking->total_price        = $tblTable->min_price + $bottlePrice;


		if($bottlePrice)
		{
			$finalPriceCommision  = $bottlePrice * $tblVenue->deposit_per /100;
			$totalPriceCommision  = $bottlePrice * $tblVenue->deposit_per /100;
		}
		else
		{
			$finalPriceCommision  = $tblTable->min_price * $tblVenue->deposit_per /100;
		    $totalPriceCommision  = $tblTable->min_price * $tblVenue->deposit_per /100;
		}


		$tblVenueBooking->final_price       = $tblVenueBooking->final_price - $finalPriceCommision;
		$tblVenueBooking->total_price       = $tblVenueBooking->total_price - $totalPriceCommision;
		$tblVenueBooking->remaining_amount  = $tblVenueBooking->total_price; // by jamal
		$tblVenueBooking->pay_deposite      = $finalPriceCommision;

		if(!$tblVenueBooking->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$userDetail = $this->helper->guestUserDetail($this->IJUserID);

		$notificationType = "venue.booking.confirm";

		if($tblVenue->is_day_club)
		{
			$formatedBookingTime = $this->helper->convertToHM($tblVenueBooking->booking_time);

			$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_DAY_VENUE_TABLE_BOOKING_CONFIRMED',
								$userDetail->full_name,
								$tblTable->table_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date),
								$formatedBookingTime
							);
		}
		else
		{
			$formatedBookingTime = '-';

			$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_NIGHT_VENUE_TABLE_BOOKING_CONFIRMED',
								$userDetail->full_name,
								$tblTable->table_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date)
							);
		}

		$actor            = $this->IJUserID;
		$target           = $tblVenue->user_id;
		$elementID        = $tblVenue->venue_id;
		$elementType      = "Venue";
		$cid              = $tblVenueBooking->venue_table_booking_id;
		$extraParams      = array();
		$extraParams["venueTableBookingID"] = $tblVenueBooking->venue_table_booking_id;

		$showDirection = "Show Directions";

		if($tblVenue->deposit_per == '0')
		{
			if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$cid))
			{
				$venueDefaultImage = $this->helper->getElementDefaultImage($tblVenue->venue_id,'Venue');
				$venueThumb = Juri::base().'images/beseated/'.$venueDefaultImage->thumb_image;
				$bookingDate = date('d F Y',strtotime($tblVenueBooking->booking_date));
				$companyDetail = JFactory::getUser($tblVenue->user_id);

				$passkey = ($tblVenueBooking->passkey) ? $tblVenueBooking->passkey : '-';

				$this->emailHelper->venueBookingconfirmedUserMail($userDetail->full_name,$companyDetail->name,$venueThumb,$tblVenue->location,$companyDetail->phone,$showDirection,$cid,$bookingDate,$formatedBookingTime,$tblTable->table_name,$tblVenue->currency_code,number_format($tblTable->min_price,0),$tblVenueBooking->male_guest,$tblVenueBooking->female_guest,$passkey,$userDetail->full_name,$userDetail->email,$bottleRow,number_format($tblVenueBooking->total_bottle_price,0),$tblVenue->refund_policy,$userDetail->email);

				$this->jsonarray['pushNotificationData']['id']         = $tblVenueBooking->venue_table_booking_id;
				$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
				$this->jsonarray['pushNotificationData']['to']         = $target;
				$this->jsonarray['pushNotificationData']['message']    = $title;
				//$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_VENUE_BOOKING_CONFIRMED');
				$this->jsonarray['pushNotificationData']['type']       = $notificationType;
				$this->jsonarray['pushNotificationData']['configtype'] = '';
			}
		}

		$this->jsonarray['paymentURL'] = ($tblVenue->deposit_per == '0') ? '' : JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$venueBookingID.'&booking_type=venue.confirm';
		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"PayByCashStatusChange","taskData":{"bookingID":"2","action":"accept/decline"}}
	 *
	 * {
		    "code": "200",
		    "php_server_error": ""
		}
	 *
	 */
	function PayByCashStatusChange()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingID      = IJReq::getTaskData('bookingID',0,'int');
		$notificationID = IJReq::getTaskData('notificationID',0,'int');
		$action         = IJReq::getTaskData('action','','string'); //accept/decline

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenueBooking      = JTable::getInstance('VenueBooking', 'BeseatedTable');
		//$tblVenueBooking->load($bookingID);
		$tblVenueBookingSplit = JTable::getInstance('VenueBookingSplit', 'BeseatedTable');
		$tblNotification           = JTable::getInstance('Notification', 'BeseatedTable');
		$tblNotification->load($notificationID);
		/*if($bookingType == 'venue.split')
		{
			$tblVenueBookingSplit->load($bookingID);
			if(!$tblVenueBookingSplit->venue_table_booking_split_id){
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}
			$tblVenueBooking->load($tblVenueBookingSplit->venue_table_booking_id);
		}else{
			$tblVenueBooking->load($bookingID);
		}*/

		if(empty($action) || !$bookingID || !$notificationID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!$tblNotification->notification_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($tblNotification->notification_type == 'venue.split.paybycash.request')
		{
			$tblVenueBookingSplit->load($bookingID);
			$tblVenueBooking->load($tblVenueBookingSplit->venue_table_booking_id);
		}
		else
		{
			$tblVenueBooking->load($bookingID);
		}

		//echo "<pre/>";print_r($tblVenueBooking);exit;

		if(!$tblVenueBooking->venue_table_booking_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($tblVenueBooking->is_splitted)
		{
			/*$query = $this->db->getQuery(true);
			$query->select('*')
				->from($this->db->quoteName('#__beseated_venue_table_booking_split'))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($this->IJUserID))
				->where($this->db->quoteName('venue_table_booking_id') . ' = ' . $this->db->quote($tblVenueBooking->venue_table_booking_id));
			$this->db->setQuery($query);
			$resSplitDetail = $this->db->loadObject();*/

			$tblVenueBookingSplit->load($tblNotification->cid);

			if(!$tblVenueBookingSplit->venue_table_booking_split_id)
			{
				IJReq::setResponseCode(400);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
				IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
				return false;
			}

			$bookingType = 'venue.split';

			if(strtolower($action) == 'decline')
			{
				$tblVenueBookingSplit->pay_by_cash_status = 3;
				$tblVenueBookingSplit->store();
				$tblNotification->delete();
			}
		}
		else
		{
			$bookingType = 'venue';

			if(strtolower($action) == 'decline')
			{
				$tblVenueBooking->pay_by_cash_status = 3;
				$tblVenueBooking->store();
				$tblNotification->delete();
				//$this->jsonarray['code'] = 200;
				//return $this->jsonarray;
			}
		}

		$currencyCode = $tblVenueBooking->booking_currency_code;

		if($bookingType == 'venue')
		{
			$target = $tblVenueBooking->user_id;

			if($action == 'accept')
			{
				if($tblVenueBooking->is_splitted)
				{
					$userPaidAmount = $tblVenueBooking->each_person_pay;

					$tblVenueBookingSplit->split_payment_status = 7;
					$tblVenueBookingSplit->pay_by_cash_status = 2;
					$tblVenueBookingSplit->store();

					$totalPrice = $userPaidAmount;
					$tblVenueBooking->remaining_amount = $tblVenueBooking->remaining_amount - $userPaidAmount;
					$elementID = $tblVenueBooking->venue_table_booking_id;
					$tblVenueBooking->store();

					$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
					$tblVenueBooking->load($elementID);
					$remainingAmount = $this->getSplitedRemainingAmount($tblVenueBooking->venue_table_booking_id);
					if($remainingAmount == 0)
					{
						$tblVenueBooking->user_status       = 5;
						$tblVenueBooking->venue_status = 5;
					}

					//$this->deleteReplaceInvitee($tblVenueBooking->venue_table_booking_id,$tblVenueBookingSplit->venue_table_booking_split_id);

				}
				else
				{
					if($tblVenueBooking->has_bottle)
					{
						$totalPrice           = $tblVenueBooking->final_price;
					}
					else
					{
						$totalPrice           = $tblVenueBooking->total_price;
					}

					$tblVenueBooking->pay_by_cash_status = 2;
					$tblVenueBooking->user_status        = 5;
					$tblVenueBooking->venue_status       = 5;

					//$this->deleteReplaceInvitee($tblVenueBooking->venue_table_booking_id,$tblVenueBooking->venue_table_booking_id);

				}
			}
		}
		else if($bookingType == 'venue.split')
		{
			$target = $tblVenueBookingSplit->user_id;

			if($action == 'accept')
			{
				$tblVenueBookingSplit->split_payment_status = 7;
				$tblVenueBookingSplit->pay_by_cash_status = 2;

				$userPaidAmount = $tblVenueBookingSplit->splitted_amount;
				$totalPrice     = $userPaidAmount;
				$totalAmount    = $userPaidAmount;
				$tblVenueBookingSplit->store();

				$tblVenueBooking->remaining_amount = $tblVenueBooking->remaining_amount - $userPaidAmount;
				$tblVenueBooking->store();

				$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
				$tblVenueBooking->load($tblVenueBookingSplit->venue_table_booking_id);

				$remainingAmount      = $this->getSplitedRemainingAmount($tblVenueBookingSplit->venue_table_booking_id);
				if($remainingAmount == 0)
				{
					$tblVenueBooking->user_status       = 5;
					$tblVenueBooking->venue_status = 5;
				}

			}
			//$this->deleteReplaceInvitee($tblVenueBooking->venue_table_booking_id,$tblVenueBookingSplit->venue_table_booking_split_id);

		}
		else
		{
			$this->jsonarray['code'] = 400;
			return $this->jsonarray;
		}

		$tblVenue = JTable::getInstance('Venue', 'BeseatedTable');
		$tblVenue->load($tblVenueBooking->venue_id);

		$tblService = JTable::getInstance('Table', 'BeseatedTable');
		$tblService->load($tblVenueBooking->table_id);

		if($action == 'accept')
		{
			require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

			$beseatedParams = BeseatedHelper::getExtensionParam();

			//echo "<pre/>";print_r($bookingType);exit;

			if(strtolower($bookingType) == 'venue')
			{
				$this->calculateLoyaltyPoint($totalPrice,$currencyCode,$beseatedParams->loyalty,'purchase.venue.cash',$tblVenueBooking->venue_table_booking_id,1,$target,$tblVenue->venue_name,$tblService->table_name);
			}
			else if(strtolower($bookingType) == 'venue.split')
			{
				$this->calculateLoyaltyPoint($totalPrice,$currencyCode,$beseatedParams->loyalty,'purchase.splited.venue.cash',$tblVenueBookingSplit->venue_table_booking_split_id,1,$target,$tblVenue->venue_name,$tblService->table_name);
			}
		}

		if(!$tblVenueBooking->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		if($tblNotification->notification_id)
		{
			$tblNotification->delete();
		}


		$tblVenue = JTable::getInstance('Venue', 'BeseatedTable');
		$tblVenue->load($tblVenueBooking->venue_id);

		$tblVenueTable = JTable::getInstance('VenueTable', 'BeseatedTable');
		$tblVenueTable->load($tblVenueBooking->table_id);

		if($action == 'accept')
		{
			$notificationType = "venue.paybycash.request.accepted";

			if($tblVenue->is_day_club)
			{
				$title = JText::sprintf(
							'COM_BESEATED_NOTIFICATION_DAY_TABLE_BOOKING_PAY_BY_CASH_REQUEST_ACCEPT_BY_VENUE',
							$tblVenue->venue_name,
							$tblVenueTable->table_name,
							$this->helper->convertDateFormat($tblVenueBooking->booking_date),
							$this->helper->convertToHM($tblVenueBooking->booking_time)
						);
			}
			else
			{
				$title = JText::sprintf(
							'COM_BESEATED_NOTIFICATION_NIGHT_TABLE_BOOKING_PAY_BY_CASH_REQUEST_ACCEPT_BY_VENUE',
							$tblVenue->venue_name,
							$tblVenueTable->table_name,
							$this->helper->convertDateFormat($tblVenueBooking->booking_date)
						);
			}

		}
		else if($action == 'decline')
		{
			$notificationType = "venue.paybycash.request.declined";

			if($tblVenue->is_day_club)
			{
				$title      = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_DAY_TABLE_BOOKING_PAY_BY_CASH_REQUEST_DECLINE_BY_VENUE',
									$tblVenue->venue_name,
									$tblVenueTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$this->helper->convertToHM($tblVenueBooking->booking_time)
								);
			}
			else
			{
				$title      = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_NIGHT_TABLE_BOOKING_PAY_BY_CASH_REQUEST_DECLINE_BY_VENUE',
									$tblVenue->venue_name,
									$tblVenueTable->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date)
								);
			}
		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_SERVICE_INVALID_STATUS'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$actor            = $this->IJUserID;
		//$target           = $tblProtectionBooking->user_id;
		$elementID        = $tblVenue->venue_id;
		$elementType      = "Venue";
		$cid              = $tblVenueBooking->venue_table_booking_id;
		$extraParams      = array();
		$extraParams["venueBookingID"] = $tblVenueBooking->venue_table_booking_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$cid))
		{
			$this->jsonarray['pushNotificationData']['id']         = $tblVenueBooking->venue_table_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
			$this->jsonarray['pushNotificationData']['to']         = $target;
			$this->jsonarray['pushNotificationData']['message']    = $title;
			$this->jsonarray['pushNotificationData']['type']       = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype'] = '';
		}


		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	public function convertCurrencyGoogle($amount = 1, $from, $to)
	{
		$url  = "https://www.google.com/finance/converter?a=$amount&from=$from&to=$to";
		$data = file_get_contents($url);
		preg_match("/<span class=bld>(.*)<\/span>/",$data, $converted);
		$converted = preg_replace("/[^0-9.]/", "", $converted[1]);

		return round($converted, 2);
	}

	public function calculateLoyaltyPoint($amount,$currencyCode,$loyalty,$app,$cid,$isValid = 0,$target,$elementName,$tableName)
	{
		$amountInUSD = $this->convertCurrencyGoogle(trim($amount),$currencyCode,'USD');

		if(isset($currencyCode) && !empty($currencyCode))
		{
			if($currencyCode == 'AED'){
					$currencySign = 'AED';
		    }
			else if($currencyCode == 'USD' || $currencyCode == 'CAD' || $currencyCode == 'AUD'){
					$currencySign = '$';
			}
			else if($currencyCode == 'EUR'){
					$currencySign = '€';
			}
			else if($currencyCode == 'GBP'){
					$currencySign = '£';
			}
		}

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_beseated/tables');
		$tblLoyaltyPoint = JTable::getInstance('LoyaltyPoint', 'BeseatedTable');
		$tblLoyaltyPoint->load(0);
		$user = JFactory::getUser();
		$loyaltyPoint = (($amountInUSD * $loyalty)/100) * 10;
		$loyaltyPost['user_id']    = $target;
		$loyaltyPost['money_used'] = number_format($amount,0);
		$loyaltyPost['money_usd']  = $amountInUSD;
		$loyaltyPost['earn_point'] = $loyaltyPoint;
		$loyaltyPost['point_app']  = $app;
		$loyaltyPost['cid']        = $cid;
		//$loyaltyPost['title']      = strtoupper($elementName);
		$loyaltyPost['title']      = $elementName .' - '.$tableName.' - '.$currencySign.' '.number_format($amount,0);
		$loyaltyPost['is_valid']   = $isValid;
		$loyaltyPost['time_stamp'] = time();
		$loyaltyPost['money_used'] = $amount;
		$tblLoyaltyPoint->bind($loyaltyPost);
		$tblLoyaltyPoint->store();

		return $tblLoyaltyPoint->loyalty_point_id;
	}

	// call from acceptPayByCash
	public function getSplitedRemainingAmount($bookingID)
	{
		// Initialiase variables.
		$db                 = JFactory::getDbo();
		$query              = $db->getQuery(true);
		$tableName          = "";
		$bookingIDFieldName = "";
		$tableName          = "#__beseated_venue_table_booking_split";
		$bookingIDFieldName = "venue_table_booking_id";

		// Create the base select statement.
		$query->select('sum(splitted_amount)')
			->from($db->quoteName($tableName))
			->where($db->quoteName($bookingIDFieldName) . ' = ' . $db->quote($bookingID))
			->where($db->quoteName('split_payment_status') . ' <> ' . $db->quote(7));

		// Set the query and load the result.
		$db->setQuery($query);
		return $db->loadResult();
	}

	function getBookings()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$pageNO    = IJReq::getTaskData('pageNO',0);
		$pageLimit = BESEATED_GENERAL_LIST_LIMIT;
		if($pageNO==0 || $pageNO==1){
			$startFrom=0;
		}else{
			$startFrom = $pageLimit*($pageNO-1);
		}

		$venue = $this->helper->venueUserDetail($this->IJUserID);

		$bookedStatus = $this->helper->getStatusID('booked');

		$bookingStatus = array();
		$bookingStatus[] = $this->helper->getStatusID('booked');
		$bookingStatus[] = $this->helper->getStatusID('confirmed');
		$bookingStatus[] = $this->helper->getStatusID('canceled');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('vtb.*')
			->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vtb')
			->where($db->quoteName('vtb.venue_id') . ' = ' . $db->quote($venue->venue_id))
			->where($db->quoteName('vtb.venue_status') . ' IN ('.implode(',', $bookingStatus).')')
			->where($db->quoteName('vtb.deleted_by_venue') . ' = ' . $db->quote(0))
			->where($db->quoteName('usr.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('vtb.booking_date') . ' ASC,'.$db->quoteName('vtb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=vtb.user_id');

		$query->select('v.venue_name,v.location,v.city,v.venue_type,v.is_day_club')
				->join('LEFT','#__beseated_venue AS v ON v.venue_id=vtb.venue_id');

		$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=vtb.venue_status');

		$query->select('vt.table_name,vt.thumb_image,vt.image,vt.min_price')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vtb.table_id');

		// Set the query and load the result.
		//$db->setQuery($query,$startFrom,$pageLimit);
		$db->setQuery($query);

		$resBookings = $db->loadObjectList();

		if(count($resBookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$resultBookings = array();
		$resultUpcomingBookings = array();
		$resultHistoryBookings = array();

		foreach ($resBookings as $key => $booking)
		{
			$temp = array();
			$temp['venueBookingID']        = $booking->venue_table_booking_id;

			$temp['fullName']              = $booking->full_name;
			$temp['phone']                 = $booking->phone;
			$temp['avatar']                = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$temp['thumbAvatar']           = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';

			$userDetail = $this->helper->guestUserDetail($booking->user_id);

			$temp['fbid']                  = ($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id)) ? $userDetail->fb_id : '';
			$temp['serviceName']           = $booking->table_name;
			$temp['isShow']                = $booking->is_show;
			$temp['dayClub']               = $booking->is_day_club;
			//$temp['thumbImage']          = ($booking->thumb_image)?JUri::root().'image/beseated/'.$booking->thumb_image:'';
			//$temp['image']               = ($booking->image)?JUri::root().'image/beseated/'.$booking->image:'';

			$temp['totalguest']            = $booking->total_guest;
			$temp['maleGuest']             = $booking->male_guest;
			$temp['femaleGuest']           = $booking->female_guest;
			$temp['bookingDate']           = $this->helper->convertDateFormat($booking->booking_date);
			$temp['bookingTime']           = $this->helper->convertToHM($booking->booking_time);
			$temp['formatedCurrency']      = $this->helper->currencyFormat($booking->booking_currency_sign,$booking->total_price);
			$temp['hoursRquested']         = $booking->total_hours;
			$temp['bookingCurrencyCode']   = $booking->booking_currency_code;
			$temp['totalAmount']           = $this->helper->currencyFormat('',$booking->total_price);

			$temp['totalBottlePrice']      = $this->helper->currencyFormat('',$booking->total_bottle_price);
			$temp['tablePrice']            = $this->helper->currencyFormat('',$booking->min_price);

			$temp['PayByCashStatus']       = $booking->pay_by_cash_status;
			$temp['billPostedAmount']      = $this->helper->currencyFormat('',$booking->bill_post_amount);
			$temp['isBillPosted']          = $booking->is_bill_posted;
			//Added by aarathi
			$temp['statusCode']			   =  $booking->venue_status;
			$temp['bookingTimestamp']      = strtotime($booking->booking_date.' ' . $this->helper->convertToHM($booking->booking_time));

			$temp['hasBottleAdded']        =  ($this->hasBottleAdded($booking->venue_table_booking_id)) ? '1' : '0';
			$temp['isRead']                =  $this->helper->isReadBooking('venue','booking', $booking->venue_table_booking_id);
			$temp['isNoShow']              =  $booking->is_noshow;
			$temp['passkey']               =  $booking->passkey;


			//$temp['bookingCurrencySign'] = $booking->booking_currency_sign;
			//$temp['bookingCurrencyCode'] = $booking->booking_currency_code;

			//$checkDate = date('Y-m-d H:i:s',strtotime($booking->booking_date.$this->helper->convertToHM($booking->booking_time).' + 24h'));
			$checkDate = $booking->booking_date;
			$checkDateTime = date('Y-m-d H:i:s',strtotime($booking->booking_date.' '.$booking->booking_time) + (3600 * $booking->total_hours));


			//$a = $this->helper->isPastDateTime($checkDateTime) ;

			//$date = date('d-m-Y H:i',($message->time_stamp + $this->offset));
			/*echo "<br /> Check In Time : " . $booking->booking_time;
			echo "<br /> Total Hours : " . $booking->total_hours;
			echo "<br /> Check out Time : " . $checkDateTime;
			exit;*/

			//if($this->helper->isPastDateTime($checkDateTime) && $booking->venue_status == $bookedStatus && $booking->user_status == $bookedStatus)


			if($this->helper->isPastDateTime($checkDateTime) || $booking->is_noshow == 1)
			{
				$resultHistoryBookings[] = $temp;
			}
			else
			{
				$resultUpcomingBookings[] = $temp;
			}


		}

		if(count($resultHistoryBookings) == 0 && count($resultUpcomingBookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//$resultBookings['upcomming'] = $resultUpcomingBookings;
		//$resultBookings['history'] = $resultHistoryBookings;

		$this->jsonarray['code']          = 200;
		$this->jsonarray['totalUpcoming'] = count($resultUpcomingBookings);
		$this->jsonarray['totalHistory']  = count($resultHistoryBookings);
		$this->jsonarray['bookings']      = array('upcoming' => $resultUpcomingBookings,'history' => $resultHistoryBookings);
		//$this->jsonarray['bookings'] = $resultBookings;
		//$this->jsonarray['currentDateTime'] = date('Y-m-d H:i:s');

		return $this->jsonarray;
	}

	function bookGuestToVenue()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$userDetail = JFactory::getUser($this->IJUserID);

		$venueID      = IJReq::getTaskData('venueID','', 'string');
		$bookingDate  = IJReq::getTaskData('bookingDate','','string');
		$female_guest = IJReq::getTaskData('femaleGuest','', 'string');
		$male_guest   = IJReq::getTaskData('maleGuest','', 'string');

		$bookingDate = $this->helper->convertToYYYYMMDD($bookingDate);

		$isClosed = $this->helper->checkForVenueClosed($venueID,$bookingDate);

		if(!$isClosed)
		{
			IJReq::setResponseCode(902);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_CLOSED_ON_BOOKING_DATE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$request_status_id   = $this->helper->getStatusID('request');
		$pending_status_id   = $this->helper->getStatusID('pending');

		$total_guest = $male_guest + $female_guest;

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');

		$tblVenue->load($venueID);

		if(!$tblVenue->venue_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($this->helper->checkForAlreadyGuestList($tblVenue->venue_id,$this->IJUserID,$bookingDate))
		{
			IJReq::setResponseCode(707);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_GUESTLIST_ALREADY_EXIST_ON_DATE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base insert statement.
		$query->insert($db->quoteName('#__beseated_venue_guest_booking'))
			->columns(array($db->quoteName('venue_id'),
				            $db->quoteName('user_id'),
				            $db->quoteName('booking_date'),
				            $db->quoteName('male_guest'),
				            $db->quoteName('female_guest'),
				            $db->quoteName('total_guest'),
				            $db->quoteName('remaining_guest'),
				            $db->quoteName('guest_status'),
				            $db->quoteName('venue_status'),
				            $db->quoteName('request_date_time'),
				            $db->quoteName('time_stamp'),
				            $db->quoteName('created')
				            )
			         )
			->values($db->quote($venueID) . ', ' .
				     $db->quote($this->IJUserID) . ', ' .
				     $db->quote($bookingDate) . ', ' .
				     $db->quote($male_guest) . ', ' .
				     $db->quote($female_guest) . ', ' .
				     $db->quote($total_guest) . ', ' .
				     $db->quote($total_guest) . ', ' .
				     $db->quote($pending_status_id) . ', ' .
				     $db->quote($request_status_id). ', ' .
				     $db->quote(date('Y-m-d H:i:s')). ', ' .
				     $db->quote(time()) . ', ' .
				     $db->quote(date('Y-m-d H:i:s'))
				     );

		// Set the query and execute the insert.
		$db->setQuery($query);

		$db->execute();

		$venue_guest_booking_id = $db->insertid();

		$venueDetail = $this->helper->getVenueDetail($venueID);
		$venueOwner = $venueDetail->user_id;
		$userDetail       = $this->helper->guestUserDetail($this->IJUserID);
		$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_GUEST_BOOKING_REQUEST_TO_VENUE',
								$userDetail->full_name,
								$venueDetail->venue_name,
								$this->helper->convertDateFormat($bookingDate)
							);

		$actor            = $this->IJUserID;
		$target           = $tblVenue->user_id;
		$elementID        = $tblVenue->venue_id;
		$elementType      = "Venue";
		$notificationType = "guestlist.request";
		$formatedBookingTime = "";

		$cid              = $db->insertid();
		$extraParams      = array();
		$extraParams["guestlistID"] = $cid;

		$venue_details       = $this->helper->guestUserDetail($venueDetail->user_id);



		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$venue_guest_booking_id))
		{
			$this->emailHelper->guestlistRequestMail($venueDetail->venue_name,$userDetail->full_name,$this->helper->convertDateFormat($bookingDate),$total_guest,$venue_details->email);
		}

		$this->jsonarray['code']                                = 200;
		$this->jsonarray['pushNotificationData']['id']          = $venue_guest_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
		$this->jsonarray['pushNotificationData']['to']          = $venueOwner;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		//$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_VENUE_GUEST_BOOKING_REQUEST_RECEIVED');
		$this->jsonarray['pushNotificationData']['type']        = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype']  = '';

		return $this->jsonarray;
	}

	function getGuestList()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$venueID      = IJReq::getTaskData('venueID','', 'string');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');

		$tblVenue->load($venueID);

		if(!$tblVenue->venue_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$status_id = $this->helper->getStatusID('accept');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('DISTINCT(booking_date)')
			->from($db->quoteName('#__beseated_venue_guest_booking'))
			->where($db->quoteName('venue_status') . ' = ' . $db->quote($status_id))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->order($db->quoteName('time_stamp') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$guest_bookings = $db->loadColumn();

		if(count($guest_bookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_GUEST_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$guestList = array();


		for ($i = 0; $i <count($guest_bookings) ; $i++)
		{
			$bookingdate = $guest_bookings[$i];

			// Initialiase variables.
			$db    = JFactory::getDbo();

			// $db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('SUM(remaining_guest)')
				->from($db->quoteName('#__beseated_venue_guest_booking'))
				->where($db->quoteName('booking_date') . ' = ' . $db->quote($bookingdate))
				->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
				->where($db->quoteName('venue_status') . ' = ' . $db->quote($status_id))
				->order($db->quoteName('time_stamp') . ' DESC');

			// Set the query and load the result.
			$db->setQuery($query);
			$totalguest_byDate = $db->loadResult();

			$guestList[$i]['bookingDate'] = date('d-m-Y',strtotime($bookingdate));
			$guestList[$i]['totalGuest']  = $totalguest_byDate;

		}

		$this->jsonarray['guests']      = $guestList;
		$this->jsonarray['code']        = 200;
		$this->jsonarray['venueID']     = $venueID;
		$this->jsonarray['totalInPage'] = count($guestList);

		return $this->jsonarray;
	}

	function getGuestListByDate()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingDate      = IJReq::getTaskData('bookingDate','', 'string');
		$venueID          = IJReq::getTaskData('venue_id','', 'string');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenue        = JTable::getInstance('Venue', 'BeseatedTable');

		$tblVenue->load($venueID);

		if(!$tblVenue->venue_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$bookingDate = $this->helper->convertToYYYYMMDD($bookingDate);
		$status_id   = $this->helper->getStatusID('accept');

		$guestLists = array();


		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_guest_booking'))
			->where($db->quoteName('booking_date') . ' = ' . $db->quote($bookingDate))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('venue_status') . ' = ' . $db->quote($status_id))
			->order($db->quoteName('time_stamp') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$guestList = $db->loadObjectList();

		if(count($guestList) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_GUEST_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//echo "<pre/>";print_r($guestList);exit;

		foreach ($guestList as $key => $guest)
		{
			$userDetail = $this->helper->guestUserDetail($guest->user_id);

			$guestLists[$key]['guestBookingID'] = $guest->guest_booking_id;
			$guestLists[$key]['bookingDate']    = date('d-m-Y',strtotime($guest->booking_date));
			$guestLists[$key]['fullName']       = $userDetail->full_name;
			$guestLists[$key]['totalGuest']     = $guest->total_guest;
			$guestLists[$key]['maleGuest']      = $guest->male_guest;
			$guestLists[$key]['femaleGuest']    = $guest->female_guest;
			$guestLists[$key]['remainingGuest'] = $guest->remaining_guest;
		}

		$this->jsonarray['guests']      = $guestLists;
		$this->jsonarray['code']        = 200;
		$this->jsonarray['totalInPage'] = count($guestList);

		return $this->jsonarray;
	}

	function remainingGuest()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$guestBookingID      = IJReq::getTaskData('guestBookingID','', 'string');
		$minusGuests         = IJReq::getTaskData('minusGuests','', 'string');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenueGuestBooking        = JTable::getInstance('GuestBooking', 'BeseatedTable');

		$tblVenueGuestBooking->load($guestBookingID);

		if(!$tblVenueGuestBooking->venue_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_GUEST_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($minusGuests > $tblVenueGuestBooking->total_guest)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_GUEST_MINUS_VALUE'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$remaining_guest = $tblVenueGuestBooking->remaining_guest - $minusGuests;

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_venue_guest_booking'))
			->set($db->quoteName('remaining_guest') . ' = ' . $db->quote($remaining_guest))
			->where($db->quoteName('guest_booking_id') . ' = ' . $db->quote($guestBookingID));

		// Set the query and execute the update.
		$db->setQuery($query);

		$db->execute();

		$this->jsonarray['code']        = 200;
		return $this->jsonarray;
	}

	function changeGuestBookingStatus()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$guestBookingID     = IJReq::getTaskData('guestBookingID',0,'int');
		$statusCode         = IJReq::getTaskData('statusCode',0,'int');
		$userDetail       = $this->helper->guestUserDetail($this->IJUserID);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblGuestBooking = JTable::getInstance('GuestBooking', 'BeseatedTable');
		$tblGuestBooking->load($guestBookingID);

		$tblElement = JTable::getInstance('Venue', 'BeseatedTable');
		$tblElement->load($tblGuestBooking->venue_id);

		if(!$tblGuestBooking->guest_booking_id || !$statusCode)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_GUEST_CHANGE_STATUS_INVALID_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($tblElement->user_id != $this->IJUserID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_OWNER'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($statusCode == 11)
		{
			$tblGuestBooking->guest_status = $this->helper->getStatusID('available');

			$tblGuestBooking->venue_status = $this->helper->getStatusID('accept');

			$notificationType = "guest.request.accepted";
			$title = JText::sprintf(
						'COM_BESEATED_PUSHNOTIFICATION_GUEST_BOOKING_REQUEST_ACCEPTED_BY_VENUE',
						$userDetail->full_name,
						//$tblElement->venue_name,
						$this->helper->convertDateFormat($tblGuestBooking->booking_date)
					);

			$dbTitle = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_GUEST_BOOKING_REQUEST_ACCEPTED_BY_VENUE',
						$tblGuestBooking->total_guest,
						$this->helper->convertDateFormat($tblGuestBooking->booking_date)
					);
		}
		else if($statusCode == 6)
		{

			$tblGuestBooking->guest_status = $this->helper->getStatusID('decline');

			$tblGuestBooking->venue_status = $this->helper->getStatusID('decline');

			$notificationType = "guest.request.declined";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_GUEST_BOOKING_REQUEST_DECLINED_BY_VENUE',
									$userDetail->full_name,
									$tblElement->venue_name,
									$this->helper->convertDateFormat($tblGuestBooking->booking_date)
								);

			$dbTitle = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_GUEST_BOOKING_REQUEST_DECLINED_BY_VENUE',
						$tblGuestBooking->total_guest,
						$this->helper->convertDateFormat($tblGuestBooking->booking_date)
					);
		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_GUEST_INVALID_STATUS'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblGuestBooking->response_date_time = date('Y-m-d H:i:s');
		if(!$tblGuestBooking->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_GUEST_ERROR_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$actor            = $this->IJUserID;
		$target           = $tblGuestBooking->user_id;

		$elementID        = $tblElement->venue_id;
		$elementType      = "Venue";
		$cid              = $tblGuestBooking->guest_booking_id;
		$extraParams      = array();

		$extraParams["guestBookingID"] = $cid;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$dbTitle,$cid,$extraParams,$cid))
		{
			$guestDetail = JFactory::getUser($tblGuestBooking->user_id);

			if($statusCode == 11)
			{
				$this->emailHelper->guestlistRequestAcceptedMail($guestDetail->name,$tblElement->venue_name,$this->helper->convertDateFormat($tblGuestBooking->booking_date),$guestDetail->email);
			}
			else if ($statusCode == 6)
			{
				$this->emailHelper->guestlistRequestDeclinedMail($guestDetail->name,$tblElement->venue_name,$this->helper->convertDateFormat($tblGuestBooking->booking_date),$guestDetail->email);
			}
		}


		$this->jsonarray['code'] = 200;

		/*$pushNotificationTitle = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_GUEST_BOOKING_REQUEST_ACCEPTED_BY_VENUE',
									$tblElement->venue_name,
									$this->helper->convertDateFormat($tblProtectionBooking->booking_date)
								);*/

		$this->jsonarray['pushNotificationData']['id']          = $tblGuestBooking->guest_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		//$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_VENUE_GUEST_BOOKING_REQUEST_STATUS_CHANGED');
		$this->jsonarray['pushNotificationData']['type']        = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype']  = '';

		return $this->jsonarray;
	}

	function sendInvitation()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$emails    = IJReq::getTaskData('emails', '', 'string');
		$fbids     = IJReq::getTaskData('fbids', '', 'string');
		$bookingID = IJReq::getTaskData('bookingID', 0, 'int');

		$loginUser   = JFactory::getUser();
		$inviteeName = $loginUser->name;

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$filterEmails       = BeseatedHelper::filterEmails($emails);
		$filterFbFrndEmails = BeseatedHelper::filterFbIds($fbids);

		$tblVenueBooking      = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$alreadyInvited       = $this->helper->getInvitationDetail($bookingID,'venue');

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
		$emails           = IJReq::getTaskData('emails', '', 'string');
		$emailsArray      = explode(",", $emails);

		$notRegiEmail     = array_udiff($emailsArray, $filterEmails['allRegEmail'],'strcasecmp'); // not registered
		$notRegiEmail     = array_udiff($notRegiEmail, $alreadyInvitedEmails,'strcasecmp');   // not registered and not invited emails

		if(!empty($emails))
		{
			foreach ($filterEmails['guest'] as $key => $guest)
			{
				if($this->my->email == $guest)
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
				if($this->my->email == $guest)
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
		//$newInvitedEmails = array_merge($notRegiEmail,$newInvitedEmails);

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
			$this->jsonarray['message'] = JText::_('COM_IJOOMERADV_BESEATED_ELEMENT_USERS_ALREADY_INVITED');
			$this->jsonarray['code'] = 400;
			return $this->jsonarray;
		}

		$tblVenueBooking->load($bookingID);

		$tblService = JTable::getInstance('Table', 'BeseatedTable');
		$tblService->load($tblVenueBooking->table_id);

		$tblElement = JTable::getInstance('Venue', 'BeseatedTable');
		$tblElement->load($tblVenueBooking->venue_id);

		$userIDs = array();

		foreach ($newInvitedEmails as $key => $email)
		{
			$userID = BeseatedHelper::getUserForSplit($email);

			$tblInvitation = JTable::getInstance('Invitation', 'BeseatedTable');
			$tblInvitation->load(0);
			$invitationPost = array();
			$invitationPost['element_booking_id'] = $bookingID;
			$invitationPost['element_id']         = $tblVenueBooking->venue_id;
			$invitationPost['element_type']       = 'venue';
			$invitationPost['user_id']            = $userID;
			$invitationPost['email']              = $email;

			$userDetail = $this->helper->guestUserDetail($userID);

			if($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id))
			{
				$invitationPost['fbid']               = $userDetail->fb_id;
			}
			else
			{
				$invitationPost['fbid']               = '';
			}

			$invitationPost['user_action']        = 2;
			$invitationPost['time_stamp']         = time();

			$tblInvitation->bind($invitationPost);
			$tblInvitation->store();

			$invitationData = array();

			$tblReadElementBooking = JTable::getInstance('ReadElementBooking', 'BeseatedTable');
			$tblReadElementBooking->load(0);

			$invitationData['booked_type']      = 'invitation';
			$invitationData['element_type']     = 'venue';
			$invitationData['booking_id']       = $tblInvitation->invitation_id;
			$invitationData['from_user_id']     = $this->IJUserID;
			$invitationData['to_user_id']       = ($userID) ? $userID : 0;
			$invitationData['to_user_email_id'] = $email;

			$tblReadElementBooking->bind($invitationData);
			$tblReadElementBooking->store();

			$tblReadElementRsvp = JTable::getInstance('ReadElementRsvp', 'BeseatedTable');
			$tblReadElementRsvp->load(0);
			$tblReadElementRsvp->bind($invitationData);
			$tblReadElementRsvp->store();

			if($tblElement->is_day_club)
			{
				$isNightVenue = 0;
				$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_VENUE_DAY_TABLE_BOOKING_INVITATION',
									$tblService->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date),
									$this->helper->convertToHM($tblVenueBooking->booking_time)
								);
			}
			else
			{
				$isNightVenue = 1;
				$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_VENUE_NIGHT_TABLE_BOOKING_INVITATION',
									$tblService->table_name,
									$this->helper->convertDateFormat($tblVenueBooking->booking_date)
								);
			}


			$userIDs[]        = $userID;


			$notificationType = "venue.service.invitation";

			$actor       = $this->IJUserID;
			$target      = $userID;
			$elementID   = $tblElement->venue_id;
			$elementType = "Venue";
			$cid         = $tblInvitation->invitation_id;
			$extraParams = array();
			$extraParams["venueBookingID"]      = $tblVenueBooking->venue_table_booking_id;
			$extraParams["invitationID"]        = $tblInvitation->invitation_id;
			$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblVenueBooking->venue_table_booking_id,$email);

			$this->jsonarray['pushNotificationData']['id']          = $tblVenueBooking->venue_table_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
			$this->jsonarray['pushNotificationData']['to']          = $target;
			$this->jsonarray['pushNotificationData']['message']     = $title;
			//$pushNotification['pushNotificationData']['type']     = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_VENUE_TABLE_BOOKING_INVITATION_REQUEST');
			$this->jsonarray['pushNotificationData']['type']        = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype']  = '';

			$loginUser   = JFactory::getUser();
			$inviteeName = $loginUser->name;

			$invitedUserName = ($userDetail)?$userDetail->full_name :$email;

			$this->emailHelper->invitationMailUser($invitedUserName,$inviteeName,$tblElement->venue_name,$tblService->table_name,$this->helper->convertDateFormat($tblVenueBooking->booking_date),$this->helper->convertToHM($tblVenueBooking->booking_time),$isNightVenue,$email);

		}


		$tblVenueBooking->has_invitation = 1;
		$tblVenueBooking->store();

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	function replaceShareInvitee()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$invitationID = IJReq::getTaskData('invitationID', 0, 'int');
		$email        = IJReq::getTaskData('email', '', 'string');
		$fbid         = IJReq::getTaskData('fbid', '', 'string');
		$bookingID    = IJReq::getTaskData('bookingID', 0, 'int');

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$filterEmail        = BeseatedHelper::filterEmails($email);
		$filterFbFrndEmail  = BeseatedHelper::filterFbIds($fbid);

		$tblVenueBooking    = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$alreadyInvited     = $this->helper->getSplitedDetail($bookingID,'Venue');

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
		$email            = IJReq::getTaskData('email', '', 'string');
		$emailsArray      = explode(",", $email);

		$notRegiEmail     = array_udiff($emailsArray, $filterEmail['allRegEmail'],'strcasecmp'); // not registered
		$notRegiEmail     = array_udiff($notRegiEmail, $alreadyInvitedEmail,'strcasecmp');   // not registered and not invited emails

		if(!empty($email))
		{
			foreach ($filterEmail['guest'] as $key => $guest)
			{
				if($this->my->email == $guest)
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
				if($this->my->email == $guest)
				{
					continue;
				}
				else if(!in_array($guest, $alreadyInvitedEmail))
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
			$this->jsonarray['message'] = JText::_('COM_IJOOMERADV_BESEATED_ELEMENT_USERS_ALREADY_INVITED');
			$this->jsonarray['code'] = 400;
			return $this->jsonarray;
		}

		$tblVenueBooking->load($bookingID);

		$tblService = JTable::getInstance('Table', 'BeseatedTable');
		$tblService->load($tblVenueBooking->table_id);

		$tblElement = JTable::getInstance('Venue', 'BeseatedTable');
		$tblElement->load($tblVenueBooking->venue_id);

		$email= $newInvitedEmails[0];
		$userID = BeseatedHelper::getUserForSplit($email);
		$tblVenueBookingSplit = JTable::getInstance('VenueBookingSplit', 'BeseatedTable');
		$tblVenueBookingSplit->load($invitationID);

		$splitPost = array();
		$splitPost['user_id']               = $userID;
		$splitPost['is_owner']              = ($userID==$this->IJUserID)?1:0;
		$splitPost['email']                 = $email;

		$userDetail = $this->helper->guestUserDetail($userID);

		if($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id))
		{
			$splitPost['fbid']               = $userDetail->fb_id;
		}
		else
		{
			$splitPost['fbid']               = '';
		}
		$splitPost['split_payment_status']  = 2;
		$splitPost['time_stamp']            = time();
		$tblVenueBookingSplit->bind($splitPost);
		$tblVenueBookingSplit->store();
		$this->deleteReplaceInvitee($bookingID,$invitationID);


		$userDetail       = $this->helper->guestUserDetail($this->IJUserID);
		$actor            = $this->IJUserID;
		$target           = $userID;
		$elementID        = $tblElement->venue_id;
		$elementType      = "Venue";
		$notificationType = "venue.share.invitation.request";

		if($tblElement->is_day_club)
		{
			$title     = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_SHARE_DAY_BOOKING_REQUEST_TO_INVITEE_FOR_VENUE',
								$userDetail->full_name,
								$tblService->table_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date),
							    $this->helper->convertToHM($tblVenueBooking->booking_time)
							);
		}
		else
		{
			$title     = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_SHARE_NIGHT_BOOKING_REQUEST_TO_INVITEE_FOR_VENUE',
								$userDetail->full_name,
								$tblService->table_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date)
							);
		}


		$cid              = $tblVenueBookingSplit->venue_table_booking_split_id;
		$extraParams      = array();
		$extraParams["venueBookingID"] = $tblVenueBooking->venue_table_booking_id;
		$extraParams["invitationID"]   = $tblVenueBookingSplit->venue_table_booking_split_id;
		$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblVenueBooking->venue_table_booking_id,$email);

		$this->jsonarray['code'] = 200;

		$this->jsonarray['pushNotificationData']['id']          = $tblVenueBooking->venue_table_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		//$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_VENUE_SHARE_BOOKING_REQUEST_RECEIVED');
		$this->jsonarray['pushNotificationData']['type']        = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype']  = '';

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	function shareAmount()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$emails    = IJReq::getTaskData('emails', '', 'string');
		$fbids     = IJReq::getTaskData('fbids', '', 'string');
		$bookingID = IJReq::getTaskData('bookingID', 0, 'int');

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$filterEmails         = BeseatedHelper::filterEmails($emails);
		$filterFbFrndEmails   = BeseatedHelper::filterFbIds($fbids);

		$tblVenueBooking      = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$alreadySplited       = $this->helper->getSplitedDetail($bookingID,'Venue');

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
		$emails           = IJReq::getTaskData('emails', '', 'string');
		$emailsArray      = explode(",", $emails);
		$notRegiEmail     = array_udiff($emailsArray, $filterEmails['allRegEmail'],'strcasecmp');
		$notRegiEmail     = array_udiff($notRegiEmail, $alreadySplitedEmails,'strcasecmp');


		if(!empty($emails))
		{
			foreach ($filterEmails['guest'] as $key => $guest)
			{
				if($this->my->email == $guest)
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
				if($this->my->email == $guest)
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

			$this->jsonarray['code'] = 400;
			return $this->jsonarray;
		}

		if(!in_array($this->my->email, $newSplitedEmails) && !in_array($this->my->email, $alreadySplitedEmails))
		{
			$newSplitedEmails[] = $this->my->email;
		}

		$tblVenueBooking->load($bookingID);
		$totalAmountToSplit = $tblVenueBooking->total_price;
		$totalSplitCount    = count($newSplitedEmails) + count($alreadySplited);
		$splittedAmount     = $totalAmountToSplit / $totalSplitCount;

		$tblService = JTable::getInstance('Table', 'BeseatedTable');
		$tblService->load($tblVenueBooking->table_id);

		$tblElement = JTable::getInstance('Venue', 'BeseatedTable');
		$tblElement->load($tblVenueBooking->venue_id);

		$userIDs = array();

		foreach ($newSplitedEmails as $key => $email)
		{
			$userID = BeseatedHelper::getUserForSplit($email);
			$tblVenueBookingSplit = JTable::getInstance('VenueBookingSplit', 'BeseatedTable');
			$tblVenueBookingSplit->load(0);
			$splitPost = array();
			$splitPost['venue_table_booking_id'] = $bookingID;
			$splitPost['venue_id']               = $tblVenueBooking->venue_id;
			$splitPost['table_id']               = $tblVenueBooking->table_id;
			$splitPost['user_id']                = $userID;
			$splitPost['is_owner']               = ($userID==$this->IJUserID)?1:0;
			$splitPost['email']                  = $email;

			$userDetail = $this->helper->guestUserDetail($userID);

			if($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id))
			{
				$splitPost['fbid']               = $userDetail->fb_id;
			}
			else
			{
				$splitPost['fbid']               = '';
			}

			$splitPost['split_payment_status']  = 2;
			$splitPost['time_stamp']            = time();
			$tblVenueBookingSplit->bind($splitPost);
			$tblVenueBookingSplit->store();

			if($userID !== $this->IJUserID)
			{
				$invitationData = array();

				$tblReadElementBooking = JTable::getInstance('ReadElementBooking', 'BeseatedTable');
				$tblReadElementBooking->load(0);

				$invitationData['booked_type']      = 'share';
				$invitationData['element_type']     = 'venue';
				$invitationData['booking_id']       = $tblVenueBookingSplit->venue_table_booking_split_id;
				$invitationData['from_user_id']     = $this->IJUserID;
				$invitationData['to_user_id']       = ($userID) ? $userID : 0;
				$invitationData['to_user_email_id'] = $email;

				$tblReadElementBooking->bind($invitationData);
				$tblReadElementBooking->store();

				$tblReadElementRsvp = JTable::getInstance('ReadElementRsvp', 'BeseatedTable');
				$tblReadElementRsvp->load(0);
				$tblReadElementRsvp->bind($invitationData);
				$tblReadElementRsvp->store();
			}


			if($userID !== $this->IJUserID)
			{
				$userIDs[]        = $userID;

				$userDetail       = $this->helper->guestUserDetail($this->IJUserID);
				$actor            = $this->IJUserID;
				$target           = $userID;
				$elementID        = $tblElement->venue_id;
				$elementType      = "Venue";
				$notificationType = "venue.share.invitation.request";

				if($tblElement->is_day_club)
				{
					$title   = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_SHARE_DAY_BOOKING_REQUEST_TO_INVITEE_FOR_VENUE'
								/*$userDetail->full_name,
								$tblService->table_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date),
							    $this->helper->convertToHM($tblVenueBooking->booking_time)*/
							);

					$dbTitle   = JText::sprintf(
								'COM_BESEATED_DB_NOTIFICATION_SHARE_DAY_BOOKING_REQUEST_TO_INVITEE_FOR_VENUE',
								$tblService->table_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date),
							    $this->helper->convertToHM($tblVenueBooking->booking_time)
							);
				}
				else
				{
					$title   = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_SHARE_NIGHT_BOOKING_REQUEST_TO_INVITEE_FOR_VENUE'
								/*$userDetail->full_name,
								$tblService->table_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date)*/
							);

					$dbTitle   = JText::sprintf(
								'COM_BESEATED_DB_NOTIFICATION_SHARE_NIGHT_BOOKING_REQUEST_TO_INVITEE_FOR_VENUE',
								$tblService->table_name,
								$this->helper->convertDateFormat($tblVenueBooking->booking_date)
							);
				}


				$cid              = $tblVenueBookingSplit->venue_table_booking_split_id;
				$extraParams      = array();
				$extraParams["venueBookingID"] = $tblVenueBooking->venue_table_booking_id;
				$extraParams["invitationID"]   = $tblVenueBookingSplit->venue_table_booking_split_id;
				$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$dbTitle,$cid,$extraParams,$tblVenueBooking->venue_table_booking_id,$email);
			}
		}

		if(!empty($userIDs))
		{
			$this->jsonarray['pushNotificationData']['id']         = $tblVenueBooking->venue_table_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Venue';
			$this->jsonarray['pushNotificationData']['to']         = implode(',', $userIDs);
			$this->jsonarray['pushNotificationData']['message']    = $title;
			//$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_VENUE_SHARE_BOOKING_REQUEST_RECEIVED');
			$this->jsonarray['pushNotificationData']['type']       = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype'] = '';
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_venue_table_booking_split'))
			->set($db->quoteName('splitted_amount') . ' = ' . $db->quote($tblVenueBooking->each_person_pay))
			->where($db->quoteName('venue_table_booking_id') . ' = ' . $db->quote($bookingID));

		// Set the query and execute the update.
		$db->setQuery($query);
		$db->execute();

		$tblVenueBooking->is_splitted      = 1;
		//$tblVenueBooking->each_person_pay  = $splittedAmount;
		$tblVenueBooking->splitted_count   = $totalSplitCount;
		//$tblVenueBooking->remaining_amount = $totalAmountToSplit;
		$tblVenueBooking->store();

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	function bookingShowAction()
	{
		$my = JFactory::getUser();

		if(!$my->id)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$venueBookingID     = IJReq::getTaskData('bookingID',0,'int');
		$showAction         = IJReq::getTaskData('showAction',0,'int');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblVenueBooking->load($venueBookingID);

		$tblVenue = JTable::getInstance('Venue', 'BeseatedTable');
		$tblVenue->load($tblVenueBooking->venue_id);

		$tblTable = JTable::getInstance('Table', 'BeseatedTable');
		$tblTable->load($tblVenueBooking->table_id);

		if($showAction == 1)
		{
			$tblVenueBooking->is_show = 1;
			$tblVenueBooking->is_noshow = 0;
		}
		else if($showAction == 0)
		{
			$tblVenueBooking->is_show = 0;
			$tblVenueBooking->is_noshow = 1;

			$booking_owner = JFactory::getUser($tblVenueBooking->user_id);

		    $this->emailHelper->NoShowVenueUserMail($booking_owner->name,$tblVenue->venue_name,$this->helper->convertDateFormat($tblVenueBooking->booking_date),$this->helper->convertToHM($tblVenueBooking->booking_time),$tblVenue->is_day_club,$booking_owner->email);
		    $this->emailHelper->NoShowLuxuryManagerMail($booking_owner->name,$tblVenue->venue_name,$my->email,'Venue',$tblTable->table_name);
		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_ERROR_IN_SHOW_ACTION_INVALID_SHOW_ACTION'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//$tblProtectionBooking->show_action = $showAction;
		if(!$tblVenueBooking->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_ERROR_IN_SHOW_ACTION'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}



		$this->jsonarray['code'] = 200;

		return $this->jsonarray;
	}

	function getRevenue()
	{
		$my = JFactory::getUser();

		if(!$my->id)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$pageNO    = IJReq::getTaskData('pageNO',0);
		$pageLimit = BESEATED_GENERAL_LIST_LIMIT;

		if($pageNO==0 || $pageNO==1)
		{
			$startFrom=0;
		}
		else
		{
			$startFrom = $pageLimit*($pageNO-1);
		}

		$venue = $this->helper->venueUserDetail($my->id);

		$bookingStatus = array();
		$bookingStatus[] = $this->helper->getStatusID('booked');
		//$bookingStatus[] = $this->helper->getStatusID('confirmed');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('vtb.*')
			->from($db->quoteName('#__beseated_venue_table_booking') . ' AS vtb')
			->where($db->quoteName('vtb.venue_id') . ' = ' . $db->quote($venue->venue_id))
			->where($db->quoteName('vtb.venue_status') . ' IN ('.implode(',', $bookingStatus).')')
			->order($db->quoteName('vtb.booking_date') . ' ASC,'.$db->quoteName('vtb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.is_deleted')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=vtb.user_id');

		$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=vtb.venue_status');

		$query->select('vt.table_name,vt.thumb_image,vt.image')
			->join('LEFT','#__beseated_venue_table AS vt ON vt.table_id=vtb.table_id');

		// Set the query and load the result.
		//$db->setQuery($query,$startFrom,$pageLimit);
		$db->setQuery($query);

		$resBookings = $db->loadObjectList();

		if(count($resBookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$blacklistedUsers = $this->helper->getBlackListedUser($venue->venue_id,'Venue');

		$resultRevenues = array();
		$revenuePrice = 0;

		foreach ($resBookings as $key => $booking)
		{
			$temp                          = array();
			$temp['venueBookingID']        = $booking->venue_table_booking_id;
			$temp['fullName']              = $booking->full_name;
			$temp['phone']                 = $booking->phone;
			$temp['userID']                = $booking->user_id;
			$temp['isDeletedUser']         = $booking->is_deleted;

			$threadId = $this->helper->getThreadId($booking->user_id);

			$temp['connectionID']          = ($threadId) ? $threadId : "0";
			$temp['serviceName']           = $booking->table_name;
			//$temp['thumbImage']          = ($booking->thumb_image)?JUri::root().'image/beseated/'.$booking->thumb_image:'';
			//$temp['image']               = ($booking->image)?JUri::root().'image/beseated/'.$booking->image:'';

			$temp['bookingDate']           = $this->helper->convertDateFormat($booking->booking_date);
			//$temp['pricePerHours']         = $booking->price_per_hours;
			//$temp['totalHours']            = $booking->total_hours;
			//$temp['bookingTime']         = $this->helper->convertToHM($booking->booking_time);
			$temp['formatedCurrency']      = $this->helper->currencyFormat($booking->booking_currency_sign,$booking->total_price);
			//$temp['bookingCurrencySign'] = $booking->booking_currency_sign;
			//$temp['bookingCurrencyCode'] = $booking->booking_currency_code;
			//$temp['pricePerHours']       = $booking->price_per_hours;
			//$temp['totalGuard']          = $booking->total_guard;
			$temp['totalGuest']            = $booking->total_guest;
			$temp['maleGuest']             = $booking->male_guest;
			$temp['femaleGuest']           = $booking->female_guest;

			if(in_array($booking->user_id, $blacklistedUsers))
				$temp['isBlacklisted'] = "1";
			else
				$temp['isBlacklisted'] = "0";

			$temp['bookingCurrencyCode'] = $booking->booking_currency_code;
			$temp['totalAmount']         = str_replace(' ', '', $this->helper->currencyFormat('',$booking->total_price + $booking->pay_deposite));
			$revenuePrice                = $revenuePrice + $booking->total_price +  $booking->pay_deposite;
			$resultRevenues[]            = $temp;
		}

		if(count($resultRevenues) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_REVENUE_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray['code']                = 200;
		$this->jsonarray['revenueCount']        = count($resultRevenues);
		$this->jsonarray['bookingCurrencyCode'] = $booking->booking_currency_code;
		$this->jsonarray['revenues']            = $resultRevenues;
		$this->jsonarray['totalRevenue']        = str_replace(' ', '', $this->helper->currencyFormat('',$revenuePrice));

		return $this->jsonarray;
	}

	function deleteReplaceInvitee($bookingID,$invitationID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->select('extra_pramas,notification_id')
			->from($db->quoteName('#__beseated_notification'))
			->where($db->quoteName('notification_type') . ' = ' . $db->quote('venue.share.invitation.request'))
			->where($db->quoteName('actor') . ' = ' . $db->quote((int) $this->IJUserID))
			->where($db->quoteName('cid') . ' = ' . $db->quote((int) $invitationID));

		// Set the query and execute the delete.
		$db->setQuery($query);

		$notif_data = $db->loadObjectList();

		foreach ($notif_data as $key => $value)
		{
			$extra_pramas = $value->extra_pramas;

			if(json_decode($extra_pramas)->venueBookingID == $bookingID)
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();

				// $db    = $this->getDbo();
				$query = $db->getQuery(true);

				// Create the base delete statement.
				$query->delete()
					->from($db->quoteName('#__beseated_notification'))
					->where($db->quoteName('notification_id') . ' = ' . $db->quote((int) $value->notification_id));

				// Set the query and execute the delete.
				$db->setQuery($query);

				$db->execute();
			}

		}

		return true;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"addUserToBlackList","taskData":{"userID":"0"}}
	 */
	function addUserToBlackList()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$venue = $this->helper->venueUserDetail($this->IJUserID);
		$userID    = IJReq::getTaskData('userID',0, 'int');

		if(!$userID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_USER_FOR_BLACKLIST_ADD'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$isBlackListed = $this->helper->checkBlackList($userID,$venue->venue_id,'Venue');

		if($isBlackListed)
		{
			IJReq::setResponseCode(707);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_USER_ALREDY_ADD_IN_BLACKLIST'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$result = $this->helper->addUserToBlackList($userID,$venue->venue_id,'Venue');

		if($result)
		{
			$this->jsonarray['code'] = 200;
		}
		else
		{
			$this->jsonarray['code'] = 500;
		}

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","extTask":"removeUserFromBlackList","taskData":{"userID":"0"}}
	 */
	function removeUserFromBlackList()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$venue = $this->helper->venueUserDetail($this->IJUserID);
		$userID    = IJReq::getTaskData('userID',0, 'int');

		if(!$userID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_USER_FOR_BLACKLIST_ADD'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$isBlackListed = $this->helper->removeUserFromBlackList($userID,$venue->venue_id,'Venue');
		$this->jsonarray['code'] = 200;

		return $this->jsonarray;
	}

	/* @example the json string will be like, : 1) only for check avaibility of table on date and time
	 * {"extName":"beseated","extView":"venue","extTask":"testing","taskData":{"venueID":"4","tableID":"28","bookingDate":"2015-12-04","bookingTime":"12:00"}}
	 */

	/* @example the json string will be like, : only for check working date of venue
	 * {"extName":"beseated","extView":"venue","extTask":"testing","taskData":{"date":""}}
	 */
	function testing()
	{
		// 1) Following code is for check Available of table
		/*$venueID     = IJReq::getTaskData('venueID',0, 'int');
		$tableID     = IJReq::getTaskData('tableID',0, 'int');
		$bookingDate = IJReq::getTaskData('bookingDate','', 'string');
		$bookingTime = IJReq::getTaskData('bookingTime','', 'string');
		$bookingTime = $this->helper->convertToHMS($bookingTime);
		$spender = $this->helper->checkForVenueTableAvaibility($venueID,$tableID,$bookingDate,$bookingTime,'');
		echo "<pre>";
		print_r($spender);
		echo "</pre>";
		exit;*/

		// 2) Following code is for check venue is working on this date
		/*$allDays = array('Monday' => 1,'Tuesday' => 2,'Wednesday' => 3,'Thursday' => 4,'Friday' => 5,'Saturday' => 6,'Sunday' => 7);
		$date    = IJReq::getTaskData('date','', 'string');
		if($date==''){ $t = date('d-m-Y'); } else { $t = date('d-m-Y',strtotime($date)); }
		$dayName = date('l',strtotime($t));
		$dayName = ucfirst($dayName);
		$dayNum = $allDays[$dayName];
		$this->jsonarray['dayName'] = $dayName;
		$this->jsonarray['dayNum'] = $dayNum;
		return $this->jsonarray;*/

		// 3) Generate pass key
		$this->jsonarray['code'] = 200;
		$this->jsonarray['uniqueCode'] = uniqid();
		$this->jsonarray['uniqueCodeLength'] = strlen($this->jsonarray['uniqueCode']);

		return $this->jsonarray;
	}

	function deleteBooking()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$bookingID   = IJReq::getTaskData('bookingID', 0, 'int');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblVenueBooking->load($bookingID);

		$tblElement = JTable::getInstance('Venue', 'BeseatedTable');
		$tblElement->load($tblVenueBooking->venue_id);


		if(!$tblVenueBooking->venue_table_booking_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($tblElement->user_id != $this->IJUserID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_CHAUFFEUR_SERVICE_OR_COMPANY_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblVenueBooking->deleted_by_venue = 1;

		if(!$tblVenueBooking->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	function getVenueBottleBooking()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$venue_table_booking_id   = IJReq::getTaskData('tableBookingID', 0, 'int');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
		$tblVenueBooking->load($venue_table_booking_id);

		if(!$tblVenueBooking->venue_table_booking_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_VENUE_TABLE_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.*,b.brand_name,b.size,b.bottle_type')
			->from($db->quoteName('#__beseated_venue_bottle_booking').'AS a')
			->where($db->quoteName('a.venue_table_booking_id') . ' = ' . $db->quote($venue_table_booking_id))
			->join('LEFT', '#__beseated_venue_bottle AS b ON b.bottle_id=a.bottle_id')
			->order($db->quoteName('time_stamp') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$bottle_booking = $db->loadObjectList();

		if(count($bottle_booking) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_REVENUE_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$totalPriceBottleBooking = '';

		foreach ($bottle_booking as $key => $bottleBooking)
		{
			$this->jsonarray['bottleBooking'][$key]['bottleID']            = $bottleBooking->bottle_id;
			$this->jsonarray['bottleBooking'][$key]['userID']              = $bottleBooking->user_id;
			$this->jsonarray['bottleBooking'][$key]['qty']                 = $bottleBooking->qty;
			$this->jsonarray['bottleBooking'][$key]['price']               = number_format($bottleBooking->price,0);
			$this->jsonarray['bottleBooking'][$key]['totalPrice']          = number_format($bottleBooking->total_price,0);
			$this->jsonarray['bottleBooking'][$key]['date']                = date('d-m-Y',strtotime($bottleBooking->created));
			$this->jsonarray['bottleBooking'][$key]['bottleType']          = $bottleBooking->bottle_type;
			$this->jsonarray['bottleBooking'][$key]['bottleSize']          = $bottleBooking->size;
			$this->jsonarray['bottleBooking'][$key]['bottleBrand']         = $bottleBooking->brand_name;
			$this->jsonarray['bottleBooking'][$key]['bookingCurrencyCode'] = $tblVenueBooking->booking_currency_code;


			$totalPriceBottleBooking+=$bottleBooking->total_price;
		}

		$this->jsonarray['totalBottleBookingPrice'] = number_format($totalPriceBottleBooking,0);
		$this->jsonarray['bookingCurrencyCode'] = $tblVenueBooking->booking_currency_code;

		//echo "<pre/>";print_r($temp);exit;

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	function hasBottleAdded($venue_table_booking_id)
	{

		// Initialiase variables.
		$db    = JFactory::getDbo();

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('COUNT(a.venue_bottle_booking_id)')
			->from($db->quoteName('#__beseated_venue_bottle_booking').'AS a')
			->where($db->quoteName('a.venue_table_booking_id') . ' = ' . $db->quote($venue_table_booking_id));

		// Set the query and load the result.
		$db->setQuery($query);
		$count_bottle_booking = $db->loadResult();

		return $count_bottle_booking;
	}

	public function getBookedTableDate($venueID)
	{
		$venueID   = IJReq::getTaskData('venueID','','string');
		$tableID   = IJReq::getTaskData('tableID','','string');

		$bookingStatus[] = $this->helper->getStatusID('booked');
		$bookingStatus[] = $this->helper->getStatusID('confirmed');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.booking_date')
			->from($db->quoteName('#__beseated_venue_table_booking').'AS a')
			->where($db->quoteName('a.venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('a.table_id') . ' = ' . $db->quote($tableID))
			->where($db->quoteName('a.booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('a.venue_status') . ' IN ('.implode(',', $bookingStatus).')')
			->where($db->quoteName('v.is_day_club') . ' = ' . $db->quote('0'))
			->join('INNER','#__beseated_venue AS v ON v.venue_id=a.venue_id')
			->order($db->quoteName('a.booking_date') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$bookedDates = $db->loadColumn();

		if(count($bookedDates) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_BESEATED_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$bookedDate = array();

		foreach ($bookedDates as $key => $date)
		{
			$bookedDate[] = date('d-m-Y',strtotime($date));
		}

		$this->jsonarray['bookingDates'] = array_values(array_unique($bookedDate));
		$this->jsonarray['code'] = 200;


		return $this->jsonarray;


	}


}
