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
 * The Besated User Booking Detail View
 *
 * @since  0.0.1
 */
class BeseatedViewYachtshareinvitation extends JViewLegacy
{
	protected $booking;

	protected $user;

	protected $pagination;

	protected $state;

	protected $bookingType;

	protected $bctConfig;

	protected $loyaltyPoints;

	protected $currencyRateUSD = 0;

	protected $facebook_friends;
	/**
	 * Display the Besated User Booking Detail view
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
		$this->user           = JFactory::getUser();
		$app                  = JFactory::getApplication();
		$input                = $app->input;

		$this->yachtBookingDetail = $this->get('YachtBookingDetail');

		// Display the template
		parent::display($tpl);
	}
}
