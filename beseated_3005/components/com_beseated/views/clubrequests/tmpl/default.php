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
$type         = $input->get('type', '', 'string');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
?>
<script type="text/javascript">
function deleteRequest(requestID)
{
	jQuery.ajax({
		type: "GET",
		url: "index.php?option=com_bcted&task=ajax.calculateExp",
		data: "&discount="+disocunt+"&auction_length="+auctionLength+"&batch_id="+batchID,
		success: function(data){
			jQuery('#min_accepted_bid').html(data);
			jQuery('#minimum_accepted_bid').val(data);
		}
    });
}
</script>

<script type="text/javascript">
jQuery(document).ready(function()
{
	var tabid = window.location.hash;
	var type = "<?php echo $type; ?>";
	if(type.length > 0 && type == 'packages')
	{
		jQuery('#table1').removeClass('active');
		jQuery('#packages1').addClass('active');
		jQuery('#table').removeClass('active');
		jQuery('#packages').addClass('active');
	}
	else{
		if(tabid == '#packages')
		{
			jQuery('#table1').removeClass('active');
			jQuery('#packages1').addClass('active');
		}
		else
		{
			jQuery('#packages1').removeClass('active');
			jQuery('#table1').addClass('active');
		}
	}
});
</script>
<div id="alert-error" class="alert alert-error"></div>
<div class="bct-summary-container">
	<ul class="nav nav-tabs book-tab">
		<li class="active" id="table1"><a href="#table" data-toggle="tab">Requests</a></li>
		<!-- <li id="packages1"><a href="#packages" data-toggle="tab">Packages</a></li> -->
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="table">
			<div class="summary-list">
				<ul>
					<?php if (count($this->clubGuestListRequest) > 0):?>
						<?php foreach ($this->clubGuestListRequest as $key => $guestRequest):?>
							<li id="request_booking_<?php echo $guestRequest->guest_booking_id; ?>">
								<div class="main-request-booking">
									<div class="booking-image">
										<?php if (!empty($guestRequest->thumb_avatar)):?>
											<?php $pos = strpos($guestRequest->thumb_avatar, 'facebook'); ?>
												<?php if ($pos > 0):?>
													<a href="<?php echo 'https://www.facebook.com/'.$guestRequest->fb_id;?>" target="_blank">
													<img src="<?php echo $guestRequest->thumb_avatar;?>" alt="" />
													</a>
												<?php else:?>
													<a data-toggle="modal" data-target="#myFacebookFriendsModal">
													<img src="<?php echo JURI::root().'/images/beseated/'.$guestRequest->thumb_avatar;?>" alt="" />
													</a>
												<?php endif; ?>
										<?php else:?>
											<?php $pos = strpos($guestRequest->thumb_avatar, 'facebook'); ?>
												<?php if ($pos > 0):?>
													<a href="<?php echo 'https://www.facebook.com/'.$guestRequest->fb_id;?>" target="_blank">
													<img src="<?php echo JURI::root().'images/bcted/default/banner.png'?>" alt="" />
													</a>
												<?php else:?>
													<a data-toggle="modal" data-target="#myFacebookFriendsModal">
													<img src="<?php echo JURI::root().'images/bcted/default/banner.png'?>" alt="" />
													</a>
												<?php endif; ?>
										<?php endif;?>
									</div>
									<div class="request-booking-detail">
										<div class="booking-name">
											<?php echo ucfirst($guestRequest->full_name);?>
										</div>
										<div class="booking-service-name">
											<?php echo ucfirst('GuestList Request');?>
										</div>
										<div class="booking-service-name">
											<?php echo $guestRequest->total_guest .'&nbsp;Guest(s)&nbsp;(' . $guestRequest->male_guest . 'M/' . $guestRequest->female_guest .'F)';?>
										</div>
										<div class="booking-date-time">
											<div class="request-booking-date">
												<?php echo date('d-M-Y',strtotime($guestRequest->booking_date));?>
											</div>
										</div>
										<div class="status">
											<input type="button" class="set-status-btn decline" value="Accept" onclick="changeGuestRequestStatus(<?php echo $guestRequest->guest_booking_id;?>,11)">
											<input type="button" class="set-status-btn decline" value="Decline" onclick="changeGuestRequestStatus(<?php echo $guestRequest->guest_booking_id;?>,6)">
										</div>
									</div>
								</div>
							</li>
						<?php endforeach;?>
					<?php endif;?>
					<?php if(count($this->bookings) > 0):?>
						<?php foreach ($this->bookings as $key => $booking):?>
							<li id="request_club_booking_<?php echo $booking->venue_table_booking_id;?>">
								<div class="main-request-booking">
									<div class="booking-image">
										<?php if (!empty($booking->thumb_avatar)):?>
											<?php $pos = strpos($booking->thumb_avatar, 'facebook'); ?>
											<?php if ($pos > 0):?>
												<a href="<?php echo 'https://www.facebook.com/'.$booking->fb_id;?>" target="_blank">
												<img src="<?php echo $booking->thumb_avatar;?>" alt="" />
												</a>
											<?php else:?>
												<a data-toggle="modal" data-target="#myFacebookFriendsModal">
												<img src="<?php echo JURI::root().'/images/beseated/'.$booking->thumb_avatar;?>" alt="" />
												</a>
											<?php endif; ?>
										<?php else:?>
											<?php $pos = strpos($booking->thumb_avatar, 'facebook'); ?>
											<?php if ($pos > 0):?>
												<a href="<?php echo 'https://www.facebook.com/'.$booking->fb_id;?>" target="_blank">
												<img src="<?php echo JURI::root().'images/bcted/default/banner.png'?>" alt="" />
												</a>
											<?php else:?>
												<a data-toggle="modal" data-target="#myFacebookFriendsModal">
												<img src="<?php echo JURI::root().'images/bcted/default/banner.png'?>" alt="" />
												</a>
											<?php endif; ?>
										<?php endif;?>
									</div>
									<div class="request-booking-detail">
										<div class="booking-name">
											<?php echo ucfirst($booking->full_name);?>
										</div>
										<div class="booking-service-name">
											<?php echo ucfirst($booking->table_name);?>
										</div>
										<div class="booking-service-name">
											<?php echo 'Min.'.BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->min_price);?>
										</div>
										<div class="booking-service-name">
											<?php echo $booking->total_guest .'&nbsp;Guest(s)&nbsp;(' . $booking->male_guest . 'M/' . $booking->female_guest .'F)';?>
										</div>
										<div class="booking-date-time">
											<div class="request-booking-date">
												<?php echo date('d-M-Y',strtotime($booking->booking_date));?>
											</div>
										</div>
									</div>
									<div class="status">
										<?php if($booking->venue_status == 3): ?>
											<input type="button" class="set-status-btn" onclick="changeBookingStatus(<?php echo $booking->venue_table_booking_id;?>,6)" value="Awaiting Confirmation">
										<?php elseif($booking->venue_status == 1): ?>
											<input type="button" class="set-status-btn decline" value="Accept" onclick="changeBookingStatus(<?php echo $booking->venue_table_booking_id;?>,3)">
											<input type="button" class="set-status-btn decline" value="Decline" onclick="changeBookingStatus(<?php echo $booking->venue_table_booking_id;?>,6)">
										<?php elseif($booking->venue_status == 6): ?>
											<input type="button" class="set-status-btn" value="Delete" onclick="deleteBooking(<?php echo $booking->venue_table_booking_id;?>)">
										<?php endif; ?>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					<?php endif;?>
				</ul>
			</div>
		</div>
	</div>
</div>
<!-- Modal -->
<div id="myFacebookFriendsModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="modal-message">Oops…the user is not connected on Facebook.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="myDeleteBookingModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="modal-message">Do you want to Decline this booking ?</div>
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-default delete-booking" data-dismiss="modal">Yes</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery('#alert-error').hide();
});

function changeGuestRequestStatus(id, status){
	jQuery.ajax({
		type: "GET",
		url: "index.php?option=com_beseated&task=clubrequests.changeGuestRequestStatus",
		data: "&booking_id="+id+"&booking_status="+status,
		success: function(response){
			if(response == "200")
			{
				jQuery('#request_booking_'+id).hide();
				jQuery('#alert-error').show();
				jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Status Changed.</h4>');
			}
			if(response == "400")
			{
				jQuery('#alert-error').show();
				jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Request Not Deleted.</h4>');
			}
		}
	});
}

function changeBookingStatus(id, status){
	if (status == 6){
		jQuery('#myDeleteBookingModal').modal('show');
		jQuery('.delete-booking').click(function(event) {
			jQuery.ajax({
				type: "GET",
				url: "index.php?option=com_beseated&task=clubrequests.changeBookingStatus",
				data: "&booking_id="+id+"&booking_status="+status,
				success: function(response){
					if(response == "200")
					{
						location.reload();
					}
					if(response == "400")
					{
						jQuery('#alert-error').show();
						jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Request Not Declined.</h4>');
					}
				}
			});
		});
	}else{
		jQuery.ajax({
			type: "GET",
			url: "index.php?option=com_beseated&task=clubrequests.changeBookingStatus",
			data: "&booking_id="+id+"&booking_status="+status,
			success: function(response){
				if(response == "200")
				{
					location.reload();
				}
				if(response == "400")
				{
					jQuery('#alert-error').show();
					jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Request Not Declined.</h4>');
				}
			}
		});
	}
}

function deleteBooking(id)
{
	jQuery.ajax({
		type: "GET",
		url: "index.php?option=com_beseated&task=clubrequests.deleteBooking",
		data: "&booking_id="+id,
		success: function(response){
			if(response == "200")
			{
				jQuery('#request_club_booking_'+id).hide();
			}
			if(response == "400")
			{
				jQuery('#alert-error').show();
				jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Request Not Deleted.</h4>');
			}
		}
	});
}
</script>