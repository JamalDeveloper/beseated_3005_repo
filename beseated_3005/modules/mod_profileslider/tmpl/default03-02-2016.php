<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$profileImages = $images;
$imagesCount   = count($profileImages);
$document      = JFactory::getDocument();
$document->addStylesheet(JUri::root().'modules/mod_profileslider/media/css/carousal.css');
$document->addScript(JUri::root().'components/com_beseated/assets/carousal/jquery.jcarousel.min.js');
$document->addScript(JUri::root().'components/com_beseated/assets/carousal/carousal.js');
?>
<div class="span12 prof-img" style="float: none;" id="drop-box">
	<?php if($profileImages[0]->is_video > 0): ?>
		<video controls>
			<source src="<?php echo JUri::base()."images/beseated/".$profileImages[0]->image; ?>" class="display_venue_video" type="video/mp4">
			Your browser does not support HTML5 video.
		</video>
	<?php elseif(!empty($profileImages[0]->image)): ?>
		<img id="display_venue_images" class="display_venue_image" src="<?php echo JUri::root().'images/beseated/'. $profileImages[0]->image; ?>" alt="" />
	<?php else: ?>
		<img id="display_venue_images" src="images/beseated/default/banner.png" alt="" />
	<?php endif; ?>
	<div id="spinner">
		<img src="<?php echo JUri::base(); ?>images/beseated/default/ajax-loader.gif" alt="Loading..."/>
	</div>
</div>

<div class="wrapper">
<?php if($imagesCount > 0): ?>
	<div class="jcarousel-wrapper">
	   	<div class="jcarousel">
	        <ul>
		        <?php for ($i = 0; $i < $imagesCount; $i++): ?>
					<?php if($profileImages[$i]->is_video > 0):?>
						<li>
							<img src="<?php echo JUri::base()."images/beseated/".$profileImages[$i]->thumb_image;?>" class="video-thumb" value="<?php echo $profileImages[$i]->image_id; ?>" id="<?php echo JUri::base()."images/beseated/".$profileImages[$i]->image;?>" width="200" height="200" alt="">
							<div class="deleteimage" id="<?php echo $profileImages[$i]->image_id; ?>"></div>
						</li>
						<?php elseif(!empty($profileImages[$i]->image)):?>
        					<li>
	        					<img src="<?php echo JUri::base()."images/beseated/".$profileImages[$i]->thumb_image;?>" value="<?php echo $profileImages[$i]->image_id; ?>" width="200" height="200" alt="">
	        					<div class="deleteimage" id="<?php echo $profileImages[$i]->image_id; ?>"></div>
        					</li>
						<?php else: ?>
						<img src="<?php echo JUri::base()."images/beseated/default/banner.png";?>" alt="" />
					<?php endif; ?>
				<?php endfor; ?>
			</ul>
		</div>
		<?php if ($imagesCount > 2): ?>
			<a href="#" class="jcarousel-control-prev">&lsaquo;</a>
			<a href="#" class="jcarousel-control-next">&rsaquo;</a>
		<?php endif; ?>
			<!-- <p class="jcarousel-pagination"></p> -->
	</div>
<?php endif; ?>
	<div class="column-small-12 padd0">
		<input type="file" name="venue_image" id="venue_image" style="display: none;" />
	</div>
	<div class="addimage padd0" id="addimage"></div>
</div>

<script type="text/javascript">
jQuery("#addimage").click(function() {
	var totalImages = '<?php echo $imagesCount; ?>';
	if (totalImages == 10){
		alert('You have uploaded maximum number of images');
	}
	else{
		jQuery("input[id='venue_image']").click();
	}
});

jQuery(".jcarousel").children('ul').children('li').children('img').click(function() {
	var imageId   = jQuery(this).attr('value');
	var elementId = '<?php echo $profileImages[0]->element_id; ?>';
	var src       = jQuery(this).attr('src');
	var imgId     = jQuery(this).attr('id');

	jQuery.ajax({
		type: "GET",
		url: "index.php?option=com_beseated&task=profile.changeDefaultImage",
		data: "&image_id="+imageId+"&element_id="+elementId,
		success: function(response){
			if(response == "200"){
				jQuery('#currency_change_error').html('Main image changed successfully');
				jQuery('#currency_change_error').removeClass('alert-error');
				jQuery('#currency_change_error').addClass(' alert-success');
			}
			jQuery('#currency_change_error').show();

			if (typeof(imgId) === "undefined") {
				console.log('yes');
				jQuery('.display_venue_image').attr('src', src);
				location.reload();

			}else{
				jQuery('.display_venue_video').attr('src', imgId);
				jQuery('.display_venue_image').attr('src', src);
				location.reload();
			}

			/*if (imgClass == "video-thumb"){
				var source = jQuery(this).attr('id');
				console.log(source);
			}else{
			}*/

		}
	});
});


jQuery(".jcarousel").children('ul').children('li').children('.deleteimage').click(function(event) {
	var imageId     = jQuery(this).attr('id');
	var elementId   = '<?php echo $profileImages[0]->element_id; ?>';

	jQuery.ajax({
		type: "GET",
		url: "index.php?option=com_beseated&task=profile.deleteProfileImage",
		data: "&image_id="+imageId+"&element_id="+elementId,
		success: function(response){
			if(response == "200"){
				jQuery('#currency_change_error').html('Image deleted successfully');
				jQuery('#currency_change_error').removeClass('alert-error');
				jQuery('#currency_change_error').addClass(' alert-success');
			}
			location.reload();
			jQuery('#currency_change_error').show();
		}
	});
});

</script>