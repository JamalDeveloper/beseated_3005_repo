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

$input        = JFactory::getApplication()->input;
$Itemid       = $input->get('Itemid', 0, 'int');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');

?>

<section class="page-section page-venue-reviews">
	<div class="container">
		<div class="sub-menu">
			<?php foreach (JModuleHelper::getModules('position-8') as $module) { 
		 		echo JModuleHelper::renderModule($module); 
			} ?>
		</div>
		<div class="row">
			<div class="reviews-box">
				<span class="rating">
					<?php $full = floor($this->club->avg_ratting); $half = $this->club->avg_ratting - $full > 0; $empty = 5 - ceil($this->club->avg_ratting); ?>
             <?php for($i = 0; $i < $full; $i++): ?>
               <i class="full-large"></i>
             <?php endfor; ?>
             <?php if($half): ?>
               <i class="half-large"></i>
             <?php endif; ?>
             <?php for($i = 0; $i < $empty; $i++): ?>
               <i class="empty-large"></i>
          <?php endfor; ?>
        </span>    
	      <ul>
					<?php foreach(array('food', 'service', 'atmosphere', 'value') as $rating_type) { ?>
						<?php $ratings = array_map(function($item) use ($rating_type) { return $item->{$rating_type . '_rating'}; }, $this->items); ?>
						<?php $rating = array_sum($ratings) / count($ratings); ?>
		      	<li>		
		      		<label><?php echo ucfirst($rating_type); ?></label>
		      		<span class="rating">
		        		<?php $full = floor($rating); $half = $rating - $full > 0; $empty = 5 - ceil($rating); ?>
	        			<?php for($i = 0; $i < $full; $i++): ?>
	                <i class="full"></i>
	              <?php endfor; ?>
	              <?php if($half): ?>
	                <i class="half"></i>
	              <?php endif; ?>
	              <?php for($i = 0; $i < $empty; $i++): ?>
	                <i class="empty"></i>
		            <?php endfor; ?>
		      		</span>
		      	</li>
					<?php } ?>
	      </ul>
			</div>
			<div class="row">
				<?php foreach ($this->items as $key => $item):?>
					<div class="col-md-12">
						<div class="bordered-box single-review">
							<div class="avatar">
								<?php $hasAvatar = !empty($item->thumb_avatar); ?>
								<?php if($hasAvatar): ?>
									<?php $isFacebook = strpos($item->thumb_avatar, 'facebook') > 0; ?>
									<?php if($isFacebook): ?>
										<a href="<?php echo 'https://www.facebook.com/'.$item->fb_id;?>" target="_blank">
											<img src="<?php echo $item->thumb_avatar;?>" alt="" class="img-circle" />
										</a>
									<?php else:?>
										<img src="<?php echo JURI::root().'/images/beseated/'.$item->thumb_avatar;?>" class="img-circle" alt="" />
									<?php endif; ?>
								<?php else:?>								
										<img src="<?php echo JURI::root().'images/bcted/default/banner.png'?>" class="img-circle" alt="" />
								<?php endif; ?>
							</div>
							<div class="copy">
								<span class="author"><?php echo $item->name; ?></span>
								<span class="comment">It’s an amazing place! I would go there again!</span>
								<span class="date"><?php echo date('F d, Y @ H:i',strtotime($item->created)); ?></span> 
							</div>
							<span class="rating">
			        		<?php $full = floor($item->avg_rating); $half = $item->avg_rating - $full > 0; $empty = 5 - ceil($item->avg_rating); ?>
		        			<?php for($i = 0; $i < $full; $i++): ?>
			                <i class="full-large"></i>
			              <?php endfor; ?>
			              <?php if($half): ?>
			                <i class="half-large"></i>
			              <?php endif; ?>
			              <?php for($i = 0; $i < $empty; $i++): ?>
			                <i class="empty-large"></i>
				            <?php endfor; ?>
				      		</span>
						</div>
					</div>	
				<?php endforeach; ?>
			</div>	
		</div>
	</div>
</section>