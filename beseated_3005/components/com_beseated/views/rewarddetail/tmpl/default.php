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

$app    = JFactory::getApplication();
$input  = JFactory::getApplication()->input;
$Itemid = $input->get('Itemid', 0, 'int');
?>
<div class="reward-detail-wrapper">
	<div id="alert-error"></div>
	<div class="reward-detail-image">
		<img src="<?php echo JURI::root().$this->rewardDetail->image;?>">
	</div>
	<div class="reward-detail-display" style="margin-top:10px;">
		Reward Name : <?php echo ucfirst($this->rewardDetail->reward_name);?><br>
		Reward Coins : <?php echo ucfirst($this->rewardDetail->reward_coin);?>
	</div>
	<div class="reward-description">
		<h4>Description</h4>
		<?php echo $this->rewardDetail->reward_desc;?>
	</div>
	<div class="reward-reedme-btn">
		 <button class="btn reedme-btn">Redeem Reward</button>
	</div>
</div>

<style>
div.modal.fade{display: none;}
div.modal.fade.in{display: block;}
.reward-description{color: #FFFFFF;}

 </style>
}
<!-- Modal -->
<div id="rewardBookingModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-body">
            <div class="modal-message">You Have Insufficient Coins For This Purchase..</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery('#alert-error').hide();
	jQuery('.reedme-btn').click(function(event) {
		var reward_id = '<?php echo $this->rewardDetail->reward_id;?>';
		jQuery.ajax({
			type: "GET",
			url: "index.php?option=com_beseated&task=rewarddetail.bookreward",
			data: "&reward_id="+reward_id,
			success: function(response){
				console.log(response);
				if(response == "200")
				{
					jQuery('#alert-error').html('Reward Booked Successfully');
					jQuery('#alert-error').show();
				}

				if(response == "400")
				{
					jQuery('#rewardBookingModal').modal('show');
				}
			}
		});
	});
});
</script>
