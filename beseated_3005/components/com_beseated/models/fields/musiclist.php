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
class JFormFieldMusiclist extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 */
	protected $type = 'musiclist';

	protected $messages = null;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 */
	protected function getOptions()
	{
		$this->getChauffeurList();

		$options  = array();
		$musiclists = $this->musiclists;


		if ($musiclists)
		{
			foreach ($musiclists as $musiclist)
			{
				$options[] = JHtml::_('select.option', $musiclist->music_id, $musiclist->music_name);
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
	protected function getMusicList()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			  ->from($db->quoteName('#__beseated_venue_music_table'))
			  ->where($db->quoteName('a.published') . ' = ' . $db->quote('1'));

		// Set the query and load the result.
		$db->setQuery((string) $query);

		try
		{
			$this->musiclists = $db->loadObjectList();

		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}
	}
}