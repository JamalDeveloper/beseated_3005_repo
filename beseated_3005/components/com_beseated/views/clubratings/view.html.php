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
 * The Beseated Club Ratings View
 *
 * @since  0.0.1
 */
class BeseatedViewClubRatings extends JViewLegacy
{
	protected $items;

	protected $club;

	protected $pagination;

	protected $state;
	/**
	 * Display the Beseated Club Ratings view
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
		$app         = JFactory::getApplication();
		$this->items = $this->get('VenueRatings');
		$clubID      = $app->input->get('club_id',0,'int');
		$this->club  = JTable::getInstance('Venue', 'BeseatedTable',array());
		$this->club->load($clubID);

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
