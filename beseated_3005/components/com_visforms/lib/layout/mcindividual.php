<?php
/**
 * Visforms Layout class Bootstrap default
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
/**
 * Set properties of a form according to it's type and layout settings
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsLayoutMcindividual extends VisformsLayout
{            
      /**
        * Method to create Custom css
        * Used for display and positioning of required asterix
        * @param string $parent id of enclosing form element
        * @return string css
        */
       protected function getCustomRequiredCss ($parent)
       {
           $parent = 'form#' . $parent;
           $css = "";
           //css for required fields except checkboxes and inputs with placeholder instead of label
           $css .= $parent . ' div.required > label:after, ';
           //css for required checkboxes
           $css .= $parent . ' div.required > label.checkbox.asterix-ancor:after, ';
           //css for required inputs with placeholder instead of label 
           $css .= $parent . ' div.required > span.asterix-ancor:after, ';
		   //css for required date inputs with placeholder instead of label 
           $css .= $parent . ' div.required > div.asterix-ancor > div:after ';
           $css .= '{content:"*"; color:red; display: inline-block; padding-left: 10px; } ';
           //no required asterix on the control labels of individual radio control or checkbox control in checkbeox groups
           $css .= $parent . ' div.required > label.radio:after, ';
           $css .= $parent . ' div.required > label.checkbox:after ';
           $css .= '{content:""; color:red; } ';
           return $css;
       }
}