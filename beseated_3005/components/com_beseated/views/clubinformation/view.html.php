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
 * The Beseated Club Information View
 *
 * @since  0.0.1
 */
class BeseatedViewClubInformation extends JViewLegacy
{
	protected $club;
	protected $isFavourite;
	protected $user;

	/**
	 * Display the Beseated Club Information view
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
		// Get data from the model
		$this->club   = $this->get('VenueDetail');
		$this->images = $this->get('VenueImages');
		$this->user   = JFactory::getUser();

		if($this->user->id)
		{
			$this->isFavourite = BeseatedHelper::isFavouriteVenue($this->club->venue_id,$this->user->id);
		}
		else
		{
			$this->isFavourite = 0;
		}

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
