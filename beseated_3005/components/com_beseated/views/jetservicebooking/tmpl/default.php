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

$input        = JFactory::getApplication()->input;
$Itemid       = $input->get('Itemid', 0, 'int');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
$document     = JFactory::getDocument();
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/rangeslider/js/rangeslider.js');
$document->addStyleSheet(Juri::root(true) . '/components/com_beseated/assets/rangeslider/css/rangeslider.css');
$document->addStyleSheet(Juri::root(true) . '/templates/bcted/css/custom.css');
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/datepicker/jquery.timepicker.js');
$document->addStyleSheet(Juri::root(true) . '/components/com_beseated/assets/datepicker/jquery.timepicker.css');
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/timer/jquery.countdownTimer.js');
$document->addscript(JURI::root().'components/com_beseated/assets/autocomplete/js/autocomplete.min.js');
$document->addscript(JURI::root().'components/com_beseated/assets/autocomplete/js/jquery-ui8.1.min.js');
$document->addstylesheet(JURI::root().'components/com_beseated/assets/autocomplete/css/autocomplete.min.css');
$document->addstylesheet(JURI::root().'components/com_beseated/assets/autocomplete/css/autocomplete.css');
$mailPath  = JURI::root().'templates/bcted/images/mail_icon.png';
$phonePath = JURI::root().'templates/bcted/images/mail_icon.png';
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
<!-- <script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script> -->
<?php $currentTime = date('H'); ?>
<script type="text/javascript">

jQuery(document).ready(function($) {

	var mailPath  = '<?php echo $mailPath;?>';
	var phonePath = '<?php echo $phonePath;?>';

	jQuery(function() {
		var NoResultsLabel = "No Results";
		var searchData     = JSON.parse('<?php echo json_encode($this->airportList)?>');
		var searchBox      = jQuery(".autocompleted");
		var minLength      = 3;

		jQuery(searchBox).autocomplete({
			minLength: minLength,
			source: function(request, response) {
				var results = $.ui.autocomplete.filter(searchData, request.term);

				if (!results.length) {
					results = [NoResultsLabel];
				}
				response(results.slice(0, 20));
			},
			select: function (event, ui) {
				if (ui.item.label === NoResultsLabel) {
					event.preventDefault();
				}
			},
			focus: function (event, ui) {
				if (ui.item.label === NoResultsLabel) {
					event.preventDefault();
				}
			}
		});
	});
	jQuery('#alert-error').hide();

	jQuery('.email').click(function(event) 
	{
		 var email = jQuery('#email').val();

		 jQuery('#contact').val(email);


		jQuery(this).css('background-color', '#ffe0b2');
		jQuery('.phone').css('background-color', '#fbb829');
	});

	jQuery('.phone').click(function(event) 
	{
		var phone = jQuery('#phone').val();

		 jQuery('#contact').val(phone);

		jQuery(this).css('background-color', '#ffe0b2');
		jQuery('.email').css('background-color', '#fbb829');
	});
});

//var isDefault = 1;
/*jQuery(function() 
{

    jQuery('#timepicker_from').timepicker({
    	timeFormat: 'HH:mm',
    	interval: 15,
    	defaultTime: '<?php echo $currentTime; ?>',
    	scrollbar: true,
    	change: function(time) {
    		if(isDefault == 0)
			{
				var element = jQuery(this), text;
				var timepicker = element.timepicker();
				var selectedTime = timepicker.format(time);
				jQuery("#timepicker_to").timepicker('setTime', selectedTime, null);
			}

			isDefault = 0;
		}
    });

    jQuery('#timepicker_to').timepicker({
    	timeFormat: 'HH:mm',
    	interval: 15,
    	scrollbar: true,
    	defaultTime: '<?php echo $currentTime; ?>'
    });
});*/
</script>

<script type="text/javascript">

    var validDateTime = 1;
    var contact = 0

	function bookReturnFlight() 
	{
		if (jQuery('#flight_return').attr('checked') == 'checked')
		{
			    jQuery('<div class="control-group date" id="flight_return_div"><label class="control-label">Date</label><div class="controls span6"><input type="text" name="return_flight_date" readonly="true" class="span12" required="required" id="datepicker1"/></div></div>').insertAfter("#flight_return + .control-label");

			    var currentDate = new Date();
				jQuery( "#datepicker1" ).datepicker({minDate: 0, maxDate: "+1M +10D",dateFormat:"dd-mm-yy" });
				jQuery("#datepicker1").datepicker("setDate", currentDate);


				jQuery('<div class="control-group time" id="flight_return_time_div"><label class="control-label"><?php echo JText::_("COM_BESEATED_CLUBTABLEBOOKIN_BOOKING_TIME_LABEL"); ?></label><div class="controls span6"><input type="text" onchange="checkForReturnDate()" name="return_flight_time" readonly="true" class="span6" required="required" id="return_booking_time"/><div id="invalid-return-date-time" class="invalid-return-date-time"><?php echo JText::_("COM_BESEATED_CLUBTABLEBOOKIN_BOOKING_INVALID_DATETIME_MESSAGE_LABEL"); ?></div></div></div>').insertAfter("#flight_return_div");

				jQuery('#invalid-return-date-time').hide();

			    jQuery('#return_booking_time').timepicker({
		        	timeFormat: 'HH:mm',
		        	interval: 15,
		        	defaultTime: "'"+ (clientHours + 1 )+"'",
		        	scrollbar: true,
					change: function(time) 
					{
						if(isDefault == 0)
						{
							var element      = jQuery(this), text;
							var timepicker   = element.timepicker();
							/*var selectedTime = timepicker.format(time);
							jQuery("#timepicker_to").timepicker('setTime', selectedTime, null);*/
						}
						isDefault = 0;
						checkForReturnDate();
					}
		        });

		        jQuery('#timepicker_to').timepicker({
		        	timeFormat: 'HH:mm',
		        	defaultTime: "'"+ (clientHours + 1 )+"'",
		        	interval: 15,
		        	scrollbar: true
		        });
			
		}
		else
		{
			jQuery("#flight_return_time_div").remove();
			jQuery("#flight_return_div").remove();
		}
	}

	function validateContact()
	{
		contact = 1;
	}

	function validateForm()
	{
		var from_location = jQuery("#jetserviceform input[name=from_location]").val();
		var to_location   = jQuery("#jetserviceform input[name=to_location]").val();
		var flight_date   = jQuery("#jetserviceform input[name=flight_date]").val();
		var flight_time   = jQuery("#jetserviceform input[name=flight_time]").val();
		var total_guest   = jQuery("#jetserviceform input[name=total_guest]").val();
		
		if (from_location.length == 0)
		{
			jQuery('#alert-error').show();
    		jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please enter from location</h4>');
    		return 0;
		}
		else if(to_location.length == 0)
		{
			jQuery('#alert-error').show();
    		jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please entro to location</h4>');
    		return 0;
		}
		else if(flight_date.length == 0)
		{
			jQuery('#alert-error').show();
    		jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please select valid date and time</h4>');
    		return 0;
		}
		else if(flight_time.length == 0)
		{
			jQuery('#alert-error').show();
    		jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please select valid date and time</h4>');
    		return 0;
		}
		else if(total_guest.length == 0)
		{
			jQuery('#alert-error').show();
    		jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please enter total guest</h4>');
    		return 0;
		}
		else if(contact == 0)
		{
			jQuery('#alert-error').show();
    		jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please select preferred contact</h4>');
    		return 0;
		}
		else if (jQuery('#flight_return').attr('checked') == 'checked')
		{
			var return_flight_date   = jQuery("#jetserviceform input[name=return_flight_date]").val();
		    var return_flight_time   = jQuery("#return_booking_time").val();

			if(flight_date == return_flight_date && flight_time == return_flight_time)
			{
				jQuery('#alert-error').show();
	    		jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Return flight date and time should not be same</h4>');
	    		return 0;
			}
			else
			{
				jQuery('#alert-error').hide();
	            jQuery('#jetserviceform').submit();
			}
		}
		else
		{
			jQuery('#alert-error').hide();
	        jQuery('#jetserviceform').submit();
		}

	}


	function checkForReturnDate()
	{
		var requested_date = jQuery('#datepicker1').val();
		var booking_time   = jQuery('#return_booking_time').val();
		var timepicker_to  = jQuery('#timepicker_to').val();
		var currentTime    = new Date();
		var dateParts      = requested_date.split("-");
		var timeParts      = booking_time.split(":");
		var selectedDate   = new Date(dateParts[2],dateParts[1] - 1, dateParts[0],timeParts[0],timeParts[1],00);

		if(currentTime.getTime() >= selectedDate.getTime())
		{
			validDateTime = 0;
			jQuery('#invalid-return-date-time').show();
			return false;
		}
		else
		{
			validDateTime = 1;
			jQuery('#invalid-return-date-time').hide();
			return true;
		}
	}


</script>

 <script>
jQuery(function() {
	var currentDate = new Date();
	jQuery( "#datepicker" ).datepicker({minDate: 0, maxDate: "+1M +10D",dateFormat:"dd-mm-yy" });
	jQuery("#datepicker").datepicker("setDate", currentDate);
});
</script>

<script type="text/javascript">
	var d        = new Date();
	var day      = d.getDate();
	var month    = d.getMonth()+1;
	var year     = d.getFullYear();
	var dateOnly =  year + '/' + month + '/' + day;

	jQuery(function(){
		jQuery('#future_date').countdowntimer({
			dateAndTime : dateOnly + ' 20:00:00',
			size : "lg",
			regexpMatchFormat: "([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})",
			regexpReplaceWith: "$2h:$3m"
		});
	});
</script>

<div id="alert-error" class="alert alert-error"></div>
<div class="guest-jet-wrp">
	<div class="inner-jet-wrp">
		<form id="jetserviceform" class="form-horizontal" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=userbookings&Itemid='.$Itemid);?>">
			<div class="control-group">
				<div class="controls span6">
					<input type="text" name="from_location" class="autocompleted span12" required="required" placeholder="From" />
				</div>
			</div>
			<div class="control-group">
				<div class="controls span6">
					<input type="text" name="to_location" class="autocompleted span12" required="required" placeholder="To" />
				</div>
			</div>
			<div class="control-group date">
				<label class="control-label">Date</label>
				<div class="controls span6">
					<input type="text" name="flight_date" readonly="true" class="span12" required="required" id="datepicker"/>
				</div>
			</div>

			<div class="control-group time">
				<label class="control-label"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_BOOKING_TIME_LABEL'); ?></label>
				<div class="controls span6">
					<input type="text" onchange="checkForDate()" name="flight_time" readonly="true" class="span6" required="required" id="booking_time"/>
					<div id="invalid-date-time" class="invalid-date-time"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_BOOKING_INVALID_DATETIME_MESSAGE_LABEL'); ?></div>
				</div>
			</div>

			<div class="control-group return-flight">				
				<div class="controls flight">					
					<input id="flight_return" type="checkbox" style="display: block;" onclick="bookReturnFlight();">
					<label class="control-label"><?php echo JText::_('COM_BESEATED_RETURN_FLIGHT_PRIVATE_JET_BOOING'); ?></label>
				</div>
				
			</div>
					
			<div class="control-group guest">
				<div class="controls span6" >
					<input type="text" name="total_guest" onkeypress="return IsNumeric(event);" class="span12" placeholder="Passengers" id="guest" />
					<span id="error" style="color: Red; display: none">* Input digits (0 - 9)</span>
				</div>
			</div>

			<div class="contact">
				<h3>Preferred Contact</h3>
				<div class="control-group email">
					<div class="controls span6">
						<input type="button" name="email"  value="Email"  class="span6 email" id="email" onclick="validateContact();" />
					</div>
				</div>
				<div class="control-group phone">
					<div class="controls span6">
						<input type="button" name="phone" value="Phone" class="span6 phone" id="phone" onclick="validateContact();"/>
					</div>
				</div>
			</div>

			<div class="control-group text-area">
				<div class="controls span6">
					<textarea class="span12" name="extra_information" placeholder="Additional information.." ></textarea>
				</div>
			</div>

			<div class="control-group submit-button">
				<div class="controls span6">
					<button onclick="return validateForm();" type="button" class="btn btn-large span">Request Quote</button>
					<!-- <button type="submit" class="btn btn-large span">Request Quote</button> -->
					<input type="hidden" id="task" name="task" value="jetservicebooking.bookJetService">
					<input type="hidden" id="view" name="view" value="">
					<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
					<input type="hidden" id="contact" name="contact" value="">
				</div>
			</div>
		</form>
	</div>
</div>


<script type="text/javascript">

var specialKeys = new Array();
specialKeys.push(8); //Backspace
function IsNumeric(e) {
    var keyCode = e.which ? e.which : e.keyCode
    var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != -1);
    document.getElementById("error").style.display = ret ? "none" : "block";
    return ret;
}


</script>


<?php $currentTime = date('H'); ?>
<script type="text/javascript">
	var isDefault = 1;
	var clientTime = new Date();
	var clientHours = clientTime.getHours();

    jQuery(function() 
    {

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

</script>


<script type="text/javascript">
jQuery(function() {
	jQuery( "#datepicker" ).datepicker({ minDate: 0, maxDate: "+1M +10D",dateFormat:"dd-mm-yy" });
	var currentDate = new Date();
	jQuery("#datepicker").datepicker("setDate", currentDate);
	jQuery('#invalid-date-time').hide();
});
</script>
<script type="text/javascript">
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
			validDateTime = 0;
			jQuery('#invalid-date-time').show();
			return false;
		}
		else
		{
			validDateTime = 1;
			jQuery('#invalid-date-time').hide();
			return true;
		}
	}

	
</script>

<script type="text/javascript">
	var d = new Date();
	var day = d.getDate();
	var month = d.getMonth()+1;
	var year = d.getFullYear();
	var dateOnly =  year + '/' + month + '/' + day;
	jQuery(function(){
		jQuery('#future_date').countdowntimer({
			dateAndTime : dateOnly + ' 20:00:00',
			size : "lg",
			regexpMatchFormat: "([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})",
			regexpReplaceWith: "$2h:$3m"
		});
		jQuery('#alert-error').hide();
	});
</script>
