<?php
/**
 * @package     Bcted.Administrator
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Bcted Companies View
 *
 * @since  0.0.1
 */
class BeseatedViewGuests extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $model;
	/**
	 * Display the Companies view
	 *
	 * @param   string  $tpl  The name of the template file to parse;
	 * automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   0.0.1
	 */
	function display($tpl = null)
	{
		require JPATH_ADMINISTRATOR.'/components/com_beseated/helpers/beseated.php';

		BeseatedHelper::addSubmenu('guests');

		// Get data from the model
		//$this->items         = $this->get('Items');


		$this->model = $this->getModel();
		$this->state         = $this->get('State');

		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$this->items         = $this->get('GuestListDetail');
		$this->pagination    = $this->get('Pagination');


		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

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
		$title = JText::_('COM_BESEATED_MANAGE_GUESTS');

		if ($this->pagination->total)
		{
			$title .= "";
		}

		JToolBarHelper::title($title, 'Beseated Guests');

		//JToolBarHelper::editList('company.edit');
		//JToolbarHelper::trash('companies.trash');
		//JToolBarHelper::deleteList('', 'Backevents.delete');
		//JToolbarHelper::custom('companies.featured', 'featured.png', 'featured_f2.png', 'JFEATURED', true);

	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.published' => JText::_('JSTATUS'),
			'a.message'     => JText::_('JGLOBAL_TITLE'),
			'a.company_id'        => JText::_('JGRID_HEADING_ID')
		);
	}
}
