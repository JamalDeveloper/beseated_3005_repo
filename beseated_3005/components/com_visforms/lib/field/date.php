<?php
/**
 * Visforms field date class
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
require_once(__DIR__ . '/text.php');

/**
 * Visforms field date
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsFieldDate extends VisformsFieldText
{  
    
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
        $this->addDateFormatsToField();
        $this->setFieldDefaultValue();
        $this->setDbValue();
        $this->setRedirectParam();
    }
    
    /**
     * The the default value of the field which is displayed in the form according to field defition, query params, user inputs
     */
    
    protected function setFieldDefaultValue()
    {
        $field = $this->field;
        if ($this->input->getCmd('task', '') == 'editdata')
        {
            $this->validateUserInput('editValue');
            if (isset($this->editValue))
            {
                $this->field->attribute_value = $this->editValue;
            }
            else
            {
                $this->field->attribute_value = "";
            }
            $this->field->dataSource = 'db';
            return;
        }
        //if we have a POST Value, we use this
        if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id))
        {
            $valid = $this->validateUserInput('postValue');
            if (isset($_POST[$field->name]) && ($valid === true))
            {
                $this->field->attribute_value = $this->postValue;
            }
            else
            {
                $this->field->attribute_value = "";
            }
            $this->field->dataSource = 'post';
            return;
        }
        
        //if we have a GET Value and field may use GET values, we uses this
        if (isset($field->allowurlparam) && ($field->allowurlparam == true) && isset($this->queryValue) && !(is_null($this->queryValue)))
        {
            if ($valid = $this->validateUserInput('queryValue'))
            {
                $this->field->attribute_value = $this->queryValue;
            }
            else
            {
                $this->field->attribute_value = "";
            }
            $this->field->dataSource = 'query';
            return;
        }
        //if we have a special default value set in field declaration we use this
        if (strcmp($field->attribute_value,'') == 0 && (isset($field->daydate) && strcmp($field->daydate,'1') == 0)) 
        {
            $this->field->attribute_value = JHtml::_('date', 'now', $field->dateFormatPhp);
        }
        //Nothing to do
        return;
    }
    
    /**
     * 
     * Method to validate user inputs, if not: set field property isValid to false and set error message
     * @param string $inputType user input type (postValue, queryValue)
     */
    protected function validateUserInput($inputType)
    {
        $type = $this->type;
        $value = $this->$inputType;
        //Empty value is valid
        if ($value == "")
        {
            return true;
        }
        //if a value is set we test it has a valid date format
        if (VisformsValidate::validate($type, array('value' => $value, 'format' => $this->field->dateFormatPhp)))
        {
            return true;
        }
        else
        {
            //invalid date format - set field->isValid to false
            $this->field->isValid = false;
            //get the Error Message
            $error = VisformsMessage::getMessage($this->field->name, $type, array('format' => $this->field->dateFormatPhp));
            $this->setErrors($error);             
            //we cannot use the value!            
            return false;
        }
    }
    
    /**
     * Method to add date format properties to date field
     */
    private function addDateFormatsToField() 
    {
        $this->field->dateFormatPhp = '';
        $this->field->dateFormatJs = '';
        if (isset($this->field->format)) 
        {
            // get dateformat for php and for javascript	
            $dformat = explode(";", $this->field->format);
            if (count($dformat) == 2) 
            {
                $this->field->dateFormatPhp = $dformat[0];
                $this->field->dateFormatJs = $dformat[1];
            }
        }
    }
}