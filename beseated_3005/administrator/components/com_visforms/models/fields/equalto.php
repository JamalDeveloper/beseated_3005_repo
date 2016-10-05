<?php
/**
 * Visform field equalto
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
class JFormFieldEqualTo extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'EqualTo';


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
        //get a default option which will not go into the validate_array because it has a falsy value
        $options[] = JHtml::_(
                    'select.option', '0',
                    JText::_('COM_VISFORMS_CHOOSE_FIELD'), 'value', 'text',
                    false
                );
        $id = 0;
        //extract form id
        $form = $this->form;
        $fid = $form->getValue('fid');       
        if (isset($fid) && is_numeric($fid))
        {
            $id = $fid;
        }
        //get field type
        $typefield = $form->getValue('typefield', null, '');
        $fieldname = $form->getValue('name', null, '');
        
        if (($fid != 0) && ($typefield != '') &&($fieldname != ''))
        {
            // Create options according to visfield settings
            $db	= JFactory::getDbo();
            $query = ' SELECT c.id , c.label from #__visfields as c where c.fid='.$id.' AND c.published = 1 '.
                'and (c.typefield = ' . $db->quote($typefield) .') AND NOT (c.name = ' . $db->quote($fieldname) . ')';
			$canFrontendEdit = VisformsAEF::checkAEF(VisformsAEF::$allowfrontenddataedit);
            if (!empty($canFrontendEdit))
            {
                $query .= 'AND NOT (c.editonlyfield = 1)';
            }

            $db->setQuery( $query );
            $fields = $db->loadObjectList();
            if ($fields)
            {
                foreach ($fields as $field)
                {
                    $tmp = JHtml::_(
                        'select.option', '#field' . $field->id,
                        $field->label, 'value', 'text',
                        false
                    );

                    // Add the option object to the result set.
                    $options[] = $tmp;
                }
            }
        }
        // Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
