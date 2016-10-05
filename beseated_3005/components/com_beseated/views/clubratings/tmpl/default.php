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
JHtml::_('bootstrap.modal');

$input        = JFactory::getApplication()->input;
$Itemid       = $input->get('Itemid', 0, 'int');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
?>
<div class="guest-club-wrp">
	<H1>Venue Ratings</H1>
	<div class="review-mainwrp">
	<div class="rating-detail">
		<?php foreach ($this->items as $key => $item):?>
		<div class="rating-details-inner rating-details-<?php echo $item->rating_id;?>">
			<div class="media-rating-main">
				<div class="small-rating-wrp" id="<?php echo $item->rating_id;?>">
					<?php $avgRating        = $item->avg_rating;?>
					<?php $avgStarValue     = floor($item->avg_rating); ?>
					<?php $avgMaxRating     = 5; ?>
					<?php $avgPrintedStart  = 0 ;?>
					<?php for($a = 1; $a <= $avgStarValue;$a++): ?>
						<i class="full"> </i>
						<?php $avgPrintedStart = $avgPrintedStart + 1; ?>
					<?php endfor; ?>
					<?php if($avgStarValue<$avgRating): ?>
						<i class="half"> </i>
						<?php $avgPrintedStart = $avgPrintedStart + 1; ?>
					<?php endif; ?>
					<?php if($avgPrintedStart < $avgMaxRating): ?>
						<?php for($i = $avgMaxRating-$avgPrintedStart; $i > 0;$i--): ?>
							<i class="empty"> </i>
						<?php endfor; ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="media media-rating-detail" style="display:none" id="media-rating_<?php echo $item->rating_id;?>">
				<label class="food-label">Food</label>
				<div class="food-rating-wrp">
					<?php $foodRating        = $item->food_rating;?>
					<?php $foodStarValue     = floor($item->food_rating);?>
					<?php $foodMaxRating     = 5; ?>
					<?php $foodPrintedStart  = 0 ;?>
					<?php for($f = 1; $f <= $foodStarValue;$f++): ?>
						<i class="full"> </i>
						<?php $foodPrintedStart = $foodPrintedStart + 1; ?>
					<?php endfor; ?>
					<?php if($foodStarValue<$foodRating): ?>
						<i class="half"> </i>
						<?php $foodPrintedStart = $foodPrintedStart + 1; ?>
					<?php endif; ?>
					<?php if($foodPrintedStart < $foodMaxRating): ?>
						<?php for($i = $foodMaxRating-$foodPrintedStart; $i > 0;$i--): ?>
							<i class="empty"> </i>
						<?php endfor; ?>
					<?php endif; ?>
				</div>

				<label class="service-label">Service</label>
				<div class="service-rating-wrp">
					<?php $serviceRating        = $item->service_rating;?>
					<?php $serviceStarValue     = floor($item->service_rating); ?>
					<?php $serviceMaxRating     = 5; ?>
					<?php $ServicePrintedStart  = 0 ;?>
					<?php for($s = 1; $s <= $serviceStarValue;$s++): ?>
						<i class="full"> </i>
						<?php $ServicePrintedStart = $ServicePrintedStart + 1; ?>
					<?php endfor; ?>
					<?php if($serviceStarValue<$serviceRating): ?>
						<i class="half"> </i>
						<?php $ServicePrintedStart = $ServicePrintedStart + 1; ?>
					<?php endif; ?>
					<?php if($ServicePrintedStart < $serviceMaxRating): ?>
						<?php for($i = $serviceMaxRating-$ServicePrintedStart; $i > 0;$i--): ?>
							<i class="empty"> </i>
						<?php endfor; ?>
					<?php endif; ?>
				</div>

				<label class="atmosphere-label">Atmosphere</label>
				<div class="atmosphere-rating-wrp">
					<?php $atmRating        = $item->atmosphere_rating;?>
					<?php $atmStarValue     = floor($item->atmosphere_rating); ?>
					<?php $atmMaxRating     = 5; ?>
					<?php $atmPrintedStart  = 0 ;?>
					<?php for($at = 1; $at <= $atmStarValue;$at++): ?>
						<i class="full"> </i>
						<?php $atmPrintedStart = $atmPrintedStart + 1; ?>
					<?php endfor; ?>
					<?php if($atmStarValue<$atmRating): ?>
						<i class="half"> </i>
						<?php $atmPrintedStart = $atmPrintedStart + 1; ?>
					<?php endif; ?>
					<?php if($atmPrintedStart < $atmMaxRating): ?>
						<?php for($i = $atmMaxRating-$atmPrintedStart; $i > 0;$i--): ?>
							<i class="empty"> </i>
						<?php endfor; ?>
					<?php endif; ?>
				</div>

				<label class="value-label">Value</label>
				<div class="value-rating-wrp">
					<?php $valueRating        = $item->value_rating;?>
					<?php $valueStarValue     = floor($item->value_rating); ?>
					<?php $valueMaxRating     = 5; ?>
					<?php $valueprintedStart  = 0 ;?>
					<?php for($v = 1; $v <= $valueStarValue;$v++): ?>
						<i class="full"> </i>
						<?php $valueprintedStart = $valueprintedStart + 1; ?>
					<?php endfor; ?>
					<?php if($valueStarValue<$valueRating): ?>
						<i class="half"> </i>
						<?php $valueprintedStart = $valueprintedStart + 1; ?>
					<?php endif; ?>
					<?php if($valueprintedStart < $valueMaxRating): ?>
						<?php for($i = $valueMaxRating-$valueprintedStart; $i > 0;$i--): ?>
							<i class="empty"> </i>
						<?php endfor; ?>
					<?php endif; ?>
				</div>
			</div>
			<?php if (!empty($item->thumb_avatar)):?>
				<?php $pos = strpos($item->thumb_avatar, 'facebook'); ?>
				<div class="biggest-spender-section">
					<ul>
						<li>
						<?php if ($pos > 0):?>
							<a href="<?php echo 'https://www.facebook.com/'.$item->fb_id;?>" target="_blank">
							<img src="<?php echo $item->thumb_avatar;?>" alt="" />
							</a>
						<?php else:?>
							<a data-toggle="modal" data-target="#myFacebookFriendsModal">
							<img src="<?php echo JURI::root().'/images/beseated/'.$item->thumb_avatar;?>" alt="" />
							</a>
						<?php endif; ?>
						</li>
					</ul>
				</div>
			<?php else:?>
				<?php $pos = strpos($item->thumb_avatar, 'facebook'); ?>
				<div class="biggest-spender-section">
					<ul>
						<li>
						<?php if ($pos > 0):?>
							<a href="<?php echo 'https://www.facebook.com/'.$item->fb_id;?>" target="_blank">
							<img src="<?php echo JURI::root().'images/bcted/default/banner.png'?>" alt="" />
							</a>
						<?php else:?>
							<a data-toggle="modal" data-target="#myFacebookFriendsModal">
							<img src="<?php echo JURI::root().'images/bcted/default/banner.png'?>" alt="" />
							</a>
						<?php endif; ?>
						</li>
					</ul>
				</div>
			<?php endif;?>
			<div class="msg_with_date">
					<div class="media-body">
						<?php echo trim($item->rating_comment); ?>
					</div>
					<div class="rw-user-info">
						<span><?php echo $item->full_name; ?></span> /
						<span> <?php echo date('d-M-Y',strtotime($item->created)); ?></span>
					</div>
			</div>
		</div>
		<?php endforeach; ?>
		</div>
	</div>
</div>
<style>
div.modal.fade{display: none;}
div.modal.fade.in{display: block;}
</style>
<!-- Modal -->
<div id="myFacebookFriendsModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="modal-message">Oops…the user is not connected on Facebook.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">


jQuery(document).ready(function($) {
	jQuery('.small-rating-wrp').click(function(event) {
		var id       = jQuery(this).attr('id');
		var ratingId = jQuery('#media-rating_'+id);
		jQuery(ratingId).css('display', 'block');
		jQuery('.rating-details-'+id).css('background-color', '#2c2208');


	});
});
</script>
