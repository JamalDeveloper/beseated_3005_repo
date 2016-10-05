<?php
/**
 * Visforms HTMLLayout class 
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
 * Set properties of a form field according to it's type and layout settings
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
abstract class VisformsHtmllayout
{
    /**
	 * layout type
	 *
	 * @var    string
	 * @since  11.1
	 */
         protected $type;
       
     /**
	 * $fieldHtml html class
	 *
	 * @var    object
	 * @since  11.1
	 */
         protected $fieldHtml;
         
    /**
	 * $field
	 *
	 * @var    object
	 * @since  11.1
	 */
         protected $field;
     
    /**
     * 
     * Constructor
     * 
     * @param string $type layout type
     * @param VisformsHtml $field instance of field html class
     * @param array $option additionals layout options
     */
       public function __construct($type, VisformsHtml $fieldHtml)
       {
           $this->type = $type;
           $this->fieldHtml = $fieldHtml;
           $this->field = $fieldHtml->getField();
           $this->fieldtype = $fieldHtml->getFieldType();
       }
       
       /**
        * Method to get the right instance of HTMLlayout class
        * @param string $type layout type
        * @param VisformsHtml $field instance of field html class
        * @param array $option additionals layout options
        * @return \classname|boolean
        */
       public static function getInstance($type = 'visforms', $fieldHtml)
       {           
           $classname = get_called_class() . ucfirst($type);
           if (!class_exists($classname))
           {
               //try to register it
               JLoader::register($classname, dirname(__FILE__) . '/html/layout/'. $type . '.php');
               if (!class_exists($classname))
               {
                    //return a default class?
                    return false;
               }
           }
           //delegate to the appropriate subclass
           return new $classname($type, $fieldHtml);
       }
       
       abstract public function prepareHtml();
       abstract protected function setFieldControlHtml();
      
       /**
        * Method to set the property validateArray in field
        *  
        */
       protected function setFieldValidateArray()
       {
           $this->field = $this->fieldHtml->setFieldValidateArray($this->field);
           //only a view field types (at the moment the date type)have individual Validations, attach those rules
           if (method_exists($this->fieldHtml, 'setFieldCustomValidateArray'))
           {
               $this->field = $this->fieldHtml->setFieldCustomValidateArray($this->field);
           }         
       }
       
       /**
        * Method to get custom error messages, used in javascript validation, as an array and attach the array to the field
        */
       protected function setFieldCustomErrorMessageArray()
       {
            //validation rules are stored in xml-definition-fields with name that ends on _validate_rulename (i.e. _validate_minlength).
            //each form field is represented by a fieldset in xml-definition file 
            if(isset($this->field->customerror) && $this->field->customerror != "")
            {
                foreach ($this->field as $name => $value) 
                {
                    $attributes = array("maxlength", "min", "max", "required");
                    $types = array("email", "url", "date", "number");
                    if (!is_array($value))
                    {
                        if ($value)
                        {
                            if (strpos($name, 'validate') !== false) 
                            {
                                $name = str_replace('validate_', "", $name);
                                $this->field->customErrorMsgArray[$name] = $this->field->customerror;
                            }
                            if (strpos($name, 'attribute_') !== false) 
                               {
                                $name = str_replace('attribute_', "", $name);
                                if (in_array($name, $attributes)) 
                                {
                                      $this->field->customErrorMsgArray[$name] = $this->field->customerror;
                                }
                            }

                            $name = $this->field->typefield;
                            if (in_array($name, $types)) {
                                $this->field->customErrorMsgArray[$name] = $this->field->customerror;
                            }
                        }
                    }
                }
            }
       }
       
       /**
        * set property errorId in field
        * get string from field html class
        */
       protected function setErrorId ()
       {
           $this->field->errorId = $this->fieldHtml->getErrorId($this->field);
       }
       
       /**
        * set property attributeArray in field
        * get array from field html class
        */
       protected function setFieldAttributeArray()
       {
           $this->field->attributeArray = $this->fieldHtml->getFieldAttributeArray();
       }
       
       /**
        * Method to handle uneditable fields
        *  
        */
       protected function makeFieldUneditable()
       {
           if (isset($this->field->isForbidden) && ($this->field->isForbidden == true))
           {
                // a view field types need custom care
                if (method_exists($this->fieldHtml, 'makeFieldUneditable'))
                {
                    $this->field = $this->fieldHtml->makeFieldUneditable($this->field);
                }
                else
                {
                    //set a readonly attribute
                    $this->field->attribute_readonly = 'readonly';
                }
            }
       }
}