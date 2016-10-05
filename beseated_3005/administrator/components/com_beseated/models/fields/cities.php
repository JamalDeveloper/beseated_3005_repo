<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Citi list of all users Form Field class
 *
 * @package     Pass.Administrator
 * @subpackage  com_pass
 * @since       0.0.1
 */
class JFormFieldDateTimePicker extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   0.0.1
	 */
	protected $type = 'DateTimePicker';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  date  The field input markup.
	 *
	 * @since   0.0.1
	 */
	public function getInput()
	{
		$script = "<script>
						jQuery('#" . $this->id . "').datetimepicker({
							minDate: 0,
	        				formatDate:'d.m.Y',
							allowBlank:false
							});

					</script>";

		return '<input autocomplete="off" placeholder="' . JText::_($this->hint) . '" value="' . JText::_($this->value) . '" type="text" id="' . $this->id . '" name="' . $this->name . '">' . $script;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   0.0.1
	 */
	public function getLabel()
	{
		return  parent::getLabel();
	}
}
