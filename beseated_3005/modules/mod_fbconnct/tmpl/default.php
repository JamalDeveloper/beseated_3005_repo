<?php 
/**
* @package 		Facebook Connect Extension (joomla 3.x)
* @copyright	Copyright (C) Computer - http://www.sanwebe.com. All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @author		Saran Chamling
* @download URL	http://www.sanwebe.com
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$db = JFactory::getDBO();
$document = JFactory::getDocument();
$showfacebookimage 	= $params->get('show-facebook-image');

$javascript = '
function poploginbox(url) {
	var height = 300;
	var width = 550;
	var left = (screen.width/2)-(width/2);
	var top = (screen.height/2)-(height/2);
	
	var winop= window.open(url,\'\',\'height=\'+height+\',width=\'+width+\', top=\'+top+\', left=\'+left+\'\');
	if (window.focus) {winop.focus()}
	return false;
} 
';

$stylelink = '<style type="text/css">';
$stylelink .= '.fbconnct_btn{background: url('.JRoute::_(JURI::base().'modules/mod_fbconnct/assets/facebook_login.png').') no-repeat 0px 0px;width:145px;height:26px;float: left;}
.fbconnct_btn:hover{background: url('.JRoute::_(JURI::base().'modules/mod_fbconnct/assets/facebook_login.png').') no-repeat 0px -26px;}
.fbconnct_btn_wrp{width:160px;height:26px;overflow:hidden;}';
$stylelink .= '</style>';
 
echo '<!-- sanwebe.com Facebook Login Start -->';
if($type == 'logout'){
	$logoutUrl = JRoute::_( 'index.php?task=logout&option=com_fbconnct&return='. base64_encode(JURI::current()));

	$query = "SELECT facebook_userid FROM #__facebook_joomla_connect WHERE joomla_userid=$user->id AND linked=1";
	$db->setQuery($query);
	$rows = $db->loadObjectList();
	if($rows && $showfacebookimage!='none')
	{
		$fbuserid = (strlen($rows[0]->facebook_userid)>0)?$rows[0]->facebook_userid:0;
		echo '<div class="fb_profile_pic_wrp"><img class="fb_profile_pic" src="https://graph.facebook.com/'.$fbuserid.'/picture?type='.$showfacebookimage.'" border="0" /></div>';
	}
	
	echo '<div class="fb_welcome_text_wrp">'.$user->name.' <a href="'.$logoutUrl.'" class="btn btn-primary">'.JText::_('MOD_FBCONNCT_LOG_OUT').'</a></div>';
}else{

	if($params->get('show-login-form')==1){
		
			echo '<form action="'.JRoute::_('index.php', true, $params->get('usesecure')).'" method="post" id="login-form" class="form-inline">';
			if ($params->get('pretext')){
				echo '<div class="pretext">'.$params->get('pretext').'</div>'; 
			}
			echo '<div class="userdata">';
			echo '<div id="form-login-username" class="control-group">';
			echo '<div class="controls">';
			
			if (!$params->get('usetext')){
				echo '<div class="input-prepend input-append">';
				echo '<span class="add-on">';
				echo '<span class="icon-user tip" title="'.JText::_('MOD_FBCONNCT_USERNAME').'"></span>';
				echo '<label for="modlgn-username" class="element-invisible">'.JText::_('MOD_FBCONNCT_USERNAME').'</label>';
				echo '</span>';
				echo '<input id="modlgn-username" type="text" name="username" class="input-small" tabindex="0" size="18" placeholder="'.JText::_('MOD_FBCONNCT_USERNAME').'" />';
				echo '</div>';
			}else{
				echo '<label for="modlgn-username">'.JText::_('MOD_FBCONNCT_USERNAME').'</label>';
				echo '<input id="modlgn-username" type="text" name="username" class="input-small" tabindex="0" size="18" placeholder="'.JText::_('MOD_FBCONNCT_USERNAME').'" />';
			}
			
			echo '</div>';
			echo '</div>';
			echo '<div id="form-login-password" class="control-group">';
			echo '<div class="controls">';
			
			if (!$params->get('usetext')){
			echo '<div class="input-prepend input-append">';
			echo '<span class="add-on">';
			echo '<span class="icon-lock tip" title="'.JText::_('MOD_FBCONNCT_PASSWORD').'">';
			echo '</span>';
			echo '<label for="modlgn-passwd" class="element-invisible">'.JText::_('MOD_FBCONNCT_PASSWORD').'';
			echo '</label>';
			echo '</span>';
			echo '<input id="modlgn-passwd" type="password" name="password" class="input-small" tabindex="0" size="18" placeholder="'.JText::_('MOD_FBCONNCT_PASSWORD').'" />';
			echo '</div>';
			}else{
			echo '<label for="modlgn-passwd">'.JText::_('MOD_FBCONNCT_PASSWORD').'</label>';
			echo '<input id="modlgn-passwd" type="password" name="password" class="input-small" tabindex="0" size="18" placeholder="'. JText::_('MOD_FBCONNCT_PASSWORD').'" />';
			}
			echo '</div>';
			echo '</div>';
			
			if (JPluginHelper::isEnabled('system', 'remember')){
				echo '<div id="form-login-remember" class="control-group checkbox">';
				echo '<label for="modlgn-remember" class="control-label">'.JText::_('MOD_FBCONNCT_REMEMBER_ME').'</label> <input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>';
				echo '</div>';
			}
			echo '<div id="form-login-submit" class="control-group">';
			echo '<div class="controls">';
			echo '<button type="submit" tabindex="0" name="Submit" class="btn btn-primary">'.JText::_('MOD_FBCONNCT_LOGIN').'</button>';
			echo '</div>';
			echo '</div>';
			
			$usersConfig = JComponentHelper::getParams('com_users');
			if ($usersConfig->get('allowUserRegistration')){
			
			echo '<ul class="unstyled">';
			echo '<li>';
			echo '<a href="'.JRoute::_('index.php?option=com_users&view=registration').'">';
			echo JText::_('MOD_FBCONNCT_CREATE_ACCOUNT');
			echo '<span class="icon-arrow-right"></span></a>';
			echo '</li>';
			echo '<li>';
			echo '<a href="'.JRoute::_('index.php?option=com_users&view=remind').'">'.JText::_('MOD_FBCONNCT_FORGOT_USERNAME').'</a>';
			echo '</li>';
			echo '<li>';
			echo '<a href="'.JRoute::_('index.php?option=com_users&view=reset').'">'.JText::_('MOD_FBCONNCT_FORGOT_PASSWORD').'</a>';
			echo '</li>';
			echo '</ul>';
			}
			
			echo '<input type="hidden" name="option" value="com_users" />';
			echo '<input type="hidden" name="task" value="user.login" />';
			echo '<input type="hidden" name="return" value="'.base64_encode(JURI::current()).'" />';
			echo JHtml::_('form.token');
			echo '</div>';
			echo '</form>';
			
	}
	$document->addCustomTag($stylelink);
	$document->addScriptDeclaration($javascript);

	echo '<div class="fbconnct_btn_wrp"><a href="#" rel="nofollow" title="Login or Sign-up with Facebook" class="fbconnct_btn" onclick="return poploginbox(\''.
	JURI::base().substr(JRoute::_('index.php?option=com_fbconnct&task=login&format=raw'), strlen(JURI::base(true)) + 1).'\')" /><img src="'.JRoute::_(JURI::base().'modules/mod_fbconnct/assets/spacer.gif').'" width="145" height="26" border="0" /></a>
	<a href="http://www.sanwebe.com" rel="nofollow" target="_blank" title="Powered by Sanwebe"><img src="'.JRoute::_(JURI::base().'modules/mod_fbconnct/assets/brd.png').'" width="15" height="26" border="0" /></a></div>';
}
echo '<!-- sanwebe.com Facebook Login end -->';