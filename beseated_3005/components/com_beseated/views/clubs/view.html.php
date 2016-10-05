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
 * The Beseated Clubs View
 *
 * @since  0.0.1
 */
class BeseatedViewClubs extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $serachType;

	protected $cityList;
	/**
	 * Display the Beseated Clubs view
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
		$this->serachType = '';
		$app              = JFactory::getApplication();
		$input            = $app->input;
		$clubSearch       = $input->get('club','club','string');
		$serviceSearch    = $input->get('service','','string');

		if($clubSearch == 'club')
		{
			$this->serachType = "club";
		}
		else if($serviceSearch == 'service')
		{
			$this->serachType = "service";
		}

		// Get data from the model
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->cityList   = $this->getCityList();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		// Display the template
		parent::display($tpl);
	}

	public function getCityList()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$queryVC = $db->getQuery(true);

		// Create the base select statement.
		/*$queryVC->select('LOWER(city)')
			->from($db->quoteName('#__bcted_company'))
			->where($db->quoteName('company_active') . ' = ' . $db->quote(1))
			->where($db->quoteName('city') . ' <> ' . $db->quote(''));

		// Set the query and load the result.
		$db->setQuery($queryVC);
		$venueCountry = $db->loadColumn();
		*/

		// Create the base select statement.
		$queryCC      = $db->getQuery(true);
		$queryCC->select('LOWER(city)')
			->from($db->quoteName('#__beseated_venue'))
			->where($db->quoteName('published') . ' = ' . $db->quote(1))
			->where($db->quoteName('city') . ' <> ' . $db->quote(''));

		// Set the query and load the result.
		$db->setQuery($queryCC);
		$companyCountry = $db->loadColumn();
		$country        = $companyCountry;

		return $country;
	}


}
