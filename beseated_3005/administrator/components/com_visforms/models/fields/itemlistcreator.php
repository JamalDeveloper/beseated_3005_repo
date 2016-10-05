<?php

/**
 * @author      Aicha Vack
 * @package     Joomla.Site
 * @subpackage  com_visforms
 * @link        http://www.vi-solutions.de
 * @copyright   2014 Copyright (C) vi-solutions, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die();

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('hidden');
JHtml::_('bootstrap.framework');
JHtml::_('formbehavior.chosen', 'select');

class JFormFieldItemlistcreator extends JFormFieldHidden
{
	protected $type='itemlistcreator';
    
	protected function getInput()
	{
        $doc = JFactory::getDocument();
        $doc->addScript(JURI::root(true).'/administrator/components/com_visforms/js/itemlistcreator.js');
		$texts =  "{texts : {txtMoveUp: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_MOVE_UP' )). "',".
				"txtMoveDown: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_MOVE_DOWN' )). "',".
				"txtChange: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_CHANGE' )). "',".
                "txtChangeItem: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_CHANGE_ITEM' )). "',".
				"txtDelete: '" . addslashes(JText::_( 'COM_VISFORMS_DEL' )). "',".
				"txtClose: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_CLOSE' )). "',".
				"txtAddItem: '" . addslashes(JText::_( 'COM_VISFORMS_ADD' )). "',".
                "txtAddAndNewItem: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_ADD_AND_NEW' )). "',".
                "txtCreateItem: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_CREATE_NEW_ITEM' )). "',".
				"txtReset: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_RESET' )). "',".
				"txtSave: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_SAVE' )). "',".
                "txtSaveAndNew: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_SAVE_AND_NEW' )). "',".
                "txtJYes: '" . addslashes(JText::_( 'JYES' )). "',".
                "txtJNo: '" . addslashes(JText::_( 'JNO' )). "',".
                "txtAlertRequired: '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_REQUIRED_LABEL_VALUE' )). "',".
                "txtTitle : '" . addslashes(JText::_( 'COM_VISFORMS_ITEMLISTCREATOR_TITLE' )). "',".
                "txtItemsImported : '". addslashes(JText::_( 'COM_VISFORMS_IMPORT_OPTION_SUCCESS' )). "',".
                "txtReaderError : '" . addslashes(JText::_( 'COM_VISFORMS_INVALID_IMPORT_OPTIONS_FORMAT' )). "',".
                "txtNoDataToImport: '" . addslashes(JText::_( 'COM_VISFORMS_NO_DATA_TO_IMPORT' )). "'".
			"},".
            " params: {fieldName : '" . $this->fieldname . "',".
                "idPrefix : 'jform_defaultvalue_',".
                "dbFieldExt : '_list_hidden',".
                "importField : '_importOptions', ".
                "importSeparator : '_importSeparator', ".
                "hdnMFlds : {".
                    "listitemvalue:{fname : 'listitemvalue', flabel : '". addslashes(JText::_( 'COM_VISFORMS_VALUE' ))."', fdesc : '" . addslashes(JText::_('COM_VISFORMS_SELECT_VALUE_DESC')). "', ftype: 'text', frequired: true, fvalue : '', fheader : '" . addslashes(JText::_('COM_VISFORMS_VALUE')). "'},".
                    "listitemlabel:{fname : 'listitemlabel', flabel : '" . addslashes(JText::_( 'COM_VISFORMS_LABEL' )). "', fdesc : '" . addslashes(JText::_('COM_VISFORMS_SELECT_LABEL_DESC')). "', ftype: 'text', frequired: true, fvalue : '', fheader : '" . addslashes(JText::_('COM_VISFORMS_LABEL')). "'},".
                    "listitemischecked:{fname : 'listitemischecked', flabel : '" . addslashes(JText::_( 'COM_VISFORMS_DEFAULT' )). "', fdesc : '" . addslashes(JText::_('COM_VISFORMS_SELECT_DEFAULT_DESC')). "', ftype: 'checkbox', frequired: false, fvalue : '1', fheader : '" . addslashes(JText::_('COM_VISFORMS_DEFAULT')). "'}".
                "},". 
            //add ctype for custom use, where ctype is not field name based
            //"ctype : 'test'".
            "}".
            "}";
		$script = 'var visformsItemlistCreator' . $this->fieldname. ' = jQuery(document).ready(function() {jQuery("#item-form").visformsItemlistCreator(' . $texts . ')});';
		$doc->addScriptDeclaration($script);
		
        $hiddenInput = parent::getInput();
		$html = $hiddenInput;
		
		return $html;
	}
	
}