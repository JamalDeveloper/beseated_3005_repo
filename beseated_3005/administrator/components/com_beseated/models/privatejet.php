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
 * Beseated Private Jet Model
 *
 * @since  0.0.1
 */
class BeseatedModelPrivateJet extends JModelAdmin
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
	public function getTable($type = 'PrivateJet', $prefix = 'BeseatedTable', $config = array())
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
			'com_beseated.privatejet',
			'privatejet',
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
			'com_beseated.edit.Privatejet.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $ids      Ids to delete
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   0.0.1
	 */
	public function delete(&$ids)
	{
		if(count($ids) == 0)
		{
			return false;
		}

		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base update statement.
		$query->update($db->quoteName('#__beseated_private_jet'))
			->set($db->quoteName('is_deleted') . ' = ' . $db->quote('1'))
			->set($db->quoteName('published') . ' = ' . $db->quote('0'))
			->where($db->quoteName('private_jet_id') . ' IN (' . implode(',', $ids).')');

		// Set the query and execute the update.
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}
	}

	public function save($data)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$input = JFactory::getApplication()->input;
		$file = $input->files->get('jform', '', 'array');
		$dispatcher = JEventDispatcher::getInstance();

		$isNew = true;

		$table = $this->getTable();

		$input = JFactory::getApplication()->input;
		$private_jet_id = $input->getInt('private_jet_id');

		$tmp_name  = $file['image']['tmp_name'];
		$imageName = $file['image']['name'];
		$imageName = str_replace(' ', '', $imageName);
		$imageName = JFile::makeSafe($imageName);
		$imgnm     = JFile::stripExt($imageName);
		$ext       = JFile::getExt($imageName);

		$allowedExts = array("gif", "jpeg", "jpg", "png");
		$extension = end(explode(".", $file['image']["name"]));


		if(!empty($imageName))
		{
			if ((($file['image']["type"] == "image/gif") || ($file['image']["type"] == "image/jpeg") || ($file['image']["type"] == "image/jpg") || ($file['image']["type"] == "image/png")) && in_array($extension, $allowedExts))
			{
				if(!JFolder::exists(JPATH_ROOT . "images/beseated/"))
				{
					JFolder::create(JPATH_ROOT . "images/beseated/");
				}

				if(!JFolder::exists(JPATH_ROOT . "images/beseated/Private-Jet/"))
				{
					JFolder::create(JPATH_ROOT . "images/beseated/Private-Jet/");
				}


				$img = $imgnm . "_" . rand(0, 99999) . "." . $ext;
				$dest = "../images/beseated/Private-Jet/$img";
				$path = "images/beseated/Private-Jet/$img";

				$data['image'] = $path;

				if(!JFile::upload($tmp_name, $dest))
				{
					$this->setError('error in file uploading');
					return false;
				}

			}
			else
			{
				$this->setError('invalid type of image');
				return false;
			}
		}


		if ($private_jet_id > 0)
		{
			$isNew = false;
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
}
