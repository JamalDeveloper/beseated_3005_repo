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
//JHtml::_('formbehavior.chosen', 'select');

//echo "<pre>";print_r($this->yachtRsvp);echo "<pre/>";exit();

$app                 = JFactory::getApplication();
$itemId              = $app->input->getInt('Itemid');

$redirectURL = JUri::base().'index.php?option=com_beseated&view=guestrequests&Itemid='.$itemId;

$comp                = $app->input->getString('comp');
$venueRsvpCount      = count($this->venueRsvp);
$protectionRsvpCount = count($this->protectionRsvp);
$chauffeurRsvpCount  = count($this->chauffeurRsvp);
$yachtRsvpCount      = count($this->yachtRsvp);
$eventRsvpCount      = count($this->eventRsvp);


//echo "<pre>";print_r($this->chauffeurRsvp);echo "<pre/>";exit();


$luxuryTab = '';
$eventTab = '';
$venueTab = '';

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

<div id="alert-error" class="alert alert-error"></div>
<div class="bct-summary-container">
    <ul class="nav nav-tabs book-tab">
        <li class="<?php echo $venueTab; ?>"><a href="#set1">Venues</a></li>
        <li class="<?php echo $luxuryTab; ?>"><a href="#set2">Luxury</a></li>
        <li class="<?php echo $eventTab; ?>"><a href="#set3">Events</a></li>
    </ul>
<div class="tab-content">
    <div class="tab-pane fade <?php echo $venueClass; ?>" id="set1">
        <div class="tabbable">
			<?php if($venueRsvpCount > 0):?>
				<?php foreach ($this->venueRsvp as $key => $venueRsvp):?>
					<div class="venue-detail-main">
						<span class="venue-name"><?php echo ucfirst($venueRsvp['venueName']);?>,</span>
						<span class="venue-city"><?php echo $venueRsvp['city'];?></span>
					</div>
					<div class="venue-detail">
						<?php foreach ($venueRsvp['bookings'] as $keyVenue => $booking):?>
							<div class="venue-detail-inner">
								<div class="venue-booking-image">
									<img src="<?php echo $booking['thumbImage']; ?>">
								</div>
								<div class="venue-booking-details">
									<p><?php echo ucfirst($booking['tableName']);?></p>
									<p><?php //echo ucfirst($booking->service_name)?></p>
									<p><?php echo $booking['totalPrice']; ?></p>
									<p><?php echo date('d-M-Y',strtotime($booking['bookingDate']));?></p>
								</div>
								<div class="status">
									<?php if ($booking['statusCode'] == 2):?>
										<input type="button" class="set-status-btn pending" value="Pending" onclick="cancelBooking(<?php echo $booking['venueBookingID'];?>,1)">
									<?php elseif ($booking['statusCode'] == 4):?>
										<a href="index.php?option=com_beseated&view=guestrequestsdetail&table_booking_id=<?php echo $booking['venueBookingID']?>&venue_id=<?php echo $booking['venueID']?>&Itemid=<?php echo $itemId;?>">
											<input type="button" class="set-status-btn available" value="Confirm">
										</a>
									<?php elseif ($booking['statusCode'] == 6): ?>
										<input type="button" class="set-status-btn available" value="Decline" onclick="deleteBooking(<?php echo $booking['venueBookingID'];?>,1)">
									<?php elseif ($booking['statusCode'] == 8): ?>
										<input type="button" class="set-status-btn available" value="Delete" onclick="deleteBooking(<?php echo $booking['venueBookingID'];?>,1)">
									<?php endif;?>
								</div>
							</div>
							<!-- <?php if(count($booking['splits'] > 0)):?>
								<?php foreach ($booking['splits'] as $key => $splits):?>
									<div class="venue-detail-inner">
										<div class="venue-booking-image">
											<img src="<?php echo $splits['thumbAvatar']; ?>">
										</div>
										<div class="venue-booking-details">
											<p><?php echo ucfirst($splits['fullName']);?></p>
											<p><?php echo ucfirst($booking['tableName'])?></p>
											<p><?php //echo $booking['totalPrice']; ?></p>
											<p><?php echo date('d-M-Y',strtotime($booking['bookingDate']));?></p>
										</div>
										<div class="status">

										</div>
									</div>
								<?php endforeach;?>
							<?php endif; ?> -->
						<?php endforeach;?>
					</div>
				<?php endforeach;?>
			<?php endif;?>
        </div>
    </div>
    <div class="tab-pane fade <?php echo $luxuryClass; ?>" id="set2">
        <div class="tabbable">
        	<?php if($protectionRsvpCount > 0):?>
				<?php foreach ($this->protectionRsvp as $key => $protectionRsvp): ?>
					<?php $viewURL = JUri::base().'index.php?option=com_beseated&view=protectionrequestpay&viewByShareUser=1&protection_booking_id='.$protectionRsvp['protection_booking_id'].'&Itemid='.$itemId; ?>
					<div class="venue-detail">
							<div class="venue-detail-inner">
								<div class="venue-booking-image">
									<?php $protectionAvatar = ($protectionRsvp['thumb_image']) ? JUri::base().'images/beseated/'.$protectionRsvp['thumb_image'] : '';?>
									<img src="<?php echo $protectionAvatar; ?>">
								</div>
								<div class="venue-booking-details">
									<p><?php echo ucfirst($protectionRsvp['service_name']);?></p>
									<p><?php echo $protectionRsvp['total_guard']. ' Bodyguard(s)';?></p>
									<p><?php echo $protectionRsvp['booking_currency_sign'] .' '.number_format($protectionRsvp['total_price']);?></p>
									<p><?php echo date('d M Y',strtotime($protectionRsvp['booking_date']));?></p>
								</div>
								<div class="status">
									<?php if ($protectionRsvp['bookedType'] == 'booking'):?>
										<?php if ($protectionRsvp['statusCode'] == 2):?>
											<input type="button" class="set-status-btn pending" value="Pending" onclick="cancelRequest('protection',<?php echo $protectionRsvp['protection_booking_id'];?>,'luxury')">
										<?php elseif ($protectionRsvp['statusCode'] == 4):?>
											<?php $detailURL = JUri::base().'index.php?option=com_beseated&view=protectionrequestpay&protection_booking_id='.$protectionRsvp['protection_booking_id'].'&Itemid='.$itemId;?>
											<a href="<?php echo $detailURL;?>">
												<input type="button" class="set-status-btn available" value="Pay">
											</a>
										<?php elseif ($protectionRsvp['statusCode'] == 6): ?>
											<input type="button" class="set-status-btn available" value="Decline" onclick="deleteRequest('protection',<?php echo $protectionRsvp['protection_booking_id'];?>,'luxury')">
										<?php elseif ($protectionRsvp['statusCode'] == 8): ?>
											<input type="button" class="set-status-btn available" value="Delete" onclick="deleteRequest('protection',<?php echo $protectionRsvp['protection_booking_id'];?>,'luxury')">
										<?php endif;?>
									<?php endif;?>
									<?php if ($protectionRsvp['bookedType'] == 'share'):?>
										<?php $payURL = JUri::base().'index.php?option=com_beseated&view=protectionshareuserpay&protection_booking_id='.$protectionRsvp['protection_booking_id'].'&protection_booking_split_id='.$protectionRsvp['protection_booking_split_id'].'&Itemid='.$itemId; ?>
										<?php if ($protectionRsvp['statusCode'] == 2):?>
											<a href="<?php echo $viewURL;?>">
												<input type="button" class="set-status-btn available" value="View Luxury">
											</a>
											<div class="controls request-status">
												<select class="selectpicker" id="invite_status" onchange="changeShareInvitationStatus('protection',<?php echo $protectionRsvp['protection_booking_id'];?>,'luxury','<?php echo $payURL; ?>');">
											        <option style="display:none;" value="Request">Request</option>
											        <option value="7">Pay</option>
											        <option value="6">Decline</option>
											    </select>
										    </div>
										<?php endif;?>
									<?php endif;?>
									<?php if ($protectionRsvp['bookedType'] == 'invitation'):?>
										<?php $invitedUserStatusURL = JUri::base().'index.php?option=com_beseated&view=protectioninviteduserstatus&viewByInvitedUser=1&booking_id='.$protectionRsvp['protection_booking_id'].'&Itemid='.$itemId; ?>
										<?php if ($protectionRsvp['statusCode'] == 2):?>
											<a href="<?php echo $invitedUserStatusURL;?>">
												<input type="button" class="set-status-btn available" value="View Luxury">
											</a>
											<div class="controls request-status">
												<select class="selectpicker" class="invite_status" id="invite_request_status" onchange="changeInvitationStatus('luxury',<?php echo $protectionRsvp['invitation_id'];?>);">
											        <option style="display:none;">Invited</option>
											        <option value="9">Going</option>
											        <option value="10">Not Going</option>
											        <option value="12">Maybe</option>
											    </select>
										    </div>
										<?php endif;?>
										<?php if ($protectionRsvp['statusCode'] == 12):?>
											<div class="controls request-status">
												<input type="button" class="set-status-btn pending" value="Maybe">											   
										    </div>
										<?php endif;?>
										<?php if ($protectionRsvp['statusCode'] == 10):?>
											<div class="controls request-status">
												<input type="button" class="set-status-btn pending" value="Not Going">											   
										    </div>
										<?php endif;?>
									<?php endif;?>
								</div>
							</div>
					</div>
				<?php endforeach;?>
			<?php endif;?>
			<?php if($chauffeurRsvpCount > 0):?>
				<?php foreach ($this->chauffeurRsvp as $key => $chauffeurRsvp): ?>
					<?php $viewURL = JUri::base().'index.php?option=com_beseated&view=chauffeurrequestpay&viewByShareUser=1&chauffeur_booking_id='.$chauffeurRsvp['chauffeur_booking_id'].'&Itemid='.$itemId; ?>
					<div class="venue-detail">
							<div class="venue-detail-inner">
								<div class="venue-booking-image">
									<?php $chauffeurAvatar = ($chauffeurRsvp['thumb_image']) ? JUri::base().'images/beseated/'.$chauffeurRsvp['thumb_image'] : '';?>
									<img src="<?php echo $chauffeurAvatar; ?>">
								</div>
								<div class="venue-booking-details">
									<p><?php echo ucfirst($chauffeurRsvp['service_name']);?></p>
									<p><?php echo $chauffeurRsvp['capacity']. ' Passenger(s)';?></p>
									<p><?php echo date('d M Y',strtotime($chauffeurRsvp['booking_date']));?></p>
								</div>
								<div class="status">
									<?php if ($chauffeurRsvp['bookedType'] == 'booking'):?>
										<?php if ($chauffeurRsvp['statusCode'] == 2):?>
											<input type="button" class="set-status-btn pending" value="Pending" onclick="cancelRequest('chauffeur',<?php echo $chauffeurRsvp['chauffeur_booking_id'];?>,'luxury')">
										<?php elseif ($chauffeurRsvp['statusCode'] == 4):?>
											<?php $detailURL = JUri::base().'index.php?option=com_beseated&view=chauffeurrequestpay&chauffeur_booking_id='.$chauffeurRsvp['chauffeur_booking_id'].'&Itemid='.$itemId;?>
											<a href="<?php echo $detailURL;?>">
												<input type="button" class="set-status-btn available" value="Pay">
											</a>
										<?php elseif ($chauffeurRsvp['statusCode'] == 6): ?>
											<input type="button" class="set-status-btn available" value="Decline" onclick="deleteRequest('chauffeur',<?php echo $chauffeurRsvp['chauffeur_booking_id'];?>,'luxury')">
										<?php elseif ($chauffeurRsvp['statusCode'] == 8): ?>
											<input type="button" class="set-status-btn available" value="Delete" onclick="deleteRequest('chauffeur',<?php echo $chauffeurRsvp['chauffeur_booking_id'];?>,'luxury')">
										<?php endif;?>
									<?php endif;?>
									<?php if ($chauffeurRsvp['bookedType'] == 'share'):?>
										<?php $payURL = JUri::base().'index.php?option=com_beseated&view=chauffeurshareuserpay&chauffeur_booking_id='.$chauffeurRsvp['chauffeur_booking_id'].'&chauffeur_booking_split_id='.$chauffeurRsvp['chauffeur_booking_split_id'].'&Itemid='.$itemId; ?>
										<?php if ($chauffeurRsvp['statusCode'] == 2):?>
											<a href="<?php echo $viewURL;?>">
												<input type="button" class="set-status-btn available" value="View Luxury">
											</a>
											<div class="controls request-status">
												<select class="selectpicker" id="invite_status" onchange="changeShareInvitationStatus('chauffeur',<?php echo $chauffeurRsvp['chauffeur_booking_id'];?>,'luxury','<?php echo $payURL; ?>');">
											        <option style="display:none;" value="Request">Request</option>
											        <option value="7">Pay</option>
											        <option value="6">Decline</option>
											    </select>
										    </div>
										<?php endif;?>
									<?php endif;?>
									<?php if ($chauffeurRsvp['bookedType'] == 'invitation'):?>
										<?php $invitedUserStatusURL = JUri::base().'index.php?option=com_beseated&view=chauffeurinviteduserstatus&viewByInvitedUser=1&booking_id='.$chauffeurRsvp['chauffeur_booking_id'].'&Itemid='.$itemId; ?>
										<?php if ($chauffeurRsvp['statusCode'] == 2):?>
											<a href="<?php echo $invitedUserStatusURL;?>">
												<input type="button" class="set-status-btn available" value="View Luxury">
											</a>
											<div class="controls request-status">
												<select class="selectpicker" class="invite_status_new" id="invite_request_status" onchange="changeInvitationStatus('luxury',<?php echo $chauffeurRsvp['invitation_id'];?>);">
											        <option style="display:none;">Invited</option>
											        <option value="9">Going</option>
											        <option value="10">Not Going</option>
											        <option value="12">Maybe</option>
											    </select>
										    </div>
										<?php endif;?>
										<?php if ($chauffeurRsvp['statusCode'] == 12):?>
											<div class="controls request-status">
												<input type="button" class="set-status-btn pending" value="Maybe">											   
										    </div>
										<?php endif;?>
										<?php if ($chauffeurRsvp['statusCode'] == 10):?>
											<div class="controls request-status">
												<input type="button" class="set-status-btn pending" value="Not Going">											   
										    </div>
										<?php endif;?>
									<?php endif;?>
								</div>
							</div>
					</div>
				<?php endforeach;?>
			<?php endif;?>
			<?php if($yachtRsvpCount > 0):?>
				<?php foreach ($this->yachtRsvp as $key => $yachtRsvp): ?>
					<?php $viewURL = JUri::base().'index.php?option=com_beseated&view=yachtrequestpay&viewByShareUser=1&yacht_booking_id='.$yachtRsvp['yacht_booking_id'].'&Itemid='.$itemId; ?>
					<div class="venue-detail">
							<div class="venue-detail-inner">
								<div class="venue-booking-image">
									<?php $yachtAvatar = ($yachtRsvp['thumb_image']) ? JUri::base().'images/beseated/'.$yachtRsvp['thumb_image'] : '';?>
									<img src="<?php echo $yachtAvatar; ?>">
								</div>
								<div class="venue-booking-details">
									<p><?php echo ucfirst($yachtRsvp['service_name']);?></p>
									<p><?php echo $yachtRsvp['booking_currency_sign'] .' '.number_format($yachtRsvp['total_price']);?></p>
									<p><?php echo date('d M Y',strtotime($yachtRsvp['booking_date']));?></p>
								</div>
								<div class="status">
									<?php if ($yachtRsvp['bookedType'] == 'booking'):?>
										<?php if ($yachtRsvp['statusCode'] == 2):?>
											<input type="button" class="set-status-btn pending" value="Pending" onclick="cancelRequest('yacht',<?php echo $yachtRsvp['yacht_booking_id'];?>,'luxury')">
										<?php elseif ($yachtRsvp['statusCode'] == 4):?>
											<?php $detailURL = JUri::base().'index.php?option=com_beseated&view=yachtrequestpay&yacht_booking_id='.$yachtRsvp['yacht_booking_id'].'&Itemid='.$itemId;?>
											<a href="<?php echo $detailURL;?>">
												<input type="button" class="set-status-btn available" value="Pay">
											</a>
										<?php elseif ($yachtRsvp['statusCode'] == 6): ?>
											<input type="button" class="set-status-btn available" value="Decline" onclick="deleteRequest('yacht',<?php echo $yachtRsvp['yacht_booking_id'];?>,'luxury')">
										<?php elseif ($yachtRsvp['statusCode'] == 8): ?>
											<input type="button" class="set-status-btn available" value="Delete" onclick="deleteRequest('yacht',<?php echo $yachtRsvp['yacht_booking_id'];?>,'luxury')">
										<?php endif;?>
									<?php endif;?>
									<?php if ($yachtRsvp['bookedType'] == 'share'):?>
										<?php $payURL = JUri::base().'index.php?option=com_beseated&view=yachtshareuserpay&yacht_booking_id='.$yachtRsvp['yacht_booking_id'].'&yacht_booking_split_id='.$yachtRsvp['yacht_booking_split_id'].'&Itemid='.$itemId; ?>
										<?php if ($yachtRsvp['statusCode'] == 2):?>
											<a href="<?php echo $viewURL;?>">
												<input type="button" class="set-status-btn available" value="View Luxury">
											</a>
											<div class="controls request-status">
												<select class="selectpicker" id="invite_status" onchange="changeShareInvitationStatus('yacht',<?php echo $yachtRsvp['yacht_booking_id'];?>,'luxury','<?php echo $payURL; ?>');">
											        <option style="display:none;" value="Request">Request</option>
											        <option value="7">Pay</option>
											        <option value="6">Decline</option>
											    </select>
										    </div>
										<?php endif;?>
									<?php endif;?>
									<?php if ($yachtRsvp['bookedType'] == 'invitation'):?>
										<?php $invitedUserStatusURL = JUri::base().'index.php?option=com_beseated&view=yachtinviteduserstatus&viewByInvitedUser=1&booking_id='.$yachtRsvp['yacht_booking_id'].'&Itemid='.$itemId; ?>
										<?php if ($yachtRsvp['statusCode'] == 2):?>
											<a href="<?php echo $invitedUserStatusURL;?>">
												<input type="button" class="set-status-btn available" value="View Luxury">
											</a>
											<div class="controls request-status">
												<select class="selectpicker" class="invite_status_new" id="invite_request_status" onchange="changeInvitationStatus('luxury',<?php echo $yachtRsvp['invitation_id'];?>);">
											        <option style="display:none;">Invited</option>
											        <option value="9">Going</option>
											        <option value="10">Not Going</option>
											        <option value="12">Maybe</option>
											    </select>
										    </div>
										<?php endif;?>
										<?php if ($yachtRsvp['statusCode'] == 12):?>
											<div class="controls request-status">
												<input type="button" class="set-status-btn pending" value="Maybe">											   
										    </div>
										<?php endif;?>
										<?php if ($yachtRsvp['statusCode'] == 10):?>
											<div class="controls request-status">
												<input type="button" class="set-status-btn pending" value="Not Going">											   
										    </div>
										<?php endif;?>
									<?php endif;?>
								</div>
							</div>
					</div>
				<?php endforeach;?>
			<?php endif;?>
        </div>
    </div>
     <div class="tab-pane fade <?php echo $eventClass; ?>" id="set3">
        <div class="tabbable">
        	<?php if($eventRsvpCount > 0):?>
        		<?php foreach ($this->eventRsvp as $key => $eventRsvp):?>
					<div class="venue-detail-main">
						<span class="venue-name"><?php echo ucfirst($eventRsvp['event_name']);?>,</span>
						<span class="venue-city"><?php echo $eventRsvp['city'];?></span>
					</div>
					<div class="venue-detail">
							<div class="venue-detail-inner">
								<div class="user-request-image">
								    <?php $eventUserAvatar = ($eventRsvp['thumb_avatar']) ? JUri::base().'images/beseated/'.$eventRsvp['thumb_avatar'] : '';?>
									<img src="<?php echo $eventUserAvatar; ?>">
								</div>
								<div class="venue-booking-details">
									<p><?php echo ucfirst($eventRsvp['event_name']);?></p>
									<p><?php echo $eventRsvp['booking_currency_sign'].' '.$eventRsvp['ticket_price'];?></p>
									<p><?php echo date('d M Y',strtotime($eventRsvp['event_date']));?></p>
								</div>
								<div class="status">
									<div class="control-group">
				                        <div class="controls request-status">
				                        	<?php if ($eventRsvp['invited_user_status'] == 1):?>				                            
				                             <select class="selectpicker" id="invite_request_status" onchange="changeEventInvitationStatus(<?php echo $eventRsvp['invite_id'];?>);">
										        <option style="display:none;" value="Request">Request</option>
										        <option value="11">Accept</option>
										        <option value="6">Decline</option>
										     </select>
										    <?php elseif ($eventRsvp['invited_user_status'] == 6):?>
										    	<input type="button" class="set-status-btn request" value="Delete" onclick="deleteRequest('Event',<?php echo $eventRsvp['invite_id'];?>,'event')">
											<?php endif;?>
				                        </div>
				                    </div>
								</div>
							</div>
					</div>
				<?php endforeach;?>
        	<?php endif;?>
        </div>
    </div>
</div>


<div id="myDeleteBookingModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>

      <div class="modal-header">Delete Booking</div>

      <div class="modal-body">
        <div class="modal-message">Are You Sure You Want To Delete This Booking?</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default cancel-booking" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-default delete-booking" data-dismiss="modal">Confirm</button>
      </div>
    </div>
  </div>
</div>
<div id="myCancelRequestModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>
      
      <div class="modal-header">Cancel Booking</div>
      
      <div class="modal-body">
        <div class="modal-message">Are You Sure You Want To Cancel This Booking? This Cannot Be Undone</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default cancel-booking" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-default delete-booking" data-dismiss="modal">Confirm</button>
      </div>
    </div>
  </div>
</div>

<style type="text/css">
div.modal.fade{display: none;}
div.modal.fade.in{display: block;}
</style>

<script type="text/javascript">
jQuery(document).ready(function()
{
	 jQuery('#alert-error').css('display', 'none');

	jQuery("ul.nav-tabs a").click(function (e) 
	{
	  e.preventDefault();
	    jQuery(this).tab('show');
	});
});


function changeEventInvitationStatus(invitationID)
{
	var redirectURL = '<?php echo $redirectURL; ?>';
	var invite_status = jQuery("#invite_request_status").val();

	jQuery.ajax({
		type: "GET",
		url: "index.php?option=com_beseated&task=guestrequests.changeEventInvitationStatus",
		data: "&invitationID="+invitationID+"&statusCode="+invite_status,
		success: function(response){
			if(response == "200")
			{
				window.location = redirectURL+'&comp=event';
			}
			else if(response == "400")
			{
				jQuery('#alert-error').show();
				jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Invalid invitation detail</h4>');
			}
			else if(response == "500")
			{
				jQuery('#alert-error').show();
				jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Error accur while change invitation status</h4>');
			}
			else if(response == "704")
			{
				jQuery('#alert-error').show();
				jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Session expired</h4>');
			}
		}
	});
}

function changeShareInvitationStatus(requestType,bookingID,managerType,viewURL)
{
	var redirectURL = '<?php echo $redirectURL; ?>';
	var invite_status = jQuery("#invite_status").val();
	
	
    jQuery(".delete-booking").unbind('click').bind('click', function () { }); 

    if(parseInt(invite_status) == 6)
    {
    	jQuery('#myDeleteBookingModal').modal('show');

	   jQuery('.delete-booking').click(function (e) 
	   {
			jQuery.ajax({
				type: "GET",
				url: "index.php?option=com_beseated&task=guestrequests.cancelShareInvitation",
				data: "&bookingID="+bookingID+"&bookingType="+requestType,
				success: function(response){
					if(response == "200")
					{
						window.location = redirectURL+'&comp='+requestType;
					}
					else if(response == "400")
					{
						jQuery('#alert-error').show();
						jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Invalid invitation detail</h4>');
					}
					else if(response == "500")
					{
						jQuery('#alert-error').show();
						jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Error accur while change invitation status</h4>');
					}
					else if(response == "704")
					{
						jQuery('#alert-error').show();
						jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Session expired</h4>');
					}
				}
			});
		});

	   jQuery('.cancel-booking').click(function (e) 
	   {
	   		jQuery("#invite_status option[value=Request]").attr('selected', 'selected');
	   });
	}
	else
	{
		window.location = viewURL;
	}
}

function changeInvitationStatus(requestType,invitationID)
{
	var redirectURL = '<?php echo $redirectURL; ?>';
	var invite_status = jQuery("#invite_request_status").val();

	jQuery.ajax({
		type: "GET",
		url: "index.php?option=com_beseated&task=guestrequests.changeInvitationStatus",
		data: "&invitationID="+invitationID+"&statusCode="+invite_status,
		success: function(response){
			if(response == "200")
			{
				window.location = redirectURL+'&comp='+requestType;
			}
			else if(response == "400")
			{
				jQuery('#alert-error').show();
				jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Invalid invitation detail</h4>');
			}
			else if(response == "500")
			{
				jQuery('#alert-error').show();
				jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Error accur while change invitation status</h4>');
			}
			else if(response == "704")
			{
				jQuery('#alert-error').show();
				jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Session expired</h4>');
			}
		}
	});
		
}


function deleteRequest(requestType,bookingID,managerType)
{
	jQuery('#myDeleteBookingModal').modal('show');
	var invite_status = jQuery("#invite_status").val();
	var redirectURL = '<?php echo $redirectURL; ?>';

	jQuery(".delete-booking").unbind('click').bind('click', function () { }); 

	jQuery('.delete-booking').click(function (e) 
	{
		jQuery.ajax({
			type: "GET",
			url: "index.php?option=com_beseated&task=guestrequests.deleteBooking",
			data: "&bookingID="+bookingID+"&bookingType="+requestType,
			success: function(response){
				if(response == "200")
				{
					window.location = redirectURL+'&comp='+managerType;
					
				}
				else if(response == "400")
				{
					jQuery('#alert-error').show();
					jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Invalid invitation detail</h4>');
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

function cancelRequest(requestType,bookingID,managerType)
{
	jQuery('#myCancelRequestModal').modal('show');
	
	var redirectURL = '<?php echo $redirectURL; ?>';

	jQuery(".delete-booking").unbind('click').bind('click', function () { }); 

	jQuery('.delete-booking').click(function (e) 
	{
		jQuery.ajax({
			type: "GET",
			url: "index.php?option=com_beseated&task=guestrequests.cancelBooking",
			data: "&bookingID="+bookingID+"&bookingType="+requestType,
			success: function(response){
				if(response == "200")
				{
					window.location = redirectURL+'&comp='+managerType;
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

