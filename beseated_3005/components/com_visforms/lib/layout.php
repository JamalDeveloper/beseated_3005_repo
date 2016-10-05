<?php
/**
 * Visforms Layout class 
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
 * Set properties of a form according to it's type and layout settings
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
abstract class VisformsLayout
{
    /**
	 * layout type
	 *
	 * @var    string
	 * @since  11.1
	 */
         protected $type;
         
    /**
	 * layout options
	 *
	 * @var    array
	 * @since  11.1
	 */
         protected $options;
     /**
     * 
     * Constructor
     * 
     * @param string $type layout type
     * @param array $option additionals layout options
     */
       public function __construct($type, $options)
       {
           $this->type = $type;
           $this->showRequiredAsterix = true;
           $this->parentFormId = "";
           //get additional options from $options
           if (!(is_null($options)))
           {
               if (isset($options['showRequiredAsterix']))
               {
                    $this->showRequiredAsterix = $options['showRequiredAsterix'];
               }
               if (isset($options['parentFormId']))
               {
                   $this->parentFormId = $options['parentFormId'];
               }
           }
       }
       
       /**
        * Method to get the right instance of layout class
        * @param string $type layout type
        * @param array $option additionals layout options
        * @return \classname|boolean
        */
       public static function getInstance($type = 'visforms', $options = null)
       {    
           if ($type == '')
           {
               $type = 'visforms';
           }
           $classname = get_called_class() . ucfirst($type);
           if (!class_exists($classname))
           {
               //try to register it
               JLoader::register($classname, dirname(__FILE__) . '/layout/'. $type . '.php');
               if (!class_exists($classname))
               {
                    //return a default class?
                    return false;
               }
           }
           //delegate to the appropriate subclass
           return new $classname($type, $options);
       }
       
       /**
        * Method to add layout specific custom css to the view.
        */
       public function addCss ()
       {
           $css = "";
           $doc = JFactory::getDocument();
           if ($this->showRequiredAsterix == true)
           {
                $css = $this->getCustomRequiredCss($this->parentFormId);
           }
           if ($css != "")
           {
            $doc->addStyleDeclaration($css);
           }
       }
       
       /**
        * Method to create Custom css
        * Used for display and positioning of required asterix
        * @return string css
        */
       abstract protected function getCustomRequiredCss ($parent);
}