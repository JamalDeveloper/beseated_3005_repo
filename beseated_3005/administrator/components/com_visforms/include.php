<?php
/**
 * Form component for Joomla
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
//load Visforms classes
// Register helper class
if (!defined('VISFORMS_INCLUDED'))
{
    define('VISFORMS_INCLUDED', 'v3.7.1');
    JLoader::register('VisformsHelper', __DIR__ . '/helpers/visforms.php');
    JLoader::register('VisformsmediaHelper', __DIR__ . '/helpers/visformsmedia.php');
    JLoader::discover('JHtml', __DIR__ . '/helpers/html/');
    JLoader::register('VisformsAEF', __DIR__ . '/helpers/aef/aef.php');
    //load component library main classes
    JLoader::discover('Visforms', JPATH_SITE . '/components/com_visforms/lib/');
    if (JFactory::getApplication()->isSite())
    {
        JLoader::register('VisformsEditorHelper', JPATH_SITE . '/components/com_visforms/helpers/editor.php');
    }
}
