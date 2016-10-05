<?php
/**
 * @package     IJoomer.Backend
 * @subpackage  com_ijoomeradv.models
 *
 * @copyright   Copyright (C) 2010 - 2014 Tailored Solutions PVT. Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     IJoomer.Backdend
 * @subpackage  com_ijoomeradv.models
 * @since       1.0
 */
class JFormFieldMenutypeselect extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.0
	 */
	protected $type = 'menutype';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 *
	 * @since    1.0
	 */
	protected function getInput()
	{
		// Initialise variables.
		$html     = array();
		$size     = ($v = $this->element['size']) ? ' size="' . $v . '"' : '';
		$class    = ($v = $this->element['class']) ? ' class="' . $v . '"' : 'class="text_area"';

		$menuarray = array();
		$menuarray = json_decode($this->form->getValue('screen'));

		$menustr = '';

		if(!empty($menuarray))
		{
			foreach ($menuarray as $key => $value)
			{
				$menustr .= ','.implode(',', $value);
			}

			$menustr = ltrim($menustr, ',');
		}

		$input = JFactory::getApplication()->input;
		$recordId = $input->getInt('id',0);


		// Load the javascript and css
		JHtml::_('behavior.framework');
		JHtml::_('behavior.modal');

		$screens = json_decode($this->value, true);

		if ($screens)
		{
			foreach ($screens as $key => $value)
			{
				foreach ($value as $k1 => $v1)
				{
					$sname = explode('.', $v1);
					$screenname[] = $sname[1];
				}
			}

			$screen_list = implode(',', $screenname);
		}
		else
		{
			$screen_list = '';
		}

		$html[] = '<input type="text" value="' . $screen_list . '"' . $size . $class . ' />';
		$html[] = '<input type="button" value="' . JText::_('JSELECT') . '" onclick="SqueezeBox.fromElement(this, {handler:\'iframe\', size: {x: 600, y: 450}, url:\'' . JRoute::_('index.php?option=com_ijoomeradv&view=menutypes&layout=select&tmpl=component&menustr='.$menustr.'&recordId=' . $recordId) . '\'})" />';
		$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';

		return implode("\n", $html);
	}
}
