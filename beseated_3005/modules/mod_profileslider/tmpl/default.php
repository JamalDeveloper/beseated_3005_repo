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
$document->addStylesheet(JUri::root().'modules/mod_profileslider/media/css/style_slider.css');
$document->addScript(JUri::root().'modules/mod_profileslider/media/js/html5gallery.js');
?>
<div class="wrapper">
<?php if($imagesCount > 0): ?>
	<div  class="main-slider-area" style="text-align:center;">
	<div style="display:none;" class="html5gallery" data-skin="gallery" data-width="500" data-height="300" >
		<?php for ($i = 0; $i < $imagesCount; $i++): ?>
			<?php if($profileImages[$i]->is_video > 0):?>
				<a href="<?php echo JUri::base()."images/beseated/".$profileImages[$i]->image; ?>"><img src="<?php echo JUri::base()."images/beseated/".$profileImages[$i]->thumb_image; ?>"></a>
				<input type="hidden" class="image-id"value="<?php echo $profileImages[$i]->image_id;?>">
			<?php elseif(!empty($profileImages[$i]->image)):?>
				<a href="<?php echo JUri::base()."images/beseated/".$profileImages[$i]->image; ?>"><img src="<?php echo JUri::base()."images/beseated/".$profileImages[$i]->image; ?>"></a>
			<?php else: ?>
				<img id="display_venue_images" src="images/beseated/default/banner.png" alt="" />
			<?php endif; ?>
		<?php endfor; ?>
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

jQuery(document).ready(function($) 
{
	
	var imagesArray = '<?php echo json_encode($profileImages); ?>';
	var elementId   = '<?php echo $profileImages[0]->element_id; ?>';
	var elementType   = '<?php echo $elementType; ?>';

	var imgObject   = JSON.parse(imagesArray);

	jQuery('.image-thumb').click(function(event) {
		var index   = jQuery(this).attr('data-index');
		var imageId = imgObject[index]['image_id'];

		jQuery.ajax({
			type: "GET",
			url: "index.php?option=com_beseated&task=profile.changeDefaultImage",
			data: "&image_id="+imageId+"&element_id="+elementId+"&elementType="+elementType,
			success: function(response){
				if(response == "200"){
					jQuery('#currency_change_error').html('Main image changed successfully');
					jQuery('#currency_change_error').removeClass('alert-error');
					jQuery('#currency_change_error').addClass(' alert-success');
				}
				jQuery('#currency_change_error').show();
			}
		});
	});
});


jQuery(".jcarousel").children('ul').children('li').children('.deleteimage').click(function(event) {
	var imageId     = jQuery(this).attr('id');
	var elementId   = '<?php echo $profileImages[0]->element_id; ?>';
	var elementType = '<?php echo $elementType; ?>';

	jQuery.ajax({
		type: "GET",
		url: "index.php?option=com_beseated&task=profile.deleteProfileImage",
		data: "&image_id="+imageId+"&element_id="+elementId+"&elementType="+elementType,
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