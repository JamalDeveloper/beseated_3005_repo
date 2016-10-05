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

$input         = JFactory::getApplication()->input;
$Itemid        = $input->get('Itemid', 0, 'int');
$this->user    = JFactory::getUser();
$this->isRoot  = $this->user->authorise('core.admin');
$fromTime      = $this->clubDetail->from_time;
$arrayFromTime = explode(":", $fromTime);
$toTime        = $this->clubDetail->to_time;
$arrayToTime   = explode(":", $toTime);
$days          = explode(',', $this->clubDetail->working_days);

foreach ($days as $index => $dayNumber) {
	$days[$index] = ((int)$dayNumber) - 1;
}
?>

<section class="page-section page-venue-guestlist">
	<div class="container">
		<?php foreach (JModuleHelper::getModules('position-8') as $module) { 
	 		echo JModuleHelper::renderModule($module); 
		} ?>
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="bordered-box">
					<form action="<?php echo JRoute::_('index.php?option=com_beseated&club_id='.$this->clubID.'&Itemid='.$Itemid);?>" method="post">
						<input type="hidden" id="all-count" name="total_guest" value="1">

						<div class="field inline-datepicker">
							<input type="hidden" name="request_date_time" data-date-input>
						</div>				
						<div class="row">
							<div class="col-md-6">
								<p class="heading-3">Event participants</p>
								<p class="copy">More Ladies increase priority confirmation</p>								
							</div>
							<div class="col-md-3">
								<div class="field counter">
									<img src="./templates/beseated-theme/images/guest-male-icon.png" alt="">							
									<input type="hidden" id="males-count" name="male_guest" value="1">
								</div>
							</div>
							<div class="col-md-3">
								<div class="field counter">
									<img src="./templates/beseated-theme/images/guest-female-icon.png" alt="">							
									<input type="hidden" id="females-count" name="female_guest">
								</div>
							</div>
						</div>
						<hr>
						<p class="sumamry">
							<span class="all-count">9 people</span> - 
							<span class="males-count">3 MALE</span>
							<span class="females-count">6 FEMALE</span>
						</p>
						<button class="button" type="submit">Beseated</button>
						<input type="hidden" id="task" name="task" value="clubguestlist.addGuestListRequest">
						<input type="hidden" id="view" name="view" value="clubguestlist">
					</form>
				</div>
			</div>
		</div>
	</div>
</section>

<script>

	function updateSummary() {
		var males 	= parseInt($('#males-count').val());
		var females = parseInt($('#females-count').val());
		var all 	  = males + females;

		$('#all-count').val(all);
		$('.all-count').html(all + ' ' + (all == 1 ? 'person' : 'people'));
		$('.females-count').html(females + ' ' + (females == 1 ? 'FEMALE' : 'FEMALES'));
		$('.males-count').html(males + ' ' + (males == 1 ? 'MALE' : 'MALES'));
	}

	$('.inline-datepicker').dateTimePicker({
		disableTimePicker: true,
		availableDays: <?php echo json_encode($days); ?>
	});

	$('.counter').counter({
		min: function(counter, nextValue) {
			var all 		= parseInt($('#all-count').val());
			return all - 1 > 0 && nextValue >= 0;
		},
		max: function() {
			var max 		= 10;
			var males 	= parseInt($('#males-count').val());
			var females = parseInt($('#females-count').val());
			
			return males + females + 1 <= max;
		},		
		onChange: updateSummary
	});

	updateSummary();

</script>

