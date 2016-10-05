<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * The Beseated Profile Model
 *
 * @since  0.0.1
 */
class BeseatedModelProfile extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelList
	 * @since   0.0.1
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array();
		}

		parent::__construct($config);
	}

	public function getProfileData()
	{
		$user          = JFactory::getUser();
		$elementType   = BeseatedHelper::getUserType($user->id);
		$elementDetail = BeseatedHelper::getUserElementID($user->id);

		return $elementDetail;
	}

	public function saveVenueProfile($data,$id)
	{
		$tblVenue = JTable::getInstance('Venue','BeseatedTable',array());
		$tblVenue->load($id);
		$tblVenue->bind($data);
		if($tblVenue->store())
		{
			$tblUser = JTable::getInstance('Users','BeseatedTable',array());
		    $tblUser->load($tblVenue->user_id);
 
			if($tblUser->id)
			{
				$tblUser->name = trim($tblVenue->venue_name);
				$tblUser->store();
			}

			$tblBctUser     = JTable::getInstance('Profile', 'BeseatedTable', array());
			$bctUserProfile = BeseatedHelper::getBeseatedUserProfile($tblVenue->user_id);
			$tblBctUser->load($bctUserProfile->user_id);
			$tblBctUser->full_name = $tblVenue->venue_name;
			$tblBctUser->store();

			return true;
		}

		return false;
	}

	public function is_booking_in_venue($venue_id)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venue_id))
			->where($db->quoteName('venue_status') . ' = ' . $db->quote(5));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObjectList();

		if(count($result)!=0)
		{
			return 1;
		}

		$query       = $db->getQuery(true);
		$statusArray = array(1,6);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venue_id))
			->where($db->quoteName('venue_status') . ' IN (' . implode(",", $statusArray) .')');

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadObjectList();
		$pastCount = 0;
		foreach ($result as $key => $value)
		{
			$bookingTime = strtotime($value->venue_booking_datetime);
			$currentTime = strtotime(date('Y-m-d'));

			if($currentTime > $bookingTime)
			{
				$pastCount = $pastCount + 1;
			}
		}

		if(count($result) == $pastCount)
		{
			return 0;
		}

		return 1;
	}

	public function deleteImage($imageId, $elementId)
	{

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->delete()
			->from($db->quoteName('#__beseated_element_images'))
			->where($db->quoteName('image_id') . ' = ' . $db->quote((int) $imageId))
			->where($db->quoteName('element_id') . ' = ' . $db->quote((int) $elementId));

		// Set the query and execute the delete.
		$db->setQuery($query);

		try
		{
			$result = $db->execute();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $result;

	}

	public function getMusicList()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			  ->from($db->quoteName('#__beseated_venue_music_table'))
			  ->where($db->quoteName('published') . ' = ' . $db->quote('1'));

		// Set the query and load the result.
		$db->setQuery((string) $query);
			
		$musiclists = $db->loadObjectList();

		return $musiclists;

		
	}

}
