<?php
/**
 * Visforms field password class
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
require_once(__DIR__ . '/text.php');

/**
 * Visforms field password
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsFieldPassword extends VisformsFieldText
{
    /**
     * The the default value of the field which is displayed in the form according to field defition, query params, user inputs
     */
    
    protected function setFieldDefaultValue()
    {
        $field = $this->field;
        if ($this->input->getCmd('task', '') == 'editdata')
        {
            if (isset($this->editValue))
            {
                $this->field->attribute_value = $this->editValue;
                $this->field->dataSource = 'db';
                return;
            }
        }
        //if we have a POST Value, we use this
        if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id))
        {
            if (isset($_POST[$field->name]))
            {
                $this->field->attribute_value = $this->postValue;
            }
            else
            {
                $this->field->attribute_value = "";
            }
            $this->field->dataSource = 'post';
            return;
        }
        
        //if we have a GET Value and field may use GET values, we uses this
        if (isset($field->allowurlparam) && ($field->allowurlparam == true) && isset($this->queryValue) && !(is_null($this->queryValue)))
        {
            $this->field->attribute_value = $this->queryValue;
            $this->field->dataSource = 'query';
            return;
        }
       
        //Nothing to do
        return;
    }
}