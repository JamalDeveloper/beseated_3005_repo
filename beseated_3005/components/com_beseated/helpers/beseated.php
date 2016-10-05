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
abstract class BeseatedHelper
{
	public static $bctedExtensionParam = null;
	public static $clubDetail          = null;
	public static $companyDetail       = null;
	public static $guestDetail         = null;
	public static $collectedStatus     = null;
	public static $splitUserDetail     = null;


	public static function getBeseatedUserProfile($userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

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

	public static function chauffeurUserDetail($userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_chauffeur'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $result;
	}

	public static function protectionUserDetail($userID)
	{
		// Initialiase variables.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_protection'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	public static function venueUserDetail($userID)
	{
		$db = JFactory::getDbo();
		// Initialiase variables.
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	public static function yachtUserDetail($userID)
	{
		// Initialiase variables.
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_yacht'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	public static function isAlreadyRated($userID,$elementType,$elementID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('count(rating_id)')
			->from($db->quoteName('#__beseated_rating'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadResult();
		return $result;
	}

	public static function calculateAvgRating($elementType,$elementID){
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('sum(avg_rating) as total_rating,count(element_id) AS rating_count')
			->from($db->quoteName('#__beseated_rating'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote(strtolower($elementType)));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();
		/*echo "<pre>";
		print_r($result);
		echo "</pre>";
		die('In Avg Rating');*/

	}

	public static function alreadyRatedVenues($userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('element_id')
			->from($db->quoteName('#__beseated_rating'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('venue'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadColumn();
		if(count($result) != 0 )
			$result = array_unique($result);

		return $result;
	}

	public static function getUserLastBookingDetail($userID)
	{
		/*$ratedService = BeseatedHelper::alreadyRatedServices($userID);*/
		$ratedVenues  = BeseatedHelper::alreadyRatedVenues($userID);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$currentDateTime = date('Y-m-d H:i:s');

		/*$query->select('sb.*')
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
*/

		$processedTableIDs = array();

		$query = $db->getQuery(true);

		$query->select('vb.*')
			->from($db->quoteName('#__beseated_venue_table_booking','vb'))
			->where($db->quoteName('vb.user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('vb.is_rated') . ' = ' . $db->quote('0'))
			->where($db->quoteName('vb.deleted_by_venue') . ' = ' . $db->quote('0'))
			->where($db->quoteName('vb.deleted_by_user') . ' = ' . $db->quote('0'))
			->where($db->quoteName('vb.venue_status') . ' = ' . $db->quote('5'))
			->where($db->quoteName('vb.user_status') . ' = ' . $db->quote('5'));

		/*$query->select('vt.premium_table_id,vt.venue_table_name,vt.custom_table_name')
			->join('LEFT','#__beseated_venue_premium_table AS vt ON vt.venue_table_id=vb.venue_table_id');*/

		$query->select('v.venue_name')
			->join('LEFT','#__beseated_venue AS v ON v.venue_id=vb.venue_id');

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
				$currentTime            = time();
				$timestamp              = strtotime($bv->booking_date);
				$BookedTimestampAfert24 = strtotime('+1 day', $timestamp);

				if($currentTime >= $BookedTimestampAfert24)
				{
					if(!in_array($bv->table_id, $processedTableIDs))
					{
						$tempData = array();
						$tempData['bookingID'] = $bv->venue_table_booking_id;
						$tempData['tableID']   = $bv->table_id;
						$tempData['venueID']   = $bv->venue_id;

						$tableDetail = self::getVenueTableDetail($bv->table_id);

						$tempData['tableName'] = $tableDetail->table_name;
						$tempData['venueName'] = $bv->venue_name;
						$rateToVenue[] = $tempData;
					}
				}
			}
		}

		$ratingArras            = array();
		/*$ratingArras['service'] = $rateToService;*/
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

	public static function getUserType($userID)
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

	public static function getBeseatedMenuItem($alias)
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

	public static function getUserElementID($userID)
	{
		$bctedConfig = BeseatedHelper::getExtensionParam();
		$user        = JFactory::getUser($userID);
		$groups      = $user->get('groups');
		$groups      = array_values($groups);
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		if(in_array($bctedConfig->venue, $groups))
		{
			if(empty(self::$clubDetail))
			{
				$query->select('a.*')
  		 			->from($db->quoteName('#__beseated_venue','a'))
    				->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

				// Set the query and load the result.
				$db->setQuery($query);
				self::$clubDetail = $db->loadObject();

				//echo "<br />Rand : " . rand();
			}

			return self::$clubDetail;
		}
		else if(in_array($bctedConfig->yacht, $groups))
		{
			if(empty(self::$yachtDetail))
			{
				// Create the base select statement.
				$query->select('*')
					->from($db->quoteName('#__beseated_yacht'))
					->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

				// Set the query and load the result.
				$db->setQuery($query);
				$yachtDetail = $db->loadObject();
			}

			return $yachtDetail;
		}
		else if(in_array($bctedConfig->chauffeur, $groups))
		{
			if(empty(self::$chauffeurDetail))
			{
				// Create the base select statement.
				$query->select('*')
					->from($db->quoteName('#__beseated_chauffeur'))
					->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

				// Set the query and load the result.
				$db->setQuery($query);
				$chauffeurDetail = $db->loadObject();
			}

			return $chauffeurDetail;
		}
		else if(in_array($bctedConfig->protection, $groups))
		{
			if(empty(self::$protectionDetail))
			{
				// Create the base select statement.
				$query->select('*')
					->from($db->quoteName('#__beseated_protection'))
					->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

				// Set the query and load the result.
				$db->setQuery($query);
				$protectionDetail = $db->loadObject();
			}

			return $protectionDetail;
		}
		else if(in_array($bctedConfig->beseated_guest, $groups))
		{
			if(empty(self::$guestDetail))
			{
				// Create the base select statement.
				$query->select('*')
					->from($db->quoteName('#__beseated_user_profile'))
					->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

				// Set the query and load the result.
				$db->setQuery($query);
				self::$guestDetail = $db->loadObject();
			}

			return self::$guestDetail;
		}
	}

	// Used in beseateduser plugin
	public static function getExtensionParam()
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

	function getAddressFromLatlong($lattitude, $longitude)
	{
		$address = '';

		$url     = 'http://maps.google.com/maps/api/geocode/json?latlng=' . urlencode($lattitude . "," . $longitude) . '&sensor=false';

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

	public static function currencyFormat($currencyCode,$currencySign,$amount,$allowDeciaml = 0)
	{
		$amount = number_format($amount,$allowDeciaml);

		if($currencyCode == 'AED')
		{
			$currencySign = 'AED ';
		}
		return $currencySign.' '.$amount;
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
			->from($db->quoteName('#__beseated_loyalty_point'))
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
			$tmpData['pointID']   = $value->loyalty_point_id;
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
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_favourite'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Venue'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		if($result)
			return 1;
		else
			return 0;
	}

	public static function isFavouriteChauffeur($chauffeurID,$userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_favourite'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($chauffeurID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Chauffeur'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		if($result)
			return 1;
		else
			return 0;
	}

	public static function isFavouriteProtection($protectionID,$userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_favourite'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($protectionID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Protection'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		if($result)
			return 1;
		else
			return 0;
	}

	public static function isFavouriteYacht($protectionID,$userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_favourite'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($protectionID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Yacht'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		if($result)
			return 1;
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
				->from($db->quoteName('#__beseated_status'));

			// Set the query and load the result.
			$db->setQuery($query);
			$result = $db->loadObjectList();
			foreach ($result as $key => $item)
			{
				$nameArray[$item->status_name]             = $item;
				$idArray[$item->status_id]                 = $item;
				$statusDisplayArray[$item->status_display] = $item;
			}

			self::$collectedStatus->status_name    = $nameArray;
			self::$collectedStatus->status_id      = $idArray;
			self::$collectedStatus->status_display = $statusDisplayArray;
		}

		//return self::$collectedStatus;
	}

	public static function getUserForSplit($userEmail)
	{
		if (!self::$splitUserDetail)
		{
			//self::$collectedStatus = new stdClass;

			// Initialiase variables.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Create the base select statement.
			$query->select('id,email')
				->from($db->quoteName('#__users'));

			// Set the query and load the result.
			$db->setQuery($query);
			$result = $db->loadObjectList();
			foreach ($result as $key => $item)
			{
				self::$splitUserDetail[$item->email] = $item->id;
			}
		}

		if(isset(self::$splitUserDetail[$userEmail]))
		{
			return self::$splitUserDetail[$userEmail];
		}
		else
		{
			return 0;
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

		return self::$collectedStatus->status_display[$statusName]->status_id;
	}

	public static function getStatusNameFromStatusID($statusID)
	{
		self::collectAllStatus();

		if(!$statusID)
		{
			return '';
		}

		return self::$collectedStatus->status_id[$statusID]->status_id;
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

	

	

	
	public static function getLuxuryService($serviceID,$elementType)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_'.strtolower($elementType).'_services'))
			->where($db->quoteName('service_id') . ' = ' . $db->quote($serviceID))
			->where($db->quoteName('published') . ' = ' . $db->quote(1));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	

	public static function getVenueTableDetail($tableID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_table'))
			->where($db->quoteName('table_id') . ' = ' . $db->quote($tableID));

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
			->from($db->quoteName('#__beseated_venue_table'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('published') . ' = ' . $db->quote(1));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	public static function getVenueBottleDetail($bottleID, $venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_bottle'))
			->where($db->quoteName('bottle_id') . ' = ' . $db->quote($bottleID))
			->where($db->quoteName('venue_id') . '  = ' . $db->quote($venueID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}

	public static function getMessageConnectionOnly($fromUser,$toUser)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_message_connection'))
			->where($db->quoteName('from_user_id') . ' IN (' . $fromUser.','.$toUser .')')
			->where($db->quoteName('to_user_id') . ' IN (' . $fromUser.','.$toUser .')');

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		if($result)
			return $result->connection_id;
		else
			return 0;
	}

	// User in app Message view
	public static function getMessageConnection($fromUser,$toUser)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_message_connection'))
			->where($db->quoteName('from_user_id') . ' IN (' . $fromUser.','.$toUser .')')
			->where($db->quoteName('to_user_id') . ' IN (' . $fromUser.','.$toUser .')')
			->where($db->quoteName('to_user_id').' <> '.$db->quoteName('from_user_id'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		if($result)
		{
			return $result->connection_id;
		}
		else
		{
			JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
			$tblConnection = JTable::getInstance('Connection', 'BeseatedTable',array());
			$data                 = array();
			$data['from_user_id'] = $fromUser;
			$data['to_user_id']   = $toUser;
			$data['time_stamp']   = time();
			$tblConnection->load(0);
			$tblConnection->bind($data);
			if($tblConnection->store())
			{
				return $tblConnection->connection_id;
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
				$query->select('b.user_type')
					->join('LEFT','#__beseated_user_profile AS b ON b.user_id=a.userid');

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
							$options['data']['message']  = strip_tags($jsonarray['pushNotificationData']['message']);
							$options['data']['type']     = $jsonarray['pushNotificationData']['type'];
							$options['data']['id']       = ($jsonarray['pushNotificationData']['id'] != 0) ? $jsonarray['pushNotificationData']['id'] : $jsonarray['pushNotificationData']['id'];
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
		$tblMessage = JTable::getInstance('Message', 'BeseatedTable');
		$tblMessage->load(0);
		$loginUser  = JFactory::getUser();
		$data       = array();

		$data['message_type'] = 'Venue';
		$data['to_user_id']   = $TouserID;
		$data['message_body'] = $message;
		if(count($extraParam)!=0)
		{
			$data['extra_params'] = json_encode($extraParam);
		}
		else
		{
			$data['extra_params'] = "";
		}


		$data['created']      = date('Y-m-d H:i:s');
		$data['time_stamp']   = time();
		$data['from_user_id'] = $loginUser->id;
		$connectionID         = BeseatedHelper::getMessageConnection($data['from_user_id'],$data['to_user_id']);
		if(!$connectionID)
		{
			return 0;
		}

		$data['connection_id'] = $connectionID;

		$tblMessage->bind($data);

		if (!$tblMessage->store())
		{
			return 0;
		}
		else
		{
			return 1;
		}


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

	public static function getLiveBeseatedGuests()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('user_id')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('user_type') . ' = ' . $db->quote('beseated_guest'))
			->order($db->quoteName('user_id') . ' ASC');

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
			->from($db->quoteName('#__beseated_black_list'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote((int) $elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
			->where($db->quoteName('user_id') . ' = ' . $db->quote((int) $userID));

		// Set the query and execute the delete.
		$db->setQuery($query);

		$db->execute();
	}

	public static function addUserToBlackList($userID,$elementID,$elementType)
	{
		$tblBlacklist = JTable::getInstance('Blacklist', 'BeseatedTable');
		$tblBlacklist->load(0);

		$data = array();
		$data['element_id']   = $elementID;
		$data['element_type'] = $elementType;
		$data['user_id']      = $userID;
		$data['time_stamp']   = time();
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
			->from($db->quoteName('#__beseated_black_list'))
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
			->from($db->quoteName('#__beseated_black_list'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}

	public static function uploadFile($file,$elementType,$elementID)
	{
		$uploadedImage = "";
		$uploadLimit   = 80;
		$uploadLimit   = ( $uploadLimit * 1024 * 1024 );
		$input         = JFactory::getApplication()->input;
		$view          = $input->get('view');

		if(is_array($file) && isset($file['size']) && $file['size']>0)
		{
			jimport('joomla.filesystem.file');
			jimport('joomla.utilities.utility');
			jimport('joomla.filesystem.folder');

			if( filesize( $file['tmp_name'] ) > $uploadLimit && $uploadLimit != 0 )
			{
				return '';
			} // End of if( filesize( $file['tmp_name'] ) > $uploadLimit && $uploadLimit != 0 )

			$filename          = JApplication::getHash( $file['tmp_name'] . time() );
			$hashFileName      = JString::substr( $filename , 0 , 24 );
			$info['extension'] = pathinfo($file['name'],PATHINFO_EXTENSION);
			$info['extension'] = '.'.$info['extension'];

			if(!JFolder::exists(JPATH_ROOT . "/images/beseated/"))
			{
				JFolder::create(JPATH_ROOT . "/images/beseated/");
			}

			if(!JFolder::exists(JPATH_ROOT . "/images/beseated/".$elementType."/"))
			{
				JFolder::create(JPATH_ROOT . "/images/beseated/".$elementType."/");
			}

			if ($view == 'clubownertableedit' || $view == 'clubownerbottleedit')
			{
				if(!JFolder::exists(JPATH_ROOT . "/images/beseated/".$elementType."/". $elementID . '/Tables/'))
				{
					JFolder::create(JPATH_ROOT . "/images/beseated/".$elementType."/". $elementID . '/Tables/');
				}
			}
			else if ($view == 'protectionownerserviceedit'  || $view == "chauffeurownerserviceedit" || $view == "yachtownerserviceedit")
			{
				if(!JFolder::exists(JPATH_ROOT . "/images/beseated/".$elementType."/". $elementID . '/Services/'))
				{
					JFolder::create(JPATH_ROOT . "/images/beseated/".$elementType."/". $elementID . '/Services/');
				}
			}
			else
			{
				if(!JFolder::exists(JPATH_ROOT . "/images/beseated/".$elementType."/". $elementID . '/'))
				{
					JFolder::create(JPATH_ROOT . "/images/beseated/".$elementType."/". $elementID . '/');
				}
			}

			if ($view == 'clubownertableedit' || $view == 'clubownerbottleedit'){
				$storage       = JPATH_ROOT . '/images/beseated/'.$elementType.'/'. $elementID. '/Tables';
			}
			else if ($view == 'protectionownerserviceedit'  || $view == "chauffeurownerserviceedit" || $view == "yachtownerserviceedit"){
				$storage       = JPATH_ROOT . '/images/beseated/'.$elementType.'/'. $elementID. '/Services';
			}
			else{
				$storage       = JPATH_ROOT . '/images/beseated/'.$elementType.'/'. $elementID;
			}
			$storageImage  = $storage . '/' . $hashFileName .  $info['extension'] ;

			if ($view == 'clubownertableedit' || $view == 'clubownerbottleedit'){
				$uploadedImage = $elementType.'/' .$elementID .'/Tables/'. $hashFileName . $info['extension'] ;
			}
			else if ($view == 'protectionownerserviceedit' || $view == "chauffeurownerserviceedit"  || $view == "yachtownerserviceedit")
			{
				$uploadedImage = $elementType.'/' .$elementID .'/Services/'. $hashFileName . $info['extension'] ;
			}
			else
			{
				$uploadedImage = $elementType.'/' .$elementID .'/'. $hashFileName . $info['extension'] ;
			}

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
		$cmdOut  = BeseatedHelper::_runCommand($command);
		$command = "/usr/bin/ffmpeg -i ".$videoFullPath." -vf \"transpose=1\" -ar 22050 ".$videoMp4Path; //Wroking
		$cmdOut  = BeseatedHelper::_runCommand($command);
		$command = "/usr/bin/ffmpeg -i ".$videoFullPath." -sameq ".$videoOggPath; //Wroking
		$cmdOut  = BeseatedHelper::_runCommand($command);

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

	public static function _runCommand($command)
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
		$i   = strrpos($str,".");
		if (!$i) { $ext = ""; }
		$l   = strlen($str) - $i;
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
		{
			@chmod($thumbName,0777);
			imagepng($dst_img,$thumbName);
		}
		else
		{
			@chmod($thumbName,0777);
			imagejpeg($dst_img,$thumbName);
		}

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

	public static function createVideoThumb($videoFile, $thumbFile, $thumbSize='600x500')
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.utility');
		jimport( 'joomla.user.helper' );

		$videoInfor = BeseatedHelper::getVideoInfo($videoFile);

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
		$cmdOut = BeseatedHelper::_runCommand($cmd);

		if (JFile::exists($thumbFullPath) && (filesize($thumbFullPath) > 0))
		{
			return $thumbFileName;
		}

		$cmd	= '/usr/bin/ffmpeg' . ' -i ' . $videoFile . ' -vcodec mjpeg -vframes 1 -an -f rawvideo ' .  $thumbFullPath;
		$cmdOut = BeseatedHelper::_runCommand($cmd);

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

	public static function getVideoInfo($videoFile, $cmdOut = '')
	{
		$data = array();

		if (!is_file($videoFile) && empty($cmdOut))
			return $data;

		if (!$cmdOut) {
			$cmd	= '/usr/bin/ffmpeg' . ' -i ' . $videoFile . ' 2>&1';
			$cmdOut	= BeseatedHelper::_runCommand($cmd);
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
			$data['duration']['sec']	= $videoFrame = BeseatedHelper::formatDuration($data['duration']['hms'], 'seconds');
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

	public static function formatDuration($duration = 0, $format = 'HH:MM:SS')
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

		$companyTypes = array('yacht','protection','chauffeur');

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
		$companyEmails = array();
		$venueEmails   = array();
		$allRegEmail   = array();

		$invalidEmailflg = 0;

		for ($i = 0; $i < count($userids); $i++)
		{
			$query1 = $db->getQuery(true);

			// Create the base select statement.
			$query1->select('a.user_type,b.email')
				->from($db->quoteName('#__beseated_user_profile').'AS a')
				->where($db->quoteName('a.user_id') . ' = ' . $db->quote($userids[$i]))
				->join('INNER', '#__users AS b ON b.id=a.user_id');

			// Set the query and load the result.
			$db->setQuery($query1);
			$userData = $db->loadObject();

			$allRegEmail[] = $userData->email;

			if($userData->user_type == 'beseated_guest')
			{
				$guestEmails[] = $userData->email;
			}

			if(in_array($userData->user_type,$companyTypes))
			{
				$companyEmails[] = $userData->email;
			}

			if($userData->user_type == 'venue')
			{
				$venueEmails[] = $userData->email;
			}
		}

		$foundEmails                = array();
		$foundEmails['guest']       = $guestEmails;
		$foundEmails['company']     = $companyEmails;
		$foundEmails['venue']       = $venueEmails;
		$foundEmails['allRegEmail'] = $allRegEmail;

		return $foundEmails;
	}

	public static function filterFbIds($fbIds)
	{
		$seletedFbIds = explode(",", $fbIds);

		$checkForValid = 0;
		if(count($seletedFbIds)!=0)
		{
			$checkForValid = 1;
		}

		$seletedFbIds = implode("','", $seletedFbIds);
		$seletedFbIds = "'".$seletedFbIds."'";

		$companyTypes = array('yacht','protection','chauffeur','venue');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('b.id')
			->from($db->quoteName('#__beseated_user_profile'). ' AS a ')
			->where($db->quoteName('b.block') . ' = ' . $db->quote('0'))
			->where($db->quoteName('a.fb_id') . ' IN ('.$seletedFbIds.')')
			->join('INNER', '#__users AS b ON b.id=a.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$userids = $db->loadColumn();

		$guestEmails   = array();
		$companyEmails = array();
		$venueEmails   = array();
		$allRegEmail   = array();

		$invalidEmailflg = 0;

		for ($i = 0; $i < count($userids); $i++)
		{
			$query1 = $db->getQuery(true);

			// Create the base select statement.
			$query1->select('a.user_type,b.email')
				->from($db->quoteName('#__beseated_user_profile').'AS a')
				->where($db->quoteName('a.user_id') . ' = ' . $db->quote($userids[$i]))
				->where($db->quoteName('a.is_fb_user') . ' = ' . $db->quote('1'))
				->join('INNER', '#__users AS b ON b.id=a.user_id');

			// Set the query and load the result.
			$db->setQuery($query1);
			$userData = $db->loadObject();

			if(!empty($userData))
			{
				$allRegEmail[] = $userData->email;

				if($userData->user_type == 'beseated_guest')
				{
					$guestEmails[] = $userData->email;
				}

				if(in_array($userData->user_type,$companyTypes))
				{
					$companyEmails[] = $userData->email;
				}

				if($userData->user_type == 'venue')
				{
					$venueEmails[] = $userData->email;
				}
			}
		}

		$foundEmails                = array();
		$foundEmails['guest']       = $guestEmails;
		$foundEmails['company']     = $companyEmails;
		$foundEmails['venue']       = $venueEmails;
		$foundEmails['allRegEmail'] = $allRegEmail;

		return $foundEmails;
	}

	public static function filterFbIdsToUserIDs($fbIds)
	{
		$seletedFbIds = explode(",", $fbIds);

		$checkForValid = 0;
		if(count($seletedFbIds)!=0)
		{
			$checkForValid = 1;
		}

		$seletedFbIds = implode("','", $seletedFbIds);
		$seletedFbIds = "'".$seletedFbIds."'";

		$companyTypes = array('yacht','protection','chauffeur','venue');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('b.id')
			->from($db->quoteName('#__beseated_user_profile'). ' AS a ')
			->where($db->quoteName('b.block') . ' = ' . $db->quote('0'))
			->where($db->quoteName('a.fb_id') . ' IN ('.$seletedFbIds.')')
			->join('INNER', '#__users AS b ON b.id=a.user_id');

		// Set the query and load the result.
		$db->setQuery($query);
		$userids = $db->loadColumn();

		$guestEmails   = array();
		$companyEmails = array();
		$venueEmails   = array();
		$allRegEmail   = array();

		$invalidEmailflg = 0;

		for ($i = 0; $i < count($userids); $i++)
		{
			$query1 = $db->getQuery(true);

			// Create the base select statement.
			$query1->select('a.user_id,a.user_type,b.email')
				->from($db->quoteName('#__beseated_user_profile').'AS a')
				->where($db->quoteName('a.user_id') . ' = ' . $db->quote($userids[$i]))
				->where($db->quoteName('a.is_fb_user') . ' = ' . $db->quote('1'))
				->join('INNER', '#__users AS b ON b.id=a.user_id');

			// Set the query and load the result.
			$db->setQuery($query1);
			$userData = $db->loadObject();

			if(!empty($userData))
			{
				$allRegEmail[] = $userData->user_id;

				if($userData->user_type == 'beseated_guest')
				{
					$guestEmails[] = $userData->user_id;
				}

				if(in_array($userData->user_type,$companyTypes))
				{
					$companyEmails[] = $userData->user_id;
				}

				if($userData->user_type == 'venue')
				{
					$venueEmails[] = $userData->user_id;
				}
			}
		}

		$foundEmails                = array();
		$foundEmails['guest']       = $guestEmails;
		$foundEmails['company']     = $companyEmails;
		$foundEmails['venue']       = $venueEmails;
		$foundEmails['allRegEmail'] = $allRegEmail;

		return $foundEmails;
	}

	public static function getUserTimezoneDifferent($userID)
	{
		$userDetail = BeseatedHelper::getUserElementID($userID);

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
	public static function getBiggestSpender($venue_id)
	{
		$statusID = self::getStatusID('booked');
		// Initialiase variables.
		$user = JFactory::getUser();
		$db   = JFactory::getDbo();

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
		 /*&& $spender->user_id != $user->id*/

		foreach ($resBigSpender as $key => $spender)
		{
			if(in_array($spender->user_id, $resLiveUsers))
			{
				$spenderDetail = self::guestUserDetail($spender->user_id);

				if($spenderDetail->show_in_biggest_spender)
				{
					$temp = array();
					$temp['userID']      = $spenderDetail->user_id;
					$temp['fbID']        = ($spenderDetail->fb_id)?$spenderDetail->fb_id:'';
					$temp['avatar']      = ($spenderDetail->avatar)?self::getUserAvatar($spenderDetail->avatar):'';
					$temp['thumbAvatar'] = ($spenderDetail->thumb_avatar)?self::getUserAvatar($spenderDetail->thumb_avatar):'';
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
	 *  Method to get chauffeurUserDetail profile
	 *
	 * @param   integer  $userID  User ID.
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public static function guestUserDetail($userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
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
	 * @param   integer  $elementID  Element ID.
	 *
	 * @param   string  $elementType  Element Type.
	 *
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public static function getUserAvatar($image)
	{
		//echo "<pre/>";print_r("hi");exit;
		if(empty($image))
		{

			$app = JFactory::getApplication('site');
			$componentParams = $app->getParams('com_beseated');
			$image = JURI::root().$componentParams->get('default_image', '');

			return $image;
		}

		$url = parse_url($image);

		if(is_array($url) && isset($url['scheme']) && $url['scheme'] == 'https')
		{
			return $image;
		}
		else if(is_array($url) && isset($url['scheme']) && $url['scheme'] == 'http')
		{
			return $image;
		}
		else
		{
			return JUri::base().'images/beseated/'.$image;
		}
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
	public static function convertToYYYYMMDD($date)
	{
		return date('Y-m-d',strtotime($date));
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
	public static function convertDateFormat($date,$format = 'd-m-Y')
	{
		return date($format,strtotime($date));
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
	public static function getStatusID($statusName)
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
	public static function getStatusName($statusID)
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

	public static function isPastDate($date)
	{
		$currentDate = date('Y-m-d');
		$date = new DateTime($date);
		$now  = new DateTime($currentDate);

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
	public static function getElementDefaultImage($elementID,$elementType)
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
			$result = BeseatedHelper::getElementImage($elementID,$elementType);
		}

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
	public static function getInvitationDetail($bookingID,$bookingType = '')
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

	public static function getUserEmail($fbID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('email')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('fb_id') . ' = ' . $db->quote($fbID))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $result;
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
	public static function getElementImage($elementID,$elementType)
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
	 *  Method to get guestUserDetailFromEmail
	 *
	 * @param   integer  $userID  User ID.
	 *
	 * @return Object
	 *
	 * @since    1.0
	 */
	public static function guestUserDetailFromEmail($email)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('email') . ' = ' . $db->quote($email))
			->where($db->quoteName('is_deleted') . ' = ' . $db->quote(0));

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
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
	public static function storeNotification($actor,$target,$elementID,$elementType,$notificationType,$title,$cid,$extraParams = array())
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
		$notificationPost['is_new']            = "1";
		$notificationPost['extra_pramas']      = json_encode($extraParams);
		$notificationPost['time_stamp']        = time();
		$tblNotification->bind($notificationPost);

		if($tblNotification->store())
		{
			return true;
		}
		return false;
	}

	public function getMessageThread()
	{
		// Initialiase variables.
		$user        = JFactory::getUser();
		$db          = JFactory::getDbo();
		$queryThread = $db->getQuery(true);

		// Create the base select statement.
		$queryThread->select('MAX(a.message_id)')
			->from($db->quoteName('#__beseated_message') .' AS a')
			//->where('(('.$db->quoteName('a.from_user_id') . ' = '. $db->quote($this->IJUserID) . ' OR '.$db->qn('a.to_user_id') . ' = '. $db->quote($this->IJUserID).'))')
			//->where($db->quoteName('b.deleted_by_from_user') . ' = ' . $db->quote('0'))
			->where('(('.$db->quoteName('a.from_user_id') . ' = '. $db->quote($user->id) .' AND '. $db->quoteName('a.deleted_by_from_user') .' = ' . $db->quote('0').')' . ' OR ' .'('.$db->qn('a.to_user_id') . ' = '. $db->quote($user->id) . ' AND ' . $db->quoteName('a.deleted_by_to_user') .' = ' . $db->quote('0').'))')
			->join('INNER','#__beseated_message_connection AS b ON b.connection_id = a.connection_id')
			->group($db->quoteName('a.connection_id'))
			->order($db->quoteName('a.time_stamp') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($queryThread);
		$MaxIds = $db->loadColumn();

		if(count($MaxIds) == 0)
		{
			return array();
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

		return $resThreads;
	}

	public static function getMessageDetail()
	{
		$app          = JFactory::getApplication();
		$input        = $app->input;
		$connectionID = $input->get('connection_id',0,'int');
		$user         = JFactory::getUser();

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->quoteName('#__beseated_message'))
			->where($db->quoteName('connection_id') . ' = ' . $db->quote($connectionID))
			->where('(('.$db->quoteName('from_user_id') . ' = '. $db->quote($user->id) .' AND '. $db->quoteName('deleted_by_from_user') .' = ' . $db->quote('0').')' . ' OR ' .'('.$db->qn('to_user_id') . ' = '. $db->quote($user->id) . ' AND ' . $db->quoteName('deleted_by_to_user') .' = ' . $db->quote('0').'))')
			->order($db->quoteName('time_stamp') . ' ASC');

		$db->setQuery($query);
        $result = $db->loadObjectList();

        return $result;
			
	}

	public static function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
	{
		//echo "<pre>";print_r($arr);echo "</pre>";exit;

		$sort_col = array();

		foreach ($arr as $key=> $row)
		{
			$sort_col[$key] = strtotime($row[$col]);
		}

		//echo "<pre/>";print_r($dir);
		array_multisort($sort_col, $dir, $arr);

		//echo "<pre>";print_r(date('d-m-Y',));echo "</pre>";exit;
	}


	public static function deleteMessageThread($connectionID)
	{
		$user               = JFactory::getUser();

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');
		$msgConnectionTable = JTable::getInstance('MessageConnection', 'BeseatedTable');

		$msgConnectionTable->load($connectionID);

		if(!$msgConnectionTable->connection_id)
		{
			return 400; // COM_BESEATED_MESSAGE_ERROR_WHILE_DELETING_MESSAGE
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

    	foreach ($messageDetails as $key => $message)
    	{
	    	 if($message->from_user_id == $user->id)
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

    	$cntMessageDetails = self::getMessageDetail();
    	if(count($cntMessageDetails) == 0)
    	{
    		return 204;
    	}

		return 200;

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

	function getBookedElementShareInvitations($bookingType,$bookingID)
	{
		$statusArray    = array();
		$statusArray[] =  self::getStatusID('paid');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Create the base select statement.
		$query->select('email')
			->from($db->quoteName('#__beseated_'.$bookingType.'_booking_split'))
			->where($db->quoteName('split_payment_status') . ' IN ('.implode(",", $statusArray).')')
			->where($db->quoteName('is_owner') . ' = ' . $db->quote('0'))
			->where($db->quoteName($bookingType.'_booking_id') . ' = ' . $db->quote($bookingID));

		$db->setQuery($query);
		$bookedSharedUsers = $db->loadObjectList();

		return $bookedSharedUsers;
	}

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

	public 	function encrypt($plainText,$key)
	{
		$secretKey = self::hextobin(md5($key));

		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);

		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');

		$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');

		$plainPad = self::pkcs5_pad($plainText, $blockSize);
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

			$packedString = pack("H",trim($subString));
			//$packedString = pack("H*",trim($subString)); // jamal

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
		$secretKey = self::hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$encryptedText=self::hextobin($encryptedText);
		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
		mcrypt_generic_init($openMode, $secretKey, $initVector);
		$decryptedText = mdecrypt_generic($openMode, $encryptedText);
		$decryptedText = rtrim($decryptedText, "\0");
		mcrypt_generic_deinit($openMode);
		return $decryptedText;
	}




}
?>