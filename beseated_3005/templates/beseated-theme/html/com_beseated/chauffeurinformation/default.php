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

$app              = JFactory::getApplication();
$input            = JFactory::getApplication()->input;
$chauffeurId      = $input->getInt('chauffeur_id');
$Itemid           = $input->get('Itemid', 0, 'int');
$this->user       = JFactory::getUser();
$this->isRoot     = $this->user->authorise('core.admin');
$chaufferDetail   = $this->chaufferDetail;
$chaufferServices = $this->chaufferServices;
$menu             = $app->getMenu();
$menuItem         = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
$loginItemid      = $menuItem->id;
$bctParams        = BeseatedHelper::getExtensionParam();
$accessLevel      = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
$access           = array('access','link');
$property         = array($accessLevel,'index.php?option=com_beseated&view=chauffeurinformation');
$menuItem2        = $menu->getItems( $access, $property, true );
$link2            = 'index.php?option=com_beseated&view=chauffeurinformation&chauffeur_id='.$chauffeurId.'&Itemid='.$menuItem2->id.'&addtofavourite=true';
$loginLink        = 'index.php?option=com_users&view=login&Itemid='.$loginItemid.'&return='.base64_encode($link2);
$imagesCount      = count($this->images);
$document         = JFactory::getDocument();
?>

<section class="page-section page-chauffeur-information">
	<div class="container">
		<h2 class="heading-1"><?php echo ucfirst($chaufferDetail->chauffeur_name); ?></h2>
		
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div class="bordered-box">
					<button data-chauffeur-id="<?php echo $chaufferDetail->chauffeur_id; ?>" data-user-id="<?php echo $this->user->id; ?>" data-is-favorite="<?php echo $this->isFavourite ? 'true' : 'false'; ?>" type="button" class="toggle-favorite button">
						<?php echo !$this->isFavourite ? 'Add to favorites' : 'Remove from favorites' ?>							
					</button>
					<span class="rating">
						<?php $overallRating = $chaufferDetail->avg_ratting; ?>
		      	<?php $starValue = floor($chaufferDetail->avg_ratting); ?>
		      	<?php $maxRating = 5; ?>
		      	<?php $printedStart = 0 ;?>
						<?php for($i = 1; $i <= $starValue;$i++): ?>
							<i class="full-large"> </i>
							<?php $printedStart = $printedStart + 1; ?>
						<?php endfor; ?>
						<?php if($starValue<$overallRating): ?>
							<i class="half-large"> </i>
							<?php $printedStart = $printedStart + 1; ?>
						<?php endif; ?>
						<?php if($printedStart < $maxRating): ?>
							<?php for($i = $maxRating-$printedStart; $i > 0;$i--): ?>
								<i class="empty-large"> </i>
							<?php endfor; ?>
						<?php endif; ?>
					</span>
				</div>
			</div>
		</div>

		<div class="row">			
			<?php foreach ($chaufferServices as $key => $service):
					$image = $service->image ?: $result->image;
			?>
				<div class="col-md-8 col-md-offset-2 chauffeur">
					<div class="row">
						<div class="col-md-6">
							<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=chauffeurservicebooking&service_id='.$service->service_id.'&Itemid='.$Itemid);?>">
								<div class="image" style="background-image: url(<?php echo $image ? JUri::base().'images/beseated/'.$image : 'images/bcted/default/banner.png' ?>);">								
								</div>
							</a>								
						</div>
						<div class="col-md-6">
							<h3 class="heading-1"><?php echo ucfirst($service->service_name);?></h3>
							<p class="capacity"><?php echo $service->capacity; ?> PPL</p>
							<hr>
							<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=chauffeurservicebooking&service_id='.$service->service_id.'&Itemid='.$Itemid);?>" class="button">
								Beseated
							</a>							
						</div>						
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<script type="text/javascript">
	function toggleFavorite(event) {
		var element = $(event.currentTarget);
		var isFavourite = element.data('isFavourite');
		var task = isFavourite ? 'removefromfavourite' : 'addtofavourite';
		var data = {
			option: 'com_beseated',
			task: 'chauffeurs.' + task,
			chauffeur_id: element.data('chauffeurId'),
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

	if(window.location.search.match('addtofavourite=true')) {
		var element = $('.toggle-favorite');
		var isFavourite = element.data('isFavourite');

		if (!isFavourite) {
			$('.toggle-favorite').trigger('click');
		};		
	}
</script>

