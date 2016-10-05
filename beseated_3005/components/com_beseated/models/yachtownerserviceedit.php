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
class BeseatedModelYachtOwnerServiceEdit extends JModelAdmin
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
	public function getTable($type = 'YachtService', $prefix = 'BeseatedTable', $config = array())
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
			'com_beseated.yachtservice',
			'yachtservice',
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
			'com_beseated.edit.yachtservice.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function save($data,$serviceID,$unique_code)
	{
		$serviceID = ($unique_code) ? 0 : $serviceID;

		$serviceTable = $this->getTable();
		$serviceTable->load($serviceID);
		$serviceTable->bind($data);

		if($serviceTable->store())
		{
			if(!empty($unique_code))
			{
				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				
				// Create the base select statement.
				$query->select('image_id,service_id,thumb_image,image')
					->from($db->quoteName('#__beseated_element_images'))
					->where($db->quoteName('service_id') . ' = ' . $db->quote($unique_code));
			
				// Set the query and load the result.
				$db->setQuery($query);
				
				$yachtImages = $db->loadObjectList();

				$tblImages  = JTable::getInstance('Images','BeseatedTable');

				foreach ($yachtImages as $key => $image) 
				{
					$tblImages->load($image->image_id);

					//$oldImagePath      = str_replace('/', '\\', JPATH_ROOT.'/images/beseated/Yacht/'.$serviceTable->yacht_id.'/Services/'.$unique_code);
					$oldImagePath      =  JPATH_ROOT.'/images/beseated/Yacht/'.$serviceTable->yacht_id.'/Services/'.$unique_code;
					
					//echo "<pre/>";print_r($oldImagePath);exit;

					$tblImages->service_id = $serviceTable->service_id;
					$tblImages->image = str_replace($unique_code, $serviceTable->service_id, $tblImages->image);
					$tblImages->thumb_image = str_replace($unique_code, $serviceTable->service_id, $tblImages->thumb_image);

					if($tblImages->store())
					{
						//$newImagePath      = str_replace('/', '\\', JPATH_ROOT.'/images/beseated/Yacht/'.$serviceTable->yacht_id.'/Services/'.$serviceTable->service_id);
						$newImagePath      = JPATH_ROOT.'/images/beseated/Yacht/'.$serviceTable->yacht_id.'/Services/'.$serviceTable->service_id;
						
						rename($oldImagePath, $newImagePath);
						//ename($oldImagePath, $newImagePath);
					}

				}

				//$serviceTable = $this->getTable();
				$serviceTable->load($serviceTable->service_id);
				$serviceTable->thumb_image = $tblImages->thumb_image;
				$serviceTable->image = $tblImages->image;
				$serviceTable->store();
				
			}
			
			if(!empty($serviceTable->yacht_id))
			{
				$tblYacht = JTable::getInstance('Yacht', 'BeseatedTable');
				$tblYacht->load($serviceTable->yacht_id);

				if ($tblYacht->published == 1)
				{
					$tblYacht->has_service = 1;
				}

				$tblYacht->store();
			}

			$session = JFactory::getSession();
			$session->set( 'yacht_service_saved', 1);

			return 1;
		}

		return 0;
	}
}

