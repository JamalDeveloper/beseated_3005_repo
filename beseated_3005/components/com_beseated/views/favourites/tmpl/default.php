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

$input        = JFactory::getApplication()->input;
$Itemid       = $input->get('Itemid', 0, 'int');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
$app          = JFactory::getApplication();
$menu         = $app->getMenu();
$bctParams        = BeseatedHelper::getExtensionParam();
$accessLevel      = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
$access           = array('access','link');
$property         = array($accessLevel,'index.php?option=com_beseated&view=chauffeurinformation');
$menuItem2        = $menu->getItems( $access, $property, true );
?>
<div class="wrapper">
	<div class="tabbable boxed parentTabs">
	    <ul class="nav nav-tabs">
	        <li class="active"><a href="#set1">Venues</a>
	        </li>
	        <li><a href="#set2">Luxury</a>
	        </li>
	    </ul>
	    <div class="tab-content">
	        <div class="tab-pane fade active in" id="set1">
	            <div class="tabbable">
                  	<?php foreach ($this->items as $key => $result):?>
						<div class="venue_blck">
							<div class="fav-venue-img">
								<?php if ($this->user->id > 0){
									$property     = array($accessLevel,'index.php?option=com_beseated&view=clubinformation');
									$clubMenuItem = $menu->getItems( $access, $property, true );
								}
								else{
									$clubMenuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubinformation', true );
								}?>
								<?php $Itemid   = $clubMenuItem->id;?>
								<?php $imgPath  = JPATH_SITE."/images/beseated/". $result->image;?>
								<?php if(file_exists($imgPath)): ?>
									<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubinformation&club_id='.$result->element_id.'&Itemid='.$Itemid) ?>"><img src="<?php echo JUri::root()."images/beseated/". $result->thumb_image; ?>" alt="" /></a>
								<?php else: ?>
									<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubinformation&club_id='.$result->element_id.'&Itemid='.$Itemid) ?>"><img src="images/beseated/default/banner.png" alt="" /></a>
								<?php endif; ?>
								<div class="fav-rating-title">
									<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubinformation&club_id='.$result->element_id.'&Itemid='.$Itemid) ?>">
										<div class="venue-title">
											<h4><?php echo $result->venue_name; ?></h4>
											<div class="venue-location"><?php echo $result->city; ?></div>
										</div>
			                            <div class="rating-wrp">
			                            	<?php $result->avg_ratting = ceil($result->avg_ratting); ?>
											<?php for($i = 1; $i <= $result->avg_ratting;$i++): ?>
												<i class="full"> </i>
											<?php endfor; ?>
											<?php for($i = $result->avg_ratting+1; $i <= 5; $i++): ?>
												<i class="empty"> </i>
											<?php endfor; ?>
										</div>
									</a>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
	            </div>
	        </div>
	        <div class="tab-pane fade" id="set2">
	            <div class="tabbable">
					<?php if (count($this->protectionFavourite) > 0):?>
						<?php foreach ($this->protectionFavourite as $key => $result):?>
							<div class="venue_blck">
								<div class="fav-venue-img">
									<?php if ($this->user->id > 0){
										$property           = array($accessLevel,'index.php?option=com_beseated&view=protectioninformation');
										$protectionMenuItem = $menu->getItems( $access, $property, true );
									}
									else{
										$protectionMenuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=protectioninformation', true );
									}?>
									<?php $Itemid   = $protectionMenuItem->id;?>
									<?php $imgPath  = JPATH_SITE."/images/beseated/". $result->image;?>
									<?php if(file_exists($imgPath)): ?>
										<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=protectioninformation&protection_id='.$result->element_id.'&Itemid='.$Itemid) ?>"><img src="<?php echo JUri::root()."images/beseated/". $result->thumb_image; ?>" alt="" /></a>
									<?php else: ?>
										<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=protectioninformation&protection_id='.$result->element_id.'&Itemid='.$Itemid) ?>"><img src="images/beseated/default/banner.png" alt="" /></a>
									<?php endif; ?>
									<div class="fav-rating-title">
										<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=protectioninformation&protection_id='.$result->element_id.'&Itemid='.$Itemid) ?>">
											<div class="venue-title">
												<h4><?php echo $result->protection_name; ?></h4>
												<div class="fav-venue-location"><?php echo $result->city; ?></div>
											</div>
				                            <div class="rating-wrp">
				                            	<?php $result->avg_ratting = ceil($result->avg_ratting); ?>
												<?php for($i = 1; $i <= $result->avg_ratting;$i++): ?>
													<i class="full"> </i>
												<?php endfor; ?>
												<?php for($i = $result->avg_ratting+1; $i <= 5; $i++): ?>
													<i class="empty"> </i>
												<?php endfor; ?>
											</div>
										</a>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>

					<?php if (count($this->chauffeurFavourite) > 0):?>
						<?php foreach ($this->chauffeurFavourite as $key => $result):?>
							<div class="venue_blck">
								<div class="fav-venue-img">
									<?php if ($this->user->id > 0){
										$property           = array($accessLevel,'index.php?option=com_beseated&view=chauffeurinformation');
										$chauffeurMenuItem = $menu->getItems( $access, $property, true );
									}
									else{
										$chauffeurMenuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=chauffeurinformation', true );
									}?>
									<?php $Itemid   = $chauffeurMenuItem->id;?>
									<?php $imgPath  = JPATH_SITE."/images/beseated/". $result->image;?>
									<?php if(file_exists($imgPath)): ?>
										<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=chauffeurinformation&chauffeur_id='.$result->element_id.'&Itemid='.$Itemid) ?>"><img src="<?php echo JUri::root()."images/beseated/". $result->thumb_image; ?>" alt="" /></a>
									<?php else: ?>
										<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=chauffeurinformation&chauffeur_id='.$result->element_id.'&Itemid='.$Itemid) ?>"><img src="images/beseated/default/banner.png" alt="" /></a>
									<?php endif; ?>
									<div class="fav-rating-title">
										<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=chauffeurinformation&chauffeur_id='.$result->element_id.'&Itemid='.$Itemid) ?>">
											<div class="venue-title">
												<h4><?php echo $result->chauffeur_name; ?></h4>
												<div class="venue-location"><?php echo $result->city; ?></div>
											</div>
				                            <div class="rating-wrp">
				                            	<?php $result->avg_ratting = ceil($result->avg_ratting); ?>
												<?php for($i = 1; $i <= $result->avg_ratting;$i++): ?>
													<i class="full"> </i>
												<?php endfor; ?>
												<?php for($i = $result->avg_ratting+1; $i <= 5; $i++): ?>
													<i class="empty"> </i>
												<?php endfor; ?>
											</div>
										</a>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>

					<?php if (count($this->yachtFavourite) > 0):?>
						<?php foreach ($this->yachtFavourite as $key => $result):?>
							<div class="venue_blck">
								<div class="fav-venue-img">
									<?php if ($this->user->id > 0){
										$property      = array($accessLevel,'index.php?option=com_beseated&view=yachtinformation');
										$yachtMenuItem = $menu->getItems( $access, $property, true );
									}
									else{
										$yachtMenuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=yachtinformation', true );
									}?>
									<?php $Itemid   = $yachtMenuItem->id;?>
									<?php $imgPath  = JPATH_SITE."/images/beseated/". $result->image;?>
									<?php if(file_exists($imgPath)): ?>
										<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=yachtinformation&yacht_id='.$result->element_id.'&Itemid='.$Itemid) ?>"><img src="<?php echo JUri::root()."images/beseated/". $result->thumb_image; ?>" alt="" /></a>
									<?php else: ?>
										<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=yachtinformation&yacht_id='.$result->element_id.'&Itemid='.$Itemid) ?>"><img src="images/beseated/default/banner.png" alt="" /></a>
									<?php endif; ?>
									<div class="fav-rating-title">
										<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=yachtinformation&yacht_id='.$result->element_id.'&Itemid='.$Itemid) ?>">
											<div class="venue-title">
												<h4><?php echo $result->yacht_name; ?></h4>
												<div class="venue-location"><?php echo $result->city; ?></div>
											</div>
				                            <div class="rating-wrp">
				                            	<?php $result->avg_ratting = ceil($result->avg_ratting); ?>
												<?php for($i = 1; $i <= $result->avg_ratting;$i++): ?>
													<i class="full"> </i>
												<?php endfor; ?>
												<?php for($i = $result->avg_ratting+1; $i <= 5; $i++): ?>
													<i class="empty"> </i>
												<?php endfor; ?>
											</div>
										</a>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
	            </div>
	        </div>
	    </div>
	</div>
</div>





<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery("ul.nav-tabs a").click(function (e) {
	  e.preventDefault();
	    jQuery(this).tab('show');
	});
});
</script>
