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
$comp         = $input->getString('comp');
$type         = $input->get('type', '', 'string');
$booking_type = $input->get('booking_type', '', 'string');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');

$redirectURL = JUri::base().'index.php?option=com_beseated&view=userbookings&Itemid='.$Itemid;

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
$eventBookingHistory       = $this->eventBookings['history'];
$eventBookingUpcoming      = $this->eventBookings['upcoming'];

$eventBookingHistory  = json_decode(json_encode($eventBookingHistory)); 
$eventBookingUpcoming = json_decode(json_encode($eventBookingUpcoming)); 

//echo "<pre>";print_r($yachtBookingUpcoming);echo "<pre/>";exit();


$eventTab   = '';
$eventClass = '';
$venueTab   = '';
$venueClass = '';
$luxuryTab   = '';
$luxuryClass = '';

if($comp == 'luxury')
{
	$luxuryTab = 'active';
	$luxuryClass = 'active in';
}
elseif ($comp == 'event') 
{
	$eventTab = 'active';
	$eventClass = 'active in';
}
elseif ($comp == 'venue') 
{
	$venueTab = 'active';
	$venueClass = 'active in';
}
else
{
	$venueTab = 'active';
	$venueClass = 'active in';
}

?>
<div class="bct-summary-container">
    <ul class="nav nav-tabs book-tab">
        <li class="<?php echo $venueTab; ?>"><a href="#set1">Venues</a></li>
        <li class="<?php echo $luxuryTab; ?>"><a href="#set2">Luxury</a></li>
        <li class="<?php echo $eventTab; ?>"><a href="#set3">Events</a></li>
    </ul>
   <div class="tab-content">
        <div class="tab-pane fade <?php echo $venueClass; ?>" id="set1">
            <div class="tabbable">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#sub11">Upcoming</a>
                    </li>
                    <li><a href="#sub12">History</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active in" id="sub11">
                  		<div class="summary-list">
							<ul>
								<?php if(count($venueBookingUpcoming) > 0):?>
									<?php foreach ($venueBookingUpcoming as $key => $booking):?>
										<div class="user-booking-main user-booking-<?php echo $booking->venue_table_booking_id; ?>">
											<div class="user-booking-image">
												<img src="<?php echo JUri::base().'images/beseated/'. $booking->thumb_image; ?>">
											</div>
											<div class="user-booking-details" onclick="showShare('<?php echo $booking->venue_table_booking_id; ?>')">
												<p><?php echo ucfirst($booking->venue_name);?></p>
												<p><?php echo ucfirst($booking->table_name).'&nbsp;-&nbsp;'.BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?></p>
												<p><?php echo $booking->total_guest .'&nbsp; Ppl &nbsp;('.$booking->male_guest .'&nbsp;Male(s)&nbsp;-&nbsp;'.$booking->female_guest .'&nbsp;Female(s))';?></p>
												<p><?php echo date('d-M-Y',strtotime($booking->booking_date));?></p>
											</div>
											<div class="booking-status">
												<?php if ($booking->user_status == 5):?>
													<p class="status" onclick="showShare('<?php echo $booking->venue_table_booking_id; ?>')">Booked</p>
													<div class="share-main share-main-<?php echo $booking->venue_table_booking_id; ?>">
														<!-- <div class="share">
															<h4>Share on</h4>
															<ul>
																<li class="facebbok"><a href="#"></a></li>
																<li class="twitter"><a href="#"></a></li>
																<li class="instagram"><a href="#"></a></li>
															</ul>
														</div> -->
													</div>
												<?php elseif ($booking->user_status == 8):?>
													<p class="status" onclick="showShare('<?php echo $booking->venue_table_booking_id; ?>')">Cancelled</p>
													<div class="share-main share-main-<?php echo $booking->venue_table_booking_id; ?>">
														<!-- <div class="share">
															<h4>Share on</h4>
															<ul>
																<li class="facebbok"><a href="#"></a></li>
																<li class="twitter"><a href="#"></a></li>
																<li class="instagram"><a href="#"></a></li>
															</ul>
														</div> -->
													</div>
												<?php elseif ($booking->user_status == 13 && $booking->is_bill_posted == 1): ?>
													<p class="status" onclick="showShare('<?php echo $booking->venue_table_booking_id; ?>')">Bill Posted</p>
													<div class="share-main share-main-<?php echo $booking->venue_table_booking_id; ?>">
														<!-- <div class="share">
															<h4>Share on</h4>
															<ul>
																<li class="facebbok"><a href="#"></a></li>
																<li class="twitter"><a href="#"></a></li>
																<li class="instagram"><a href="#"></a></li>
															</ul>
														</div> -->
														<div class="pay-now">
															<a href="<?php echo $booking->paymentURL;?>"><input type="button" class="btn btn-warning" value="Pay"></button></a>
														</div>
													</div>
												<?php elseif ($booking->user_status == 13 && $booking->is_bill_posted == 0): ?>
													<p class="status" onclick="showShare('<?php echo $booking->venue_table_booking_id; ?>')">Confirmed</p>
													<div class="share-main share-main-<?php echo $booking->venue_table_booking_id; ?>">
														<!-- <div class="share">
															<h4>Share on</h4>
															<ul>
																<li class="facebbok"><a href="#"></a></li>
																<li class="twitter"><a href="#"></a></li>
																<li class="instagram"><a href="#"></a></li>
															</ul>
														</div> -->
													</div>
												<?php endif; ?>
											</div>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
                    </div>
                    <div class="tab-pane fade" id="sub12">
                        <div class="summary-list">
							<ul>
								<?php if(count($venueBookingHistory) > 0):?>
									<?php foreach ($venueBookingHistory as $key => $booking):?>
										<div class="user-booking-main">
											<div class="user-booking-image">
												<img src="<?php echo JUri::base().'images/beseated/'. $booking->thumb_image; ?>">
											</div>
											<div class="user-booking-details">
												<p><?php echo ucfirst($booking->venue_name);?></p>
												<p><?php echo ucfirst($booking->table_name).'&nbsp;-&nbsp;'.BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?></p>
												<p><?php echo $booking->total_guest .'&nbsp; Ppl &nbsp;('.$booking->male_guest .'&nbsp;Male(s)&nbsp;-&nbsp;'.$booking->female_guest .'&nbsp;Female(s))';?></p>
												<p><?php echo date('d-M-Y',strtotime($booking->booking_date));?></p>
											</div>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade <?php echo $luxuryClass; ?>" id="set2">
            <div class="tabbable">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#sub21">Upcoming</a>
                    </li>
                    <li><a href="#sub22">History</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active in" id="sub21">
						<div class="summary-list">
							<ul>
								<?php if(count($chauffeurBookingUpcoming) > 0):?>
									<?php foreach ($chauffeurBookingUpcoming as $key => $booking): ?>
										<div class="user-booking-main" onclick= "expandDetail(<?php echo $booking->chauffeur_booking_id;?>)">
											<div class="user-booking-image">
												<img src="<?php echo JUri::base().'images/beseated/'. $booking->thumb_image; ?>">
											</div>
											<div class="user-booking-details">
												<p><?php echo ucfirst($booking->chauffeur_name);?></p>
												<p><?php echo ucfirst($booking->service_name).'&nbsp;-&nbsp;'.BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?></p>
												<p><?php echo $booking->capacity .' Passenger(s)';?></p>

												<div class="booking-date-time">
													<div class="booking-date">
														<?php echo date('d M Y',strtotime($booking->booking_date));?>
													</div>
													<div class="booking-time">
														<?php echo BeseatedHelper::convertToHM($booking->booking_time); ?>
													</div>
												</div>
											</div>
										</div>
										
										<?php if($booking->bookedType == 'booking') : ?>
											<div class="booking-view-cancel-btn view-btn-<?php echo $booking->chauffeur_booking_id;?>" style="display:none">
													<?php if($booking->user_status !== BeseatedHelper::getStatusID('canceled')) : ?>
													<a href="<?php echo JUri::root().'index.php?option=com_beseated&view=chauffeurinviteduserstatus&booking_id='.$booking->chauffeur_booking_id.'&booking_type=chauffeur&Itemid='.$Itemid; ?>">
											            <input type="button" class="btn btn-large" name="payment" value="View Luxury">
											        </a>
											        <input type="button" class="btn btn-large" name="payment" value="Cancel" onclick="cancelBooking('<?php echo $booking->chauffeur_booking_id;?>','chauffeur')">
											        <?php endif; ?>
											</div>
										<?php endif; ?>
										<?php if($booking->bookedType == 'share') : ?>
											<div class="booking-view-luxury-btn view-btn-<?php echo $booking->chauffeur_booking_id;?>" style="display:none">
												<a href="<?php echo JUri::base().'index.php?option=com_beseated&view=chauffeurrequestpay&viewByShareUser=1&chauffeur_booking_id='.$booking->chauffeur_booking_id.'&Itemid='.$Itemid; ?>">
										            <input type="button" class="btn btn-large" name="payment" value="View Luxury">
										        </a>
											</div>
										<?php endif; ?>
										<?php if($booking->bookedType == 'invitation') : ?>
											<div class="booking-view-luxury-btn view-btn-<?php echo $booking->chauffeur_booking_id;?>" style="display:none">
												<a href="<?php echo JUri::base().'index.php?option=com_beseated&view=chauffeurinviteduserstatus&viewByInvitedUser=1&booking_id='.$booking->chauffeur_booking_id.'&Itemid='.$Itemid; ?>">
										            <input type="button" class="btn btn-large" name="payment" value="View Luxury">
										        </a>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
						<div class="summary-list">
							<ul>
								<?php if(count($protectionBookingUpcoming) > 0):?>
									<?php foreach ($protectionBookingUpcoming as $key => $booking): ?>
										<div class="user-booking-main" onclick= "expandDetail(<?php echo $booking->protection_booking_id;?>)">
											<div class="user-booking-image">
												<img src="<?php echo JUri::base().'images/beseated/'. $booking->thumb_image; ?>">
											</div>
											<div class="user-booking-details">
												<p><?php echo ucfirst($booking->protection_name);?></p>
												<p><?php echo ucfirst($booking->service_name).'&nbsp;-&nbsp;'.BeseatedHelper::currencyFormat($booking->currency_code,$booking->currency_sign,$booking->total_price);?></p>
												<p><?php echo $booking->total_guard .' Bodyguards(s)';?></p>

												<div class="booking-date-time">
													<div class="booking-date">
														<?php echo date('d M Y',strtotime($booking->booking_date));?>
													</div>
													<div class="booking-time">
														<?php echo BeseatedHelper::convertToHM($booking->booking_time); ?>
													</div>
												</div>
											</div>
										</div>
										
										<?php if($booking->bookedType == 'booking') : ?>
											<div class="booking-view-cancel-btn view-btn-<?php echo $booking->protection_booking_id;?>" style="display:none">
													<?php if($booking->user_status !== BeseatedHelper::getStatusID('canceled')) : ?>
													<a href="<?php echo JUri::root().'index.php?option=com_beseated&view=protectioninviteduserstatus&booking_id='.$booking->protection_booking_id.'&booking_type=protection&Itemid='.$Itemid; ?>">
											            <input type="button" class="btn btn-large" name="payment" value="View Luxury">
											        </a>
											        <input type="button" class="btn btn-large" name="payment" value="Cancel" onclick="cancelBooking('<?php echo $booking->protection_booking_id;?>','protection')">
											        <?php endif; ?>
											</div>
										<?php endif; ?>
										<?php if($booking->bookedType == 'share') : ?>
											<div class="booking-view-luxury-btn view-btn-<?php echo $booking->protection_booking_id;?>" style="display:none">
												<a href="<?php echo JUri::base().'index.php?option=com_beseated&view=protectionrequestpay&viewByShareUser=1&protection_booking_id='.$booking->protection_booking_id.'&Itemid='.$Itemid; ?>">
										            <input type="button" class="btn btn-large" name="payment" value="View Luxury">
										        </a>
											</div>
										<?php endif; ?>
										<?php if($booking->bookedType == 'invitation') : ?>
											<div class="booking-view-luxury-btn view-btn-<?php echo $booking->protection_booking_id;?>" style="display:none">
												<a href="<?php echo JUri::base().'index.php?option=com_beseated&view=protectioninviteduserstatus&viewByInvitedUser=1&booking_id='.$booking->protection_booking_id.'&Itemid='.$Itemid; ?>">
										            <input type="button" class="btn btn-large" name="payment" value="View Luxury">
										        </a>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
						<div class="summary-list">
							<ul>
								<?php if(count($yachtBookingUpcoming) > 0):?>
									<?php foreach ($yachtBookingUpcoming as $key => $booking): ?>
										<div class="user-booking-main" onclick= "expandDetail(<?php echo $booking->yacht_booking_id;?>)">
											<div class="user-booking-image">
												<img src="<?php echo JUri::base().'images/beseated/'. $booking->thumb_image; ?>">
											</div>
											<div class="user-booking-details">
												<p><?php echo ucfirst($booking->yacht_name);?></p>
												<p><?php echo ucfirst($booking->service_name).'&nbsp;-&nbsp;'.BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?></p>
												<p><?php echo $booking->capacity .' Passenger(s)';?></p>

												<div class="booking-date-time">
													<div class="booking-date">
														<?php echo date('d M Y',strtotime($booking->booking_date));?>
													</div>
													<div class="booking-time">
														<?php echo BeseatedHelper::convertToHM($booking->booking_time); ?>
													</div>
												</div>
											</div>
										</div>
										
										<?php if($booking->bookedType == 'booking') : ?>
											<div class="booking-view-cancel-btn view-btn-<?php echo $booking->yacht_booking_id;?>" style="display:none">
													<?php if($booking->user_status !== BeseatedHelper::getStatusID('canceled')) : ?>
													<a href="<?php echo JUri::root().'index.php?option=com_beseated&view=yachtinviteduserstatus&booking_id='.$booking->yacht_booking_id.'&booking_type=yacht&Itemid='.$Itemid; ?>">
											            <input type="button" class="btn btn-large" name="payment" value="View Luxury">
											        </a>
											        <input type="button" class="btn btn-large" name="payment" value="Cancel" onclick="cancelBooking('<?php echo $booking->yacht_booking_id;?>','yacht')">
											        <?php endif; ?>
											</div>
										<?php endif; ?>
										<?php if($booking->bookedType == 'share') : ?>
											<div class="booking-view-luxury-btn view-btn-<?php echo $booking->yacht_booking_id;?>" style="display:none">
												<a href="<?php echo JUri::base().'index.php?option=com_beseated&view=yachtrequestpay&viewByShareUser=1&yacht_booking_id='.$booking->yacht_booking_id.'&Itemid='.$Itemid; ?>">
										            <input type="button" class="btn btn-large" name="payment" value="View Luxury">
										        </a>
											</div>
										<?php endif; ?>
										<?php if($booking->bookedType == 'invitation') : ?>
											<div class="booking-view-luxury-btn view-btn-<?php echo $booking->yacht_booking_id;?>" style="display:none">
												<a href="<?php echo JUri::base().'index.php?option=com_beseated&view=yachtinviteduserstatus&viewByInvitedUser=1&booking_id='.$booking->yacht_booking_id.'&Itemid='.$Itemid; ?>">
										            <input type="button" class="btn btn-large" name="payment" value="View Luxury">
										        </a>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
                    </div>
                    <div class="tab-pane fade" id="sub22">
                        <div class="summary-list">
							<ul>
								<?php if(count($chauffeurBookingHistory) > 0):?>
									<?php foreach ($chauffeurBookingHistory as $key => $booking):?>
										<div class="user-booking-main">
											<div class="user-booking-image">
												<img src="<?php echo JUri::base().'images/beseated/'. $booking->thumb_image; ?>">
											</div>
											<div class="user-booking-details">
												<p><?php echo ucfirst($booking->chauffeur_name);?></p>
												<p><?php echo ucfirst($booking->service_name).'&nbsp;-&nbsp;'.BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?></p>
												<p><?php echo $booking->capacity .' Passenger(s)';?></p>
												<div class="booking-date-time">
													<div class="booking-date">
														<?php echo date('d M Y',strtotime($booking->booking_date));?>
													</div>
													<div class="booking-time">
														<?php echo BeseatedHelper::convertToHM($booking->booking_time); ?>
													</div>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
						<div class="summary-list">
							<ul>
								<?php if(count($protectionBookingHistory) > 0):?>
									<?php foreach ($protectionBookingHistory as $key => $booking):?>
										<div class="user-booking-main">
											<div class="user-booking-image">
												<img src="<?php echo JUri::base().'images/beseated/'. $booking->thumb_image; ?>">
											</div>
											<div class="user-booking-details">
												<p><?php echo ucfirst($booking->protection_name);?></p>
												<p><?php echo ucfirst($booking->service_name).'&nbsp;-&nbsp;'.BeseatedHelper::currencyFormat($booking->currency_code,$booking->currency_sign,$booking->total_price);?></p>
												<p><?php //echo $booking->total_guest .'&nbsp; Ppl &nbsp;('.$booking->male_guest .'&nbsp;Male(s)&nbsp;-&nbsp;'.$booking->female_guest .'&nbsp;Female(s))';?></p>
												<p><?php echo date('d-M-Y',strtotime($booking->booking_date));?></p>
											</div>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
						<div class="summary-list">
							<ul>
								<?php if(count($yachtBookingHistory) > 0):?>
									<?php foreach ($yachtBookingHistory as $key => $booking):?>
										<div class="user-booking-main">
											<div class="user-booking-image">
												<img src="<?php echo JUri::base().'images/beseated/'. $booking->thumb_image; ?>">
											</div>
											<div class="user-booking-details">
												<p><?php echo ucfirst($booking->yacht_name);?></p>
												<p><?php echo ucfirst($booking->service_name).'&nbsp;-&nbsp;'.BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?></p>
												<p><?php echo $booking->capacity .' Passenger(s)';?></p>
												<div class="booking-date-time">
													<div class="booking-date">
														<?php echo date('d M Y',strtotime($booking->booking_date));?>
													</div>
													<div class="booking-time">
														<?php echo BeseatedHelper::convertToHM($booking->booking_time); ?>
													</div>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade <?php echo $eventClass; ?>" id="set3">
            <div class="tabbable">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#sub31">Upcoming</a></li>
                    <li><a href="#sub32">History</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active in" id="sub31">
						<div class="summary-list">
							<ul>
								<?php if(count($eventBookingUpcoming) > 0):?>
									<?php foreach ($eventBookingUpcoming as $key => $booking):?>
										<?php if($booking->bookedType == 'invitation'):?>
											<a href="index.php?option=com_beseated&view=eventbookingdetail&event_id=<?php echo $booking->event_id;?>&ticket_booking_id=<?php echo $booking->ticket_booking_id;?>&booking_type=invitation&Itemid=<?php echo $Itemid; ?>">
												<div class="user-booking-main">
													<div class="user-request-image">
														<img src="<?php echo JUri::base().'images/beseated/'. $booking->thumb_avatar; ?>">
													</div>
													<div class="user-booking-details">
													    <p><?php echo ucfirst($booking->full_name);?></p>
														<p><?php echo ucfirst($booking->event_name);?></p>
														<p><?php echo BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?></p>
														<div class="ticket-booking-date-time">
															<p class="ticket-booking-date"><?php echo date('d M Y',strtotime($booking->event_date));?></p>
															<p class="ticket-booking-time"><?php echo $booking->event_time;?></p>
														</div>
													</div>
												</div>
											</a>
										<?php endif; ?>

										<?php if($booking->bookedType == 'booking'):?>
												<?php if($booking->availableTickets == 0): ?>
													<a href="index.php?option=com_beseated&view=eventinviteduserstatus&event_id=<?php echo $booking->event_id;?>&ticket_booking_id=<?php echo $booking->ticket_booking_id;?>&remaining_ticket=<?php echo $booking->availableTickets; ?>&Itemid=<?php echo $Itemid; ?>">
												<?php else: ?>
													<a href="index.php?option=com_beseated&view=eventbookingdetail&event_id=<?php echo $booking->event_id;?>&ticket_booking_id=<?php echo $booking->ticket_booking_id;?>&Itemid=<?php echo $Itemid; ?>">
												<?php endif; ?>
												<div class="user-booking-main">
													<div class="user-booking-image">
														<img src="<?php echo JUri::base().'images/beseated/'. $booking->thumb_avatar; ?>">
													</div>
													<div class="user-booking-details">
														<p><?php echo ucfirst($booking->event_name);?></p>
														<p><?php echo BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?></p>
														<p><?php echo $booking->total_ticket.'&nbsp;Ticket(s)';?></p>
														<div class="ticket-booking-date-time">
															<p class="ticket-booking-date"><?php echo date('d M Y',strtotime($booking->event_date));?></p>
															<p class="ticket-booking-time"><?php echo $booking->event_time;?></p>
														</div>
													</div>
												</div>
											</a>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
					</div>
					<div class="tab-pane fade" id="sub32">
                        <div class="summary-list">
							<ul>
								<?php if(count($eventBookingHistory) > 0):?>
									<?php foreach ($eventBookingHistory as $key => $booking):?>
										<?php if($booking->bookedType == 'invitation'):?>
											<a href="index.php?option=com_beseated&view=eventbookingdetail&event_id=<?php echo $booking->event_id;?>&ticket_booking_id=<?php echo $booking->ticket_booking_id;?>&booking_type=invitation&Itemid=<?php echo $Itemid; ?>">
												<div class="user-booking-main">
													<div class="user-request-image">
														<img src="<?php echo JUri::base().'images/beseated/'. $booking->thumb_avatar; ?>">
													</div>
													<div class="user-booking-details">
													    <p><?php echo ucfirst($booking->full_name);?></p>
														<p><?php echo ucfirst($booking->event_name);?></p>
														<p><?php echo BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?></p>
														<div class="ticket-booking-date-time">
															<p class="ticket-booking-date"><?php echo date('d M Y',strtotime($booking->event_date));?></p>
															<p class="ticket-booking-time"><?php echo $booking->event_time;?></p>
														</div>
													</div>
												</div>
											</a>
										<?php endif; ?>

										<?php if($booking->bookedType == 'booking'):?>
											<a href="index.php?option=com_beseated&view=eventbookingdetail&event_id=<?php echo $booking->event_id;?>&ticket_booking_id=<?php echo $booking->ticket_booking_id;?>&Itemid=<?php echo $Itemid; ?>">
												<div class="user-booking-main">
													<div class="user-booking-image">
														<img src="<?php echo JUri::base().'images/beseated/'. $booking->thumb_avatar; ?>">
													</div>
													<div class="user-booking-details">
														<p><?php echo ucfirst($booking->event_name);?></p>
														<p><?php echo BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?></p>
														<p><?php echo $booking->total_ticket.'&nbsp;Ticket(s)';?></p>
														<div class="ticket-booking-date-time">
															<p class="ticket-booking-date"><?php echo date('d M Y',strtotime($booking->event_date));?></p>
															<p class="ticket-booking-time"><?php echo $booking->event_time;?></p>
														</div>
													</div>
												</div>
											</a>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
    </div>
</div>



<div id="myCancelBookingModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>

      <div class="modal-header">Cancel Booking</div>

      <div class="modal-body">
        <div class="modal-message">Are You Sure You Want To Cancel This Booking? We Will Refund Your Full Amount.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-default cancel-booking" data-dismiss="modal">Confirm</button>
      </div>
    </div>
  </div>
</div>



<script type="text/javascript">

function expandDetail(id)
{
	jQuery('.view-btn-'+id).toggle();
}

function cancelBooking(bookingID,bookingType)
{
    jQuery('#myCancelBookingModal').modal('show');

    if(bookingType == 'protection' || bookingType == 'chauffeur'|| bookingType == 'yacht')
    {
    	var companyType = 'luxury';
    }
    else
    {
    	var companyType = 'venue';
    }
    
    var redirectURL = '<?php echo $redirectURL; ?>';

    jQuery(".cancel-booking").unbind('click').bind('click', function () { }); 

    jQuery('.cancel-booking').click(function (e) 
    {
        jQuery.ajax({
            type: "GET",
            url: "index.php?option=com_beseated&task=guestrequests.cancelBooking",
            data: "&bookingID="+bookingID+"&bookingType="+bookingType,
            success: function(response){
                if(response == "200")
                {
                    window.location = redirectURL+'&comp='+companyType;
                }
                else if(response == "400")
                {
                    jQuery('#alert-error').show();
                    jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Invalid booking detail</h4>');
                }
                else if(response == "500")
                {
                    jQuery('#alert-error').show();
                    jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Error accur while delete invitation request</h4>');
                }
                else if(response == "704")
                {
                    jQuery('#alert-error').show();
                    jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Session expired</h4>');
                }
            }
        });        
    });

}
   

</script>



</script>