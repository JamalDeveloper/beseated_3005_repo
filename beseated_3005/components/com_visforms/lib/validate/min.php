<?php
/**
 * Visforms validate min class
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
 * Visforms validate min
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsValidateMin extends VisformsValidate
{
    
    /**
	 * count.
	 *
	 * @var    string
	 * @since  11.1
	 */
       protected $count;
       
     /**
	 * mincount.
	 *
	 * @var    string
	 * @since  11.1
	 */
       protected $minCount;
       
       
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
           //we expect an item with key 'count' and an item with key 'mincount' in $args
           $this->count = isset($args['count']) ? $args['count'] : 0;
           $this->mincount = isset($args['mincount']) ? $args['mincount'] : 0;
       }
       
       /**
        * Method that performs the validation
        * @return boolean
        */
       protected function test()
       {
            if ($this->count < $this->mincount)
            {
                return false;
 
            }
            else
            {
                return  true;
            }
       }
}