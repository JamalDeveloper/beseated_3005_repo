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
 * The Beseated Profile Model
 *
 * @since  0.0.1
 */
class BeseatedModelEventtickettypes extends JModelList
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

	function getTicketTypesDetail()
	{
		$app          = JFactory::getApplication();
		$eventID      = $app->input->getInt('event_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		// Create the base select statement.
		$query->select('a.*,e.image as event_image,e.event_name,e.currency_sign,e.currency_code')
			->from($db->quoteName('#__beseated_event_ticket_type_zone').' AS a')
			->where($db->quoteName('a.event_id') . ' = ' . $db->quote($eventID))
			->where($db->quoteName('a.is_deleted') . ' = ' . $db->quote('0'))
			->join('LEFT','#__beseated_event AS e ON e.event_id=a.event_id')
			->order($db->quoteName('a.available_tickets') . ' DESC');
		
		// Set the query and load the result.
		$db->setQuery($query);
		
		$ticketTypes = $db->loadObjectList();

		foreach ($ticketTypes as $key => $ticketType)
		{
			$ticketType->image           = ($ticketType->ticket_type_image)?JUri::root().'images/beseated/'.$ticketType->ticket_type_image:JUri::root().'images/beseated/'.$ticketType->event_image;
			
		}

		return $ticketTypes;

	}

}
