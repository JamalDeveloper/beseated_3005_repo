<?php
/**
 * visfield model for Visforms
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
require_once JPATH_COMPONENT_ADMINISTRATOR.'/models/visform.php';
use Joomla\Utilities\ArrayHelper;

/**
 * Visfield model class for Visforms
 *
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 *
 * @since        Joomla 1.6 
 */
class VisformsModelVisfield extends JModelAdmin
{
    
    public function __construct($config = array())
    {
        $config['events_map'] = array(
            'delete' => 'visforms',
            'save' => 'visforms',
            'change_state' => 'visforms'
            );
        $config['event_before_save'] = 'onVisformsBeforeJFormSave';
        $config['event_after_save'] = 'onVisformsAfterJFormSave';
        $config['event_before_delete'] = 'onVisformsBeforeJFormDelete';
        $config['event_after_delete'] = 'onVisformsAfterJFormDelete';
        $config['event_change_state'] = 'onVisformsJFormChangeState';
        
        parent::__construct($config);
    }
	
	/**
	 * Method to perform batch operations on an form or a set of forms.
	 *
	 * @param   array  $commands  An array of commands to perform.
	 * @param   array  $pks       An array of form ids.
	 * @param   array  $contexts  An array of form contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function batch($commands, $pks, $contexts)
	{
		// Sanitize field ids.
		$pks = array_unique($pks);
		//JArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true))
		{
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks))
		{
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));
			return false;
		}

		$done = false;

		$result = $this->batchCopy($commands, $pks, $contexts);
		if (is_array($result))
		{
			$pks = $result;
		}
		else
		{
			return false;
		}
			

		$done = true;
		
		if (!$done)
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch copy field.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 * @since	11.1
	 */
	protected function batchCopy($commands, $oldFields, $contexts)
	{
		$isFormCopy = ArrayHelper::getValue($commands,'isFormCopy', false);
		$newFid = ArrayHelper::getValue($commands,'form_id', 0);
		//array is used to mend restricts and restrictions in copied fields
		$copyFormOldNewFieldsIdMap = array();
		$isFieldInFormCopy = false;
		$table = $this->getTable();
		
		if (empty($newFid)) {
			$this->setError((JText::_('COM_VISFORMS_ERROR_BATCH_NO_FORM_SELECTED')));
			return false;
		}
		$i = 0;

		// Check that the user has create permission for this form
		$extension = JFactory::getApplication()->input->get('option', '');
		$user = JFactory::getUser();
		if (!$user->authorise('core.create', $extension . '.visform.' . $newFid))
		{
			$this->setError(JText::_('COM_VISFORMS_FIELD_ERROR_BATCH_CANNOT_CREATE'));
			return false;
		}

		// Parent exists so we let's proceed
		while (!empty($oldFields))
		{
			// Pop the first ID off the stack
			$pk = array_shift($oldFields);
			$table->reset();

			// Check that the row actually exists
			if (!$table->load($pk))
			{
				if ($error = $table->getError())
				{
					// Fatal error
					$this->setError($error);
					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			if ($newFid == $table->fid)
			{
				// We copy into the same form so Alter the label & alias
				$data = $this->generateNewTitle('', $table->name, $table->label);
				$table->label = $data['0'];
				$table->name = $data['1'];
				$isFieldInFormCopy = true;
				//Remove values in database field restrictions
				$table->restrictions = "";
			}
			else
			{
				//alter formid
				$table->fid = $newFid;
				if ($isFormCopy !== true)
				{
					//Reset values in fields that reference other fields like _validate_equalTo
					$table->defaultvalue = $this->removeRestrictsValues($table->defaultvalue, $table->name, true);
				}
				//Remove values in database field restrictions, we set them anew if necessary
				$table->restrictions = "";

			}

			// Reset the ID because we are making a copy
			$table->id = 0;

			$unpublish = ArrayHelper::getValue($commands, 'unpublish', true);
			// Set to unpublished
			$table->published = ($unpublish) ?  0 : $table->published ;
			// delete ordering to get the next ordering number
			$table->ordering = '';

			// Check the row.
			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}

			// Store the row.
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}

			//Are Data saved for the table the copied fields belong to?
			//Then we have to create a datatable field
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			//Select Ids of Fields of copied form in Table visfields
			$query
				->select('a.saveresult')
				->from('#__visforms AS a')
				->where('a.id = ' .$table->fid);

			$db->setQuery($query);
			$saveresult = $db->loadResult();
			if (isset($saveresult) && $saveresult === '1')
			{
				$this->createDataTableFields($table->fid, $table->id);
			}


			// Get the new item ID
			$newId = $table->get('id');

			//we copy a complete form and must adapt conditional fields to new form
			if ($isFormCopy === true)
			{
				//create an item in the field map array
				$copyFormOldNewFieldsIdMap[$pk] = $newId;
			}

			if ($isFieldInFormCopy === true)
			{

				$oldRestricts = $this->getOldRestricts($newId);
				if (!empty($oldRestricts))
				{
					//only have to add new restrictions to existing fields
					$this->setRestrictions($oldRestricts);
				}
			}

			// Add the new ID to the array
			$newIds[$i]	= $newId;
			$i++;
		}

		if (!empty($copyFormOldNewFieldsIdMap))
		{
				$this->setConditionsInCopiedFields($copyFormOldNewFieldsIdMap);
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}
	protected function setConditionsInCopiedFields($idMap)
	{
		foreach ($idMap as $newId)
		{
			//get restrictions in new Field
			$oldRestricts = $this->getOldRestricts($newId);
			$newRestricts = array();
			$newEqualToRestrict = "";
			$newShowWhenRestricts = array();
			if (!empty($oldRestricts))
			{
				//replace oldIds in restricts['retrictedId'] with the proper newId value
				$c = count($oldRestricts);
				for ($i = 0; $i < $c; $i++)
				{
					$oldRestrictId = $oldRestricts[$i]['restrictedId'];
					if (!array_key_exists($oldRestrictId, $idMap))
					{
						//actually these data are invalid, we prevent them from being stored in the defaultvalue again and add a message
						JFactory::getApplication()->enqueueMessage((JText::_('COM_VISFORMS_CHECK_RESTRICTS_AFTER_BATCH_COPY')));
						continue;
					}
					$oldRestricts[$i]['restrictedId'] = $idMap[$oldRestrictId];

					switch($oldRestricts[$i]['type'])
					{
						case 'usedAsEqualTo' :
							$newEqualToRestrict = '#field' . $oldRestricts[$i]['restrictedId'];
							break;
						case 'usedAsShowWhen' :
							$newShowWhenRestricts[] = $oldRestricts[$i];
							break;
						default :
							//actually these data are invalid, we prevent them from being stored in the defaultvalue again and add a message
							JFactory::getApplication()->enqueueMessage((JText::_('COM_VISFORMS_CHECK_RESTRICTS_AFTER_BATCH_COPY')));
							continue;
					}
					//we need full restrict array to set restrictiosn
					$newRestricts[] = $oldRestricts[$i];
				}
			}
			//set restrictions
			$this->setRestrictions($newRestricts);
			//save new restricts
			//create strings from newRestricts
			$newShowWhenRestricts = array_map(function ($element) {return 'field' . $element['restrictedId'] . '__' . $element['optionId'];}, $newShowWhenRestricts);

			//get old values from database
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select($db->quoteName(array('defaultvalue', 'typefield')))
				->from($db->quoteName('#__visfields'))
				->where('id = ' .$newId);

			$db->setQuery($query);
			$result = $db->loadObject();

			//extract defaultvalue
			$registry = new JRegistry();
			$registry->loadString($result->defaultvalue);
			$defaultvalue = $registry->toArray();

			//reset or remove value in defaultvalue
			if (!empty($newShowWhenRestricts))
			{
				$defaultvalue['f_' . $result->typefield . '_showWhen'] = $newShowWhenRestricts;
			}
			else
			{
				unset($defaultvalue['f_' . $result->typefield . '_showWhen']);
			}
			if (!empty($newEqualToRestrict))
			{
				$defaultvalue['f_' . $result->typefield . '_validate_equalTo'] = $newEqualToRestrict;
			}
			else
			{
				$defaultvalue['f_' . $result->typefield . '_validate_equalTo'];
			}

			//parse defaultvalue as string
			$registry = new JRegistry();
			$registry->loadArray($defaultvalue);
			$defaultvalue = $registry;

			//update database
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->update($db->quoteName('#__visfields'))
				->set($db->quoteName('defaultvalue')  . " = " . $db->quote($defaultvalue))
				->where($db->quoteName('id'). " = " . $newId);
			$db->setQuery($query);
			$db->execute();
		}
	}
	
	public function save($data)
	{
        $app = JFactory::getApplication();
		//reset fields in group "defaultvalue" that are not part of the selected fieldtype (and its subfieldtypes) to their defaults before storage
		$form = $this->getForm();
        $prefix = 'f_' . $data['typefield'];
		
		//get all fieldsets in fields group defaultvalue
		$groupFieldSets = $form->getFieldsets('defaultvalue');
		$canFrontendEdit = VisformsAEF::checkAEF(VisformsAEF::$allowfrontenddataedit);
		//go through all fieldsets
		foreach ($groupFieldSets as $name => $fieldSet) {
			//and find those that are not selected in the listbox typefield and t_typefield
			if ($name !== 'visf_' . $data['typefield']) { 
				foreach ($form->getFieldset($name) as $field) {
					//$data['defaultvalue'][$field->fieldname] = $form->getFieldAttribute($field->fieldname, 'default', '', 'defaultvalue');
                    unset($data['defaultvalue'][$field->fieldname]);
				}
			}
            //set aef field values to defaulf if no aef is available
            else
            {
                if (empty($canFrontendEdit))
                {
                    $data['editonlyfield'] = 0;            
                }
            }
		}
		if (isset($data['defaultvalue']) && is_array($data['defaultvalue'])) 
        {
            if ($app->input->get('task') != 'save2copy')
            {
                if (!empty($canFrontendEdit))
                {
                    //only allow to set aef field editonlyfield to 1 if field is not used as restrictor otherwise reset it to default
                    if (!$this->canSaveEditOnlyField($data))
                    {
                       $data['editonlyfield'] = 0;
                    }
                    //if aef editonlyfield is set to 1
                    if (!empty($data['editonlyfield']))
                    {
                        //remove restricts from submitted data
                        //restrictions in restrictor fields will be removed automatically by the code below and no new restrictions will be set if restricts are empty
                        $data['defaultvalue'] = $this->removeRestrictsValues($data['defaultvalue'], $data['name'], false, false);
                    }
                }
                
                //if we deal with a select, radio or multicheckbox and one of it's options, 
                //that is used as a restriction in another field, is going to be removed, we have to do something
                //we will remove restricts in conditional field (defaultvalue) and give a message
                if (in_array($data['typefield'], array('select', 'radio', 'multicheckbox')))
                {
                    //get list of restritions and respective restricted field id's from database
                    $oldRestrictions = $this->getRestrictions($data['id']);
                    if (isset($oldRestrictions['usedAsShowWhen']) && (count($oldRestrictions['usedAsShowWhen']) > 0))
                    {
                        //loop through restrictions
                        foreach ($oldRestrictions['usedAsShowWhen'] as $oRKey => $oRId)
                        {
                            $this->mendOldRestricts($oRKey, $oRId, $data);
                        }
                    }
                }
                // get old restrict values, set in "defaultvalues", from database (recordset of field that is restricted)
                if ($oldRestricts = $this->getOldRestricts($data['id']))
                {
                    //remove dependencies, stored in "restrictions" (in database), in the recordset of the restricting fields
                    $this->removeRestrictions($oldRestricts);
                }
                //get new restrict values, set in "defaultvalue" in the form (which are not yet stored in database)
                if ($restricts = $this->getRestricts($data['id'], $data['name'], $data['defaultvalue']))
                {
                    //store dependencies in database in "restrictions" , in the recordset of the restriction field
                    $restrictions = $this->setRestrictions($restricts);
                }
            }
				$registry = new JRegistry;
				$registry->loadArray($data['defaultvalue']);
				$data['defaultvalue'] = (string)$registry;
			}

		// Alter the title for save as copy
		if ($app->input->get('task') == 'save2copy') 
        {
			list($label, $name) = $this->generateNewTitle('', $data['name'], $data['label']);
			$data['label']	= $label;
			$data['name']	= $name;
            $data['restrictions'] = "";
		}

		if (parent::save($data)) {
			$isNew = $this->getState($this->getName() . '.new');
			$newId = $this->getState($this->getName() . '.id');
			if ((!empty($isNew)) && (!empty($newId)) && ($app->input->get('task') == 'save2copy'))
			{
				$oldRestricts = $this->getOldRestricts($newId);
				if (!empty($oldRestricts))
				{
					//only have to add new restrictions to existing fields
					$this->setRestrictions($oldRestricts);
				}
			}
			return true;
		}

	return false;
	}
	
	/**Method to create a field in datatable
	 *
	 * @params string $fid form id
	 * @return boolean true
	 *
	 * @since Joomla 1.6
	 */
	 // Test if data must be saved in DB for this form
	
	public function createDataTableFields($fid = Null, $id = Null) 
	{
        if (!$this->createDataTableField($fid, $id))
        {
            //throw error
        }
         if (!$this->createDataTableField($fid, $id, true))
        {
            //throw error
        }

	return true;
	}


	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{	
		// Get the form.
		$form = $this->loadForm('com_visforms.visfield', 'visfield', array('control' => 'jform', 'load_data' => $loadData));
		$canFrontendEdit = VisformsAEF::checkAEF(VisformsAEF::$allowfrontenddataedit);
		if (empty($form)) {
			return false;
		}
		
		$app=JFactory::getApplication();
		$fid = $app->input->getInt('fid', 0);
		$id = $app->input->getInt('id', 0);
        $model = JModelLegacy::getInstance('Visform', 'VisformsModel', array('ignore_request' => true));
        $visform = $model->getItem($fid);
		if ((!empty($visform)) && ($visform->layoutsettings['formlayout'] != 'mcindividual'))
        {
            $form->removeField('multicolumnbtdesc');
            $form->removeField('bootstrap_size');
        }
		$user = JFactory::getUser();

		// Check for existing article.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_visforms.visform.'. $fid . '.visfield.'.(int) $id))
		|| ($id == 0 && !$user->authorise('core.edit.state', 'com_visforms.visform.'. $fid)))
		{
			// Disable fields for display.
			$form->setFieldAttribute('published', 'disabled', 'true');
		}
        $form->setFieldAttribute('ordering', 'disabled', 'true');
        //remove aef fields
        if (empty($canFrontendEdit))
        {
                $form->removeField('editonlyfield');
                $form->removeField('editonlyfield');
        }
		
		return $form;
	}
	
	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {
			// Convert the defaultvalue field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->defaultvalue);
			$item->defaultvalue = $registry->toArray();
		}

		return $item;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{	
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_visforms.edit.visfield.data', array());

		if (empty($data)) 
        {
			$data = $this->getItem();
		}

		return $data;
	}
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Visfield', $prefix = 'VisformsTable', $config = array())
	{	
		return JTable::getInstance($type, $prefix, $config);
	}
	
	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param	object	A record object.
	 *
	 * @return	array	An array of conditions to add to add to ordering queries.
	 * @since	1.6
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'fid = '.JFactory::getApplication()->input->get('fid', 0);
		return $condition;
	}
	
	/**
	 * Method to test whether a record state can be changed.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   11.1
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check for existing field.
		if (!empty($record->id)  && !empty($record->fid)) 
        {
			return $user->authorise('core.edit.state', 'com_visforms.visform.' . (int) $record->fid . '.visfield.' .(int) $record->id);
		}
		// Default to component settings 
		else {
			return parent::canEditState($record);
		}
	}
	
    /**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   11.1
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id)  && !empty($record->fid)) 
        {
            if (!(empty($record->restrictions)))
            {
                if ($restrictions = $this->getRestrictions($record->id))
                {
                    foreach ($restrictions as $r => $value)
                    {
                        foreach ($value as $fieldName => $fieldId)
                        {
                            switch ($r)
                            {
                                case  'usedAsEqualTo' :
                                    $option = JText::_('COM_VISFORMS_EQUAL_TO');
                                    break;
                                case  'usedAsShowWhen' :
                                    $option = JText::_('COM_VISFORMS_SHOW_WHEN');
                                    break;
                                default :
                                    $option = "";
                                    break;
                            }
                            $this->setError(JText::sprintf('COM_VISFORMS_HAS_RESTRICTIONS', $record->name, $option, $fieldName));
                        }
                    }
                    return false;
                }
            }
			$user = JFactory::getUser();
			return $user->authorise('core.delete', 'com_visforms.visform.' . (int) $record->fid . '.visfield.' .(int) $record->id);
		}
		else
		{
			//use component settings
			return parent::canDelete($record);
		}
	}
    
    protected function canSaveEditOnlyField($data)
    {
        if (!empty($data['id']))
        {
			$canFrontendEdit = VisformsAEF::checkAEF(VisformsAEF::$allowfrontenddataedit);
            if (empty($canFrontendEdit))
            {
                return true;            
            }
            if (empty($data['editonlyfield']))
            {
                return true;            
            }
            if ($restrictions = $this->getRestrictions($data['id']))
            {
                foreach ($restrictions as $r => $value)
                {
                    foreach ($value as $fieldName => $fieldId)
                    {
                        switch ($r)
                        {
                            case  'usedAsEqualTo' :
                                $option = JText::_('COM_VISFORMS_EQUAL_TO');
                                break;
                            case  'usedAsShowWhen' :
                                $option = JText::_('COM_VISFORMS_SHOW_WHEN');
                                break;
                            default :
                                $option = "";
                                break;
                        }
                        $app = JFactory::getApplication();
                        $app->enqueueMessage(JText::sprintf('COM_VISFORMS_HAS_SAVE_RESTRICTIONS', $data['name'], $option, $fieldName, JText::_('COM_VISFORMS_IS_EDIT_ONLY_FIELD_LABEL')), 'warning');
                    }
                }                
                return false;
            }
		}
        return true;
    }
	
	/**
	 * Method to change the label & name.
	 *
	 * @param   string   $name        The name.
	 * @param   string   $label        The label.
	 *
	 * @return	array  Contains the modified label and name.
	 *
	 * @since	11.1
	 */
	protected function generateNewTitle( $catid, $name, $label)
	{
		// Alter the label & name
		$table = $this->getTable();
		while ($table->load(array('name' => $name)))
		{

			$label = JString::increment($label);
			$name = JString::increment($name, 'dash');
		}

		return array($label, $name);
	}
    
    /**
     * get value of database field restrictions of a given recordset and convert it into an array
     * @param int $id record set id
     * @return array (of arrays) of stored information about depentent fields
     */
    public function getRestrictions($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        //Select restriction		
        $query 
            ->select('restrictions')
            ->from('#__visfields')
            ->where('id = ' .$id);

        $db->setQuery($query);
        $result = $db->loadResult();
        $registry = new JRegistry;
		$registry->loadString($result);
		$restrictions = $registry->toArray();
        return $restrictions;
    }
    
    /**
     * create for each option of a given field that references to another field a JSON Object and save it in the restrictions field of the dependent field 
     * @param array $restricts array of options of a given field that reference to other fields (like equalTo Validation)
     * @return boolen
     */
    protected function setRestrictions ($restricts)
    {        
        while (!empty($restricts))
		{
			// Pop the first ID off the stack
			$restriction = array_shift($restricts);
            //extract params in database field restriction
            $restrictions = $this->getRestrictions($restriction['restrictedId']);
            //if restriction is already set, do nothing
            foreach ($restrictions as $r => $v)
            {
                if ($r == $restriction['type'])
                {
                    foreach ($v as $index => $restrictorId)
                    {
                        if ($restrictorId == $restriction['restrictorId'])
                        {
                            continue;
                        }
                    }
                }
                else
                {
                    //add restriction to restriction type
                    $restrictions[$r][$restriction['restrictorName']] = $restriction['restrictorId'];
                }
            }

            // add restrictiontype and restriction to field
            $restrictions[$restriction['type']][$restriction['restrictorName']] = $restriction['restrictorId'];
        
        
        if (isset($restrictions) && is_array($restrictions)) {
            $registry = new JRegistry;
            $registry->loadArray($restrictions);
            $restrictions = (string)$registry;
        }
            $this->saveRestriction($restriction['restrictedId'], $restrictions);
        }
        return true;
    }
    
    /**
     * check if a given field has in its unsaved form options that reference to other fields (like equalTo Validation)
     * @param int $id id of field to get the restricts from
     * @param string $name name of field to get the restricts from
     * @param array $defaultvalue 
     * @return array of array of restricts
     */
    public function getRestricts ($id, $name, $defaultvalues)
    {
        $i = 0;
        foreach ($defaultvalues as $dfname => $dfvalue)
        {
            if ((strpos($dfname, '_validate_equalTo') > 0) && (strpos($dfvalue, '#field') === 0 ))
            {
                $restricts[$i] = array();
                 $restricts[$i]['type'] = "usedAsEqualTo";
                 $restricts[$i]['restrictedId'] = JHtml::_('visforms.getRestrictedId', $dfvalue);
                 $restricts[$i]['restrictorId'] = $id;
                 $restricts[$i]['restrictorName'] = $name;
                 $i++;
            }
            if ((strpos($dfname, '_showWhen') > 0) && is_array($dfvalue))
            {
                foreach ($dfvalue as $value)
                {
                    if (preg_match('/^field/', $value) === 1)
                    {
                        $restricts[$i] = array();
                         $restricts[$i]['type'] = "usedAsShowWhen";
                         $parts = explode('__', $value, 2);
                         $restricts[$i]['restrictedId'] = JHtml::_('visforms.getRestrictedId', $parts[0]);
                         $restricts[$i]['restrictorId'] = $id;
                         $restricts[$i]['restrictorName'] = $name;
                         $i++;
                    }
                }
            }
        }
        return $restricts;
    }
    
    /**
     * check if a given field has in its stored defaultvalues options, that reference to other fields (like equalTo Validation)
     * @param int $id field id
     * @return array of array of restricts
     */
    public function getOldRestricts ($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);	
        $query 
            ->select($db->quoteName(array('defaultvalue', 'name')))
            ->from('#__visfields')
            ->where('id = ' .$id);

        $db->setQuery($query);
        $result = $db->loadObject();
        $restricts = array();
        // Convert the defaultvalue field to an array.
        $registry = new JRegistry;
        $registry->loadString($result->defaultvalue);
        $defaultvalues = $registry->toArray();
        $i = 0;
        foreach ($defaultvalues as $dfname => $dfvalue)
        {
            if ((strpos($dfname, '_validate_equalTo') > 0) && (strpos($dfvalue, '#field') === 0 ))
            {
                $restricts[$i] = array();
                 $restricts[$i]['type'] = "usedAsEqualTo";
                 $restricts[$i]['restrictedId'] = preg_replace('/[^0-9]/', '', $dfvalue);
                 $restricts[$i]['restrictorId'] = $id;
                 $restricts[$i]['restrictorName'] = $result->name;
                 $i++;
            }
            if ((strpos($dfname, '_showWhen') > 0) && is_array($dfvalue))
            {
                foreach ($dfvalue as $value)
                {
                    if (preg_match('/^field/', $value) === 1)
                    {
                        $restricts[$i] = array();
                         $restricts[$i]['type'] = "usedAsShowWhen";
                         $parts = explode('__', $value, 2);
                         $restricts[$i]['restrictedId'] = preg_replace('/[^0-9]/', '', $parts[0]);
                         $restricts[$i]['restrictorId'] = $id;
                         $restricts[$i]['restrictorName'] = $result->name;
                         $restricts[$i]['optionId'] = $parts[1];
                         $i++;
                    }
                }
            }
        }
        return $restricts;
    }
    
    /**
     * Save a string with information that a give field is used in other field options, so changes on the field might have negative effects, in the restrictions database field
     * @param int $id record set id
     * @param string $value JSON Object as string with information of dependent fields {dependencyName : {dependentField1Name: dependentField1Id, dependentField1Name : dependentField2Id}}
     */
    protected function saveRestriction ($id, $value)
    {
        $db = JFactory::getDbo();        
        $db->setQuery("UPDATE " . $db->quoteName('#__visfields') . " SET " . $db->quoteName('restrictions') . " = " . $db->quote($value) . " WHERE " . $db->quoteName('id'). " = " . $id);
		$db->execute();
    }
    
    /**
     * method to remove remove values from database field "restrictions" of all fields that are listed in the $restricts parameter
     * @param array $restricts Array restrictions. Each restsriction is an arrays consisting of type, restrictedid and restrictorid
     * @return boolean
     */
    public function removeRestrictions($restricts)
    {
        while (!empty($restricts))
		{
			// Pop the first ID off the stack
			$restriction = array_shift($restricts);
            //extract params in database field restrictions
            $restrictions = $this->getRestrictions($restriction['restrictedId']);
            //if restriction is set, remove it
            foreach ($restrictions as $r => $v)
            {
                if ($r == $restriction['type'])
                {
                    foreach ($v as $index => $restrictorId)
                    {
                        if ($restrictorId == $restriction['restrictorId'])
                        {
                            unset($restrictions[$r][$index]);
                        }
                    }
                }
            }
            foreach ($restrictions as $r => $v)
            {
                if ((is_array($v)) && (count($v) == 0))
                {
                    unset($restrictions[$r]);
                }
            }
        
        
            if (isset($restrictions) && is_array($restrictions)) {
                $registry = new JRegistry;
                $registry->loadArray($restrictions);
                $restrictions = (string)$registry;
            }
            //save the changed restriction
            $this->saveRestriction($restriction['restrictedId'], $restrictions);
        }
        return true;
    }
    
    /**
     * Method to remove restricts from the defaultvalue database field of a given field
     * @param string $defaultvalue JSON String with field default settings
     * @param string $fieldname
     * @param string $msg
     * @return string, JSON string with field defaut setting (defaultvalues)
     */
    protected function removeRestrictsValues ($defaultvalue, $fieldname, $msg = true, $isRegistry = true)
    {
        if (!empty($isRegistry))
        {
            $registry = new JRegistry;
            $registry->loadString($defaultvalue);
            $defaultvalue = $registry->toArray();
        }
        foreach ($defaultvalue as $dfname => $dfvalue)
        {
            if ((strpos($dfname, '_validate_equalTo') > 0) && (strpos($dfvalue, '#field') === 0 ))
            {
                $defaultvalue[$dfname] = '';
                if ($msg)
                {
                    JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_RESTIRCTS_RESET', JText::_('COM_VISFORMS_EQUAL_TO'), $fieldname), 'warning');
                }
            }
            if ((strpos($dfname, '_showWhen') > 0) && is_array($dfvalue))
            {
                $defaultvalue[$dfname] = '';
                if ($msg)
                {
                    JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_VISFORMS_RESTIRCTS_RESET', JText::_('COM_VISFORMS_SHOW_WHEN'), $fieldname), 'warning');
                }
            }
        }
        // Convert the defaultvalue field to an array.
        if (isset($defaultvalue) && is_array($defaultvalue) && (!empty($isRegistry))) 
        {
            $registry = new JRegistry;
            $registry->loadArray($defaultvalue);
            $defaultvalue = (string)$registry;
        }
        return $defaultvalue;
    }
    
    /**
     * Methode to do something, if an option of a select, radio or multicheckbox is deleted and this option toggles the display of some other field
     * 
     * @param string $fieldname field name of restrictor field
     * @param int $id field id of restrictor field
     * @param obj $data restricted field
     * @param in $restrictedId field id of restricted field
     */
    protected function mendOldRestricts($fieldname, $id, $data)
    {
        $options = $data['defaultvalue']['f_' . $data['typefield'] . '_list_hidden'];
        $restrictedId = $data['id'];
        $name = $data['name'];
        if (is_null($options))
        {
            return;
        }
        
        if (($options == "") || ($options == "{}"))
        {
            return;
        }
        
        //extract options (of select, radio or multselect that is actually save)
        $registry = new JRegistry();
        $registry->loadString($options);
        $options = $registry->toArray();
        //get a list of all item ids of the options list
        $optionsId = array_map(function($element) {return $element['listitemid'];}, $options);
        
        //get old restricts in depending field (which is actually represented by $id)
        $oldRestricts = $this->getOldRestricts($id);
        $newRestricts = $oldRestricts;
        
        foreach ($oldRestricts as $oldRestrictKey => $oldRestrictValue)
        {
            //find Restricts that come from the field we ar actually trying to save (type= usedAsShowWhen, restrictorId = rId)
            if (isset($oldRestrictValue['type']) && ($oldRestrictValue['type'] == 'usedAsShowWhen') && isset($oldRestrictValue['restrictedId'])&& ($oldRestrictValue['restrictedId'] == $restrictedId))
            {
                if(isset($oldRestrictValue['optionId']) && (!(in_array($oldRestrictValue['optionId'], $optionsId))))
                {
                    //do something for example add a message
                    JFactory::getApplication()->enqueueMessage(JText::sprintf("COM_VISFORMS_OPTION_TOGGLES_DISPLAY", $oldRestrictValue['optionId'], $name, $fieldname), 'notice');
                    //remove it from the restricts list
                    unset($newRestricts[$oldRestrictKey]);
                }
            }
        }
        
        //check for changed restricts
        if ($newRestricts != $oldRestricts)
        {
            //we save the changed values in the database
            //create the string from newRestricts
            $newRestricts = array_map(function ($element) {return 'field' . $element['restrictedId'] . '__' . $element['optionId'];}, $newRestricts);
            
            //get old values from database
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);	
            $query 
                ->select($db->quoteName(array('defaultvalue', 'typefield')))
                ->from($db->quoteName('#__visfields'))
                ->where('id = ' .$id);

            $string = $db->setQuery($query);
            $result = $db->loadObject();
            
            //extract defaultvalue
            $registry = new JRegistry();
            $registry->loadString($result->defaultvalue);
            $defaultvalue = $registry->toArray();
            
            //reset value in defaultvalue
            $defaultvalue['f_' . $result->typefield . '_showWhen'] = $newRestricts;
            
            //parse defaultvalue as string
            $registry = new JRegistry();
            $registry->loadArray($defaultvalue);
            $defaultvalue = $registry;
            
            //update database
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);	
            $query 
                ->update($db->quoteName('#__visfields'))
                ->set($db->quoteName('defaultvalue')  . " = " . $db->quote($defaultvalue))
                ->where($db->quoteName('id'). " = " . $id);
            $db->setQuery($query);
            $db->execute();
        }
    }

        /**
     * Method to create a new field in the data table (used for storing submitted user inputs)
     * @param int $fid form id
     * @param int $id field id
     * @param boolean $save set to true if field is to be created in the data save table
     * @return boolean
     */
    public function createDataTableField($fid = Null, $id = Null, $save = false) 
	{
		
		$tn = "#__visforms_".$fid;
        if ($save === true)
        {
            $tn .= "_save";
        }
	
		$dba	= JFactory::getDbo(); 

		$tableFields = $dba->getTableColumns($tn,false);
		$fieldname = "F" . $id;
		
		
		if (!isset( $tableFields[$fieldname] ))  
		{

			$query = "ALTER TABLE ".$tn." ADD F".$id." TEXT NULL";
			$dba->SetQuery($query);
			if (!$dba->execute()) 
			{
				echo JText::_( 'COM_VISFORMS_PROBLEM_WITH' )." (".$query.")";
				return false;
			}
		return true;
		}
	return true;
	}
    
    /**
     * Method to publish a recordset
     * @param array $pks array of id's
     * @param boolean $value wether to publish or unpublish
     * @return type
     */
    public function publish(&$pks, $value = 1)
	{
		$pks = (array) $pks;

		// Look for restrictions.
		foreach ($pks as $i => $pk)
        {
            $restrictions = $this->getRestrictions($pk);
            if ((is_array($restrictions)) && (count($restrictions) > 0) && isset($this->state->task) && $this->state->task == 'unpublish')
            {
                //Give an error message
                JFactory::getApplication()->enqueueMessage(JText::sprintf("COM_VISFORMS_FIELD_HAS_RESTICTIONS", $pk), 'warning');
                //unset the pk
                unset($pks[$i]); 
            }
        }
        return parent::publish($pks, $value);
    }
}		
?>
