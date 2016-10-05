<?php
/**
 * @author       Aicha Vack
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @link         http://www.vi-solutions.de
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controllerform');

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @since       1.6
 */
class VisformsControllerVisfield extends JControllerForm
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since	1.6
	 */
	public function __construct($config = array())
	{

		parent::__construct($config);
	}
	
	
	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean	 True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Visfield', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_visforms&view=visfields' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}
	
	
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{	
		$item = $model->getItem();
        $id = $item->get('id'); 
		$fid = $item->get('fid');
		if (($fid) && ($id))
		{		
			$db = JFactory::getDbo();
			// Test if data must be saved in DB for this form
			$query = ' SELECT * from #__visforms c where c.id='.$fid.' ';
			$db->setQuery($query);
			$forms = $db->loadObjectList();
			if (count($forms ) > 0)
			{
				$rowTable = $forms[0];
				if ($rowTable->saveresult == 1) {		
					$model->createDataTableFields($fid, $id);
				}
			}
		}
	}
	
	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$fid = $fid = $this->input->getInt('fid', 0);
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&fid=' . $fid;

		return $append;
	}
	
	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   11.1
	 */
	protected function getRedirectToListAppend()
	{
		$fid = $fid = $this->input->getInt('fid', 0);
		$append = '';

		// Setup redirect info.
		if ($fid != 0)
		{
			$append .= '&fid=' . $fid;
		}
		parent::getRedirectToListAppend();

		return $append;
	}
	
	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Initialise variables.
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$fid = $fid = $this->input->getInt('fid');
		$assetId = 'com_visforms.visform.' . $fid . '.visfield.' . $recordId;
		$user = JFactory::getUser();
		$userId = $user->get('id');
		// Check general edit permission first.
		if ($user->authorise('core.edit', $assetId))
		{
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', $assetId))
		{

			// Now test the owner is the user.
			$ownerId = (int) isset($data['created_by']) ? $data['created_by'] : 0;
			if (empty($ownerId) && $recordId)
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $userId)
			{
				return true;
			}
		}
		
		return false;
	}
}
