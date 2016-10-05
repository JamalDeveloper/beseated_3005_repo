<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;
JFormHelper::loadFieldClass('hidden');
require_once JPATH_ADMINISTRATOR.'/components/com_visforms/helpers/aef/aef.php';

/**
 * Form Field class for the Joomla Platform.
 * Provides a hidden field
 *
 * @link   http://www.w3.org/TR/html-markup/input.hidden.html#input.hidden
 * @since  11.1
 */
class JFormFieldAef extends JFormFieldHidden
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Aef';  
    
    public function renderField($options = array())
    {
        return $this->getInput();
    }

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
        $feature = $this->getAttribute('feature', 8);
        $featureexists = VisformsAEF::checkAEF($feature);
        if (!empty($featureexists))
        {
            $this->value = "1";
        }
        else
        {
            $this->value = "0";
        }
        return parent::getInput();
	}
}
