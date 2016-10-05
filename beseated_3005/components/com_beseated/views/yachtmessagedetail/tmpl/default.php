<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$input     = JFactory::getApplication()->input;
$Itemid    = $input->get('Itemid', 0, 'int');
$loginUser = JFactory::getUser();

$userGroup = BeseatedHelper::getUserType($loginUser->id);
$this->messages  = BeseatedHelper::getMessageDetail();

//echo "<pre>";print_r($this->messages);echo "<pre/>";
//exit();

?>
<div id="alert-error" class="alert alert-error"></div>
<div class="table-wrp">
	<div class="req-detailwrp msg-detailwrp-venue">
		<h2><?php echo $this->otherUser->name; ?></h2>
		<div class="message-detail-list-venue">
			<div id="message-box">
				<?php foreach ($this->messages as $key => $message):?>
					<?php if($loginUser->id == $message->from_user_id): ?>
					<div class="message-detail-venue right">
					<?php else: ?>
					<div class="message-detail-venue left">
				    <?php endif; ?>
						<div class="message-detail-venue-inner">
							<div class="message-detail-body-venue">
								<p><?php echo ucfirst($message->message_body);?></p>
							</div>
							<div class="message-detail-date-time-venue">
								<p class="message-detail-date-venue"><?php echo date('d-M-Y',strtotime($message->created)); ?></p>
								<p class="message-detail-time-venue"><?php echo gmdate('H:i',$message->time_stamp); ?></p>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="controls span12">
			    <input type="hidden" name="other_user_id" id="other_user_id" value="<?php echo $this->otherUser->id; ?>">
				<input type="hidden" name="connectionID" id="connectionID" value="<?php echo $this->connectionID; ?>">
				<input type="text" id="message" placeholder="Message">
				<span class="send-msg-icn" id="send_msg"></span>
 			</div>
		</div>
	</div>
</div>
<?php echo $this->pagination->getListFooter(); ?>

<script type="text/javascript">
	jQuery("#send_msg").click(function() {

		var msg = jQuery('#message').val();
		msg = jQuery.trim(msg);

		if(msg.length == 0)
		{
			return 0;
		}

		var otherUserID = jQuery('#other_user_id').val();
		var connectionID = jQuery('#connectionID').val();

		// Ajax call to send message....
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=message.send_message',
			type: 'GET',
			data: 'connection_id='+connectionID+'&other_user_id='+otherUserID+'&message='+msg,

			success: function(response){

				if(response == 400)
				{
					jQuery('#alert-error').show();
					jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Invalid detail for send message</h4>');
					return false;
				}
				else if(response == 500)
				{
					jQuery('#alert-error').show();
					jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Error while sending a message</h4>');
					return false;
				}
				else if(response == 704)
				{
					jQuery('#alert-error').show();
					jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please Login .Session Expired</h4>');
					return false;
				}
				else
				{
					jQuery('#message-box').append(response); 
					jQuery('#message').val(''); 
				}
	        }
		})
		.done(function() {
			//console.log("success");
		})
		.fail(function() {
			//console.log("error");
		})
		.always(function() {
			//console.log("complete");
		});
	});

	jQuery(document).ready(function($) {
		jQuery('#alert-error').css('display', 'none');
	});

</script>


