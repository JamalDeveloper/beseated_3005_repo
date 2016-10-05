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
 * The Beseated Club Requests Model
 *
 * @since  0.0.1
 */
class BeseatedModelGuestRequestsDetail extends JModelList
{
	protected $liveUsers;
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
		$this->liveUsers = BeseatedHelper::getLiveBeseatedGuests();
		parent::__construct($config);
	}

	public function getVenueRsvpDetails()
	{
		$user      = JFactory::getUser();
		$app       = JFactory::getApplication();
		$bookingID = $app->input->getInt('table_booking_id');
		$venueID   = $app->input->getInt('venue_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('tb.*,t.table_name,t.min_price')
			->from($db->quoteName('#__beseated_venue_table_booking', 'tb'))
			->where($db->quoteName('tb.venue_table_booking_id') . ' = ' . $db->quote($bookingID))
			->join('LEFT','#__beseated_venue_table AS t ON tb.table_id=t.table_id');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$venueDetail = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $venueDetail;
	}

	public function getHasVenueBottle()
	{
		$app       = JFactory::getApplication();
		$venueID   = $app->input->getInt('venue_id');
		// Initialiase variables.
		$db    = JFactory::getDbo();

		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('COUNT(bottle_id) bottleCount')
			->from($db->quoteName('#__beseated_venue_bottle'))
			->where($db->quoteName('published') . ' = ' . $db->quote('1'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID));

		// Set the query and load the result.
		$db->setQuery($query);
		$bottleCount = $db->loadResult();


		return $bottleCount;

	}

}
