<?php
/**
 * Visforms field checkbox class
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
 * Visforms field checkbox
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsFieldCheckbox extends VisformsField
{
    /**
     * 
     * Constructor
     * 
     * @param object $field field object as extracted from database
     * @param object $form  form object as extracted from database
     */
    
    public function __construct($field, $form)
    {
        parent::__construct($field, $form);
        $this->queryValue = $this->input->get->get($field->name, null, 'STRING');
        //unchecked checkboxes are not send with post
        //checkboxes always have a value different from empty string (enforced in field configuration)
        //we set unchecked chechboxes in postValue with empty string
        $this->postValue = $this->input->post->get($field->name, '', 'STRING');
        $this->editValue = $this->input->post->get('F'.$field->id, null, 'STRING');
    }
    
    /**
     * Public method to get the field object
     * @return object VisformsFieldText
     */
    
    public function getField()
    {
        $this->setField();
        return $this->field;
    }
    
    /**
     * Preprocess field. Set field properties according to field defition, query params, user inputs
     */
    
    protected function setField()
    {
        //preprocessing field
        $this->extractDefaultValueParams();
        $this->extractRestrictions();
        $this->mendBooleanAttribs();
        $this->setIsConditional();
        $this->setIsDisplayChanger();
        $this->setFieldDefaultValue();
        $this->setDbValue();
        $this->setRedirectParam();
    }
    
    /**
     * The the default value of the field which is displayed in the form according field defition, query params, user inputs
     */
    
    protected function setFieldDefaultValue()
    {
        $field = $this->field;
        if ($this->input->getCmd('task', '') == 'editdata')
        {
            if (!empty($this->editValue))
            {
                $this->field->attribute_checked = "checked";
                $this->field->dataSource = 'db';
                return;
            }
        }
        //if we have a POST Value, we use this
        if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id))
        {
            $valid = $this->validateUserInput('postValue');
            if ((isset($_POST[$field->name])) && ($valid === true))
            {
                $this->field->attribute_checked = "checked";
            }
            
            $this->field->dataSource = 'post';
            return;
        }
        
        //if we have a GET Value and field may use GET values, we uses this
        if (isset($field->allowurlparam) && ($field->allowurlparam == true) && isset($this->queryValue) && !(is_null($this->queryValue)))
        {
            $this->field->attribute_checked = "checked";
            $this->field->dataSource = 'query';
            return;
        }
        //Nothing to do
        return;
    }
    
    /**
     * Method to convert post values into a string that can be stored in db and attach it as property to the field object
     */
    protected function setDbValue()
    {
        if (isset($this->field->dataSource) && $this->field->dataSource == 'post')
        {
            $this->field->dbValue = $this->postValue;
        }
    }
    
   /**
     * Method to check, that user inputs are valid option values
     */
	protected function validateUserInput($inputType)
	{
        //value set by user
        $value = $this->$inputType;
        
        //Empty value is valid
        if ($value == "")
        {
            return true;
        }

        //is there a value set by user which is not allowed?
        if ($value !== $this->field->attribute_value)
        {
            //we have an invalid user input
            $this->field->isValid = false;
            //attach error to form
            $error = JText::sprintf('COM_VISFORMS_OPTION_HAS_INVALID_POST_VALUE', $this->field->label);
            $this->setErrors($error);
            //remove value from $this->$inputType
            $this->$inputType = "";
            return false;
        }

       return true;
    }
    
    protected function setRedirectParam()
    {
        if (isset($this->field->dataSource) && $this->field->dataSource == 'post' && (!empty($this->field->addtoredirecturl)))
        {
            // checkbox was not checked
            if (empty($this->postValue))
            {
                return;
            }
            $this->field->redirectParam = $this->postValue;
        }
    }
}