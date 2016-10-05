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

$input        = JFactory::getApplication()->input;
$Itemid       = $input->get('Itemid', 0, 'int');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
$document     = JFactory::getDocument();
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/rangeslider/js/rangeslider.js');
$document->addStyleSheet(Juri::root(true) . '/components/com_beseated/assets/rangeslider/css/rangeslider.css');
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/datepicker/jquery.timepicker.js');
$document->addStyleSheet(Juri::root(true) . '/components/com_beseated/assets/datepicker/jquery.timepicker.css');
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/timer/jquery.countdownTimer.js');

$days            = explode(',', $this->clubDetail->working_days);
$daysNameArray[] = (in_array(1, $days) ? 'Monday' : "");
$daysNameArray[] = (in_array(2, $days) ? 'Tuesday' : "");
$daysNameArray[] = (in_array(3, $days) ? 'Wednesday' : "");
$daysNameArray[] = (in_array(4, $days) ? 'Thursday' : "");
$daysNameArray[] = (in_array(5, $days) ? 'Friday' : "");
$daysNameArray[] = (in_array(6, $days) ? 'Saturday' : "");
$daysNameArray[] = (in_array(7, $days) ? 'Sunday' : "");
$daysNameArray   = array_values(array_filter($daysNameArray));
?>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
<?php
	/*$fromTime = $this->clubDetail->from_time;
	$arrayFromTime = explode(":", $fromTime);
 	$toTime = $this->clubDetail->to_time;
 	$arrayToTime = explode(":", $toTime);*/
 ?>
<?php $currentTime = date('H'); ?>
<script type="text/javascript">
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
				//checkForDate();
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
        jQuery('#timepicker').timepicker({
        	timeFormat: 'HH:mm',
        	interval: 15,
            minTime: '2p',
            maxTime: '8p',
            scrollbar: true,
        });
    });
</script>
<script type="text/javascript">
jQuery(function() {
	jQuery( "#datepicker" ).datepicker({minDate: 0, maxDate: "+1M +10D",dateFormat:"dd-mm-yy"});
	var currentDate = new Date();
	jQuery("#datepicker").datepicker("setDate", currentDate);
	jQuery('#invalid-date-time').hide();
});
</script>

<script type="text/javascript">
	function checkForVenueTableAvaibility()
	{
		var table_id       = jQuery('#table_id').val();
		var requested_date = jQuery('#datepicker').val();
		var booking_time   = jQuery('#booking_time').val();
		/*var timepicker_to  = jQuery('#timepicker_to').val();*/

		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=clubtablebooking.checkForTableAvaibility',
			type: 'GET',
			data: 'table_id='+table_id+'&requested_date='+requested_date+'&booking_time='+booking_time,
			success: function(response){
	        	if(response == "602")
	        	{
	        		jQuery('#alert-error').show();
	        		jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Table not selected!</h4><br />Please select table');
	        		return false;
	        	}
	        	else if(response != "200")
	        	{
	        		jQuery('#alert-error').show();
	        		jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Table not available!</h4><br />This table is booked already, please try another one ');
	        		return false;
	        	}
	        	else
	        	{
	        		jQuery('#alert-error').hide();
	        		jQuery('#form_venuetablebooking').submit();
	        	}
	        }
		})
		.done(function() {
		})
		.fail(function() {
		})
		.always(function() {
		});

		return false;
	}
</script>



<script type="text/javascript">
	function checkForDate()
	{
		var clubworkingDay = '<?php echo json_encode($daysNameArray);?>';
		var workingDays    = JSON.parse(clubworkingDay);

		var weekday = new Array(7);
		weekday[0]  = "Sunday";
		weekday[1]  = "Monday";
		weekday[2]  = "Tuesday";
		weekday[3]  = "Wednesday";
		weekday[4]  = "Thursday";
		weekday[5]  = "Friday";
		weekday[6]  = "Saturday";

		var date = jQuery('#datepicker').datepicker('getDate');
		var day  = weekday[date.getDay()];

		if (jQuery.inArray(day, workingDays) !== -1){
			var club           = '<?php echo $this->clubDetail->is_day_club;?>';
			var requested_date = jQuery('#datepicker').val();
			var booking_time   = jQuery('#booking_time').val();
			var timepicker_to  = jQuery('#timepicker_to').val();
			var currentTime    = new Date();
			var dateParts      = requested_date.split("-");
			if (club == 1){
				var timeParts      = booking_time.split(":");
				var selectedDate   = new Date(dateParts[2],dateParts[1] - 1, dateParts[0],timeParts[0],timeParts[1],00);
			}else{
				var selectedDate   = new Date(dateParts[2],dateParts[1] - 1, dateParts[0],00);
			}

			if(currentTime.getTime() >= selectedDate.getTime()){
				jQuery('#invalid-date-time').show();
				return false;
			}else{
				jQuery('#invalid-date-time').hide();
				return true;
			}
		}else{
				jQuery('#myModal').modal('show');
				jQuery("#datepicker").datepicker().datepicker("setDate", new Date());
		}
	}
</script>

<script type="text/javascript">
	var d     = new Date();
	var day   = d.getDate();
	var month = d.getMonth()+1;
	var year  = d.getFullYear();
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
<?php
$tblName = "Please Select Table";
if($this->tableDetail)
{
	if($this->tableDetail->premium_table_id)
	{
		$tblName = 'Book '. ucfirst($this->tableDetail->venue_table_name) . ' Table';
	}
	else
	{
		$tblName = 'Book '. ucfirst($this->tableDetail->custom_table_name) . ' Table';
	}

	$maxVal = $this->tableDetail->venue_table_capacity;
}
else
{
	$maxVal = 10;
}
?>
    <div id="alert-error" class="alert alert-error">
    </div>

<div class="guest-club-wrp">
	<div class="inner-guest-wrp">
		<form id="form_venuetablebooking" class="form-horizontal" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=userbookings&club_id='.$this->clubID.'&Itemid='.$Itemid);?>">
			<div class="control-group">
				<label id="tableName" class="control-label req-title span12"><?php echo trim($tblName); ?></label>
			</div>
			<?php if($this->showtableList):?>
				<div class="control-group">
					<label class="control-label span6"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_TABLE_LIST_LABEL'); ?></label>
					<div class="controls club-add-table-premium span6">
						<select class="currency_select_box" name="table_id" id="table_id" onchange="changedSelectedTable(this)">
							<option value=""><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_TABLE_LIST_DEFAULT_OPTION'); ?></option>
							<?php foreach ($this->allTables as $key => $table):?>
								<?php if ($table->premium_table_id):?>
									<option value="<?php echo $table->table_id; ?>"><?php echo $table->table_name; ?></option>
								<?php else: ?>
									<option value="<?php echo $table->table_id; ?>"><?php echo $table->table_name; ?></option>
								<?php endif; ?>
							<?php endforeach ?>
						</select>
					</div>
				</div>
			<?php else: ?>
				<input type="hidden" id="table_id" name="table_id" value="<?php echo $this->tableID; ?>" >
			<?php endif; ?>
			<div class="control-group">
				<label class="control-label span6"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_BOOKING_DATE_LABEL'); ?></label>
				<div class="controls span6">
					<input type="text" onchange="checkForDate()" name="booking_date" readonly="true" class="span12" required="required" id="datepicker"/>
				</div>
			</div>
			<?php if ($this->clubDetail->is_day_club == 1): ?>
				<div class="control-group">
					<label class="control-label span6"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_BOOKING_TIME_LABEL'); ?></label>
					<div class="controls span6">
						<input type="text" onchange="checkForDate()" name="booking_time" readonly="true" class="span6" required="required" id="booking_time"/>
						<!-- <input type="text" onchange="checkForDate()" name="requested_to_time" readonly="true" class="span6" required="required" id="timepicker_to"> -->
						<div id="invalid-date-time" class="invalid-date-time"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_BOOKING_INVALID_DATETIME_MESSAGE_LABEL'); ?></div>
					</div>
				</div>
			<?php endif; ?>
			<div class="control-group guest-range">
				<label class="control-label span6">
					<?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_TABLE_NUMBER_OF_GUESTS_LABLE'); ?>
					<span class="mf-icon-change-text"><?php echo JText::_('COM_BCTED_MALE_FEMALE_ICONE_CHANGE_TEXT'); ?></span>
				</label>
				<div class="controls span6" >
					<div class="guest-range-inner">
						<input id="range" type="range" value="0" min="1" max="<?php echo $maxVal; ?>" data-rangeslider>
						<output id="slider-value-disp"></output>
						<input type="hidden" id="guest_count" name="total_guest" value="1">
						<input type="hidden" id="male_count" name="male_guest" value="1">
						<input type="hidden" id="female_count" name="female_guest" value="0">
					</div>
					<div id="guest-icon" class="gust-icn">
						<a class="gust-icn-male"> </a>
					</div>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label span6">
					<?php echo JText::_('Privarcy'); ?>
				</label>
				<div class="controls span6" >
					<select class="privarcy_select_box" name="privacy" id="privacy">
						<option value="1"><?php echo JText::_('Yes'); ?></option>
						<option value="0"><?php echo JText::_('No'); ?></option>
					</select>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label span6">
					<?php echo JText::_('PassKey'); ?>
				</label>
				<div class="controls span6" >
					<select class="passkey_select_box" name="passkey" id="passkey">
						<option value="1"><?php echo JText::_('Yes'); ?></option>
						<option value="0"><?php echo JText::_('No'); ?></option>
					</select>
				</div>
			</div>

			<!-- <div class="control-group">
				<label class="control-label span6"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_TABLE_ADDITIONAL_INFORMATION_LABLE'); ?></label>
				<div class="controls span6">
					<textarea class="span12" name="additional_information" ></textarea>
				</div>
			</div> -->
			<div class="control-group">
				<div class="controls span6"></div>
				<div class="controls span6">
					<!-- <button onclick="return checkForVenueTableAvaibility();" type="button" class="btn btn-large span"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_TABLE_BOOK_NOW_BUTTON_LABLE'); ?></button> -->
					<button onclick="return checkForVenueTableAvaibility();" type="button" class="btn btn-large span"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_TABLE_BOOK_NOW_BUTTON_LABLE'); ?></button>
					<input type="hidden" id="task" name="task" value="clubtablebooking.bookVenueTable">
					<input type="hidden" id="view" name="view" value="clubguestlist">
					<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
				</div>
			</div>
		</form>
	</div>
</div>

<style>
div.modal.fade{display: none;}
div.modal.fade.in{display: block;}
</style>
<!-- Modal -->
<div id="myModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="modal-message">oops we are closed..</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
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
	oldMale = $('#male_count').val();

	if(oldValue == value)
	{
	}
	else if(oldValue < value)
	{
		appendGuest = value - oldValue;
		newHtml ='';
		for(i = 0; i < appendGuest; i++)
		{
			newHtml = newHtml + '<a class="gust-icn-male" > </a>';
		}
		$('#guest-icon').append(newHtml);
		$('#guest_count').val(value);
		newMale = parseInt(oldMale) + parseInt(appendGuest);
		$('#male_count').val(newMale);
	}
	else if(oldValue > value)
	{
		retmoveGuest = value - 1;
		$("#guest-icon > a:gt("+retmoveGuest+")").remove();
		$('#guest_count').val(value);
		$('#male_count').val($('.gust-icn-male').length);
		$('#female_count').val($('.gust-icn-female').length);
	}
}
});
});

$(document).on('click', "a.gust-icn-male", function() {
    if($(this).hasClass('gust-icn-male'))
    {
    	$(this).removeClass('gust-icn-male');
    	$(this).addClass(' gust-icn-female');
    	var male_count   = $('#male_count').val();
		var female_count = $('#female_count').val();
		$('#male_count').val(parseInt(male_count) - 1);
		$('#female_count').val(parseInt(female_count) + 1);
    }
});

$(document).on('click', "a.gust-icn-female", function() {
    if($(this).hasClass('gust-icn-female'))
    {
    	$(this).removeClass('gust-icn-female');
    	$(this).addClass(' gust-icn-male');
		var male_count   = $('#male_count').val();
		var female_count = $('#female_count').val();
		$('#male_count').val(parseInt(male_count) + 1);
		$('#female_count').val(parseInt(female_count) - 1);
    }
});

	//var $element = jQuery('[data-rangeslider]');
	// Initialize rangeslider.js
	/*$element.rangeslider({
	  polyfill: false
	});*/
	function changedSelectedTable(selectedTable)
	{
		$.ajax({
			url: 'index.php?option=com_bcted&task=clubtablebooking.getTableDetail',
			type: 'GET',
			data: 'table_id='+selectedTable.value,
			success: function(response){
				console.log(response);
	        	if(response.length!=0)
	        	{
	        		var responseArray = response.split("|");
	        		var tblName = "Book " + responseArray[0] + " Table";
	        		$('#tableName').html(tblName);

	        		//var $element = $('[data-rangeslider]');
	        		//$element.rangeslider('destroy');
					// Initialize rangeslider.js
					/*$element.rangeslider({
					  polyfill: false
					});*/
				    /*jQuery("#range").attr("max", 50);
				    jQuery("#range").rangeslider('update');*/
				    /*var attributes = {
					    min: 1,
					    max: 50,
					    step: 1
					};
					// update attributes
					$element.attr(attributes);
					$element.val(1).change();*/

					// pass updated attributes to rangeslider.js
					//$element.rangeslider('update', true);

					/*$element.rangeslider({
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
							alert('hello');
							console.log('position: ' + position, 'value: ' + value);

							newHtml = '';
							oldValue = $('#guest_count').val();
							oldMale = $('#male_count').val();

							if(oldValue == value)
							{
							}
							else if(oldValue < value)
							{
								appendGuest = value - oldValue;
								newHtml ='';
								for(i = 0; i < appendGuest; i++)
								{
									newHtml = newHtml + '<a class="gust-icn-male" > </a>';
								}
								$('#guest-icon').append(newHtml);
								$('#guest_count').val(value);
								newMale = parseInt(oldMale) + parseInt(appendGuest);
								$('#male_count').val(newMale);
							}
							else if(oldValue > value)
							{
								retmoveGuest = value - 1;
								$("#guest-icon > a:gt("+retmoveGuest+")").remove();
								$('#guest_count').val(value);
								$('#male_count').val($('.gust-icn-male').length);
								$('#female_count').val($('.gust-icn-female').length);
							}
						}
					});*/
	        	}
	        	else
	        	{
	        		var tblName = "Please Select Table";
	        		$('#tableName').html(tblName);
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
