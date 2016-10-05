<?php
/**
 * Visforms field file class
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
 * Visforms field file
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsFieldFile extends VisformsField
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
        //preprocessing field
        $this->extractDefaultValueParams();
        $this->extractRestrictions();
        $this->mendBooleanAttribs();
        $this->setIsConditional();
        $this->setFieldDefaultValue();
        $this->setFileInfo();
    }
    
    /**
     * The the default value of the field which is displayed in the form according field defition, query params, user inputs
     */
    
    protected function setFieldDefaultValue()
    {

        //file upload fields do not use the post value but the $_FILES var, but if we have a POST Value, we set dataSource property
        if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id))
        {
            $this->field->dataSource = 'post';
            return;
        }
        //Nothing to do
        return;
    }
    
    /**
     * Method to convert post values into a string that can be stored in db and attach it as property to the field object
     */
    protected function setDbValue()
    {
        //Nothing to to
        //We can gather the required information only when we have performed full validity test of form and perform the real file upload process
        return;
    }
    
    //if we are editing record set, we gather information of the old file and store it with the field, in order that we can display file information in edit form
    protected function setFileInfo()
    {
       if (empty($this->field->recordId))
       {
          return;
       }
       $key = 'F' . $this->field->id;
       $dbValue = $this->input->post->get($key, '', 'string');
       if (empty($dbValue))
       {
           return;
       }
       $file = VisformsmediaHelper::getFileInfo($dbValue);
       if (!empty($file))
       {
            $this->field->orgfile = $file;
       }
       $this->field->orgfile = $file;
       return;
    }
    
    protected function setRedirectParam()
    {
        return;
    }
}