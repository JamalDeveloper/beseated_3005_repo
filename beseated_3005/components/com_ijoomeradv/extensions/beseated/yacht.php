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
class yacht
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

		$this->db          = JFactory::getDBO();
		$this->mainframe   =  JFactory::getApplication ();
		$this->IJUserID    = $this->mainframe->getUserState ( 'com_ijoomeradv.IJUserID', 0 ); //get login user id
		$this->my          = JFactory::getUser ( $this->IJUserID ); // set the login user object
		$this->helper      = new beseatedAppHelper;
		$this->emailHelper = new BeseatedEmailHelper;
		$this->jsonarray   = array();

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
	 * 	{"extName":"beseated","extView":"yacht","extTask":"getYachts","taskData":{"city":"","latitude":"1","longitude":"1","pageNO":"0"}}
	 */
	function getYachts()
	{
		$stringQuery      = IJReq::getTaskData('query','','string');
		$city      = IJReq::getTaskData('city','','string');
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

		$query->from($db->quoteName('#__beseated_yacht'))
			->where($db->quoteName('has_service') . ' = ' . $db->quote('1'))
			->where($db->quoteName('published') . ' = ' . $db->quote('1'));
			//->order($db->quoteName('yacht_name') . ' ASC');

		if(!empty($city))
		{
			$query->where(
				'('.
					$db->quoteName('location') .' LIKE ' . $db->quote('%'.$city.'%'). ' OR '.
					$db->quoteName('city') .' LIKE ' . $db->quote('%'.$city.'%').
				')'
			);
		}
		if(!empty($stringQuery)){
			$query->where($db->quoteName('yacht_name') .' LIKE ' . $db->quote('%'.$stringQuery.'%'));
		}

		if(!empty($latitude) && !empty($longitude))
		{
			$sqlString = $query;
			$sqlString .= ' GROUP BY yacht_id HAVING distance<'.COM_IJOOMERADV_BESEATED_RADIOUS;
			$sqlString .= ' ORDER BY yacht_name ASC';
			$query = $sqlString;

		}
		else
		{
			$query->order($db->quoteName('yacht_name') . ' ASC');
		}

		if($this->IJUserID)
		{
			$yachtIDs = $this->helper->getBlackListedElementOfUser($this->IJUserID,'Yacht');
			if(count($yachtIDs) != 0)
			{
				$query->where($db->quoteName('yacht_id') .' NOT IN ('.implode(",", $yachtIDs).')');
			}
		}

		$db->setQuery($query,$startFrom,$pageLimit);
		$resYachts = $db->loadObjectList();

		if(count($resYachts) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHTS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$resultYachts =  array();
		$resultYachtIDs = array();
		foreach ($resYachts as $key => $yacht)
		{
			$temp = array();
			$resultYachtIDs[]  = $yacht->yacht_id;
			$temp['yachtID']   = $yacht->yacht_id;
			$temp['yachtName'] = $yacht->yacht_name;
			$temp['location']  = $yacht->location;
			$temp['city']      = $yacht->city;
			$temp['ratting']   = $yacht->avg_ratting;
			$temp['latitude']  = $yacht->latitude;
			$temp['longitude'] = $yacht->longitude;
			$resultYachts[] = $temp;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('element_id') . ' IN ('.implode(',', $resultYachtIDs).')')
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Yacht'))
			->order($db->quoteName('image_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$resultYachtImages = $db->loadObjectList();
		$allYachtImages    = array();
		$corePath          = JUri::base().'images/beseated/';
		foreach ($resultYachtImages as $key => $yachtImage)
		{
			$tempImg = array();

			if($yachtImage->is_video)
			{
				$tempImg['thumbImage'] = ($yachtImage->thumb_image)?$corePath.$yachtImage->thumb_image:"";
			}
			else
			{
				$tempImg['thumbImage'] = ($yachtImage->image)?$corePath.$yachtImage->image:"";
			}
			//$tempImg['thumbImage'] = ($yachtImage->thumb_image)?$corePath.$yachtImage->thumb_image:'';
			$tempImg['image']       = ($yachtImage->image)?$corePath.$yachtImage->image:'';
			$tempImg['isVideo']    = $yachtImage->is_video;
			$tempImg['isDefault']  = $yachtImage->is_default;
			$allYachtImages[$yachtImage->element_id][] = $tempImg;
		}

		foreach ($resultYachts as $key => $yacht)
		{
			if(isset($allYachtImages[$yacht['yachtID']]))
			{
				$resultYachts[$key]['images'] = $allYachtImages[$yacht['yachtID']];
			}
			else
			{
				$resultYachts[$key]['images'] = array();
			}
		}

		$this->jsonarray['code']      = 200;
		$this->jsonarray['totalInPage']     = count($resultYachts);
		$this->jsonarray['pageLimit'] = BESEATED_YACHT_LIST_LIMIT;
		$this->jsonarray['yachts']    = $resultYachts;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * 	{
	 * 		"extName":"beseated",
	 * 		"extView":"yacht",
	 * 		"extTask":"getYachtDetail",
	 * 		"taskData":{
	 *   		"yachtID":"1",
	 *
	 * 		}
	 * 	}
	 */
	function getYachtDetail()
	{
		$yachtID   = IJReq::getTaskData('yachtID','','string');
		if(!$yachtID)
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
			->from($db->quoteName('#__beseated_yacht'))
			->where($db->quoteName('yacht_id') . ' = ' . $db->quote($yachtID));

		$db->setQuery($query,0,1);
		$resYachts = $db->loadObjectList();
		if(count($resYachts) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$userType = $this->helper->getUserType($this->IJUserID);
		if($userType == 'Guest')
		{
			$favouritIDs = $this->helper->getUserFavourites($this->IJUserID,'Yacht');
		}

		$resultYachts =  array();
		$resultYachtIDs = array();
		foreach ($resYachts as $key => $yacht)
		{
			$temp                 = array();
			$resultYachtIDs[]     = $yacht->yacht_id;
			$temp['yachtID']      = $yacht->yacht_id;
			$temp['yachtName']    = $yacht->yacht_name;
			$temp['currencyCode'] = $yacht->currency_code;
			$temp['location']     = $yacht->location;
			$temp['city']         = $yacht->city;
			$temp['ratting']      = $yacht->avg_ratting;
			$temp['latitude']     = $yacht->latitude;
			$temp['longitude']    = $yacht->longitude;

			if($userType == 'Guest' && in_array($yacht->yacht_id, $favouritIDs))
			{
				$temp['isFavourite'] = 1;
			}
			else if($userType == 'Guest')
			{
				$temp['isFavourite'] = 0;
			}
			$resultYachts[]    = $temp;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('element_id') . ' IN ('.implode(',', $resultYachtIDs).')')
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Yacht'))
			->order($db->quoteName('image_id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$resultYachtImages = $db->loadObjectList();

		$this->helper->array_sort_by_column($resultYachtImages,'is_default',3);

		$allYachtImages    = array();
		$corePath          = JUri::base().'images/beseated/';
		foreach ($resultYachtImages as $key => $yachtImage)
		{
			$tempImg = array();

			if($yachtImage->is_video)
			{
				$tempImg['thumbImage'] = ($yachtImage->thumb_image)?$corePath.$yachtImage->thumb_image:$defaultImage;
			}
			else
			{
				$tempImg['thumbImage'] = ($yachtImage->image)?$corePath.$yachtImage->image:$defaultImage;
			}

			//$tempImg['thumbImage'] = ($yachtImage->thumb_image)?$corePath.$yachtImage->thumb_image:$defaultImage;
			$tempImg['image']       = ($yachtImage->image)?$corePath.$yachtImage->image:$defaultImage;
			$tempImg['isVideo']    = $yachtImage->is_video;
			$tempImg['isDefault']  = $yachtImage->is_default;
			$allYachtImages[$yachtImage->element_id][] = $tempImg;
		}

		foreach ($resultYachts as $key => $yacht)
		{
			if(isset($allYachtImages[$yacht['yachtID']]))
			{
				$resultYachts[$key]['images'] = $allYachtImages[$yacht['yachtID']];
			}
			else
			{
				$resultYachts[$key]['images'] = array();
				$resultYachts[$key]['images'][$key]['thumbImage']    = $defaultImage;
				$resultYachts[$key]['images'][$key]['image']         = $defaultImage;
				$resultYachts[$key]['images'][$key]['isVideo']       = "0";
				$resultYachts[$key]['images'][$key]['isDefault']     = "1";
			}
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_yacht_services'))
			->where($db->quoteName('yacht_id') . ' = ' . $db->quote($yachtID))
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
			$temp['yachtID']       = $service->yacht_id;
			$temp['serviceType']   = $service->service_type;
			$temp['serviceName']   = $service->service_name;
			$temp['dock']          = $service->dock;
			$temp['pricePerHours'] = $this->helper->currencyFormat('',$service->price_per_hours);
			$temp['minHours']      = $service->min_hours;
			$temp['capacity']      = $service->capacity;
			//$temp['thumbImage']    = ($service->thumb_image)?$corePath.$service->thumb_image:'';
			//$temp['image']         = ($service->image)?$corePath.$service->image:'';

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($db->quoteName('#__beseated_element_images'))
				->where($db->quoteName('element_id') . ' = ' . $db->quote($yachtID))
				->where($db->quoteName('service_id') . ' = ' . $db->quote($service->service_id))
				->where($db->quoteName('element_type') . ' = ' . $db->quote('yacht.service'))
				->order($db->quoteName('image_id') . ' ASC');

			// Set the query and load the result.
			$db->setQuery($query);

			$resultYachtServiceImages = $db->loadObjectList();

			$this->helper->array_sort_by_column($resultYachtServiceImages,'is_default',3);

			$temp['images'] = array();

			foreach ($resultYachtServiceImages as $key1 => $serviceImg)
			{
				$temp['images'][$key1]['imageID']       = $serviceImg->image_id;
				$temp['images'][$key1]['thumbImage']    = $corePath.$serviceImg->thumb_image;
				$temp['images'][$key1]['image']         = $corePath.$serviceImg->image;
				$temp['images'][$key1]['isVideo']       = "0";
				$temp['images'][$key1]['isDefault']     = $serviceImg->is_default;
			}

			$resultServices[]      = $temp;
		}

		if($yachtID)
		{
			$resultYachts = $resultYachts[0];
			$resultYachts['services'] = $resultServices;
		}

		$queryRatings = $db->getQuery(true);

		// Create the base select statement.
		$queryRatings->select('r.rating_id,r.user_id,r.avg_rating,r.food_rating,r.service_rating,r.atmosphere_rating,r.value_rating,r.rating_count,r.rating_comment,r.created')
			->from($db->quoteName('#__beseated_rating','r'))
			->where($db->quoteName('r.element_id') . ' = '.$db->quote($yachtID))
			->where($db->quoteName('r.element_type') . ' = ' . $db->quote('yacht'))
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
			$tempRating['avgrating']        = $rating->avg_rating;
			$tempRating['foodRating']       = $rating->food_rating;
			$tempRating['serviceRating']    = $rating->service_rating;
			$tempRating['atmosphereRating'] = $rating->atmosphere_rating;
			$tempRating['valueRating']      = $rating->value_rating;
			$tempRating['created']          = $this->helper->convertDateFormat($rating->created);
			$tempRating['avatar']           = ($rating->avatar)?$this->helper->getUserAvatar($rating->avatar):'';
			$tempRating['thumbAvatar']      = ($rating->thumb_avatar)?$this->helper->getUserAvatar($rating->thumb_avatar):'';
			$tempRating['fullName']         = $rating->full_name;
			$allRatings[]                   = $tempRating;
		}

		$resultYachts['ratings']        = $allRatings;
		$this->jsonarray['code']        = 200;
		$this->jsonarray['yachtDetail'] = $resultYachts;

		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * 	{"extName":"beseated","extView":"yacht","extTask":"addService","taskData":{"serviceID":"0","serviceName":"Yacht Service One","yachtType":"Yacht Service Type One","pricePerHours":"150","dock":"Kankariya","capacity":"5","yachtID":"1"}}
	 */
	function addService()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$serviceID       = IJReq::getTaskData('serviceID',0,'int');
		$serviceName     = IJReq::getTaskData('serviceName','','string');
		$yachtType       = IJReq::getTaskData('yachtType','','string');
		$pricePerHours   = IJReq::getTaskData('pricePerHours',0,'int');
		$minHours        = IJReq::getTaskData('minHours',0,'int');
		$dock            = IJReq::getTaskData('dock','','string');
		$capacity        = IJReq::getTaskData('capacity',0,'int');
		$yachtID         = IJReq::getTaskData('yachtID',0,'int');
		$defaultImageKey = IJReq::getTaskData('defaultImageKey','','string');
		$defaultImageID  = IJReq::getTaskData('defaultImageID',0,'int');
		$deletedImageIDs = IJReq::getTaskData('deletedImageIDs','','string');

		if(!$yachtID || !$pricePerHours || !$capacity)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_YACHT_SERVICE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		if(empty($serviceName) || empty($yachtType))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_YACHT_SERVICE_DETAIL_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}


		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
		$tblService->load($serviceID);

		$data['yacht_id']        = $yachtID;
		$data['service_name']    = $serviceName;
		$data['service_type']    = $yachtType;
		$data['dock']            = $dock;
		$data['price_per_hours'] = $pricePerHours;
		$data['min_hours']       = $minHours;
		$data['capacity']        = $capacity;
		$data['published']       = 1;
		$data['time_stamp']      = time();

		$tblService->bind($data);

		if(!$tblService->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$serviceID = ($serviceID)? $serviceID :$tblService->service_id;

		foreach ($_FILES as $key => $file)
		{
			if(is_array($file) && isset($file['size']) && $file['size']>0)
			{
				$defualtPath = JPATH_ROOT . '/images/beseated/';
				$tableImage = $this->helper->uplaodServiceImage($file,'Yacht',$yachtID,$serviceID);

				if(!empty($tableImage))
				{
					if(!JFolder::exists($defualtPath.'Yacht/'.$yachtID.'/Services/'.$serviceID.'/thumb'))
					{
						JFolder::create($defualtPath.'Yacht/'.$yachtID .'/Services/'.$serviceID.'/thumb');
					}

					$pathInfo            = pathinfo($defualtPath.$tableImage);
					$fileType            = $pathInfo['extension'];

					$thumbPath           =  $pathInfo['dirname'].'/thumb/thumb_'.$pathInfo['basename'];
					$storeThumbPath      = 'Yacht/'. $yachtID . '/Services/'.$serviceID.'/thumb/thumb_'.$pathInfo['basename'];
					$this->helper->createThumb($defualtPath.$tableImage,$thumbPath);

					$tblImages               = JTable::getInstance('Images','BeseatedTable');
					$tblImages->load(0);
					$tblImages->element_id   = $yachtID;
					$tblImages->service_id   = $serviceID;
					$tblImages->element_type = 'yacht.service';
					$tblImages->thumb_image  = $storeThumbPath;
					$tblImages->image        = $tableImage;
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
							->where($db->quoteName('element_id') . ' = ' . $db->quote($yachtID))
							->where($db->quoteName('service_id') . ' = ' . $db->quote($serviceID))
							->where($db->quoteName('element_type') . ' = ' . $db->quote('yacht.service'));

						// Set the query and execute the update.
						$db->setQuery($query);
						$db->execute();

						$tblImages->is_default = 1;
					}

						$tblImages->store();


				}
			}
		}

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
					->where($db->quoteName('element_id') . ' = ' . $db->quote($yachtID))
					->where($db->quoteName('service_id') . ' = ' . $db->quote($serviceID))
					->where($db->quoteName('element_type') . ' = ' . $db->quote('yacht.service'));

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

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($yachtID))
			->where($db->quoteName('service_id') . ' = ' . $db->quote($serviceID))
			->where($db->quoteName('is_default') . ' = ' . $db->quote('1'))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('yacht.service'));

		// Set the query and load the result.
		$db->setQuery($query);

		$yachtDefaultServiceImage = $db->loadObject();

		if(!empty($yachtDefaultServiceImage))
		{
			$tblService->thumb_image = $yachtDefaultServiceImage->thumb_image;
			$tblService->image       = $yachtDefaultServiceImage->image;
			$tblService->bind($data);
			$tblService->store();
		}


		$tblYacht = JTable::getInstance('Yacht','BeseatedTable');
		$tblYacht->load($yachtID);
		$tblYacht->has_service = 1;
		$tblYacht->store();

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"yacht","extTask":"deleteService","taskData":{"serviceID":"0","yachtID":"1"}}
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
		$yachtID = IJReq::getTaskData('yachtID',0,'int');
		$elementID   = $yachtID;

		if(!$yachtID || !$serviceID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_YACHT_SERVICE_DELETE_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
		$tblService->load($serviceID);

		if(!$tblService->service_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_YACHT_SERVICE_DELETE_NOT_VALID'));
			IJException::setErrorInfo(__FILE__, __LINE__, __CLASS__, __METHOD__, __FUNCTION__);

			return false;
		}

		$tblService->published = 0;

		if(!$tblService->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$this->helper->checkForActiveSubElement($elementID, 'Yacht');

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"venue","yacht":"getNotification","taskData":{"pageNO":"0"}}
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
	 * {"extName":"beseated","extView":"yacht","extTask":"contact","taskData":{"subject":"Testing Subject","message":"This is Testing Message from protectin manager"}}
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
		$yachtDetail = $this->helper->yachtUserDetail($this->IJUserID);
		$userID           = $yachtDetail->user_id;
		$elementID        = $yachtDetail->venue_id;
		$elementType      = 'yacht';
		$this->helper->storeContactRequest($userID,$elementID,$elementType,$subject,$message);
		$this->emailHelper->contactAdmin($subject, $message);
		$this->emailHelper->contactThankYouEmail();
		return $this->jsonarray;
	}
	/* @example the json string will be like, :
	 * {"extName":"beseated","extView":"yacht","extTask":"promotion","taskData":{"subject":"Testing Subject","message":"This is Testing Message from protectin manager"}}
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
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_CONTACT_MESSAGE_DETAIL_INVALID'));
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

		$yachtDetail = $this->helper->yachtUserDetail($this->IJUserID);

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
				$msgData['message_type']  = 'yachtPromotion';
				$msgData['message_body']  = $subject."\n".$message;
				$msgData['extra_params']  = "";
				$msgData['time_stamp']    = time();

				$elementType                = "Yacht";
				$elementID                  = $yachtDetail->yacht_id;
				$cid                        = $yachtDetail->yacht_id;
				$extraParams                = array();
				$extraParams["yachtID"]     = $cid;
				$notificationType           = "yacht.promotion.message";
				$title                      = JText::sprintf(
												'COM_BESEATED_NOTIFICATION_YACHT_PROMOTION_MESSAGE_TO_USER',
												$yachtDetail->yacht_name);

				$tblMessage->bind($msgData);
				if($tblMessage->store())
				{
					//$this->helper->storeNotification($this->IJUserID,$user_id,$elementID,$elementType,$notificationType,$title,$cid,$extraParams);

					// add this user id to send push notification;
					$push_user_ids[] = $user_id;

					$guestDetail = JFactory::getUser($user_id);

					$this->emailHelper->userNewMessageMail($guestDetail->name,$yachtDetail->yacht_name,$message,$subject,$guestDetail->email);
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

		$userID          = $yachtDetail->user_id;
		$elementID       = $yachtDetail->yacht_id;
		$elementType     = 'yacht';
		$this->helper->storePromotionRequest($userID,$elementID,$elementType,$subject,$message,$userDetail->city,count($user_ids));

		$this->jsonarray['code'] = 200;

		$this->jsonarray['pushNotificationData']['id']         = $elementID;
		$this->jsonarray['pushNotificationData']['to']         = implode(',', $user_ids);
		$this->jsonarray['pushNotificationData']['message']    = $message;
		/*$this->jsonarray['pushNotificationData']['type']       = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_YACHT_PROMOTION_MSG_RECEIVED');*/
		$this->jsonarray['pushNotificationData']['type']       = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype'] = '';

		return $this->jsonarray;
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

		$yacht = $this->helper->yachtUserDetail($this->IJUserID);

		$bookingStatus = array();
		$bookingStatus[] = $this->helper->getStatusID('booked');
		$bookingStatus[] = $this->helper->getStatusID('confirmed');
		$bookingStatus[] = $this->helper->getStatusID('canceled');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('yb.*')
			->from($db->quoteName('#__beseated_yacht_booking') . ' AS yb')
			->where($db->quoteName('yb.yacht_id') . ' = ' . $db->quote($yacht->yacht_id))
			->where($db->quoteName('yb.yacht_status') . ' IN ('.implode(',', $bookingStatus).')')
			->where($db->quoteName('yb.deleted_by_yacht') . ' = ' . $db->quote(0))
			->where($db->quoteName('usr.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('yb.booking_date') . ' ASC,'.$db->quoteName('yb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=yb.user_id');

		$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=yb.yacht_status');

		$query->select('ys.service_name,ys.thumb_image,ys.image')
			->join('LEFT','#__beseated_yacht_services AS ys ON ys.service_id=yb.service_id');


		// Set the query and load the result.
		//$db->setQuery($query,$startFrom,$pageLimit);
		$db->setQuery($query);

		$resBookings = $db->loadObjectList();

		if(count($resBookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$resultBookings = array();
		$resultUpcomingBookings = array();
		$resultHistoryBookings = array();

		foreach ($resBookings as $key => $booking)
		{
			$temp = array();
			$temp['yachtBookingID'] = $booking->yacht_booking_id;

			$temp['fullName']    = $booking->full_name;
			$temp['phone']       = $booking->phone;
			$temp['avatar']      = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$temp['thumbAvatar'] = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
			$userDetail          = $this->helper->guestUserDetail($booking->user_id);
			$temp['fbid']        = ($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id)) ? $userDetail->fb_id : '';

			$temp['serviceName']  = $booking->service_name;
			//$temp['thumbImage'] = ($booking->thumb_image)?JUri::root().'image/beseated/'.$booking->thumb_image:'';
			//$temp['image']      = ($booking->image)?JUri::root().'image/beseated/'.$booking->image:'';

			$temp['bookingDate']         = $this->helper->convertDateFormat($booking->booking_date);
			$temp['bookingTime']         = $this->helper->convertToHM($booking->booking_time);
			$temp['isShow']              = $booking->is_show;
			$temp['pricePerHours']       = number_format($booking->price_per_hours,0);
			//$temp['pickupLocation']      = $booking->pickup_location;
			//$temp['dropLocation']        = $booking->dropoff_location;
			$temp['formatedCurrency']    = $this->helper->currencyFormat($booking->booking_currency_sign,$booking->total_price);
			$temp['hoursRquested']       = $booking->total_hours;
			$temp['bookingCurrencyCode'] = $booking->booking_currency_code;
			$temp['totalAmount']         = $this->helper->currencyFormat('',$booking->total_price);
			$temp['statusCode']			 = $booking->yacht_status;
			$temp['isRead']              = $this->helper->isReadBooking('yacht','booking', $booking->yacht_booking_id);
			$temp['isNoShow']            = $booking->is_noshow;
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
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_BOOKINGS_NOT_FOUND'));
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

	function bookYachtService()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$yachtBookingPost      = array();
		$serviceID             = IJReq::getTaskData('serviceID',0,'int');
		$elementID             = IJReq::getTaskData('yachtID',0,'int');
		$bookingDate           = IJReq::getTaskData('bookingDate','','string');
		$bookingTime           = IJReq::getTaskData('bookingTime','','string');
		$totalHours            = IJReq::getTaskData('totalHours','','string');
		$date                  = date('d F Y',strtotime($bookingDate));

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$tblYachtBooking      = JTable::getInstance('YachtBooking', 'BeseatedTable');

		$tblService           = JTable::getInstance('YachtService', 'BeseatedTable');
		$tblService->load($serviceID);

		$tblElement = JTable::getInstance('Yacht', 'BeseatedTable');
		$tblElement->load($elementID);

		if(!$tblService->service_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_SERVICE_FOR_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!$tblElement->yacht_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_COMPANY_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($tblElement->yacht_id != $tblService->yacht_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_SERVICE_OR_COMPANY_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($this->helper->isPastDate($bookingDate))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_INVALID_DATE_FOR_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if(!$this->helper->isTime($bookingTime))
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_INVALID_TIME_FOR_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$yachtBookingPost['yacht_id']              = $elementID;
		$yachtBookingPost['service_id']            = $serviceID;
		$yachtBookingPost['user_id']               = $this->IJUserID;
		$yachtBookingPost['booking_date']          = $this->helper->convertToYYYYMMDD($bookingDate);
		$yachtBookingPost['booking_time']          = $this->helper->convertToHMS($bookingTime);
		$yachtBookingPost['total_hours']           = $totalHours;
		$yachtBookingPost['total_price']           = $tblService->price_per_hours * $totalHours;
		$yachtBookingPost['price_per_hours']       = $tblService->price_per_hours;
		$yachtBookingPost['capacity']       = $tblService->capacity;

		$yachtBookingPost['user_status']           = $this->helper->getStatusID('pending');
		$yachtBookingPost['yacht_status']          = $this->helper->getStatusID('request');
		$yachtBookingPost['booking_currency_code'] = $tblElement->currency_code;
		$yachtBookingPost['booking_currency_sign'] = $tblElement->currency_sign;
		$yachtBookingPost['request_date_time']     = date('Y-m-d H:i:s');
		$yachtBookingPost['time_stamp']            = time();


		$tblYachtBooking->load(0);
		$tblYachtBooking->bind($yachtBookingPost);

		if(!$tblYachtBooking->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_ERROR_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblReadElementRsvp = JTable::getInstance('ReadElementRsvp', 'BeseatedTable');
		$tblReadElementRsvp->load(0);

		$tblReadElementRsvp->booked_type  = 'booking';
		$tblReadElementRsvp->element_type = 'yacht';
		$tblReadElementRsvp->booking_id   = $tblYachtBooking->yacht_booking_id;
		$tblReadElementRsvp->from_user_id = $tblElement->user_id;
		$tblReadElementRsvp->to_user_id   = $this->IJUserID;

		$tblReadElementRsvp->store();

		$userDetail       = $this->helper->guestUserDetail($this->IJUserID);
		$actor            = $this->IJUserID;
		$target           = $tblElement->user_id;
		$elementID        = $tblElement->yacht_id;
		$elementType      = "Yacht";
		$notificationType = "service.request";
		$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_SERVICE_BOOKING_REQUEST_TO_YACHT',
								$userDetail->full_name,
								$tblService->service_name,
								$date,
								$this->helper->convertToHM($bookingTime)
							);
		$cid              = $tblYachtBooking->yacht_booking_id;
		$extraParams      = array();
		$extraParams["yachtBookingID"] = $cid;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$cid))
		{
			$yachtDefaultImage = $this->helper->getElementDefaultImage($tblElement->yacht_id,'Yacht');

			$thumb = Juri::base().'images/beseated/'.$yachtDefaultImage->thumb_image;

			$companyDetail = JFactory::getUser($tblElement->user_id);

			$this->emailHelper->yachtBookingRequestUserMail($companyDetail->name,$thumb,$date,$this->helper->convertToHM($bookingTime),$tblService->service_name,$tblService->dock,$tblService->capacity,$totalHours,$userDetail->full_name,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblYachtBooking->total_price,0),$userDetail->email);
			$this->emailHelper->yachtBookingRequestManagerMail($companyDetail->name,$thumb,$date,$this->helper->convertToHM($bookingTime),$tblService->service_name,$tblService->dock,$tblService->capacity,$totalHours,$userDetail->full_name,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblYachtBooking->total_price,0),$companyDetail->email);
		}

		$this->jsonarray['code'] = 200;

		$this->jsonarray['pushNotificationData']['id']          = $tblYachtBooking->yacht_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Yacht';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		//$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_YACHT_BOOKING_REQUEST_RECEIVED');
		$this->jsonarray['pushNotificationData']['type']        = $notificationType;
		$this->jsonarray['pushNotificationData']['configtype']  = '';

		return $this->jsonarray;
	}

	function changeBookingStatus()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$yachtBookingID = IJReq::getTaskData('yachtBookingID',0,'int');
		$statusCode     = IJReq::getTaskData('statusCode',0,'int');
		$userDetail     = $this->helper->guestUserDetail($this->IJUserID);

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
		$tblYachtBooking->load($yachtBookingID);

		$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
		$tblService->load($tblYachtBooking->service_id);

		$tblElement = JTable::getInstance('Yacht', 'BeseatedTable');
		$tblElement->load($tblYachtBooking->yacht_id);

		if(!$tblYachtBooking->yacht_booking_id || !$statusCode)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_VENUE_GUEST_CHANGE_STATUS_INVALID_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		/*if($tblElement->user_id != $this->IJUserID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_YACHT_OWNER'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}*/

		if($statusCode == 3)
		{
			$tblYachtBooking->user_status = $this->helper->getStatusID('available');
			$tblYachtBooking->yacht_status = $this->helper->getStatusID('awaiting-payment');

			//$tblYachtBooking->remaining_amount = $tblYachtBooking->total_price;
			$totalPrice = $tblYachtBooking->total_price;

			$tblYachtBooking->remaining_amount         = ($tblElement->deposit_per == '0') ? $totalPrice: $totalPrice*$tblElement->deposit_per/100;
			$tblYachtBooking->deposite_price           = ($tblElement->deposit_per == '0') ? $totalPrice: $totalPrice*$tblElement->deposit_per/100;
			$tblYachtBooking->org_remaining_amount     = $totalPrice;

			$notificationType = "yacht.request.accepted";
			$title = JText::sprintf(
						'COM_BESEATED_PUSHNOTIFICATION_USER_BOOKING_REQUEST_ACCEPTED_BY_YACHT',
						$userDetail->full_name,
						$tblService->service_name,
						$this->helper->convertDateFormat($tblYachtBooking->booking_date),
						$this->helper->convertToHM($tblYachtBooking->booking_time)
					);

			$dbTitle = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_USER_BOOKING_REQUEST_ACCEPTED_BY_YACHT',
						$tblService->service_name,
						$this->helper->convertDateFormat($tblYachtBooking->booking_date),
						$this->helper->convertToHM($tblYachtBooking->booking_time),
						$tblYachtBooking->total_hours
					);
		}
		else if($statusCode == 6)
		{

			$tblYachtBooking->user_status = $this->helper->getStatusID('decline');

			$tblYachtBooking->yacht_status = $this->helper->getStatusID('decline');

			$notificationType = "yacht.request.declined";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_USER_BOOKING_REQUEST_DECLINED_BY_YACHT',
									$userDetail->full_name,
									$tblService->service_name,
									$this->helper->convertDateFormat($tblYachtBooking->booking_date),
									$this->helper->convertToHM($tblYachtBooking->booking_time)
								);

			$dbTitle = JText::sprintf(
						'COM_BESEATED_DB_PUSHNOTIFICATION_USER_BOOKING_REQUEST_DECLINED_BY_YACHT',
						$tblService->service_name,
						$this->helper->convertDateFormat($tblYachtBooking->booking_date),
						$this->helper->convertToHM($tblYachtBooking->booking_time),
						$tblYachtBooking->total_hours
					);
		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_INVALID_STATUS'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}


		$tblYachtBooking->response_date_time = date('Y-m-d H:i:s');

		if(!$tblYachtBooking->store())
		{
			IJReq::setResponseCode(500);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_ERROR_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$actor            = $this->IJUserID;
		$target           = $tblYachtBooking->user_id;

		$elementID        = $tblElement->yacht_id;
		$elementType      = "Yacht";
		$cid              = $tblYachtBooking->yacht_booking_id;
		$extraParams      = array();

		$extraParams["yachtBookingID"] = $cid;

		if($this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$dbTitle,$cid,$extraParams,$cid))
		{
			$yachtDefaultImage = $this->helper->getElementDefaultImage($tblElement->yacht_id,'Yacht');
			$bookingDate = date('d F Y',strtotime($tblYachtBooking->booking_date));

			$thumb = Juri::base().'images/beseated/'.$yachtDefaultImage->thumb_image;

			//echo "<pre/>";print_r($thumb);exit;

			$companyDetail = JFactory::getUser($tblElement->user_id);
			$userDetail     = $this->helper->guestUserDetail($tblYachtBooking->user_id);
			//$userDetail    = JFactory::getUser($target);

			if($statusCode == 3)
			{
				$this->emailHelper->yachtBookingAvailableUserMail($userDetail->full_name,$companyDetail->name,$thumb,$bookingDate,$this->helper->convertToHM($tblYachtBooking->booking_time),$tblService->service_name,$tblService->dock,$tblService->capacity,$tblYachtBooking->total_hours,$userDetail->full_name,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblYachtBooking->total_price,0),$userDetail->email,$userDetail->email);
			}
			else if ($statusCode == 6)
			{
				$this->emailHelper->yachtBookingNotAvailableUserMail($userDetail->full_name,$companyDetail->name,$thumb,$bookingDate,$this->helper->convertToHM($tblYachtBooking->booking_time),$tblService->service_name,$tblService->dock,$tblService->capacity,$tblYachtBooking->total_hours,$userDetail->full_name,$userDetail->email,$tblElement->currency_code,number_format($tblService->price_per_hours,0),number_format($tblYachtBooking->total_price,0),$userDetail->email,$userDetail->email);
			}
		}

		$this->jsonarray['code'] = 200;

		/*$pushNotificationTitle = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_GUEST_BOOKING_REQUEST_ACCEPTED_BY_VENUE',
									$tblElement->venue_name,
									$this->helper->convertDateFormat($tblProtectionBooking->booking_date)
								);*/

		$this->jsonarray['pushNotificationData']['id']          = $tblYachtBooking->yacht_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Yacht';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		/*$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_YACHT_BOOKING_REQUEST_STATUS_CHANGED');*/
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

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		$filterEmails       = BeseatedHelper::filterEmails($emails);
		$filterFbFrndEmails = BeseatedHelper::filterFbIds($fbids);

		$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
		$alreadyInvited  = $this->helper->getInvitationDetail($bookingID,'yacht');
		//$alreadySplited       = $this->helper->getSplitedDetail($bookingID,'Yacht');
		//$alreadyInvited = array_merge($alreadyInvited,$alreadySplited);

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

		//echo "<pre/>";print_r($newInvitedEmails);exit;

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

		$tblYachtBooking->load($bookingID);

		$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
		$tblService->load($tblYachtBooking->service_id);

		$tblElement = JTable::getInstance('Yacht', 'BeseatedTable');
		$tblElement->load($tblYachtBooking->yacht_id);

		foreach ($newInvitedEmails as $key => $email)
		{
			$userID = BeseatedHelper::getUserForSplit($email);

			$tblInvitation = JTable::getInstance('Invitation', 'BeseatedTable');
			$tblInvitation->load(0);
			$invitationPost = array();
			$invitationPost['element_booking_id'] = $bookingID;
			$invitationPost['element_id']         = $tblYachtBooking->yacht_id;
			$invitationPost['element_type']       = 'yacht';
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
			$invitationData['element_type']     = 'yacht';
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

			$notificationType = "yacht.service.invitation";
			$title            = JText::sprintf(
									'COM_BESEATED_NOTIFICATION_YACHT_SERVICE_BOOKING_INVITATION',
									$tblService->service_name,
									$this->helper->convertDateFormat($tblYachtBooking->booking_date),
									$this->helper->convertToHM($tblYachtBooking->booking_time)
								);


			$actor       = $this->IJUserID;
			$target      = $userID;
			$elementID   = $tblElement->yacht_id;
			$elementType = "Yacht";
			$cid         = $tblInvitation->invitation_id;
			$extraParams = array();
			$extraParams["yachtBookingID"]  = $tblYachtBooking->yacht_booking_id;
			$extraParams["invitationID"]    = $tblInvitation->invitation_id;
			$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblYachtBooking->yacht_booking_id,$email);

			$this->jsonarray['pushNotificationData']['id']          = $tblYachtBooking->yacht_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Yacht';
			$this->jsonarray['pushNotificationData']['to']          = $target;
			$this->jsonarray['pushNotificationData']['message']     = $title;
			//$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_YACHT_BOOKING_INVITATION_REQUEST');
			$this->jsonarray['pushNotificationData']['type']        = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype']  = '';


			$loginUser   = JFactory::getUser();
			$inviteeName = $loginUser->name;

			$invitedUserName = ($userDetail)?$userDetail->full_name :$email;

			$this->emailHelper->invitationMailUser($invitedUserName,$inviteeName,$tblElement->yacht_name,$tblService->service_name,$this->helper->convertDateFormat($tblYachtBooking->booking_date),$this->helper->convertToHM($tblYachtBooking->booking_time),$isNightVenue = 0,$email);
		}

		$tblYachtBooking->has_invitation = 1;
		$tblYachtBooking->store();

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

		$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
		$alreadyInvited  = $this->helper->getSplitedDetail($bookingID,'Yacht');

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

		$tblYachtBooking->load($bookingID);

		$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
		$tblService->load($tblYachtBooking->service_id);

		$tblElement = JTable::getInstance('Yacht', 'BeseatedTable');
		$tblElement->load($tblYachtBooking->yacht_id);

		$email= $newInvitedEmails[0];

		$userID = BeseatedHelper::getUserForSplit($email);
		$tblYachtBookingSplit = JTable::getInstance('YachtBookingSplit', 'BeseatedTable');
		$tblYachtBookingSplit->load($invitationID);

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
		$tblYachtBookingSplit->bind($splitPost);
		$tblYachtBookingSplit->store();
		$this->deleteReplaceInvitee($bookingID,$invitationID);

		$userDetail       = $this->helper->guestUserDetail($this->IJUserID);
		$actor            = $this->IJUserID;
		$target           = $userID;
		$elementID        = $tblElement->yacht_id;
		$elementType      = "Yacht";
		$notificationType = "yacht.share.invitation.request";
		$title            = JText::sprintf(
								'COM_BESEATED_NOTIFICATION_SHARE_BOOKING_REQUEST_TO_INVITEE_FOR_YACHT',
								$userDetail->full_name,
								$tblService->service_name,
								$this->helper->convertDateFormat($tblYachtBooking->booking_date),
							    $this->helper->convertToHM($tblYachtBooking->booking_time)
							);

		$cid              = $tblYachtBookingSplit->yacht_booking_split_id;
		$extraParams      = array();
		$extraParams["yachtBookingID"] = $tblYachtBooking->yacht_booking_id;
		$extraParams["invitationID"]   = $tblYachtBookingSplit->yacht_booking_split_id;
		$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams,$tblYachtBooking->yacht_booking_id,$email);

		$this->jsonarray['code'] = 200;

		$this->jsonarray['pushNotificationData']['id']          = $tblYachtBooking->yacht_booking_id;
		$this->jsonarray['pushNotificationData']['elementType'] = 'Yacht';
		$this->jsonarray['pushNotificationData']['to']          = $target;
		$this->jsonarray['pushNotificationData']['message']     = $title;
		/*$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_YACHT_SHARE_BOOKING_REQUEST_RECEIVED');*/
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

		$tblYachtBooking      = JTable::getInstance('YachtBooking', 'BeseatedTable');
		$alreadySplited       = $this->helper->getSplitedDetail($bookingID,'Yacht');

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
		else if (empty($notRegiEmail) && !empty($newInvitedEmails))
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

		$tblYachtBooking->load($bookingID);
		$totalAmountToSplit = $tblYachtBooking->total_price;
		$totalSplitCount    = count($newSplitedEmails) + count($alreadySplited);
		$splittedAmount     = $totalAmountToSplit / $totalSplitCount;

		$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
		$tblService->load($tblYachtBooking->service_id);

		$tblElement = JTable::getInstance('Yacht', 'BeseatedTable');
		$tblElement->load($tblYachtBooking->yacht_id);

		$userIDs = array();

		foreach ($newSplitedEmails as $key => $email)
		{
			$userID = BeseatedHelper::getUserForSplit($email);
			$tblYachtBookingSplit = JTable::getInstance('YachtBookingSplit', 'BeseatedTable');
			$tblYachtBookingSplit->load(0);
			$splitPost = array();
			$splitPost['yacht_booking_id']  = $bookingID;
			$splitPost['yacht_id']              = $tblYachtBooking->yacht_id;
			$splitPost['service_id']            = $tblYachtBooking->service_id;
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
			$tblYachtBookingSplit->bind($splitPost);
			$tblYachtBookingSplit->store();

			if($userID !== $this->IJUserID)
			{
				$invitationData = array();

				$tblReadElementBooking = JTable::getInstance('ReadElementBooking', 'BeseatedTable');
				$tblReadElementBooking->load(0);

				$invitationData['booked_type']      = 'share';
				$invitationData['element_type']     = 'yacht';
				$invitationData['booking_id']       = $tblYachtBookingSplit->yacht_booking_split_id;
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
				$elementID        = $tblElement->yacht_id;
				$elementType      = "Yacht";
				$notificationType = "yacht.share.invitation.request";
				$title            = JText::sprintf(
										'COM_BESEATED_NOTIFICATION_SHARE_BOOKING_REQUEST_TO_INVITEE_FOR_YACHT'
										/*$userDetail->full_name,
										$tblService->service_name,
										$this->helper->convertDateFormat($tblYachtBooking->booking_date),
									    $this->helper->convertToHM($tblYachtBooking->booking_time)*/
									);

				$dbTitle            = JText::sprintf(
										'COM_BESEATED_DB_NOTIFICATION_SHARE_BOOKING_REQUEST_TO_INVITEE_FOR_YACHT',
										$tblService->service_name,
										$this->helper->convertDateFormat($tblYachtBooking->booking_date),
									    $this->helper->convertToHM($tblYachtBooking->booking_time)
									);

				$cid              = $tblYachtBookingSplit->yacht_booking_split_id;
				$extraParams      = array();
				$extraParams["yachtBookingID"] = $tblYachtBooking->yacht_booking_id;
				$extraParams["invitationID"]   = $tblYachtBookingSplit->yacht_booking_split_id;
				$this->helper->storeNotification($actor,$target,$elementID,$elementType,$notificationType,$dbTitle,$cid,$extraParams,$tblYachtBooking->yacht_booking_id,$email);
			}
		}

		if(!empty($userIDs))
		{
			$this->jsonarray['pushNotificationData']['id']          = $tblYachtBooking->yacht_booking_id;
			$this->jsonarray['pushNotificationData']['elementType'] = 'Yacht';
			$this->jsonarray['pushNotificationData']['to']          = implode(',', $userIDs);
			$this->jsonarray['pushNotificationData']['message']     = $title;
			/*$this->jsonarray['pushNotificationData']['type']      = JText::_('COM_BESEATED_PUSHNOTIFICATION_TYPE_YACHT_SHARE_BOOKING_REQUEST_RECEIVED');*/
			$this->jsonarray['pushNotificationData']['type']        = $notificationType;
			$this->jsonarray['pushNotificationData']['configtype']  = '';
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_yacht_booking_split'))
			->set($db->quoteName('splitted_amount') . ' = ' . $db->quote($tblYachtBooking->each_person_pay))
			->where($db->quoteName('yacht_booking_id') . ' = ' . $db->quote($bookingID));

		// Set the query and execute the update.
		$db->setQuery($query);
		$db->execute();

		$tblYachtBooking->is_splitted      = 1;
		//$tblYachtBooking->each_person_pay  = $splittedAmount;
		$tblYachtBooking->splitted_count   = $totalSplitCount;
		//$tblYachtBooking->remaining_amount = $totalAmountToSplit;
		$tblYachtBooking->store();

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}

	function bookingShowAction()
	{
		$my = JFactory::getUser();

		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$yachtBookingID     = IJReq::getTaskData('bookingID',0,'int');
		$showAction         = IJReq::getTaskData('showAction',0,'int');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
		$tblYachtBooking->load($yachtBookingID);

		$tblService = JTable::getInstance('YachtService', 'BeseatedTable');
		$tblService->load($tblYachtBooking->service_id);

		$tblYacht = JTable::getInstance('Yacht', 'BeseatedTable');
		$tblYacht->load($tblYachtBooking->yacht_id);


		if($showAction == 1)
		{
			$tblYachtBooking->is_show = 1;
			$tblYachtBooking->is_noshow = 0;
		}
		else if($showAction == 0)
		{
			$tblYachtBooking->is_show = 0;
			$tblYachtBooking->is_noshow = 1;

			$booking_owner = JFactory::getUser($tblYachtBooking->user_id);

			$this->emailHelper->NoShowLuxuryUserMail($booking_owner->name,$tblYacht->yacht_name,$this->helper->convertDateFormat($tblYachtBooking->booking_date),$this->helper->convertToHM($tblYachtBooking->booking_time),$booking_owner->email);
			$this->emailHelper->NoShowLuxuryManagerMail($booking_owner->name,$tblYacht->yacht_name,$my->email,'Yacht',$tblService->service_name);
		}
		else
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_PROTECTION_ERROR_IN_SHOW_ACTION_INVALID_SHOW_ACTION'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		//$tblProtectionBooking->show_action = $showAction;
		if(!$tblYachtBooking->store())
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
		if(!$this->IJUserID)
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

		$yacht = $this->helper->yachtUserDetail($this->IJUserID);

		$bookingStatus = array();
		$bookingStatus[] = $this->helper->getStatusID('booked');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('yb.*')
			->from($db->quoteName('#__beseated_yacht_booking') . ' AS yb')
			->where($db->quoteName('yb.yacht_id') . ' = ' . $db->quote($yacht->yacht_id))
			->where($db->quoteName('yb.yacht_status') . ' IN ('.implode(',', $bookingStatus).')')
			->order($db->quoteName('yb.booking_date') . ' ASC,'.$db->quoteName('yb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.is_deleted')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=yb.user_id');

		$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=yb.yacht_status');

		$query->select('ys.service_name,ys.thumb_image,ys.image,ys.capacity')
			->join('LEFT','#__beseated_yacht_services AS ys ON ys.service_id=yb.service_id');


		// Set the query and load the result.
		//$db->setQuery($query,$startFrom,$pageLimit);
		$db->setQuery($query);

		$resBookings = $db->loadObjectList();

		if(count($resBookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$blacklistedUsers = $this->helper->getBlackListedUser($yacht->yacht_id,'Yacht');

		$resultRevenues = array();
		$revenuePrice = 0;

		foreach ($resBookings as $key => $booking)
		{
			$temp                          = array();
			$temp['yachtBookingID']        = $booking->yacht_booking_id;
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
			$temp['pricePerHours']         = $booking->price_per_hours;
			$temp['totalHours']            = $booking->total_hours;
			$temp['totalPeople']           = $booking->capacity;
			//$temp['bookingTime']         = $this->helper->convertToHM($booking->booking_time);
			$temp['formatedCurrency']      = $this->helper->currencyFormat($booking->booking_currency_sign,$booking->total_price);
			//$temp['bookingCurrencySign'] = $booking->booking_currency_sign;
			//$temp['bookingCurrencyCode'] = $booking->booking_currency_code;
			//$temp['pricePerHours']       = $booking->price_per_hours;
			//$temp['totalGuard']          = $booking->total_guard;
			//$temp['totalGuest']            = $booking->total_guest;
			//$temp['maleGuest']             = $booking->male_guest;
			//$temp['femaleGuest']           = $booking->female_guest;

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
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_REVENUE_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$this->jsonarray['code']         = 200;
		$this->jsonarray['revenueCount'] = count($resultRevenues);
		$this->jsonarray['revenues']     = $resultRevenues;
		$this->jsonarray['totalRevenue'] = $this->helper->currencyFormat('',$revenuePrice);

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
			->where($db->quoteName('notification_type') . ' = ' . $db->quote('yacht.share.invitation.request'))
			->where($db->quoteName('actor') . ' = ' . $db->quote((int) $this->IJUserID))
			->where($db->quoteName('cid') . ' = ' . $db->quote((int) $invitationID));

		// Set the query and execute the delete.
		$db->setQuery($query);

		$notif_data = $db->loadObjectList();

		foreach ($notif_data as $key => $value)
		{
			$extra_pramas = $value->extra_pramas;

			if(json_decode($extra_pramas)->yachtBookingID == $bookingID)
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

		if($pageNO==0 || $pageNO==1)
		{
			$startFrom=0;
		}
		else
		{
			$startFrom = $pageLimit*($pageNO-1);
		}

		$yacht = $this->helper->yachtUserDetail($this->IJUserID);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$rsvpStatus = array();
		$rsvpStatus[] = $this->helper->getStatusID('request');
		$rsvpStatus[] = $this->helper->getStatusID('awaiting-payment');
		$rsvpStatus[] = $this->helper->getStatusID('decline');

		// Create the base select statement.
		$query->select('yb.*')
			->from($db->quoteName('#__beseated_yacht_booking') . ' AS yb')
			->where($db->quoteName('yb.yacht_id') . ' = ' . $db->quote($yacht->yacht_id))
			->where($db->quoteName('yb.yacht_status') . ' IN ('.implode(',', $rsvpStatus).')')
			//->where($db->quoteName('yb.yacht_status') . ' IN ('.implode(',', $rsvpStatus).')')
			->where($db->quoteName('yb.deleted_by_yacht') . ' = ' . $db->quote(0))
			->where($db->quoteName('usr.is_deleted') . ' = ' . $db->quote(0))
			->order($db->quoteName('yb.booking_date') . ' ASC,'.$db->quoteName('yb.booking_time') . ' ASC');

		$query->select('usr.full_name,usr.phone,usr.avatar,usr.thumb_avatar')
			->join('LEFT','#__beseated_user_profile AS usr ON usr.user_id=yb.user_id');

		/*$query->select('st.status_display AS status_text')
			->join('LEFT','#__beseated_status AS st ON st.status_id=pb.protection_status');*/

		$query->select('y.yacht_name')
			->join('LEFT','#__beseated_yacht AS y ON y.yacht_id=yb.yacht_id');

		$query->select('ys.service_name,ys.thumb_image,ys.image,ys.capacity')
			->join('LEFT','#__beseated_yacht_services AS ys ON ys.service_id=yb.service_id');


		// Set the query and load the result.
		//$db->setQuery($query,$startFrom,$pageLimit);
		$db->setQuery($query);

		$resBookings = $db->loadObjectList();
		if(count($resBookings) == 0)
		{
			IJReq::setResponseCode(204);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_BOOKINGS_NOT_FOUND'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$resultBookings = array();
		$resultUpcomingBookings = array();
		$resultHistoryBookings = array();
		foreach ($resBookings as $key => $booking)
		{
			$temp = array();
			$temp['yachtBookingID'] = $booking->yacht_booking_id;

			$temp['fullName']    = $booking->full_name;
			$temp['phone']       = $booking->phone;
			$temp['avatar']      = ($booking->avatar)?$this->helper->getUserAvatar($booking->avatar):'';
			$temp['thumbAvatar'] = ($booking->thumb_avatar)?$this->helper->getUserAvatar($booking->thumb_avatar):'';
			$userDetail          = $this->helper->guestUserDetail($booking->user_id);
		    $temp['fbid']        = ($userDetail->is_fb_user == '1' && !empty($userDetail->fb_id)) ? $userDetail->fb_id : '';
			$temp['yachtName']   = $booking->yacht_name;
			$temp['serviceName'] = $booking->service_name;
			//$temp['thumbImage'] = ($booking->thumb_image)?JUri::root().'image/beseated/'.$booking->thumb_image:'';
			//$temp['image']      = ($booking->image)?JUri::root().'image/beseated/'.$booking->image:'';

			$temp['bookingDate']         = $this->helper->convertDateFormat($booking->booking_date);
			$temp['bookingTime']         = $this->helper->convertToHM($booking->booking_time);
			$temp['formatedCurrency']    = $this->helper->currencyFormat($booking->booking_currency_sign,$booking->total_price);
			$temp['pricePerHours']       = $this->helper->currencyFormat('',$booking->price_per_hours);
			$temp['hoursRquested']       = $booking->total_hours;
			$temp['statusCode']          = $booking->yacht_status;
			$temp['totalPeople']         = $booking->capacity;
			$temp['bookingCurrencyCode'] = $booking->booking_currency_code;
			$temp['totalAmount']         = $this->helper->currencyFormat('',$booking->total_price);
			$temp['isRead']              = $this->helper->isReadRSVP('yacht','booking', $booking->yacht_booking_id);
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
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_RSVP_NOT_FOUND'));
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
	 * {"extName":"beseated","extView":"yacht","extTask":"addUserToBlackList","taskData":{"userID":"0"}}
	 */
	function addUserToBlackList()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$yacht     = $this->helper->yachtUserDetail($this->IJUserID);
		$userID    = IJReq::getTaskData('userID',0, 'int');

		if(!$userID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_USER_FOR_BLACKLIST_ADD'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$isBlackListed = $this->helper->checkBlackList($userID,$yacht->yacht_id,'Yacht');

		if($isBlackListed)
		{
			IJReq::setResponseCode(707);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_USER_ALREDY_ADD_IN_BLACKLIST'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$result = $this->helper->addUserToBlackList($userID,$yacht->yacht_id,'Yacht');

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
	 * {"extName":"beseated","extView":"yacht","extTask":"removeUserFromBlackList","taskData":{"userID":"0"}}
	 */
	function removeUserFromBlackList()
	{
		if(!$this->IJUserID)
		{
			IJReq::setResponse(704);
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		} // End of login Condition

		$yacht     = $this->helper->yachtUserDetail($this->IJUserID);
		$userID    = IJReq::getTaskData('userID',0, 'int');

		if(!$userID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_USER_FOR_BLACKLIST_ADD'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$isBlackListed = $this->helper->removeUserFromBlackList($userID,$yacht->yacht_id,'Yacht');

		$this->jsonarray['code'] = 200;

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

		$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
		$tblYachtBooking->load($bookingID);

		$tblElement = JTable::getInstance('Yacht', 'BeseatedTable');
		$tblElement->load($tblYachtBooking->yacht_id);

		if(!$tblYachtBooking->yacht_booking_id)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_INVALID_DETAIL'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		if($tblElement->user_id != $this->IJUserID)
		{
			IJReq::setResponseCode(400);
			IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_BESEATED_YACHT_SERVICE_OR_COMPANY_IN_BOOKING'));
			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
			return false;
		}

		$tblYachtBooking->deleted_by_yacht = 1;

		if(!$tblYachtBooking->store())
		{
			$this->jsonarray['code'] = 500;
			return $this->jsonarray;
		}

		$this->jsonarray['code'] = 200;
		return $this->jsonarray;
	}


}