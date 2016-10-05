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
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/rangeslider/js/rangeslider.js');
$document->addStyleSheet(Juri::root(true) . '/components/com_beseated/assets/rangeslider/css/rangeslider.css');
$maxVal = 10;
?>
<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places&amp;key=AIzaSyAKf4wfPXxaajH5eB3mH4Bsc7G-jSAhKKI" type="text/javascript"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
<div id="alert-error" class="alert alert-error"></div>
<div class="protection-servicebooking-wrp">
	<div class="inner-servicebooking-wrp">
		<div class="control-group service-main">
			<div class="controls">
				<div class="service-title">
					<?php echo ucfirst($serviceDetail->service_name);?>
				</div>
				<div class="service-capacity">
					<?php echo BeseatedHelper::currencyFormat($serviceDetail->currency_code,$serviceDetail->currency_code,$serviceDetail->price_per_hours).'/Hr'; ?>
				</div>
			</div>
		</div>
	</div>
	<form id="form_protectionservicebooking" class="form-horizontal" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=protectionservicebooking&service_id='.$serviceDetail->service_id.'&Itemid='.$Itemid);?>">
		<div class="control-group">
			<div class="controls">
				<div class="control-group time">
					<label class="control-label"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_BOOKING_TIME_LABEL'); ?></label>
					<div class="controls ">
						<input type="text" onchange="checkForDate()" name="booking_time" readonly="true" class="" required="required" id="booking_time"/>
						<div id="invalid-date-time" class="invalid-date-time"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_BOOKING_INVALID_DATETIME_MESSAGE_LABEL'); ?></div>
					</div>
				</div>
				<div class="control-group date">
					<label class="control-label">Date</label>
					<div class="controls">
						<input type="text" name="booking_date" readonly="true" class="span12" required="required" id="datepicker"/>
					</div>
				</div>
				<div class="control-group hours">
					<label class="control-label">Hours</label>
					<div class="controls">
						<input type="text" name="total_hours" class="span12" required="required" id="total_hours" placeholder="Hours"/>
					</div>
				</div>
				<div class="control-group pickup">
					<div class="controls">
						<input type="text" name="meetup_location" class="meetup_location" required="required" id="meetup_location" placeholder="Meetup Location"/>
					</div>
				</div>
				<div class="control-group guest-range">
					<div class="controls" >
						<div id="guest-icon" class="gust-icn">
							<a class="guard-icn"> </a>
						</div>
						<div class="guard-range-inner">
							<input id="range" type="range" value="0" min="1" max="<?php echo $maxVal; ?>" data-rangeslider>
							<output id="slider-value-disp"></output>
							<input type="hidden" id="total_guard" name="total_guard" value="1">
						</div>
					</div>
				</div>
				<button onclick="return checkForLocation();" type="button" class="btn btn-large span"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_TABLE_BOOK_NOW_BUTTON_LABLE'); ?></button>
			</div>
		</div>
		<input type="hidden" id="protection_id" name="protection_id" value="<?php echo $serviceDetail->protection_id;?>">
		<input type="hidden" id="service_id" name="service_id" value="<?php echo $serviceDetail->service_id;?>">
		<input type="hidden" id="price_per_hours" name="price_per_hours" value="<?php echo $serviceDetail->price_per_hours;?>">
		<input type="hidden" id="booking_currency_code" name="booking_currency_code" value="<?php echo $serviceDetail->currency_code;?>">
		<input type="hidden" id="booking_currency_sign" name="booking_currency_sign" value="<?php echo $serviceDetail->currency_sign;?>">
		<input type="hidden" id="task" name="task" value="protectionservicebooking.bookProtectionService">
		<input type="hidden" id="view" name="view" value="protectionservicebooking">
		<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
	</form>
</div>

<?php $currentTime = date('H'); ?>
<script type="text/javascript">

jQuery(document).ready(function() {
	jQuery("#total_hours").keydown(function (e) {
	    // Allow: backspace, delete, tab, escape, enter and .
	    if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
	         // Allow: Ctrl+A
	        (e.keyCode == 65 && e.ctrlKey === true) ||
	         // Allow: home, end, left, right, down, up
	        (e.keyCode >= 35 && e.keyCode <= 40)) {
	             // let it happen, don't do anything
	             return;
	    }
	    // Ensure that it is a number and stop the keypress
	    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
	        e.preventDefault();
	    }
	});
});

	function checkForLocation()
	{
		var meetupLocation  = jQuery('#meetup_location').val();

		if (meetupLocation == " ")
		{
			jQuery('#alert-error').show();
			jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please select Meetup Point</h4>');
			return false;
		}
		else
		{
			jQuery('#alert-error').hide();
		    jQuery('#form_protectionservicebooking').submit();
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
	    var input = document.getElementById('meetup_location');
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


var $ = jQuery.noConflict();
$('#slider-value-disp').hide();
$(function() {
var $document = $(document),
selector = '[data-rangeslider]',
$element = $(selector);
// Example functionality to demonstrate a value feedback
function valueOutput(element) {
var value = element.value,
output = element.parentNode.getElementsByTagName('output')[0];
output.innerHTML = value;

}
for (var i = $element.length - 1; i >= 0; i--) {
valueOutput($element[i]);
};
$document.on('change', 'input[type="range"]', function(e) {
valueOutput(e.target);
});
// Example functionality to demonstrate disabled functionality
$document .on('click', '#js-example-disabled button[data-behaviour="toggle"]', function(e) {
var $inputRange = $('input[type="range"]', e.target.parentNode);
if ($inputRange[0].disabled) {
$inputRange.prop("disabled", false);
}
else {
$inputRange.prop("disabled", true);
}
$inputRange.rangeslider('update');
});
// Example functionality to demonstrate programmatic value changes
$document.on('click', '#js-example-change-value button', function(e) {
var $inputRange = $('input[type="range"]', e.target.parentNode),
value = $('input[type="number"]', e.target.parentNode)[0].value;
$inputRange.val(value).change();
});
// Example functionality to demonstrate destroy functionality
$document
.on('click', '#js-example-destroy button[data-behaviour="destroy"]', function(e) {
$('input[type="range"]', e.target.parentNode).rangeslider('destroy');
})
.on('click', '#js-example-destroy button[data-behaviour="initialize"]', function(e) {
$('input[type="range"]', e.target.parentNode).rangeslider({ polyfill: false });
});
// Example functionality to test initialisation on hidden elements
$document
.on('click', '#js-example-hidden button[data-behaviour="toggle"]', function(e) {
var $container = $(e.target.previousElementSibling);
$container.toggle();
});
// Basic rangeslider initialization
$element.rangeslider({
	// Deactivate the feature detection
	polyfill: false,
	// Callback function
	onInit: function() {},
	// Callback function
	onSlide: function(position, value) {
	//console.log('onSlide');
	//console.log('position: ' + position, 'value: ' + value);
	},
	// Callback function
	onSlideEnd: function(position, value) {
	console.log('onSlideEnd');
	console.log('position: ' + position, 'value: ' + value);

	newHtml = '';
	oldValue = $('#guest_count').val();
	oldMale = $('#total_guard').val();

	if(oldMale == value)
	{
	}
	else if(oldMale < value)
	{
		appendGuest = value - oldMale;
		newHtml ='';
		for(i = 0; i < appendGuest; i++)
		{
			newHtml = newHtml + '<a class="guard-icn" > </a>';
		}
		$('#guest-icon').append(newHtml);
		$('#guest_count').val(value);
		newMale = parseInt(oldMale) + parseInt(appendGuest);
		$('#total_guard').val(newMale);
	}
	else if(oldMale > value)
	{
		retmoveGuest = value - 1;
		$("#guest-icon > a:gt("+retmoveGuest+")").remove();
		$('#guest_count').val(value);
		$('#total_guard').val($('.guard-icn').length);
	}
}
});
});

$(document).on('click', "a.gust-icn-female", function() {
    if(this.hasClass('gust-icn-female'))
    {
    	this.addClass(' guard-icn');
		var male_count   = $('#male_count').val();
		var female_count = $('#female_count').val();
		$('#male_count').val(parseInt(male_count) + 1);
		$('#female_count').val(parseInt(female_count) - 1);
    }
});

</script>



