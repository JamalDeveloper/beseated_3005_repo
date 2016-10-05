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
$app          = JFactory::getApplication();
$menu         = $app->getMenu();
?>

<section class="page-section page-favourites">
	<div class="container">

		<?php include_once('templates/beseated-theme/partials/guest-profile-menu.php') ?>

		<div class="row">
			<div class="col-md-12">
				<div class="tabs-headers">
				    <h2 class="tab-header heading-3 active" data-target="venues">Venues</h2>
				    <h2 class="tab-header heading-3" data-target="chauffeurs">Chauffeurs</h2>
				    <h2 class="tab-header heading-3" data-target="protections">Protections</h2>
				    <h2 class="tab-header heading-3" data-target="yachts">Yachts</h2>
				</div>
			</div>
		</div>
		<div class="tabs-content">
			<div class="row active" id="venues">
				<div class="col-md-8 col-md-offset-2">
					<?php if (count($this->items) > 0):
						foreach ($this->items as $key => $result):
						$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubinformation', true );
						$Itemid   = $menuItem->id;
						$image = $result->thumb_image;?>

						<div class="row fav-item">
							<div class="col-md-6">
								<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubinformation&club_id='.$result->element_id.'&Itemid='.$Itemid) ?>">
									<div class="image" style="background-image: url(<?php echo JUri::root().(isset($image) ? 'images/beseated/'.$result->thumb_image : 'images/beseated/default/banner.png') ?>);">								
									</div>
								</a>
							</div>
							<div class="col-md-6">
								<h3 class="heading-1"><?php echo $result->venue_name; ?></h3>
								<p class="location"><?php echo $result->city; ?></p>
								<hr>
								<div class="rating">
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
								</div>
							</div>
						</div>

					<?php endforeach;
						else: echo '<span class="empty">You do not have Venues in your favourites!</span>'; endif; ?>	
				</div>
			</div>

			<div class="row" id="chauffeurs">
				<div class="col-md-8 col-md-offset-2">
					<?php if (count($this->chauffeurFavourite) > 0):
						foreach ($this->chauffeurFavourite as $key => $result):
						$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=chauffeurinformation', true );
						$Itemid   = $menuItem->id;
						$image = $result->thumb_image;?>

						<div class="row fav-item">
							<div class="col-md-6">
								<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=chauffeurinformation&chauffeur_id='.$result->element_id.'&Itemid='.$Itemid) ?>">
									<div class="image" style="background-image: url(<?php echo JUri::root().(isset($image) ? 'images/beseated/'.$result->thumb_image : 'images/beseated/default/banner.png') ?>);">									
									</div>
								</a>
							</div>
							<div class="col-md-6">
								<h3 class="heading-1"><?php echo $result->chauffeur_name; ?></h3>
								<p class="location"><?php echo $result->city; ?></p>
								<hr>
								<div class="rating">
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
								</div>
							</div>
						</div>

					<?php endforeach;
						else: echo '<span class="empty">You do not have Chauffeurs in your favourites!</span>'; endif; ?>		
				</div>	
			</div>

			<div class="row" id="protections">
				<div class="col-md-8 col-md-offset-2">
					<?php if (count($this->protectionFavourite) > 0):
						foreach ($this->protectionFavourite as $key => $result):
						$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=protectioninformation', true );
						$Itemid   = $menuItem->id;
						$image = $result->thumb_image;?>

						<div class="row fav-item">
							<div class="col-md-6">
								<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=protectioninformation&protection_id='.$result->element_id.'&Itemid='.$Itemid) ?>">
									<div class="image" style="background-image: url(<?php echo JUri::root().(isset($image) ? 'images/beseated/'.$result->thumb_image : 'images/beseated/default/banner.png') ?>);">								
									</div>
								</a>
							</div>
							<div class="col-md-6">
								<h3 class="heading-1"><?php echo $result->protection_name; ?></h3>
								<p class="location"><?php echo $result->city; ?></p>
								<hr>
								<div class="rating">
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
								</div>
							</div>
						</div>

					<?php endforeach;
						else: echo '<span class="empty">You do not have Protections in your favourites!</span>'; endif; ?>		
				</div>	
			</div>

			<div class="row" id="yachts">
				<div class="col-md-8 col-md-offset-2">
					<?php if (count($this->yachtFavourite) > 0):
						foreach ($this->yachtFavourite as $key => $result):
						$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=yachtinformation', true );
						$Itemid   = $menuItem->id;
						$image = $result->thumb_image;?>

						<div class="row fav-item">
							<div class="col-md-6">
								<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=yachtinformation&yacht_id='.$result->element_id.'&Itemid='.$Itemid) ?>">
									<div class="image" style="background-image: url(<?php echo JUri::root().(isset($image) ? 'images/beseated/'.$result->thumb_image : 'images/beseated/default/banner.png') ?>);">								
									</div>
								</a>
							</div>
							<div class="col-md-6">
								<h3 class="heading-1"><?php echo $result->yacht_name; ?></h3>
								<p class="location"><?php echo $result->city; ?></p>
								<hr>
								<div class="rating">
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
								</div>
							</div>
						</div>

					<?php endforeach;
						else: echo '<span class="empty">You do not have Protections in your favourites!</span>'; endif; ?>		
				</div>	
			</div>

		</div>
	</div>
</section>

<script type="text/javascript">
	$('.tabs-headers').tabs();
</script>
