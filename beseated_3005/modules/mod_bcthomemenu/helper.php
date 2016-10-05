<?php
/**
* @version		$Id: mod_appzoom.php 10000 2014-01-22 03:35:53Z schro $
* @package		Joomla 3.2.x
* @copyright	Copyright (C) 2005 - 2014 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* */
// no direct access
defined('_JEXEC') or die('Restricted access');

abstract class modBctedHomeMenu
{
	public static function getUserInformation()
	{
		echo "call in helper";
		exit;

		//return $items;
	}

	public static function getUserGroup($userID)
	{
		$bctedConfig = modBctedHomeMenu::getExtensionParam();

		$user = JFactory::getUser($userID);

		$groups = $user->get('groups');

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
			return  'Registered';
		}
		else
		{
			return 'Public';
		}

		return 'Registered';


	}

	public static function getExtensionParam()
	{
		$app    = JFactory::getApplication();
		//$option = $app->input->get('option');
		$option = "com_bcted";
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

		return $params;
	}
}
