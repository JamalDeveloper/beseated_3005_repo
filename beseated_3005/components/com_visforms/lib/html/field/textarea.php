<?php
/**
 * Visforms HTML class for textarea fields
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
 * Create HTML of a textarea field according to it's type
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsHtmlTextarea extends VisformsHtml
{     
    /**
    * Method to create the field attribute array
    * @return array Html tag attributes for field
    */
   public function getFieldAttributeArray()
   {
       $attributeArray = array('class' => '');
        //attributes are stored in xml-definition-fields with name that ends on _attribute_attributename (i.e. _attribute_checked).
        //each form field is represented by a fieldset in xml-definition file 
        //each form field should have in xml-definition file a field with name that ends on _attribute_class. default " " or class-Attribute values for form field 
        foreach ($this->field as $name => $value) 
        {
            if (!is_array($value))
            {
                if (strpos($name, 'attribute_') !== false) 
                {
                    if ($value || $name == 'attribute_class') 
                    {
                        $newname = str_replace('attribute_', "", $name);
                        if ($newname == "class") 
                        {
                            $value =  $value . $this->field->fieldCSSclass;
                            if ((isset($this->field->textareaRequired) && $this->field->textareaRequired === true) || (isset($this->field->hasHTMLEditor) && $this->field->hasHTMLEditor == true)) 
                            {
                                    $value = "mce_editable";
                            }
                        }
                        $attributeArray[$newname] = $value;  
                    }
                }
                if ($name == 'name')
                {
                    $attributeArray['name'] = $value;                      
                }

                if ($name == 'id')
                {
                    $value = 'field' . $value;
                    $attributeArray['id'] = $value;
                }

                if ($name == 'attribute_required')
                {
                    $attributeArray['aria-required'] = 'true';
                }
                if (($name == 'isDisabled') && ($value == true))
                { 
                    $attributeArray['class'] .= " ignore";
                    $attributeArray['disabled'] = "disabled";
                }

                if (($name == 'isDisplayChanger') && ($value == true))
                {
                    $attributeArray['class'] .= " displayChanger";
                }
                if (($name == 'isValid') && ($value == false))
                {
                    $attributeArray['class'] .= " error";
                }
                if (isset($this->field->hasHTMLEditor) && $this->field->hasHTMLEditor) 
                { 
                    //set some special attaribute for the textarea that is linked to the editor
                    $attributeArray['style'] = "width: 97%; height: 200px;";
                }
                if (!isset($attributeArray['cols']) || $attributeArray['cols'] == "")
                {
                    $attributeArray['cols'] = "10";
                }
                if (!isset($attributeArray['rows']) || $attributeArray['rows'] == "")
                {
                    $attributeArray['rows'] = "20";
                }
                $attributeArray['aria-labelledby'] = $this->field->name . 'lbl';
            }               
        }
        return $attributeArray;
   }
}