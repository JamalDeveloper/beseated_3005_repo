<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;


/**
 * Beesated Event Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerVenueBookingRequest extends JControllerForm
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 */
	public function getVenueServcies()
	{
		$venue_id = JRequest::getInt('venue_id');

		$this->getServiceList($venue_id);

		$options  =array();
		$services = $this->serviceList;

		if ($services)
		{
			foreach ($services as $key => $service)
			{
				$options[] = JHtml::_('select.option', $service->table_id, $service->table_name);
			}
		}

		//echo "<pre/>";print_r($options);exit;

		$dropdown = JHTML::_('select.genericlist', $options, 'jform[table_id]', 'class="inputbox service_name_chzn"', 'value', 'text', 1);

		echo $dropdown;
		exit;
	}

	/**
	 * Get list of mail's type to display
	 *
	 * @return  [type]  [description]
	 */
	public function getServiceList($venue_id)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select(
			array(
				'table_id',
				'table_name',

			)
		);
			$query->from($db->quoteName('#__beseated_venue_table'))
			     ->where($db->quoteName('venue_id') . ' = ' . $db->quote($venue_id));

		// Set the query and load the result.
		$db->setQuery((string) $query);

		try
		{
			$this->serviceList = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}
	}

	public function isDayClub()
	{
		$venue_id = JRequest::getInt('venue_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('is_day_club')
		      ->from($db->quoteName('#__beseated_venue'))
		      ->where($db->quoteName('venue_id') . ' = ' . $db->quote($venue_id))
		      ->where($db->quoteName('has_table') . ' = ' . $db->quote('1'))
			  ->where($db->quoteName('published') . ' = ' . $db->quote('1'))
			  ->where($db->quoteName('is_deleted') . ' = ' . $db->quote('0'));

		// Set the query and load the result.
		$db->setQuery($query);
		$isDayClub = $db->loadResult();

		echo $isDayClub;
		exit;

	}

}
