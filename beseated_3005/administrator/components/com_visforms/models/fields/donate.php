<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.formfield');
JLoader::register('VisformsAEF', JPATH_ADMINISTRATOR . '/components/com_visforms/helpers/aef/aef.php');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class JFormFieldDonate extends JFormField
{
	/**
	 * The id of parent form
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Donate';

	/**
	 * Method to get the id of the parent visforms form
	 *
	 * @return	array	The field input element.
	 * @since	1.6
	 */
	protected function getInput()
	{
        
        $app             = JFactory::getApplication();
        $doc             = JFactory::getDocument();
        $language  = $doc->language;
        
        switch ($language)
        {
            case "de-de" :
                $lang = "de";
                break;
            case "fr-fr" :
                $lang = "fr";
                break;
            default :
                $lang = "en";
                break;
        }
        $html = '';
        if (!(VisformsAef::checkForOneAef()))
        {       
            $html .= '<iframe src="http://www.vi-solutions.de/'.$lang.'/donate-'.$lang.'?tmpl=donatebutton" style="max-width: 206px; border:none" height="60" scrolling="no">';
            $html .=' </iframe>';
        }
		return 	$html;
	}
}
