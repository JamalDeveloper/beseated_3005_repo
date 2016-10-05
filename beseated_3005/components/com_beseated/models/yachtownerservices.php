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
 * The Beseated Club Owner Tables
 *
 * @since  0.0.1
 */
class BeseatedModelYachtOwnerServices extends JModelList
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
	public function getTable($type = 'YachtService', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getYachtServices()
	{
		$app     = JFactory::getApplication();
		$input   = $app->input;
		$user    = JFactory::getUser();
		$element = BeseatedHelper::getUserElementID($user->id);
		$yachtID = $element->yacht_id;
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);

		// Set the query and load the result.
		$query->select('a.*')
			->from($db->quoteName('#__beseated_yacht_services','a'))
			->where($db->quoteName('a.published') . ' =  ' .  $db->quote(1))
			->where($db->quoteName('a.yacht_id') . ' =  ' .  $db->quote($yachtID));

		$query->select('b.currency_code,b.currency_sign')
			->join('LEFT','#__beseated_yacht AS b ON b.yacht_id=a.yacht_id');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	public function deleteService($serviceID)
	{
		$serviceTable = $this->getTable();
		$serviceTable->load($serviceID);
		$serviceTable->published = 0;

		$bookingsAvailable = $this->checkForActiveBooking($serviceTable->service_id,$serviceTable->yacht_id);

		if(count($bookingsAvailable) != 0)
		{
			return "500";
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__beseated_yacht_booking'))
			->set($db->quoteName('deleted_by_user') . ' = ' . $db->quote(1))
			->set($db->quoteName('deleted_by_yacht') . ' = ' . $db->quote(1))
			->where($db->quoteName('service_id') . ' = ' . $db->quote($serviceTable->service_id));

		$db->setQuery($query);
		$db->execute();

		if($serviceTable->store())
		{
			return "200";
		}

		return "500";
	}

	public function checkForActiveBooking($serviceID,$yachtID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('yacht_booking_id')
			->from($db->quoteName('#__beseated_yacht_booking'))
			->where($db->quoteName('yacht_id') . ' = ' . $db->quote($yachtID))
			->where($db->quoteName('service_id') . ' = ' . $db->quote($serviceID))
			/*->where($db->quoteName('is_deleted') . ' = ' . $db->quote(0))*/
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d H:i')));


		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadColumn();

		return $result;
	}
}
