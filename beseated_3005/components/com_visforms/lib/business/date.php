<?php
/**
 * Visforms field date business class
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
 * Perform business logic for date field
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsBusinessDate extends VisformsBusinessText
{     
     /**
     * Method to validate values set by post according to business logic
     * Invalid post values can have effects on the disabled state of other fields
     * Therefor we do not validate for required yet!
     */
     protected function validatePostValue()
     {
        //rules for date are: equalTo
        
        //update $this->field with value from $this->fields
        $this->updateField();
        
        $valid = true;
        $app = JFactory::getApplication();       
        //only to perform when the value is not empty
        if ($this->field->attribute_value != "")
        {
            //perform equalTo validation
            if ((isset($this->field->validate_equalTo)) && ($this->field->validate_equalTo != '0'))
            {
                $value = $this->field->attribute_value;
                $id = str_replace("#field", "", $this->field->validate_equalTo);

                foreach ($this->fields as $equalToField)
                {
                    if ($equalToField->id == $id)
                    {
                        if (VisformsValidate::validate('equalto', array('value' => $value, 'cvalue' => $equalToField->attribute_value)) == false)
                        {
                            //invalid value
                            $valid = false;
                            //attach error to form
                            $error = JText::sprintf('COM_VISFORMS_EQUAL_TO_VALIDATION_FAILED', $equalToField->label, $this->field->label);
                            $this->setErrors($error);
                            break;
                        }
                    }
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
    }
}