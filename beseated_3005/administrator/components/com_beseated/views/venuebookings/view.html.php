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
 * Beseated Venue Bookings View
 *
 * @since  0.0.1
 */
class BeseatedViewVenueBookings extends JViewLegacy
{
	/**
	 * Display the Venue Bookings view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since  0.0.1
	 */
	function display($tpl = null)
	{
		require JPATH_ADMINISTRATOR.'/components/com_beseated/helpers/beseated.php';

		BeseatedHelper::addSubmenu('venuebookings');

		// Get data from the model
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('FilterForm');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode('<br />', $errors), 500);

			return false;
		}

		// Set the tool-bar and number of found items
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   0.0.1
	 */
	protected function addToolBar()
	{
		$title = JText::_('COM_BESEATED_MANAGE_VENUE_BOOKINGS');

		if ($this->pagination->total)
		{
			$title .= "<span style='font-size: 0.5em; vertical-align: middle;'>(" . $this->pagination->total . ")</span>";
		}

		JToolBarHelper::title($title, 'Venue Bookings');
		JToolBarHelper::addNew('venuebooking.add','Add Booking');
		JToolbarHelper::preferences('com_beseated');

	}
}