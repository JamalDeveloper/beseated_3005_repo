<?php
/**
 * Visforms control html for select for default layout
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


if (!empty($displayData)) : 
    if (isset($displayData['field'])) :
        $field = $displayData['field'];
        $html = '';
        $k=count($field->opts);
        $options = array();
        $checked = array();
        $hasSelectedItem = false;

        //Has select no default value? Then we need a supplementary 'default' option for selects that are not "multiple" or have a height < 1. Otherwise the first option can not be selected properly.
        for ($j=0;$j < $k; $j++)
        {
            if ($field->opts[$j]['selected'] != false) 
            {
                $hasSelectedItem = true;
                break;
            }
        }
        if (((empty($field->attribute_multiple)) 
            && (empty($field->attribute_size)) 
            && (empty($hasSelectedItem)))) 
        {
            $options[] = JHtml::_('select.option', '', JText::_('CHOOSE_A_VALUE'));
        }
        for ($j=0;$j < $k; $j++)
        {
            $optKey = array();
            if ($field->opts[$j]['selected'] != false) 
            {
                $checked[] = $field->opts[$j]['value'];
            }
            
            if (!empty($field->opts[$j]['disabled']))
            {
                $optKey['disable'] = true;
            }

            $options[] = JHtml::_('select.option', $field->opts[$j]['value'], $field->opts[$j]['label'], $optKey);	
        }
        $html .= JHtml::_('select.genericlist', $options, $field->name . '[]', array('id'=>'field' . $field->id,'list.attr'=>$field->attributeArray, 'list.select'=>$checked));
        echo $html;
    endif;  
endif; ?>