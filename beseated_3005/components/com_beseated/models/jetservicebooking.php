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
 * The Beseated Jet Service Booking Model
 *
 * @since  0.0.1
 */
class BeseatedModelJetServiceBooking extends JModelList
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
	public function getTable($type = 'PrivateJetBooking', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getAirportList()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		//$query->select(' CAST(aiport AS BOOLEAN)')
			//->from($db->quoteName('#__beseated_airport_codes'));


		$query = "SELECT REPLACE(REPLACE(airport,'\'','`'),1,'true') AS hide FROM #__beseated_airport_codes";

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadColumn();

			//echo "<pre/>";print_r($result);exit;
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		return $result;
	}

	public function bookJetService($data = array())
	{
		$tblJetServiceBooking = $this->getTable();
		$tblJetServiceBooking->load(0);
		$tblJetServiceBooking->bind($data);
		if(!$tblJetServiceBooking->store())
		{
			return 0;
		}

		return 1;
	}
}
