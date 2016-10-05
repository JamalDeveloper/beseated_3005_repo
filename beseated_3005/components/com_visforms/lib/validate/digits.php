<?php
/**
 * Visforms validate digits class
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
 * Visforms validate Digits
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsValidateDigits extends VisformsValidate
{
    
     /**
	 * The field value.
	 *
	 * @var    string
	 * @since  11.1
	 */
       protected $value;
       
     /**
	 * regex.
	 *
	 * @var    string
	 * @since  11.1
	 */
       protected $regex;
       
    /**
     * 
     * Constructor
     * 
     * @param string $type control type
     * @param array $args params for validate
     */
    
       public function __construct($type, $args)
       {
           parent::__construct($type, $args);
           $this->regex = '/^\d+$/';
           //we expect an item with key 'value' in $args
           $this->value = isset($args['value']) ? $args['value'] : "";
       }
       
       /**
        * Method that performs the validation
        * @return boolean
        */
       protected function test()
       {
            if (!(preg_match($this->regex, $this->value) == true)) 
            {
                return false;
 
            }
            else
            {
                return  true;
            }
       }
}