<?php
/**
 * Visforms HTML class for fields
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

//load control html classes
JLoader::discover('VisformsHtml', dirname(__FILE__) . '/html/control/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControl',dirname(__FILE__) . '/html/control/decorator/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlDecorator', dirname(__FILE__) . '/html/control/decorator/decorators/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlVisforms',dirname(__FILE__) . '/html/control/default/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlBtdefault',dirname(__FILE__) . '/html/control/btdefault/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlBthorizontal',dirname(__FILE__) . '/html/control/bthorizontal/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlMcindividual',dirname(__FILE__) . '/html/control/mcindividual/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEdit',dirname(__FILE__) . '/html/control/edit/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEditbtdefault',dirname(__FILE__) . '/html/control/editbtdefault/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEditbthorizontal',dirname(__FILE__) . '/html/control/editbthorizontal/', $force = true, $recurse = false);
JLoader::discover('VisformsHtmlControlEditmcindividual',dirname(__FILE__) . '/html/control/editmcindividual/', $force = true, $recurse = false);

/**
 * Create HTML of a form field according to it's type
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
abstract class VisformsHtml
{
    /**
	 * The field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
       protected $type;
       
     /**
	 * Field.
	 *
	 * @var    object
	 * @since  11.1
	 */
     protected $field;
         
    /**
	 * Decorable
	 *
	 * @var    boolean
	 * @since  11.1
	 */
      protected $decorable;
      
      /**
       * Input attribute type value
       * @var string 
       */
      protected $attribute_type;
     
    /**
     * 
     * Constructor
     * 
     * @param object $field field object as extracted from database
     */
       public function __construct($field, $decorable, $attribute_type)
       {
           $this->type = $field->typefield;
           $this->field = $field;
           $this->setDecorable($decorable);
           $this->setAttributeType($attribute_type);
           $this->setAttributePlaceholder();
           
       }
       
       /**
        * Factory to create instances of field objects according to their type
        * 
        * @param object $field
        * @return \classname|boolean
        */
       
       public static function getInstance($field, $decorable = null)
       {
            if (!(isset($field->typefield)))
            {
                return false;
            }
           
           $classname = get_called_class() . ucfirst($field->typefield);
           if (!class_exists($classname))
           {
               //try to register it
               JLoader::register($classname, dirname(__FILE__) . '/html/field/'. $field->typefield . '.php');
               if (!class_exists($classname))
               {
                    //return a default class?
                    return false;
               }
           }
           //delegate to the appropriate subclass
           return new $classname($field, $decorable, $attribute_type = null);
       }
       
       abstract public function getFieldAttributeArray();
   
       /**
        * 
        * @param object $field
        * @return modified field object
        */
       public function setFieldValidateArray ($field)
       {
           $validateArray = array();
           //validation rules are stored in xml-definition-fields with name that ends on _validate_rulename (i.e. _validate_minlength).
            //each form field is represented by a fieldset in xml-definition file 
            foreach ($field as $name => $value) 
            {

                if (!is_array($value))
                {
                    if (strpos($name, 'validate') !== false) 
                    {
                        if ($value) {
                            $newname = str_replace('validate_', "", $name);
                            $validateArray[$newname] = $value;                        
                        }
                    }
                    //user can use custom regex for custom field validation
                    //we have to create a custom validation method, create an entry in the validators rules section and the addMethod itself
                    if (strpos($name, 'customvalidation') !== false)
                    {
                        if ($value)
                        {
                            //information to create entry in validators rules section
                            $validateArray['cv'. $field->id] = 'true';
                            //information to create the addMethod() which is the method that performs the custom validation
                            $field->addMethod['methodname'] = 'cv'. $field->id; 
                            $field->addMethod['regex'] = $value;
                        }
                    }
                }
            }
            if (count($validateArray) > 0)
            {
                $field->validateArray = $validateArray;
            }
            return $field;
       }
       
       /**
        * 
        * @return string field type
        */
       public function getFieldType ()
       {
           return $this->type;
       }
       
       /**
        * 
        * @return object field
        */
       public function getField ()
       {
           return $this->field;
       }
       
       /**
        * 
        * @param object $field field object
        * @return string errorId
        */
       public function getErrorId($field)
       {
           return 'field' . $field->id;
       }
       
       /**
       * Methode to set decorable state
       * @param boolean $decorable
       */
       public function setDecorable($state)
       {
           if (is_null($state))
           {
                if (!(isset($this->decorable)))
                {
                    $this->decorable = true;
                }
           }
           else
           {
               $this->decorable = $state;
           }
       }
       
       /**
        * Methode to get decorable state
        * @return $decorable
        */
       public function getDecorable()
       {  
            return $this->decorable;
       }
       
       /**
       * Methode to set property attribute_type to field
       * @param string $type
       */
       protected function setAttributeType($type)
       {
           if (!is_null($type))
           {
               $this->field->attribute_type = $type;
           }         
       }
       
       /**
       * Methode to set field label text as placeholder if the label is hidden
       * @param boolean 
       */
       protected function setAttributePlaceholder()
       {
           //show label is set to hide
           if(isset($this->field->show_label) && ($this->field->show_label == 1))
           {
               //no placeholder available for field
               if (isset($this->field->attribute_placeholder) && ($this->field->attribute_placeholder == ""))
               {
                   //set label text into placeholder
                   if (isset($this->field->label))
                   {
                        $this->field->attribute_placeholder = $this->field->label;
                   }
               }
           }
       }
}