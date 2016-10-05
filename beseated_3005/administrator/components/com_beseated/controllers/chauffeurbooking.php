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
class BeseatedControllerChauffeurBooking extends JControllerForm
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
	public function getChauffeurServcies()
	{
		$chauffeur_id = JRequest::getInt('chauffeur_id');

		$this->getServiceList($chauffeur_id);

		$options  =array();
		$services = $this->serviceList;

		if ($services)
		{
			foreach ($services as $key => $service)
			{
				$options[] = JHtml::_('select.option', $service->service_id, $service->service_name);
			}
		}

		$dropdown = JHTML::_('select.genericlist', $options, 'jform[service_id]', 'class="inputbox service_name_chzn"', 'value', 'text', 1);

		echo $dropdown;
		exit;
	}

	/**
	 * Get list of mail's type to display
	 *
	 * @return  [type]  [description]
	 */
	public function getServiceList($chauffeur_id)
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
			$query->from($db->quoteName('#__beseated_chauffeur_services'))
			     ->where($db->quoteName('chauffeur_id') . ' = ' . $db->quote($chauffeur_id));

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

}
