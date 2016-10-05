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
 * The Beseated Club Table Booking Model
 *
 * @since  0.0.1
 */
class BeseatedModelYachtServiceBooking extends JModelList
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

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   0.0.1
	 */
	public function getTable($type = 'YachtBooking', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function bookYachtService($data = array())
	{

		$tblYachtbooking = $this->getTable();
		$tblYachtbooking->load(0);
		$tblYachtbooking->bind($data);
		if(!$tblYachtbooking->store())
		{
			return 0;
		}

		return 1;

	}

	public function getServiceDetail()
	{
		$serviceID = JFactory::getApplication()->input->getInt('service_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('s.*')
			->from($db->quoteName('#__beseated_yacht_services', 's'))
			->where($db->quoteName('s.published') . ' = ' . $db->quote('1'))
			->where($db->quoteName('s.service_id') . ' = ' . $db->quote($serviceID));
		$query->select('p.*')
     		->join('LEFT','#__beseated_yacht AS p ON s.yacht_id=p.yacht_id');

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();
		return $result;
	}
}
