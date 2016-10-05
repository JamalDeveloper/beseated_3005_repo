<?php
/**
 * @package     BigSubscription.Administrator
 * @subpackage  com_bigsubscription
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * BigSubscription mailtype Form Field class for the BigSubscription component
 *
 * @since  0.0.1
 */
class JFormFieldVenuelistguest extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 */
	protected $type = 'venuelistguest';

	protected $messages = null;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 */
	protected function getOptions()
	{
		$this->getVenueList();

		$options  = array();
		$venuelists = $this->venuelists;


		if ($venuelists)
		{
			foreach ($venuelists as $venuelist)
			{
				$options[] = JHtml::_('select.option', $venuelist->venue_id, $venuelist->venue_name);
			}
		}
		//echo "<pre/>";print_r($options);exit;
		return $options;
	}

	/**
	 * Get list of mail's type to display
	 *
	 * @return  [type]  [description]
	 */
	protected function getVenueList()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.venue_id,a.venue_name')
			  ->from($db->quoteName('#__beseated_venue') . ' AS a ')
			  ->where($db->quoteName('has_guestlist') . ' = ' . $db->quote('1'))
			  ->group('a.venue_id')
			  ->join('INNER', '#__beseated_venue_table AS b ON b.venue_id=a.venue_id');

		// Set the query and load the result.
		$db->setQuery((string) $query);

		try
		{
			$this->venuelists = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}
	}
}