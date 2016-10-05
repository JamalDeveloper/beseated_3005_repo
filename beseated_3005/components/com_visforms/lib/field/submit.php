<?php
/**
 * Visforms field submit class
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
 * Visforms field submit
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsFieldSubmit extends VisformsField
{  
    /**
     * Public method to get the field object
     * @return object VisformsFieldText
     */
    
    public function getField()
    {
        $this->setField();
        return $this->field;
    }
    
    /**
     * Preprocess field. Set field properties according to field defition, query params, user inputs
     */
    
    protected function setField()
    {
        $this->extractDefaultValueParams();
        $this->setIndividualProperties();
        $this->setFieldDefaultValue();
    }
    
    /**
     * The the default value of the field which is displayed in the form according field defition, query params, user inputs
     * 
     * @return boolean
     */
    
    protected function setFieldDefaultValue()
    {
        //Nothing to do for Submit buttons
    }
    
    /**
     * add individual properties to field declaration
     */
    protected function setIndividualProperties()
    {      
        $this->field->isButton = true;
    }
    
    /**
     * Method to convert post values into a string that can be stored in db and attach it as property to the field object
     */
    protected function setDbValue()
    {
        return;
    }
    
    protected function setRedirectParam()
    {
        return;
    }
}