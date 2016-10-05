<?php
/**
 * Visforms field select class
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
 * Visforms field select
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsFieldSelect extends VisformsField
{
    /**
     * 
     * Constructor
     * 
     * @param object $field field object as extracted from database
     * @param object $form  form object as extracted from database
     */
    
    public function __construct($field, $form)
    {
        parent::__construct($field, $form);
        $this->queryValue = $this->input->get->get($field->name, null, 'ARRAY');
        $this->postValue = $this->input->post->get($field->name, array(), 'ARRAY'); 
        $editValue = $this->input->post->get('F'.$field->id, null, 'String');
        if (isset($editValue) && $editValue != "")
        {
            $this->editValue = JHtmlVisformsselect::explodeMsDbValue($editValue);
        }
    }
    
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
        $this->setIsDisplayChanger();
        $this->getOptions();
        $this->setFieldDefaultValue();
        $this->disableUsedOptsOnUniqueValues();
        $this->setDbValue();
        $this->setRedirectParam();
    }
    
    /**
     * The the default value of the field which is displayed in the form according field defition, query params, user inputs
     * 
     * @return boolean
     */
    
    protected function setFieldDefaultValue()
    {
        $field = $this->field;
        if ($this->input->getCmd('task', '') == 'editdata')
        {
            if (isset($this->editValue) && !(is_null($this->editValue)))
            {
                $this->setSelectedOptions('editValue');
            }
            $this->field->dataSource = 'db';
            return;
        }
        //if we have a POST Value, we use this
        if ((count($_POST) > 0) && isset($_POST['postid']) && ($_POST['postid'] == $this->form->id))
        {
            $this->validateUserInput('postValue');
            $this->setSelectedOptions('postValue');
            $this->field->dataSource = 'post';
            return;
        }
        
        //if we have a GET Value and field may use GET values, we uses this
        if (isset($field->allowurlparam) && ($field->allowurlparam == true) && isset($this->queryValue) && !(is_null($this->queryValue)))
        {
            $this->setSelectedOptions('queryValue');
            $this->field->dataSource = 'query';
            return;
        }
        //we use default values
        return;
    }
    
    /**
     * Method to get options of select
     * @throws InvalidArgumentException
     */
    private function getOptions()
    {
        //No Options for select given
        if (!(isset($this->field->list_hidden)) || $this->field->list_hidden == "")
        {   
            throw new InvalidArgumentException ('Select must have at least one option.');
        }
        //split options into an array
        $opts = JHtml::_('Visformsselect.extractHiddenList', $this->field->list_hidden);
        if (!is_array($opts))
        {
            throw new InvalidArgumentException ('Select must have at least one option.');
        }
        $this->field->opts = $opts;
    }
    
    /**
     * Method to set selected value in options according to user input
     * @param string $inputType Type of user input (query or post)
     * @throws InvalidArgumentException
     */
    private function setSelectedOptions($inputType)
    {
        if (!isset($this->field->opts) || !(is_array($this->field->opts)))
        {
            throw new InvalidArgumentException ('Select must have at least one option.');
        }
        $values = $this->$inputType;
        $optsNew = array();
        //we set options
        foreach ($this->field->opts as $opt)
        {
            if (in_array($opt['value'], $values))
            {
                $opt['selected'] = true;
            }
            else
            {
                $opt['selected'] = false;
            }
            $optsNew[] = $opt;
        }
        $this->field->opts = $optsNew;
    }
    
    /**
     * Method to convert post values into a string that can be stored in db and attach it as property to the field object
     */
    protected function setDbValue()
    {
        if (isset($this->field->dataSource) && $this->field->dataSource == 'post')
        {
            $this->field->dbValue = implode(JHtmlVisformsselect::$msdbseparator, $this->postValue);
        }
    }
	
	/**
     * Method to check, that user inputs are valid option values
     */
	protected function validateUserInput($inputType)
	{
        if (!isset($this->field->opts) || !(is_array($this->field->opts)))
        {
            throw new InvalidArgumentException ('Select must have at least one option.');
        }
        //Array of values set by user
        $values = $this->$inputType;
        if (is_array($values))
        {
            foreach ($values as $index => $word)
            {
                $values[$index] = trim($word);
            }
        }
        
        //Array of options set in field definition
        $opts = $this->field->opts;
        
        //array of values allowed by field settings
        $allowedValues = array_map(function($element) {return $element['value'];}, $opts);
        //when we deal with a select that is not required, we may hat an empty string submitted by post which is valid but not part of the option list
        array_push($allowedValues, '');
        
        //are there any values in the post which are not allowed?
        $diff = array_diff($values, $allowedValues);
        if (count($diff) > 0)
        {
            //we have an invalid value in post
            $this->field->isValid = false;
            $error = JText::sprintf('COM_VISFORMS_OPTION_HAS_INVALID_POST_VALUE', $this->field->label);
            $this->setErrors($error);
        }

        //Remove invalid value from user input array, so that it might not accidentally be stored in the database
        foreach ($diff as $diff)
        {
            $key = array_keys($values, $diff);
            array_splice($this->$inputType, $key[0], 1);
        }
    }
    
    private function disableUsedOptsOnUniqueValues()
    {
        if (empty($this->field->uniquevaluesonly))
        {
            return true;
        }
        if (!isset($this->field->opts) || !(is_array($this->field->opts)))
        {
            throw new InvalidArgumentException ('Select must have at least one option.');
        }
        $usedOpts = array();
        
        if (isset($this->field->id) && is_numeric($this->field->id))
        {
            $db	= JFactory::getDbO();
            $query = $db->getQuery(true);
            $query->select($db->qn('F' . $this->field->id))
                ->from($db->qn('#__visforms_'.$this->form->id));            
            if (!empty($this->field->uniquepublishedvaluesonly))
            {
                $query->where($db->qn('published') . ' = ' . 1);
            }
            if (!empty($this->field->recordId))
            {
                $query->where($db->qn('id') . ' != ' . $this->field->recordId);
            }
            $query->where($db->qn('F' . $this->field->id) . ' IS NOT NULL');
            $query->where($db->qn('F' . $this->field->id) . " != ''");
            $query->group($db->qn('F' . $this->field->id));
            $db->setQuery($query);
            try
            {
                $usedOpts = $db->loadColumn();
            } catch(Exception $exc)
            {
                return true;
            }            
        }
        $optsNew = array();
        $usedOptsValues = array();
        if (!empty($usedOpts))
        {
            foreach ($usedOpts as $usedOpt)
            {
                $usedOptValues = JHtmlVisformsselect::explodeMsDbValue($usedOpt);
                foreach ($usedOptValues as $usedOptValue)
                {
                    $usedOptsValues[] = $usedOptValue;
                }
            }
            
        }

        foreach ($this->field->opts as $opt)
        {
            if (in_array($opt['value'], $usedOptsValues))
            {
                $opt['disabled'] = true;
                $opt['selected'] = false;
            }
            $optsNew[] = $opt;
        }
        $this->field->opts = $optsNew;
    }
    
    protected function setRedirectParam()
    {
        if (isset($this->field->dataSource) && $this->field->dataSource == 'post' && (!empty($this->field->addtoredirecturl)))
        {
            $this->field->redirectParam = $this->postValue;
        }
    }
}