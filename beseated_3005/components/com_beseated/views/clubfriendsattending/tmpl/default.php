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

$input        = JFactory::getApplication()->input;
$Itemid       = $input->get('Itemid', 0, 'int');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
$item         = $this->items;
$itemCount    = count($item);
?>
<style type="text/css">
.frnd-rspns.pull-left {
    color: #fff;
}
</style>
<script type="text/javascript">
	function setValueInModel(bookingID,userName,tableName,venueName)
	{
		jQuery('#booking_id').val(bookingID);
		jQuery('#model-body-text').html('Send a request to '+ userName+' to add you to their '+tableName+' table at '+venueName+' venue.');
	}

	function sendAddMeRequest()
	{
		var booking_id = jQuery('#booking_id').val();

		var msg_for_addme = jQuery('#msg_for_addme').val();
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=clubfriendsattending.addMeForVenueTable',
			type: 'GET',
			data: 'booking_id='+booking_id+'&message='+msg_for_addme,
			success: function(response){
				if(response == "200")
				{
					location.reload();
				}
				else if(response == "501")
				{
					alert("oops...You have already sent request for join table")
				}
	        }
		})
		.done(function() {
		})
		.fail(function() {
		})
		.always(function() {
			jQuery('#myModal').modal('hide');
		});
	}
</script>
<div class="table-wrp">
	<h2>Friends Attending</h2>
	<div class="frnd-wrp row-fluid">
		<?php for ($i = 0; $i < $itemCount; $i++): ?>
			<?php $fbImage = "http://graph.facebook.com/".$item[$i]->fb_id."/picture" ;?>
			<div class="span6">
				<div class="media">
					<a class="pull-left" href="<?php echo 'http://www.facebook.com/'.$item[$i]->fb_id; ?>">
						<img class="media-object" data-src="<?php echo $fbImage; ?>" src="<?php echo $fbImage; ?>">
					</a>
					<div class="media-body">
						<h4 class="media-heading"><?php echo $item[$i]->name; ?></h4>
						<?php $tableDetail = BeseatedHelper::getVenueTableDetail($item[$i]->table_id);?>
						<h5><?php echo $tableDetail->table_name; ?></h5>
						<h6><?php echo date('d-M-Y',strtotime($item[$i]->booking_date)); ?></h6>
					</div>
					<?php $requestStatus = $this->model->checkForRequestAlreadySent($item[$i]->venue_table_booking_id,$item[$i]->table_id,$item[$i]->venue_id);?>
					<?php if(empty($requestStatus)): ?>
						<a href="#myModal" role="button" onclick="setValueInModel('<?php echo $item[$i]->venue_table_booking_id; ?>','<?php echo $item->name; ?>','<?php echo $tableDetail->table_name; ?>','<?php echo $item->venue_name; ?>');" class="frnd-actn-btn pull-right" data-toggle="modal"></a>
					<?php else: ?>
						<a class="frnd-rspns pull-left" href="#"><?php echo $requestStatus; ?></a>
					<?php endif; ?>
				</div>
			</div>
		<?php endfor; ?>
	</div>
</div>
<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="myModalLabel">Send Add Me Request</h3>
	</div>
	<div class="modal-body">
		<p id="model-body-text">Send a request to name to add you to their table booking at venue.</p>
		<input type="hidden" name="booking_id" id="booking_id" value="0">
		<textarea class="add-me-venue-table" id="msg_for_addme"></textarea>
	</div>
	<div class="modal-footer">
		<button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</button>
		<button class="btn btn-primary" onclick="sendAddMeRequest()">Send Request</button>
	</div>
</div>
