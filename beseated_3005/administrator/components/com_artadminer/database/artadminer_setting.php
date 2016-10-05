<?php
/**
* @module		Art Adminer
* @copyright	Copyright (C) 2013 artetics.com
* @license		GPL
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class TableArtAdminer_setting extends JTable
{
	var $id = null;
	var $cssfile = null;
	var $autologin = null;
	
	function __construct(&$db)
	{
		parent::__construct( '#__art_adminer_setting', 'id', $db );
	}
}

?>