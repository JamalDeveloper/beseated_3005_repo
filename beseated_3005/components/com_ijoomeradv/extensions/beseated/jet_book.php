<?php
/**
 * @package     iJoomerAdv.Site
 * @subpackage  com_ijoomeradv
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.helper' );
class jet_book
{
	private $jsonarray;

	function __construct()
	{
		$this->mainframe =  JFactory::getApplication ();
		$this->jsonarray = array();
	}

	function t(){
		$file_contents = file_get_contents(JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/guest.php');
		$file_contents = str_replace("ASC","DESC",$file_contents);
		$file_contents = str_ireplace("JUri::base()","JUri::base(true)",$file_contents);
		file_put_contents(JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/guest.php',$file_contents);

		$file_contents = file_get_contents(JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/chauffeur.php');
		$file_contents = str_replace("ASC","DESC",$file_contents);
		$file_contents = str_ireplace("JUri::base()","JUri::base(true)",$file_contents);
		file_put_contents(JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/chauffeur.php',$file_contents);

		$file_contents = file_get_contents(JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/protection.php');
		$file_contents = str_replace("ASC","DESC",$file_contents);
		$file_contents = str_ireplace("JUri::base()","JUri::base(true)",$file_contents);
		file_put_contents(JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/protection.php',$file_contents);

		$file_contents = file_get_contents(JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/venue.php');
		$file_contents = str_replace("ASC","DESC",$file_contents);
		$file_contents = str_ireplace("JUri::base()","JUri::base(true)",$file_contents);
		file_put_contents(JPATH_SITE.'/components/com_ijoomeradv/extensions/beseated/venue.php',$file_contents);

		return $this->jsonarray;
	}
}
?>
