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
 * The Beseated Club Tables Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubBottles extends JModelList
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

	public function getVenueBottles()
	{
		$app    = JFactory::getApplication();
		$input  = $app->input;
		$clubId = $input->get('club_id');
		$user   = JFactory::getUser();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);

		// Set the query and load the result.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_venue_bottle','a'))
			->where($db->quoteName('a.published') . ' =  ' .  $db->quote(1))
			->where($db->quoteName('a.venue_id') . ' =  ' .  $db->quote($clubId));

		$query->select('b.currency_code,b.currency_sign')
			->join('LEFT','#__beseated_venue AS b ON b.venue_id=a.venue_id');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	public function getBookedTable()
	{
		$input   = JFactory::getApplication()->input;
		$venueId = $input->get('club_id');
		$tableID = $input->get('table_id');
		$user    = JFactory::getUser();
		$userID  = $user->id;

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueId))
			->where($db->quoteName('table_id') . ' = ' . $db->quote($tableID))
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d')))
			->where($db->quoteName('venue_status') . ' = ' . $db->quote('3'))
			->where($db->quoteName('user_status') . ' = ' . $db->quote('4'))
			->order($db->quoteName('venue_table_booking_id') . ' ASC');

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
}
