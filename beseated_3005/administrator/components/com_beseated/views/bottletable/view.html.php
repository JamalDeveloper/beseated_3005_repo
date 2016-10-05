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
 * Beseated Premium Table View
 *
 * @since  0.0.1
 */
class BeseatedViewBottleTable extends JViewLegacy
{
	/**
	 * View Premium Table
	 *
	 * @var   form
	 */
	protected $form = null;

	/**
	 * Display the Beseated Premium Table view
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

		$isNew = ($this->item->bottle_type_id == 0);

		if ($isNew)
		{
			$title = JText::_('COM_BESEATED_MANAGE_BOTTLE_TABLE_NEW');
		}
		else
		{
			$title = JText::_('COM_BESEATED_MANAGE_BOTTLE_TABLE_EDIT');
		}

		JToolBarHelper::title($title, 'Bottle Table');
		JToolBarHelper::save('bottletable.save');
		JToolBarHelper::cancel(
			'bottletable.cancel',
			$isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
		);


	}
}
