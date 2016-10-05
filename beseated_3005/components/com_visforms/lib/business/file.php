<?php
/**
 * Visforms field file business class
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Perform business logic on field file
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsBusinessFile extends VisformsBusiness
{  
    /**
    * Public method to get the field object
    * @return object field
    */
   public function getFields()
    {
        $this->setField();
        return $this->fields;
    }

    /**
    * Process business logic on field
    */
   protected function setField()
    {
       $this->setIsDisabled();
       if (isset($this->field->dataSource) && $this->field->dataSource == 'post')
       {
            $this->validatePostValue();
       }
       $this->addShowWhenForForm();
       $this->setFileInfo();
    }
    
    /**
     * Method to validate values set by post according to business logic
     * Invalid post values can have effects on the disabled state of other fields
     * Therefor we do not validate for required yet!
     */
    protected function validatePostValue()
    {
        //upload fields do not have a post value
        return true;
    }
    
    /**
     * Methode to validate if a post value is set in field, if we deal with a post and the field is required and not disabled
     * @return object field
     */
    public function validateRequired()
    {
        if (isset($this->field->dataSource) && $this->field->dataSource == 'post')
        {
            $app = JFactory::getApplication();

            //check that a value is set if field is required
            if ((isset($this->field->attribute_required)) && ($this->field->attribute_required == true))
            {
                
                if ((!(isset($this->field->isDisabled))) || ($this->field->isDisabled === false))
                {
                    //if we are in the data edit modus we assume that a required file upload field has a value and we only have to make sure that a new file is uploaded if the old one is deleted
                    $deleteFlagId = $this->field->name. '-filedelete';
                    $deleteFlagValue = $app->input->get($deleteFlagId);
                    if ((empty($this->field->recordId)) || (!empty($deleteFlagValue)))
                    {
                        if ((isset($_FILES[$this->field->name]['name']) === false) || (isset($_FILES[$this->field->name]['name']) && $_FILES[$this->field->name]['name'] ==''))
                        {
                            $this->field->isValid = false;
                            $error = JText::sprintf('COM_VISFORMS_FIELD_REQUIRED_UPLOAD', $this->field->label);
                            $this->setErrors($error);
                        }
                    }
                }
            }
        }
        return $this->field;
    }
    //if we are editing record set, we gather information of the old file and store it with the field, in order that we can display file information in edit form
    protected function setFileInfo()
    {
       if (empty($this->field->recordId))
       {
          return;
       }
       $key = 'F' . $this->field->id;
       $tablename ='#__visforms_' . $this->form->id;
       $db = JFactory::getDbo();
       $query = $db->getQuery(true);
       $query->select($db->qn($key))
           ->from($db->qn($tablename))
           ->where($db->qn('id') . ' = ' . $this->field->recordId);
       $db->setQuery($query);
       try
       {
            $dbValue = $db->loadResult();
       }
       catch (RuntimeException $e)
       {
           return;
       }
       if (empty($dbValue))
       {
           return;
       }
       $file = VisformsmediaHelper::getFileInfo($dbValue);
       if (!empty($file))
       {
            $this->field->orgfile = $file;
       }
       return;
    }
}
