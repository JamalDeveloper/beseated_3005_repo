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
 * The Beseated User Profile View
 *
 * @since  0.0.1
 */
class BeseatedViewUserProfile extends JViewLegacy
{
	protected $user;

	protected $beseatedProfile;
	/**
	 * Display the Beseated User Profile view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   0.0.1
	 */
	public function display($tpl = null)
	{
		$app        = JFactory::getApplication();
		$this->user = JFactory::getUser();
		$userType   = BeseatedHelper::getUserType($this->user->id);
		if($userType == "Venue")
		{
			$profileMenu = BeseatedHelper::getBctedMenuItem('club-profile');
			$link        = $profileMenu->link."&Itemid=".$profileMenu->id;
			$app->redirect($link);
		}
		elseif($userType == "Chauffeur" || $userType == "Protection" || $userType == "Yacht")
		{
			$profileMenu = BeseatedHelper::getBctedMenuItem('company-profile');

			$link = $profileMenu->link."&Itemid=".$profileMenu->id;
			$app->redirect($link);
		}

		// Get the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		$this->bctedProfile = BeseatedHelper::getUserElementID($this->user->id);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new RuntimeException(implode('<br />', $errors), 500);

			return false;
		}
		// Display the template
		parent::display($tpl);
	}
}
