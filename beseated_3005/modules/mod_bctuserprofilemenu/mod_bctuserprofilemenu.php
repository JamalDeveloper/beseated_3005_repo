<?php
/**
* @version		$Id: mod_bctedintro.php 10000 2014-01-16 03:35:53Z schro $
* @package		Joomla 3.2.x
* @copyright	Copyright (C) 2005 - 2014 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/// no direct access
defined('_JEXEC') or die('Restricted access');


// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$doc		= JFactory::getDocument();
$modulebase 	= ''.JURI::base().'modules/mod_bctuserprofilemenu/';

$layout 	= $params->get('layout', 'default');


/* call jquery lib */
JHtml::_('jquery.framework');


$beseatedConfig = BeseatedHelper::getExtensionParam();
$userType       = "";
$user           = JFactory::getUser();
$groups         = $user->get('groups');

if(in_array($beseatedConfig->venue, $groups))
{
	$userType = 'Club';
}
else if(in_array($beseatedConfig->beseated_guest, $groups))
{
	$userType = 'BeseatedGuest';
}
else if(in_array($beseatedConfig->chauffeur, $groups))
{
	$userType = 'Chauffeur';
}
else if(in_array($beseatedConfig->protection, $groups))
{
	$userType = 'Protection';
}
else if(in_array($beseatedConfig->yacht, $groups))
{
	$userType = 'Yacht';
}
else
{
	$userType = 'Guest';
}

/*$firstLine  = "";
$secondLine = "";
$thirdLine  = "";

$app     = JFactory::getApplication();
$input   = $app->input;
$clubID  = $input->get('club_id', 0, 'int');
$companyID  = $input->get('company_id', 0, 'int');
$itemdID = $input->get('Itemid', 0, 'int');
$view = $input->get('view', '', 'string');*/

/*echo "<pre>";
print_r($user);
echo "</pre>";
exit;*/

/*if($userType == 'Club')
{
	$venueDetail = modBctedTitle::getUserVenueDetail($user->id);
	$firstLine = $venueDetail->venue_name;
	if($user->lastvisitDate!='0000-00-00 00:00:00')
	{
		$secondLine = 'Last visited : '. date('d-m-Y' , strtotime($user->lastvisitDate));
	}
	else
	{
		$secondLine = 'Last visited : ';
	}

}
else if ($userType == 'Registered' || $userType == 'Guest')
{
	if($clubID)
	{
		$clubDetail = modBctedTitle::getUserVenueDetail(0,$clubID);
		$firstLine = $clubDetail->venue_name;
		$secondLine = '';
		$thirdLine = 'Location : ' . $clubDetail->country ;
	}
	else if ($companyID)
	{
		$clubDetail = modBctedTitle::getUserCompanyDetail(0,$companyID);
		$firstLine = $clubDetail->company_name;
		$secondLine = '';
		$thirdLine = 'Location : ' . $clubDetail->country ;
	}

}*/



//$items = modAppZoomHelper::getItems($params);

require( JModuleHelper::getLayoutPath('mod_bctuserprofilemenu', $layout) );
