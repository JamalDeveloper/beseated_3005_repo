<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class JFormFieldFormId extends JFormField
{
	/**
	 * The id of parent form
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'FormId';

	/**
	 * Method to get the id of the parent visforms form
	 *
	 * @return	array	The field input element.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$fid = JFactory::getApplication()->input->getInt('fid', 0);
		return 	'<input id="jform_fid" class="readonly" type="text" readonly="readonly" size="10" value="' .$fid .'" name="jform[fid]">';
	}
}
