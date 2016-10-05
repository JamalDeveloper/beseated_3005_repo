<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_visforms
 * @copyright	Copyright (C) vi-solutions, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_visforms/models', 'VisformsModel');

class modVisformsHelper
{
	/**
	 * Method to retrieve form and field data structure from db
	 *
	 * @return object containing the data from the database
	 * @since		1.6
	 */
	public static function getForm(&$params)
	{

        $app = JFactory::getApplication();
		$id = $params->get('catid', 0);

		// Get an instance of the generic visforms model
		$model = JModelLegacy::getInstance('Visforms', 'VisformsModel', array('ignore_request' => true, 'id' => $id));		
		$visforms = $model->getForm();
		if (empty($visforms))
        {
			return $visforms;
        }        
        $fields = $model->getFields();
                
        $app->setUserState('com_visforms.form' . $visforms->id . '.fields', null);
        $app->setUserState('com_visforms.form' . $visforms->id , null);
        $visforms->fields = $fields;
        
        //Trigger onFormPrepare event 
        JPluginHelper::importPlugin('visforms');
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger('onVisformsFormPrepare', array ('mod_visforms.form', &$visforms, &$params));
        
        $nbFields=count($visforms->fields );
        //get some infos to look whether it's neccessary to add Javascript or special HTML-Code or not
        //variables are set to true if they are true for at least one field
        $required = false;
        $upload = false;
        $textareaRequired = false;
        $hasHTMLEditor = false;
        //helper, used to set focus on first visible field
        $firstControl = true;

        for ($i=0;$i < $nbFields; $i++)
        { 
            $field = $visforms->fields[$i];
            //set the controll variables
            if (isset($field->attribute_required) && ($field->attribute_required == "required")) 
            {
                $required = true;
            }
            if (isset($field->typefield) && $field->typefield == "file")
            {
                $upload = true;
            }
            if (isset($field->textareaRequired) && $field->textareaRequired === true) 
            {
                //we have some work to do to use Javascript to validate that the textarea has content
                $textareaRequired = true;
            }
            if (isset($field->hasHTMLEditor) && $field->hasHTMLEditor == true)
            {
                $hasHTMLEditor = true;
            }
        }
        
        //push helper variabels into params
        $params->set('nbFields', $nbFields);
        $params->set('required', $required);
        $params->set('upload', $upload);
        $params->set('textareaRequired', $textareaRequired);
        $params->set('hasHTMLEditor', $hasHTMLEditor);
        
        $options = array();
        $options['showRequiredAsterix'] = (isset($visforms->requiredasterix)) ? $visforms->requiredasterix : 1;
        $options['parentFormId'] = 'mod-visform' . $visforms->id;
        
        //process form layout
        $olayout = VisformsLayout::getInstance($visforms->formlayout, $options);
        if(is_object($olayout))
        {
            //add layout specific css
            $olayout->addCss();
        }

		return $visforms;
	}
}