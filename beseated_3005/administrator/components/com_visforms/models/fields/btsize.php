<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('integer');

/**
 * Form Field class for the Joomla Platform.
 * Provides a select list of integers with specified first, last and step values.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldBtsize extends JFormFieldInteger
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Btsize';
    protected $show = true;
    
    protected function getLabel()
    {
        $form = $this->form;
        $fid = JFactory::getApplication()->input->getInt('fid', 0);
        $db	= JFactory::getDbo();
        $query = ' SELECT layoutsettings from #__visforms as c where id='.$fid;

        $db->setQuery( $query );
        $layoutsettings = json_decode( $db->loadResult(), true );
        if (is_array($layoutsettings) && isset($layoutsettings['formlayout']) && $layoutsettings['formlayout'] != 'mcindividual')
        {
            $this->__set('disabled', 'disabled');
        }
        return parent::getLabel();

    }
}
