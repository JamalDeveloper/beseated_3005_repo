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

$input        = JFactory::getApplication()->input;
$Itemid       = $input->get('Itemid', 0, 'int');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
$document     = JFactory::getDocument();
?>

<section class="page-section page-jet-service-booking">
	<div class="container">
		<h2 class="heading-4">Private Jets Request</h2>
		<h3 class="heading-3">Flight Details</h3>
		
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="bordered-box booking">

					<p>Flight Route</p>
				
					<form id="jet-service-form" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=userbookings&Itemid='.$Itemid);?>" novalidate>
						
						<div class="field">
							<input type="text" name="from_location" required="required" placeholder="From" class="form-control airport" />
						</div>
						
						<div class="field">
							<input type="text" name="to_location" required="required" placeholder="To" class="form-control airport" />
						</div>

						<hr>

						<div class="field inline-datepicker">
							<input type="hidden" name="flight_date" data-date-input>
							<input type="hidden" name="flight_time" data-time-input/>
						</div>
						
						<hr>
											
						<div class="passengers">
							<img class="icon" src="templates/beseated-theme/images/male-icon.png" alt="" />
							<div class="field">
								<input type="text" name="total_guest" autocomplete="off" required data-rule-digits="true" data-rule-range="[1,100]" placeholder="Number of passengers" class="form-control" />
							</div>
						</div>
						
						<div class="field preferred-contact">	
							<p>Preferred Contact</p>
							<div class="checkbox-inline">
								<label for="email">Email</label>
								<input type="radio" name="preferred-contact" id="email" value="Email">								
							</div>
							<div class="checkbox-inline">
								<label for="phone">Phone</label>
								<input type="radio" name="preferred-contact" id="phone" checked value="Phone">
							</div>
						</div>
						
						<div class="field">
							<textarea class="form-control additional-info" name="extra_information" placeholder="Additional information"></textarea>
						</div>
						
						<button data-role="submit" type="submit" class="button">Request Quote</button>

						<input type="hidden" id="task" name="task" value="jetservicebooking.bookJetService">
						<input type="hidden" id="view" name="view" value="">
						<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>"> 
					</form>
				</div>
			</div>
		</div>

	</div>
</section>

<script>
	var airports = new Bloodhound({
	  datumTokenizer: Bloodhound.tokenizers.whitespace,
	  queryTokenizer: Bloodhound.tokenizers.whitespace,
	  local: JSON.parse('<?php echo json_encode($this->airportList)?>')
	});

	$('input').iCheck({
		checkboxClass: 'icheckbox_minimal',
		radioClass: 'iradio_minimal'
	});

	$('.inline-datepicker').dateTimePicker();

	$('.airport').typeahead({
			hint: true,
	  	highlight: true,
	  	minLength: 1
		}, {
	  	name: 'airports',
	  	source: airports
		}
	);

	$('#jet-service-form').validate({
		errorPlacement: function(error, element) {
			element.closest('.field').append(error);
		}
	});
</script>