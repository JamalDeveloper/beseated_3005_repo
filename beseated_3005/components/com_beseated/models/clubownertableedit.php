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
 * The Beseated Club Owner Table Edit Message Model
 *
 * @since  0.0.1
 */
class BeseatedModelClubOwnerTableEdit extends JModelAdmin
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
	public function getTable($type = 'Table', $prefix = 'BeseatedTable', $config = array())
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
			'com_beseated.table',
			'table',
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
			'com_beseated.edit.Table.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function save($data,$tableID)
	{
		$tblTable = $this->getTable();
		$tblTable->load($tableID);

		if(isset($data['image']))
		{
			$newImage = $data['image'];
			if(!empty($newImage) && !empty($tblTable->image))
			{
				$oldImgPath      = JPATH_SITE.'/images/beseated/'.$tblTable->image;
				$oldThumbImgPath = JPATH_SITE.'/images/beseated/'.$tblTable->image;

				if(file_exists($oldImgPath))
				{
					@unlink($oldImgPath);
				}
				if(file_exists($oldThumbImgPath))
				{
					@unlink($oldThumbImgPath);
				}
			}
		}

		$tblTable->bind($data);
		if($tblTable->store())
		{
			if(!empty($tblTable->table_name))
			{

				$venueTable = JTable::getInstance('Venue', 'BeseatedTable');
				$venueTable->load($tblTable->venue_id);
				if ($tblTable->published == 1){
					$venueTable->has_table = 1;
				}
				$venueTable->store();

			/*	$premiumID  = $this->checkForPremiumTable($tblTable->table_name,$tblTable->venue_id,$tblTable->table_id);
				$tableIDNew = $tblTable->table_id;
				$tblTable2  = $this->getTable();
				$tblTable2->load($tableIDNew);
				$tblTable2->premium_table_id = $premiumID;
				$tblTable2->store();*/
			}

			return 1;
		}

		return 0;
	}

	public function checkForPremiumTable($tableName,$venueID,$tableID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_venue_premium_table'))
			->where($db->quoteName('premium_table_name') . ' = ' . $db->quote($tableName));

		// Set the query and load the result.
		$db->setQuery($query);

		$premiumTable = $db->loadObject();

		if($premiumTable)
		{
			$queryUPDT = $db->getQuery(true);

			// Create the base update statement.
			$queryUPDT->update($db->quoteName('#__beseated_venue_premium_table'))
				->set($db->quoteName('premium_table_name') . ' = ' . $db->quote(" "))
				->set($db->quoteName('published') . ' = ' . $db->quote('0'))
				->where($db->quoteName('premium_id') . ' = ' . $db->quote($premiumTable->premium_id));

			// Set the query and execute the update.
			$db->setQuery($queryUPDT);
			$db->execute();

			return $premiumTable->premium_id;
		}

		return 0;
	}

	public function deleteTable($tableID,$premiumID)
	{
		$tblTable = $this->getTable();
		$tblTable->load($tableID);
		$tblTable->published = 0;

		$bookingsAvailable = $this->checkForActiveBooking($tblTable->table_id,$tblTable->venue_id);


		if(count($bookingsAvailable) != 0)
		{
			return "500";
		}

	/*	if($tblTable->premium_table_id)
		{
			$tblTable->table_name = $tblTable->table_name;
			
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__beseated_venue_premium_table'))
				->set($db->quoteName('premium_table_name') . ' = ' . $db->quote(''))
				->set($db->quoteName('published') . ' = ' . $db->quote(0))
				->where($db->quoteName('premium_id') . ' = ' . $db->quote($tblTable->premium_table_id));

			$db->setQuery($query);
			$db->execute();
		}*/

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__beseated_venue_table_booking'))
			/*->set($db->quoteName('is_deleted') . ' = ' . $db->quote(1))*/
			->set($db->quoteName('deleted_by_user') . ' = ' . $db->quote(1))
			->set($db->quoteName('deleted_by_venue') . ' = ' . $db->quote(1))
			->where($db->quoteName('table_id') . ' = ' . $db->quote($tblTable->table_id));

		$db->setQuery($query);
		$db->execute();	

		if($tblTable->store())
		{
			return "200";
		}

		return "500";
	}

	public function checkForActiveBooking($tableID,$venueID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('venue_table_booking_id')
			->from($db->quoteName('#__beseated_venue_table_booking'))
			->where($db->quoteName('venue_id') . ' = ' . $db->quote($venueID))
			->where($db->quoteName('table_id') . ' = ' . $db->quote($tableID))
			/*->where($db->quoteName('is_deleted') . ' = ' . $db->quote(0))*/
			->where($db->quoteName('booking_date') . ' >= ' . $db->quote(date('Y-m-d H:i')));

		// Set the query and load the result.
		$db->setQuery($query);
		$result = $db->loadColumn();

		return $result;
	}
}

