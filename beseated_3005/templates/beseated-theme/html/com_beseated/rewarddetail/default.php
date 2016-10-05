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

$app    = JFactory::getApplication();
$input  = JFactory::getApplication()->input;
$Itemid = $input->get('Itemid', 0, 'int');
?>

<section class="page-section page-reward">
	<div class="container">
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
				<h2 class="heading-1">Reward Detail</h2>
				<div class="reward">
					<div class="reward-image" style="background-image: url(<?php echo JUri::root().($this->rewardDetail->image ?: 'images/beseated/default/banner.png') ?>);"></div>
					<div class="reward-details">
                        <span class="reward-name heading-4"><?php echo ucfirst($this->rewardDetail->reward_name);?></span>
                        <span class="reward-coins heading-4"><img src="/templates/beseated-theme/images/coin-icon-small.png" /><?php echo BeseatedHelper::currencyFormat('','',$this->rewardDetail->reward_coin)?></span>
                    </div>
                    <div class="reward-desc"><?php echo $this->rewardDetail->reward_desc;?>
                    </div>
                    <button class="button">Redeem Reward</button>
				</div>
			</div>
		</div>
	</div>
</section>


<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery('.button').click(function(event) {
		var reward_id = '<?php echo $this->rewardDetail->reward_id;?>';
		jQuery.ajax({
			type: "GET",
			url: "index.php?option=com_beseated&task=rewarddetail.bookreward",
			data: "&reward_id="+reward_id,
			success: function(response){
				console.log(response);
				if(response == "200")
				{
					noty({
					    layout: 'topRight',
					    theme: 'relax',
					    text: '<strong>Success!</strong><br>Reward Booked Successfully',
					  });
				}

				if(response == "400")
				{
					noty({
					    layout: 'topRight',
					    theme: 'relax',
					    text: '<strong>Message</strong><br>You Have Insufficient Coins For This Purchase',
					  });
				}
			}
		});
	});
});
</script>