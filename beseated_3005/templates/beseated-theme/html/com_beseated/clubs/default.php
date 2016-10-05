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

$input               = JFactory::getApplication()->input;
$Itemid              = $input->get('Itemid', 0, 'int');
$app                 = JFactory::getApplication();
$menu                = $app->getMenu();
$inner_search        = $app->input->cookie->get('inner_search', '');
$myfriends_attending = $app->input->cookie->get('myfriends_attending', 0);
$caption             = $app->input->cookie->get('caption', '');
$menuItem       		 = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubs', true );
$this->user     		 = JFactory::getUser();
$this->isRoot   		 = $this->user->authorise('core.admin');

?>

<section class="page-section page-venues">
	<div class="container">
		<h2 class="heading-1">Our Venues</h2>
		<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=clubs&Itemid='.$Itemid);?>" class="filters-form" method="post">
			<h3 class="heading-3">Search Venues</h3>
			<img class="icon" src="templates/beseated-theme/images/search.png" alt="">
			<div class="bordered-box">
				<div class="field">
					<input type="text" class="form-control" placeholder="Venue Name" name="caption" value="<?php echo $caption; ?>">
				</div>				
				<button class="button">Search</button>
			</div>
			<input type="hidden" name="inner_search" value="1">
		</form>
		<div class="row">
			<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubinformation', true );?>
			<?php foreach ($this->items as $key => $result):?>
				<?php $image = $result->thumb_image ?: $result->image; ?>
				<div class="col-md-4">
					<div class="item-box">
						<a class="image"
							 href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubinformation&club_id='.$result->venue_id.'&Itemid='.$menuItem->id ) ?>"
							 style="background-image: url(<?php echo $image ? JUri::base().'images/beseated/'.$image : 'images/bcted/default/banner.png' ?>)">
						</a>
						<h3 class="heading-3">
							<span class="text">
								<?php echo $result->venue_name; ?>
							</span>
							<span class="rating">
								<?php $full = floor($result->avg_ratting); $half = $result->avg_ratting - $full > 0; $empty = 5 - ceil($result->avg_ratting); ?>
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
						</h3>
						<p class="description">
							<?php echo $result->venue_type; ?>
							<span class="city right"><?php echo $result->city; ?></span>
						</p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		
		<?php echo $this->pagination->getListFooter(); ?>
	</div>
</section>
