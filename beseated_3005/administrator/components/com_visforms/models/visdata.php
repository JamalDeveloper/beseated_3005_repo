<?php

/**
 * Visdata model for visforms
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
require_once JPATH_COMPONENT_ADMINISTRATOR.'/models/visdatas.php';

class VisformsModelVisdata extends JModelAdmin
{
    protected $fielddefinition;
    
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->fielddefinition = $this->getDatafields();
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
	public function getForm($data = array(), $loadData = false)
	{	
		// Get the form.
		$form = $this->loadForm('com_visforms.visdata', 'visdata', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
        $fields = $this->fielddefinition;
        foreach ($fields as $field)
        {
            $required = '';//we cannot add required validation without proper handling of conditional fields!
			//$required = (!empty($field->attribute_required)) ? ' required="true"' : '';
            switch($field->typefield)
            {
                case 'text':
                case 'password':
                case 'hidden':
                    $fieldstring = '<field name="F'. $field->id. '"'.
                        ' type="text"'.
                        ' label="'.$field->label. '"'.
                        $required .
                        ' />';
                    $fieldXml = new SimpleXMLElement($fieldstring);
                    $form->setField($fieldXml);
                    break;
                case 'file':
                    $fieldstring = '<field name="F'. $field->id. '"'.
                        ' type="file"'.
                        ' label="'.$field->label. '"'.
                        ' class="hiddenFileUpload"' .
                        ' disabled="true"' .
                        ' />';
                    $fieldXml = new SimpleXMLElement($fieldstring);
                    $form->setField($fieldXml);
                    break;
                case 'email' :
                case 'checkbox':
                case 'number':
                case 'url':
                    $fieldstring = '<field name="F'. $field->id. '"'.
                        ' type="'.$field->typefield.'"'.
                        ' label="'.$field->label. '"'.
                        $required .
                        ' />';
                    $fieldXml = new SimpleXMLElement($fieldstring);
                    $form->setField($fieldXml);
                    break;
                case 'textarea':
                    $type = (!empty($field->HTMLEditor)) ? 'editor' : 'textarea';
                    $fieldstring = '<field name="F'. $field->id. '"'.
                        ' type="'.$type.'"'.
                        ' label="'.$field->label. '"'.
                        $required .
                        ' />';
                    $fieldXml = new SimpleXMLElement($fieldstring);
                    $form->setField($fieldXml);
                    unset($type);
                    break;
                case 'select':
                    $options = JHtmlVisformsselect::extractHiddenList($field->list_hidden);
                    $selectOptions = '';
                    foreach ($options as $option)
                    {
                        $selectOptions .= '<option value="'.htmlspecialchars($option['value'],ENT_COMPAT, 'UTF-8').'">'.htmlspecialchars($option['label'],ENT_COMPAT, 'UTF-8').'</option>';
                    }
                    $type = 'list';
                    $multiple = (!empty($field->attribute_multiple)) ? ' multiple="true"' : '' ;
                    $fieldstring = '<field name="F'. $field->id. '"'.
                        ' type="'.$type.'"'.
                        ' label="'.$field->label. '"'.
                        $multiple .
                        $required .
                        '>'.
                        $selectOptions .
                        ' </field>';
                    $fieldXml = new SimpleXMLElement($fieldstring);
                    $form->setField($fieldXml);
                    unset($options);
                    unset($selectOptions);
                    unset($type);
                    unset($multiple);
                    break;
                case 'radio':
                    $options = JHtmlVisformsselect::extractHiddenList($field->list_hidden);
                    $selectOptions = '';
                    foreach ($options as $option)
                    {
                        $selectOptions .= '<option value="' . htmlspecialchars($option['value'],ENT_COMPAT, 'UTF-8') . '">'. htmlspecialchars($option['label'],ENT_COMPAT, 'UTF-8') .'</option>';
                    }
                    $fieldstring = '<field name="F'. $field->id. '"'.
                        ' type="'.$field->typefield.'"'.
                        ' label="'.$field->label. '"'.
                        ' class="radio inline"' .
                        '>'.
                        $selectOptions .
                        $required .
                        ' </field>';
                    $fieldXml = new SimpleXMLElement($fieldstring);
                    $form->setField($fieldXml);
                    unset($options);
                    unset($selectOptions);
                    break;
                case 'multicheckbox':
                    $options = JHtmlVisformsselect::extractHiddenList($field->list_hidden);
                    $selectOptions = '';
                    foreach ($options as $option)
                    {
                        $selectOptions .= '<option class="checkbox inline" value="'.htmlspecialchars($option['value'],ENT_COMPAT, 'UTF-8').'">'.htmlspecialchars($option['label'],ENT_COMPAT, 'UTF-8').'</option>';
                    }
                    $type = 'checkboxes';
                    $maxlength = ((!empty($field->attribute_maxlength)) && ($field->attribute_maxlength > 1)) ? ' maxlength="'.$field->attribute_maxlength.'"' : '' ;
                    $fieldstring = '<field name="F'. $field->id. '"'.
                        ' type="'.$type.'"'.
                        ' label="'.$field->label. '"'.
                        $required .
					    //' filter="options"' .
                        $maxlength .
                        '>'.
                        $selectOptions .
                        ' </field>';
                    $fieldXml = new SimpleXMLElement($fieldstring);
                    $form->setField($fieldXml);
                    unset($options);
                    unset($selectOptions);
                    unset($type);
                    unset($maxlength);
                    break;
                case 'date':
                    $dateformat = '';
                    $format = (!empty($field->format)) ? explode(';', $field->format) : array();
                    if (isset($format[1]))
                    {
                        $dateformat = ' format="'.$format[1].'"';
                    }
                    $fieldstring = '<field name="F'. $field->id. '"'.
                        ' type="calendar"'.
                        ' label="'.$field->label. '"'.
                        $dateformat .
                        $required .
                        '/>';
                    $fieldXml = new SimpleXMLElement($fieldstring);
                    $form->setField($fieldXml);
                    break;
                default:
                    break;
            }
            unset($fieldstring);
            unset($fieldXml);
            unset($field);
            
        }
        $data = $this->loadFormData();
        $form->bind($data);
		return $form;
	}
    
    public function getDatafields()
    {
        $model = JModelLegacy::getInstance('Visdatas', 'VisformsModel', array('ignore_request' => true));
        $fielddefinition = $model->getDatafields();
        if (!empty($fielddefinition))
        {
            $count = count($fielddefinition);
            for ($i = 0; $i < $count; $i++)
            {
                $fielddefinition[$i] = $this->extractDefaultValueParams($fielddefinition[$i]);
            }
            return $fielddefinition;
        }
        return false;
    }
    
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        if (!empty($item))
        {
            $fields = $this->fielddefinition;
            foreach ($fields as $field)
            {
                $key = 'F'. $field->id;
                switch($field->typefield)
                {
                    
                    case 'select':
                    case 'multicheckbox' :
                        if (!empty($item->$key))
                        {
                            $item->$key = JHtmlVisformsselect::explodeMsDbValue($item->$key);
                        }
                        break;
                    /*case 'file':
                        if (!empty($item->$key))
                        {
                            $item->$key = JHtml::_('visforms.getUploadFileName', $item->$key);
                        }
                        break;*/
                    default:
                        break;
                }
                unset($key);
                unset($field);
            }
        }
        return $item;
    }
    
    protected function loadFormData()
	{	
		// Check the session for previously entered form data.
        $app = JFactory::getApplication();
		$data = $app->getUserState('com_visforms.edit.visdata.data', array());

		if (empty($data)) 
        {
			$data = $this->getItem();
		}

		return $data;
	}
        
    protected function extractDefaultValueParams($field)
    {

         foreach ($field->defaultvalue as $name => $value) 
         {
                 //make names shorter and set all default values as properties of field object
                 $prefix =  'f_' . $field->typefield . '_';
                 if (strpos($name, $prefix) !== false) {
                     $key = str_replace($prefix, "", $name);
                     $field->$key = $value;
                 }
         }            

         //delete defaultvalue array
         unset($field->defaultvalue);
         return $field;
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
	public function getTable($type = 'Visdata', $prefix = 'VisformsTable', $config = array())
	{	
		return JTable::getInstance($type, $prefix, $config);
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
        $fid = JFactory::getApplication()->input->getInt('fid', -1);

		// Check form settings.
		if ($fid != -1) 
        {
			return $user->authorise('core.edit.data.state', 'com_visforms.visform.' . (int) $fid);
		}
		// Default to component settings 
		else {
			return $user->authorise('core.edit.data.state', 'com_visforms');
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
        $fid = JFactory::getApplication()->input->getInt('fid', -1);
        $user = JFactory::getUser();

		// Check form settings.
		if ($fid != -1) 
        {
			return $user->authorise('core.delete.data', 'com_visforms.visform.' . (int) $fid);
		}
		else
		{
			//use component settings
            return $user->authorise('core.delete.data', 'com_visforms');
		}
	}
    
    public function setIsmfd($id, $state = true)
    {
        $table = $this->getTable();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update($db->quoteName($table->getTableName('name')))
            ->set($db->quoteName('ismfd') . ' = ' . $state )
            ->where($db->quoteName('id') . ' = ' . $id);
        $db->setQuery($query);
        $db->execute();
    }
    
    public function restoreToUserInputs($id)
    {
        if ($this->checkIsmfd ($id))
        {
            $table = $this->getTable();
            $tableName = $table->getTableName('name');
            $saveTableName = $tableName . "_save";
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('*')
                ->from($db->quoteName($saveTableName))
                ->where($db->quoteName('mfd_id') . ' = ' . $id);
            $db->setQuery($query);
            if ($orgData = $db->loadObject())
            {
                $orgData->id = $id;
                $fid = JFactory::getApplication()->input->get('fid', 0, 'int');
                $this->copyFiles($fid, $orgData, true);
                $this->deleteFiles(Joomla\Utilities\ArrayHelper::fromObject($orgData), true);
                unset($orgData->mfd_id);
                unset($orgData->published);
                $orgData->ismfd = false;
                $db->updateObject($tableName, $orgData, 'id');
            }
        }
    }
    
    public function copyOrgData($data)
    {
        $id = $data['id'];
        $ismfd = false;
        $table = $this->getTable();
        $tableName = $table->getTableName('name');
        $saveTableName = $tableName . "_save";
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName($tableName))
            ->where($db->quoteName('id') . ' = ' . $id);
        $db->setQuery($query);
        if ($orgData = $db->loadObject())
        {
            //check if data has really been modified
            foreach ($data as $dataname => $datavalue)
            {
                //only real formfield can be modified not the overhead fields. Fieldname of formfields in datatable starts with "F"
                if (($dataname === "" || strpos($dataname, "F") === 0) &&($datavalue !== $orgData->$dataname))
                {
                    $ismfd = true;
                    break;
                }
            }
            if (($ismfd == true) && ($orgData->ismfd == false))
            {
                //recordset is modified for the first time. We save the original user inputs in the save-table
                //move uploaded files to a save directory im necessary
                $fid = JFactory::getApplication()->input->getInt('fid', -1);
                $this->copyFiles($fid, $orgData);
                unset($orgData->id);
                $orgData->mfd_id = $id;
                $orgData->checked_out = 0;
                $orgData->checked_out_time = '0000-00-00 00:00:00';
                unset($orgData->ismfd);
                $db->insertObject($saveTableName, $orgData);
            }
        }
        return $ismfd;
    }
    
    public function deleteOrgData($id)
    {
        $table = $this->getTable();
        $saveTableName = $table->getTableName('name') . "_save";
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->delete($db->quoteName($saveTableName))
            ->where($db->quoteName('mfd_id') . ' = ' . $id);
        $db->setQuery($query);
        try
        {
            $db->execute();
        }
        catch (RuntimeException $e)
        {
            JError::raiseWarning(500, $e->getMessage);
           return false;
        }
        return true;
    }
    
    public function checkIsmfd ($id)
    {
        $table = $this->getTable();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('ismfd'))
            ->from($db->quoteName($table->getTableName('name')))
            ->where($db->quoteName('id') . ' = ' . $id);
        $db->setQuery($query);
        return $db->loadResult();
    }
    
    public function save($data)
	{
        $fields = $this->fielddefinition;
        foreach ($fields as $field)
        {
            if ((!empty($field->typefield)) && (in_array($field->typefield, array('select', 'multicheckbox'))))
            {
                $key = 'F'.$field->id;
                if ((!empty($data[$key])) && (is_array($data[$key])))
                {
                    $dbValue = implode(JHtmlVisformsselect::$msdbseparator, $data[$key]);
                    $data[$key] = $dbValue;
                }
                unset($key);
            }
            unset($field);
        }
		return parent::save($data);
	}
    
    public function uploadFiles($data)
	{
        $fields = $this->fielddefinition;
        $input = JFactory::getApplication()->input;
        $fid = $input->get('fid', 0, 'int');
        $formmodel = JModelLegacy::getInstance('Visform', 'VisformsModel', array('ignore_request' => true));
        $visform = $formmodel->getItem($fid);
        $folder = $visform->uploadpath;
        $uploadfields = array();
        foreach ($fields as $field)
        {
            $key = 'F'.$field->id;
            if ((!empty($field->typefield)) && ($field->typefield == 'file'))
            {
                //we have to check if a new file was selected and needs upload
                $uploadfield = new stdClass();
                $uploadfield->name = $key;
                $uploadfield->typefield = 'file';
                $uploadfields[] = $uploadfield; 
            }
            unset($key);
            unset($field);
            unset($uploadfield);
        }
        if (!empty($uploadfields))
        {
            $visform->fields = $uploadfields;
            try
            {
                $uploadsuccess = VisformsmediaHelper::uploadFiles($visform, 'admin');
            }
            catch (RuntimeException $e)
            {
                $msg = $e->getMessage();
                JFactory::getApplication()->enqueueMessage($msg, 'error');
            }
            
            foreach ($visform->fields as $uploadfield)
            {
                //set database value to empty if the file was marked as "to delete"
                $deleteFlagId = $uploadfield->name. '-filedelete';
                if (!empty($data[$deleteFlagId]))
                {
                    $data[$uploadfield->name] = "";
                }
                //store path and file information in database if a new file was uploaded
                if (!empty($uploadfield->file['new_name']))
                {
                     $file = new stdClass();
                     $file->folder = $folder;
                     $file->file = $uploadfield->file['new_name'];
                     $registry = new JRegistry($file);
                     $data[$uploadfield->name] = $registry->toString();
                }
                unset($uploadfield);
            }
            
        }
		return $data;
	}
    
    public function deleteFiles($data, $restore = false)
    {
        if (empty($data) || (!is_array($data)))
        {
            return false;
        }
        if ((empty($this->fielddefinition)) || (!is_array($this->fielddefinition)))
        {
            return false;
        }
        $item = $this->getItem($data['id']);
        if (empty($item))
        {
            return $data;
        }
        foreach ($this->fielddefinition as $fielddefinition)
        {
            $deleteFlagId = "F" . $fielddefinition->id. '-filedelete';
            $fieldkey = "F" . $fielddefinition->id;
            if (((empty($restore)) && (!empty($data[$deleteFlagId])) && ($data[$deleteFlagId] == 'delete') && ($fielddefinition->typefield == 'file'))
                || ((!empty($restore)) && ($data[$fieldkey] != $item->$fieldkey) && ($fielddefinition->typefield == 'file')))
            {
                
                $path = JHtml::_('visforms.getUploadFilePath', $item->$fieldkey);
                if (!empty($path))
                {                  
                    VisformsmediaHelper::deletefile($path);
                }                
            }
        }
        return $data;
    }
    
    //if restore is set to true, we restsore original data and move file from the save folder to the original folder
    private function copyFiles($formid, $data, $restore = false)
    {
        if ((empty($formid)) || (empty($data)) || empty($data->id))
        {
            return true;
        }
        if ((empty($this->fielddefinition)) || (!is_array($this->fielddefinition)))
        {
            return false;
        }
         foreach ($this->fielddefinition as $fielddefinition)
        {
            if ($fielddefinition->typefield == 'file')
            {
                $fieldkey = "F" . $fielddefinition->id;
                $filename = JHtml::_('visforms.getUploadFileName', $data->$fieldkey);
                $path = JHtml::_('visforms.getUploadFilePath', $data->$fieldkey);
                if ((!empty($path)) && (!empty($filename)))
                {
                    VisformsmediaHelper::copyfile($filename, $path, $restore);
                }
            }
        }
    }
}