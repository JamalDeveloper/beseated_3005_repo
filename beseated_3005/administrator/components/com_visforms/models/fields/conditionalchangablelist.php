<?php
/**
 * Visform field typefield
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');
require_once JPATH_COMPONENT_ADMINISTRATOR.'/models/visfield.php';

/**
 * Form Field class for Visforms.
 * Supports list field types. 
 * Prevents a user from changing the selected option if the field has restrictions (it's values are used in conditional field parameter of other fields)
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldConditionalChangableList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'ConditionalChangableList';

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
        if (empty($label))
        {
            $label = $form->getFieldAttribute($this->fieldname, 'label', null, 'defaultvalue');
        }

        //get field defaultvalues
        $model = new VisformsModelVisfield();
        if ($restrictions = $model->getRestrictions($form->getValue('id', null, 0)))
        {
            $rfieldNames = array ();
            foreach ($restrictions as $r => $value)
            {
                $rfieldNames[] = implode(', ', array_keys($value));           
            }
            $fieldNames = implode(', ', $rfieldNames);
            //as long as the restrictions are not empty we do not allow to change the typefield
            $attr .= ' onchange="fieldUsed(this, \'' . $this->value. '\', \'' . JText::sprintf(("COM_VISFORMS_FIELD_HAS_RESTICTIONS_JS"), $fieldNames, JText::_($label)) . '\')"';
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
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		return parent::getOptions();
	}
}
