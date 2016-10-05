<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
jimport( 'joomla.application.component.helper' );
jimport('joomla.filesystem.folder');
/**
 * The Beseated Venue Model
 *
 * @since  0.0.1
 */
class BeseatedModelVenue extends JModelList
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
	public function getTable($type = 'Venue', $prefix = 'BctedTable', $config = array())
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
			'com_bcted.venue',
			'venue',
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
			'com_bcted.edit.Venue.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a list of articles.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function addtofavourite($venueID, $userID)
	{
		if($this->checkForAlreadyFavourite($venueID,'Venue',$userID))
		{
			return 2;
		}

		$tblFavourite         = JTable::getInstance('Favourite', 'BeseatedTable', array());
		$tblFavourite->load(0);
		$data                 = array();
		$data['element_type'] = 'Venue';
		$data['element_id']   = $venueID;
		$data['user_id']      = $userID;
		$data['created']      = date('Y-m-d h:i:s');
		$data['time_stamp']   = time();

		$tblFavourite->bind($data);
		if(!$tblFavourite->store())
		{
			return 0;
		}

		return 1;
	}

	public function removefromfavourite($venueID, $userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base delete statement.
		$query->delete()
			->from($db->quoteName('#__beseated_favourite'))
			->where($db->quoteName('element_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote('Venue'));

		// Set the query and execute the delete.
		$db->setQuery($query);
		$db->execute();

		return 1;
	}

	public function checkForAlreadyFavourite($elementID,$elementType,$userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('favourite_id')
			->from($db->quoteName('#__beseated_favourite'))
			->where($db->quoteName('favourite_id') . ' = ' . $db->quote($elementID))
			->where($db->quoteName('element_type') . ' = ' . $db->quote($elementType))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		return $db->loadResult();
	}
}
