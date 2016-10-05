<?php
/**
 * Visforms Layout class Visforms
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
 * Set properties of a form field according to it's type and layout settings
 *
 * @package		Joomla.Site
 * @subpackage	com_visforms
 * @since		1.6
 */
class VisformsLayoutVisforms extends VisformsLayout
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
           //css for required fields with placeholder instead of label
           $css .= $parent . ' div.required > label.visCSSlabel.asterix-ancor:after ';
           $css .= '{content:"*"; color:red; display: inline-block; padding-left: 0; } ';
           //css for all other required fields
           $css .= $parent . ' div.required > label.visCSSlabel:after ';
           $css .= '{content:"*"; color:red; display: inline-block; padding-left: 10px; } ';
           return $css;
       }
}