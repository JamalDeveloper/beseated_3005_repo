<?php
/**
 * Visform field Visdatasortorder
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

/**
 * Form Field class for Visforms.
 * Supports list Visforms fields.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldVisDataSortOrder extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'VisDataSortOrder';


	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();
        $options[] = JHtml::_(
                    'select.option', 'id',
                    JText::_('COM_VISFORMS_ID'), 'value', 'text',
                    false
                );
		$options[] = JHtml::_(
                    'select.option', 'ipaddress',
                    JText::_('COM_VISFORMS_IP'), 'value', 'text',
                    false
                );
        $options[] = JHtml::_(
                    'select.option', 'created',
                    JText::_('COM_VISFORMS_SUBMISSIONDATE'), 'value', 'text',
                    false
                );
        $options[] = JHtml::_(
                    'select.option', 'ismfd',
                    JText::_('COM_VISFORMS_MODIFIED'), 'value', 'text',
                    false
                );
        $id = 0;
        //extract form id
        $form = $this->form;
        $link = $form->getValue('link');
        if (isset($link) && $link != "")
        {
            $parts = array();
            parse_str($link, $parts);
            if (isset($parts['id']) && is_numeric($parts['id']))
            {
                $id = $parts['id'];
            }
        }
        // Create options according to visfield settings
        $db	= JFactory::getDbo();
        $query = ' SELECT c.id , c.label from #__visfields as c where c.fid='.$id.' AND c.published = 1 AND (c.frontdisplay is null or c.frontdisplay = 1 or c.frontdisplay = 2) ' .
            "and !(c.typefield = 'reset') and !(c.typefield = 'submit') and !(c.typefield = 'image') and !(c.typefield = 'fieldsep') and !(c.typefield = 'hidden')";
        
        $db->setQuery( $query );
        $fields = $db->loadObjectList();
        if ($fields)
        {
            foreach ($fields as $field)
            {
                $tmp = JHtml::_(
                    'select.option', $field->id,
                    $field->label, 'value', 'text',
                    false
                );

                // Add the option object to the result set.
                $options[] = $tmp;
            }
        }
        // Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
