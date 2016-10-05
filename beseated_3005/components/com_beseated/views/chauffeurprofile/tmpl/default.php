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
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$input    = JFactory::getApplication()->input;
$Itemid   = $input->get('Itemid', 0, 'int');
$document = JFactory::getDocument();
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/datepicker/jquery.timepicker.js');
$document->addStyleSheet(Juri::root(true) . '/components/com_beseated/assets/datepicker/jquery.timepicker.css');
?>
<script src="https://maps.googleapis.com/maps/api/js?libraries=geometry,places&location=no&key=AIzaSyAKf4wfPXxaajH5eB3mH4Bsc7G-jSAhKKI"></script>
<script>
var map;
var marker;
var myCenter=new google.maps.LatLng(<?php echo $this->profile->latitude; ?>,<?php echo $this->profile->longitude; ?>);
var marker;

function initialize() {
  var mapOptions = {
	center:myCenter,
    zoom: 8
  };
  map = new google.maps.Map(document.getElementById('googleMap'),
    mapOptions);

  // Get GEOLOCATION
  /*if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var pos = new google.maps.LatLng(<?php echo $this->profile->latitude; ?>,<?php echo $this->profile->longitude; ?>);
      map.setCenter(pos);
      marker = new google.maps.Marker({
        position: pos,
        map: map,
        draggable: true
      });
    }, function() {
      handleNoGeolocation(true);
    });
  } else {
    // Browser doesn't support Geolocation
    handleNoGeolocation(false);
  }*/

  /*function handleNoGeolocation(errorFlag) {
    if (errorFlag) {
      var content = 'Error: The Geolocation service failed.';
    } else {
      var content = 'Error: Your browser doesn\'t support geolocation.';
    }

    var options = {
      map: map,
      position: new google.maps.LatLng(60, 105),
      content: content
    };

    map.setCenter(options.position);
    marker = new google.maps.Marker({
      position: options.position,
      map: map,
      draggable: true
    });
  }*/

  // get places auto-complete when user type in venue_address
  var input = /** @type {HTMLInputElement} */
    (
      document.getElementById('venue_address'));
  var autocomplete = new google.maps.places.Autocomplete(input);
  autocomplete.bindTo('bounds', map);

  var infowindow = new google.maps.InfoWindow();
  marker = new google.maps.Marker({
    map: map,
    anchorPoint: new google.maps.Point(0, -29),
    draggable: true
  });

  google.maps.event.addListener(autocomplete, 'place_changed', function() {
    infowindow.close();
    marker.setVisible(false);
    var place = autocomplete.getPlace();
    if (!place.geometry) {
      return;
    }

    city_name    = '';
	country_name = '';
	for (var i = 0; i < place.address_components.length; i++) {
		var addressType = place.address_components[i].types[0];
		if (addressType == 'locality') {
			city_name = place.address_components[i]['long_name'];
		}
		if (addressType == 'country') {
			country_name = place.address_components[i]['long_name'];
		}
	}

    // If the place has a geometry, then present it on a map.
    if (place.geometry.viewport) {
      map.fitBounds(place.geometry.viewport);
    } else {
      map.setCenter(place.geometry.location);
      map.setZoom(17); // Why 17? Because it looks good.
    }

    marker.setPosition(place.geometry.location);
    marker.setVisible(true);
    jQuery('#latitude').val(place.geometry.location.lat());
	jQuery('#longitude').val(place.geometry.location.lng());
	jQuery('#city').val(city_name);
	jQuery('#country').val(country_name);

    var address = '';
    if (place.address_components) {
      address = [
        (place.address_components[0] && place.address_components[0].short_name || ''), (place.address_components[1] && place.address_components[1].short_name || ''), (place.address_components[2] && place.address_components[2].short_name || '')
      ].join(' ');
    }

  });
}

google.maps.event.addDomListener(window, 'load', initialize);
</script>
<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery('#file_upload_error_msg').hide();
	jQuery('#currency_change_error').hide();
});
</script>

<div class="table-wrp profile-wrp">
	<div>
		<div id="file_upload_error_msg" class="alert alert-error">image dimenssions must be greater than 500px width and 350px height</div>
		<div id="currency_change_error" class="alert"></div>
	</div>
	<div style="display:none">
			<div id="googleMap" style="height:250px; margin-bottom:15px;"></div>
	</div>
	<form class="form-horizontal prf-form data-image" enctype="multipart/form-data" method="post" accept="<?php echo JRoute::_('index.php?option=com_beseated&view=profile'); ?>">
		<div class="row-fluid prof-locatnwrp">
			<?php
				$module = JModuleHelper::getModule('mod_profileslider','mod_profileslider');
				echo JModuleHelper::renderModule($module);
			?>
		</div>
		<div class="control-group">
			<div class="controls span12">
				<input type="text" name="chauffeur_name" id="chauffeur_name" value="<?php echo $this->profile->chauffeur_name; ?>" placeholder="Company Name">
			</div>
		</div>
		<div class="control-group">
			<div class="controls span6">
				<input type="text" name="location" id="venue_address" value="<?php echo $this->profile->location; ?>" placeholder="Location">
			</div>
		</div>

		<div class="control-group">
			<div class="controls span4">
				<select class="currency_select_box" name="currency_code" id="currency_code">
					<option value="">Select Currency</option>
					<?php if($this->profile->currency_code == "EUR"): ?>
						<option selected="selected" value="EUR">Euro(€)</option>
					<?php else: ?>
						<option value="EUR">Euro(€)</option>
					<?php endif; ?>

					<?php if($this->profile->currency_code == "GBP"): ?>
						<option selected="selected" value="GBP">British pound(£)</option>
					<?php else: ?>
						<option value="GBP">British pound(£)</option>
					<?php endif; ?>

					<?php if($this->profile->currency_code == "AED"): ?>
						<option selected="selected" value="AED">UAE dirham(AED)</option>
					<?php else: ?>
						<option value="AED">UAE dirham(AED)</option>
					<?php endif; ?>

					<?php if($this->profile->currency_code == "USD"): ?>
						<option selected="selected" value="USD">United States dollar($)</option>
					<?php else: ?>
						<option value="USD">United States dollar($)</option>
					<?php endif; ?>

					<?php if($this->profile->currency_code == "CAD"): ?>
						<option selected="selected" value="CAD">Canadian dollar($)</option>
					<?php else: ?>
						<option value="CAD">Canadian dollar($)</option>
					<?php endif; ?>

					<?php if($this->profile->currency_code == "AUD"): ?>
						<option selected="selected" value="AUD">Australian dollar($)</option>
					<?php else: ?>
						<option value="AUD">Australian dollar($)</option>
					<?php endif; ?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<div class="controls span12">
				<input type="hidden" id="task" name="task" value="chauffeurprofile.save">
				<input type="hidden" id="view" name="view" value="chauffeurprofile">
				<input type="hidden" id="city" name="city" value="<?php echo $this->profile->city; ?>">
				<!-- <input type="hidden" id="country" name="country" value="<?php echo $this->profile->country; ?>"> -->
				<input type="hidden" id="latitude" name="latitude" value="<?php echo $this->profile->latitude; ?>">
				<input type="hidden" id="longitude" name="longitude" value="<?php echo $this->profile->longitude; ?>">
				<input type="hidden" id="chauffeur_id" name="chauffeur_id" value="<?php echo $this->profile->chauffeur_id; ?>">
				<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
				<button type="submit" class="btn btn-large span">Save Profile</button>
			</div>
		</div>
	</form>
</div>
<script type="text/javascript">

jQuery(function()
{
	var _URL = window.URL || window.webkitURL;
	jQuery("#addimage").click(function(){
		if(this.id != 'addimage'){
			jQuery("#venue_image").click();
		}
	});

	// Add events
	jQuery('input[type=file]').on('change', fileUpload);

	function fileUpload(event){
		jQuery("#uploading_msg").html("<p>"+event.target.value+" uploading...</p>");
		jQuery('#spinner').fadeIn('fast');
		files = event.target.files;
		var data  = new FormData();
		var error = 0;
		for (var i = 0; i < files.length; i++) {
			var file = files[i];
			image    = new Image();
			image.onload = function(){
				if(this.width <= 500 || this.height<=350)
				{
					document.getElementById("venue_image").value = "";
					jQuery('#file_upload_error_msg').show();
					//image dimenssions must be greater than 500px width and 350px height
					error=1;
					jQuery('#spinner').stop().fadeOut('fast');
					return false;
				}
				else
				{
					jQuery('#file_upload_error_msg').hide();
					if(!error){
						var xhr = new XMLHttpRequest();
						xhr.open('POST', 'index.php?option=com_beseated&task=chauffeurprofile.uploadImage', true);
						xhr.send(data);
						xhr.onload = function () {
							jQuery('#spinner').stop().fadeOut('fast');
							location.reload();
						};
					}
					else
					{
						alert('Error'  + error);
					}
				}
			};

			var ext = jQuery('#venue_image').val().split('.').pop().toLowerCase();
			if (jQuery.inArray(ext, ['mp4','3gp','mov']) != -1){

				jQuery.each(jQuery('#venue_image')[0].files, function(i, file) {
				    data.append('image', file);
				});

				var xhr = new XMLHttpRequest();
				xhr.open('POST', 'index.php?option=com_beseated&task=chauffeurprofile.uploadImage', true);
				xhr.send(data);
				xhr.onload = function () {
					jQuery('#spinner').stop().fadeOut('fast');
					location.reload();
				};
			};

			image.src = _URL.createObjectURL(file);
			console.log(file.size);
			console.log("File Pritned fewllow");
			console.log(file.type);
			if(!file.type.match('image.*') && !file.type.match('video.*')) {
				jQuery("#drop-box").html("<p> Images only. Select another file</p>");
				error = 1;
			}else{
				data.append('image', file, file.name);
			}
		}
	}

});


jQuery(document).ready(function($) {
	jQuery('#file_upload_error_msg').hide();
	jQuery('#currency_change_error').hide();

	jQuery('#currency_code').on('change', function() {
	var chauffeur_id = jQuery('#chauffeur_id').val();
		jQuery.ajax({
			type: "GET",
			url: "index.php?option=com_beseated&task=chauffeurprofile.change_currency",
			data: "&chauffeur_id="+chauffeur_id+"&currency_code="+this.value,
			success: function(response){
				console.log(response);
				if(response == "200")
				{
					jQuery('#currency_change_error').html('Currency changed successfully');
					jQuery('#currency_change_error').removeClass('alert-error');
					jQuery('#currency_change_error').addClass(' alert-success');
				}

				if(response == "400")
				{
					jQuery('#currency_change_error').html('Invalid currency selected');
					jQuery('#currency_change_error').removeClass('alert-success');
					jQuery('#currency_change_error').addClass(' alert-error');
				}

				if(response == "500")
				{
					jQuery('#currency_change_error').html('Error while changing currency');
					jQuery('#currency_change_error').removeClass('alert-success');
					jQuery('#currency_change_error').addClass(' alert-error');
				}

				if(response == "707")
				{
					jQuery('#currency_change_error').html('Can not changed currency. bookings are available');
					jQuery('#currency_change_error').removeClass('alert-success');
					jQuery('#currency_change_error').addClass(' alert-error');
				}

				jQuery('#currency_change_error').show();
			}
		});
	});
});


/*	jQuery("#display_venue_image").click(function() {
		jQuery("input[id='venue_image']").click();
	});*/

	var _URL = window.URL || window.webkitURL;
	jQuery(document).ready(function(){
		function readURL(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				console.log(reader);
				reader.onload = function (e) {
					jQuery('#display_venue_image').attr('src', e.target.result);
				}
				reader.readAsDataURL(input.files[0]);
			}
		}

		jQuery("#venue_image").change(function(){
			var image, file;
			/*var profileType = '<?php echo $this->profile->licence_type; ?>';*/
			var ext = jQuery('#venue_image').val().split('.').pop().toLowerCase();
			if ((file = this.files[0])) {
			}

			readURL(this);
		});

	});
</script>
