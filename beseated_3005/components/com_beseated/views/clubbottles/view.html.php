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
 * The Beseated Club Tables View
 *
 * @since  0.0.1
 */
class BeseatedViewClubBottles extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;
	/**
	 * Display the Beseated Club Tables view
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
		$input             = JFactory::getApplication()->input;
		$tableID           = $input->getInt('table_id');
		$this->tableDetail = BeseatedHelper::getVenueTableDetail($tableID);
		$this->items       = $this->get('VenueBottles');
		$this->tableBooked = $this->get('bookedTable');


		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		// Display the template
		parent::display($tpl);
	}
}
