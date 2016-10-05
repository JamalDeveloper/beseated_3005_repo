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

<section class="page-section page-yacht-service-booking">
	<div class="container">
		
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				
				<h2 class="heading-4">Yacht Request</h2>
				
				<div class="yacht-details">
					<h3 class="heading-3"><?php echo ucfirst($serviceDetail->service_name);?></h3>
					<p><?php echo ucfirst($serviceDetail->service_type);?></p>
					<p><?php echo "Capacity " . $serviceDetail->capacity." "."people";?></p>
				</div>
				
				<div class="bordered-box booking">

					<p class="location">Dock Location</p>

					<div class="field location-icon">
						<button class="button" type="button" id="show-map">
							<?php echo $serviceDetail->dock;?>
						</button>

						<!-- <input type="text" class="form-control" readonly="readonly" value="" /> -->
					</div>

					<form id="form_yachtservicebooking" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=chauffeurservicebooking&service_id='.$serviceDetail->service_id.'&Itemid='.$Itemid);?>">

						<hr>

						<div class="field inline-datepicker">
							<input type="hidden" name="booking_date" data-date-input>
							<input type="hidden" name="booking_time" data-time-input/>
						</div>

						<div class="hours">
							<div class="field counter-hours">
								<img src="templates/beseated-theme/images/hours-icon-large.png" alt="">							
								<input type="hidden" id="total_hours" name="total_hours" value="<?php echo $serviceDetail->min_hours; ?>">
							</div>
							<p>Hours</p>
						</div>

						<hr>
					
						<button type="submit" class="button">Request Yacht</button>

						<input type="hidden" id="yacht_id" name="yacht_id" value="<?php echo $serviceDetail->yacht_id;?>">
						<input type="hidden" id="service_id" name="service_id" value="<?php echo $serviceDetail->service_id;?>">
						<input type="hidden" id="capacity" name="capacity" value="<?php echo $serviceDetail->capacity;?>">
						<input type="hidden" id="price_per_hours" name="price_per_hours" value="<?php echo $serviceDetail->price_per_hours;?>">
						<input type="hidden" id="booking_currency_code" name="booking_currency_code" value="<?php echo $serviceDetail->currency_code;?>">
						<input type="hidden" id="booking_currency_sign" name="booking_currency_sign" value="<?php echo $serviceDetail->currency_sign;?>">
						<input type="hidden" id="task" name="task" value="yachtservicebooking.bookYachtService">
						<input type="hidden" id="view" name="view" value="yachtservicebooking">
						<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
					</form>
				</div>
			</div>
		</div>
	</div>
</section>

<div class="modal fade" tabindex="-1" role="dialog" id="map-modal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="heading-3">Dock location</h3>
      </div>
      <div class="modal-body">
        <div id="map"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Hide</button>
      </div>
    </div>
  </div>
</div>

<script>
	$('.inline-datepicker').dateTimePicker();

	$('.counter-hours').counter({
		min: <?php echo $serviceDetail->min_hours; ?>
	});

	$('#show-map').on('click', function(event) {
		event.preventDefault();
		$('#map-modal').modal('show');
		$('#map-modal').on('shown.bs.modal', function() {
			var map = new GMaps({
			  el: '#map',
			  lat: <?php echo $serviceDetail->latitude; ?>,
			  lng: <?php echo $serviceDetail->longitude; ?>
			});

			map.addMarker({
				lat: <?php echo $serviceDetail->latitude; ?>,
			  lng: <?php echo $serviceDetail->longitude; ?>
			});
		});
	})
</script>