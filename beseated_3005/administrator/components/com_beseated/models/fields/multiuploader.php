<?php
/**
 * @package     Administrator.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * MultiUploader Form Field class
 *
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 * @since       0.0.1
 */
class JFormFieldMultiUploader extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   0.0.1
	 */
	protected $type = 'MultiUploader';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  date  The field input markup.
	 *
	 * @since   0.0.1
	 */
	public function getInput()
	{
		return '<div id="mulitplefileuploader">'.JText::_($this->hint).'</div><div id="status"></div>';
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
