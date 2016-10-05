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
 * The Beseated Club Guest Requests By Date View
 *
 * @since  0.0.1
 */
class BeseatedViewClubGuestRequestsByDate extends JViewLegacy
{
	protected $user;

	protected $bookings;

	protected $state;

    protected $packageRequests;
	/**
	 * Display the Beseated Club Guest Requests By Date view
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
        $this->user = JFactory::getUser();

        if(!$this->user->id)
        {
              JFactory::getApplication()->redirect(JRoute::_(JURI::root().'index.php?option=com_users&view=login'));
        }

		// Get data from the model
		$this->bookings = $this->get('VenueGuestListRequestByDate');

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
