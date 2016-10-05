<?php
/**
* @module		Art Adminer
* @copyright	Copyright (C) 2013 artetics.com
* @license		GPL
*/
function adminer_object() {
	include_once "./plugins/plugin.php";
	
	foreach (glob("plugins/*.php") as $filename) {
		include_once "./$filename";
	}
	
	$plugins = array(
		new AdminerFrames
	);

	return new AdminerPlugin($plugins);
}
define('MyAdminerConst', TRUE);
include "./adminer/file.php";
?>