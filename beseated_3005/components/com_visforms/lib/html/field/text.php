<?php
/**
 * Visforms HTML class for text fields
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
 * Create HTML of a text field according to it's type
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsHtmlText extends VisformsHtml
{
    /**
     * 
     * Constructor
     * 
     * @param object $field field object as extracted from database
     */
    public function __construct($field, $decorable, $attribute_type)
    {
        if (is_null($attribute_type))
        {
            $attribute_type = "text";
        }
        parent::__construct($field, $decorable, $attribute_type);
    }
    
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
                    if ($value || $name == 'attribute_class' || ($name== 'attribute_min' && $value == 0) || ($name== 'attribute_value' && $value == 0)) 
                    {
                        $newname = str_replace('attribute_', "", $name);
                        if ($newname == "class") 
                        {
                            $value =  $value . $this->field->fieldCSSclass; 
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
                $attributeArray['aria-labelledby'] = $this->field->name . 'lbl';
            }               
        }
        return $attributeArray;
   }
}