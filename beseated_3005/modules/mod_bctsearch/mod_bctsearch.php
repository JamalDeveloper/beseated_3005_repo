<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_search
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$lang = JFactory::getLanguage();
$app  = JFactory::getApplication();

/*if ($params->get('opensearch', 1))
{
	$doc = JFactory::getDocument();

	$ostitle = $params->get('opensearch_title', JText::_('MOD_BCTSEARCH_SEARCHBUTTON_TEXT') . ' ' . $app->get('sitename'));
	$doc->addHeadLink(
			JUri::getInstance()->toString(array('scheme', 'host', 'port'))
			. JRoute::_('&option=com_search&format=opensearch'), 'search', 'rel',
			array(
				'title' => htmlspecialchars($ostitle),
				'type' => 'application/opensearchdescription+xml'
			)
		);
}*/

//$upper_limit = $lang->getUpperLimitSearchWord();

/*$button			= $params->get('button', 0);
$imagebutton	= $params->get('imagebutton', 0);
$button_pos		= $params->get('button_pos', 'left');
$button_text	= htmlspecialchars($params->get('button_text', JText::_('MOD_SEARCH_SEARCHBUTTON_TEXT')));
$width			= (int) $params->get('width', 20);
$maxlength		= $upper_limit;
$text			= htmlspecialchars($params->get('text', JText::_('MOD_SEARCH_SEARCHBOX_TEXT')));
$label			= htmlspecialchars($params->get('label', JText::_('MOD_SEARCH_LABEL_TEXT')));*/
$set_Itemid		= (int) $params->get('set_itemid', 0);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

// Initialiase variables.
$db    = JFactory::getDbo();
$queryBV = $db->getQuery(true);

// Select vanue city
$queryBV->select('city')
	->from($db->quoteName('#__beseated_venue'))
	->where($db->quoteName('published') . ' = ' . $db->quote(1))
	->where($db->quoteName('has_table') . ' = ' . $db->quote(1))
	->where($db->quoteName('city') . ' <> ' . $db->quote(''));


$db->setQuery($queryBV);
$venueCountry = $db->loadColumn();
/*****END******/


// Select Chauffeur city
$queryCC = $db->getQuery(true);

// Create the base select statement.
$queryCC->select('city')
	->from($db->quoteName('#__beseated_chauffeur'))
	->where($db->quoteName('published') . ' = ' . $db->quote(1))
	->where($db->quoteName('has_service') . ' = ' . $db->quote(1))
	->where($db->quoteName('city') . ' <> ' . $db->quote(''));

$db->setQuery($queryCC);
$chauffeurCountry = $db->loadColumn();
/*****END******/

// Select Protection city
$queryPR = $db->getQuery(true);

// Create the base select statement.
$queryPR->select('city')
	->from($db->quoteName('#__beseated_protection'))
	->where($db->quoteName('published') . ' = ' . $db->quote(1))
	->where($db->quoteName('has_service') . ' = ' . $db->quote(1))
	->where($db->quoteName('city') . ' <> ' . $db->quote(''));

$db->setQuery($queryPR);
$protectionCountry = $db->loadColumn();
//*****END******//

// Select Yacht city
$queryYC = $db->getQuery(true);

// Create the base select statement.
$queryYC->select('city')
	->from($db->quoteName('#__beseated_yacht'))
	->where($db->quoteName('published') . ' = ' . $db->quote(1))
	->where($db->quoteName('has_service') . ' = ' . $db->quote(1))
	->where($db->quoteName('city') . ' <> ' . $db->quote(''));

$db->setQuery($queryYC);
$yatchCountry = $db->loadColumn();
//*****END******//

// Select Private jet city
$queryPJ = $db->getQuery(true);

// Create the base select statement.
/*$queryPJ->select('city')
	->from($db->quoteName('#__beseated_private_jet'))
	->where($db->quoteName('published') . ' = ' . $db->quote(1))
	->where($db->quoteName('city') . ' <> ' . $db->quote(''));

$db->setQuery($queryPJ);
$jetCountry = $db->loadColumn();*/

//*****END******//


$country = array_unique(array_merge($venueCountry,$chauffeurCountry,$protectionCountry,$yatchCountry));
/*
echo "<pre>";
print_r($country);
echo "</pre>";
exit;*/

/*if ($imagebutton)
{
	$img = ModSearchHelper::getSearchImage($button_text);
}*/
$mitemid = $set_Itemid > 0 ? $set_Itemid : $app->input->get('Itemid');
require JModuleHelper::getLayoutPath('mod_bctsearch', $params->get('layout', 'default'));
