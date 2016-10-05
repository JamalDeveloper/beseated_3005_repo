<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Beseate Premium Table Model
 *
 * @since  0.0.1
 */
class BeseatedModelMusicTable extends JModelAdmin
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   0.0.1
	 */
	public function getTable($type = 'MusicTable', $prefix = 'BeseatedTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   0.0.1
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_beseated.musictable',
			'musictable',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   0.0.1
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState(
			'com_beseated.edit.MusicTable.data',
			array()
		);

		$user = JFactory::getUser();

		if (empty($data))
		{
			$data = $this->getItem();


			$data->created=date('Y-m-d H:i:s');

		}

		return $data;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   0.0.1
	 */
	public function unpublished($ids)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$queryVenue = $db->getQuery(true);

		// Create the base update statement.
		$queryVenue->update($db->quoteName('#__beseated_venue_music_table'))
			->set($db->quoteName('published') . ' = ' . $db->quote(0))
			->where($db->quoteName('music_id') . ' IN (' . $ids.')');

		// Set the query and execute the update.
		$db->setQuery($queryVenue);
		$db->execute();

		return true;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   0.0.1
	 */
	public function published($ids)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$queryVenue = $db->getQuery(true);

		// Create the base update statement.
		$queryVenue->update($db->quoteName('#__beseated_venue_music_table'))
			->set($db->quoteName('published') . ' = ' . $db->quote(1))
			->where($db->quoteName('music_id') . ' IN (' . $ids.')');

		// Set the query and execute the update.
		$db->setQuery($queryVenue);
		$db->execute();

		return true;
	}
}
