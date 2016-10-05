<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * The Beseated Loyalty View
 *
 * @since  0.0.1
 */
class BeseatedViewLoyalty extends JViewLegacy
{
	protected $loyaltyPoints;

	protected $loyaltyHistory;

	protected $user;

	protected $userType;

	protected $pagination;

	protected $usedVenues        = array();
	protected $coreVenues        = array();
	protected $corePaymentVenues = array();

	protected $usedCompanies        = array();
	protected $coreCompanies        = array();
	protected $corePaymentCompanies = array();

	protected $usedPackages        = array();
	protected $corePackages        = array();
	protected $corePaymentPackages = array();

	protected $usedPaymentStatus = array();
	protected $dispayAppOptions  = array();

	/**
	 * Display the Beseated Loyalty view
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
		$this->loyaltyHistory = $this->get('Items');
		$this->rewards        = $this->get('Rewards');
		$this->user           = JFactory::getUser();
		$this->pagination     = $this->get('Pagination');
		$totalLoyalty         = 0;
		if(!$this->user->id)
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?option=com_users&view=login');
		}

		if($this->loyaltyHistory)
		{
			foreach ($this->loyaltyHistory as $key => $loyalty)
			{
				$tempLoyalty          = array();
				$tempLoyalty['money'] = BeseatedHelper::currencyFormat('','',$loyalty->money_usd);
				$tempLoyalty['point'] = str_replace('-', '',BeseatedHelper::currencyFormat('','',$loyalty->earn_point,2));
				$totalLoyalty         = $totalLoyalty + $loyalty->earn_point;

				if($loyalty->point_app == 'purchase.protection' || $loyalty->point_app == 'purchase.splited.protection')
				{
					$tblPayment    = JTable::getInstance('Payment','BeseatedTable');
					$tblPayment->load($loyalty->cid);
					$bookingType   = 'ProtectionBooking';
					$elementName   = strtolower($tblPayment->booking_type) .'_name';
					$bookingIDType = 'protection_id';

					if($loyalty->point_app == 'purchase.protection')
					{
						$tblElementBooking   = JTable::getInstance($bookingType, 'BeseatedTable');
						$tblElementBooking->load($tblPayment->booking_id);

					}
					else
					{
						$tblElementSplit   = JTable::getInstance('ProtectionBookingSplit', 'BeseatedTable');
						$tblElementSplit->load($tblPayment->booking_id);
						$tblPayment->booking_id = $tblElementSplit->protection_booking_id;
						$tblElementBooking   = JTable::getInstance($bookingType, 'BeseatedTable');
					}
					$tblElementBooking->load($tblPayment->booking_id);
					$tblElement          = JTable::getInstance('Protection', 'BeseatedTable');
					$tblElement->load($tblElementBooking->$bookingIDType);
					$appType             = 'PURCHASE - ' . strtoupper($tblElement->protection_name);
					$tempLoyalty['date'] = BeseatedHelper::convertDateFormat($tblElementBooking->booking_date);
				}
				else if($loyalty->point_app == 'purchase.venue' || $loyalty->point_app == 'purchase.splited.venue')
				{
					$tblPayment          = JTable::getInstance('Payment','BeseatedTable');
					$tblPayment->load($loyalty->cid);
					$bookingType         = 'VenueBooking';
					$elementName         = strtolower($tblPayment->booking_type) .'_table_name';
					$bookingIDType = 'venue_id';

					if($loyalty->point_app == 'purchase.venue')
					{
						/*echo $bookingType;
						exit;*/
						$tblElementBooking   = JTable::getInstance($bookingType, 'BeseatedTable');
						$tblElementBooking->load($tblPayment->booking_id);

					}
					else
					{
						$tblElementSplit   = JTable::getInstance('VenueBookingSplit', 'BeseatedTable');
						$tblElementSplit->load($tblPayment->booking_id);
						$tblPayment->booking_id = $tblElementSplit->venue_table_booking_id;
						$tblElementBooking   = JTable::getInstance($bookingType, 'BeseatedTable');
					}

					$tblElementBooking->load($tblPayment->booking_id);
					$tblElement          = JTable::getInstance('Venue', 'BeseatedTable');
					$tblElement->load($tblElementBooking->$bookingIDType);
					$appType             = 'PURCHASE - ' . strtoupper($tblElement->venue_name);
					$tempLoyalty['date'] = BeseatedHelper::convertDateFormat($tblElementBooking->booking_date);
				}
				else if($loyalty->point_app == 'purchase.yacht' || $loyalty->point_app == 'purchase.splited.yacht')
				{
					$tblPayment          = JTable::getInstance('Payment','BeseatedTable');
					$tblPayment->load($loyalty->cid);
					$bookingType         = 'yachtBooking';
					$elementName         = strtolower($tblPayment->booking_type) .'_table_name';

					$bookingIDType       = 'yacht_id';

					if($loyalty->point_app == 'purchase.yacht')
					{
						/*echo $bookingType;
						exit;*/
						$tblElementBooking   = JTable::getInstance($bookingType, 'BeseatedTable');
						$tblElementBooking->load($tblPayment->booking_id);

					}
					else
					{
						$tblElementSplit   = JTable::getInstance('YachtBookingSplit', 'BeseatedTable');
						$tblElementSplit->load($tblPayment->booking_id);
						$tblPayment->booking_id = $tblElementSplit->yacht_table_booking_id;
						$tblElementBooking   = JTable::getInstance($bookingType, 'BeseatedTable');
					}

					$tblElementBooking->load($tblPayment->booking_id);
					$tblElement          = JTable::getInstance('Yacht', 'BeseatedTable');
					$tblElement->load($tblElementBooking->$bookingIDType);
					$appType             = 'PURCHASE - ' . strtoupper($tblElement->yacht_name);
					$tempLoyalty['date'] = BeseatedHelper::convertDateFormat($tblElementBooking->booking_date);
				}
				else if($loyalty->point_app == 'purchase.chauffeur' || $loyalty->point_app == 'purchase.splited.chauffeur')
				{
					$tblPayment          = JTable::getInstance('Payment','BeseatedTable');
					$tblPayment->load($loyalty->cid);
					$bookingType         = 'chauffeurBooking';
					$elementName         = strtolower($tblPayment->booking_type) .'_table_name';

					$bookingIDType       = 'chauffeur_id';

					if($loyalty->point_app == 'purchase.chauffeur')
					{
						/*echo $bookingType;
						exit;*/
						$tblElementBooking   = JTable::getInstance($bookingType, 'BeseatedTable');
						$tblElementBooking->load($tblPayment->booking_id);

					}
					else
					{
						$tblElementSplit   = JTable::getInstance('ChauffeurBookingSplit', 'BeseatedTable');
						$tblElementSplit->load($tblPayment->booking_id);
						$tblPayment->booking_id = $tblElementSplit->chauffeur_table_booking_id;
						$tblElementBooking   = JTable::getInstance($bookingType, 'BeseatedTable');
					}

					$tblElementBooking->load($tblPayment->booking_id);
					$tblElement          = JTable::getInstance('Chauffeur', 'BeseatedTable');
					$tblElement->load($tblElementBooking->$bookingIDType);
					$appType             = 'PURCHASE - ' . strtoupper($tblElement->chauffeur_name);
					$tempLoyalty['date'] = BeseatedHelper::convertDateFormat($tblElementBooking->booking_date);
				}
				else if($loyalty->point_app == 'purchase.event')
				{
					$tblPayment          = JTable::getInstance('Payment','BeseatedTable');
					$tblPayment->load($loyalty->cid);
					$bookingType         = 'TicketBooking';
					//$elementName         = strtolower($tblPayment->booking_type) .'_table_name';

					$bookingIDType       = 'event_id';

					if($loyalty->point_app == 'purchase.event')
					{
						/*echo $bookingType;
						exit;*/
						$tblElementBooking   = JTable::getInstance($bookingType, 'BeseatedTable');
						$tblElementBooking->load($tblPayment->booking_id);

					}

					$tblElementBooking->load($tblPayment->booking_id);
					$tblElement          = JTable::getInstance('Event', 'BeseatedTable');
					$tblElement->load($tblElementBooking->$bookingIDType);
					$appType             = 'PURCHASE TICKETS - ' . strtoupper($tblElement->event_name);
					$tempLoyalty['date'] = BeseatedHelper::convertDateFormat($tblElementBooking->created);
				}
				else if($loyalty->point_app == 'call.concierge')
				{
					$appType             = 'CONCIERGE CALL';
					$tempLoyalty['date'] = BeseatedHelper::convertDateFormat($loyalty->created);
				}
				else if($loyalty->point_app == 'purchase.reward')
				{
					$tblRewards         = JTable::getInstance('Rewards', 'BeseatedTable');
					$tblRewards->load($loyalty->cid);

					$tempLoyalty['date'] = BeseatedHelper::convertDateFormat($loyalty->created);

					$appType             = 'PURCHASE REWARDS - '.strtolower($tblRewards->reward_name);
				}
				else if($loyalty->point_app == 'inviteduserfp')
				{
					//$tblRewards         = JTable::getInstance('Rewards', 'BeseatedTable');
					//$tblRewards->load($loyalty->cid);
					$tempLoyalty['date'] = BeseatedHelper::convertDateFormat($loyalty->created);
					$appType             = 'REFER A FRIEND';
				}
				else if($loyalty->point_app == 'admin.removed')
				{
					$tempLoyalty['date'] = BeseatedHelper::convertDateFormat($loyalty->created);
					$appType             = 'DEDUCTED BY ADMIN';
				}
				else if($loyalty->point_app == 'admin.added')
				{
					$tempLoyalty['date'] = BeseatedHelper::convertDateFormat($loyalty->created);
					$appType             = 'ADDED BY ADMIN';
				}

				$tempLoyalty['date'] = BeseatedHelper::convertDateFormat($loyalty->created);

				$tempLoyalty['type'] = $appType;
				$loyaltyResult[]     = $tempLoyalty;
			}
		}

		$this->totalLoyalty = BeseatedHelper::currencyFormat('','',$totalLoyalty);
		$this->loyalty      = $loyaltyResult;
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
