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
class BeseatedViewPromotionMessage extends JViewLegacy
{
	/**
	 * Display the Events view
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

		BeseatedHelper::addSubmenu('promotionmessage');

		// Get data from the model
		//$this->items               = $this->get('Items');

		$this->state            = $this->get('State');
		$this->promotionDetails = $this->get('PromotionDetails');
		$this->filterForm       = $this->get('FilterForm');
		$this->pagination       = $this->get('Pagination');
		$this->activeFilters    = $this->get('ActiveFilters');


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
		$title = JText::_('COM_BESEATED_MANAGE_PROMOTION_MSG');

		if ($this->pagination->total)
		{
			$title .= "<span style='font-size: 0.5em; vertical-align: middle;'>(" . $this->pagination->total . ")</span>";
		}

		JToolBarHelper::title($title, 'PromotionMessage');
		//JToolBarHelper::addNew('concierge.add');
		//JToolBarHelper::editList('concierge.edit');
		//JToolBarHelper::deleteList('','concierges.delete');
		//JToolBarHelper::deleteList('','ticketbookings.delete');
		//JToolBarHelper::trash('concierge.trash','JTOOLBAR_TRASH');
		JToolbarHelper::preferences('com_beseated');

	}
}
