<?php
/**
 * Visforms field textarea business class
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
 * Perform business logic on field textarea
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsBusinessTextarea extends VisformsBusiness
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
       $this->setDbValue();
       $this->addShowWhenForForm();
    }
    
    /**
     * Method to validate values set by post according to business logic
     * Invalid post values can have effects on the disabled state of other fields
     * Therefor we do not validate for required yet!
     */
    protected function validatePostValue()
    {
        //rules for textarea are: minlength, maxlength, unique value
        
        //update $this->field with value from $this->fields
        $this->updateField();
        
        $app = JFactory::getApplication();
        $valid = true;
        
        //only to perform when the value is not empty
        if ($this->field->initvalue != "")
        {
            //check for right minlength
            if ((isset($this->field->validate_minlength)) && ($this->field->validate_minlength != ''))
            {
                $mincount = $this->field->validate_minlength;
                $count = strlen($this->field->initvalue);
                if (VisformsValidate::validate('min', array('count' => $count, 'mincount' => $mincount)) == false)
                {
                    //invalid value
                    $valid = false;
                    $error = JText::sprintf('COM_VISFORMS_FIELD_MIN_LENGTH', $this->field->label, $mincount);
                    //attach error to form
                    $this->setErrors($error);
                }
            }

            //check for right maxlength
            if ((isset($this->field->attribute_maxlength)) && ($this->field->attribute_maxlength != ''))
            {
                $maxcount = $this->field->attribute_maxlength;
                $count = strlen($this->field->initvalue);
                if (VisformsValidate::validate('max', array('count' => $count, 'maxcount' => $maxcount)) == false)
                {
                    //invalid value
                    $valid = false;
                    $error = JText::sprintf('COM_VISFORMS_FIELD_MAX_LENGTH', $this->field->label, $maxcount);
                    //attach error to form
                    $this->setErrors($error);
                }
            }
            //validate unique field value in database
            $this->validateUniqueValue();
        }
        
        //at least one validation failed
       if (!$valid)
       {
           $this->field->isValid = false;
       }
       //push field back in fields array
       //$this->updateFieldsArray($this->field);
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
                if (!(isset($this->field->isDisabled)) || ($this->field->isDisabled === false))
                {
                    if (VisformsValidate::validate('notempty', array('value' => $this->field->initvalue)) == false)
                    {
                        $this->field->isValid = false;
                        $error = JText::sprintf('COM_VISFORMS_FIELD_REQUIRED', $this->field->label);
                        //attach error to form
                        $this->setErrors($error);
                    }
                }
            }
        }
        return $this->field;
    }
    
    /**
     * Method to convert post values into a string that can be stored in db and attach it as property to the field object
     */
    protected function setDbValue()
    {
        $this->field->dbValue = $this->field->initvalue;
    }
}