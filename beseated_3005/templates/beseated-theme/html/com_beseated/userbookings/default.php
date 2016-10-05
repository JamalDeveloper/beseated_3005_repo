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
$type         = $input->get('type', '', 'string');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
?>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery("ul.nav-tabs a").click(function (e) {
	  e.preventDefault();
	    jQuery(this).tab('show');
	});

	jQuery('.share-main').hide();
});

function showShare(id){
	jQuery('.share-main-'+id).show();
	jQuery('.user-booking-'+id).css('background-color', '#2C2208');
}
</script>
<?php
$venueBookingHistory       = $this->bookings['history'];
$venueBookingUpcoming      = $this->bookings['upcoming'];
$chauffeurBookingHistory   = $this->chauffeurBookings['history'];
$chauffeurBookingUpcoming  = $this->chauffeurBookings['upcoming'];
$protectionBookingHistory  = $this->protectionBookings['history'];
$protectionBookingUpcoming = $this->protectionBookings['upcoming'];
$yachtBookingHistory       = $this->yachtBookings['history'];
$yachtBookingUpcoming      = $this->yachtBookings['upcoming'];
$eventBookingHistory       = $this->eventBookingDetail['history'];
$eventBookingUpcoming      = $this->eventBookingDetail['upcoming'];
?>

<section class="page-section page-user-bookings">
	<div class="container">

		<?php include_once('templates/beseated-theme/partials/guest-profile-menu.php') ?>

		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="tabs-headers">
				    <h2 class="tab-header heading-3 active" data-target="venues">Venues</h2>
				    <h2 class="tab-header heading-3" data-target="luxury">Luxury</h2>
				    <h2 class="tab-header heading-3" data-target="events">Events</h2>
				</div>
			</div>
		</div>

		<div class="tabs-content">

			<div class="row active" id="venues">
				<div class="col-md-10 col-md-offset-1">

				<?php if(count($venueBookingUpcoming) > 0):
					foreach ($venueBookingUpcoming as $key => $booking):
					$image = $booking->thumb_image ?: $result->image;?>

					<div class="row">
						<div class="col-md-12">
							<div class="bordered-box item">

								<div class="row">
									<div class="col-md-3">
										<div class="image" style="background-image: url(<?php echo $image ? JUri::base().'images/beseated/'.$image : 'images/bcted/default/banner.png' ?>);">				
										</div>
									</div>
									<div class="col-md-3">
										<h3 class="heading-1"><?php echo ucfirst($booking->venue_name);?></h3>
										<span class="name">
											<?php echo ucfirst($booking->table_name) . ', ' . ucfirst($booking->venue_type);?>
										</span>
										<span class="city">
											<img src="/templates/beseated-theme/images/marker-icon.png" /><?php echo ucfirst($booking->city);?>
										</span>	
									</div>
									<div class="col-md-3">
										<p class="details">
											<span>Booking Number: <?php echo ucfirst($booking->venue_table_booking_id) ?></span>
											<span class="code">Door Code: <?php echo ucfirst($booking->passkey) ?></span>
										</p>
									</div>
									<div class="col-md-2 col-md-offset-1">
										<p class="date">
											<span class="day"><?php echo date('d',strtotime($booking->booking_date));?></span>
											<span class="month-year"><?php echo date('M Y',strtotime($booking->booking_date));?></span>
											<span class="day-name"><?php echo date('l',strtotime($booking->booking_date));?></span>
										</p>
									</div>
								</div>

								<div class="row">
									<div class="col-md-12">
										<div class="bordered-box info">
											<?php 
											$status = array(5=>'Booked', 8=>'Cancelled',13=>'Confirmed');
											if($booking->user_status == 13 && $booking->is_bill_posted == 1) {
												echo '<a href="' . $booking->paymentURL . 'class="button">Pay Now</a>';
											}
											else echo '<button disabled class="button">' . $status[$booking->user_status] . '</button>' 
										?>
										</div>
									</div>
								</div>

							</div>
						</div>
					</div>

					<?php endforeach;
					endif; ?>

				</div>
			</div>

			<div class="row" id="luxury">
				<div class="col-md-6 col-md-offset-3">
					<div class="luxury-tabs bordered-box">
					    <h3 class="tab-header active" data-target="yachts"><img src="templates/beseated-theme/images/yacht-icon.png" class="icon" /><img src="templates/beseated-theme/images/yacht-icon-active.png" class="icon-active" /><span>Yachts</span></h2>
					    <h3 class="tab-header" data-target="chauffeur"><img src="templates/beseated-theme/images/chauffeur-icon.png" class="icon" /><img src="templates/beseated-theme/images/chauffeur-icon-active.png" class="icon-active" /><span>Chauffeur</span></h2>
					    <h3 class="tab-header" data-target="protection"><img src="templates/beseated-theme/images/protection-icon.png" class="icon" /><img src="templates/beseated-theme/images/protection-icon-active.png" class="icon-active" /><span>Protection</span></h2>
					</div>
				</div>

				<div class="row">
					<div class="tabs-content">

						<div class="row active" id="yachts">
							<div class="col-md-10 col-md-offset-1">
								<?php if(count($yachtBookingUpcoming) > 0):
					                foreach ($yachtBookingUpcoming as $key => $booking):
					                  $image = $booking->thumb_image ?: $result->image;?>
					              	
					              	<div class="row">
										<div class="col-md-12">
											<div class="bordered-box item">

												<div class="row">
													<div class="col-md-3">
														<div class="image" style="background-image: url(<?php echo $image ? JUri::base().'images/beseated/'.$image : 'images/bcted/default/banner.png' ?>);">				
														</div>
													</div>
													<div class="col-md-3">
														<h3 class="heading-1"><?php echo ucfirst($booking->yacht_name);?></h3>
														<span class="name">
															<?php echo ucfirst($booking->service_name) . ', ' . ucfirst($booking->service_type);?>
														</span>
														<span class="city">
															<img src="/templates/beseated-theme/images/marker-icon.png" /><?php echo ucfirst($booking->location);?>
														</span>
														<span class="hours">
														<img src="/templates/beseated-theme/images/hours-icon.png" /><?php echo ucfirst($booking->total_hours);?>
														</span>	
													</div>
													<div class="col-md-3">
														<p class="details">
															<span>Booking Number: <?php echo ucfirst($booking->yacht_booking_id) ?></span>
															<span class="time"><img src="/templates/beseated-theme/images/clock-small-icon.png" /> <?php echo ucfirst($booking->booking_time) ?></span>
															<span class="price"><img src="/templates/beseated-theme/images/money-small-icon.png" /> <?php echo ucfirst($booking->booking_currency_code) . ' ' . ucfirst($booking->total_price);?></span>
														</p>
													</div>
													<div class="col-md-2 col-md-offset-1">
														<p class="date">
															<span class="day"><?php echo date('d',strtotime($booking->booking_date));?></span>
															<span class="month-year"><?php echo date('M Y',strtotime($booking->booking_date));?></span>
															<span class="day-name"><?php echo date('l',strtotime($booking->booking_date));?></span>
														</p>
													</div>
												</div>

												<div class="row">
													<div class="col-md-12">
														<div class="bordered-box info">
															<?php 
															$status = array(5=>'Booked', 8=>'Cancelled',13=>'Confirmed');
															if($booking->user_status == 13 && $booking->is_bill_posted == 1) {
																echo '<a href="' . $booking->paymentURL . 'class="button">Pay Now</a>';
															}
															else echo '<button disabled class="button">' . $status[$booking->user_status] . '</button>' 
														?>
														</div>
													</div>
												</div>

											</div>
										</div>
									</div>

					              <?php endforeach;
					              endif; ?>
							</div>
						</div>

						<div class="row" id="chauffeur">
							<div class="col-md-10 col-md-offset-1">
								<?php if(count($chauffeurBookingUpcoming) > 0):
					                foreach ($chauffeurBookingUpcoming as $key => $booking):
					                  $image = $booking->thumb_image ?: $result->image;?>
					              	
					              	<div class="row">
										<div class="col-md-12">
											<div class="bordered-box item">

												<div class="row">
													<div class="col-md-3">
														<div class="image" style="background-image: url(<?php echo $image ? JUri::base().'images/beseated/'.$image : 'images/bcted/default/banner.png' ?>);">				
														</div>
													</div>
													<div class="col-md-3">
														<h3 class="heading-1"><?php echo ucfirst($booking->chauffeur_name);?></h3>
														<span class="name">
															<?php echo ucfirst($booking->service_name) . ', ' . ucfirst($booking->service_type);?>
														</span>
														<span class="city">
															<img src="/templates/beseated-theme/images/marker-icon.png" /><?php echo ucfirst($booking->pickup_location);?>
														</span>
														<span class="city">
															<img src="/templates/beseated-theme/images/marker-icon.png" /><?php echo ucfirst($booking->dropoff_location);?>
														</span>	
													</div>
													<div class="col-md-3">
														<p class="details">
															<span>Booking Number: <?php echo ucfirst($booking->chauffeur_booking_id) ?></span>
															<span class="time"><img src="/templates/beseated-theme/images/clock-small-icon.png" /> <?php echo ucfirst($booking->booking_time) ?></span>
															<span class="price"><img src="/templates/beseated-theme/images/money-small-icon.png" /> <?php echo ucfirst($booking->booking_currency_code) . ' ' . ucfirst($booking->total_price);?></span>
														</p>
													</div>
													<div class="col-md-2 col-md-offset-1">
														<p class="date">
															<span class="day"><?php echo date('d',strtotime($booking->booking_date));?></span>
															<span class="month-year"><?php echo date('M Y',strtotime($booking->booking_date));?></span>
															<span class="day-name"><?php echo date('l',strtotime($booking->booking_date));?></span>
														</p>
													</div>
												</div>

												<div class="row">
													<div class="col-md-12">
														<div class="bordered-box info">
															<?php 
															$status = array(5=>'Booked', 8=>'Cancelled',13=>'Confirmed');
															if($booking->user_status == 13 && $booking->is_bill_posted == 1) {
																echo '<a href="' . $booking->paymentURL . 'class="button">Pay Now</a>';
															}
															else echo '<button disabled class="button">' . $status[$booking->user_status] . '</button>' 
														?>
														</div>
													</div>
												</div>

											</div>
										</div>
									</div>

					              <?php endforeach;
					              endif; ?>
							</div>
						</div>

						<div class="row" id="protection">
							<div class="col-md-10 col-md-offset-1">
								<?php if(count($protectionBookingUpcoming) > 0):
					                foreach ($protectionBookingUpcoming as $key => $booking):
					                  $image = $booking->thumb_image ?: $result->image;?>
					              	
					              	<div class="row">
										<div class="col-md-12">
											<div class="bordered-box item">

												<div class="row">
													<div class="col-md-3">
														<div class="image" style="background-image: url(<?php echo $image ? JUri::base().'images/beseated/'.$image : 'images/bcted/default/banner.png' ?>);">				
														</div>
													</div>
													<div class="col-md-3">
														<h3 class="heading-1"><?php echo ucfirst($booking->protection_name);?></h3>
														<span class="name">
															<?php echo ucfirst($booking->service_name); ?>
														</span>
														<span class="city">
															<img src="/templates/beseated-theme/images/marker-icon.png" /><?php echo ucfirst($booking->location);?>
														</span>
														<span class="hours">
															<img src="/templates/beseated-theme/images/hours-icon.png" /><?php echo ucfirst($booking->total_hours);?>
														</span>	
														<span class="bodyguards">
															<img src="/templates/beseated-theme/images/hours-icon.png" /><?php echo ucfirst($booking->total_guard);?>
														</span>
													</div>
													<div class="col-md-3">
														<p class="details">
															<span>Booking Number: <?php echo ucfirst($booking->protection_booking_id) ?></span>
															<span class="time"><img src="/templates/beseated-theme/images/clock-small-icon.png" /> <?php echo ucfirst($booking->booking_time) ?></span>
															<span class="price"><img src="/templates/beseated-theme/images/money-small-icon.png" /> <?php echo ucfirst($booking->booking_currency_code) . ' ' . ucfirst($booking->total_price);?></span>
														</p>
													</div>
													<div class="col-md-2 col-md-offset-1">
														<p class="date">
															<span class="day"><?php echo date('d',strtotime($booking->booking_date));?></span>
															<span class="month-year"><?php echo date('M Y',strtotime($booking->booking_date));?></span>
															<span class="day-name"><?php echo date('l',strtotime($booking->booking_date));?></span>
														</p>
													</div>
												</div>

												<div class="row">
													<div class="col-md-12">
														<div class="bordered-box info">
															<?php 
															$status = array(5=>'Booked', 8=>'Cancelled',13=>'Confirmed');
															if($booking->user_status == 13 && $booking->is_bill_posted == 1) {
																echo '<a href="' . $booking->paymentURL . 'class="button">Pay Now</a>';
															}
															else echo '<button disabled class="button">' . $status[$booking->user_status] . '</button>' 
														?>
														</div>
													</div>
												</div>

											</div>
										</div>
									</div>

					              <?php endforeach;
					              endif; ?>
							</div>
						</div>

					</div>
				</div>
			</div>

			<div class="row" id="events">
				<div class="col-md-10 col-md-offset-1">

				<?php if(count($eventBookingUpcoming) > 0):
	                foreach ($eventBookingUpcoming as $key => $booking):
	                  $image = $booking->event_image ?: $result->image;?>
                
	              <div class="row">
						<div class="col-md-12">
							<div class="bordered-box item">

								<div class="row">
									<div class="col-md-3">
										<div class="image" style="background-image: url(<?php echo $image ? JUri::base().'images/beseated/'.$image : 'images/bcted/default/banner.png' ?>);">				
										</div>
									</div>
									<div class="col-md-3">
										<h3 class="heading-1"><?php echo ucfirst($booking->event_name);?></h3>
										<span class="name">
											
										</span>
										<span class="city">
											<img src="/templates/beseated-theme/images/marker-icon.png" /><?php echo ucfirst($booking->city);?>
										</span>	
									</div>
									<div class="col-md-3">
										<p class="details">
											<span>Booking Number: <?php echo ucfirst($booking->event_id) ?></span>
											<span class="time"><img src="/templates/beseated-theme/images/clock-small-icon.png" /> <?php echo ucfirst($booking->event_time) ?></span>
											<span class="price"><img src="/templates/beseated-theme/images/money-small-icon.png" /> <?php echo ucfirst($booking->event_currency_code) . ' ' . ucfirst($booking->total_price);?></span>
										</p>
									</div>
									<div class="col-md-2 col-md-offset-1">
										<p class="date">
											<span class="day"><?php echo date('d',strtotime($booking->event_date));?></span>
											<span class="month-year"><?php echo date('M Y',strtotime($booking->event_date));?></span>
											<span class="day-name"><?php echo date('l',strtotime($booking->event_date));?></span>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>

	              <?php endforeach;
	              endif; ?>

				</div>
			</div>	

		</div>



	</div>
</section>	



<script type="text/javascript">
	$('.tabs-headers').tabs();
	$('.luxury-tabs').tabs();
</script>