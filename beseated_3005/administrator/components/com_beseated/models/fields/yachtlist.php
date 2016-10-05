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
class JFormFieldYachtlist extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 */
	protected $type = 'yachtlist';

	protected $messages = null;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 */
	protected function getOptions()
	{
		$this->getYachtList();

		$options  = array();
		$yachtlists = $this->yachtlists;

		if ($yachtlists)
		{
			foreach ($yachtlists as $yachtlist)
			{
				$options[] = JHtml::_('select.option', $yachtlist->yacht_id, $yachtlist->yacht_name);
			}
		}

		return $options;
	}

	/**
	 * Get list of mail's type to display
	 *
	 * @return  [type]  [description]
	 */
	protected function getYachtList()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.yacht_id,a.yacht_name')
			  ->from($db->quoteName('#__beseated_yacht') . ' AS a ')
			  ->where($db->quoteName('a.published').' = '.$db->quote('1'))
			  ->group('a.yacht_id')
			  ->join('INNER', '#__beseated_yacht_services AS b ON b.yacht_id=a.yacht_id');

		// Set the query and load the result.
		$db->setQuery((string) $query);

		try
		{
			$this->yachtlists = $db->loadObjectList();

		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}
	}
}