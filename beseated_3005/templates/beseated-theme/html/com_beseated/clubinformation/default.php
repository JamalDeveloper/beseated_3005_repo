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

$input       = JFactory::getApplication()->input;
$Itemid      = $input->get('Itemid', 0, 'int');
$clubID      = $input->get('club_id', 0, 'int');
$app         = JFactory::getApplication();
$menu        = $app->getMenu();
$menuItem    = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
$loginItemid = $menuItem->id;
$bctParams   = BeseatedHelper::getExtensionParam();
$accessLevel = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
$access      = array('access','link');
$property    = array($accessLevel,'index.php?option=com_beseated&view=clubinformation');
$menuItem2   = $menu->getItems( $access, $property, true );
$link2       = 'index.php?option=com_beseated&view=clubinformation&club_id='.$clubID.'&Itemid='.$menuItem2->id.'&addtofavourite=true';
$loginLink   = 'index.php?option=com_users&view=login&Itemid='.$loginItemid.'&return='.base64_encode($link2);
$imagesCount = count($this->images);
$lat = $this->club->latitude;
$lon = $this->club->longitude;
?>


<section class="page-section page-venue-info">
	<div class="container">
		<div class="sub-menu">
			<?php foreach (JModuleHelper::getModules('position-8') as $module) { 
			 	echo JModuleHelper::renderModule($module); 
			} ?>
		</div>
	</div>
	<?php if ($imagesCount > 0): ?>
		<div class="venue-carousel">		
			<div class="owl-carousel">
				<?php for ($i = 0; $i < $imagesCount; $i++): ?>
					<div><img src="<?php echo JUri::base()."images/beseated/".$this->images[$i]->image; ?>"></div>
				<?php endfor; ?>	
			</div>
		</div>	
	<?php endif; ?>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="detail">
					<span class="fav">
						<button data-venue-id="<?php echo $this->club->venue_id; ?>" data-user-id="<?php echo $this->user->id; ?>" data-is-favorite="<?php echo $this->isFavourite ? 'true' : 'false'; ?>" type="button" class="toggle-favorite button">
							<?php echo !$this->isFavourite ? 'Add to favorites' : 'Remove from favorites' ?>							
						</button>
					</span>
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
				</div>
				<div class="desc"><?php echo $this->club->description; ?></div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3">
				<div class="music">
					<h3 class="heading-3">Music</h3>
					<span class="type"><?php echo $this->club->music;?></span>
				</div>
			</div>
			<div class="col-md-offset-3 col-md-6">
				<div class="bordered-box days-open">
					<h3 class="heading-3">Days Open</h3>
					<?php foreach(array('MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN') as $i => $day) { ?>
						<?php $open = strpos($this->club->working_days, (string)($i+1)) !== false; ?>
						<div class="day">
							<span class="name"><?php echo $day; ?></span>					
							<span class="<?php echo $open ? 'is-open-icon' : 'is-closed-icon'; ?>"></span>
						</div>					
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3">
				<h3 class="heading-3">Location</h3>
			</div>
		</div>
	</div>
</section>

<div id="map"></div>
<script>
	var map = new GMaps({
	  el: '#map',
	  lat: '<?php echo $lat; ?>',
	  lng: '<?php echo $lon; ?>'
	});

	map.addMarker({
		lat: '<?php echo $lat; ?>',
	  lng: '<?php echo $lon; ?>'
	});

	$('.owl-carousel').owlCarousel({
		animateOut: 'fadeOut',
		loop: true,
		nav: true,
		navText: [
    	"<i class='icon-arrow-left'></i>",
    	"<i class='icon-arrow-right'></i>"
    ],
		dots: true,
		autoplay: true,
		items: 1
	});

	function toggleFavorite(event) {
		var element = $(event.currentTarget);
		var isFavourite = element.data('isFavourite');
		var task = isFavourite ? 'removefromfavourite' : 'addtofavourite';
		var data = {
			option: 'com_beseated',
			task: 'venues.' + task,
			venue_id: element.data('venueId'),
			user_id: element.data('userId'),
		};

		$.get('index.php', data, function(response) {
			if(response === '3') {
				window.location.href="<?php echo $loginLink; ?>";
			} else {
				element
					.data('isFavourite', !isFavourite)
					.html(isFavourite ? 'Add to favorites' : 'Remove from favorites');
			}
		});
	}

	$('.toggle-favorite').on('click', toggleFavorite.bind(this))

	if(window.location.search.match('addtofavourite')) {
		var element = $('.toggle-favorite');
		var isFavourite = element.data('isFavourite');

		if (!isFavourite) {
			$('.toggle-favorite').trigger('click');
		};		
	}
</script>


