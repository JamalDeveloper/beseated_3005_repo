<?php
/**
 * Visforms decorator class for HTML controls
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
 * Decorate HTML control according to layout
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
abstract class VisformsHtmlControl
{
    /**
	 * The VisformsHtml field
	 *
	 * @var    object
	 * @since  11.1
	 */
       protected $field;
       
    /**
	 * The layout type.
	 *
	 * @var    string
	 * @since  11.1
	 */
       protected $layout;
       
    /**
     * Constructur
     * @param VisformsHtml object $field
     * @param string $layout layout type
     */
    public function __construct($field, $layout)
    {
        $this->field = $field;
        $this->layout = $layout;
    }
    
    /**
    * Factory to create instances of field objects according to their type
    * 
    * @param object $field Instance of VisformsHtml or decendant
    * @param string $layout
    * @return \classname|boolean
    */

   public static function getInstance($field, $layout)
   {
       $fieldtype = $field->getField()->typefield;
       $classname = get_called_class()  . ucfirst($layout) . ucfirst($fieldtype);
       if (!class_exists($classname))
       { 
            if ($layout == 'editbthorizontal') 
            {
                //check if we have an implemantation in editbtdefault
                $classname = get_called_class()  . ucfirst('btdefault') . ucfirst($fieldtype);
            }
            else if ($layout == 'editbtdefault') 
            {
                //check if we have an implemantation in btdefault
                $classname = get_called_class()  . ucfirst('btdefault') . ucfirst($fieldtype);

            }
            else if ($layout == 'editmcindividual') 
            {
                //check if we have an implemantation in btdefault
                $classname = get_called_class()  . ucfirst('btdefault') . ucfirst($fieldtype);

            }
            else if ($layout == 'bthorizontal') 
            {
                //check if we have an implemantation in btdefault
                $classname = get_called_class()  . ucfirst('btdefault') . ucfirst($fieldtype);
            }
            else if ($layout == 'mcindividual')
            {
                //check if we have an implemantation in btdefault
                $classname = get_called_class()  . ucfirst('btdefault') . ucfirst($fieldtype);
            }
            else
            {
                $classname = get_called_class()  . ucfirst('visforms') . ucfirst($fieldtype);
            }
       }

       if (!class_exists($classname))
       { 
           //fall back on the visform default
           $classname = get_called_class()  . ucfirst('visforms') . ucfirst($fieldtype);
       }

       //delegate to the appropriate subclass
       return new $classname($field, $layout);
   }
   
   abstract public function getControlHtml();
   
   /**
    * Method to create label html string
    * @return string label html or ''
    */
   public function createLabel()
   {
       return '';
   }
   
   /**
    * Method to create class attribute value for label tag according to layout
    * @return string class attribute value
    */
   protected function getLabelClass ()
   {
       $labelClass = '';
       switch ($this->layout)
       {
           case 'bthorizontal' :
           case 'editbthorizontal' :
               $labelClass = ' control-label ' ;
               break;
           case 'btdefault' :
           case 'editbtdefault' :
               $labelClass = '';
               break;
           default :
               $labelClass = ' visCSSlabel ';
               break;
       }
       return $labelClass;
   }
   
   /**
    * Method to create html for field custom text
    * @return string custom text or ''
    */
   public function getCustomText ()
   {
        $field = $this->field->getField();
        //input
        $html = '';
        if (isset($field->customtext) && ($field->customtext != ''))  
        {
			JPluginHelper::importPlugin('content');
			$customtext =  JHtml::_('content.prepare', $field->customtext); 
            $html .= '<div class="visCustomText ">' . $customtext. '</div>';
        }
        //Trigger onVisformsAfterCustomtextPrepare event to allow changes on field properties before control html is created
        JPluginHelper::importPlugin('visforms');
        $dispatcher = JDispatcher::getInstance();
        //make custom adjustments to the custom text html
        $dispatcher->trigger('onVisformsAfterCustomtextPrepare', array ('com_visforms.field', &$html, $this->layout));
        return $html;       
   }
}