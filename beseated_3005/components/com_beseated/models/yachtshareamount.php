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
 * The Beseated Messages Model
 *
 * @since  0.0.1
 */
class BeseatedModelYachtshareamount extends JModelList
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

	public function getYachtBookingDetail()
	{
		$input         = JFactory::getApplication()->input;
		$bookingType   = $input->get('booking_type', '', 'string');
		$bookingID     = $input->get('booking_id', 0, 'int');

		JTable::addIncludePath(JPATH_SITE . '/administrator/components/com_beseated/tables');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_yacht_booking'))
			->where($db->quoteName('yacht_booking_id') . ' = ' . $db->quote($bookingID));
		
		// Set the query and load the result.
		$db->setQuery($query);
		
		$yachtBookingDetail = $db->loadObject();

		return $yachtBookingDetail;

	}
}
