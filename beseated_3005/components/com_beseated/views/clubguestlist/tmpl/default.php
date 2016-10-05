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

$input         = JFactory::getApplication()->input;
$Itemid        = $input->get('Itemid', 0, 'int');
$this->user    = JFactory::getUser();
$this->isRoot  = $this->user->authorise('core.admin');
$fromTime      = $this->clubDetail->from_time;
$arrayFromTime = explode(":", $fromTime);
$toTime        = $this->clubDetail->to_time;
$arrayToTime   = explode(":", $toTime);
$document      = JFactory::getDocument();
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
<script type="text/javascript">
jQuery(function() {
	var currentDate = new Date();
	jQuery( "#datepicker" ).datepicker({
		minDate: 0,
		maxDate: "+1M +10D",
		dateFormat:"dd-mm-yy",
		onSelect: function(dateText) {
			var expireDateStr = this.value;
			var expireDateArr = expireDateStr.split("-");
			var expireDate = new Date(expireDateArr[2], expireDateArr[0], expireDateArr[1]);
			var todayDate = new Date();
			if (expireDate > todayDate) {
				 jQuery('#sendGuestListRequest').removeAttr('disabled');
			}
		}
	});
	jQuery("#datepicker").datepicker("setDate", currentDate);
});
</script>
<script type="text/javascript">
	var d            = new Date();
	var day          = d.getDate();
	var month        = d.getMonth()+1;
	var year         = d.getFullYear();
	var dateOnly     =  year + '/' + month + '/' + day;
	var isLoadinCall = 0;

	jQuery(function(){
		jQuery('#guest_list_time_over').hide();
		jQuery('#future_date').countdowntimer({
			dateAndTime : dateOnly + ' 20:00:00',
			size : "lg",
			regexpMatchFormat: "([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})",
			timeUp : timeisUp,
			regexpReplaceWith: "$2h:$3m"
		});

		 function timeisUp() {
        	jQuery('#sendGuestListRequest').attr('disabled','disabled');
        	jQuery('#guest_list_time_over').hide();
        	if(isLoadinCall==1)
        	{
        		isLoadinCall = 0;
        		jQuery('#guest_list_time_over').hide();
        		jQuery('#guest_list_time_left').show();
        	}
        	else
        	{
        		jQuery('#guest_list_time_left').hide();
        		jQuery('#guest_list_time_over').show();
        	}
        }
	});
</script>

<div class="guest-club-wrp">
	<div class="inner-guest-wrp">
		<form class="form-horizontal guest-frm" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&club_id='.$this->clubID.'&Itemid='.$Itemid);?>">
			<div class="control-group">
				<label class="control-label req-title span6"> <?php echo JText::_('COM_BCTED_VIEW_CLUB_GUESTLIST_REQUEST_TITLE'); ?></label>
				<div class="controls span6">
					<div id="guest_list_time_left" class="g-reqtime"><?php echo JText::_('COM_BCTED_VIEW_CLUB_GUESTLIST_REQUEST_TODAYS_TIME_LEFT'); ?>: <span id="future_date"></span></div>
					<div id="guest_list_time_over" class="g-reqtime"><?php echo JText::_('COM_BCTED_VIEW_CLUB_GUESTLIST_REQUEST_TODAYS_TIME_OVER'); ?></div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label span6">Date</label>
				<div class="controls span6">
					<input type="text" name="request_date_time" readonly="true" class="span12" required="required" id="datepicker"/>
				</div>
			</div>
			<div class="control-group guest-range">
				<label class="control-label span6">
					<?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_TABLE_NUMBER_OF_GUESTS_LABLE'); ?>
					<span class="mf-icon-change-text"><?php echo JText::_('COM_BCTED_MALE_FEMALE_ICONE_CHANGE_TEXT'); ?></span>
				</label>
				<div class="controls span6" >
					<div class="guest-range-inner">
						<input type="range" value="0" min="1" max="10" data-rangeslider>
						<output id="slider-value-disp"></output>
						<input type="hidden" id="guest_count" name="total_guest" value="1">
						<input type="hidden" id="male_count" name="male_guest" value="1">
						<input type="hidden" id="female_count" name="female_guest" value="0">
					</div>
					<div id="guest-icon" class="gust-icn">
						<a class="gust-icn-male" > </a>
					</div>
				</div>
			</div>
			<!-- <div class="control-group">
				<label class="control-label span6">Additional Information:</label>
				<div class="controls span6">
					<textarea class="span12" name="additional_information"></textarea>
				</div>
			</div> -->
			<div class="control-group">
				<div class="controls span6"></div>
				<div class="controls span6">
					<button type="submit" id="sendGuestListRequest" class="btn btn-large span">Request Guestlist</button>
					<input type="hidden" id="task" name="task" value="clubguestlist.addGuestListRequest">
					<input type="hidden" id="view" name="view" value="clubguestlist">
				</div>
			</div>
		</form>
		<span class="guestmessage">More Ladies Increase Priority Conformation</span>
	</div>
</div>

<script type="text/javascript">

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
console.log('onSlide');
console.log('position: ' + position, 'value: ' + value);
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
</script>
