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
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.utilities.utility');

/**
 * Beesated Event Controller
 *
 * @since  0.0.1
 */
class BeseatedControllerProtectionBooking extends JControllerForm
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
	public function getProtectionServcies()
	{
		$protection_id = JRequest::getInt('protection_id');

		$this->getServiceList($protection_id);

		$options  =array();
		$services = $this->serviceList;

		if ($services)
		{
			foreach ($services as $key => $service)
			{
				$options[] = JHtml::_('select.option', $service->service_id, $service->service_name);
			}
		}

		//echo "<pre/>";print_r($options);exit;

		$dropdown = JHTML::_('select.genericlist', $options, 'jform[service_id]', 'class="inputbox service_name_chzn"', 'value', 'text', 1);

		echo $dropdown;
		exit;
	}



	/**
	 * Get list of mail's type to display
	 *
	 * @return  [type]  [description]
	 */
	public function getServiceList($protection_id)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select(
			array(
				'service_id',
				'service_name',

			)
		);
			$query->from($db->quoteName('#__beseated_protection_services'))
			     ->where($db->quoteName('protection_id') . ' = ' . $db->quote($protection_id));

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

	public function getServiceHours()
	{
		$service_id = JRequest::getInt('service_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select(
			array(
				'total_hours'

			)
		);
			$query->from($db->quoteName('#__beseated_protection_services'))
			     ->where($db->quoteName('service_id') . ' = ' . $db->quote($service_id));

		// Set the query and load the result.
		$db->setQuery((string) $query);

		$serviceHours = $db->loadResult();

		return $serviceHours;

	}

	public function getServiceHour()
	{
		$service_id = JRequest::getInt('service_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();

		// $db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('min_hours')
			->from($db->quoteName('#__beseated_protection_services'))
			->where($db->quoteName('service_id') . ' = ' . $db->quote($service_id));

		// Set the query and load the result.
		$db->setQuery($query);
		$min_hours = $db->loadResult();

		echo $min_hours;
		exit;
	}

}
