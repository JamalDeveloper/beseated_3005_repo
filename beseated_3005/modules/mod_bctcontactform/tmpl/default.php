<?php
/**
	 * @package   AppImage Slider
	 * @version   1.0
	 * @author    Erwin Schro (http://www.joomla-labs.com)
	 * @author	  Based on BxSlider jQuery plugin script
	 * @copyright Copyright (C) 2013 J!Labs. All rights reserved.
	 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
	 *
	 * @copyright Joomla is Copyright (C) 2005-2013 Open Source Matters. All rights reserved.
	 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
	 */


defined('_JEXEC') or die('Restricted access');

$doc 	= JFactory::getDocument();


$modbase 	= JURI::base(true) .'/modules/mod_bctcontactform'; /* juri::base(true) will not added full path and slash at the path end */
// add style
//$doc->addStyleSheet($modbase . '/assets/css/style.css');
// add javascript
/*$doc->addScript($modbase . '/assets/js/libs/prototype.js');
$doc->addScript($modbase . '/assets/js/libs/scriptaculous.js');
$doc->addScript($modbase . '/assets/js/libs/sizzle.js');
$doc->addScript($modbase . '/assets/js/loupe.js');*/

?>

<script type="text/javascript">
    jQuery(document).ready(function() {
    	jQuery('#alert-error').hide();
    });
</script>

<script type="text/javascript">

	function IsEmail() {
		var email = jQuery('#contact_email').val();
	  	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;



		if(regex.test(email))
		{
			jQuery('#alert-error').hide();
			jQuery('#alert-error').html('');
			return true;
		}
		else
		{
			jQuery('#alert-error').show();
			jQuery('#alert-error').html('<h4>Invalid Email</h4><br /><a class="close" data-dismiss="alert">×</a>Please enter valid email address.');

			return false;
		}

	}

	function numbersonly(events){
	    var unicodes=events.charCode? events.charCode :events.keyCode;
	    if (unicodes!=8)
	    {
	        if( ((unicodes>47 && unicodes<58) || unicodes == 43 || unicodes == 46 || unicodes == 9 )){
	            return true;
	        }else{
	            return false;
	        }
	    }
	}
</script>

<div id="alert-error" class="alert alert-error">
</div>

<?php

$app = JFactory::getApplication();
$show_send_message = $app->input->get('show_send_message',0,'int');
if($show_send_message == 1)
{ ?>
	<div id="alert-error" class="alert alert-error">
		<a class="close" data-dismiss="alert">×</a><h4>Message </h4><br />We have received your message and one of our customer support team will be in touch shortly.
	</div> <?php
}
?>
<div>
	<form class="form-horizontal guest-frm" method="post" action="<?php echo JRoute::_('index.php?option=com_bcted&task=profile.contactadmin'); ?>">
		<div class="control-group">
			<label class="control-label span6">Name</label>
			<div class="controls span6">
				<input type="text" name="contact_name" class="span12" required="required" id="contact_name"/>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label span6">Email</label>
			<div class="controls span6">
				<input type="email" name="contact_email" class="span12" required="required" id="contact_email"/>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label span6">Mobile</label>
			<div class="controls span6">
				<input onkeypress="return numbersonly(event);" type="text" name="contact_mobile" class="span12" required="required" id="contact_mobile"/>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label span6">Feedback</label>
			<div class="controls span6">
				<textarea class="span12" required="required" name="contact_message" ></textarea>
			</div>
		</div>

		<div class="control-group">
			<div class="controls span6"></div>
			<div class="controls span6">
				<button type="submit" onclick="return IsEmail();" class="btn btn-large span">Send</button>
			</div>
		</div>

		<input type="hidden" name="return" value="<?php echo base64_encode(JUri::getInstance()); ?>">
	</form>
</div>



