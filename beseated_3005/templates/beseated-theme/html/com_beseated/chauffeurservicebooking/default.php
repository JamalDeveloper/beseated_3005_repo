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

<section class="page-section page-chauffeur-service-booking">
	<div class="container">
		<h2 class="heading-4">Chauffeur Request</h2>
		<div class="chauffeur-details">
			<h3 class="heading-3"><?php echo ucfirst($serviceDetail->service_name);?></h3>
			<span><?php echo ucfirst($serviceDetail->service_type);?></span>
			<span><?php echo "Capacity " . $serviceDetail->capacity." "."passengers";?></span>
		</div>		
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="bordered-box booking">

					<p>Ride Details</p>

					<form id="chauffeur-service-booking-form" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=chauffeurservicebooking&service_id='.$serviceDetail->service_id.'&Itemid='.$Itemid);?>" novalidate>

						<div class="field">
							<input type="text" name="pickup_location" required="required" placeholder="Pickup location" class="form-control location" />
						</div>
						
						<div class="field">
							<input type="text" name="dropoff_location" required="required" placeholder="Dropoff location" class="form-control location" />
						</div>

						<hr>

						<div class="field inline-datepicker">
							<input type="hidden" name="booking_date" data-date-input>
							<input type="hidden" name="booking_time" data-time-input/>
						</div>
						
						<hr>

						<button type="submit" class="button">Request Chauffeur</button>

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
			</div>
		</div>
	</div>
</section>		

<script>
	$('.inline-datepicker').dateTimePicker();

	$('.location').each(function(index, element) {
		new google.maps.places.Autocomplete(element)
	})

	$('#chauffeur-service-booking-form').validate();
</script>