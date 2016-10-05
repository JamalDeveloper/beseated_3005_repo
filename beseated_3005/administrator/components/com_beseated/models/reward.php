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
 * Beseated Chauffeur Model
 *
 * @since  0.0.1
 */
class BeseatedModelReward  extends JModelAdmin
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
	public function getTable($type = 'Rewards', $prefix = 'BeseatedTable', $config = array())
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
			'com_beseated.reward',
			'reward',
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
			'com_beseated.edit.reward.data',
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


		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$input = JFactory::getApplication()->input;
		$file = $input->files->get('jform', '', 'array');
		$dispatcher = JEventDispatcher::getInstance();

		$isNew = true;

		$table = $this->getTable();

		$input = JFactory::getApplication()->input;
		$reward_id = $input->getInt('reward_id');

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

				if(!JFolder::exists(JPATH_ROOT . "images/beseated/Reward/"))
				{
					JFolder::create(JPATH_ROOT . "images/beseated/Reward/");
				}


				$img = $imgnm . "_" . rand(0, 99999) . "." . $ext;
				$dest = "../images/beseated/Reward/$img";
				$path = "images/beseated/Reward/$img";

				$data['image'] = $path;

				if(!JFile::upload($tmp_name, $dest))
				{
					$this->setError('error in image uploading');
					return false;
				}

			}
			else
			{
				$this->setError('invalid type of image');
				return false;
			}
		}


		if ($reward_id > 0)
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
