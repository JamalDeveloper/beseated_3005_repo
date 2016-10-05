<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Beseated
 * @author     jamal <derdiwalanawaz@gmail.com>
 * @copyright  Copyright (C) 2015. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Beseated.
 *
 * @since  1.6
 */
class BeseatedViewTableTypes extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		require JPATH_ADMINISTRATOR.'/components/com_beseated/helpers/beseated.php';

		BeseatedHelper::addSubmenu('tabletypes');

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		//BeseatedHelper::addSubmenu('rewards');

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		$title = JText::_('COM_BESEATED_MANAGE_VENUE_TABLE_TYPES');

		if ($this->pagination->total)
		{
			$title .= "<span style='font-size: 0.5em; vertical-align: middle;'>(" . $this->pagination->total . ")</span>";
		}

		JToolBarHelper::title($title, 'tabletypes');
		JToolBarHelper::addNew('tabletype.add');
		JToolBarHelper::editList('tabletype.edit');
		JToolBarHelper::deleteList('','tabletypes.delete');
	//	JToolBarHelper::trash('tabletypes.trash','JTOOLBAR_TRASH');
		JToolbarHelper::preferences('com_beseated');

	}

	/**
	 * Method to order fields
	 *
	 * @return void
	 */
	protected function getSortFields()
	{
		return array(
			'a.`table_type_id`' => JText::_('COM_BESEATED_VENUE_TABLE_TYPE_ID'),
			'a.`table_type_name`' => JText::_('COM_BESEATED_VENUE_TABLE_TYPE_NAME'),
		);
	}
}
