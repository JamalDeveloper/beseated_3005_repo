<?php
/**
 * Visforms field class
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
 * Set properties of a form field according to it's type
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
abstract class VisformsField
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
	 * Form.
	 *
	 * @var    object
	 * @since  11.1
	 */
         protected $form;
       
     /**
	 * The field value.
	 *
	 * @var    mixed
	 * @since  11.1
	 */
       protected $value;
       
    /**
	 * The default value for field set as Url param.
	 *
	 * @var    mixed
	 * @since  11.1
	 */
       protected $queryValue;
       
    /**
	 * The field value submitte in POST
	 *
	 * @var    mixed
	 * @since  11.1
	 */
       protected $postValue;
       
     /**
	 * Input from request.
	 *
	 * @var    object
	 * @since  11.1
	 */
         protected $input;
         
     /**
	 * Attributs of control HTML Element (evtl. eher in die html Klasse, noch nicht verwendet).
	 *
	 * @var    string
	 * @since  11.1
	 */
         protected $controlAttribs;
     
    /**
     * 
     * Constructor
     * 
     * @param object $field field object as extracted from database
     * @param object $form  form object as extracted from database
     */
       public function __construct($field, $form)
       {
           $this->type = $field->typefield;
           $this->field = $field;
           $this->form = $form;
           $this->input = JFactory::getApplication()->input;
       }
       
       /**
        * Factory to create instances of field objects according to their type
        * 
        * @param object $field
        * @param object $form
        * @return \classname|boolean
        */
       
       public static function getInstance($field, $form)
       {
            if (!(isset($field->typefield)))
            {
                return false;
            }
           
           $classname = get_called_class() . ucfirst($field->typefield);
           if (!class_exists($classname))
           {
               //try to register it
               JLoader::register($classname, dirname(__FILE__) . '/field/'. $field->typefield . '.php');
               if (!class_exists($classname))
               {
                    //return a default class?
                    return false;
               }
           }
           //delegate to the appropriate subclass
           return new $classname($field, $form);
       }
       
       /**
       * Public method to get the field object
       * @return object VisformsField
       */
       abstract public function getField();
       
       /**
       * Preprocess field. Set field properties according to field definition, query params, user inputs
       */
       abstract protected function setField();
       
       /**
       * Set the default value of the field which is displayed in the form according field definition, query params, user inputs
       */
       abstract protected function setFieldDefaultValue();


       /**
        * Method to extract registry strings into field properties
        */
       
       protected function extractDefaultValueParams()
       {
            $registry = new JRegistry;
            $registry->loadString($this->field->defaultvalue);
            $this->field->defaultvalue = $registry->toArray();

            foreach ($this->field->defaultvalue as $name => $value) 
            {
                    //make names shorter and set all default values as properties of field object
                    $prefix =  'f_' . $this->field->typefield . '_';
                    if (strpos($name, $prefix) !== false) {
                        $key = str_replace($prefix, "", $name);
                        $this->field->$key = $value;
                    }
            }            

            //delete defaultvalue array
            unset($this->field->defaultvalue);
       }
       
       /**
        * Method to turn all possible boolean "true" values for HTML-Attributes into a proper string
        */
       protected function mendBooleanAttribs()
       {
            $attribs = array('required', 'readonly');
            foreach ($attribs as $attrib)
            {
                $attribname = 'attribute_' . $attrib;
                if (isset($this->field->$attribname) && ($this->field->$attribname == 'required' || $this->field->$attribname == '1' || $this->field->$attribname == true))
                 {
                     $this->field->$attribname = $attrib;

                 }
            }
       }
       
       /**
        * Methode to extract registry string restrictions into an array
        */
       protected function extractRestrictions()
       {
           $registry = new JRegistry;
           $registry->loadString($this->field->restrictions);
           $this->field->restrictions = $registry->toArray();
       }
       
       /**
        * Add boolean property isConditinal to field
        * @return boolean true 
        */
       protected function setIsConditional()
       {
            foreach ($this->field as $name => $avalue) 
            {
                if (strpos($name, 'showWhen') !== false)
                {
                    //as there can be more than one restrict, restricts are stored in an array
                    if (is_array($avalue) && (count($avalue) > 0))
                    {
                        foreach ($avalue as $value)
                        {
                            //if we have at least on restict with a field there is a condition set
                            if (preg_match('/^field/', $value) === 1)
                            {
                                $this->field->isConditional = true;
                                return true;
                            }
                        }
                    }
                }

            }
            $this->field->isConditional = false;
            return true;
       }
       
       /**
        * Add property isDisplayChanger to field
        */
       protected function setIsDisplayChanger()
       {
            if (isset($this->field->restrictions) && (is_array($this->field->restrictions)))
            {
                //loop through restrictions and check that there is at least one usedAsShowWhen restriction
                if (array_key_exists('usedAsShowWhen', $this->field->restrictions))
                {
                    $this->field->isDisplayChanger = true;
                }
            }
       }
       
      protected function setErrors($error)
      {
          if (!(isset($this->form->errors)))
          {
              $this->form->errors = array();
          }
          if (is_array($this->form->errors))
          {
              array_push($this->form->errors, $error);
          }
      }
       
      /**
      * Method to convert post values into a string that can be stored in db and attach it as property to the field object
      */
      abstract protected function setDbValue();
      
      protected function escapeCustomRegex ()
      {
          if (!(isset($this->field->customvalidation)))
          {
              return;
          }
          $clean1 = str_replace("\/", "/", $this->field->customvalidation);
          $clean2 = str_replace("/", "\/", str_replace("\/", "/", $clean1));
          $this->field->customvalidation = $clean2;
      }
      
      public function setRecordId($cid)
      {
          $this->field->recordId = $cid;
      }
      
      abstract protected function setRedirectParam();
}