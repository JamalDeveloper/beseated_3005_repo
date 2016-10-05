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
 * The Beseated Club Bookings View
 *
 * @since  0.0.1
 */
class BeseatedViewYachtBookings extends JViewLegacy
{
	protected $items;

	protected $user;

	protected $pagination;

	protected $state;
	/**
	 * Display the Beseated Club Bookings view
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

		$this->bookings = $this->get('YachtBookings');

		/*$this->packages = $this->get('ClubPackageBooking');*/
		$model          = $this->getModel();
		$user           = JFactory::getUser();
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		parent::display($tpl);
	}

}
