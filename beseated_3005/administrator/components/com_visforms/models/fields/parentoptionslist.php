<?php
/**
 * Visform field parentoptionslist
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
class JFormFieldParentOptionsList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'ParentOptionsList';
    protected $isRestricted = array();


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
        //extract form id
        $form = $this->form;
        $fid = $form->getValue('fid','', 0);
        $id = $form->getValue('id', '', 0);
        //get field name
        $fieldname = $form->getValue('name', null, '');
        //get field type
        $type = $form->getValue('typefield');
        //get element name from fields.xml
        $elementName = $this->fieldname;
        //only get options for the field that is actually displayed 
        //(not the field definitions of the other field types defined in fields.xml which are also created but not displayed)
        if (isset($type) && isset($elementName))
        {
            if (strpos($elementName, $type) === false)
            {
                // Merge any additional options in the XML definition.
                $options = array_merge(parent::getOptions(), $options);
                return $options;
            }
        }
        
        if (is_numeric($fid) && ($fid != 0) &&($fieldname != '') && (is_numeric($id)) && ($id != 0))
        {
            // Create options according to visfield settings
            //Only selects, radios, checkboxes and multicheckboxes are field types that can be used as trigger for conditional fields
            $db	= JFactory::getDbo();
            $query = ' SELECT c.id , c.typefield, c.label, c.defaultvalue, c.restrictions from #__visfields as c where c.fid='.$fid.' AND c.published = 1 '.
                'and c.typefield IN  ('. $db->quote('select'). ', '. $db->quote('radio'). ', '. $db->quote('checkbox'). ', '. $db->quote('multicheckbox').')';
			$canFrontendEdit = VisformsAEF::checkAEF(VisformsAEF::$allowfrontenddataedit);
            if (!empty($canFrontendEdit))
            {
                $query .= 'AND NOT (c.editonlyfield = 1)';
            }
            $query .= ' order by c.label ASC';

            $db->setQuery( $query );
            $fields = $db->loadObjectList();
            if ($fields)
            {
                //get id's of all restricted fields
                $this->getRestrictedIds($fields, $id);
                
                //create the option list
                foreach ($fields as $field)
                {
                    //only from fields which are not in the isRestricted list
                    if (!(in_array($field->id, $this->isRestricted)))
                    {
                        $registry = new JRegistry;
                        $registry->loadString($field->defaultvalue);
                        $defaultvalue = $registry->toArray();
                        $type = $field->typefield;
                        if (in_array ($type , array('select', 'radio', 'multicheckbox')))
                        {
                            //get hidden list
                            $listHidden = $defaultvalue["f_" . $type . "_list_hidden"];
                            //get option strings from hidden list
                            $opts = JHtml::_('visformsselect.extractHiddenList', $listHidden);
                            foreach ($opts as $opt)
                            {
                                $tmp = JHtml::_(
                                    'select.option', 'field' . $field->id . '__'. $opt['id'],
                                    $field->label . ' || ' . $opt['label'], 'value', 'text',
                                    false
                                );

                                // Add the option object to the result set.
                                $options[] = $tmp;
                            }
                        }
                        else
                        {
                            $options[] = JHtml::_(
                                    'select.option', 'field' . $field->id . '__' . $defaultvalue["f_" . $type . "_attribute_value"],
                                    $field->label . ' || ' . $defaultvalue["f_" . $type . "_attribute_value"], 'value', 'text',
                                    false
                                );
                        }
                    }
                }
            }
        }
        // Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
    
    /**
     * 
     * @param array $fields array of fields objects. Only contains fields of typ select, radio, multicheckbox or checkbox.
     * @param int $id id of field that is to be processed
     * @return type
     */
    private function getRestrictedIds ($fields, $id)
    {
        //add id to list with restsricted id's.
        //on first call: don't show ourselfs in option list
        $this->isRestricted[] = $id;
        
        foreach ($fields as $field)
        {
            if ($field->id == $id)
            {
                //extract db field restrictions
                $registry = new JRegistry();
                $registry->loadString($field->restrictions);
                $restrictions = $registry->toArray();
                
                if (!isset($restrictions['usedAsShowWhen']))
                {
                    return;
                }
                
                //when we have a usedAsShowWhen item, call ourself with the id retrieved from $value
                foreach ($restrictions['usedAsShowWhen'] as $key => $value)
                {
                    $this->getRestrictedIds( $fields, $value);
                }
            }
        }
    }
}
