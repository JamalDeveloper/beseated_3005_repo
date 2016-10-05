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

//echo "<pre>";print_r($this->bookings);echo "<pre/>";exit();

?>

<div id="alert-error" class="alert alert-error"></div>
<div class="bct-summary-container">
	<ul class="nav nav-tabs book-tab">
		<li class="active" id="table1"><a href="#table" data-toggle="tab">Requests</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="table">
			<div class="summary-list">
				<ul>
					<?php $hasRequest = 0; ?>
					<?php if($this->bookings): ?>
						<div class="booking-count">Requests <span class="total-booking-count"><?php echo count($this->bookings);?></span></div>
						<?php foreach ($this->bookings as $key => $booking):?>
							<li id="request_booking_<?php echo $booking->yacht_booking_id; ?>">
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
									<div class="request-booking-detail" onclick= "expandDetail(<?php echo $booking->yacht_booking_id;?>)">
										<div class="booking-name">
											<?php echo ucfirst($booking->full_name);?>
										</div>
										<div class="booking-service-name">
											<?php echo ucfirst($booking->service_name);?>
										</div>
										<div class="booking-service-name">
											<?php echo 'Min.'.BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->price_per_hours);?>
										</div>
										<div class="request-booking-location location-<?php echo $booking->yacht_booking_id;?>" style="display:none">
											<div class="request-booking-capacity">
												<?php echo $booking->total_hours.' Hour(s)';?>
											</div>
											<div class="request-booking-capacity">
												<?php echo BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?>
											</div>
										</div>
										<div class="booking-date-time">
											<div class="request-booking-date">
												<?php echo date('d M Y',strtotime($booking->booking_date));?>
											</div>
											<div class="request-booking-time">
												<?php echo BeseatedHelper::convertToHM($booking->booking_time); ?>
											</div>
										</div>
									</div>
									<div class="status">
										<?php if($booking->yacht_status == 3): ?>
											<input type="button" class="set-status-btn" onclick="deleteBookingRequest(<?php echo $booking->yacht_booking_id;?>,'1')" value="Awaiting Confirmation">
										<?php elseif($booking->yacht_status	 == 1): ?>
											<input type="button" class="set-status-btn decline" value="Accept" onclick="changeBookingStatus(<?php echo $booking->yacht_booking_id;?>,'3')">
											<input type="button" class="set-status-btn decline" value="Decline" onclick="changeBookingStatus(<?php echo $booking->yacht_booking_id;?>,'6')">
										<?php elseif($booking->yacht_status	 == 6): ?>
											<input type="button" class="set-status-btn" value="Delete" onclick="deleteBookingRequest(<?php echo $booking->yacht_booking_id;?>,'0')">
										<?php endif; ?>
									</div>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if(count($this->bookings) == 0): ?>
						<div id="system-message">
							<div class="alert alert-block">
								<button type="button" class="close" data-dismiss="alert">&times;</button>
								<h4><?php echo JText::_('COM_BCTED_CLUBREQUESTS_NO_REQUEST_FOUND_TITLE'); ?></h4>
								<div><p> <?php echo JText::_('COM_BCTED_CLUBREQUESTS_NO_REQUEST_FOUND_DESC'); ?></p></div>
							</div>
						</div>
					<?php endif; ?>
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
<div id="declineBookingModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="modal-message">Are You Sure You Want To Decline This Request?</div>
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-default cancel-request" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-default decline-request" data-dismiss="modal">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="deleteBookingModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="modal-message">Are You Sure You Want To Delete This Booking Request? It Will Be Removed From Beseated</div>
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-default cancel-request" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-default delete-request" data-dismiss="modal">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="deleteBookingModalAfterAccept"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="modal-message">Are You Sure You Want To Decline This Request?</div>
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-default cancel-request" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-default delete-request" data-dismiss="modal">Confirm</button>
      </div>
    </div>
  </div>
</div>



<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery('#alert-error').hide();
});
function expandDetail(id){
	jQuery('.location-'+id).toggle();
}

function deleteBookingRequest(yachtBookingID,type)
{
	jQuery(".delete-request").unbind('click').bind('click', function () { }); 

	if(type == 1)
	{
		jQuery('#deleteBookingModalAfterAccept').modal('show');
	}
	else
	{
		jQuery('#deleteBookingModal').modal('show');
	}
	

	jQuery('.delete-request').click(function (e) 
	{
		jQuery.ajax({
			type: "GET",
			url: "index.php?option=com_beseated&task=yachtrequests.deleteBookingRequest",
			data: "yachtBookingID="+yachtBookingID,
			success: function(response){
				if(response == "200")
				{
					location.reload();
				}
				else if(response == "400")
				{
					jQuery('#alert-error').show();
					jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Invalid booking request detail.</h4>');
				}
				else if(response == "704")
				{
					jQuery('#alert-error').show();
					jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Session Expired.</h4>');
				}
				else if(response == "500")
				{
					jQuery('#alert-error').show();
					jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Error accur while delete booking request.</h4>');
				}
			}
		});
	});
}

function changeBookingStatus(yachtBookingID,statusCode)
{
	jQuery(".decline-request").unbind('click').bind('click', function () { }); 

	if(parseInt(statusCode) == 6)
	{
		jQuery('#declineBookingModal').modal('show');

		jQuery('.decline-request').click(function (e) 
		{
			jQuery.ajax({
				type: "GET",
				url: "index.php?option=com_beseated&task=yachtrequests.changeBookingStatus",
				data: "&yachtBookingID="+yachtBookingID+"&statusCode=6",
				success: function(response){
					if(response == "200")
					{
						location.reload();
					}
					else if(response == "400")
					{
						jQuery('#alert-error').show();
						jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Invalid change status detail.</h4>');
					}
					else if(response == "401")
					{
						jQuery('#alert-error').show();
						jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Invalid protection owner.</h4>');
					}
					else if(response == "704")
					{
						jQuery('#alert-error').show();
						jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Session Expired.</h4>');
					}
					else if(response == "500")
					{
						jQuery('#alert-error').show();
						jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Error accur while change status.</h4>');
					}
				}
			});
		});
	}
	else
	{
		jQuery.ajax({
				type: "GET",
				url: "index.php?option=com_beseated&task=yachtrequests.changeBookingStatus",
				data: "&yachtBookingID="+yachtBookingID+"&statusCode=3",
				success: function(response){
					if(response == "200")
					{
						location.reload();
					}
					else if(response == "400")
					{
						jQuery('#alert-error').show();
						jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Invalid change status detail.</h4>');
					}
					else if(response == "401")
					{
						jQuery('#alert-error').show();
						jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Invalid protection owner.</h4>');
					}
					else if(response == "704")
					{
						jQuery('#alert-error').show();
						jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Session Expired.</h4>');
					}
					else if(response == "500")
					{
						jQuery('#alert-error').show();
						jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Error accur while change status.</h4>');
					}
				}
			});
	}

	
}
</script>