<?php
/**
 * visdforms controller for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

/**
 * visforms Controller
 *
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @since        Joomla 1.6 
 */
class VisformsControllerVisform extends JControllerForm
{
	/**
	 * constructor (registers additional tasks to methods)
	 *
	 * @return void
	 * @since Joomla 1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config = array());
	}
	
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		$item = $model->getItem();
        $id = $item->get('id');
		if ($id)
		{		
			if ($validData['saveresult'] == 1) 
			{			
				//Create a new datatable if it doesn't allready exist
				if (!$model->createDataTables($id)) 
				{
					$this->setMessage($model->getError());
				}
			}
		}
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
		$model = $this->getModel('Visform', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_visforms&view=visforms' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}
	
	
	/**
	 * display the fields list for the selected form
	 *
	 * @return void
	 * @since Joomla 1.6
	 */
	function fields()
	{
        $fid = $this->input->getInt('id', -1);
        $context = "$this->option.edit.$this->context";
		$this->getModel()->checkin($fid);
        $this->releaseEditId($context, $fid);
		$this->setRedirect( "index.php?option=com_visforms&view=visfields&fid=".$fid);
		return true;
	}
    
    /**
	 * display the fields list for the selected form
	 *
	 * @return void
	 * @since Joomla 1.6
	 */
	function datas()
	{
        $fid = $this->input->getInt('id', -1);
        $context = "$this->option.edit.$this->context";
		$this->getModel()->checkin($fid);
        $this->releaseEditId($context, $fid);
		$this->setRedirect( "index.php?option=com_visforms&view=visdatas&fid=".$fid);
		return true;
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
		$user = JFactory::getUser();
		$userId = $user->get('id');
		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_visforms.visform.' . $recordId))
		{

			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', 'com_visforms.visform.' . $recordId))
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
