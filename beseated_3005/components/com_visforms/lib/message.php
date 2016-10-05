<?php
/**
 * Visforms message class
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
 * Add message
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
abstract class VisformsMessage
{
    /**
	 * The field name
	 *
	 * @var    string
	 * @since  11.1
	 */
       protected $name;
       
    /**
	 * The field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
       protected $type;
     
     /**
	 * The message translation string
	 *
	 * @var    string
	 * @since  11.1
	 */
       protected $text;
       
    /**
	 * The message type
	 *
	 * @var    string
	 * @since  11.1
	 */
       protected $messageType;
       
    /**
     * 
     * Constructor
     * 
     * @param string $name field name
     * @param array $args additional arguments
     */
       public function __construct($name, $args , $messageType = 'warning')
       {
           $this->name = $name;
           $this->messageType = $messageType;
       }
       
       /**
        * @param string $name field name
        * @param string $type field type
        * @return mixed boolean
        */
       public static function getMessage($name, $type, $args = array())
       {
           $classname = get_called_class() . ucfirst($type);
           if (!class_exists($classname))
           {
               //try to register it
               JLoader::register($classname, dirname(__FILE__) . '/message/'. $type . '.php');
               if (!class_exists($classname))
               {
                    //return a default class?
                    return false;
               }
           }
           //Get message from the appropriate subclass
           $message = new $classname($name, $args);
           return $message->setMessage();
       }
       
       abstract protected function setMessage();
}