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
abstract class VisformsHtmlControlDecorator extends VisformsHtmlControl
{
    /**
	 * The VisformsHtmlControl.
	 *
	 * @var    VisformsHtmlControl object
	 * @since  11.1
	 */
       protected $control;
    
    /**
     * Constructur
     * @param VisformsHtmlControl object $control
     */
    public function __construct($control)
    {
        $this->control = $control;
    }
    
    abstract protected function decorate();
    
    /**
     * method to get the preprocessed control html code as string
     * @return string control html code
     */
    public function getControlHtml ()
    {
        $field = $this->control->field->getField();
        $layout = $this->control->layout;
        $html = '';
        
        JPluginHelper::importPlugin('visforms');
        $dispatcher = JDispatcher::getInstance();
        //Trigger onVisformsBeforeHtmlPrepare event to allow changes on field properties before control html is created
        $dispatcher->trigger('onVisformsBeforeHtmlPrepare', array ('com_visforms.field', &$field, $layout));
        if ($this->control->field->getDecorable() == true)
        {
            //return decorated html string
            $html = $this->decorate();
        }
        else
        {
            //return html string
            $html = $this->control->getControlHtml();
        }
        
        //Trigger onVisformsAfterHtmlPrepare event to allow changes on field properties after control html is created
        $dispatcher->trigger('onVisformsAfterHtmlPrepare', array ('com_visforms.field', &$field, &$html, $layout));
        
        return $html;
    }
}
?>