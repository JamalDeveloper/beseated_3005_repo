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
		$this->user           = JFactory::getUser();
		$this->pagination     = $this->get('Pagination');
		if(!$this->user->id)
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?option=com_users&view=login');
		}

		if($this->loyaltyHistory)
		{
			foreach ($this->loyaltyHistory as $key => $loyalty)
			{
				if($loyalty->point_app == 'purchase.venue' || $loyalty->point_app == 'venue.noshow' || $loyalty->point_app == 'venue.refund' || $loyalty->point_app == 'payout.venue' || $loyalty->point_app == 'venue.cancelbooking')
                {
                	if(!in_array($loyalty->cid, $this->usedPaymentStatus))
                	{
                		$tblPaymentStatus = JTable::getInstance('PaymentStatus', 'BctedTable');
						$tblPaymentStatus->load($loyalty->cid);
						$tblVenueBooking  = JTable::getInstance('Venuebooking', 'BctedTable');
						$tblVenueBooking->load($tblPaymentStatus->booked_element_id);
						if(!in_array($tblVenueBooking->venue_id, $this->usedVenues))
						{
							$tblVenue    = JTable::getInstance('Venue', 'BctedTable');
							$tblVenue->load($tblVenueBooking->venue_id);
							$this->dispayAppOptions[$loyalty->lp_id] = $tblVenue->venue_name;
							$this->usedVenues[] = $tblVenue->venue_id;
							$this->usedPaymentStatus[] = $tblPaymentStatus->payment_id;
							$this->coreVenues[$tblVenue->venue_id] = $tblVenue->venue_name;
							$this->corePaymentVenues['purchase.venue'][$loyalty->cid] = $tblVenue->venue_name;
						}
						else
						{
							$this->dispayAppOptions[$loyalty->lp_id] = $this->coreVenues[$tblVenueBooking->venue_id];
						} // End of usedVenues
                	}
                	else
                	{
                		$this->dispayAppOptions[$loyalty->lp_id] = $this->corePaymentVenues['purchase.venue'][$loyalty->cid];
                	} // End of usedPaymentStatus
                } // End of purchase.venue
                else if($loyalty->point_app == 'purchase.service' || $loyalty->point_app == 'service.refund' || $loyalty->point_app == 'payout.service' || $loyalty->point_app == 'service.cancelbooking')
                {
                	if(!in_array($loyalty->cid, $this->usedPaymentStatus))
                	{
                		$tblPaymentStatus = JTable::getInstance('PaymentStatus', 'BctedTable');
						$tblPaymentStatus->load($loyalty->cid);
						$tblServicebooking  = JTable::getInstance('Servicebooking', 'BctedTable');
						$tblServicebooking->load($tblPaymentStatus->booked_element_id);
						if(!in_array($tblServicebooking->company_id, $this->usedCompanies))
						{
							$tblCompany    = JTable::getInstance('Company', 'BctedTable');
							$tblCompany->load($tblServicebooking->company_id);
							$this->dispayAppOptions[$loyalty->lp_id] = $tblCompany->company_name;
							$this->usedCompanies[] = $tblCompany->venue_id;
							$this->usedPaymentStatus[] = $tblPaymentStatus->payment_id;
							$this->coreCompanies[$tblServicebooking->company_id] = $tblCompany->company_name;
							$this->corePaymentCompanies['purchase.service'][$loyalty->cid] = $tblCompany->company_name;
						}
						else
						{
							$this->dispayAppOptions[$loyalty->lp_id] = $this->coreCompanies[$tblServicebooking->company_id];
						}// End of usedCompanies
                	}
                	else
                	{
                		$this->dispayAppOptions[$loyalty->lp_id] = $this->corePaymentCompanies['purchase.service'][$loyalty->cid];
                	} // End of usedPaymentStatus
                } // End of purchase.service
                else if($loyalty->point_app == 'purchase.package' || $loyalty->point_app == 'package.refund')
                {
                	if(!in_array($loyalty->cid, $this->usedPaymentStatus))
                	{
                		$tblPaymentStatus = JTable::getInstance('PaymentStatus', 'BctedTable');
						$tblPaymentStatus->load($loyalty->cid);
						$tblPackagePurchased  = JTable::getInstance('PackagePurchased', 'BctedTable');
						$tblPackagePurchased->load($tblPaymentStatus->booked_element_id);
						if(!in_array($tblPackagePurchased->package_id, $this->usedPackages))
						{
							$tblPackage    = JTable::getInstance('Package', 'BctedTable');
							$tblPackage->load($tblPackagePurchased->package_id);
							$this->dispayAppOptions[$loyalty->lp_id] = $tblPackage->package_name;
							$this->usedPackages[] = $tblPackage->package_id;
							$this->usedPaymentStatus[] = $tblPaymentStatus->payment_id;
							$this->corePackages[$tblPackage->package_id] = $tblPackage->package_name;
							$this->corePaymentPackage['purchase.package'][$loyalty->cid] = $tblPackage->package_name;
						}
						else
						{
							$this->dispayAppOptions[$loyalty->lp_id] = $this->corePackages[$tblPackagePurchased->package_id];
						}// End of usedCompanies
                	}
                	else
                	{
                		$this->dispayAppOptions[$loyalty->lp_id] = $this->corePaymentPackage['purchase.package'][$loyalty->cid];
                	} // End of usedPaymentStatus
                } // End of purchase.package
                else if($loyalty->point_app == 'purchase.packageinvitation' || $loyalty->point_app == 'packageinvitation.refund')
                {
                	if(!in_array($loyalty->cid, $this->usedPaymentStatus))
                	{
                		$tblPaymentStatus = JTable::getInstance('PaymentStatus', 'BctedTable');
						$tblPaymentStatus->load($loyalty->cid);
						$tblPackageInvite = JTable::getInstance('PackageInvite', 'BctedTable');
						$tblPackageInvite->load($tblPaymentStatus->booked_element_id);
						if(!in_array($tblPackageInvite->package_id, $this->usedPackages))
						{
							$tblPackage    = JTable::getInstance('Package', 'BctedTable');
							$tblPackage->load($tblPackageInvite->package_id);
							$this->dispayAppOptions[$loyalty->lp_id] = $tblPackage->package_name;
							$this->usedPackages[] = $tblPackage->package_id;
							$this->usedPaymentStatus[] = $tblPaymentStatus->payment_id;
							$this->corePackages[$tblPackage->package_id] = $tblPackage->package_name;
							$this->corePaymentPackage['purchase.packageinvitation'][$loyalty->cid] = $tblPackage->package_name;
						}
						else
						{
							$this->dispayAppOptions[$loyalty->lp_id] = $this->corePackages[$tblPackageInvite->package_id];
						}// End of usedCompanies
                	}
                	else
                	{
                		$this->dispayAppOptions[$loyalty->lp_id] = $this->corePaymentPackage['purchase.packageinvitation'][$loyalty->cid];
                	} // End of usedPaymentStatus
                } // End of purchase.packageinvitation
                else if ($loyalty->point_app == 'inviteduserfp')
                {
                	$tblRefer = JTable::getInstance('Refer', 'BctedTable');
                	$tblRefer->load($loyalty->cid);
                	$referUser = BctedHelper::getBeseatedUserProfile($tblRefer->ref_user_id);
                	$this->dispayAppOptions[$loyalty->lp_id] = $referUser->first_name.' '.$referUser->last_name;
                }
			}
		}

		$this->loyaltyPoints = $this->get('TotalPoints');
		$this->userType = BeseatedHelper::getUserType($this->user->id);

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
