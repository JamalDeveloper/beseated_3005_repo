<?php
/**
 * Visforms control html for fieldseparator for default layout
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
        //input
        $html = '';
        if (!(isset($field->noborder)) ||((isset($field->noborder)) && ($field->noborder != "1"))) 
        {
            $html .= '<hr ';
            if (!empty($field->attributeArray)) 
            {
                 //add all attributes
                 $html .= JArrayHelper::toString($field->attributeArray, '=',' ', true);
            } 

            $html .=  '/>';
        }
        echo $html;
    endif;  
endif; ?>