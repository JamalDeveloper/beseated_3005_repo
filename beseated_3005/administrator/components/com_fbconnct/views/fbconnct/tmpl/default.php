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

?>
<div class="span6">
    <div class="well well-small">
        <div class="module-title nav-header"><?php echo JText::_('COM_FBCONNCT_FACEBOOK_CONNECT'); ?> 
        (v <?php
       	$xml = JFactory::getXML(JPATH_SITE .'/administrator/components/com_fbconnct/com_fbconnct.xml');
		$version = @(string)$xml->version;
			
		if($version){
			echo $version;
		}else{
			echo '';
		}
		?>) - <?php echo JText::_('COM_FBCONNCT_INSTRUCTIONS'); ?></div>
        <div class="row-striped">
          <div class="row-fluid">1. <?php echo JText::sprintf(JText::_('COM_FBCONNCT_CURL_HOST_SUPPORT'),'<a href="http://www.php.net/manual/en/intro.curl.php" target="_blank">PHP curl</a>' ); ?></div>
          <div class="row-fluid">2. <?php echo JText::sprintf(JText::_('COM_FBCONNCT_SETUP_APPLICATION'),'<a href="http://www.sanwebe.com/2011/11/creating-facebook-application-for-your-site/" target="_blank">Setup</a>' ); ?></div>
          <div class="row-fluid">3. <?php echo JText::_('COM_FBCONNCT_COPY_APP_ID_SECRET'); ?></div>
          <div class="row-fluid">4. <?php echo JText::_('COM_FBCONNCT_COPY_APP_ID_SECRET'); ?></u></div>
          <div class="row-fluid">5. <?php echo JText::_('COM_FBCONNCT_ENABLE_MODULE'); ?></div>
        </div>
    </div>
    
    <div class="well well-small">
        <div class="module-title nav-header"><?php echo JText::_('COM_FBCONNCT_WITHOUT_LOGIN_MODULE'); ?></div>
        <div class="row-striped">
          <div class="row-fluid"><?php echo JText::_('COM_FBCONNCT_DISPLAY_BUTTON_WITHOUT_MODULE'); ?></div>
          <div class="row-fluid"><?php echo JText::_('COM_FBCONNCT_COPY_PASTE_BUTTON_CODE'); ?><br /><textarea style="width:300px;height:50px;background:#FFFFCC;"><a href="#" onclick="window.open('<?php 
		  echo  JRoute::_(JURI::root().'index.php?option=com_fbconnct&task=login&format=raw'); 		  
		  ?>','name','height=300,width=550');return false;" >Login with Facebook</a></textarea></div>
        </div>
    </div>
    
    <div class="well well-small">
        <div class="module-title nav-header">Changelog</div>
        <div class="row-striped">
         <ul>
         <li><span class="badge badge- hasTooltip" title="" data-original-title="Hits">3.4.1</span> Using default getMailer(). Some changes in original language files</li>
         <li><span class="badge badge- hasTooltip" title="" data-original-title="Hits">3.3e</span> Added Wall publish, fixed infinite loop in multilingual websites</li>
         <li><span class="badge badge- hasTooltip" title="" data-original-title="Hits">3.2e</span> Fixed "Undefined Password" while registration</li>
         <li><span class="badge badge- hasTooltip" title="" data-original-title="Hits">3.1e</span> Checks invalid Facebook Email</li>
         <li><span class="badge badge- hasTooltip" title="" data-original-title="Hits">3.0e</span> Modified to work with only Joomla 3.0</li>
         <li><span class="badge badge- hasTooltip" title="" data-original-title="Hits">3.00</span> Total modification. Updated Facebook PHP SDK (v.3.2.2)</li>
			<li><span class="badge badge- hasTooltip" title="" data-original-title="Hits">2.xx</span> No longer supported!</li>
    	</ul>           
       </div>
    </div>
</div>
<div class="span4">
    <div class="well well-small">
        <div class="module-title nav-header">Login with Facebook</div>
        <div class="row-striped">
        <?php echo JText::sprintf(JText::_('COM_FBCONNCT_MAINTAINED_BY'),'<a href="http://www.saaraan.com" target="_blank">Sanwebe.com</a>','<a href="https://twitter.com/saaraan" target="_blank">Twitter</a>'); ?>
        </div>
    </div>
    
    <div class="well well-small">
        <div class="module-title nav-header"><?php echo JText::_('COM_FBCONNCT_SUPPORT_AUTHOR'); ?></div>
<?php echo JText::_('COM_FBCONNCT_SIDE_PROJECT_INFO'); ?>
<form name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="saaraan@gmail.com">
<input type="hidden" name="item_name" value="fbconnectcom">
<input type="hidden" name="currency_code" value="USD">
<table width="100%" border="0">
  <tr>
    <td>USD</td>
    <td><input type="text" name="amount" value="10"></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="image" src="components/com_fbconnct/assets/buy-me-a-coffee.png" style="border:none" border="0" name="submit"></td>
    </tr>
</table>

<div style="margin-top:5px;" align="center"><span class="style1"><a href="http://extensions.joomla.org/extensions/access-a-security/site-access/authentication-cloud-based/16350" target="_blank">Review it</a> | <a href="http://www.sanwebe.com/forums/forum/general-discussions" target="_blank">Support</a> | <a href="http://www.sanwebe.com/2012/04/facebook-connect-2-0-for-joomla" target="_blank">Download</a></span></div>
</form>
</div>

<div class="well well-small">
    <div class="module-title nav-header"><?php echo JText::_('COM_FBCONNCT_IN_OTHER_LANGUAGES'); ?> </div>
    <div class="row-striped">
     <?php echo JText::sprintf(JText::_('COM_FBCONNCT_IN_OTHER_LANGUAGES_INFO'),'<a href="https://github.com/saaraan/Joomla_FB_connect_languages/archive/master.zip" target="_blank">Github</a>','<a href="https://docs.google.com/forms/d/14qTCPDGHlaKHC3FfIw7-M9WjyuH3jVzczFjYHoPD_BY/viewform" target="_blank">Google form</a>'); ?>
    </div>
</div>

</div>