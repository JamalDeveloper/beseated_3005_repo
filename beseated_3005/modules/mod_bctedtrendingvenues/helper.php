<?php
/**
* @version		$Id: mod_appzoom.php 10000 2014-01-22 03:35:53Z schro $
* @package		Joomla 3.2.x
* @copyright	Copyright (C) 2005 - 2014 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* */
// no direct access
defined('_JEXEC') or die('Restricted access');

abstract class modBctedTrendingVenues
{
	public static function getVenueByRating()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.

		$query->select('a.*, i.*')
			->from($db->quoteName('#__beseated_venue','a'))
			->join('LEFT', $db->quoteName('#__beseated_element_images', 'i') . ' ON a.venue_id = i.element_id')
			->where($db->quoteName('a.published') . ' =  ' .  $db->quote(1))
			->order($db->quoteName('avg_ratting') . ' DESC');


		// Set the query and load the result.
		$db->setQuery($query,0,3);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $result;
	}

	public static function getVenueByCity()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$app = JFactory::getApplication();
		$city = $app->input->cookie->getString('search_in_city', '');

		// Create the base select statement.
		$query->select('a.*, i.*')
			->from($db->quoteName('#__beseated_venue','a'))
			->join('LEFT', $db->quoteName('#__beseated_element_images', 'i') . ' ON a.venue_id = i.element_id')
			->where($db->quoteName('has_table') . ' = ' . $db->quote('1'))
			->where($db->quoteName('a.published') . ' =  ' .  $db->quote('1'));

		if(!empty($city))
		{
			$query->where($db->quoteName('a.city') . ' = ' . $db->quote($city));
		}
		$query->group($db->quoteName('a.venue_id'));
		$query->order($db->quoteName('a.avg_ratting') . ' DESC');

		// Set the query and load the result.
		$db->setQuery($query,0,3);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $result;
	}
}
