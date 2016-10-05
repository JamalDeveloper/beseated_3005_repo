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
<script src="https://maps.googleapis.com/maps/api/js?libraries=geometry,places&key=AIzaSyAKf4wfPXxaajH5eB3mH4Bsc7G-jSAhKKI"></script>
<script type="text/javascript">

	jQuery(function() {
		jQuery('#from_time').timepicker({
			timeFormat: 'HH:mm',
			interval: 30,
			scrollbar: true
		});

		jQuery('#to_time').timepicker({
			timeFormat: 'HH:mm',
			interval: 30,
			scrollbar: true
		});
	});
</script>
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
/*  if (navigator.geolocation) {
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

 /* function handleNoGeolocation(errorFlag) {
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
	jQuery('#file_upload_error_msg').hide();
	jQuery('currency_change_error').hide();
</script>

<script type="text/javascript">
	/*jQuery(function()
	{
		var _URL = window.URL || window.webkitURL;
		jQuery("#display_venue_image").click(function(){
			console.log('yes');
			if(this.id != 'display_venue_image'){
				jQuery("#venue_image").click();
			}
		});

		// Add events
		jQuery('input[type=file]').on('change', fileUpload);

		function fileUpload(event){
			jQuery("#uploading_msg").html("<p>"+event.target.value+" uploading...</p>");
			jQuery('#spinner').fadeIn('fast');
			files = event.target.files;
			var data = new FormData();
			var error = 0;
			for (var i = 0; i < files.length; i++) {
				var file = files[i];
				image = new Image();
				image.onload = function() {
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
							xhr.open('POST', 'index.php?option=com_beseated&task=profile.uploadImage', true);
							xhr.send(data);
							xhr.onload = function () {
								jQuery('#spinner').stop().fadeOut('fast');
							};
						}
						else
						{
							alert('Error'  + error);
						}
					}
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
	});*/
</script>


<div class="table-wrp profile-wrp">
	<div>
		<div id="file_upload_error_msg" class="alert alert-error">image dimenssions must be greater than 500px width and 350px height</div>
		<div id="currency_change_error" class="alert"></div>
	</div>
	<form class="form-horizontal prf-form data-image" enctype="multipart/form-data" method="post" accept="<?php echo JRoute::_('index.php?option=com_beseated&view=profile'); ?>">
		<div class="row-fluid prof-locatnwrp">
			<?php $module = JModuleHelper::getModule('mod_profileslider','mod_profileslider');
				  echo JModuleHelper::renderModule($module);?>

			 <!-- <div class="span6 prof-img" style="float: left;" id="drop-box">
			 				<?php if($this->profile->is_video > 0): ?>
			 					<video id="display_venue_image"  width="380" height= "460" controls>
			 						<source src="<?php echo JUri::base().$this->profile->venue_video_webm; ?>" type='video/webm;codecs="vp8, vorbis"'/>
			 						<source src="<?php echo JUri::base().$this->profile->venue_video; ?>" type="video/mp4">
			 						Your browser does not support HTML5 video.
			 					</video>
			 				<?php elseif(!empty($this->profile->image)): ?>
			 					<img id="display_venue_image" src="<?php echo JUri::root().'images/beseated/'. $this->profile->image; ?>" alt="" />
			 				<?php else: ?>
			 					<img id="display_venue_image" src="images/beseated/default/banner.png" alt="" />
			 				<?php endif; ?>
			 				<div class="column-small-12 padd0">
			 					<input type="file" name="venue_image" id="venue_image" style="display: none;" />
			 				</div>
			 				<div id="spinner">
			 					<img src="<?php echo JUri::base(); ?>images/beseated/default/ajax-loader.gif" alt="Loading..."/>
			 				</div>
			 			</div> -->
		</div>
		<!-- <div class="span12" style="float: right;">
			<div id="googleMap" style="height:250px;"></div>
		</div> -->
		<div style="display:none">
			<div id="googleMap" style="height:250px; margin-bottom:15px;"></div>
		</div>
		<div class="control-group">
			<div class="controls span12">
				<input type="text" name="venue_name" id="venue_name" value="<?php echo $this->profile->venue_name; ?>" placeholder="Company Name">
			</div>
		</div>
		<div class="control-group">
			<div class="controls span6">
				<input type="text" name="location" id="venue_address" value="<?php echo $this->profile->location; ?>" placeholder="Location">
			</div>
			<!-- <div class="controls span3">
				<?php if(!empty($this->profile->from_time) && !empty($this->profile->to_time)): ?>
					<?php $fromTime = explode(":", $this->profile->from_time); ?>
					<?php $fromTimeDis = $fromTime[0].':'.$fromTime[1]; ?>
					<input type="text" readonly="true" name="from_time" id="from_time" value="<?php echo $fromTimeDis; ?>" placeholder="Opening hour">
				<?php else: ?>
					<input type="text" readonly="true" name="from_time" id="from_time" placeholder="Opening hour">
				<?php endif; ?>
			</div>
			<div class="controls span3">
				<?php if(!empty($this->profile->from_time) && !empty($this->profile->to_time)): ?>
					<?php $toTime = explode(":", $this->profile->to_time); ?>
					<?php $toTimeDis = $toTime[0].':'.$toTime[1]; ?>
					<input type="text" readonly="true" name="to_time" id="to_time" value="<?php echo $toTimeDis; ?>" placeholder="Closing hour">
				<?php else: ?>
					<input type="text" readonly="true" name="to_time" id="to_time" placeholder="Closing hour">
				<?php endif; ?>
			</div> -->
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
			<div class="controls span4">
				<select class="venue_type_select_box" name="venue_type" id="venue_type">
					<option value="">Venue Type</option>
					<?php if($this->profile->venue_type == "Club"): ?>
						<option selected="selected" value="Club">Club</option>
					<?php else: ?>
						<option value="Club">Club</option>
					<?php endif; ?>

					<?php if($this->profile->venue_type == "Lounge"): ?>
						<option selected="selected" value="Lounge">Lounge</option>
					<?php else: ?>
						<option value="Lounge">Lounge</option>
					<?php endif; ?>

					<?php if($this->profile->venue_type == "Restaurant"): ?>
						<option selected="selected" value="Restaurant">Restaurant</option>
					<?php else: ?>
						<option value="Restaurant">Restaurant</option>
					<?php endif; ?>

					<?php if($this->profile->venue_type == "Beach club"): ?>
						<option selected="selected" value="Beach club">Beach club</option>
					<?php else: ?>
						<option value="Beach club">Beach club</option>
					<?php endif; ?>

					<?php if($this->profile->venue_type == "Bar"): ?>
						<option selected="selected" value="Bar">Bar</option>
					<?php else: ?>
						<option value="Bar">Bar</option>
					<?php endif; ?>
				</select>
			</div>
		</div>

		<div class="control-group">
			<div class="controls span4">
				<select class="music_select_box" name="music" id="music">
				<option value="">Music Type</option>

				<?php foreach ($this->music as $key => $music) 
				{ ?>
						<?php if($this->profile->music == $music->music_name): ?>
							<option selected="selected" value="<?php echo $music->music_name; ?>"> <?php echo $music->music_name; ?></option>
						<?php else: ?>
							<option value="<?php echo $music->music_name; ?>"><?php echo $music->music_name; ?></option>
						<?php endif; ?>
				<?php }

				?>

				</select>
			</div>
		</div>

		<div class="control-group">
			<div class="controls span6">
				<textarea rows="8" name="description" id="venue_about"><?php echo $this->profile->description; ?></textarea>
			</div>
			<div class="controls span6">
				<ul>
					<?php if(!empty($this->profile->working_days)): ?>
						<?php $days = explode(",", $this->profile->working_days); ?>
					<?php else: ?>
						<?php $days = array(); ?>
					<?php endif; ?>
					<li>
						<?php if(in_array(1, $days)): ?>
							<input type="checkbox" value="1" checked="checked" id="c1" name="c1" /><label for="c1">Monday</label>
						<?php else: ?>
							<input type="checkbox" value="1" id="c1" name="c1" /><label for="c1">Monday</label>
						<?php endif; ?>
					</li>
					<li>
						<?php if(in_array(2, $days)): ?>
							<input type="checkbox" value="2" checked="checked" id="c2" name="c2" /><label for="c2">Tuesday</label>
						<?php else: ?>
							<input type="checkbox" value="2" id="c2" name="c2" /><label for="c2">Tuesday</label>
						<?php endif; ?>
					</li>
					<li>
						<?php if(in_array(3, $days)): ?>
							<input type="checkbox" value="3" i checked="checked" id="c3" name="c3" /><label for="c3">Wednesday</label>
						<?php else: ?>
							<input type="checkbox" value="3" id="c3" name="c3" /><label for="c3">Wednesday</label>
						<?php endif; ?>
					</li>
					<li>
						<?php if(in_array(4, $days)): ?>
							<input type="checkbox" value="4"  checked="checked" id="c4" name="c4" /><label for="c4">Thursday</label>
						<?php else: ?>
							<input type="checkbox" value="4" id="c4" name="c4" /><label for="c4">Thursday</label>
						<?php endif; ?>
					</li>
					<li>
						<?php if(in_array(5, $days)): ?>
							<input type="checkbox" value="5" checked="checked" id="c5" name="c5" /><label for="c5">Friday</label>
						<?php else: ?>
							<input type="checkbox" value="5" id="c5" name="c5" /><label for="c5">Friday</label>
						<?php endif; ?>
					</li>
					<li>
						<?php if(in_array(6, $days)): ?>
							<input type="checkbox" value="6"  checked="checked"id="c6" name="c6" /><label for="c6">Saturday</label>
						<?php else: ?>
							<input type="checkbox" value="6" id="c6" name="c6" /><label for="c6">Saturday</label>
						<?php endif; ?>
					</li>
					<li>
						<?php if(in_array(7, $days)): ?>
							<input type="checkbox" value="7" checked="checked" id="c7" name="c7" /><label for="c7">Sunday</label>
						<?php else: ?>
							<input type="checkbox" value="7" id="c7" name="c7" /><label for="c7">Sunday</label>
						<?php endif; ?>
					</li>
				</ul>
			</div>
		</div>

		<!-- <div class="control-group icn-wrp">
			<div class="span4">
				<label>Attire</label>
				<input id="smart_casual" type="button" class="smart-attire-btn">
				<input type="hidden" id="is_smart" name="is_smart" value="<?php echo $this->profile->is_smart; ?>">
				<input type="hidden" id="is_casual" name="is_casual" value="<?php echo $this->profile->is_casual; ?>">
			</div>
			<div class="span4">
				<label>Category</label>
				<input id="food_drink" type="button" class="smart-category-btn">
				<input type="hidden" id="is_food" name="is_food" value="<?php echo $this->profile->is_food; ?>">
				<input type="hidden" id="is_drink" name="is_drink" value="<?php echo $this->profile->is_drink; ?>">
			</div>
			<div class="span4">
				<label>Smoking</label>
				<input id="smoking_nosmoking" type="button" class="smoking-btn">
				<input type="hidden" id="is_smoking" name="is_smoking" value="<?php echo $this->profile->is_smoking; ?>">
			</div>
		</div> -->

		<div class="control-group">
			<div class="controls span12">
				<input type="hidden" id="task" name="task" value="profile.save">
				<input type="hidden" id="view" name="view" value="profile">
				<input type="hidden" id="city" name="city" value="<?php echo $this->profile->city; ?>">
				<!-- <input type="hidden" id="country" name="country" value="<?php echo $this->profile->country; ?>"> -->
				<input type="hidden" id="latitude" name="latitude" value="<?php echo $this->profile->latitude; ?>">
				<input type="hidden" id="longitude" name="longitude" value="<?php echo $this->profile->longitude; ?>">
				<input type="hidden" id="venue_id" name="venue_id" value="<?php echo $this->profile->venue_id; ?>">
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
							xhr.open('POST', 'index.php?option=com_beseated&task=profile.uploadImage', true);
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
					xhr.open('POST', 'index.php?option=com_beseated&task=profile.uploadImage', true);
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

	jQuery('#smart_casual').on('click',function(){
		if (jQuery(this).hasClass('smart-attire-btn'))
		{
			jQuery('#is_smart').val('0');
			jQuery('#is_casual').val('1');
			jQuery(this).removeClass('smart-attire-btn');
			jQuery(this).addClass(' casual-attire-btn');
		}
		else
		{
			jQuery('#is_smart').val('1');
			jQuery('#is_casual').val('0');
			jQuery(this).removeClass('casual-attire-btn');
			jQuery(this).addClass(' smart-attire-btn');
		}
	});

	jQuery('#food_drink').on('click',function(){
		if (jQuery(this).hasClass('smart-category-btn'))
		{
			jQuery('#is_food').val('0');
			jQuery('#is_drink').val('1');
			jQuery(this).removeClass('smart-category-btn');
			jQuery(this).addClass(' casual-category-btn');
		}
		else
		{
			jQuery('#is_food').val('1');
			jQuery('#is_drink').val('0');
			jQuery(this).removeClass('casual-category-btn');
			jQuery(this).addClass(' smart-category-btn');
		}
	});

	jQuery('#smoking_nosmoking').on('click',function(){
		if (jQuery(this).hasClass('smoking-btn'))
		{
			jQuery('#is_smoking').val('0');
			jQuery(this).removeClass('smoking-btn');
			jQuery(this).addClass(' no-smoking-btn');
		}
		else
		{
			jQuery('#is_smoking').val('1');
			jQuery(this).removeClass('no-smoking-btn');
			jQuery(this).addClass(' smoking-btn');
		}
	});

	jQuery(document).ready(function() {
		jQuery('#file_upload_error_msg').hide();
		jQuery('#currency_change_error').hide();
		/*var smart_casual      = "<?php echo $this->profile->is_smart; ?>";
		var food_drink        = "<?php echo $this->profile->is_food; ?>";
		var smoking_nosmoking = "<?php echo $this->profile->is_smoking; ?>";*/
		/*if(smart_casual == '1')
		{
			jQuery('#is_smart').val('1');
			jQuery('#is_casual').val('0');
			jQuery('#smart_casual').removeClass('casual-attire-btn');
			jQuery('#smart_casual').addClass(' smart-attire-btn');
		}
		else
		{
			jQuery('#is_smart').val('0');
			jQuery('#is_casual').val('1');
			jQuery('#smart_casual').removeClass('smart-attire-btn');
			jQuery('#smart_casual').addClass(' casual-attire-btn');
		}

		if(food_drink == '1')
		{
			jQuery('#is_food').val('1');
			jQuery('#is_drink').val('0');
			jQuery('#food_drink').removeClass('casual-category-btn');
			jQuery('#food_drink').addClass(' smart-category-btn');
		}
		else
		{
			jQuery('#is_food').val('0');
			jQuery('#is_drink').val('1');
			jQuery('#food_drink').removeClass('smart-category-btn');
			jQuery('#food_drink').addClass(' casual-category-btn');
		}

		if(smoking_nosmoking == '1')
		{
			jQuery('#is_smoking').val('1');
			jQuery('#smoking_nosmoking').removeClass('no-smoking-btn');
			jQuery('#smoking_nosmoking').addClass(' smoking-btn');
		}
		else
		{
			jQuery('#is_smoking').val('0');
			jQuery('#smoking_nosmoking').removeClass('smoking-btn');
			jQuery('#smoking_nosmoking').addClass(' no-smoking-btn');
		}*/

		jQuery('#currency_code').on('change', function() {
			var venue_id = jQuery('#venue_id').val();
			console.log(venue_id);
			jQuery.ajax({
				type: "GET",
				url: "index.php?option=com_beseated&task=profile.change_currency",
				data: "&venue_id="+venue_id+"&currency_code="+this.value,
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

		jQuery('#venue_type').on('change', function() {
			var venue_id = jQuery('#venue_id').val();
			console.log(venue_id);
			jQuery.ajax({
				type: "GET",
				url: "index.php?option=com_beseated&task=profile.changeVanueType",
				data: "&venue_id="+venue_id+"&venue_type="+this.value,
				success: function(response){
					console.log(response);
					if(response == "200")
					{
						jQuery('#currency_change_error').html('Venue type changed successfully');
						jQuery('#currency_change_error').removeClass('alert-error');
						jQuery('#currency_change_error').addClass(' alert-success');
					}

					if(response == "400")
					{
						jQuery('#currency_change_error').html('Invalid Venue type selected');
						jQuery('#currency_change_error').removeClass('alert-success');
						jQuery('#currency_change_error').addClass(' alert-error');
					}

					if(response == "500")
					{
						jQuery('#currency_change_error').html('Error while changing Venue type');
						jQuery('#currency_change_error').removeClass('alert-success');
						jQuery('#currency_change_error').addClass(' alert-error');
					}

					if(response == "707")
					{
						jQuery('#currency_change_error').html('Can not changed Venue type. bookings are available');
						jQuery('#currency_change_error').removeClass('alert-success');
						jQuery('#currency_change_error').addClass(' alert-error');
					}

					jQuery('#currency_change_error').show();
				}
			});
		});

		jQuery('#music').on('change', function() {
			var venue_id = jQuery('#venue_id').val();
			console.log(venue_id);
			jQuery.ajax({
				type: "GET",
				url: "index.php?option=com_beseated&task=profile.changeMusic",
				data: "&venue_id="+venue_id+"&music="+this.value,
				success: function(response){
					console.log(response);
					if(response == "200")
					{
						jQuery('#currency_change_error').html('Music changed successfully');
						jQuery('#currency_change_error').removeClass('alert-error');
						jQuery('#currency_change_error').addClass(' alert-success');
					}

					if(response == "400")
					{
						jQuery('#currency_change_error').html('Invalid Music selected');
						jQuery('#currency_change_error').removeClass('alert-success');
						jQuery('#currency_change_error').addClass(' alert-error');
					}

					if(response == "500")
					{
						jQuery('#currency_change_error').html('Error while changing Music');
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
			/*if(profileType == 'basic')
			{
				if(jQuery.inArray(ext, ['gif','png','jpg','jpeg']) == -1) {
					alert('invalid extension!');
					document.getElementById("venue_image").value = "";
					return false;
				}
			}

			if(profileType != 'basic')
			{
				if(jQuery.inArray(ext, ['gif','png','jpg','jpeg','mp4','3gp','mov']) == -1) {
					alert('invalid extension!');
					document.getElementById("venue_image").value = "";
					return false;
				}
			}*/
			if ((file = this.files[0])) {
			}

			readURL(this);
		});

	});
</script>
