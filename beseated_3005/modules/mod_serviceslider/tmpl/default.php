<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$serviceImages = $images;
$imagesCount   = count($serviceImages);
$document      = JFactory::getDocument();
$document->addStylesheet(JUri::root().'modules/mod_serviceslider/media/css/style_slider.css');
$document->addScript(JUri::root().'modules/mod_serviceslider/media/js/html5gallery.js');


?>


<div id="alert-error" class="alert alert-error"></div>
    
<div class="wrapper">
<?php if($imagesCount > 0): ?>
	<div  class="main-slider-area" style="text-align:center;">
		<div style="display:none;" class="html5gallery" data-skin="gallery" data-width="500" data-height="300" >
			<?php for ($i = 0; $i < $imagesCount; $i++): ?>
				<?php if(!empty($serviceImages[$i]->image)):?>
					<a href="<?php echo JUri::base()."images/beseated/".$serviceImages[$i]->image; ?>"><img src="<?php echo JUri::base()."images/beseated/".$serviceImages[$i]->image; ?>"></a>
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
</div>

<script type="text/javascript">
jQuery("#addimage").click(function() {
	var totalImages = '<?php echo $imagesCount; ?>';
	if (totalImages == 100){
		alert('You have uploaded maximum number of images');
	}
	else{
		jQuery("input[id='venue_image']").click();
	}
});

jQuery(document).ready(function() {
        jQuery('#alert-error').hide();
    });


jQuery(document).ready(function($) 
{

	var imagesArray = '<?php echo json_encode($serviceImages); ?>';
	var elementId   = '<?php echo $serviceImages[0]->element_id; ?>';
	var service_id  = '<?php echo $serviceImages[0]->service_id; ?>';
	var view        = '<?php echo $view; ?>';
	var imgObject   = JSON.parse(imagesArray);

	jQuery('.image-thumb').click(function(event) {
		var index   = jQuery(this).attr('data-index');
		var imageId = imgObject[index]['image_id'];

		jQuery.ajax({
			type: "GET",
			url: "index.php?option=com_beseated&task="+view+".changeDefaultImage&detail_page=1",
			data: "image_id="+imageId+"&element_id="+elementId+"&service_id="+service_id,
			success: function(response)
			{
				if(response == "200")
				{
					//jQuery('#file_upload_error_msg').html('Main image changed successfully');
					jQuery('#alert-error').show();
	    	        jQuery('#alert-error').html('Main image changed successfully');
				}

				
			}
		});
	});
});


jQuery(".jcarousel").children('ul').children('li').children('.deleteimage').click(function(event) {
	var imageId     = jQuery(this).attr('id');
	var elementId   = '<?php echo $serviceImages[0]->element_id; ?>';

	jQuery.ajax({
		type: "GET",
		url: "index.php?option=com_beseated&task="+view+".deleteserviceImage&detail_page=1",
		data: "image_id="+imageId+"&element_id="+elementId+"&service_id="+service_id,
		success: function(response){
			if(response == "200"){
				jQuery('#alert-error').show();
	    	    jQuery('#alert-error').html('Image deleted successfully');
			}
			location.reload();
		}
	});
});

</script>