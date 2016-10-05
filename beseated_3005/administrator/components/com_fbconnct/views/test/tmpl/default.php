<?php
/**
* @package 		Facebook Connect Extension (joomla 3.x)
* @copyright	Copyright (C) Computer - http://www.sanwebe.com. All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @author		Saran Chamling
* @download URL	http://www.sanwebe.com
*/
// No direct access

defined('_JEXEC') or die('Restricted access'); 
$document 			= JFactory::getDocument();
$document->addStyleSheet('components/com_fbconnct/assets/fbbackend.css');
	$myparams 	= JComponentHelper::getParams('com_fbconnct');
	$getappid = $myparams->get('appid');
	$getappsec = $myparams->get('appsecret');
	
echo '<div class="span10"><div class="well well-small">';
	if (strnatcmp(phpversion(),'5.0.0') >= 0)
	{$installedphp = '<li>PHP version : <span style="color:green">5+</span></li>';
	}else{$installedphp = '<li>PHP version : <span style="color:red">Lower (Recommended 5+)</span></li>';}

	$installedcurl = (iscurlinstalled())? '<li><b>cURL</b> is : <span style="color:green"><b>Enabled</b></span> in this Server!</li>' : '<li><b>cURL</b> <span style="color:red"><b>NOT Found</b></span>. cCURL is needed to run facebook!</li>';

	$db = JFactory::getDBO();
	$query = "SELECT published FROM #__modules WHERE module='mod_fbconnct'";
	$db->setQuery($query);
	$pubdata = $db->loadObject();
	
	if($pubdata)
	{
	$fbconectmoduleEnabled = ($pubdata->published==1)?'<li>Facebook Connect Module : <span style="color:green"><b>Enabled</b></span></li>':'<li>Facebook Connect Module : <span style="color:red"><b>Disabled</b></span> </li>';
	}else{
	$fbconectmoduleEnabled = '<li>Facebook Connect Module : <span style="color:red"><b>Not Found</b></span></li>';
	}
	echo $getappid;
	$appidentered 		= (strlen($getappid)<1)? '<li>App ID Entered : <strong style="color:red">No</strong></li>':'';
	if(strlen($getappid)<1 || strlen($getappsec)<1)
		{
		$enterappid = '<li>Enter your facebook App ID and App secret in parameters fields</li>';
		}else{
		$enterappid ='';
		}
	
	$appsecretenter = (strlen($getappsec)<1)? '<li>App Secret Entered <strong style="color:red">No</strong></li>' :'';	
	
	echo "<ul>".$installedphp.$enterappid.$appidentered.$appsecretenter.$installedcurl.$fbconectmoduleEnabled."</ul>";
	
	function iscurlinstalled() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return true;
		}
		else{
			return false;
		}
	}
	###################################################\
	require_once( JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_fbconnct'.DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'facebook.php' );
		$facebook = new Facebook(array('appId' => $getappid,'secret' => $getappsec));
	$access_token="";
	$fbuser = $facebook->getUser();

		if ($fbuser) {
		  try {
			$access_token = $facebook->getAccessToken();
			$user_profile = $facebook->api('/me');
		  } catch (FacebookApiException $e) {
			error_log($e);
			echo '<fieldset><legend>Facebook API exception</legend><pre>'.htmlspecialchars(print_r($e, true)).'</pre></fieldset>';
			$fbuser = null;
		  }
		}
	if ($fbuser) {
	  $logoutUrl = $facebook->getLogoutUrl(array('redirect_uri'=>JRoute::_(JURI::current().'?option=com_fbconnct&view=test', false)));
	} else {
	  $loginUrl = $facebook->getLoginUrl(array('redirect_uri'=>JRoute::_(JURI::current().'?option=com_fbconnct&view=test', false)));
	}

	if ($fbuser)
	{
		echo '<ul><div style="margin:10px"><strong><a href="'.$logoutUrl.'">Logout</a></strong></div></ul>';
	}
	else
	{
		echo '<ul><div style="margin:10px"><strong><a href="'.$loginUrl.'">Login with Facebook</a></strong></div></ul>';
	}

	if ($fbuser)
	{
		echo '<ul><h3>Your Profile</h3>
		<div style="color:green">Success!</div>
		<img src="https://graph.facebook.com/'.$fbuser.'/picture"><br />
		<pre style="font-size:12px;">';
		print_r($user_profile);
		echo '</pre></ul>
		<div id="fb-root"></div>
		<script>
		  window.fbAsyncInit = function() {
			FB.init({
			  appId      : \''.$getappid.'\', // App ID
			  status     : true, // check login status
			  cookie     : true, // enable cookies to allow the server to access the session
			  oauth      : true, // enable OAuth 2.0
			  xfbml      : true  // parse XFBML
			});
			// Additional initialization code here
		  };
		  // Load the SDK Asynchronously
		  (function(d){
			 var js, id = \'facebook-jssdk\'; if (d.getElementById(id)) {return;}
			 js = d.createElement(\'script\'); js.id = id; js.async = true;
			 js.src = "//connect.facebook.net/en_US/all.js";
			 d.getElementsByTagName(\'head\')[0].appendChild(js);
		   }(document));
		</script>
		';
	}
echo '</div></div>';
echo '<div class="span10">';
?>
<div style="font-size:11px;">Facebook Connect by <a href="http://www.sanwebe.com" target="_blank">sanwebe.com</a></div>
<?php 
echo '</div>'; 
?>