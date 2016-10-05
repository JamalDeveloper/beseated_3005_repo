<?php
/**
 * @package     iJoomerAdv.Site
 * @subpackage  com_ijoomeradv
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

//defined('_JEXEC') or die;
jimport('joomla.application.component.helper');

class beseatedAppHelper
{
	private $date_now;

	private $IJUserID;

	private $mainframe;

	private $db;

	private $my;

	private $config;

	function __construct()
	{
		$this->date_now  = JFactory::getDate();
		$this->mainframe = JFactory::getApplication();
		$this->db        = JFactory::getDbo();
		$this->IJUserID  = $this->mainframe->getUserState('com_ijoomeradv.IJUserID', 0);
		$this->my        = JFactory::getUser($this->IJUserID);
		$this->config    = JFactory::getConfig();

	}

	/**
	 *  Method to get Login User Type
	 *
	 * @param   integer  $userID  User ID.
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public function getUserFavourites($userID,$elementType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('element_id')
			->from($db->quoteName('#__beseated_favourite'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote(ucfirst($elementType)));

		// Set the query and load the result.
		$db->setQuery($query);

		$favourite = $db->loadColumn();
		return $favourite;
	}

	/**
	 *  Method to get Login User Type
	 *
	 * @param   integer  $userID  User ID.
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public function getUserType($userID)
	{
		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

		$user           = JFactory::getUser($userID);
		$beseatedParams = BeseatedHelper::getExtensionParam();
		$groups         = $user->get('groups');

		if(in_array($beseatedParams->beseated_guest, $groups))
		{
			return "Guest";
		}
		else if(in_array($beseatedParams->chauffeur, $groups))
		{
			return "Chauffeur";
		}
		else if(in_array($beseatedParams->protection, $groups))
		{
			return "Protection";
		}
		else if(in_array($beseatedParams->venue, $groups))
		{
			return "Venue";
		}
		else if(in_array($beseatedParams->yacht, $groups))
		{
			return "Yacht";
		}

		return false;
	}

	/**
	 *  Method to get chauffeurUserDetail profile
	 *
	 * @param   integer  $userID  User ID.
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public function guestUserDetail($userID)
	{
		// Initialiase variables.
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->quoteName('#__beseated_user_profile'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($userID));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	/**
	 *  Method to get guestUserDetailFromEmail
	 *
	 * @param   integer  $userID  User ID.
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public function guestUserDetailFromEmail($email)
	{
		// Initialiase variables.
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->quoteName('#__beseated_user_profile'))
			->where($this->db->quoteName('email') . ' = ' . $this->db->quote($email))
			->where($this->db->quoteName('is_deleted') . ' = ' . $this->db->quote('0'));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	/**
	 *  Method to get chauffeurUserDetail profile
	 *
	 * @param   integer  $userID  User ID.
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public function chauffeurUserDetail($userID)
	{
		// Initialiase variables.
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->quoteName('#__beseated_chauffeur'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($userID));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	/**
	 *  Method to get chauffeurUserDetail profile
	 *
	 * @param   integer  $userID  User ID.
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public function protectionUserDetail($userID)
	{
		// Initialiase variables.
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->quoteName('#__beseated_protection'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($userID));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	/**
	 *  Method to get chauffeurUserDetail profile
	 *
	 * @param   integer  $userID  User ID.
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public function venueUserDetail($userID)
	{
		// Initialiase variables.
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->quoteName('#__beseated_venue'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($userID));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	public function getVenueDetail($venue_id)
	{
		// Initialiase variables.
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->quoteName('#__beseated_venue'))
			->where($this->db->quoteName('venue_id') . ' = ' . $this->db->quote($venue_id));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	/**
	 *  Method to get chauffeurUserDetail profile
	 *
	 * @param   integer  $userID  User ID.
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public function yachtUserDetail($userID)
	{
		// Initialiase variables.
		$query = $this->db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($this->db->quoteName('#__beseated_yacht'))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($userID));

		// Set the query and load the result.
		$this->db->setQuery($query);

		try
		{
			$result = $this->db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	/**
	 *  Method to get Element profile default image
	 *
	 * @param   integer  $elementID  Element ID.
	 *
	 * @param   string  $elementType  Element Type.
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public function getElementDefaultImage($elementID,$elementType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote(ucfirst($elementType)))
			->where($db->quoteName('is_default') . ' = ' . $db->quote('1'));
			//->order($db->quoteName('element_type') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadObject();

		if(empty($result))
		{
			$result = $this->getElementImage($elementID,$elementType);
		}

		/*$returnImg = array();
		if(count($result))
		{
			$returnImg['thumbImage'] = ($result[0]->thumb_image)?$corePath.$result[0]->thumb_image:'';
			$returnImg['image'] = ($result[0]->image)?$corePath.$result[0]->image:'';
		}
		else
		{
			$returnImg['thumbImage'] = '';
			$returnImg['image'] = '';
		}*/

		return $result;
	}

	/**
	 *  Method to get chauffeurUserDetail profile
	 *
	 * @param   integer  $elementID  Element ID.
	 *
	 * @param   string  $elementType  Element Type.
	 *
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public function getUserAvatar($image)
	{
		if(empty($image)){
			return '';
		}

		$url = parse_url($image);
		if(is_array($url) && isset($url['scheme']) && $url['scheme'] == 'https'){
			return $image;
		}
		else if(is_array($url) && isset($url['scheme']) && $url['scheme'] == 'http'){
			return $image;
		}else{
			return JUri::base().'images/beseated/'.$image;
		}
	}

	/**
	 *  Method to get chauffeurUserDetail profile
	 *
	 * @param   integer  $elementID  Element ID.
	 *
	 * @param   string  $elementType  Element Type.
	 *
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public function checkForActiveSubElement($elementID,$elementType)
	{
		$isValid = true;
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		switch ($elementType) {
			case 'Venue':
				$tblElement = JTable::getInstance('Venue', 'BeseatedTable');
				$tblElement->load($elementID);
				break;

			case 'Yacht':
				$tblElement = JTable::getInstance('Yacht', 'BeseatedTable');
				$tblElement->load($elementID);
				break;

			case 'Chauffeur':
				$tblElement = JTable::getInstance('Chauffeur', 'BeseatedTable');
				$tblElement->load($elementID);
				break;

			case 'Protection':
				$tblElement = JTable::getInstance('Protection', 'BeseatedTable');
				$tblElement->load($elementID);
				break;

			default:
				$isValid = false;
				break;
		}

		if(!$isValid)
		{
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		switch ($elementType) {
			case 'Venue':
				// Create the base select statement.
				$query->select('count(1)')
					->from($db->quoteName('#__beseated_venue_table'))
					->where($db->quoteName('venue_id') . ' = ' . $db->quote($elementID))
					->where($db->quoteName('published') . ' = ' . $db->quote('1'));
				break;

			case 'Yacht':
				// Create the base select statement.
				$query->select('count(1)')
					->from($db->quoteName('#__beseated_yacht_services'))
					->where($db->quoteName('yacht_id') . ' = ' . $db->quote($elementID))
					->where($db->quoteName('published') . ' = ' . $db->quote('1'));
				break;

			case 'Chauffeur':
				// Create the base select statement.
				$query->select('count(1)')
					->from($db->quoteName('#__beseated_chauffeur_services'))
					->where($db->quoteName('chauffeur_id') . ' = ' . $db->quote($elementID))
					->where($db->quoteName('published') . ' = ' . $db->quote('1'));
				break;

			case 'Protection':
				// Create the base select statement.
				$query->select('count(1)')
					->from($db->quoteName('#__beseated_protection_services'))
					->where($db->quoteName('protection_id') . ' = ' . $db->quote($elementID))
					->where($db->quoteName('published') . ' = ' . $db->quote('1'));
				break;

			default:
				$isValid = 0;
				break;
		}

		// Set the query and execute the insert.
		$db->setQuery($query);

		$hasActiveSubElement = $db->loadResult();

		if($hasActiveSubElement)
		{
			if($elementType == 'Venue')
			{
				$tblElement->has_table = 1;
			}
			else
			{
				$tblElement->has_service = 1;
			}

			$tblElement->store();
			return true;
		}
		else
		{
			if($elementType == 'Venue')
			{
				$tblElement->has_table = 0;
			}
			else
			{
				$tblElement->has_service = 0;
			}

			$tblElement->store();
			return true;
		}
	}

	/**
	 *  Method to check give date is past date or not
	 *
	 * @param   string  $date  Date in any format
	 *
	 *
	 * @return boolean
	 *
	 * @since    1.0
	 */
	public function isPastDate($date)
	{
		$currentDate = date('Y-m-d');
		$date = new DateTime($date);
		$now  = new DateTime($currentDate);

		/*echo "<pre>";
		print_r($date);
		echo "</pre>";
		exit;*/
		/*(
		    [date] => 2015-12-11 00:00:00
		    [timezone_type] => 3
		    [timezone] => America/Chicago
		)*/
		/*echo "<pre>";
		print_r($now);
		echo "</pre>";
		exit;
		(
		    [date] => 2015-12-11 03:12:50
		    [timezone_type] => 3
		    [timezone] => America/Chicago
		)*/
		if($date < $now) {
		    return true;
		}

		return false;
	}

	public function isPastDateTime($dateTime)
	{
		$currentDate = strtotime(date('Y-m-d H:i:s'));
		$dateTime    = strtotime($dateTime);

		if($dateTime < $currentDate) {
		    return true;
		}

		return false;
	}

	public static function getUserTimezoneDifferent($userID)
	{
		$userDetail = $this->guestUserDetail($userID);

		if(!$userDetail->latitude)
		{ $userDetail->latitude = 51.50655216115383; }

		if(!$userDetail->longitude)
		{ $userDetail->longitude = -0.1327800750732422; }
		$url = "http://api.timezonedb.com/?lat=".$userDetail->latitude."&lng=".$userDetail->longitude."&key=A6IFVHFFCUJ1&format=json";


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($json);
		return (int)$data->gmtOffset;
	}

	/**
	 *  Method to Convert date to specific format
	 *
	 * @param   string  $date  Date in any format
	 *
	 *
	 * @return String
	 *
	 * @since    1.0
	 */
	public function convertDateFormat($date,$format = 'd-m-Y')
	{

		return date($format,strtotime($date));
	}

	/**
	 *  Method to Convert date to YYYY-MM-DD format
	 *
	 * @param   string  $date  Date in any format
	 *
	 *
	 * @return String
	 *
	 * @since    1.0
	 */
	public function convertToYYYYMMDD($date)
	{
		return date('Y-m-d',strtotime($date));
	}

	/**
	 *  Method to Convert date to DD-MM-YYYY format
	 *
	 * @param   string  $date  Date in any format
	 *
	 *
	 * @return String
	 *
	 * @since    1.0
	 */
	public function convertToDDMMYYYY($date)
	{
		return date('d-m-Y',strtotime($date));
	}

	/**
	 *  Method to Convert Time to Hours and minute and seconds
	 *
	 * @param   string  $time  Time in any Format.
	 *
	 * @param   boolean  $is24Hours  Is time in 24 hours format
	 *
	 * @param   boolean  $seconds  Is seconds included in check format
	 *
	 *
	 * @return boolean
	 *
	 * @since    1.0
	 */
	public function isTime($time,$is24Hours=true,$seconds=false)
	{
		$timeArray = explode(":", $time);
		if(count($timeArray) == 3)
			$seconds = true;

		$pattern = "/^".($is24Hours ? "([1-2][0-3]|[01]?[1-9])" : "(1[0-2]|0?[1-9])").":([0-5]?[0-9])".($seconds ? ":([0-5]?[0-9])" : "")."$/";
		if (preg_match($pattern, $time))
		{
		    return true;
		}
		return false;
	}

	/**
	 *  Method to Convert Time to Hours and minute and seconds
	 *
	 * @param   string  $time  Time in any Format.
	 *
	 *
	 * @return String
	 *
	 * @since    1.0
	 */
	public function convertToHMS($time)
	{
		$timeFormat = explode(":", $time);

		if(count($timeFormat)==1)
		{
			$hmsTime = $timeFormat[0].":"."00:00";
		}
		else if(count($timeFormat)==2)
		{
			$hmsTime = $timeFormat[0].":".$timeFormat[1].":00";
		}
		else if(count($timeFormat)>=3)
		{
			$hmsTime = $timeFormat[0].":".$timeFormat[1].":".$timeFormat[2];
		}

		return $hmsTime;
	}

	/**
	 *  Method to Convert Time to Hours and minute
	 *
	 * @param   string  $time  Time in any Format.
	 *
	 *
	 * @return String
	 *
	 * @since    1.0
	 */
	public function convertToHM($time)
	{
		$timeFormat = explode(":", $time);

		if(count($timeFormat)==1)
		{
			$hmsTime = $timeFormat[0].":"."00";
		}
		else if(count($timeFormat)==2)
		{
			$hmsTime = $timeFormat[0].":".$timeFormat[1];
		}
		else if(count($timeFormat)>=3)
		{
			$hmsTime = $timeFormat[0].":".$timeFormat[1];
		}

		return $hmsTime;
	}

	/**
	 *  Method to Get Status ID from status name
	 *
	 * @param   string  $statusName  Name of status
	 *
	 *
	 * @return int
	 *
	 * @since    1.0
	 */
	public function getStatusID($statusName)
	{
		if(empty($statusName))
		{
			return 0;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_status'))
			->where($db->quoteName('status_name') . ' = ' . $db->quote($statusName));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result->status_id;

	}

	/**
	 *  Method to Get Status Name from status ID
	 *
	 * @param   int  $statusID  ID of status
	 *
	 *
	 * @return String
	 *
	 * @since    1.0
	 */
	public function getStatusName($statusID)
	{
		if(!$statusID)
		{
			return '';
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_status'))
			->where($db->quoteName('status_id') . ' = ' . $db->quote($statusID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result->status_display;

	}

	/**
	 *  Method to Get Status Name from status ID
	 *
	 * @param   string  $currencySign  Currency Sign
	 *
	 * @param   int  $amount  amount of service or tables
	 *
	 * @param   int  $allowDecimal  allow to decimal or not
	 *
	 *
	 * @return String
	 *
	 * @since    1.0
	 */
	public function currencyFormat($currencySign,$amount,$allowDecimal = 0)
	{
		$amount = number_format($amount,$allowDecimal);

		return $currencySign.' '.$amount;
	}

	/**
	 *  Method to Check if user is already exists in blacklist
	 *
	 * @param   int  $userID  User ID
	 *
	 * @param   int  $elementID  Element ID
	 *
	 * @param   int  $elementType  Element Type
	 *
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function checkForAlreadyGuestList($venueID, $userID,$date)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$date = $this->convertToYYYYMMDD($date);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_guest_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('booking_date') . ' = ' . $db->quote($date))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return count($result);
	}

	/**
	 *  Method to Check if user is already exists in blacklist
	 *
	 * @param   int  $userID  User ID
	 *
	 * @param   int  $elementID  Element ID
	 *
	 * @param   int  $elementType  Element Type
	 *
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function checkBlackList($userID,$elementID,$elementType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_black_list'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 *  Method to Add Notification
	 *
	 * @param   int  $actor  Actor User ID
	 *
	 * @param   int  $target  Target User ID
	 *
	 * @param   int  $elementID  Element ID
	 *
	 * @param   string  $elementType  Element Type
	 *
	 * @param   string  $notificationType  Notification Type
	 *
	 * @param   string  $title  Message
	 *
	 * @param   array  $extraParams  Extra params if needed
	 *
	 * @return boolean
	 *
	 * @since    1.0
	 */
	public function storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams = array(),$bookingID = 0,$email = NULL)
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblNotification = JTable::getInstance('Notification', 'BeseatedTable');
		$tblNotification->load(0);
		$notificationPost['actor']             = $actor;
		$notificationPost['target']            = $target;
		$notificationPost['element_id']        = $elementID;
		$notificationPost['element_type']      = $elementType;
		$notificationPost['notification_type'] = $notificationType;
		$notificationPost['title']             = $title;
		$notificationPost['cid']               = $cid;
		$notificationPost['booking_id']        = $bookingID;
		$notificationPost['email']             = $email;
		$notificationPost['extra_pramas']      = json_encode($extraParams);
		$notificationPost['time_stamp']        = time();
		$tblNotification->bind($notificationPost);

		if($tblNotification->store())
		{
			return true;
		}
		return false;
	}

	/**
	 *  Method to Check if user is already exists in blacklist
	 *
	 * @param   int  $userID  User ID
	 *
	 * @param   int  $elementID  Element ID
	 *
	 * @param   int  $elementType  Element Type
	 *
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function getBlackListedUser($elementID,$elementType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('user_id')
			->from($db->quoteName('#__beseated_black_list'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadColumn();

		return $result;
	}

	/**
	 *  Method to Check if user is already exists in blacklist
	 *
	 * @param   int  $userID  User ID
	 *
	 * @param   int  $elementID  Element ID
	 *
	 * @param   int  $elementType  Element Type
	 *
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function getBlackListedElementOfUser($userID,$elementType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('element_id')
			->from($db->quoteName('#__beseated_black_list'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadColumn();

		return $result;
	}

	/**
	 *  Method to  Add user in blacklist
	 *
	 * @param   int  $userID  User ID
	 *
	 * @param   int  $elementID  Element ID
	 *
	 * @param   int  $elementType  Element Type
	 *
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function addUserToBlackList($userID,$elementID,$elementType)
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblBlackList = JTable::getInstance('BlackList', 'BeseatedTable');
		$tblBlackList->load(0);
		$blackListPost['element_id']   = $elementID;
		$blackListPost['element_type'] = $elementType;
		$blackListPost['user_id']      = $userID;
		$blackListPost['time_stamp']   = time();
		$tblBlackList->bind($blackListPost);
		if($tblBlackList->store())
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base delete statement.
			$query->delete()
				->from($db->quoteName('#__beseated_favourite'))
				->where($db->quoteName('element_id') . ' = ' . $db->quote((int) $elementID))
				->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
				->where($db->quoteName('user_id') . ' = ' . $db->quote((int) $userID));

			// Set the query and execute the delete.
			$db->setQuery($query);
			$db->execute();

			return true;
		}
		return false;
	}

	/**
	 *  Method to  Remove user from blacklist
	 *
	 * @param   int  $userID  User ID
	 *
	 * @param   int  $elementID  Element ID
	 *
	 * @param   int  $elementType  Element Type
	 *
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function removeUserFromBlackList($userID,$elementID,$elementType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->delete()
			->from($db->quoteName('#__beseated_black_list'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote((int) $elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
			->where($db->quoteName('user_id') . ' = ' . $db->quote((int) $userID));

		// Set the query and execute the delete.
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 *  Method to  get Splited Booking Detail
	 *
	 * @param   int  $bookingID  Booking ID
	 *
	 * @param   int  $elementType  Element Type
	 *
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function getInvitationDetail($bookingID,$bookingType = '')
	{
		if(!$bookingID || empty($bookingType))
		{
			return array();
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		if($bookingType == 'event'){
			$query->select('*')
				->from($db->quoteName('#__beseated_event_ticket_booking_invite'))
				->where($db->quoteName('ticket_booking_id') . ' = ' . $db->quote($bookingID))
				->order($db->quoteName('time_stamp') . ' ASC');
		}else{
			$query->select('*')
				->from($db->quoteName('#__beseated_invitation'))
				->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($bookingID))
				->where($db->quoteName('element_type') . ' = ' . $db->quote($bookingType))
				->order($db->quoteName('time_stamp') . ' ASC');
		}

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 *  Method to  get Splited Booking Detail
	 *
	 * @param   int  $bookingID  Booking ID
	 *
	 * @param   int  $elementType  Element Type
	 *
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function getSplitedDetail($bookingID,$bookingType = '')
	{
		if(!$bookingID || empty($bookingType))
		{
			return array();
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*');
		if($bookingType == 'Chauffeur'){
			$query->from($db->quoteName('#__beseated_chauffeur_booking_split'));
			$query->where($db->quoteName('chauffeur_booking_id') . ' = ' . $db->quote($bookingID));
		}
		else if ($bookingType == 'Protection'){
			$query->from($db->quoteName('#__beseated_protection_booking_split'));
			$query->where($db->quoteName('protection_booking_id') . ' = ' . $db->quote($bookingID));
		}
		else if ($bookingType == 'Venue'){
			$query->from($db->quoteName('#__beseated_venue_table_booking_split'));
			$query->where($db->quoteName('venue_table_booking_id') . ' = ' . $db->quote($bookingID));
		}
		else if ($bookingType == 'Yacht'){
			$query->from($db->quoteName('#__beseated_yacht_booking_split'));
			$query->where($db->quoteName('yacht_booking_id') . ' = ' . $db->quote($bookingID));
		}
		else
		{
			return array();
		}

		$query->order($db->quoteName('time_stamp') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 *  Method to  get biggest spender on venue
	 *
	 * @param   int  $bookingID  Booking ID
	 *
	 * @param   int  $elementType  Element Type
	 *
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function getBiggestSpender($venue_id)
	{
		$statusID = $this->getStatusID('booked');
		// Initialiase variables.
		$db    = JFactory::getDbo();

		$queryLiveUser = $db->getQuery(true);

		$queryLiveUser->select('id')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('block') . ' = ' . $db->quote(0));
		$db->setQuery($queryLiveUser);

		$resLiveUsers = $db->loadColumn();

		$query = $db->getQuery(true);
		$query->select('SUM(total_price) as total_price,user_id')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venue_id))
			->where($db->quoteName('user_status') . ' = ' . $db->quote($statusID))
			->group($db->quoteName('user_id'))
			->order($db->quoteName('total_price') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);
		$resBigSpender = $db->loadObjectList();

		$resultSpender = array();

		foreach ($resBigSpender as $key => $spender)
		{
			if(in_array($spender->user_id, $resLiveUsers))
			{
				$spenderDetail = $this->guestUserDetail($spender->user_id);

				if($spenderDetail->show_in_biggest_spender)
				{
					$temp = array();
					$temp['userID']      = $spenderDetail->user_id;
					$temp['fbID']        = ($spenderDetail->fb_id)?$spenderDetail->fb_id:'';
					$temp['avatar']      = ($spenderDetail->avatar)?$this->getUserAvatar($spenderDetail->avatar):'';
					$temp['thumbAvatar'] = ($spenderDetail->thumb_avatar)?$this->getUserAvatar($spenderDetail->thumb_avatar):'';
					$resultSpender[]     = $temp;
				}
			}

			if(count($resultSpender) >=3)
			{
				return $resultSpender;
			}

		}



		return $resultSpender;
	}

	/**
	 *  Method to  Add user in blacklist
	 *
	 * @param   int  $userID  User ID
	 *
	 * @param   int  $elementID  Element ID
	 *
	 * @param   int  $elementType  Element Type
	 *
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function sendEmail($emailAddress,$subject,$body)
	{
		// Initialise variables.
		$app     = JFactory::getApplication();
		$config  = JFactory::getConfig();

		$site    = $config->get('sitename');
		$from    = $config->get('mailfrom');
		$sender  = $config->get('fromname');
		$email   = $emailAddress;
		$subject = $subject;

		// Build the message to send.
		$msg     = $body;
		$body    = sprintf($msg, $site, $sender, $from);

		// Clean the email data.
		$sender  = JMailHelper::cleanAddress($sender);
		$subject = JMailHelper::cleanSubject($subject);
		$body    = JMailHelper::cleanBody($body);

		// Send the email.
		$return = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body,true);

		// Check for an error.
		if ($return !== true)
		{
			return new JException(JText::_('COM__SEND_MAIL_FAILED'), 500);
		}
	}

	/**
	 *  Method to  Store Contact request
	 *
	 * @param   int  $userID  User ID
	 *
	 * @param   int  $elementID  Element ID
	 *
	 * @param   string  $elementType  Element Type
	 *
	 * @param   string  $subject  email subject
	 *
	 * @param   string  $message  email message
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function storeContactRequest($userID,$elementID,$elementType,$subject,$message)
	{
		$timeStamp = time();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->insert($db->quoteName('#__beseated_contact'))
			->columns(
				array(
					$db->quoteName('user_id'),
					$db->quoteName('element_id'),
					$db->quoteName('element_type'),
					$db->quoteName('subject'),
					$db->quoteName('message'),
					$db->quoteName('time_stamp')
				)
			)
			->values(
				$db->quote($userID) . ', ' .
				$db->quote($elementID) . ', ' .
				$db->quote($elementType) . ', ' .
				$db->quote($subject) . ', ' .
				$db->quote($message) . ', ' .
				$db->quote($timeStamp)
			);

		// Set the query and execute the insert.
		$db->setQuery($query);
		$db->execute();
	}

	public function storePromotionRequest($userID,$elementID,$elementType,$subject,$message,$city,$peopleCount)
	{
		$timeStamp = time();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->insert($db->quoteName('#__beseated_promotion_message'))
			->columns(
				array(
					$db->quoteName('user_id'),
					$db->quoteName('element_id'),
					$db->quoteName('element_type'),
					$db->quoteName('subject'),
					$db->quoteName('message'),
					$db->quoteName('city'),
					$db->quoteName('people_count'),
					$db->quoteName('time_stamp')
				)
			)
			->values(
				$db->quote($userID) . ', ' .
				$db->quote($elementID) . ', ' .
				$db->quote($elementType) . ', ' .
				$db->quote($subject) . ', ' .
				$db->quote($message) . ', ' .
				$db->quote($city) . ', ' .
				$db->quote($peopleCount) . ', ' .
				$db->quote($timeStamp)
			);

		// Set the query and execute the insert.
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 *  Method to  Get Element Images
	 *
	 * @param   int  $elementID  Element ID
	 *
	 * @param   int  $elementType  Element Type
	 *
	 *
	 * @return object
	 *
	 * @since    1.0
	 */
	public function getElementImage($elementID,$elementType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote(ucfirst($elementType)));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 *  Method to check wether this venue table available or not on this specific date and time
	 *
	 * @param   array  $file  Uploaded file attributed like tmp_name,name,size,error
	 *
	 * @param   string  $elementType  Type of element e.g. venue chauffeur, yacht,protection etc
	 *
	 * @param   int  $elementID  id of element e.g. venue chauffeur, yacht,protection etc
	 *
	 * @return string
	 *
	 * @since    1.0
	 */
	public function checkForVenueTableAvaibility($venueID,$tableID,$is_day_club,$date,$fromTime,$toTime)
	{
		$currentBookingDate = $date;
		$datesArray         = array();

		if($is_day_club)
		{
			$datesArray[]       = date('Y-m-d', strtotime($currentBookingDate .' -1 day'));   // 6-04-16
			$datesArray[]       = date('Y-m-d', strtotime($currentBookingDate .' +1 day'));  // 8-04-16
		}

		$datesArray[]       = $date;

		$currentBookingFrom = $fromTime;
		$currentBookingTo   = $toTime;

		$bookingStatus[] = $this->getStatusID('booked');
		$bookingStatus[] = $this->getStatusID('confirmed');

		if(!$toTime){
			$currentBookingFromTS = date('Y-m-d H:i:s', strtotime($currentBookingFrom));
			$currentBookingTo= date('H:i:s', strtotime($currentBookingFromTS . ' + 2 hours'));
		}

		$currentBookingFrom = $this->convertToHMS($currentBookingFrom);
		$currentBookingTo = $this->convertToHMS($currentBookingTo);

		if (strtotime($currentBookingFrom)>strtotime($currentBookingTo))
		{
			$currentBookingFrom = date('Y-m-d H:i:s', strtotime($currentBookingDate .' '.$currentBookingFrom));
			$currentBookingTo = date('Y-m-d H:i:s', strtotime($currentBookingDate .' '.$currentBookingTo.' +1 day'));
		} else {
			$currentBookingFrom = date('Y-m-d H:i:s', strtotime($currentBookingDate .' '.$currentBookingFrom));
			$currentBookingTo = date('Y-m-d H:i:s', strtotime($currentBookingDate .' '.$currentBookingTo));
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('table_id') . ' = ' . $db->quote($tableID))
			//->where('(('.$db->quoteName('venue_status') . ' <> '. $db->quote('6') . ' AND '.$db->quoteName('user_status') . ' <> '. $db->quote('8').'))')
			->where($db->quoteName('venue_status') . ' IN ('.implode(',', $bookingStatus).')')
			->where($db->quoteName('booking_date') . ' IN (\''. implode("','", $datesArray) .'\')' );
			// ->where($db->quoteName('venue_booking_id') . ' <> ' . $db->quote($tblVenuebooking->venue_booking_id))

		// Set the query and load the result.
		$db->setQuery($query);

		//echo $query;exit;

		$slotBooked = 0;
		$bookingsOnSameDate = $db->loadObjectList();

		$timeSlots = array();

		foreach ($bookingsOnSameDate as $key => $booking)
		{
			$bookingDate = $booking->booking_date;
			$bookingFrom = $booking->booking_time;
			$bookingTo = date('H:i:s', strtotime($bookingDate.' '.$bookingFrom . ' + ' . $booking->total_hours . ' hours'));

			if (strtotime($bookingDate.' '.$bookingFrom)>strtotime($bookingDate.' '.$bookingTo))
			{
				$bookingFrom = date('Y-m-d H:i:s', strtotime($bookingDate .' '.$bookingFrom));
				$bookingTo = date('Y-m-d H:i:s', strtotime($bookingDate .' '.$bookingTo.' +1 days'));
			}
			else
			{
				$bookingFrom = date('Y-m-d H:i:s', strtotime($bookingDate .' '.$bookingFrom));
				$bookingTo = date('Y-m-d H:i:s', strtotime($bookingDate .' '.$bookingTo));
			}

			$numbercurrentBookingFrom = strtotime($currentBookingFrom);  // 2016-04-07 00:01:01
			$numbercurrentBookingTo   = strtotime($currentBookingTo);    // 2016-04-07 02:01:01
			$numberbookingFrom        = strtotime($bookingFrom);         // 2016-04-06 22:30:00
			$numberbookingTo          = strtotime($bookingTo);           // 2016-04-07 00:30:00


			if(($numberbookingFrom < $numbercurrentBookingFrom) && ($numbercurrentBookingFrom < $numberbookingTo))
			{
				$slotBooked = $slotBooked + 1;
			}
			else if(($numberbookingFrom < $numbercurrentBookingTo) && ($numbercurrentBookingTo < $numberbookingTo))
			{
				$slotBooked = $slotBooked + 1;
			}
			else if(($numberbookingTo == $numbercurrentBookingTo) && ($numbercurrentBookingFrom == $numberbookingFrom))
			{
				$slotBooked = $slotBooked + 1;
			}

		}


		if($slotBooked)
		{
			return 0;
		}

		return 1;
	}

	/**
	 *  Method to Upload Image/Video on server based on element type
	 *
	 * @param   array  $file  Uploaded file attributed like tmp_name,name,size,error
	 *
	 * @param   string  $elementType  Type of element e.g. venue chauffeur, yacht,protection etc
	 *
	 * @param   int  $elementID  id of element e.g. venue chauffeur, yacht,protection etc
	 *
	 * @return string
	 *
	 * @since    1.0
	 */
	public function uplaodFile($file,$elementType,$elementID)
	{
		$uploadedImage = "";
		$uploadLimit	= 0;
		//$uploadLimit	= ( $uploadLimit * 1024 * 1024 );

		if(is_array($file) && isset($file['size']) && $file['size']>0)
		{
			jimport('joomla.filesystem.file');
			jimport('joomla.utilities.utility');

			if( filesize( $file['tmp_name'] ) > $uploadLimit && $uploadLimit != 0 )
			{
				/*IJReq::setResponseCode(416);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_IMAGE_FILE_SIZE_EXCEEDED'));
				return false;*/
				return '';
			} // End of if( filesize( $file['tmp_name'] ) > $uploadLimit && $uploadLimit != 0 )

			//$imageMaxWidth	= 160;
			$filename = JApplication::getHash( $file['tmp_name'] . time() );
			$hashFileName	= JString::substr( $filename , 0 , 24 );
			$info['extension'] = pathinfo($file['name'],PATHINFO_EXTENSION);
			$info['extension'] = '.'.$info['extension'];

			if(!JFolder::exists(JPATH_ROOT . "images/beseated/"))
			{
				JFolder::create(JPATH_ROOT . "images/beseated/");
			}

			if(!JFolder::exists(JPATH_ROOT . "images/beseated/".$elementType."/"))
			{
				JFolder::create(JPATH_ROOT . "images/beseated/".$elementType."/");
			}

			if(!JFolder::exists(JPATH_ROOT . "images/beseated/".$elementType."/". $elementID . '/'))
			{
				JFolder::create(JPATH_ROOT . "images/beseated/".$elementType."/". $elementID . '/');
			}

			$storage      = JPATH_ROOT . '/images/beseated/'.$elementType.'/'. $elementID . '/';
			$storageImage = $storage . '/' . $hashFileName .  $info['extension'] ;
			$uploadedImage   = $elementType.'/' .$elementID .'/'. $hashFileName . $info['extension'] ;

			if(!JFile::upload($file['tmp_name'], $storageImage))
		    {
				return '';
		    }

		    return $uploadedImage;

		} // End of if(is_array($file) && $file['size']>0)

		return '';
	}

	/**
	 *  Method to Upload Image/Video on server based on element type
	 *
	 * @param   array  $file  Uploaded file attributed like tmp_name,name,size,error
	 *
	 * @param   string  $elementType  Type of element e.g. venue chauffeur, yacht,protection etc
	 *
	 * @param   int  $elementID  id of element e.g. venue chauffeur, yacht,protection etc
	 *
	 * @return string
	 *
	 * @since    1.0
	 */
	public function uplaodServiceImage($file,$elementType,$elementID,$serviceID)
	{
		$uploadedImage = "";
		$uploadLimit	= 0;
		//$uploadLimit	= ( $uploadLimit * 1024 * 1024 );

		if(is_array($file) && isset($file['size']) && $file['size']>0)
		{
			jimport('joomla.filesystem.file');
			jimport('joomla.utilities.utility');

			if( filesize( $file['tmp_name'] ) > $uploadLimit && $uploadLimit != 0 )
			{
				/*IJReq::setResponseCode(416);
				IJReq::setResponseMessage(JText::_('COM_IJOOMERADV_IMAGE_FILE_SIZE_EXCEEDED'));
				return false;*/
				return '';
			} // End of if( filesize( $file['tmp_name'] ) > $uploadLimit && $uploadLimit != 0 )

			//$imageMaxWidth	= 160;
			$filename = JApplication::getHash( $file['tmp_name'] . time() );
			$hashFileName	= JString::substr( $filename , 0 , 24 );
			$info['extension'] = pathinfo($file['name'],PATHINFO_EXTENSION);
			$info['extension'] = '.'.$info['extension'];

			$corePath = JPATH_ROOT . '/images/beseated/';

			if(!JFolder::exists($corePath))
			{
				JFolder::create($corePath);
			}

			if(!JFolder::exists($corePath.$elementType.'/'))
			{
				JFolder::create($corePath.$elementType.'/');
			}

			if(!JFolder::exists($corePath.$elementType.'/'. $elementID . '/'))
			{
				JFolder::create($corePath.$elementType.'/'. $elementID . '/');
			}

			if($elementType == 'Venue')
				$serviceNameType = 'Tables';
			else
				$serviceNameType = 'Services';

			if(!JFolder::exists($corePath.$elementType.'/'. $elementID . '/'.$serviceNameType.'/'))
			{
				JFolder::create($corePath.$elementType.'/'. $elementID . '/'.$serviceNameType.'/');
			}

			if(!JFolder::exists($corePath.$elementType.'/'. $elementID . '/'.$serviceNameType.'/' .$serviceID.'/'))
			{
				JFolder::create($corePath.$elementType.'/'. $elementID . '/'.$serviceNameType.'/'.$serviceID.'/');
			}


			$storage      = $corePath.$elementType.'/'. $elementID . '/'.$serviceNameType .'/'.$serviceID.'/';
			$storageImage = $storage . $hashFileName .  $info['extension'] ;
			$uploadedImage   = $elementType.'/'.$elementID.'/'.$serviceNameType.'/'.$serviceID.'/'.$hashFileName.$info['extension'];

			if(!JFile::upload($file['tmp_name'], $storageImage))
		    {
				return '';
		    }

		    return $uploadedImage;

		} // End of if(is_array($file) && $file['size']>0)

		return '';
	}

	/**
	 *  Method to Upload Image/Video on server based on element type
	 *
	 * @param   string  $str  Image path
	 *
	 * @param   int  $elementID  id of element e.g. venue chauffeur, yacht,protection etc
	 *
	 * @return string
	 *
	 * @since    1.0
	 */
	public function getExtension($str) {
		$i = strrpos($str,".");
		if (!$i) { return ""; }
		$l = strlen($str) - $i;
		$ext = substr($str,$i+1,$l);
		return $ext;
	}

	/**
	 *  Method to Create thumbnail from orignal image
	 *
	 * @param   string  $mainImage  Original image path
	 *
	 * @param   string  $thumbName  Thumnail image name
	 *
	 * @param   int  $new_w  New width of thumbnail image
	 *
	 * @param   int  $new_h  New height of thumbnail image
	 *
	 * @return string
	 *
	 * @since    1.0
	 */
	public function createThumb($mainImage,$thumbName,$new_w = 500,$new_h = 500)
	{


		/*echo $thumbName;
		exit;*/
		//echo $mainImage;
		///var/www/beseated-ii/images/beseated/Venue/1/113fbacf898f235cd3097b95.jpg
		///var/www/beseated-ii/images/beseated/Venue/1/113fbacf898f235cd3097b95.jpg
		//exit;
		//get image extension.
		$ext=$this->getExtension($mainImage);
		//creates the new image using the appropriate function from gd library
		if(!strcmp("jpg",$ext) || !strcmp("jpeg",$ext))
		{
			$src_img=imagecreatefromjpeg($mainImage);
		}

		if(!strcmp("png",$ext))
		{
			$src_img=imagecreatefromstring(file_get_contents($mainImage));
		}



		//imagecopy(dst_im, src_im, dst_x, dst_y, src_x, src_y, src_w, src_h)

		//$itt = imagecopy($thumbName, $mainImage, 500, 500, 100, 100, 180, 180);

		//echo "<pre/>";print_r($itt);exit;

		//gets the dimmensions of the image
		$old_x=imageSX($src_img);
		$old_y=imageSY($src_img);

		// next we will calculate the new dimmensions for the thumbnail image
		// the next steps will be taken:
		// 1. calculate the ratio by dividing the old dimmensions with the new ones
		// 2. if the ratio for the width is higher, the width will remain the one define in WIDTH variable
		// and the height will be calculated so the image ratio will not change
		// 3. otherwise we will use the height ratio for the image
		// as a result, only one of the dimmensions will be from the fixed ones
		$ratio1=$old_x/$new_w;
		$ratio2=$old_y/$new_h;

		if($ratio1>$ratio2) {
			$thumb_w=$new_w;
			$thumb_h=$old_y/$ratio1;
		}
		else {
			$thumb_h=$new_h;
			$thumb_w=$old_x/$ratio2;
		}

		// we create a new image with the new dimmensions
		$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);

		// resize the big image to the new created one
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);

		// output the created image to the file. Now we will have the thumbnail into the file named by $filename
		if(!strcmp("png",$ext))
			imagepng($dst_img,$thumbName);
		else
			imagejpeg($dst_img,$thumbName);

		//destroys source and destination images.
		imagedestroy($dst_img);
		imagedestroy($src_img);
	}

	function getThreadId($to_id)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('connection_id')
			->from($db->quoteName('#__beseated_message'))
			->where(($db->quoteName('from_user_id') . ' = ' . $db->quote($this->IJUserID) . ' AND ' . $db->quoteName('to_user_id') . ' = ' . $db->quote($to_id)) .' OR '.
				    ($db->quoteName('from_user_id') . ' = ' . $db->quote($to_id) . ' AND ' . $db->quoteName('to_user_id') . ' = ' . $db->quote($this->IJUserID)));

		// Set the query and load the result.
		$db->setQuery($query);
		$connection_id = $db->loadResult();

		return $connection_id;

	}

	public function getConnectionID($userID1,$userID2)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__beseated_message_connection'))
			->where(
				'(' .
					$db->quoteName('from_user_id') . ' = ' . $db->quote($userID1) . ' AND ' .
					$db->quoteName('to_user_id') . ' = ' . $db->quote($userID2) .
				') OR ('.
					$db->quoteName('from_user_id') . ' = ' . $db->quote($userID2) . ' AND ' .
					$db->quoteName('to_user_id') . ' = ' . $db->quote($userID1) .
				')'
			);
		$db->setQuery($query);
		$hasConnection = $db->loadObject();
		if($hasConnection){
			return $hasConnection->connection_id;
		}else{
			$tblConnection = JTable::getInstance('Connection','BeseatedTable',array());
			$connData = array();
			$connData['from_user_id'] = $userID1;
			$connData['to_user_id']   = $userID2;
			$connData['time_stamp']   = time();
			$tblConnection->load(0);
			$tblConnection->bind($connData);
			if($tblConnection->store())
			{
				return $tblConnection->connection_id;
			}
		}
		return false;
	}

	public function getNotificationCount()
	{
		$my = JFactory::getUser();

		$this->IJUserID = ($this->IJUserID) ? $this->IJUserID  :$my->id;

		$notificationDetails = array();

		$userGroup = $this->getUserType($this->IJUserID);

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_notification_show_detail'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

		// Set the query and load the result.
		$db->setQuery($query);
		$notification_details = $db->loadObjectList();

		/*if(isset($_GET['task']))
		{
			echo "<pre/>";print_r($this->IJUserID);exit;
		}*/

		//echo "<pre/>";print_r($notification_details);exit;

		foreach ($notification_details as $key => $notifDetails)
		{
			if($notifDetails->notification_type == 'messages')
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();

				// $db    = $this->getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('COUNT(message_id) as messageCount')
					->from($db->quoteName('#__beseated_message'))
					->where($db->quoteName('to_user_id') . ' = ' . $db->quote($this->IJUserID))
					->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp));

				// Set the query and load the result.
				$db->setQuery($query);
				$messageCount = $db->loadResult();

				$notificationDetails['messageCount'] = $messageCount;


			}
			elseif($notifDetails->notification_type == 'bookings')
			{
				if($userGroup == ucfirst('Venue'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Venue'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.paybycash.request').')'.'OR'.
						     	'('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.booking.confirm').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.booking.paid').'))');

					$db->setQuery($query);

					$bookingCount = $db->loadResult();

					$notificationDetails['bookingCount'] = $bookingCount;

				}
				elseif($userGroup == ucfirst('protection'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Protection'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.booking.paid').'))');

					$db->setQuery($query);

					$bookingCount = $db->loadResult();

					$notificationDetails['bookingCount'] = $bookingCount;

				}
				elseif($userGroup == ucfirst('yacht'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Yacht'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.booking.paid').'))');

					$db->setQuery($query);

					$bookingCount = $db->loadResult();

					$notificationDetails['bookingCount'] = $bookingCount;

				}
				elseif($userGroup == ucfirst('Chauffeur'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Chauffeur'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.booking.paid').'))');

					$db->setQuery($query);

					$bookingCount = $db->loadResult();

					$notificationDetails['bookingCount'] = $bookingCount;

				}
				elseif($userGroup == ucfirst('guest'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.paybycash.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.share.user.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.postbill').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.booking.paidByAdmin').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.booking.paidByAdmin').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.booking.paidByAdmin').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.booking.paidByAdmin').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.booking.paidByOwner').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.booking.paidByOwner').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.booking.paidByOwner').')'.'OR'.
                                '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.service.attend.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.booking.paidByOwner').'))');

					$db->setQuery($query);

					$bookingCount = $db->loadResult();

					$notificationDetails['bookingCount'] = $bookingCount;

				}
			}
			elseif($notifDetails->notification_type == 'requests')
			{
				if($userGroup == ucfirst('Venue'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Venue'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('table.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.split.paybycash.request').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('guestlist.request').'))');

					$db->setQuery($query);

					$requestsCount = $db->loadResult();

					$notificationDetails['requestCount'] = $requestsCount;

				}
				elseif($userGroup == ucfirst('protection'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Protection'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request').'))');

					$db->setQuery($query);

					$requestsCount = $db->loadResult();

					$notificationDetails['requestCount'] = $requestsCount;

				}
				elseif($userGroup == ucfirst('yacht'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Yacht'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request').'))');

					$db->setQuery($query);

					$requestsCount = $db->loadResult();

					$notificationDetails['requestCount'] = $requestsCount;

				}
				elseif($userGroup == ucfirst('chauffeur'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Chauffeur'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request').'))');

					$db->setQuery($query);

					$requestsCount = $db->loadResult();

					$notificationDetails['requestCount'] = $requestsCount;
				}
				elseif($userGroup == ucfirst('guest'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.service.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.service.attend.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.share.invitation.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('table.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.service.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.share.invitation.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.service.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.share.invitation.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.service.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.share.invitation.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.share.user.paid').')'.'OR'.
								'('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.share.user.paid').')'.'OR'.
								'('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.share.user.paid').')'.'OR'.
								'('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('event.invitation.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('event.invitation.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('event.invitation').'))');

					$db->setQuery($query);

					$requestsCount = $db->loadResult();

					$notificationDetails['requestCount'] = $requestsCount;
				}
			}
			elseif($notifDetails->notification_type == 'notifications')
			{
				if($userGroup == ucfirst('Venue'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Venue'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('guestlist.request').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.paybycash.request').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.table.cancel').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.booking.confirm').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.booking.paid').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('table.request').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.split.paybycash.request').'))');

					$db->setQuery($query);

					$notificationsCount = $db->loadResult();

					$notificationDetails['allNotificationCount'] = $notificationsCount;
				}
				elseif($userGroup == ucfirst('Protection'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Protection'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.booking.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request').')'.'OR'.
						        '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.service.cancel').'))');

					$db->setQuery($query);

					$notificationsCount = $db->loadResult();

					$notificationDetails['allNotificationCount'] = $notificationsCount;
				}
				elseif($userGroup == ucfirst('yacht'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Yacht'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.service.cancel').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.booking.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request').'))');

					$db->setQuery($query);

					$notificationsCount = $db->loadResult();

					$notificationDetails['allNotificationCount'] = $notificationsCount;
				}
				elseif($userGroup == ucfirst('chauffeur'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Chauffeur'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.service.cancel').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.booking.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request').'))');

					$db->setQuery($query);

					$notificationsCount = $db->loadResult();

					$notificationDetails['allNotificationCount'] = $notificationsCount;
				}
				elseif($userGroup == ucfirst('guest'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($this->IJUserID))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.service.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.share.invitation.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.share.user.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('guest.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('guest.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.service.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.share.invitation.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.invitation.status.changed').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.service.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.share.invitation.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.invitation.status.changed').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.service.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.share.invitation.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.paybycash.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.share.user.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.postbill').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.service.attend.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.service.attend.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.service.attend.request.canceled').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('event.invitation.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('event.invitation.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.share.user.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.share.user.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.share.user.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('table.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.share.invitation.cancel').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.invitation.status.changed').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.paybycash.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.share.invitation.cancel').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.share.invitation.cancel').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.share.invitation.cancel').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.invitation.status.changed').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('event.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.booking.paidByOwner').')'.'OR'.
								'('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.booking.paidByOwner').')'.'OR'.
								'('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.booking.paidByOwner').')'.'OR'.
								'('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.booking.paidByOwner').')'.'OR'.
								'('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.booking.paidByAdmin').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.booking.paidByAdmin').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.booking.paidByAdmin').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.booking.paidByAdmin').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.message').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.message').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.message').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.message').'))');


					$db->setQuery($query);

					$notificationsCount = $db->loadResult();

					$notificationDetails['allNotificationCount'] = $notificationsCount;
				}
			}
		}

		/*if(isset($_GET['task']))
		{
			echo "<pre/>";print_r($notificationDetails);exit;
		}
*/
		return $notificationDetails;


	}

	function updateNotification($notification_type)
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$tblNotificationDetail = JTable::getInstance('NotificationDetail', 'BeseatedTable');

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('notification_show_detail_id')
			->from($db->quoteName('#__beseated_notification_show_detail'))
			->where($db->quoteName('notification_type') . ' = ' . $db->quote($notification_type))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

		// Set the query and load the result.
		$db->setQuery($query);
		$notification_detail_id = $db->loadResult();

		if($notification_detail_id)
		{
			$tblNotificationDetail->load($notification_detail_id);
			$tblNotificationDetail->time_stamp = time();
		}
		else
		{
			$tblNotificationDetail->load(0);
			$tblNotificationDetail->notification_type = $notification_type;
			$tblNotificationDetail->user_id           = $this->IJUserID;
			$tblNotificationDetail->time_stamp        = time();
		}

		$tblNotificationDetail->store();

	}

	function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
	{
		$sort_col = array();

		foreach ($arr as $key=> $row)
		{
			$sort_col[$key] = ucfirst($row->$col);
		}

		//echo "<pre/>";print_r($dir);
		array_multisort($sort_col, $dir, $arr);
	}

	public function checkForVenueClosed($venueID,$bookingDate)
	{
		$day = date('D',strtotime($bookingDate));

		$daysArray = array('1' => 'Mon','2' => 'Tue','3' => 'Wed','4' => 'Thu','5' => 'Fri','6' => 'Sat','7' => 'Sun');

		$day = array_search($day, $daysArray);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('working_days')
			->from($db->quoteName('#__beseated_venue'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID));

		// Set the query and load the result.
		$db->setQuery($query);
		$working_days = $db->loadResult();

		$working_days = explode(',', $working_days);


		if(in_array($day, $working_days))
		{
			return 1;
		}
		else
		{

			return 0;
		}


	}

	public function checkForAlreadyInvited($venueID,$bookingID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('invitation_id')
			->from($db->quoteName('#__beseated_invitation'))
			->where($db->quoteName('element_booking_id') . ' = ' . $db->quote($bookingID))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('venue'))
			->where($db->quoteName('user_action') . ' = ' . $db->quote('9'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

		// Set the query and load the result.
		$db->setQuery($query);
		$isAlreadyInvited = $db->loadResult();

		return $isAlreadyInvited;
	}

	function isReadBooking($elementType,$bookedType,$elementBookingID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('from_user_id,to_user_id,is_read_from_user,is_read_to_user')
			->from($db->quoteName('#__beseated_element_read_booking'))
			->where($db->quoteName('element_type') . ' = ' . $db->quote(strtolower($elementType)))
			->where($db->quoteName('booked_type') . ' = ' . $db->quote(strtolower($bookedType)))
			->where($db->quoteName('booking_id') . ' = ' . $db->quote($elementBookingID));

		// Set the query and load the result.
		$db->setQuery($query);

		$elementBookingDetail = $db->loadObject();

		if($elementBookingDetail->to_user_id == $this->IJUserID)
		{
			return $elementBookingDetail->is_read_to_user;

		}
		else if ($elementBookingDetail->from_user_id == $this->IJUserID)
		{
			return $elementBookingDetail->is_read_from_user;
		}
		else
		{
			return 0;
		}

	}

	function isReadRSVP($elementType,$bookedType,$elementBookingID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('from_user_id,to_user_id,is_read_from_user,is_read_to_user')
			->from($db->quoteName('#__beseated_element_read_rsvp'))
			->where($db->quoteName('element_type') . ' = ' . $db->quote(strtolower($elementType)))
			->where($db->quoteName('booked_type') . ' = ' . $db->quote(strtolower($bookedType)))
			->where($db->quoteName('booking_id') . ' = ' . $db->quote($elementBookingID));

		// Set the query and load the result.
		$db->setQuery($query);

		$elementBookingDetail = $db->loadObject();

		if($elementBookingDetail->to_user_id == $this->IJUserID)
		{
			return $elementBookingDetail->is_read_to_user;

		}
		else if ($elementBookingDetail->from_user_id == $this->IJUserID)
		{
			return $elementBookingDetail->is_read_from_user;
		}
		else
		{
			return 0;
		}

	}

	public function getBookingIDForInvited()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('element_booking_id')
			->from($db->quoteName('#__beseated_invitation'))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('venue'))
			->where($db->quoteName('user_action') . ' = ' . $db->quote('9'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($this->IJUserID));

		// Set the query and load the result.
		$db->setQuery($query);
		$invitedBookngIDs = $db->loadColumn();

		return $invitedBookngIDs;
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

			$packedString = pack("H*",trim($subString));

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

	public 	function decrypt($encryptedText,$key)
	{
		$secretKey = $this->hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$encryptedText=$this->hextobin($encryptedText);
		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
		mcrypt_generic_init($openMode, $secretKey, $initVector);
		$decryptedText = mdecrypt_generic($openMode, $encryptedText);
		$decryptedText = rtrim($decryptedText, "\0");
		mcrypt_generic_deinit($openMode);
		return $decryptedText;
	}

	public function getBangeCount($userID)
	{
		$notificationDetails = array();

		$userGroup = $this->getUserType($userID);

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_notification_show_detail'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);
		$notification_details = $db->loadObjectList();

		//echo "<pre/>";print_r($notification_details);exit;

		foreach ($notification_details as $key => $notifDetails)
		{
			if($notifDetails->notification_type == 'messages')
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();

				// $db    = $this->getDbo();
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('COUNT(message_id) as messageCount')
					->from($db->quoteName('#__beseated_message'))
					->where($db->quoteName('to_user_id') . ' = ' . $db->quote($userID))
					->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp));

				// Set the query and load the result.
				$db->setQuery($query);
				$messageCount = $db->loadResult();

			}
			elseif($notifDetails->notification_type == 'notifications')
			{
				if($userGroup == ucfirst('Venue'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($userID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Venue'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('guestlist.request').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.paybycash.request').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.table.cancel').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.booking.confirm').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.booking.paid').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('table.request').')'.'OR'.
							   '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.split.paybycash.request').'))');

					$db->setQuery($query);

					$notificationsCount = $db->loadResult();
				}
				elseif($userGroup == ucfirst('Protection'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($userID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Protection'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.booking.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request').')'.'OR'.
						        '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.service.cancel').'))');

					$db->setQuery($query);

					$notificationsCount = $db->loadResult();

				}
				elseif($userGroup == ucfirst('yacht'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($userID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Yacht'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.service.cancel').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.booking.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request').'))');

					$db->setQuery($query);

					$notificationsCount = $db->loadResult();
				}
				elseif($userGroup == ucfirst('chauffeur'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($userID))
						->where($db->quoteName('element_type') . ' = ' . $db->quote('Chauffeur'))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.service.cancel').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.booking.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request').'))');

					$db->setQuery($query);

					$notificationsCount = $db->loadResult();

				}
				elseif($userGroup == ucfirst('guest'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					// Create the base select statement.
					$query->select('COUNT(notification_id) as bookingCount')
						->from($db->quoteName('#__beseated_notification'))
						->where($db->quoteName('target') . ' = ' . $db->quote($userID))
						->where($db->quoteName('time_stamp') . ' > ' . $db->quote($notifDetails->time_stamp))
						->where('(('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.service.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.share.invitation.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.share.user.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('guest.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('guest.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.service.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.share.invitation.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.invitation.status.changed').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.service.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.share.invitation.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.invitation.status.changed').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.service.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.share.invitation.request').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.paybycash.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.share.user.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.postbill').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.service.attend.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.service.attend.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.service.attend.request.canceled').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('event.invitation.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('event.invitation.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.share.user.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.share.user.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.share.user.paid').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('table.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.share.invitation.cancel').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.invitation.status.changed').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.paybycash.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.share.invitation.cancel').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('service.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.share.invitation.cancel').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.share.invitation.cancel').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.request.accepted').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.request.declined').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.invitation.status.changed').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('event.invitation').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.booking.paidByOwner').')'.'OR'.
								'('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.booking.paidByOwner').')'.'OR'.
								'('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.booking.paidByOwner').')'.'OR'.
								'('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.booking.paidByOwner').')'.'OR'.
								'('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.booking.paidByAdmin').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.booking.paidByAdmin').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.booking.paidByAdmin').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.booking.paidByAdmin').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('chauffeur.message').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('yacht.message').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('protection.message').')'.'OR'.
							    '('.$db->quoteName('notification_type') . ' = ' . $db->quote('venue.message').'))');


					$db->setQuery($query);

					$notificationsCount = $db->loadResult();
				}
			}
		}

		return $notificationsCount+$messageCount;


	}



}