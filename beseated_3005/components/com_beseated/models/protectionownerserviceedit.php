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
class BeseatedModelProtectionOwnerServiceEdit extends JModelAdmin
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
	public function getTable($type = 'ProtectionService', $prefix = 'BeseatedTable', $config = array())
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
			'com_beseated.protectionservice',
			'protectionservice',
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
			'com_beseated.edit.protectionservice.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function save($data,$serviceID)
	{
		$serviceTable = $this->getTable();
		$serviceTable->load($serviceID);

		if(isset($data['image']))
		{
			$newImage = $data['image'];
			if(!empty($newImage) && !empty($serviceTable->image))
			{
				$oldImgPath      = JPATH_SITE.'/images/beseated/'.$serviceTable->image;
				$oldThumbImgPath = JPATH_SITE.'/images/beseated/'.$serviceTable->image;

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

		$serviceTable->bind($data);
		if($serviceTable->store())
		{
			if(!empty($serviceTable->service_name))
			{

				$protectionservice = JTable::getInstance('Protection', 'BeseatedTable');
				$protectionservice->load($serviceTable->protection_id);
				if ($protectionservice->published == 1){
					$protectionservice->has_service = 1;
				}
				$protectionservice->store();
			}

			return 1;
		}

		return 0;
	}
}

