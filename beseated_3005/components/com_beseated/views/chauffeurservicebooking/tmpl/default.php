<?php
/**
 * @package     Beseated.Site
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

$input         = JFactory::getApplication()->input;
$Itemid        = $input->get('Itemid', 0, 'int');
$this->user    = JFactory::getUser();
$this->isRoot  = $this->user->authorise('core.admin');
$serviceDetail = $this->serviceDetail;
$document      = JFactory::getDocument();
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/rangeslider/js/rangeslider.js');
$document->addStyleSheet(Juri::root(true) . '/components/com_beseated/assets/rangeslider/css/rangeslider.css');
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/datepicker/jquery.timepicker.js');
$document->addStyleSheet(Juri::root(true) . '/components/com_beseated/assets/datepicker/jquery.timepicker.css');
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/timer/jquery.countdownTimer.js');
?>
<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places&amp;key=AIzaSyAKf4wfPXxaajH5eB3mH4Bsc7G-jSAhKKI" type="text/javascript"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
<div id="alert-error" class="alert alert-error"></div>
<div class="servicebooking-wrp">
	<div class="inner-servicebooking-wrp">
		<div class="control-group service-main">
			<div class="controls">
				<div class="service-title">
					<?php echo ucfirst($serviceDetail->service_name);?>
				</div>
				<div class="service-type">
					<?php echo ucfirst($serviceDetail->service_type);?>
				</div>
				<div class="service-capacity">
					<?php echo $serviceDetail->capacity." "."Passengers";?>
				</div>
			</div>
		</div>
	</div>
	<form id="form_chaffeurservicebooking" class="form-horizontal" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=chauffeurservicebooking&service_id='.$serviceDetail->service_id.'&Itemid='.$Itemid);?>">
		<div class="control-group">
			<div class="controls">
				<div class="control-group date">
					<label class="control-label">Date</label>
					<div class="controls">
						<input type="text" name="booking_date" readonly="true" class="span12" required="required" id="datepicker"/>
					</div>
				</div>

				<div class="control-group time">
					<label class="control-label"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_BOOKING_TIME_LABEL'); ?></label>
					<div class="controls ">
						<input type="text" onchange="checkForDate()" name="booking_time" readonly="true" class="" required="required" id="booking_time"/>
						<div id="invalid-date-time" class="invalid-date-time"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_BOOKING_INVALID_DATETIME_MESSAGE_LABEL'); ?></div>
					</div>
				</div>
				<div class="control-group pickup">
					<div class="controls">
						<input type="text" name="pickup_location" class="pickup_location" required="required" id="pickup_location" placeholder="Pickup location"/>
					</div>
				</div>
				<div class="control-group dropoff">
					<div class="controls ">
						<input type="text" name="dropoff_location" class="dropoff_location" required="required" id="dropoff_location" placeholder="Dropoff location"/>
					</div>
				</div>
				<button onclick="return checkForLocation();" type="button" class="btn btn-large span"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_TABLE_BOOK_NOW_BUTTON_LABLE'); ?></button>
			</div>
		</div>
		<input type="hidden" id="chauffeur_id" name="chauffeur_id" value="<?php echo $serviceDetail->chauffeur_id;?>">
		<input type="hidden" id="service_id" name="service_id" value="<?php echo $serviceDetail->service_id;?>">
		<input type="hidden" id="capacity" name="capacity" value="<?php echo $serviceDetail->capacity;?>">
		<input type="hidden" id="booking_currency_code" name="booking_currency_code" value="<?php echo $serviceDetail->currency_code;?>">
		<input type="hidden" id="booking_currency_sign" name="booking_currency_sign" value="<?php echo $serviceDetail->currency_sign;?>">
		<input type="hidden" id="task" name="task" value="chauffeurservicebooking.bookChauffeurService">
		<input type="hidden" id="view" name="view" value="chauffeurservicebooking">
		<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
	</form>
</div>

<?php $currentTime = date('H'); ?>
<script type="text/javascript">

	function checkForLocation()
	{
		var pickupLocation  = jQuery('#pickup_location').val();
		var dropoffLocation = jQuery('#dropoff_location').val();
		console.log(pickupLocation);

		if (pickupLocation == " ")
		{
			jQuery('#alert-error').show();
			jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please select Pickup Point</h4>');
			return false;
		}
		else if(dropoffLocation == " ")
		{
			jQuery('#alert-error').show();
			jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please select Dropoff Point</h4>');
			return false;
		}
		else
		{
			jQuery('#alert-error').hide();
		    jQuery('#form_chaffeurservicebooking').submit();
		}

	}


	jQuery(document).ready(function($) {
		jQuery('#alert-error').css('display', 'none');
	});

	jQuery(function() {
		jQuery( "#datepicker" ).datepicker({ minDate: 0, maxDate: "+1M +10D",dateFormat:"dd-mm-yy" });
		var currentDate = new Date();
		jQuery("#datepicker").datepicker("setDate", currentDate);
		jQuery('#invalid-date-time').hide();
	});

	function checkForDate()
	{
		var requested_date = jQuery('#datepicker').val();
		var booking_time   = jQuery('#booking_time').val();
		var timepicker_to  = jQuery('#timepicker_to').val();
		var currentTime    = new Date();
		var dateParts      = requested_date.split("-");
		var timeParts      = booking_time.split(":");
		var selectedDate   = new Date(dateParts[2],dateParts[1] - 1, dateParts[0],timeParts[0],timeParts[1],00);

		if(currentTime.getTime() >= selectedDate.getTime())
		{
			jQuery('#invalid-date-time').show();
			return false;
		}
		else
		{
			jQuery('#invalid-date-time').hide();
			return true;
		}
	}

	var isDefault = 1;
	var clientTime = new Date();
	var clientHours = clientTime.getHours();
	jQuery(function() {
	    jQuery('#booking_time').timepicker({
	    	timeFormat: 'HH:mm',
	    	interval: 15,
	    	defaultTime: "'"+ (clientHours + 1 )+"'",
	    	scrollbar: true,
			change: function(time) {
				if(isDefault == 0)
				{
					var element      = jQuery(this), text;
					var timepicker   = element.timepicker();
					var selectedTime = timepicker.format(time);
					jQuery("#timepicker_to").timepicker('setTime', selectedTime, null);
				}
				isDefault = 0;
				checkForDate();
			}
	    });

	    jQuery('#timepicker_to').timepicker({
	    	timeFormat: 'HH:mm',
	    	defaultTime: "'"+ (clientHours + 1 )+"'",
	    	interval: 15,
	    	scrollbar: true
	    });
	});

	function initialize() {
	    var input = document.getElementById('pickup_location');
	    var autocomplete = new google.maps.places.Autocomplete(input);
	    google.maps.event.addListener(autocomplete, 'place_changed', function() {
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


	        jQuery('#jform_only_city').val(city_name);
	        jQuery('#jform_only_country').val(country_name);
	        jQuery('#jform_latitude').val(place.geometry.location.lat());
	        jQuery('#jform_longitude').val(place.geometry.location.lng());
	    });
	}
	function initialize2() {
	    var input = document.getElementById('dropoff_location');
	    var autocomplete = new google.maps.places.Autocomplete(input);
	    google.maps.event.addListener(autocomplete, 'place_changed', function() {
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


	        jQuery('#jform_only_city').val(city_name);
	        jQuery('#jform_only_country').val(country_name);
	        jQuery('#jform_latitude').val(place.geometry.location.lat());
	        jQuery('#jform_longitude').val(place.geometry.location.lng());
	    });
	}
	google.maps.event.addDomListener(window, 'load', initialize);
	google.maps.event.addDomListener(window, 'load', initialize2);
</script>



