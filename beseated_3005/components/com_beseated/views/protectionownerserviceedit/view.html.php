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
 * The Beseated Club Owner Table Edit View
 *
 * @since  0.0.1
 */
class BeseatedViewProtectionownerServiceEdit extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $companyProfile;

	protected $premiumTable;

	/**
	 * Display the Beseated Club Owner Table Edit view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   0.0.1
	 */
	public function display($tpl = null)
	{
		// Get the Data
		$this->form           = $this->get('Form');
		$this->item           = $this->get('Item');
		$user                 = JFactory::getUser();
		$this->companyProfile = BeseatedHelper::getUserElementID($user->id);

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
