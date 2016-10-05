<?php
/**
* @version		$Id: mod_appzoom.php 10000 2014-01-22 03:35:53Z schro $
* @package		Joomla 3.2.x
* @copyright	Copyright (C) 2005 - 2014 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* */
// no direct access
defined('_JEXEC') or die('Restricted access');

abstract class modBctedTitle
{
	public static function getUserInformation()
	{


		//return $items;
	}

	public static function getExtensionParam()
	{
		include_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$params = BeseatedHelper::getExtensionParam();
		return $params;
	}

	/**
	 *  Method to get a table object, load it if necessary.
	 *
	 * @param   integer  $userID  User ID.
	 *
	 * @return JTable A JTable object
	 *
	 * @since    0.0.1
	 */
	public static function getUserVenueDetail($userID = 0,$venueID = 0)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue'));
		if($userID)
		{
			$query->where($db->quoteName('userid') . ' = ' . $db->quote($userID));
		}
		else if($venueID)
		{
			$query->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID));
		}
		else
		{
			return 0;
		}

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$venue = $db->loadObject();

			if($venue)
				return $venue;
			else
				return 0;

		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 *  Method to get a table object, load it if necessary.
	 *
	 * @param   integer  $userID  User ID.
	 *
	 * @return JTable A JTable object
	 *
	 * @since    0.0.1
	 */
	public static function getUserCompanyDetail($userID = 0,$venueID = 0)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_company'));

		if($userID)
		{
			$query->where($db->quoteName('userid') . ' = ' . $db->quote($userID));
		}
		else if($venueID)
		{
			$query->where($db->quoteName('company_id') . ' = ' . $db->quote($venueID));
		}
		else
		{
			return 0;
		}

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$company = $db->loadObject();

			if($company)
				return $company;
			else
				return 0;

		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}
}
