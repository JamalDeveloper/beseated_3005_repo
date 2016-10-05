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

$app                = JFactory::getApplication();
$input              = JFactory::getApplication()->input;
$protectionId       = $input->getInt('protection_id');
$Itemid             = $input->get('Itemid', 0, 'int');
$this->user         = JFactory::getUser();
$this->isRoot       = $this->user->authorise('core.admin');
$protectionDetail   = $this->protectionDetail;
$protectionServices = $this->protectionServices;
$menu               = $app->getMenu();
$menuItem           = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
$loginItemid        = $menuItem->id;
$bctParams          = BeseatedHelper::getExtensionParam();
$accessLevel        = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
$access             = array('access','link');
$property           = array($accessLevel,'index.php?option=com_beseated&view=protectioninformation');
$menuItem2          = $menu->getItems( $access, $property, true );
$link2              = 'index.php?option=com_beseated&view=protectioninformation&protection_id='.$protectionId.'&Itemid='.$menuItem2->id;
$loginLink          = 'index.php?option=com_users&view=login&Itemid='.$loginItemid.'&return='.base64_encode($link2);
$imagesCount        = count($this->images);
$document           = JFactory::getDocument();
/*$document->addStylesheet(JUri::root().'components/com_beseated/assets/carousal/carousal.css');
$document->addScript(JUri::root().'components/com_beseated/assets/carousal/jquery.jcarousel.min.js');
$document->addScript(JUri::root().'components/com_beseated/assets/carousal/carousal.js');*/
$document->addScript(JUri::root().'modules/mod_profileslider/media/js/html5gallery.js');
?>
<div class="chauffer-wrapper">
	<div class="chauffeurs-title">
		<?php echo ucfirst($protectionDetail->protection_name); ?>
	</div>
	<div class="chauffeurs-image">
		<?php if ($imagesCount > 0): ?>
			<div style="display:none;" class="html5gallery" data-skin="gallery" data-width="500" data-height="300" >
				<?php for ($i = 0; $i < $imagesCount; $i++): ?>
					<?php if($this->images[$i]->is_video > 0):?>
						<a href="<?php echo JUri::base()."images/beseated/".$this->images[$i]->image; ?>"><img src="<?php echo JUri::base()."images/beseated/".$this->images[$i]->thumb_image; ?>"></a>
						<input type="hidden" class="image-id"value="<?php echo $this->images[$i]->image_id;?>">
					<?php elseif(!empty($this->images[$i]->image)):?>
						<a href="<?php echo JUri::base()."images/beseated/".$this->images[$i]->image; ?>"><img src="<?php echo JUri::base()."images/beseated/".$this->images[$i]->image; ?>"></a>
					<?php else: ?>
						<img id="display_venue_images" src="images/beseated/default/banner.png" alt="" />
					<?php endif; ?>
				<?php endfor; ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="chauffeurs-fav">
		<div class="favourite-wrp">
			<button id="favourite-add" onclick="addProtectionToFavourite('<?php echo $protectionDetail->protection_id; ?>','<?php echo $this->user->id; ?>')" type="button" class="fav-btn"></button>
			<button id="favourite-remove" onclick="removeProtectionFromFavourite('<?php echo $protectionDetail->protection_id; ?>','<?php echo $this->user->id; ?>')" type="button" class="fav-btn active"></button>
			<?php if(!$this->isFavourite):?>
				<script type="text/javascript">
					jQuery('#favourite-add').show();
					jQuery('#favourite-remove').hide();
				</script>
			<?php else: ?>
			<script type="text/javascript">
				jQuery('#favourite-add').hide();
			    jQuery('#favourite-remove').show();
			</script>
			<?php endif; ?>
		</div>
	</div>
	<div class="chauffeurs-rating">
		<div class="rating-wrp span6">
        	<?php $overallRating = $protectionDetail->avg_ratting; ?>
        	<?php $starValue = floor($protectionDetail->avg_ratting); ?>
        	<?php $maxRating = 5; ?>
        	<?php $printedStart = 0 ;?>
			<?php for($i = 1; $i <= $starValue;$i++): ?>
				<i class="full"> </i>
				<?php $printedStart = $printedStart + 1; ?>
			<?php endfor; ?>
			<?php if($starValue<$overallRating): ?>
				<i class="half"> </i>
				<?php $printedStart = $printedStart + 1; ?>
			<?php endif; ?>
			<?php if($printedStart < $maxRating): ?>
				<?php for($i = $maxRating-$printedStart; $i > 0;$i--): ?>
					<i class="empty"> </i>
				<?php endfor; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
<div class="chauffer-service-wrapper">
	<?php foreach ($protectionServices as $key => $service):?>
		<div class="chauffer-service-detail">
			<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=protectionservicebooking&service_id='.$service->service_id.'&Itemid='.$Itemid);?>">
				<div class="chauffer-service-image">
					<img src="<?php echo JURI::root().'images/beseated/'.$service->image;?>">
				</div>
			</a>
			<div class="chauffer-details">
				<div class="chauffer-service-title">
					<?php echo ucfirst($service->service_name);?>
				</div>
				<div class="chauffer-service-capecity">
					<?php echo BeseatedHelper::currencyFormat($service->currency_code,$service->currency_code,$service->price_per_hours); ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>


<script type="text/javascript">
	function addProtectionToFavourite(protectionsID,userID)
	{
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=protections.addtofavourite',
			type: 'GET',
			data: '&protection_id='+protectionsID+'&user_id='+userID,
			success: function(response){
	        	if(response == "1" || response == "2")
	        	{
	        		jQuery('#favourite-add').hide();
	        		jQuery('#favourite-remove').show();
	        	}
	        	else if(response == "3")
	        	{
	        		window.location.href="<?php echo $loginLink; ?>";
	        	}
	        }
		})
		.done(function() {
		})
		.fail(function() {
		})
		.always(function() {
		});
	}

	function removeProtectionFromFavourite(protectionsID,userID)
	{
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=protections.removefromfavourite',
			type: 'GET',
			data: '&protection_id='+protectionsID+'&user_id='+userID,
			success: function(response){
	        	if(response == "1" || response == "2")
	        	{
	        		jQuery('#favourite-add').show();
	        		jQuery('#favourite-remove').hide();
	        	}
	        }
		})
		.done(function() {
		})
		.fail(function() {
		})
		.always(function() {
		});
	}
</script>

