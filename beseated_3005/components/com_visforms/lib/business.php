<?php
/**
 * Visforms business logic class
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
 * Perform business logic on field
 *
 * @package        Joomla.Site
 * @subpackage    com_visforms
 * @since        1.6
 */
abstract class VisformsBusiness
{
    /**
     * The field type.
     *
     * @var    string
     * @since  11.1
     */
    protected $type;

    /**
     * Field.
     *
     * @var    object
     * @since  11.1
     */
    protected $field;

    /**
     * List of all form Fields.
     *
     * @var    Array
     * @since  11.1
     */
    protected $fields;

    /**
     * Form.
     *
     * @var    object
     * @since  11.1
     */
    protected $form;


    /**
     *
     * Constructor
     *
     * @param object $field field object as extracted from database
     * @param object $form form object as extracted from database
     * @param array $fields List of all form fields
     */
    public function __construct($field, $form, $fields)
    {
        $this->type = $field->typefield;
        $this->field = $field;
        $this->form = $form;
        $this->fields = $fields;
    }

    /**
     * Factory to create instances of field objects according to their type
     *
     * @param object $field
     * @param object $form
     * @param array $fields List of all form fields
     * @return \classname|boolean
     */

    public static function getInstance($field, $form, $fields)
    {
        if (!(isset($field->typefield)))
        {
            return false;
        }

        $classname = get_called_class() . ucfirst($field->typefield);
        if (!class_exists($classname))
        {
            //try to register it
            JLoader::register($classname, dirname(__FILE__) . '/business/' . $field->typefield . '.php');
            if (!class_exists($classname))
            {
                //return a default class?
                return false;
            }
        }
        //delegate to the appropriate subclass
        return new $classname($field, $form, $fields);
    }

    /**
     * Public method to get the field object
     * @return object field
     */
    abstract public function getFields();

    /**
     * Process business logic on field
     */
    abstract protected function setField();

    /**
     * validate field values accoriding to business logic
     */
    abstract protected function validatePostValue();

    /**
     * validate that a required field has a post value set
     */
    abstract public function validateRequired();


    //self recursive function
    protected function setIsDisabled($field = null, $alreadyChecked = array())
    {
        if (is_null($field))
        {
            $self = true;
            $field = $this->field;
        }
        //we only have to check fields that are conditional and not already checked
        if (isset($field->isConditional) && ($field->isConditional == true) && (!(in_array($field->id, $alreadyChecked))))
        {
            foreach ($field as $name => $value)
            {
                //find condition and set isDisabled in field
                if (strpos($name, 'showWhen') !== false)
                {
                    $field->isDisabled = $this->showWhenValueIsNotSelected($value);
                }

            }
            //push modified field back into fields array
            $this->updateFieldsArray($field);

            //if field is disabled we have to check if it is a displayChanger and have to adapt the isDisabled in all fields that are restricted by this fields
            if ((isset($field->isDisabled) && ($field->isDisabled == true)) && (isset($this->field->isDisplayChanger) && ($this->field->isDisplayChanger == true)))
            {
                //add field id to already checked array
                $alreadyChecked[] = $field->id;

                //get id's of restricted fields
                $children = array();
                if (isset($field->restrictions['usedAsShowWhen']))
                {
                    foreach ($field->restrictions['usedAsShowWhen'] as $restrictedFieldId)
                    {
                        $children[] = $restrictedFieldId;
                    }
                }

                //loop through restricted field id's
                foreach ($children as $childid)
                {
                    //loop through field object
                    foreach ($this->fields as $childfield)
                    {
                        //find matching field in fields (if available) 
                        //and prevent infinit loops
                        if (($childid == $childfield->id) && ($field->id != $childfield->id) && (!(in_array($childfield->id, $alreadyChecked))))
                        {
                            $this->setIsDisabled($childfield, $alreadyChecked);
                        }
                    }
                }
            }
        }
    }

    /**
     * Check if a value of a showWhen restict set in one field is not selected
     * @param type $value showWhen restrict string fieldid__rvalue with id is a number and rvalue is the value to check against
     * @param type $fields object of visforms form fields
     * @return boolean returns false when the value of a restict set in one field is selected! If no match is found, true is return
     */
    protected function showWhenValueIsNotSelected($avalue)
    {
        $fields = $this->fields;
        foreach ($avalue as $value)
        {
            if (preg_match('/^field/', $value) === 1)
            {
                $restrict = explode('__', $value, 2);
                //get id of field which can activate to show the conditional field
                $fieldId = JHtml::_('visforms.getRestrictedId', $restrict[0]);
                //get value that has to be selected in the field that can activate to show the conditional field
                $rvalue = $restrict[1];
                foreach ($fields as $field)
                {
                    //restricting field, if this field is disable we hide the restricted field too
                    if (($field->id == $fieldId) && (!(isset($field->isDisabled)) || ($field->isDisabled == false)))
                    {
                        switch ($field->typefield)
                        {
                            case 'select' :
                            case 'radio' :
                            case 'multicheckbox' :
                                $opts = $field->opts;
                                foreach ($opts as $opt)
                                {
                                    if (($opt['selected'] == true) && ($opt['id'] == $rvalue))
                                    {
                                        return false;
                                    }
                                }
                                break;
                            case 'checkbox' :
                                if (isset($field->attribute_checked) && ($field->attribute_checked == 'checked'))
                                {
                                    return false;
                                }
                                break;
                            default :
                                break;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * replace a specific field in $this->fields array with
     * @param object $field
     */
    protected function updateFieldsArray($field)
    {
        $n = count($this->fields);
        for ($i = 0; $i < $n; $i++)
        {
            if ($this->fields[$i]->id == $field->id)
            {
                $this->fields[$i] = $field;
            }
        }
    }

    /**
     * replace $this->field with it's representation in $this->fields
     */
    protected function updateField()
    {
        $n = count($this->fields);
        for ($i = 0; $i < $n; $i++)
        {
            if ($this->field->id == $this->fields[$i]->id)
            {
                $this->field = $this->fields[$i];
            }
        }
    }

    protected function validateUniqueValue()
    {
        if (empty($this->form->saveresult))
        {
            return true;
        }
        //validate unique field value in database (only if user has submitted a value)
        if ((!empty($this->field->uniquevaluesonly)) && (!empty($this->field->dbValue)))
        {
            //get values of all recordsets in datatable
            $details = array();
            $db = JFactory::getDbO();
            if (isset($this->field->id) && is_numeric($this->field->id))
            {
                $query = $db->getQuery(true);
                $query->select($db->qn('F' . $this->field->id))
                    ->from($db->qn('#__visforms_' . $this->form->id));
                if (!empty($this->field->uniquepublishedvaluesonly))
                {
                    $query->where($db->qn('published') . ' = ' . 1);
                }
                if (!empty($this->field->recordId))
                {
                    $query->where($db->qn('id') . ' != ' . $this->field->recordId);
                }
                $query->where($db->qn('F' . $this->field->id) . ' = ' . $db->q($this->field->dbValue));
                $db->setQuery($query);
                try
                {
                    $details = $db->loadColumn();
                } catch (Exception $exc)
                {
                    return true;
                }
            }
            //check if there is a match
            if (!empty($details))
            {
                $this->field->isValid = false;
                $error = JText::sprintf('COM_VISFORMS_UNIQUE_VALUE_REQUIRED', $this->field->label, $this->field->dbValue);
                //attach error to form
                $this->setErrors($error);
                return false;
            }
        }
        return true;
    }

    /**
     * Make property showWhen usable in form display (for administration we store fieldId and optionId, for form we want fieldId and OptionValue
     * Attach property to field
     */
    protected function addShowWhenForForm()
    {
        $field = $this->field;
        if (isset($field->showWhen) && (is_array($field->showWhen) && count($field->showWhen > 0)))
        {
            $showWhenForForm = array();
            //showWhen is an array with showWhen options in format fieldN__optId
            //we iterate through all array items
            while (!empty($field->showWhen))
            {
                $showWhen = array_shift($field->showWhen);
                //split showWhen option in fieldN and optId
                $parts = explode('__', $showWhen, 2);
                if (count($parts) < 2)
                {
                    //showWhen option has wrong format!
                    continue;
                }
                //get Id of restricting field form "fieldN" string
                $restrictorId = JHtml::_('visforms.getRestrictedId', $parts[0]);
                //get the restricting field from fields object
                $restrictor = new stdClass();
                foreach ($this->fields as $rfield)
                {
                    if ($rfield->id == $restrictorId)
                    {
                        $restrictor = $rfield;
                        break;
                    }
                }
                //restricting fields have either an option list (listbox, radio, checkboxgroup) or are checkboxes
                //get the value that matches the optId
                switch ($restrictor->typefield)
                {
                    case 'select' :
                    case 'radio' :
                    case 'multicheckbox' :
                        if (isset($restrictor->opts) && (is_array($restrictor->opts)))
                        {
                            foreach ($restrictor->opts as $opt)
                            {
                                if ($opt['id'] == $parts[1])
                                {
                                    //create an item in showWhenForForm Property using the opt value
                                    $showWhenForForm[] = $parts[0] . '__' . $opt['value'];
                                }
                            }
                        }
                        break;
                    case 'checkbox' :
                        $showWhenForForm[] = $showWhen;
                        break;
                    default :
                        break;
                }
            }
            if (!empty($showWhenForForm))
            {
                $field->showWhenForForm = $showWhenForForm;
            }
            unset($showWhenForForm);
            $this->updateFieldsArray($field);
        }
    }

    protected function setErrors($error)
    {
        if (!(isset($this->form->errors)))
        {
            $this->form->errors = array();
        }
        if (is_array($this->form->errors))
        {
            array_push($this->form->errors, $error);
        }
    }
}