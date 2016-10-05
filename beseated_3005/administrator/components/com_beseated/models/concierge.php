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
 * Beseated Event Model
 *
 * @since  0.0.1
 */
class BeseatedModelConcierge extends JModelAdmin
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
	 * @since   1.6
	 */
	public function getTable($type = 'Concierge', $prefix = 'BeseatedTable', $config = array())
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
			'com_beseated.concierge',
			'concierge',
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
			'com_beseated.edit.concierge.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function save($data)
	{
		$conciergecount = $this->getConcierge();

		$dispatcher = JEventDispatcher::getInstance();

		$table = $this->getTable();

		$input = JFactory::getApplication()->input;
		$concierge_id = $input->getInt('concierge_id');

		if ($concierge_id > 0)
		{
			$table->load($concierge_id);
			$isNew = false;

		}

		if(!$conciergecount)
		{
			$data['is_default'] = 1;
		}
		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		return true;


	}

	public function getUserTicketDetail()
	{
		$input = JFactory::getApplication()->input;

		$concierge_id = $input->getInt('concierge_id');

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_concierge'))
			->where($db->quoteName('concierge_id') . ' = ' . $db->quote($concierge_id))
			->order($db->quoteName('concierge_id') . ' ASC');

			//echo $query;exit;
		// Set the query and load the result.
		$db->setQuery($query);
		$conciergeDetails = $db->loadObjectList();

		return $conciergeDetails;
	}

	public function getConcierge()
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('COUNT(concierge_id)')
			->from($db->quoteName('#__beseated_concierge'))
			->where($db->quoteName('is_default') . ' = ' . $db->quote('1'));

		$db->setQuery($query);
		$conciergeCount = $db->loadResult();

		return $conciergeCount;
	}
}
