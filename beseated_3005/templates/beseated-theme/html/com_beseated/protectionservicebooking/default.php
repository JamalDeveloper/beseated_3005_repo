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

$input         = JFactory::getApplication()->input;
$Itemid        = $input->get('Itemid', 0, 'int');
$this->user    = JFactory::getUser();
$this->isRoot  = $this->user->authorise('core.admin');
$serviceDetail = $this->serviceDetail;
$document      = JFactory::getDocument();
?>
<section class="page-section page-protection-service-booking">
	<div class="container">
		<h2 class="heading-4">Protection Request</h2>
		<div class="chauffeur-details">
			<h3 class="heading-3"><?php echo ucfirst($serviceDetail->service_name);?></h3>
			<p><?php echo BeseatedHelper::currencyFormat($serviceDetail->currency_code,$serviceDetail->currency_code,$serviceDetail->price_per_hours).'/Hr'; ?></p>
		</div>		
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="bordered-box booking">

					<p>Protection Details</p>

					<form id="protection-service-booking-form" novalidate method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=protectionservicebooking&service_id='.$serviceDetail->service_id.'&Itemid='.$Itemid);?>">

						<div class="field inline-datepicker">
							<input type="hidden" name="booking_date" data-date-input>
							<input type="hidden" name="booking_time" data-time-input/>
						</div>
						
						<hr>

						<div class="row">

							<div class="col-md-6">
								<div class="field counter-hours">
									<img src="templates/beseated-theme/images/hours-icon-large.png" alt="">							
									<input type="hidden" id="total_hours" name="total_hours" value="1">
								</div>
								<p>Hours</p>
							</div>
							<div class="col-md-6">
								<div class="field counter-guards">
									<img src="templates/beseated-theme/images/protection-icon-active.png" alt="">							
									<input type="hidden" id="total_guard" name="total_guard" value="1">
								</div>
								<p>Guards</p>
							</div>

						</div>

						<hr>

						<div class="field meetup-location">
							<input type="text" name="meetup_location" class="form-control" required="required" id="meetup_location" placeholder="Meetup Location" />
						</div>	

						<button type="submit" class="button">Request Protection</button>

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
			</div>
		</div>
	</div>
</section>		

<script>
	$('.inline-datepicker').dateTimePicker();

	$('.counter-hours').counter({
		min: 1
	})

	$('.counter-guards').counter({
		min: 1		
	});

	var autocomplete = new google.maps.places.Autocomplete(document.getElementById('meetup_location'));

	$('#protection-service-booking-form').validate();
</script>