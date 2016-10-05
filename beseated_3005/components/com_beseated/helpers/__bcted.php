<?php
/*------------------------------------------------------------------------
# bcted.php - The Beseated Component
# ------------------------------------------------------------------------
# author    Tailored Solution
# copyright Copyright (C) 2014. All Rights Reserved
# license   GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
# website   www.tasolglobal.com
-------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * The Beseated component helper
 */
abstract class BctedHelper
{
	public static $bctedExtensionParam = null;
	public static $clubDetail          = null;
	public static $companyDetail       = null;
	public static $guestDetail         = null;
	public static $collectedStatus     = null;

	public static function currencyFormat($currencyCode,$currencySign,$amount,$allowDeciaml = 0)
	{
		$amount = number_format($amount,$allowDeciaml);

		if($currencyCode == 'AED')
		{
			$currencySign = 'AED ';
		}

		return $currencySign.' '.$amount;
	}

	public static function getUserGroupType($userID)
	{
		$bctedConfig = BctedHelper::getExtensionParam();
		$user        = JFactory::getUser($userID);
		$groups      = $user->get('groups');
		$groups      = array_values($groups);

		if(in_array($bctedConfig->club, $groups))
		{
			return 'Club';
		}
		else if(in_array($bctedConfig->service_provider, $groups))
		{
			return 'ServiceProvider';
		}
		else if(in_array($bctedConfig->guest, $groups))
		{
			return 'Registered';
		}
	}

	public static function getUserElementID($userID)
	{
		$bctedConfig = BctedHelper::getExtensionParam();
		$user        = JFactory::getUser($userID);
		$groups      = $user->get('groups');
		$groups      = array_values($groups);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		if(in_array($bctedConfig->club, $groups))
		{
			if(empty(self::$clubDetail))
			{
				$query->select('*')
					->from($db->quoteName('#__bcted_venue'))
					->where($db->quoteName('userid') . ' = ' . $db->quote($userID));

				// Set the query and load the result.
				$db->setQuery($query);
				self::$clubDetail = $db->loadObject();

				//echo "<br />Rand : " . rand();
			}

			return self::$clubDetail;
		}
		else if(in_array($bctedConfig->service_provider, $groups))
		{
			if(empty(self::$companyDetail))
			{
				// Create the base select statement.
				$query->select('*')
					->from($db->quoteName('#__bcted_company'))
					->where($db->quoteName('userid') . ' = ' . $db->quote($userID));

				// Set the query and load the result.
				$db->setQuery($query);
				self::$companyDetail = $db->loadObject();
			}

			return self::$companyDetail;
		}
		else if(in_array($bctedConfig->guest, $groups))
		{
			if(empty(self::$guestDetail))
			{
				// Create the base select statement.
				$query->select('*')
					->from($db->quoteName('#__beseated_user_profile'))
					->where($db->quoteName('userid') . ' = ' . $db->quote($userID));

				// Set the query and load the result.
				$db->setQuery($query);
				self::$guestDetail = $db->loadObject();
			}

			return self::$guestDetail;
		}
	}

	public static function getBeseatedUserProfile($userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('userid') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$bctedProfile = $db->loadObject();

			if($bctedProfile)
				return $bctedProfile;
			else
				return 0;

		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return 0;
	}

	public static function alreadyRatedServices($userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('rated_id')
			->from($db->quoteName('#__bcted_ratings'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('rating_type') . ' = ' . $db->quote('service'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadColumn();
		if(count($result) != 0 )
			$result = array_unique($result);

		return $result;
	}

	public static function alreadyRatedVenues($userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('rated_id')
			->from($db->quoteName('#__bcted_ratings'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('rating_type') . ' = ' . $db->quote('venue'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadColumn();
		if(count($result) != 0 )
			$result = array_unique($result);

		return $result;
	}

	public static function getUserLastBookingDetail($userID)
	{
		$ratedService = BctedHelper::alreadyRatedServices($userID);
		$ratedVenues  = BctedHelper::alreadyRatedVenues($userID);
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$currentDateTime = date('Y-m-d H:i:s');

		$query->select('sb.*')
			->from($db->quoteName('#__bcted_service_booking','sb'))
			->where($db->quoteName('sb.user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('sb.is_rated') . ' = ' . $db->quote('0'))
			->where($db->quoteName('sb.service_booking_datetime') . ' <= ' . $db->quote($currentDateTime))
			->where($db->quoteName('sb.is_deleted') . ' = ' . $db->quote('0'))
			->where($db->quoteName('sb.status') . ' = ' . $db->quote('5'))
			->where($db->quoteName('sb.user_status') . ' = ' . $db->quote('5'));

		$query->select('s.service_name')
			->join('LEFT','#__bcted_company_services AS s ON s.service_id=sb.service_id');

		$query->select('c.company_name')
			->join('LEFT','#__bcted_company AS c ON c.company_id=sb.company_id');

		// Set the query and load the result.
		$db->setQuery($query);

		$bookedService = $db->loadObjectList();

		$rateToService = array();
		$processedTableIDs = array();
		$processedServiceIDs = array();

		if(count($bookedService) != 0)
		{
			foreach ($bookedService as $key => $bs)
			{
				if(in_array($bs->company_id, $ratedService))
				{
					continue;
				}
				$currentTime = time();
				$timestamp = strtotime($bs->service_booking_datetime);
				$BookedTimestampAfert24 = strtotime('+1 day', $timestamp);

				if($currentTime >= $BookedTimestampAfert24)
				{
					if(!in_array($bs->service_id, $processedServiceIDs))
					{
						$processedServiceIDs[] = $bs->service_id;
						$tempData = array();
						$tempData['bookingID'] = $bs->service_booking_id;
						$tempData['serviceID'] = $bs->service_id;
						$tempData['companyID'] = $bs->company_id;
						$tempData['serviceName'] = $bs->service_name;
						$tempData['companyName'] = $bs->company_name;
						$rateToService[] = $tempData;
					}
				}
			}
		}

		$query = $db->getQuery(true);

		$query->select('vb.*')
			->from($db->quoteName('#__bcted_venue_booking','vb'))
			->where($db->quoteName('vb.user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('vb.is_rated') . ' = ' . $db->quote('0'))
			->where($db->quoteName('vb.is_deleted') . ' = ' . $db->quote('0'))
			->where($db->quoteName('vb.status') . ' = ' . $db->quote('5'))
			->where($db->quoteName('vb.user_status') . ' = ' . $db->quote('5'));

		$query->select('vt.premium_table_id,vt.venue_table_name,vt.custom_table_name')
			->join('LEFT','#__bcted_venue_table AS vt ON vt.venue_table_id=vb.venue_table_id');

		$query->select('v.venue_name')
			->join('LEFT','#__bcted_venue AS v ON v.venue_id=vb.venue_id');

		// Set the query and load the result.
		$db->setQuery($query);

		$bookedVenueTable = $db->loadObjectList();

		$rateToVenue = array();

		if(count($bookedVenueTable) != 0)
		{
			foreach ($bookedVenueTable as $key => $bv)
			{
				if(in_array($bv->venue_id, $ratedVenues))
				{
					continue;
				}
				$currentTime = time();
				$timestamp = strtotime($bv->venue_booking_datetime);
				$BookedTimestampAfert24 = strtotime('+1 day', $timestamp);
				if($currentTime >= $BookedTimestampAfert24)
				{
					if(!in_array($bv->venue_table_id, $processedTableIDs))
					{
						$tempData = array();
						$tempData['bookingID'] = $bv->venue_booking_id;
						$tempData['tableID']   = $bv->venue_table_id;
						$tempData['venueID']   = $bv->venue_id;

						if($bv->premium_table_id)
						{
							$tempData['tableName'] = $bv->venue_table_name;
						}
						else
						{
							$tempData['tableName'] = $bv->custom_table_name;
						}
						$tempData['venueName'] = $bv->venue_name;
						$rateToVenue[] = $tempData;
					}
				}
			}
		}

		$ratingArras            = array();
		$ratingArras['service'] = $rateToService;
		$ratingArras['venues']  = $rateToVenue;

		return $ratingArras;
	}

	public static function getGroupAccessLevel($groupID)
	{
		$app    = JFactory::getApplication();
		$db     = JFactory::getDbo();
		$option = '%' . $groupID . '%';

		// Initialiase variables.
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('id')
			->from($db->qn('#__viewlevels'))
			->where($db->qn('rules') . ' LIKE ' . $db->q($option));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadResult();

		return $result;
	}

	private function getExtensionParam()
	{
		if(self::$bctedExtensionParam)
		{
			return self::$bctedExtensionParam;
		}

		$app    = JFactory::getApplication();
		$option = "com_beseated";
		$db     = JFactory::getDbo();
		$option = '%' . $db->escape($option, true) . '%';

		// Initialiase variables.
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->qn('#__extensions'))
			->where($db->qn('name') . ' LIKE ' . $db->q($option))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->order($db->qn('ordering') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
			$params = json_decode($result->params);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), 500);
		}

		self::$bctedExtensionParam = $params;

		return $params;
	}

	public static function getExtensionParam_2()
	{
		if(self::$bctedExtensionParam)
		{
			return self::$bctedExtensionParam;
		}

		$app    = JFactory::getApplication();
		$option = "com_beseated";
		$db     = JFactory::getDbo();
		$option = '%' . $db->escape($option, true) . '%';

		// Initialiase variables.
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->qn('#__extensions'))
			->where($db->qn('name') . ' LIKE ' . $db->q($option))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->order($db->qn('ordering') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
			$params = json_decode($result->params);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), 500);
		}

		self::$bctedExtensionParam = $params;

		return $params;
	}

	public static function getLatitudeAndLongitude($address)
	{
		$address = str_replace(" ", "+", $address);
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.trim($address).'&sensor=false';
		$json = @file_get_contents($url);
		$data=json_decode($json);
		$status = $data->status;
		if($status=="OK")
			return $data->results[0]->geometry->location->lat.'|'.$data->results[0]->geometry->location->lng;
		else
			return "0|0";
	}

	public static function getAddressDetail($address)
	{
		$address = str_replace(" ", "+", $address);
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.trim($address).'&sensor=false';
		$json = @file_get_contents($url);
		$data=json_decode($json);
		$status = $data->status;
		if($status=="OK")
		{
			foreach ($data->results as $key => $results)
			{
				$address_components = $results->address_components;
				foreach ($address_components as $key => $single_components)
				{
					$types = $single_components->types;
					$cityName = '';

					if(in_array('locality', $types))
					{
						$cityName = $single_components->long_name;
					}
					elseif(in_array('administrative_area_level_2',$types))
					{
						$cityName = $single_components->long_name;
					}
					elseif(in_array('administrative_area_level_1',$types))
					{
						$cityName = $single_components->long_name;
					}

					if(!empty($cityName))
					{
						return $cityName;
					}
				}
			}
		}

		return '';
	}

	public static function convertCurrency($from,$to)
	{
		$convertExp = $from.'_'.$to;
		$currencyRate = 0;
		if(!empty($convertExp))
		{
			$url = "http://www.freecurrencyconverterapi.com/api/v3/convert?q=".$convertExp."&compact=y";
			$ch = curl_init();
			$timeout = 0;
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$rawdata = curl_exec($ch);
			curl_close($ch);
			if(empty($rawdata))
			{
			    return $currencyRate;
			}

			$object = json_decode($rawdata);
			$currencyRate = $object->$convertExp->val;
        }

        return $currencyRate;
	}

	public static function convertCurrencyGoogle($amount = 1, $from, $to)
	{
		$url  = "https://www.google.com/finance/converter?a=$amount&from=$from&to=$to";
		$data = file_get_contents($url);
		preg_match("/<span class=bld>(.*)<\/span>/",$data, $converted);
		$converted = preg_replace("/[^0-9.]/", "", $converted[1]);

		return round($converted, 2);
	}

	public static function getLoyaltyPointsOfUser($userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_loyalty_point'))
			->where($db->quoteName('is_valid') . ' = ' . $db->quote('1'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->order($db->quoteName('time_stamp') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		$totalPointEarn = 0;
		$pointEntry = array();

		foreach ($result as $key => $value)
		{
			$tmpData = array();
			$tmpData['pointID'] = $value->lp_id;
			$tmpData['earnPoint'] = $value->earn_point;

			if($value->point_app == 'purchase.package')
			{
				$tmpData['earnPointType'] = "Purchase";
			}
			else if($value->point_app == 'purchase.venue')
			{
				$tmpData['earnPointType'] = "Purchase";
			}
			else if($value->point_app == 'purchase.service')
			{
				$tmpData['earnPointType'] = "Purchase";
			}
			else if($value->point_app == 'Payout')
			{
				$tmpData['earnPointType'] = "Payout";
			}
			else if($value->point_app == 'Referral')
			{
				$tmpData['earnPointType'] = "Referral";
			}

			$tmpData['pointID'] = date('d-m-Y',strtotime($value->created));

			$totalPointEarn = $totalPointEarn + $value->earn_point;
			$pointEntry[] = $tmpData;
		}

		$resultData['totalPoint']   = $totalPointEarn;
		$resultData['pointEntries'] = $pointEntry;

		return $resultData;
	}

	public static function isFavouriteVenue($venueID,$userID)
	{
		die('yes');
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_favourites'))
			->where($db->quoteName('favourited_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('favourite_type') . ' = ' . $db->quote('Venue'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		if($result)
			return 1;
		else
			return 0;
	}

	public static function isFavouriteCompany($companyID,$userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_favourites'))
			->where($db->quoteName('favourited_id') . ' = ' . $db->quote($companyID))
			->where($db->quoteName('favourite_type') . ' = ' . $db->quote('Service'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		if($result)
			return 1;
		else
			return 0;
	}

	public static function getBctedMenuItem($alias)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__menu'))
			->where($db->quoteName('alias') . ' = ' . $db->quote($alias));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		if($result)
			return $result;
		else
			return 0;
	}

	public static function collectAllStatus()
	{
		if (empty(self::$collectedStatus))
		{
			self::$collectedStatus = new stdClass;

			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('*')
				->from($db->quoteName('#__bcted_status'));

			// Set the query and load the result.
			$db->setQuery($query);
			$result = $db->loadObjectList();
			foreach ($result as $key => $item)
			{
				$nameArray[$item->status] = $item;
				$idArray[$item->id] = $item;
			}

			self::$collectedStatus->name = $nameArray;
			self::$collectedStatus->ids = $idArray;
		}

		//return self::$collectedStatus;
	}

	public static function getStatusIDFromStatusName($statusName)
	{
		self::collectAllStatus();

		if(empty($statusName))
		{
			return 0;
		}

		return self::$collectedStatus->name[$statusName]->id;
	}

	public static function getStatusNameFromStatusID($statusID)
	{
		self::collectAllStatus();

		if(!$statusID)
		{
			return '';
		}

		return self::$collectedStatus->ids[$statusID]->status;
	}

	public static function getVenueDetail($venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}

	public static function getCompanyDetail($companyID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_company'))
			->where($db->quoteName('company_id') . ' = ' . $db->quote($companyID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}

	public static function getCompanyServiceDetail($serviceID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_company_services'))
			->where($db->quoteName('service_id') . ' = ' . $db->quote($serviceID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}

	public static function getCompanyServices($companyID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_company_services'))
			->where($db->quoteName('company_id') . ' = ' . $db->quote($companyID))
			->where($db->quoteName('service_active') . ' = ' . $db->quote(1));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	public static function getpackageDetail($packageId)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_package'))
			->where($db->quoteName('package_id') . ' = ' . $db->quote($packageId));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}

	public static function getVenueTableDetail($tableID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_venue_table'))
			->where($db->quoteName('venue_table_id') . ' = ' . $db->quote($tableID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}

	public static function getVenueTables($venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_venue_table'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('venue_table_active') . ' = ' . $db->quote(1));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	public static function getMessageConnectionOnly($fromUser,$toUser)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_message_connection'))
			->where($db->quoteName('from_userid') . ' IN (' . $fromUser.','.$toUser .')')
			->where($db->quoteName('to_userid') . ' IN (' . $fromUser.','.$toUser .')');

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		if($result)
			return $result->connection_id;
		else
			return 0;
	}

	public static function getMessageConnection($fromUser,$toUser)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_message_connection'))
			->where($db->quoteName('from_userid') . ' IN (' . $fromUser.','.$toUser .')')
			->where($db->quoteName('to_userid') . ' IN (' . $fromUser.','.$toUser .')')
			->where($db->quoteName('to_userid').' <> '.$db->quoteName('from_userid'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		if($result)
		{
			return $result->connection_id;
		}
		else
		{
			$tblMessageconnection = JTable::getInstance('Messageconnection', 'BctedTable',array());

			$data = array();
			$data['from_userid'] = $fromUser;
			$data['to_userid']   = $toUser;

			$tblMessageconnection->load(0);
			$tblMessageconnection->bind($data);
			if($tblMessageconnection->store())
			{
				return $tblMessageconnection->connection_id;
			}
			else
			{
				return 0;
			}
		}
	}

	public static function sendPushNotification($jsonarray)
	{
		if (!empty($jsonarray['pushNotificationData']))
		{
			require_once JPATH_SITE.'/components/com_ijoomeradv/models/ijoomeradv.php';
			require_once JPATH_SITE.'/components/com_ijoomeradv/helpers/helper.php';

			$ijoomeradvModel = new IjoomeradvModelijoomeradv();
			$result = $ijoomeradvModel->getApplicationConfig();

			foreach ($result as $value)
			{
				defined($value->name) or define($value->name, $value->value);
			}

			$db = JFactory::getDbo();

			$memberlist = $jsonarray['pushNotificationData']['to'];

			if ($memberlist)
			{
				$query = $db->getQuery(true);

				// Create the base select statement.
				$query->select('a.userid, a.jomsocial_params, a.device_token, a.device_type')
					->from($db->qn('#__ijoomeradv_users','a'))
					->where($db->qn('a.userid') . ' = ' . $db->q($memberlist));
				$query->select('b.user_type_for_push AS user_type')
					->join('LEFT','#__beseated_user_profile AS b ON b.userid=a.userid');

				// Set the query and load the result.
				$db->setQuery($query);

				$puserlist = $db->loadObjectList();
				foreach ($puserlist as $puser)
				{
					if (!empty($jsonarray['pushNotificationData']['configtype']) and $jsonarray['pushNotificationData']['configtype'] != '')
					{
						$ijparams = json_decode($puser->jomsocial_params);
						$configallow = $jsonarray['pushNotificationData']['configtype'];
					}
					else
					{
						$configallow = 1;
					}

					if ($configallow && !empty($puser))
					{
						if (IJOOMER_PUSH_ENABLE_IPHONE == 1 && $puser->device_type == 'iphone')
						{
							$options = array();
							$options['device_token'] = $puser->device_token;
							$options['live'] = intval(IJOOMER_PUSH_DEPLOYMENT_IPHONE);
							$options['aps']['alert'] = strip_tags($jsonarray['pushNotificationData']['message']);
							$options['aps']['type'] = $jsonarray['pushNotificationData']['type'];
							$options['aps']['id'] = ($jsonarray['pushNotificationData']['id'] != 0) ? $jsonarray['pushNotificationData']['id'] : $jsonarray['pushNotificationData']['id'];
							IJPushNotif::sendIphonePushNotification($options,$puser->user_type);
						}

						if (IJOOMER_PUSH_ENABLE_ANDROID == 1 && $puser->device_type == 'android')
						{
							$options = array();
							$options['registration_ids'] = array($puser->device_token);
							$options['data']['message'] = strip_tags($jsonarray['pushNotificationData']['message']);
							$options['data']['type'] = $jsonarray['pushNotificationData']['type'];
							$options['data']['id'] = ($jsonarray['pushNotificationData']['id'] != 0) ? $jsonarray['pushNotificationData']['id'] : $jsonarray['pushNotificationData']['id'];
							IJPushNotif::sendAndroidPushNotification($options);
						}
					}
				}
			}
			unset($jsonarray['pushNotificationData']);
		}
	}

	public static function sendMessage($venueID,$companyID,$serviceID,$tableID,$TouserID,$message,$extraParam = array(),$messageType='tableaddme')
	{
		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_bcted/tables');
		$tblMessage = JTable::getInstance('Message', 'BctedTable');
		$tblMessage->load(0);
		$loginUser = JFactory::getUser();
		$data = array();

		$data['venue_id']   = $venueID;
		$data['company_id'] = $companyID;
		$data['table_id']   = $tableID;
		$data['service_id'] = $serviceID;
		$data['userid']     = $TouserID;
		$data['to_userid']  = $TouserID;
		$data['message']    = $message;
		if(count($extraParam)!=0)
		{
			$data['extra_params'] = json_encode($extraParam);
		}
		else
		{
			$data['extra_params'] = "";
		}

		$data['message_type']    = $messageType;
		$data['created']    = date('Y-m-d H:i:s');
		$data['time_stamp'] = time();
		$data['from_userid'] = $loginUser->id;
		$connectionID = BctedHelper::getMessageConnection($data['from_userid'],$data['to_userid']);
		if(!$connectionID)
		{
			return 0;
		}

		$data['connection_id'] = $connectionID;
		$tblMessage->bind($data);

		$tblMessage->store();

		if($messageType == 'noshow')
		{
			return $connectionID;
		}
	}

	public static function getVenueBookingDetail($bookingID)
	{
		$tblVenuebooking = JTable::getInstance('Venuebooking', 'BctedTable',array());
		$tblVenuebooking->load($bookingID);

		return $tblVenuebooking;
	}

	public static function getVenueBookingInvite($inviteID)
	{
		$tblVenuetableinvite = JTable::getInstance('Venuetableinvite', 'BctedTable',array());
		$tblVenuetableinvite->load($inviteID);

		return $tblVenuetableinvite;
	}

	public static function getServiceNameForPackage($serviceIDs = '')
	{
		$result = array();
		if(!empty($serviceIDs))
		{
			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('service_name')
				->from($db->quoteName('#__bcted_company_services'))
				->where($db->quoteName('service_id') . ' IN (' . $serviceIDs . ')')
				->order($db->quoteName('service_name') . ' ASC');

			// Set the query and load the result.
			$db->setQuery($query);

			$result = $db->loadColumn();
		}

		return $result;
	}

	public static function getPackageInvitedUserDetail($packagePurchaseID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('pi.*')
			->from($db->quoteName('#__bcted_package_invite','pi'));

		$query->where($db->quoteName('pi.package_purchase_id') . ' = ' . $db->quote($packagePurchaseID));


		$query->select('bs.status AS status_text')
			->join('LEFT','#__bcted_status AS bs ON bs.id=pi.status');

		$query->select('u.name')
			->join('LEFT','#__users AS u ON u.id=pi.invited_user_id');

		$query->select('bu.last_name,bu.phoneno')
			->join('LEFT','#__beseated_user_profile AS bu ON bu.userid=pi.invited_user_id');

		$query->order($db->quoteName('pi.created') . ' DESC');

		$db->setQuery($query);
		$invitations = $db->loadObjectList();

		return $invitations;
	}

	public static function getLiveBeseatedGuests()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('userid')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('user_type_for_push') . ' = ' . $db->quote('guest'))
			->order($db->quoteName('userid') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$beseatedGuests = $db->loadColumn();

		if(count($beseatedGuests) == 0)
		{
			return '';
		}

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('id')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('block') . ' = ' . $db->quote('0'))
			->order($db->quoteName('id') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);
		$liveBeseatedGuests = $db->loadColumn();

		return $liveBeseatedGuests;

	}
	public static function isLiveUser($userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('block') . ' = ' . $db->quote('0'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		if($result)
		{
			return 1;
		}

		return 0;
	}

	public static function removeUserFromBlackList($userID,$elementID,$elementType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->delete()
			->from($db->quoteName('#__bcted_blacklist'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote((int) $elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
			->where($db->quoteName('user_id') . ' = ' . $db->quote((int) $userID));

		// Set the query and execute the delete.
		$db->setQuery($query);

		$db->execute();
	}

	public static function addUserToBlackList($userID,$elementID,$elementType)
	{
		$tblBlacklist = JTable::getInstance('Blacklist', 'BctedTable');
		$tblBlacklist->load(0);

		$data = array();
		$data['element_id']   = $elementID;
		$data['element_type'] = $elementType;
		$data['user_id']      = $userID;
		$data['created']      = date('Y-m-d H:i:s');

		$tblBlacklist->bind($data);

		if(!$tblBlacklist->store())
		{
			return false;
		}

		return true;
	}

	public static function getBlackList($elementID, $elementType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('user_id')
			->from($db->quoteName('#__bcted_blacklist'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$users = $db->loadColumn();

			if($users)
				return $users;
			else
				return 0;
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	public static function checkBlackList($userID,$elementID,$elementType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_blacklist'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}

	public static function uplaodFile($file,$elementType,$userID)
	{
		$uploadedImage = "";
		$uploadLimit	= 80;
		$uploadLimit	= ( $uploadLimit * 1024 * 1024 );

		if(is_array($file) && isset($file['size']) && $file['size']>0)
		{
			jimport('joomla.filesystem.file');
			jimport('joomla.utilities.utility');
			jimport('joomla.filesystem.folder');

			if( filesize( $file['tmp_name'] ) > $uploadLimit && $uploadLimit != 0 )
			{
				return '';
			} // End of if( filesize( $file['tmp_name'] ) > $uploadLimit && $uploadLimit != 0 )

			$filename = JApplication::getHash( $file['tmp_name'] . time() );
			$hashFileName	= JString::substr( $filename , 0 , 24 );
			$info['extension'] = pathinfo($file['name'],PATHINFO_EXTENSION);
			$info['extension'] = '.'.$info['extension'];

			if(!JFolder::exists(JPATH_ROOT . "/images/bcted/"))
			{
				JFolder::create(JPATH_ROOT . "/images/bcted/");
			}

			if(!JFolder::exists(JPATH_ROOT . "/images/bcted/".$elementType."/"))
			{
				JFolder::create(JPATH_ROOT . "/images/bcted/".$elementType."/");
			}

			if(!JFolder::exists(JPATH_ROOT . "/images/bcted/".$elementType."/". $userID . '/'))
			{
				JFolder::create(JPATH_ROOT . "/images/bcted/".$elementType."/". $userID . '/');
			}

			$storage      = JPATH_ROOT . '/images/bcted/'.$elementType.'/'. $userID . '/';
			$storageImage = $storage . '/' . $hashFileName .  $info['extension'] ;
			$uploadedImage   = 'images/bcted/'.$elementType.'/' .$userID .'/'. $hashFileName . $info['extension'] ;

			if(!JFile::upload($file['tmp_name'], $storageImage))
		    {
				return '';
		    }

		    return $uploadedImage;

		} // End of if(is_array($file) && $file['size']>0)

		return '';
	}

	public static function convertVideo2($videoIn, $videoOut, $videoSize = '400x300', $deleteOriginal = false)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.utility');
		jimport( 'joomla.user.helper' );
		$qscale = "11";

		if (!JFile::exists($videoIn)){
			return false;
		}

		if (JFile::exists($videoOut)) {
			$videoFullPath = JFile::makeSafe($videoOut);
			$videoFileName = JFile::getName($videoFullPath);
		} else {
			$videoFileName  = JApplication::getHash(JUserHelper::genRandomPassword(5) . time());
			$videoFullPath  = $videoOut . $videoFileName.".flv";
			$videoMp4Path   = $videoOut . $videoFileName.'.mp4';
			$videoOggPath   = $videoOut . $videoFileName.'.ogg';
			$fileExtenstion = JFile::getExt($videoMp4Path);
			$returnFileName = $videoFileName.".".$fileExtenstion;
		}

		$videoFileNameWebm = JFile::getName($videoFullPath);

		// Build the ffmpeg command
		$ffmpeg  = '/usr/bin/ffmpeg';
		$cmd     = array();
		$cmd[]   = $ffmpeg;
		$cmd[]   = '-y -i ' . $videoIn;
		$cmd[]   = '-g 30'; //group of picture size, for video streaming
		//$cmd[] = '-q ' . $qscale;
		$cmd[]   = '-vcodec flv -f flv -ar 44100';
		$cmd[]   = '-s ' . $videoSize;
		//$cmd[] = $config->get('customCommandForVideo');
		$cmd[]   = "";
		$cmd[]   = $videoFullPath;
		//$cmd[] = '2>/var/www/html/dev/ffmpeg_test.txt';
		$cmd[]   = '2>/var/www/development/thebeseated/ffmpeg_test.txt';
		$command = implode(' ', $cmd);
		$cmdOut  = BctedHelper::_runCommand($command);
		$command = "/usr/bin/ffmpeg -i ".$videoFullPath." -vf \"transpose=1\" -ar 22050 ".$videoMp4Path; //Wroking
		$cmdOut  = BctedHelper::_runCommand($command);
		$command = "/usr/bin/ffmpeg -i ".$videoFullPath." -sameq ".$videoOggPath; //Wroking
		$cmdOut  = BctedHelper::_runCommand($command);

		if (JFile::exists($videoFullPath) && filesize($videoFullPath) > 0)
		{
			if ($deleteOriginal)
			{
				JFile::delete($videoIn);
			}
			return $returnFileName;
		}
		else
		{
			return false;
		}
	}

	public function _runCommand($command)
	{
		$output       = null;
		$return_var   = null;
		$execFunction = null;

		if ($execFunction == null)
		{
			$disableFunctions	= explode(',', ini_get('disable_functions'));
			$execFunctions		= array('passthru', 'exec', 'shell_exec', 'system');

			foreach ($execFunctions as $execFunction)
			{
				if (is_callable($execFunction) && function_exists($execFunction) && !in_array($execFunction, $disableFunctions))
				{
					$execFunction = $execFunction;
					break;
				}
			}
		}

		switch ($execFunction)
		{
			case 'passthru':
				ob_start();
				@passthru($command, $return_var);
				$output = ob_get_contents();
				ob_end_clean();
				break;
			case 'exec':
				@exec($command, $output, $return_var);
				$output	= implode("\r\n", $output);
				break;
			case 'shell_exec':
				$output	= @shell_exec($command);
				break;
			case 'system':
				ob_start();
				@system($command, $return_var);
				$output = ob_get_contents();
				ob_end_clean();
				break;
			default:
				$output	= false;
		}

		return $output;
	}

	public static function createThumb($mainImage,$thumbName,$new_w = 500,$new_h = 500)
	{
		$str = $mainImage;
		$i = strrpos($str,".");
		if (!$i) { $ext = ""; }
		$l = strlen($str) - $i;
		$ext = substr($str,$i+1,$l);
		if(!strcmp("jpg",$ext) || !strcmp("jpeg",$ext))
			$src_img=imagecreatefromjpeg($mainImage);

		if(!strcmp("png",$ext))
			$src_img=imagecreatefrompng($mainImage);

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

	public static function getExtension($str) {
		$i = strrpos($str,".");
		if (!$i) { return ""; }
		$l = strlen($str) - $i;
		$ext = substr($str,$i+1,$l);
		return $ext;
	}

	public function createVideoThumb($videoFile, $thumbFile, $thumbSize='600x500')
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.utility');
		jimport( 'joomla.user.helper' );

		$videoInfor = BctedHelper::getVideoInfo($videoFile);

		if (!JFile::exists($videoFile))
		{
			return false;
		}
		if (JFile::exists($thumbFile))
		{
			$thumbFullPath = JFile::makeSafe($thumbFile);
			$thumbFileName = JFile::getName($thumbFullPath);
		} else {
			$thumbFileName = JApplication::getHash(JUserHelper::genRandomPassword(5) . time());
			$thumbFileName = $thumbFileName.".jpg";
			$thumbFullPath = JPath::clean($thumbFile .'/'. $thumbFileName);
		}
		$videoFrame = "00:00:01";
		$cmd	= '/usr/bin/ffmpeg' . ' -i ' . $videoFile . ' -ss ' . $videoFrame . ' -t 00:00:01 -s ' . $thumbSize . ' -r 1 -f mjpeg ' . $thumbFullPath;
		$cmdOut = BctedHelper::_runCommand($cmd);

		if (JFile::exists($thumbFullPath) && (filesize($thumbFullPath) > 0))
		{
			return $thumbFileName;
		}

		$cmd	= '/usr/bin/ffmpeg' . ' -i ' . $videoFile . ' -vcodec mjpeg -vframes 1 -an -f rawvideo ' .  $thumbFullPath;
		$cmdOut =BctedHelper::_runCommand($cmd);

		if (JFile::exists($thumbFullPath) && (filesize($thumbFullPath) > 0))
		{
			return $thumbFileName;
		} else {
			$debug = 1;
			if ($debug)
			{
				echo '<pre>FFmpeg could not create video thumbs</pre>';
				echo '<pre>' . $cmd . '</pre>';
				echo '<pre>' . $cmdOut . '</pre>';
				if (!$cmdOut) { echo '<pre>Check video thumb folder\'s permission.</pre>'; }
			}
			return false;
		}
	}

	public function getVideoInfo($videoFile, $cmdOut = '')
	{
		$data = array();

		if (!is_file($videoFile) && empty($cmdOut))
			return $data;

		if (!$cmdOut) {
			$cmd	= '/usr/bin/ffmpeg' . ' -i ' . $videoFile . ' 2>&1';
			$cmdOut	= BctedHelper::_runCommand($cmd);
		}

		if (!$cmdOut) {
			return $data;
		}

		preg_match_all('/Duration: (.*)/', $cmdOut , $matches);

		if (count($matches) > 0 && isset($matches[1][0]))
		{
			$parts = explode(', ', trim($matches[1][0]));
			$data['bitrate']			= intval(ltrim($parts[2], 'bitrate: '));
			$data['duration']['hms']	= substr($parts[0], 0, 8);
			$data['duration']['exact']	= $parts[0];
			$data['duration']['sec']	= $videoFrame = BctedHelper::formatDuration($data['duration']['hms'], 'seconds');
			$data['duration']['excess']	= intval(substr($parts[0], 9));
		}
		else
		{
			$debug =1;
			if ($debug) {
				echo '<pre>FFmpeg failed to read video\'s duration</pre>';
				echo '<pre>' . $cmd . '<pre>';
				echo '<pre>' . $cmdOut . '</pre>';
			}
			return false;
		}

		preg_match('/Stream(.*): Video: (.*)/', $cmdOut, $matches);
		if (count($matches) > 0 && isset($matches[0]) && isset($matches[2]))
		{
			$data['video']	= array();

			preg_match('/([0-9]{1,5})x([0-9]{1,5})/', $matches[2], $dimensions_matches);
			$data['video']['width']		= floatval($dimensions_matches[1]);
			$data['video']['height']	= floatval($dimensions_matches[2]);

			preg_match('/([0-9\.]+) (fps|tb)/', $matches[0], $fps_matches);

			if (isset($fps_matches[1]))
				$data['video']['frame_rate']= floatval($fps_matches[1]);

			preg_match('/\[PAR ([0-9\:\.]+) DAR ([0-9\:\.]+)\]/', $matches[0], $ratio_matches);
			if(count($ratio_matches))
			{
				$data['video']['pixel_aspect_ratio']	= $ratio_matches[1];
				$data['video']['display_aspect_ratio']	= $ratio_matches[2];
			}

			if (!empty($data['duration']) && !empty($data['video']))
			{
				$data['video']['frame_count'] = ceil($data['duration']['sec'] * $data['video']['frame_rate']);
				$data['frames']				= array();
				$data['frames']['total']	= $data['video']['frame_count'];
				$data['frames']['excess']	= ceil($data['video']['frame_rate'] * ($data['duration']['excess']/10));
				$data['frames']['exact']	= $data['duration']['hms'] . '.' . $data['frames']['excess'];
			}

			$parts			= explode(',', $matches[2]);
			$other_parts	= array($dimensions_matches[0], $fps_matches[0]);

			$formats = array();
			foreach ($parts as $key => $part)
			{
				$part = trim($part);
				if (!in_array($part, $other_parts))
					array_push($formats, $part);
			}
			$data['video']['pixel_format']	= $formats[1];
			$data['video']['codec']			= $formats[0];
		}

		return $data;
	}

	public function formatDuration($duration = 0, $format = 'HH:MM:SS')
	{
		if ($format == 'seconds' || $format == 'sec') {
			$arg = explode(":", $duration);

			$hour	= isset($arg[0]) ? intval($arg[0]) : 0;
			$minute	= isset($arg[1]) ? intval($arg[1]) : 0;
			$second	= isset($arg[2]) ? intval($arg[2]) : 0;

			$sec = ($hour*3600) + ($minute*60) + ($second);
			return (int) $sec;
		}

		if ($format == 'HH:MM:SS' || $format == 'hms') {
			$timeUnits = array
			(
				'HH' => $duration / 3600 % 24,
				'MM' => $duration / 60 % 60,
				'SS' => $duration % 60
			);

			$arg = array();
			foreach ($timeUnits as $timeUnit => $value) {
				$arg[$timeUnit] = ($value > 0) ? $value : 0;
			}

			$hms = '%02s:%02s:%02s';
			$hms = sprintf($hms, $arg['HH'], $arg['MM'], $arg['SS']);
			return $hms;
		}
	}

	public static function convertToHMS($time)
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

	public static function convertToHM($time)
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

	public static function differenceInHours($start, $end)
	{
		$currentDate = date('Y-m-d');
		$nextDate = date('Y-m-d', strtotime(' +1 day'));

		if (strtotime($start)>strtotime($end)) {
			//start date is later then end date
			//end date is next day
			$s = new DateTime($currentDate.' '.$start);
			$e = new DateTime($nextDate.' '.$end);
		} else {
			//start date is earlier then end date
			//same day
			$s = new DateTime($currentDate.' '.$start);
			$e = new DateTime($currentDate.' '.$end);
		}

		$dateDiff = date_diff($s, $e);

		if($dateDiff->h == 0)
		{
			$hour = 1;
		}

		if($dateDiff->h)
		{
			$hour = $dateDiff->h;
		}

		if($dateDiff->h ==0 && $dateDiff->i > 0)
		{
			$hour = 1;
		}else if($dateDiff->i > 0)
		{
			$hour = $hour+1;
		}

		return $hour;
	}

	public static function filterEmails($emailString)
	{
		$seletedEmails = explode(",", $emailString);
		$checkForValid = 0;
		if(count($seletedEmails)!=0)
		{
			$checkForValid = 1;
		}
		$seletedEmails = implode("','", $seletedEmails);
		$seletedEmails = "'".$seletedEmails."'";

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('id')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('block') . ' = ' . $db->quote('0'))
			->where($db->quoteName('email') . ' IN ('.$seletedEmails.')');

		// Set the query and load the result.
		$db->setQuery($query);
		$userids = $db->loadColumn();

		$guestEmails   = array();
		$serviceEmails = array();
		$venueEmails   = array();
		$allRegEmail   = array();

		$invalidEmailflg = 0;

		for ($i = 0; $i < count($userids); $i++)
		{
			$query1 = $db->getQuery(true);

			// Create the base select statement.
			$query1->select('a.user_type_for_push,b.email')
				->from($db->quoteName('#__beseated_user_profile').'AS a')
				->where($db->quoteName('a.userid') . ' = ' . $db->quote($userids[$i]))
				->join('INNER', '#__users AS b ON b.id=a.userid');

			// Set the query and load the result.
			$db->setQuery($query1);
			$userData = $db->loadObject();

			$allRegEmail[] = $userData->email;

			if($userData->user_type_for_push == 'guest')
			{
				$guestEmails[] = $userData->email;
			}

			if($userData->user_type_for_push == 'service')
			{
				$serviceEmails[] = $userData->email;
			}

			if($userData->user_type_for_push == 'venue')
			{
				$venueEmails[] = $userData->email;
			}
		}

		$foundEmails                = array();
		$foundEmails['guest']       = $guestEmails;
		$foundEmails['service']     = $serviceEmails;
		$foundEmails['venue']       = $venueEmails;
		$foundEmails['allRegEmail'] = $allRegEmail;

		return $foundEmails;
	}

	public static function getUserTimezoneDifferent($userID)
	{
		$userDetail = BctedHelper::getUserElementID($userID);

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

	public static function setBeseatedSessionMessage($message,$title = 'Message')
	{
		$session = JFactory::getSession();
		$session->set('beseated_dspl_message', $message);
		$session->set('beseated_dspl_title', $title);
	}

	public static function sendEmail($email,$subject,$body)
	{
		// Initialise variables.
		$app     = JFactory::getApplication();
		$config  = JFactory::getConfig();

		$site    = $config->get('sitename');
		$from    = $config->get('mailfrom');
		$sender  = $config->get('fromname');

		// Clean the email data.
		$sender  = JMailHelper::cleanAddress($sender);
		$subject = JMailHelper::cleanSubject($subject);
		$body    = JMailHelper::cleanBody($body);

		// Send the email.
		$return = JFactory::getMailer()->sendMail($from, $sender, $email, $subject, $body,true);
	}

	public static function defineBeseatedAppConfig()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('name, value')
			->from($db->qn('#__ijoomeradv_bcted_config'));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
			foreach ($result as $value)
			{
				defined($value->name) or define($value->name, $value->value);
			}
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}
}
?>