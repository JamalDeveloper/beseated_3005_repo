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
class BeseatedViewClubOwnerTableEdit extends JViewLegacy
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
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$user       = JFactory::getUser();
		$this->companyProfile = BeseatedHelper::getUserElementID($user->id);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new RuntimeException(implode('<br />', $errors), 500);

			return false;
		}

		$this->premiumTable = $this->getPremiumTable();

		// Display the template
		parent::display($tpl);
	}

	public function getPremiumTable()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_premium_table'))
			->where($db->quoteName('premium_table_name') . ' = ' . $db->quote(' '))
			->where($db->quoteName('published') . ' = ' . $db->quote('0'));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}
}
