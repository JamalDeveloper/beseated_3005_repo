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
class BeseatedControllerGuestlistBooking extends JControllerForm
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

	public function cancel()
	{
		$this->setRedirect('index.php?option=com_beseated&view=guestlistRequests');
	}

	public function checkForVenueClosed()
	{
		$app         = JFactory::getApplication();
		$input       = $app->input;
		//$user_id     = $input->get('user_id', '', 'string');
		$venueID     = $input->get('venue_id', 0, 'int');
		$bookingDate = $input->get('booking_date', '', 'string');

		$day = date('D',strtotime($bookingDate));

		$daysArray = array('1' => 'Mon','2' => 'Tue','3' => 'Wed','4' => 'Thu','5' => 'Fri','6' => 'Sat','7' => 'Sun');

		$day = array_search($day, $daysArray);

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('working_days')
			->from($db->quoteName('#__beseated_venue'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID));

		// Set the query and load the result.
		$db->setQuery($query);
		$working_days = $db->loadResult();

		$working_days = explode(',', $working_days);

		if(in_array($day, $working_days))
		{
			echo 0;
		}
		else
		{
			echo 1;
		}

		exit;
	}


}
