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
 * The Beseated User Table Booking Detail View
 *
 * @since  0.0.1
 */
class BctedViewUserTableBookingDetail extends JViewLegacy
{
	protected $booking;

	protected $user;

	protected $pagination;

	protected $state;

	protected $bookingType;

	protected $bctConfig;

	protected $loyaltyPoints;

	protected $currencyRateUSD = 0;
	/**
	 * Display the Lgom view
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

		$app                 = JFactory::getApplication();
		$input               = $app->input;
		$this->bookingType   = $input->get('booking_type','','string');
		$this->booking       = $this->get('ClubBooking');
		$this->bctConfig     = BctedHelper::getExtensionParam_2();
		$this->loyaltyPoints = BctedHelper::getLoyaltyPointsOfUser($this->user->id);
		$convertExp          = "";
		if($this->bookingType=='package')
		{
			$elemntCurrencyCode =  $this->booking->package_currency_code;
		}
		else
		{
			$elemntCurrencyCode =  $this->booking->currency_code;
		}

		if($elemntCurrencyCode!="USD")
		{
			$convertExp = $elemntCurrencyCode."_USD";
		}

		$this->currencyRateUSD = 0;
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
