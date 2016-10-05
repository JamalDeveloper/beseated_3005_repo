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
 * Beseated Event View
 *
 * @since  0.0.1
 */
class BeseatedViewYachtBooking extends JViewLegacy
{
	/**
	 * View Event
	 *
	 * @var   form
	 */
	protected $form = null;

	/**
	 * Display the Beseated Event view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   0.0.1
	 */
	public function display($tpl = null)
	{
		// Get the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		//$this->eventTickets = $this->get('EventTickets');

		$app = JFactory::getApplication();
		$input = $app->input;


		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new RuntimeException(implode('<br />', $errors), 500);

			return false;
		}

		// Set the toolbar
		$this->addToolBar();

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
		$input = JFactory::getApplication()->input;

		// Hide Joomla Administrator Main menu
		$input->set('hidemainmenu', true);

		$isNew = ($this->item->yacht_booking_id == 0);

		if ($isNew)
		{
			$title = JText::_('COM_BESEATED_MANAGE_YACHT_BOOKINGS');
		}
		else
		{
			$title = JText::_('COM_BESEATED_MANAGE_YACHT_BOOKINGS');
		}

		JToolBarHelper::title($title, 'Yacht Booking');
		JToolBarHelper::save('yachtbooking.save');
		//JToolBarHelper::apply('event.apply');
		JToolBarHelper::cancel(
			'yachtbooking.cancel',
			$isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
		);
	}

}
