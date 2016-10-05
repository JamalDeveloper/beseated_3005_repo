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
 * The Beseated User Bookings View
 *
 * @since  0.0.1
 */
class BeseatedViewUserBookings extends JViewLegacy
{
	protected $items;

	protected $user;

	protected $pagination;

	protected $state;
	/**
	 * Display the Beseated User Bookings view
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
		$model                = $this->getModel();

		$this->bookings           = $this->get('VenueBooking');
		$this->chauffeurBookings  = $this->get('ChauffeurBookings');
		$this->protectionBookings = $this->get('ProtectionBookings');
		$this->yachtBookings      = $this->get('YachtBookings');
		$this->eventBookings      = $this->get('EventBookings');
		//$this->ticketImages       = $model->InvitationTicketBookingDetails();

		$this->user               = JFactory::getUser();
        if(!$this->user->id)
        {
              JFactory::getApplication()->redirect(JRoute::_(JURI::root().'index.php?option=com_users&view=login'));
        }

		$model = $this->getModel();
		$user = JFactory::getUser();

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
