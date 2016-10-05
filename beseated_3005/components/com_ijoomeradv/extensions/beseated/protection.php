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
class protection
{

	private $db;
	private $IJUserID;
	private $helper;
	private $jsonarray;
	private $emailHelper;
	private $my;

	function __construct()
	{
		$this->db        = JFactory::getDBO();
		$this->mainframe =  JFactory::getApplication ();
		$this->IJUserID  = $this->mainframe->getUserState ( 'com_ijoomeradv.IJUserID', 0 ); //get login user id
		$this->my        = JFactory::getUser ( $this->IJUserID ); // set the login user object
		$this->helper    = new beseatedAppHelper;
		$this->jsonarray = array();

		require_once JPATH_SITE . '/components/com_beseated/helpers/beseated.php';
		require_once JPATH_SITE . '/components/com_beseated/helpers/email.php';

		$this->emailHelper            = new BeseatedEmailHelper;

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
	 * 	{"extName":"beseated","extView":"protection","extTask":"getProtections","taskData":{"query":"string","city":"","latitude":"1","longitude":"1","pageNO":"0"}}
	 */
	function getProtections()
	{
		$city      = IJReq::getTaskData('city','','string');
		$searchQuery     = IJReq::getTaskData('query','','string');
		$latitude  = IJReq::getTaskData('latitude','','string');
		$longitude = IJReq::getTaskData('longitude','','string');
		$pageNO    = IJReq::getTaskData('pageNO',0);
		$pageLimit = BESEATED_YACHT_LIST_LIMIT;

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

		$query->from($db->quoteName('#__beseated_protection'))
			->where($db->quoteName('has_service') . ' = ' . $db->quote('1'))
			->where($db->quoteName('published') . ' = ' . $db->quote('1'));
			//->order($db->quoteName('protection_name') . ' ASC');

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
			$query->where($db->quoteName('protection_name') .' LIKE ' . $db->quote('%'.$searchQuery.'%'));
		}

		if(!empty($latitude) && !empty($longitude))
		{
			$sqlString = $query;
			$sqlString .= ' GROUP BY protection_id HAVING distance<'.COM_IJOOMERADV_BESEATED_RADIOUS;
			$sqlString .= ' ORDER BY protection_name ASC';
			$query = $sqlString;

		}
		else
		{
			$query->order($db->quoteName('protection_name') . ' ASC');
		}

		if($this->IJUserID)
		{
			$protectionIDs = $this->helper->getBlackListedElementOfUser($this->IJUserID,'Protection');
			if(count($protectionIDs) != 0)
			{
				$query->where($db->quoteName('protection_id') .' NOT IN ('.implode(",", $protectionIDs).')');
			}
		}

		$db->setQuery($query,$startFrom,$pageLimit);
		$resProtections = $db->loadObjectList();

		if(count($resProtections) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$resultProtections =  array();
		$resultProtectionIDs = array();
		foreach ($resProtections as $key => $protection)
		{
			$temp                   = array();
			$resultProtectionIDs[]  = $protection->protection_id;
			$temp['protectionID']   = $protection->protection_id;
			$temp['protectionName'] = $protection->protection_name;
			$temp['location']       = $protection->location;
			$temp['city']           = $protection->city;
			$temp['ratting']        = $protection->avg_ratting;
			$temp['latitude']       = $protection->latitude;
			$temp['longitude']      = $protection->longitude;
			$resultProtections[]    = $temp;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('element_id') . ' IN ('.implode(',', $resultProtectionIDs).')')
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Protection'))
			->order($db->quoteName('element_type') . ' ASC ,' . $db->quoteName('element_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$resultProtectionImages = $db->loadObjectList();
		$allProtectionImages    = array();
		$corePath          = JUri::base().'images/beseated/';

		foreach ($resultProtectionImages as $key => $protectionImage)
		{
			$tempImg = array();

			if($protectionImage->is_video)
			{
				$tempImg['thumbImage'] = ($protectionImage->thumb_image)?$corePath.$protectionImage->thumb_image:'';
			}
			else
			{
				$tempImg['thumbImage'] = ($protectionImage->image)?$corePath.$protectionImage->image:'';
			}

			//$tempImg['thumbImage'] = ($protectionImage->thumb_image)?$corePath.$protectionImage->thumb_image:'';
			$tempImg['image'] = ($protectionImage->image)?$corePath.$protectionImage->image:'';
			$tempImg['isVideo']    = $protectionImage->is_video;
			$tempImg['isDefault']  = $protectionImage->is_default;
			$allProtectionImages[$protectionImage->element_id][] = $tempImg;
		}

		foreach ($resultProtections as $key => $yacht)
		{
			if(isset($allProtectionImages[$yacht['protectionID']]))
			{
				$resultProtections[$key]['images'] = $allProtectionImages[$yacht['protectionID']];
			}
			else
			{
				$resultProtections[$key]['images'] = array();
			}
		}

		$this->jsonarray['code']        = 200;
		$this->jsonarray['totalInPage'] = count($resultProtections);
		$this->jsonarray['pageLimit']   = $pageLimit; //BESEATED_PROTECTION_LIST_LIMIT;
		$this->jsonarray['protections'] = $resultProtections;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"protection","extTask":"getProtectionDetail","taskData":{"protectionID":"1"}}
	 */
	function getProtectionDetail()
	{
		$protectionID   = IJReq::getTaskData('protectionID','','string');
		if(!$protectionID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_CHAUFFEUR_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$defaultImage = JUri::base().'images/beseated/elementDefaultimage.png';

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__beseated_protection'))
			->where($db->quoteName('protection_id') . ' = ' . $db->quote($protectionID));

		$db->setQuery($query,0,1);
		$resProtections = $db->loadObjectList();
		if(count($resProtections) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$resultProtections =  array();
		$resultProtectionIDs = array();
		$userType = $this->helper->getUserType($this->IJUserID);
		if($userType == 'Guest')
		{
			$favouritIDs = $this->helper->getUserFavourites($this->IJUserID,'Protection');
		}
		//$this->helper->getUserFavourites()
		foreach ($resProtections as $key => $protection)
		{
			$temp                   = array();
			$resultProtectionIDs[]  = $protection->protection_id;
			$temp['protectionID']   = $protection->protection_id;
			$temp['protectionName'] = $protection->protection_name;
			$temp['currencyCode']   = $protection->currency_code;
			$temp['location']       = $protection->location;
			$temp['city']           = $protection->city;
			$temp['ratting']        = $protection->avg_ratting;
			$temp['latitude']       = $protection->latitude;
			$temp['longitude']      = $protection->longitude;

			if($userType == 'Guest' && in_array($protection->protection_id, $favouritIDs))
			{
				$temp['isFavourite'] = 1;
			}
			else if($userType == 'Guest')
			{
				$temp['isFavourite'] = 0;
			}

			$resultProtections[]    = $temp;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('element_id') . ' IN ('.implode(',', $resultProtectionIDs).')')
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Protection'))
			->order($db->quoteName('image_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$resultProtectionImages = $db->loadObjectList();

		$this->helper->array_sort_by_column($resultProtectionImages,'is_default',3);

		$allProtectionImages    = array();
		$corePath          = JUri::base().'images/beseated/';
		foreach ($resultProtectionImages as $key => $protectionImage)
		{
			$tempImg = array();

			if($protectionImage->is_video)
			{
				$tempImg['thumbImage'] = ($protectionImage->thumb_image)?$corePath.$protectionImage->thumb_image:$defaultImage;
			}
			else
			{
				$tempImg['thumbImage'] = ($protectionImage->image)?$corePath.$protectionImage->image:$defaultImage;
			}

			//$tempImg['thumbImage'] = ($protectionImage->thumb_image) ? $corePath.$protectionImage->thumb_image : $defaultImage;
			$tempImg['image']      = ($protectionImage->image) ? $corePath.$protectionImage->image : $defaultImage;
			$temp['isVideo']       = $protectionImage->is_video;
			$temp['isDefault']     = $protectionImage->is_default;
			$allProtectionImages[$protectionImage->element_id][] = $tempImg;
		}


		foreach ($resultProtections as $key => $protection)
		{
			if(isset($allProtectionImages[$protection['protectionID']]))
			{
				$resultProtections[$key]['images'] = $allProtectionImages[$protection['protectionID']];
			}
			else
			{
				$resultProtections[$key]['images'] = array();
				$resultProtections[$key]['images'][$key]['thumbImage']    = $defaultImage;
				$resultProtections[$key]['images'][$key]['image']         = $defaultImage;
				$resultProtections[$key]['images'][$key]['isVideo']       = "0";
				$resultProtections[$key]['images'][$key]['isDefault']     = "1";
			}
		}

		$queryRatings = $db->getQuery(true);

		// Create the base select statement.
		$queryRatings->select('r.rating_id,r.user_id,r.avg_rating,r.food_rating,r.service_rating,r.atmosphere_rating,r.value_rating,r.rating_count,r.rating_comment,r.created')
			->from($db->quoteName('#__beseated_rating','r'))
			->where($db->quoteName('r.element_id') . ' = '.$db->quote($protectionID))
			->where($db->quoteName('r.element_type') . ' = ' . $db->quote('protection'))
			->order($db->quoteName('r.time_stamp') . ' ASC');

		$queryRatings->select('bu.full_name,bu.avatar,bu.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.user_id=r.user_id');

		// Set the query and load the result.
		$db->setQuery($queryRatings);
		$resProtectionRatings = $db->loadObjectList();
		$allProtectionRatings    = array();
		foreach ($resProtectionRatings as $key => $rating)
		{
			$tempRating                     = array();
			$tempRating['avgrating']        = $rating->avg_rating;
			$tempRating['foodRating']       = $rating->food_rating;
			$tempRating['serviceRating']    = $rating->service_rating;
			$tempRating['atmosphereRating'] = $rating->atmosphere_rating;
			$tempRating['valueRating']      = $rating->value_rating;
			$tempRating['comment']          = $rating->rating_comment;
			$tempRating['created']          = $this->helper->convertDateFormat($rating->created);
			$tempRating['avatar']           = ($rating->avatar)?$this->helper->getUserAvatar($rating->avatar):'';
			$tempRating['thumbAvatar']      = ($rating->thumb_avatar)?$this->helper->getUserAvatar($rating->thumb_avatar):'';
			$tempRating['fullName']         = $rating->full_name;

			$allProtectionRatings[$protectionID][] = $tempRating;
		}

		foreach ($resultProtections as $key => $protection)
		{
			if(isset($allProtectionRatings[$protection['protectionID']]))
			{
				$resultProtections[$key]['ratings'] = $allProtectionRatings[$protection['protectionID']];
			}
			else
			{
				$resultProtections[$key]['ratings'] = array();
			}
		}


		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_protection_services'))
			->where($db->quoteName('protection_id') . ' = ' . $db->quote($protectionID))
			->where($db->quoteName('published') . ' = ' . $db->quote(1))
			->order($db->quoteName('price_per_hours') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$resServices = $db->loadObjectList();
		$resultServices = array();
		foreach ($resServices as $key => $service)
		{
			$temp                  = array();
			$temp['serviceID']     = $service->service_id;
			$temp['protectionID']  = $service->protection_id;
			$temp['serviceName']   = $service->service_name;
			$temp['pricePerHours'] = $this->helper->currencyFormat('',$service->price_per_hours);
			$temp['minHours']      = $service->min_hours;
			$temp['thumbImage']    = ($service->thumb_image)?$corePath.$service->thumb_image:'';
			$temp['image']         = ($service->image)?$corePath.$service->image:'';
			$resultServices[]      = $temp;
		}

		if($protectionID)
		{
			$resultProtections = $resultProtections[0];
			$resultProtections['services'] = $resultServices;
		}

		$this->jsonarray['code']      = 200;
		$this->jsonarray['protectionDetail']    = $resultProtections;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"addService","taskData":{"serviceID":"2","serviceName":"Protection Service Two","pricePerHours":"50","protectionID":"1"}}
	 *
	 */
	function addService()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$serviceID     = IJReq::getTaskData('serviceID',0,'int');
		$serviceName   = IJReq::getTaskData('serviceName','','string');
		$pricePerHours = IJReq::getTaskData('pricePerHours',0,'int');
		$minHours      = IJReq::getTaskData('minHours',0,'int');
		$protectionID  = IJReq::getTaskData('protectionID',0,'int');

		if(!$protectionID || !$pricePerHours)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_PROTECTION_SERVICE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		if(empty($serviceName))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_PROTECTION_SERVICE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
		$tblService->load($serviceID);

		$data['protection_id']   = $protectionID;
		$data['service_name']    = $serviceName;
		$data['price_per_hours'] = $pricePerHours;
		$data['min_hours']       = $minHours;
		$data['published']       = 1;
		$data['time_stamp']      = time();

		$file = JRequest::getVar('image','','FILES','array');

		if(is_array($file) && isset($file['size']) && $file['size']>0)
		{
			$defualtPath = JPATH_ROOT . '/images/beseated/';
			$tableImage = $this->helper->uplaodServiceImage($file,'Protection',$protectionID);

			if(!empty($tableImage))
			{
				if(!empty($tblService->image) && file_exists($defualtPath.$tblService->image))
				{
					unlink($defualtPath.$tblService->image);
				}

				if(!JFolder::exists($defualtPath.'Protection/'.$protectionID.'/Services/thumb'))
				{
					JFolder::create($defualtPath.'Protection/'.$protectionID .'/Services/thumb');
				}

				$pathInfo            = pathinfo($defualtPath.$tableImage);
				$thumbPath           =  $pathInfo['dirname'].'/thumb/thumb_'.$pathInfo['basename'];
				$storeThumbPath      = 'Protection/'. $protectionID . '/Services/thumb/thumb_'.$pathInfo['basename'];
				$this->helper->createThumb($defualtPath.$tableImage,$thumbPath);
				$data['image']       = $tableImage;
				$data['thumb_image'] = $storeThumbPath;

				if(!empty($tblService->thumb_image) && file_exists($defualtPath.$tblService->thumb_image))
				{
					unlink($defualtPath.$tblService->thumb_image);
				}
			}
		}

		$tblService->bind($data);

		if(!$tblService->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$tblProtection = JTable::getInstance('Protection','BeseatedTable');
		$tblProtection->load($protectionID);
		$tblProtection->has_service = 1;
		$tblProtection->store();

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"deleteService","taskData":{"serviceID":"0","protectionID":"1"}}
	 *
	 */
	function deleteService()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$serviceID   = IJReq::getTaskData('serviceID',0,'int');
		$protectionID = IJReq::getTaskData('protectionID',0,'int');
		$elementID   = $protectionID;

		if(!$protectionID || !$serviceID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_CHAUFFEUR_SERVICE_DELETE_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
		$tblService->load($serviceID);

		if(!$tblService->service_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_CHAUFFEUR_SERVICE_DELETE_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$tblService->published = 0;

		if(!$tblService->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$this->helper->checkForActiveSubElement($elementID, 'Protection');

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"bookProtectionService","taskData":{"serviceID":"1","protectionID":"1","bookingDate":"2015-10-15","bookingTime":"10:00","meetupLocation":"Ahemdabad","totalHours":"5","totalGuard":"5"}}
	 *
	 */
	function bookProtectionService()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$protectionBookingPost = array();
		$serviceID             = IJReq::getTaskData('serviceID',0,'int');
		$elementID             = IJReq::getTaskData('protectionID',0,'int');
		$bookingDate           = IJReq::getTaskData('bookingDate','','string');
		$bookingTime           = IJReq::getTaskData('bookingTime','','string');
		$meetUpLocation        = IJReq::getTaskData('meetupLocation','','string');
		$totalHours            = IJReq::getTaskData('totalHours',0,'int');
		$totalGuard            = IJReq::getTaskData('totalGuard',0,'int');
		$maleGuest             = IJReq::getTaskData('maleGuest',0,'int');
		$femaleGuest           = IJReq::getTaskData('femaleGuest',0,'int');
		$date                  = date('d F Y',strtotime($bookingDate));

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
		$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
		$tblService->load($serviceID);
		$tblElement = JTable::getInstance('Protection', 'BeseatedTable');
		$tblElement->load($elementID);

		if(!$tblService->service_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_SERVICE_FOR_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!$tblElement->protection_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_COMPANY_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($tblElement->protection_id != $tblService->protection_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_SERVICE_OR_COMPANY_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($this->helper->isPastDate($bookingDate))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_INVALID_DATE_FOR_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!$this->helper->isTime($bookingTime))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_INVALID_TIME_FOR_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$protectionBookingPost['protection_id']   = $elementID;
		$protectionBookingPost['service_id']      = $serviceID;
		$protectionBookingPost['user_id']         = $this->IJUserID;
		$protectionBookingPost['booking_date']    = $this->helper->convertToYYYYMMDD($bookingDate);
		$protectionBookingPost['booking_time']    = $this->helper->convertToHMS($bookingTime);
		$protectionBookingPost['meetup_location'] = $meetUpLocation;
		$protectionBookingPost['total_guard']     = $totalGuard;
		$protectionBookingPost['total_hours']     = $totalHours;
		$protectionBookingPost['total_guest']     = ($maleGuest+$femaleGuest);
		$protectionBookingPost['male_guest']      = $maleGuest;
		$protectionBookingPost['female_guest']    = $femaleGuest;

		$protectionBookingPost['price_per_hours']       = $tblService->price_per_hours;
		$protectionBookingPost['total_price']           = $tblService->price_per_hours * $totalHours * $totalGuard;
		$protectionBookingPost['user_status']           = $this->helper->getStatusID('pending');
		$protectionBookingPost['protection_status']     = $this->helper->getStatusID('request');
		$protectionBookingPost['booking_currency_code'] = $tblElement->currency_code;
		$protectionBookingPost['booking_currency_sign'] = $tblElement->currency_sign;
		$protectionBookingPost['request_date_time']     = date('Y-m-d H:i:s');
		$protectionBookingPost['time_stamp']            = time();

		$tblProtectionBooking->load(0);
		$tblProtectionBooking->bind($protectionBookingPost);

		if(!$tblProtectionBooking->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_ERROR_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblReadElementRsvp = JTable::getInstance('ReadElementRsvp', 'BeseatedTable');
		$tblReadElementRsvp->load(0);

		$tblReadElementRsvp->booked_type  = 'booking';
		$tblReadElementRsvp->element_type = 'protection';
		$tblReadElementRsvp->booking_id   = $tblProtectionBooking->protection_booking_id;
		$tblReadElementRsvp->from_user_id = $tblElement->user_id;
		$tblReadElementRsvp->to_user_id   = $this->IJUserID;

		$tblReadElementRsvp->store();

		$userDetail       = $this->helper->guestUserDetail($this->IJUserID);
		$actor            = $this->IJUserID;
		$target           = $tblElement->user_id;
		$elementID        = $tblElement->protection_id;
		$elementType      = "Protection";
		$notificationType = "service.request";
		$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_REQUEST_TO_PROTECTION',
								$userDetail->full_name,
								$tblService->service_name,
								$date,
								$this->helper->convertToHM($bookingTime)
							);
		$cid              = $tblProtectionBooking->protection_booking_id;
		$extraParams      = array();
		$extraParams["protectionBookingID"] = $tblProtectionBooking->protection_booking_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$cid))
		{
			$protectionDefaultImage = $this->helper->getElementDefaultImage($tblElement->protection_id,'Protection');

			$thumb = Juri::base().'images/beseated/'.$protectionDefaultImage->thumb_image;
			$companyDetail = JFactory::getUser($tblElement->user_id);

			$this->emailHelper->protectionBookingRequestUserMail($tblElement->protection_name,$thumb,$date,$this->helper->convertToHM($bookingTime),$totalHours,$totalGuard,$userDetail->full_name,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblProtectionBooking->total_price,0),$userDetail->email);
			$this->emailHelper->protectionBookingRequestManagerMail($tblElement->protection_name,$thumb,$date,$this->helper->convertToHM($bookingTime),$totalHours,$totalGuard,$userDetail->full_name,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblProtectionBooking->total_price,0),$companyDetail->email);
		}

		$this->jsonarray['code'] = 200;

		$this->jsonarray['pushNotificationData']['id']          = $tblProtectionBooking->protection_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Protection';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		//$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_PROTECTION_BOOKING_REQUEST_RECEIVED');
		$this->jsonarray['pushNotificationData']['type']        = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype']  = '';

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"changeBookingStatus","taskData":{"protectionBookingID":"5","statusCode":"1"}}
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

		$protectionBookingID = IJReq::getTaskData('protectionBookingID',0,'int');
		$statusCode         = IJReq::getTaskData('statusCode',0,'int');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
		$tblProtectionBooking->load($protectionBookingID);

		$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
		$tblService->load($tblProtectionBooking->service_id);
		$tblElement = JTable::getInstance('Protection', 'BeseatedTable');
		$tblElement->load($tblProtectionBooking->protection_id);

		if(!$tblProtectionBooking->protection_booking_id || !$statusCode)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_SERVICE_CHANGE_STATUS_INVALID_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($tblElement->user_id != $this->IJUserID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_SERVICE_OR_COMPANY_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($statusCode == 3)
		{
			$tblProtectionBooking->user_status = $this->helper->getStatusID('available');
			$tblProtectionBooking->protection_status = $this->helper->getStatusID('awaiting-payment');

			$totalPrice = $tblProtectionBooking->total_price;

			$tblProtectionBooking->remaining_amount     = ($tblElement->deposit_per == '0') ? $totalPrice: $totalPrice*$tblElement->deposit_per/100;
			$tblProtectionBooking->deposite_price       = ($tblElement->deposit_per == '0') ? $totalPrice: $totalPrice*$tblElement->deposit_per/100;
			$tblProtectionBooking->org_remaining_amount = $totalPrice;


			$notificationType = "service.request.accepted";
			$title = JText::sprintf(
						'COM_BESEATED_PUSHNOTIFICATION_SERVICE_BOOKING_REQUEST_ACCEPTED_BY_PROTECTION',
						$tblElement->protection_name,
						$tblService->service_name,
						$this->helper->convertDateFormat($tblProtectionBooking->booking_date),
						$this->helper->convertToHM($tblProtectionBooking->booking_time)
					);

			$dbTitle = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_SERVICE_BOOKING_REQUEST_ACCEPTED_BY_PROTECTION',
						$tblService->service_name,
						$this->helper->convertDateFormat($tblProtectionBooking->booking_date),
						$this->helper->convertToHM($tblProtectionBooking->booking_time),
						$tblProtectionBooking->total_guard
					);

		}
		else if($statusCode == 6)
		{
			$tblProtectionBooking->user_status = $this->helper->getStatusID('decline');
			$tblProtectionBooking->protection_status = $this->helper->getStatusID('decline');
			$notificationType = "service.request.declined";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_REQUEST_DECLINED_BY_PROTECTION',
									$tblElement->protection_name,
									$tblService->service_name,
									$this->helper->convertDateFormat($tblProtectionBooking->booking_date),
									$this->helper->convertToHM($tblProtectionBooking->booking_time)
								);

			$dbTitle = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_SERVICE_BOOKING_REQUEST_DECLINED_BY_PROTECTION',
						$tblService->service_name,
						$this->helper->convertDateFormat($tblProtectionBooking->booking_date),
						$this->helper->convertToHM($tblProtectionBooking->booking_time),
						$tblProtectionBooking->total_guard
					);

		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_SERVICE_INVALID_STATUS'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}


		$tblProtectionBooking->response_date_time = date('Y-m-d H:i:s');
		if(!$tblProtectionBooking->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_ERROR_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//$userDetail       = $this->helper->guestUserDetail($this->IJUserID);
		$actor            = $this->IJUserID;
		$target           = $tblProtectionBooking->user_id;
		$elementID        = $tblElement->protection_id;
		$elementType      = "Protection";
		$cid              = $tblProtectionBooking->protection_booking_id;
		$extraParams      = array();
		$extraParams["protectionBookingID"] = $tblProtectionBooking->protection_booking_id;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$dbTitle,$cid,$extraParams,$cid))
		{
			$protectionDefaultImage = $this->helper->getElementDefaultImage($tblElement->protection_id,'Protection');
			$bookingDate = date('d F Y',strtotime($tblProtectionBooking->booking_date));
			$thumb = Juri::base().'images/beseated/'.$protectionDefaultImage->thumb_image;

			$companyDetail = JFactory::getUser($tblElement->user_id);
			$userDetail    = JFactory::getUser($target);

			if($statusCode == 3)
			{
				$this->emailHelper->protectionBookingAvailableUserMail($userDetail->name,$companyDetail->name,$thumb,$bookingDate,$this->helper->convertToHM($tblProtectionBooking->booking_time),$tblProtectionBooking->total_hours,$tblProtectionBooking->total_guard,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblProtectionBooking->total_price,0),$userDetail->email);
			}
			else if ($statusCode == 6)
			{
				$this->emailHelper->protectionBookingNotAvailableUserMail($userDetail->name,$companyDetail->name,$thumb,$bookingDate,$this->helper->convertToHM($tblProtectionBooking->booking_time),$tblProtectionBooking->total_hours,$tblProtectionBooking->total_guard,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblProtectionBooking->total_price,0),$userDetail->email);
			}

		}

		$this->jsonarray['code'] = 200;

		$this->jsonarray['pushNotificationData']['id']          = $tblProtectionBooking->protection_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Protection';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		//$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_PROTECTION_BOOKING_REQUEST_STATUS_CHANGED');
		$this->jsonarray['pushNotificationData']['type']        = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype']  = '';

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"bookingShowAction","taskData":{"bookingID":"5","showAction":"1"}}
	 *
	 */
	function bookingShowAction()
	{
		$my = JFactory::getUser();

		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$protectionBookingID = IJReq::getTaskData('bookingID',0,'int');
		$showAction         = IJReq::getTaskData('showAction',0,'int');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
		$tblProtectionBooking->load($protectionBookingID);

		$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
		$tblService->load($tblProtectionBooking->service_id);

		$tblProtection = JTable::getInstance('Protection', 'BeseatedTable');
		$tblProtection->load($tblProtectionBooking->protection_id);

		if($showAction == 1)
		{
			$tblProtectionBooking->is_show = 1;
			$tblProtectionBooking->is_noshow = 0;
		}
		else if($showAction == 0)
		{
			$tblProtectionBooking->is_show = 0;
			$tblProtectionBooking->is_noshow = 1;

			$booking_owner = JFactory::getUser($tblProtectionBooking->user_id);

			$this->emailHelper->NoShowLuxuryUserMail($booking_owner->name,$tblProtection->protection_name,$this->helper->convertDateFormat($tblProtectionBooking->booking_date),$this->helper->convertToHM($tblProtectionBooking->booking_time),$booking_owner->email);
			$this->emailHelper->NoShowLuxuryManagerMail($booking_owner->name,$tblProtection->protection_name,$my->email,'Protection',$tblService->service_name);
		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_ERROR_IN_SHOW_ACTION_INVALID_SHOW_ACTION'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//$tblProtectionBooking->show_action = $showAction;
		if(!$tblProtectionBooking->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_ERROR_IN_SHOW_ACTION'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		/*if($showAction == 0)
		{
			$tblElement = JTable::getInstance('Protection','BeseatedTable');
			$tblElement->load($tblProtectionBooking->protection_id);
			$tblProtectionBooking->user_status = $this->helper->getStatusID('decline');
			$tblProtectionBooking->protection_status = $this->helper->getStatusID('decline');
			$notificationType = "service.noshow";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_NOW_SHOW_AT_PROTECTION',
									$tblElement->protection_name,
									$tblService->service_name,
									$this->helper->convertDateFormat($tblProtectionBooking->booking_date),
									$this->helper->convertToHM($tblProtectionBooking->booking_time)
								);
			//$userDetail       = $this->helper->guestUserDetail($this->IJUserID);
			$actor            = $this->IJUserID;
			$target           = $tblProtectionBooking->user_id;
			$elementID        = $tblElement->protection_id;
			$elementType      = "Protection";
			$cid              = $tblProtectionBooking->protection_booking_id;
			$extraParams      = array();
			$extraParams["protectionBookingID"] = $tblProtectionBooking->protection_booking_id;
			$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);
		}*/

		$this->jsonarray['code'] = 200;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"getBookings","taskData":{"pageNO":"0"}}
	 *
	 */
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

		$protection = $this->helper->protectionUserDetail($this->IJUserID);

		$bookingStatus = array();
		$bookingStatus[] = $this->helper->getStatusID('booked');
		$bookingStatus[] = $this->helper->getStatusID('confirmed');
		$bookingStatus[] = $this->helper->getStatusID('canceled');


		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('pb.*')
			->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
			->where($db->quoteName('pb.protection_id') . ' = ' . $db->quote($protection->protection_id))
			->where($db->quoteName('pb.protection_status') . ' IN ('.implode(',', $bookingStatus).')')
			->where($db->quoteName('pb.deleted_by_protection') . ' = ' . $db->quote(0))
			->where($db->quoteName('usr.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('pb.booking_date') . ' ASC,'.$db->quoteName('pb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=pb.user_id');

		$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=pb.protection_status');

		$query->select('ps.service_name,ps.thumb_image,ps.image')
			->join('LEFT','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');


		// Set the query and load the result.
		//$db->setQuery($query,$startFrom,$pageLimit);
		$db->setQuery($query);

		$resBookings = $db->loadObjectList();
		if(count($resBookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$resultBookings = array();
		$resultUpcomingBookings = array();
		$resultHistoryBookings = array();
		foreach ($resBookings as $key => $booking)
		{
			$temp = array();
			$temp['protectionBookingID'] = $booking->protection_booking_id;

			$temp['fullName']    = $booking->full_name;
			$temp['phone']       = $booking->phone;
			$temp['avatar']      = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$temp['thumbAvatar'] = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
			$userDetail          = $this->helper->guestUserDetail($booking->user_id);
			$temp['fbid']        = ($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id)) ? $userDetail->fb_id : '';

			$temp['serviceName']  = $booking->service_name;
			$temp['isShow']       = $booking->is_show;
			$temp['totalGuard']   = $booking->total_guard;
			//$temp['thumbImage'] = ($booking->thumb_image)?JUri::root().'image/beseated/'.$booking->thumb_image:'';
			//$temp['image']      = ($booking->image)?JUri::root().'image/beseated/'.$booking->image:'';

			$temp['bookingDate']           = $this->helper->convertDateFormat($booking->booking_date);
			$temp['bookingTime']           = $this->helper->convertToHM($booking->booking_time);
			$temp['formatedCurrency']      = $this->helper->currencyFormat($booking->booking_currency_sign,$booking->total_price);
			$temp['pricePerHours']         = $this->helper->currencyFormat('',$booking->price_per_hours);
			$temp['hoursRquested']         = $booking->total_hours;
			$temp['bookingCurrencyCode']   = $booking->booking_currency_code;
			$temp['totalAmount']           = $this->helper->currencyFormat('',$booking->total_price);
			$temp['statusCode']			   = $booking->protection_status;
			$temp['isNoShow']              = $booking->is_noshow;

			$temp['isRead']                = $this->helper->isReadBooking('protection','booking', $booking->protection_booking_id);
			//$temp['bookingCurrencySign'] = $booking->booking_currency_sign;
			//$temp['bookingCurrencyCode'] = $booking->booking_currency_code;

			if($this->helper->isPastDate($booking->booking_date) || $booking->is_noshow == 1)
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
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_BOOKINGS_NOT_FOUND'));
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

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"getRSVP","taskData":{"pageNO":"0"}}
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

		$protection = $this->helper->protectionUserDetail($this->IJUserID);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$rsvpStatus = array();
		$rsvpStatus[] = $this->helper->getStatusID('request');
		$rsvpStatus[] = $this->helper->getStatusID('awaiting-payment');
		$rsvpStatus[] = $this->helper->getStatusID('decline');

		// Create the base select statement.
		$query->select('pb.*')
			->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
			->where($db->quoteName('pb.protection_id') . ' = ' . $db->quote($protection->protection_id))
			->where($db->quoteName('pb.protection_status') . ' IN ('.implode(',', $rsvpStatus).')')
			->where($db->quoteName('pb.deleted_by_protection') . ' = ' . $db->quote(0))
			->where($db->quoteName('usr.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('pb.booking_date') . ' ASC,'.$db->quoteName('pb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=pb.user_id');

		/*$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=pb.protection_status');*/

		$query->select('p.protection_name')
			->join('LEFT','#__beseated_protection AS p ON p.protection_id=pb.protection_id');

		$query->select('ps.service_name,ps.thumb_image,ps.image')
			->join('LEFT','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');


		// Set the query and load the result.
		//$db->setQuery($query,$startFrom,$pageLimit);
		$db->setQuery($query);

		$resBookings = $db->loadObjectList();
		if(count($resBookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$resultBookings = array();
		$resultUpcomingBookings = array();
		$resultHistoryBookings = array();
		foreach ($resBookings as $key => $booking)
		{
			$temp = array();
			$temp['protectionBookingID'] = $booking->protection_booking_id;

			$temp['fullName']    = $booking->full_name;
			$temp['phone']       = $booking->phone;
			$temp['avatar']      = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$temp['thumbAvatar'] = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
			$userDetail          = $this->helper->guestUserDetail($booking->user_id);
			$temp['fbid']        = ($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id)) ? $userDetail->fb_id : '';

			$temp['protectionName'] = $booking->protection_name;

			$temp['serviceName']  = $booking->service_name;
			//$temp['thumbImage'] = ($booking->thumb_image)?JUri::root().'image/beseated/'.$booking->thumb_image:'';
			//$temp['image']      = ($booking->image)?JUri::root().'image/beseated/'.$booking->image:'';

			$temp['bookingDate']         = $this->helper->convertDateFormat($booking->booking_date);
			$temp['bookingTime']         = $this->helper->convertToHM($booking->booking_time);
			$temp['formatedCurrency']    = $this->helper->currencyFormat($booking->booking_currency_sign,$booking->total_price,2);
			$temp['pricePerHours']       = $this->helper->currencyFormat('',$booking->price_per_hours);
			$temp['hoursRquested']       = $booking->total_hours;
			$temp['statusCode']          = $booking->protection_status;
			$temp['totalGuard']          = $booking->total_guard;
			$temp['bookingCurrencyCode'] = $booking->booking_currency_code;
			$temp['totalAmount']         = $this->helper->currencyFormat('',$booking->total_price);
			$temp['isRead']              = $this->helper->isReadRSVP('protection','booking', $booking->protection_booking_id);
			//$temp['bookingCurrencySign'] = $booking->booking_currency_sign;
			//$temp['bookingCurrencyCode'] = $booking->booking_currency_code;

			if(!$this->helper->isPastDate($booking->booking_date))
			{
				$resultBookings[] = $temp;
			}

		}

		if(count($resultBookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_RSVP_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//$resultBookings['upcomming'] = $resultUpcomingBookings;
		//$resultBookings['history'] = $resultHistoryBookings;

		$this->jsonarray['code']          = 200;
		$this->jsonarray['totalRSVP'] = count($resultBookings);
		$this->jsonarray['rsvp']  = $resultBookings;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"getRevenue","taskData":{"pageNO":"0"}}
	 *
	 */
	function getRevenue()
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

		$bookingStatus = array();
		$bookingStatus[] = $this->helper->getStatusID('booked');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('pb.*')
			->from($db->quoteName('#__beseated_protection_booking') . ' AS pb')
			->where($db->quoteName('pb.protection_id') . ' = ' . $db->quote($protection->protection_id))
			//->where($db->quoteName('pb.protection_status') . ' IN ('.implode(',', $bookingStatus).')')
			->order($db->quoteName('pb.booking_date') . ' ASC,'.$db->quoteName('pb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.is_deleted')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=pb.user_id');

		$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=pb.protection_status');

		$query->select('ps.service_name,ps.thumb_image,ps.image')
			->join('LEFT','#__beseated_protection_services AS ps ON ps.service_id=pb.service_id');


		// Set the query and load the result.
		//$db->setQuery($query,$startFrom,$pageLimit);
		$db->setQuery($query);

		$resBookings = $db->loadObjectList();
		if(count($resBookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$blacklistedUsers = $this->helper->getBlackListedUser($protection->protection_id,'Protection');

		$resultRevenues = array();
		$revenuePrice = 0;

		foreach ($resBookings as $key => $booking)
		{
			$temp                          = array();
			$temp['protectionBookingID']   = $booking->protection_booking_id;
			$temp['fullName']              = $booking->full_name;
			$temp['phone']                 = $booking->phone;
			$temp['userID']                = $booking->user_id;
			$temp['isDeletedUser']         = $booking->is_deleted;

			$threadId = $this->helper->getThreadId($booking->user_id);

			$temp['connectionID']          = ($threadId) ? $threadId : "0";
			$temp['serviceName']           = $booking->service_name;
			//$temp['thumbImage']          = ($booking->thumb_image)?JUri::root().'image/beseated/'.$booking->thumb_image:'';
			//$temp['image']               = ($booking->image)?JUri::root().'image/beseated/'.$booking->image:'';

			$temp['bookingDate']           = $this->helper->convertDateFormat($booking->booking_date);
			//$temp['bookingTime']         = $this->helper->convertToHM($booking->booking_time);
			$temp['formatedCurrency']      = $this->helper->currencyFormat($booking->booking_currency_sign,$booking->total_price);
			//$temp['bookingCurrencySign'] = $booking->booking_currency_sign;
			//$temp['bookingCurrencyCode'] = $booking->booking_currency_code;
			//$temp['pricePerHours']       = $booking->price_per_hours;
			$temp['totalHours']            = $booking->total_hours;
			$temp['totalGuest']            = $booking->total_guest;
			$temp['maleGuest']             = $booking->male_guest;
			$temp['femaleGuest']           = $booking->female_guest;
			$temp['totalGuard']            = $booking->total_guard;

			if(in_array($booking->user_id, $blacklistedUsers))
				$temp['isBlacklisted'] = "1";
			else
				$temp['isBlacklisted'] = "0";

			$temp['bookingCurrencyCode'] = $booking->booking_currency_code;
			$temp['totalAmount']         = $this->helper->currencyFormat('',$booking->total_price);
			$revenuePrice                = $revenuePrice + $booking->total_price;
			$resultRevenues[]            = $temp;
		}

		if(count($resultRevenues) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_REVENUE_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//$resultBookings['upcomming'] = $resultUpcomingBookings;
		//$resultBookings['history'] = $resultHistoryBookings;

		$this->jsonarray['code']         = 200;
		$this->jsonarray['revenueCount'] = count($resultRevenues);
		$this->jsonarray['revenues']     = $resultRevenues;
		$this->jsonarray['totalRevenue'] = $this->helper->currencyFormat('',$revenuePrice);

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"getNotification","taskData":{"pageNO":"0"}}
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
			$temp['notificationType'] = $notification->notification_type;
			$temp['isRead']           = $notification->is_read;
			$actorDetail              = $this->helper->guestUserDetail($notification->actor);
			$temp['avatar']           = ($actorDetail->avatar)?$this->helper->getUserAvatar($actorDetail->avatar):'';
			$temp['thumbAvatar']      = ($actorDetail->thumb_avatar)?$this->helper->getUserAvatar($actorDetail->thumb_avatar):'';
			$temp['fbid']             = ($actorDetail->is_fb_user == '1' && !empty($actorDetail->fb_id)) ? $actorDetail->fb_id : '';
			$temp['timeStamp']        = $notification->time_stamp;
			$temp['userType']         = "";
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
	 * {"extName":"beseated","extView":"protection","extTask":"addUserToBlackList","taskData":{"userID":"0"}}
	 */
	function addUserToBlackList()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$protection = $this->helper->protectionUserDetail($this->IJUserID);
		$userID    = IJReq::getTaskData('userID',0, 'int');

		if(!$userID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_USER_FOR_BLACKLIST_ADD'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$isBlackListed = $this->helper->checkBlackList($userID,$protection->protection_id,'Protection');

		if($isBlackListed)
		{
			IJReq::setResponseCode(707);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_USER_ALREDY_ADD_IN_BLACKLIST'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$result = $this->helper->addUserToBlackList($userID,$protection->protection_id,'Protection');

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
	 * {"extName":"beseated","extView":"protection","extTask":"removeUserFromBlackList","taskData":{"userID":"0"}}
	 */
	function removeUserFromBlackList()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$protection = $this->helper->protectionUserDetail($this->IJUserID);
		$userID    = IJReq::getTaskData('userID',0, 'int');

		if(!$userID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_USER_FOR_BLACKLIST_ADD'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$isBlackListed = $this->helper->removeUserFromBlackList($userID,$protection->protection_id,'Protection');

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"contact","taskData":{"subject":"Testing Subject","message":"This is Testing Message from protectin manager"}}
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
		$this->jsonarray['code'] = 200;

		$protectionDetail = $this->helper->protectionUserDetail($this->IJUserID);
		$userID           = $protectionDetail->user_id;
		$elementID        = $protectionDetail->protection_id;
		$elementType      = 'protection';
		$this->helper->storeContactRequest($userID,$elementID,$elementType,$subject,$message);
		$this->emailHelper->contactAdmin($subject, $message);
		$this->emailHelper->contactThankYouEmail();

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"promotion","taskData":{"subject":"Testing Subject","message":"This is Testing Message from protectin manager"}}
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
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_CONTACT_MESSAGE_DETAIL_INVALID'));
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

		$protectionDetail = $this->helper->protectionUserDetail($this->IJUserID);

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
				$msgData['message_type']  = 'protectionPromotion';
				$msgData['message_body']  = $subject."\n".$message;
				$msgData['extra_params']  = "";
				$msgData['time_stamp']    = time();

				$elementType                = "Protection";
				$elementID                  = $protectionDetail->protection_id;
				$cid                        = $protectionDetail->protection_id;
				$extraParams                = array();
				$extraParams["protectionID"]= $cid;
				$notificationType           = "protection.promotion.message";
				$title                      = JText::sprintf(
												'COM_BESEATED_NOTIFICATION_PROTECTION_PROMOTION_MESSAGE_TO_USER',
												$protectionDetail->protection_name);

				$tblMessage->bind($msgData);
				if($tblMessage->store())
				{
					//$this->helper->storeNotification($this->IJUserID,$user_id,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);

					// add this user id to send push notification;
					$push_user_ids[] = $user_id;

					$guestDetail = JFactory::getUser($user_id);

					$this->emailHelper->userNewMessageMail($guestDetail->name,$protectionDetail->protection_name,$message,$subject,$guestDetail->email);
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

		$userID          = $protectionDetail->user_id;
		$elementID       = $protectionDetail->protection_id;
		$elementType     = 'protection';
		$this->helper->storePromotionRequest($userID,$elementID,$elementType,$subject,$message,$userDetail->city,count($user_ids));

		$this->jsonarray['code'] = 200;

		$this->jsonarray['pushNotificationData']['id']         = '1';
		$this->jsonarray['pushNotificationData']['to']         = implode(',', $user_ids);
		$this->jsonarray['pushNotificationData']['message']    = $message;
		//$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_PROTECTION_PROMOTION_MSG_RECEIVED');
		$this->jsonarray['pushNotificationData']['type']       = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype'] = '';

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"shareAmount","taskData":{"emails":"aarathi.vaitheeswaran@gmail.com,darshit@tasolglobal.com","fbids":"","bookingID":"7"}}
	 *
	 */
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

		$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
		$alreadySplited       = $this->helper->getSplitedDetail($bookingID,'Protection');

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

		$tblProtectionBooking->load($bookingID);
		$totalAmountToSplit = $tblProtectionBooking->total_price;
		$totalSplitCount    = count($newSplitedEmails) + count($alreadySplited);
		$splittedAmount     = $totalAmountToSplit / $totalSplitCount;

		$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
		$tblService->load($tblProtectionBooking->service_id);

		$tblElement = JTable::getInstance('Protection', 'BeseatedTable');
		$tblElement->load($tblProtectionBooking->protection_id);

		$userIDs = array();

		foreach ($newSplitedEmails as $key => $email)
		{
			$userID = BeseatedHelper::getUserForSplit($email);
			$tblProtectionBookingSplit = JTable::getInstance('ProtectionBookingSplit', 'BeseatedTable');
			$tblProtectionBookingSplit->load(0);
			$splitPost = array();
			$splitPost['protection_booking_id'] = $bookingID;
			$splitPost['protection_id']         = $tblProtectionBooking->protection_id;
			$splitPost['service_id']            = $tblProtectionBooking->service_id;
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
			$tblProtectionBookingSplit->bind($splitPost);
			$tblProtectionBookingSplit->store();

			if($userID !== $this->IJUserID)
			{
				$invitationData = array();

				$tblReadElementBooking = JTable::getInstance('ReadElementBooking', 'BeseatedTable');
				$tblReadElementBooking->load(0);

				$invitationData['booked_type']      = 'share';
				$invitationData['element_type']     = 'protection';
				$invitationData['booking_id']       = $tblProtectionBookingSplit->protection_booking_split_id;
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
				$elementID        = $tblElement->protection_id;
				$elementType      = "Protection";
				$notificationType = "protection.share.invitation.request";
				$title            = JText::sprintf(
										'COM_BESEATED_NOTIFICATION_SHARE_BOOKING_REQUEST_TO_INVITEE_FOR_PROTECTION'
										/*$userDetail->full_name,
										$tblService->service_name,
										$this->helper->convertDateFormat($tblProtectionBooking->booking_date),
									    $this->helper->convertToHM($tblProtectionBooking->booking_time)*/
									);

				$dbTitle            = JText::sprintf(
										'COM_BESEATED_DB_NOTIFICATION_SHARE_BOOKING_REQUEST_TO_INVITEE_FOR_PROTECTION',
										$tblService->service_name,
										$this->helper->convertDateFormat($tblProtectionBooking->booking_date),
									    $this->helper->convertToHM($tblProtectionBooking->booking_time)
									);


				$cid              = $tblProtectionBookingSplit->protection_booking_split_id;
				$extraParams      = array();
				$extraParams["protectionBookingID"] = $tblProtectionBooking->protection_booking_id;
				$extraParams["invitationID"]        = $tblProtectionBookingSplit->protection_booking_split_id;
				$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$dbTitle,$cid,$extraParams,$tblProtectionBooking->protection_booking_id,$email);
			}
		}

		if(!empty($userIDs))
		{
			$this->jsonarray['pushNotificationData']['id']         = $tblProtectionBooking->protection_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Protection';
			$this->jsonarray['pushNotificationData']['to']         = implode(',', $userIDs);
			$this->jsonarray['pushNotificationData']['message']    = $title;
			//$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_PROTECTION_SHARE_BOOKING_REQUEST_RECEIVED');
			$this->jsonarray['pushNotificationData']['type']       = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype'] = '';
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_protection_booking_split'))
			->set($db->quoteName('splitted_amount') . ' = ' . $db->quote($tblProtectionBooking->each_person_pay))
			->where($db->quoteName('protection_booking_id') . ' = ' . $db->quote($bookingID));

		// Set the query and execute the update.
		$db->setQuery($query);
		$db->execute();

		$tblProtectionBooking->is_splitted      = 1;
		//$tblProtectionBooking->each_person_pay  = $splittedAmount;
		$tblProtectionBooking->splitted_count   = $totalSplitCount;
		//$tblProtectionBooking->remaining_amount = $totalAmountToSplit;
		$tblProtectionBooking->store();

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"protection","extTask":"sendInvitation","taskData":{"emails":"aarathi.vaitheeswaran@gmail.com,darshit@tasolglobal.com","fbids":"","bookingID":"7"}}
	 *
	 */
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

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$filterEmails         = BeseatedHelper::filterEmails($emails);
		$filterFbFrndEmails   = BeseatedHelper::filterFbIds($fbids);

		$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
		$alreadyInvited       = $this->helper->getInvitationDetail($bookingID,'protection');
		//$alreadySplited       = $this->helper->getSplitedDetail($bookingID,'Protection');
		//$alreadyInvited       = array_merge($alreadyInvited,$alreadySplited);

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
			$this->jsonarray['code'] = 200;
			return $this->jsonarray;
		}

		if(count($newInvitedEmails) == 0)
		{
			$this->jsonarray['message'] = JText::_('COM_IJOOMERADV_BESEATED_ELEMENT_USERS_ALREADY_INVITED');
			$this->jsonarray['code'] = 400;
			return $this->jsonarray;
		}

		$tblProtectionBooking->load($bookingID);

		$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
		$tblService->load($tblProtectionBooking->service_id);

		$tblElement = JTable::getInstance('Protection', 'BeseatedTable');
		$tblElement->load($tblProtectionBooking->protection_id);

		foreach ($newInvitedEmails as $key => $email)
		{
			$userID = BeseatedHelper::getUserForSplit($email);

			$tblInvitation = JTable::getInstance('Invitation', 'BeseatedTable');
			$tblInvitation->load(0);
			$invitationPost = array();
			$invitationPost['element_booking_id'] = $bookingID;
			$invitationPost['element_id']         = $tblProtectionBooking->protection_id;
			$invitationPost['element_type']       = 'protection';
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
			$invitationData['element_type']     = 'protection';
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


			$notificationType = "protection.service.invitation";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_PROTECTION_SERVICE_BOOKING_INVITATION',
									$tblService->service_name,
									$this->helper->convertDateFormat($tblProtectionBooking->booking_date),
									$this->helper->convertToHM($tblProtectionBooking->booking_time)
								);

			$actor       = $this->IJUserID;
			$target      = $userID;
			$elementID   = $tblElement->protection_id;
			$elementType = "Protection";
			$cid         = $tblInvitation->invitation_id;
			$extraParams = array();
			$extraParams["protectionBookingID"] = $tblProtectionBooking->protection_booking_id;
			$extraParams["invitationID"]        = $tblInvitation->invitation_id;
			$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblProtectionBooking->protection_booking_id,$email);

			$this->jsonarray['pushNotificationData']['id']          = $tblProtectionBooking->protection_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Protection';
			$this->jsonarray['pushNotificationData']['to']          = $target;
			$this->jsonarray['pushNotificationData']['message']     = $title;
			/*$pushNotification['pushNotificationData']['type']     = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_PROTECTION_BOOKING_INVITATION_REQUEST');*/
			$this->jsonarray['pushNotificationData']['type']        = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype']  = '';


			$loginUser   = JFactory::getUser();
			$inviteeName = $loginUser->name;

			$invitedUserName = ($userDetail)?$userDetail->full_name :$email;

			$this->emailHelper->invitationMailUser($invitedUserName,$inviteeName,$tblElement->protection_name,$tblService->service_name,$this->helper->convertDateFormat($tblProtectionBooking->booking_date),$this->helper->convertToHM($tblProtectionBooking->booking_time),$isNightVenue = 0,$email);

		}

		$tblProtectionBooking->has_invitation = 1;
		$tblProtectionBooking->store();

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

		$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
		$alreadyInvited       = $this->helper->getSplitedDetail($bookingID,'Protection');

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
			$this->jsonarray['message'] = JText::_('COM_IJOOMERADV_BESEATED_ELEMENT_USERS_ALREADY_INVITED');
			$this->jsonarray['code'] = 400;
			return $this->jsonarray;
		}

		$tblProtectionBooking->load($bookingID);

		$tblService = JTable::getInstance('ProtectionService', 'BeseatedTable');
		$tblService->load($tblProtectionBooking->service_id);

		$tblElement = JTable::getInstance('Protection', 'BeseatedTable');
		$tblElement->load($tblProtectionBooking->protection_id);

		$email= $newInvitedEmails[0];

		$userID = BeseatedHelper::getUserForSplit($email);
		$tblProtectionBookingSplit = JTable::getInstance('ProtectionBookingSplit', 'BeseatedTable');
		$tblProtectionBookingSplit->load($invitationID);

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
		$tblProtectionBookingSplit->bind($splitPost);
		$tblProtectionBookingSplit->store();
		$this->deleteReplaceInvitee($bookingID,$invitationID);

		$userDetail       = $this->helper->guestUserDetail($this->IJUserID);
		$actor            = $this->IJUserID;
		$target           = $userID;
		$elementID        = $tblElement->protection_id;
		$elementType      = "Protection";
		$notificationType = "protection.share.invitation.request";
		$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_SHARE_BOOKING_REQUEST_TO_INVITEE_FOR_PROTECTION',
								$userDetail->full_name,
								$tblService->service_name,
								$this->helper->convertDateFormat($tblProtectionBooking->booking_date),
							    $this->helper->convertToHM($tblProtectionBooking->booking_time)
							);

		$cid              = $tblProtectionBookingSplit->protection_booking_split_id;
		$extraParams      = array();
		$extraParams["protectionBookingID"] = $tblProtectionBooking->protection_booking_id;
		$extraParams["invitationID"]        = $tblProtectionBookingSplit->protection_booking_split_id;

		$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblProtectionBooking->protection_booking_id,$email);

		$this->jsonarray['code'] = 200;

		$this->jsonarray['pushNotificationData']['id']          = $tblProtectionBooking->protection_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Protection';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		/*$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_PROTECTION_SHARE_BOOKING_REQUEST_RECEIVED');*/
		$this->jsonarray['pushNotificationData']['type']        = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype']  = '';

		$this->jsonarray['code'] = 200;
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
			->where($db->quoteName('notification_type') . ' = ' . $db->quote('protection.share.invitation.request'))
			->where($db->quoteName('actor') . ' = ' . $db->quote((int) $this->IJUserID))
			->where($db->quoteName('cid') . ' = ' . $db->quote((int) $invitationID));

		// Set the query and execute the delete.
		$db->setQuery($query);

		$notif_data = $db->loadObjectList();

		foreach ($notif_data as $key => $value)
		{
			$extra_pramas = $value->extra_pramas;

			if(json_decode($extra_pramas)->protectionBookingID == $bookingID)
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

		$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
		$tblProtectionBooking->load($bookingID);

		$tblElement = JTable::getInstance('Protection', 'BeseatedTable');
		$tblElement->load($tblProtectionBooking->protection_id);

		if(!$tblProtectionBooking->protection_booking_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($tblElement->user_id != $this->IJUserID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_SERVICE_OR_COMPANY_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblProtectionBooking->deleted_by_protection = 1;

		if(!$tblProtectionBooking->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

}