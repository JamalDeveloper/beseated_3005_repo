<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');
require_once(JPATH_ADMINISTRATOR . '/components/com_visforms/models/visdatas.php');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class JFormFieldDateFormat extends JFormFieldList
{
	/**
	 * A flexible category list that respects access controls
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'DateFormat';
    
    /**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1'|| (string) $this->disabled == 'true')
		{
			$attr .= ' disabled="disabled"';
		}
        // Initialize JavaScript field attributes.
        //different onclick handler if field is used in an equalTo statement
        $form = $this->form; 
        $label = $form->getFieldAttribute($this->fieldname, 'label');

        //get field defaultvalues
        $model = new VisformsModelVisdatas();
        $datas = $model->getItems();
        $id = JFactory::getApplication()->input->getInt('id', 0);
        //as soon as user inputs are stored we do not allow to change date format
        if ((isset($datas)) && is_array($datas) && (count($datas) > 0))
        {
            $fname = 'F'.$id;
            foreach ($datas as $data)
            {
                if (isset($data->$fname) && ($data->$fname != ''))
                {
                    $attr .= ' onchange="formatFieldDateChange(\'' . JText::_("COM_VISFORMS_DATFORMAT_CANNOT_BE_CHANGED_JS") . '\')"';
                    break;
                }
            }            
        }
        else
        {
            //we allow typefield change
            $attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
        }

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true')
		{
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
		}
		else
		// Create a regular list.
		{
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}

	/**
	 * Method to get a list of categories that respects access controls and can be used for
	 * either category assignment or parent category assignment in edit screens.
	 * Use the parent element to indicate that the field will be used for assigning parent categories.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		// Initialise variables.
		$options = array();
		$options[0]       = JHtml::_('select.option',  'd.m.Y;%d.%m.%Y', 'DD.MM.YYYY');
		$options[1]         = JHtml::_('select.option',  'm/d/Y;%m/%d/%Y','MM/DD/YYYY');
		$options[2]         = JHtml::_('select.option',  'Y-m-d;%Y-%m-%d','YYYY-MM-DD');

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
