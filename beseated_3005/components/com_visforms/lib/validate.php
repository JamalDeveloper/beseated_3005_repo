<?php
/**
 * Visforms validation class
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
 * Validate user inputs
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
abstract class VisformsValidate
{
    /**
	 * The field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
       protected $type;
         
     /**
	 * The field value.
	 *
	 * @var    mixed
	 * @since  11.1
	 */
       protected $value;
       
    /**
	 * Rules to be tested
	 *
	 * @var    array
	 * @since  11.1
	 */
       protected $rules;
       
    /**
	 * The field value.
	 *
	 * @var    boolean
	 * @since  11.1
	 */
       protected $valid;
       
    /**
	 * Regex to test against.
	 *
	 * @var    boolean
	 * @since  11.1
	 */
       protected $regex;
       
    /**
     * 
     * Constructor
     * 
     * @param string $type control type
     * @param mixed $value value to validate
     */
       public function __construct($type, $args)
       {
           $this->type = $type;
           $this->args = $args;
       }
       
       /**
        * @param string $type control type
        * @param array $args arguments for validation (mixed value, number count, number requiredCount)
        * @return boolean true if value is valid
        */
       public static function validate($type, $args)
       {
           $classname = get_called_class() . ucfirst($type);
           if (!class_exists($classname))
           {
               //try to register it
               JLoader::register($classname, dirname(__FILE__) . '/validate/'. $type . '.php');
               if (!class_exists($classname))
               {
                    throw new RuntimeException('Unable to load validation class ' . $type);
               }
           }
           //Validate with the appropriate subclass
           $validation = new $classname($type, $args);
           $valid = $validation->test();
           return $valid;
       }
       
       abstract protected function test();
}