<?php
/**
 * Visforms field radio business class
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
 * Perform business logic on field radio
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsBusinessRadio extends VisformsBusiness
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
    }
    
    /**
     * Method to validate values set by post according to business logic
     * Invalid post values can have effects on the disabled state of other fields
     * Therefor we do not validate for required yet!
     */
    protected function validatePostValue()
    {
        //validate unique field value in database
        $this->validateUniqueValue();
    }
    
    /**
     * Methode to validate if a post value is set in a required field, if we deal with a post and the field is required and not disabled
     * @return object field
     */
    public function validateRequired()
    {
        if (isset($this->field->dataSource) && $this->field->dataSource == 'post')
        {
            $app = JFactory::getApplication();
        
            //check that on option is selected if field is required        
            if ((isset($this->field->attribute_required)) && ($this->field->attribute_required == true))
            {
                //only for fields that are not disabled
                if (!isset($this->field->isDisabled) || ($this->field->isDisabled === false))
                {
                    $optionSelected = false;
                    foreach ($this->field->opts as $opt)
                    {
                        if (isset($opt['selected']) && ($opt['selected'] == true))
                        {
                            $optionSelected = true;
                            break;
                        }
                    }
                    if ($optionSelected == false)
                    {
                        $this->field->isValid = false;
                        $error = JText::sprintf('COM_VISFORMS_FIELD_REQUIRED_RADIO_SELECT', $this->field->label);
                        //attach error to form
                        $this->setErrors($error);
                    }
                }
            }
        }
        return $this->field;
    }
    
    protected function validateUniqueValue()
    {
		$valid = parent::validateUniqueValue();
        if (empty($valid))
        {
            $this->disableOption();
        }
    }
    
    protected function disableOption()
    {
        $value = $this->field->dbValue;
        $ocount = count($this->field->opts);
        {
            for ($j=0; $j < $ocount; $j++)
            {
                if ($this->field->opts[$j]['value'] === $value)
                {
                    $this->field->opts[$j]['disabled'] = true;
                    $this->field->opts[$j]['selected'] = false;
                }
            }
        }
    }

}