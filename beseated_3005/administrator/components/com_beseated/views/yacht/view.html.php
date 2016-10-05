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
 * Beseated Yacht View
 *
 * @since  0.0.1
 */
class BeseatedViewYacht extends JViewLegacy
{
	/**
	 * View Yacht
	 *
	 * @var   form
	 */
	protected $form = null;

	/**
	 * Display the Beseated Yacht view
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

		$isNew = ($this->item->yacht_id == 0);

		if ($isNew)
		{
			$title = JText::_('COM_BESEATED_MANAGE_YACHT_NEW');
		}
		else
		{
			$title = JText::_('COM_BESEATED_MANAGE_YACHT_EDIT');
		}

		JToolBarHelper::title($title, 'Yacht');
		JToolBarHelper::save('yacht.save');
		JToolBarHelper::cancel(
			'yacht.cancel',
			$isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
		);


	}
}
