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

$app                 = JFactory::getApplication();
$itemId              = $app->input->getInt('Itemid');
$venueRsvpCount      = count($this->venueRsvp);
$protectionRsvpCount = count($this->protectionRsvp);
$chauffeurRsvpCpunt  = count($this->ChauffeurRsvp);
$yachtRsvpCount      = count($this->YachtRsvp);
$eventRsvpCount      = count($this->eventRsvp);
$luxuryRsvp          = array_merge($this->ChauffeurRsvp, $this->protectionRsvp, $this->YachtRsvp);

$luxuryTypes = array(
  'Chauffeur' => 2,
  'Protection' => 3,
  'Yacht' => 4,
)
?>

<section class="page-section page-rsvp">
  <div class="container">
    
    <div class="submenu">
      <?php include_once('templates/beseated-theme/partials/guest-profile-menu.php') ?>
    </div>
    
    <div class="row">
      <div class="col-md-8 col-md-offset-2">

        <div class="tabs-headers">
          <h2 class="tab-header heading-3 active" data-target="venues">Venues</h2>
          <h2 class="tab-header heading-3" data-target="luxury">Luxuries</h2>
          <h2 class="tab-header heading-3" data-target="events">Events</h2>
        </div>

        <div class="tabs-content">

          <div class="active" id="venues">
            <?php foreach ($this->venueRsvp as $rsvp):?>
              <div class="requests-heading heading-3">
                <span class="name"><?php echo ucfirst($rsvp['venueName']);?></span>
                <span class="location"><?php echo $rsvp['city'];?></span>
                <hr>
              </div>
              <?php foreach ($rsvp['bookings'] as $booking):?>
                <div class="row">
                  <div class="col-md-12">
                    <div class="bordered-box item">
                      <div class="row">
                        <div class="col-md-4">
                          <div class="image" style="background-image: url( <?php echo $booking['thumbImage']; ?> );"></div>
                        </div>
                        <div class="col-md-8">
                          <div class="description">
                            <p class="name"><?php echo ucfirst($booking['tableName']);?></p>
                            <p class="price"><?php echo $booking['totalPrice']; ?></p>
                            <p class="date"><?php echo date('d-M-Y',strtotime($booking['bookingDate']));?></p>
                          </div>
                          <div class="actions">
                            <?php if ($booking['statusCode'] == 2):?>
                              <button type="button" class="button" onclick="cancelBooking(<?php echo $booking['venueBookingID'];?>,1)">Pending</button>
                            <?php elseif ($booking['statusCode'] == 4):?>
                              <a class="button" href="index.php?option=com_beseated&view=guestrequestsdetail&table_booking_id=<?php echo $booking['venueBookingID']?>&venue_id=<?php echo $booking['venueID']?>&Itemid=<?php echo $itemId;?>">Confirm</a>
                            <?php elseif ($booking['statusCode'] == 6): ?>
                              <button type="button" class="button" onclick="deleteBooking(<?php echo $booking['venueBookingID'];?>,1)">Decline</button>
                            <?php elseif ($booking['statusCode'] == 8): ?>
                              <button type="button" class="button" onclick="deleteBooking(<?php echo $booking['venueBookingID'];?>,1)">Delete</button>
                            <?php endif;?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>

          <div id="luxury">
            <?php foreach ($luxuryRsvp as $rsvp):?>
              <div class="requests-heading heading-3">
                <span class="name"><?php echo ucfirst($rsvp['elementName']);?></span>
                <span class="location"><?php echo $rsvp['location'];?></span>
                <hr>
              </div>
              <?php foreach ($rsvp['bookings'] as $booking):?>
                <div class="row">
                  <div class="col-md-12">
                    <div class="bordered-box item">
                      <div class="row">
                        <div class="col-md-4">
                          <div class="image" style="background-image: url( <?php echo $booking['thumbImage']; ?> );"></div>
                        </div>
                        <div class="col-md-8">
                          <div class="description">
                            <p class="name"><?php echo ucfirst($booking['serviceName']); ?></p>
                            <p class="type"><?php echo ucfirst($booking['elementType']); ?></p>
                            <p class="price"><?php echo $booking['totalPrice']; ?></p>
                            <p class="date"><?php echo date('d-M-Y',strtotime($booking['bookingDate']));?></p>
                          </div>
                          <div class="actions">
                            <?php $typeId = $luxuryTypes[$booking['elementType']] ?>             
                            <?php if ($booking['statusCode'] == 2):?>
                              <button type="button" class="button" onclick="cancelBooking(<?php echo $booking['elementBookingID'];?>,<?php echo $typeId; ?>)">Pending</button>
                            <?php elseif ($booking['statusCode'] == 4):?>
                              <a class="button" href="<?php echo $booking['paymentURL']; ?>">Pay</a>
                            <?php elseif ($booking['statusCode'] == 6): ?>
                              <button type="button" class="button" onclick="deleteBooking(<?php echo $booking['elementBookingID'];?>,<?php echo $typeId; ?>)">Decline</button>
                            <?php elseif ($booking['statusCode'] == 8): ?>
                              <button type="button" class="button" onclick="deleteBooking(<?php echo $booking['elementBookingID'];?>,<?php echo $typeId; ?>)">Delete</button>
                            <?php endif;?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>        
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>

          <div id="events">
            <?php foreach ($this->eventRsvp as $rsvp):?>
              <div class="requests-heading heading-3">
                <span class="name"><?php echo ucfirst($rsvp['venueName']);?></span>
                <span class="location"><?php echo $rsvp['city'];?></span>
                <hr>
              </div>
              <?php foreach ($luxuryRsvp['bookings'] as $booking):?>
                <div class="row">
                  <div class="col-md-12">
                    <div class="bordered-box item">
                      <div class="row">
                        <div class="col-md-4">
                          <div class="image" style="background-image: url( <?php echo $booking['thumbImage']; ?> );"></div>
                        </div>
                        <div class="col-md-8">
                          <div class="description">
                            <p class="name"><?php echo ucfirst($booking['tableName']); ?></p>
                            <p class="price"><?php echo $booking['totalPrice']; ?></p>
                            <p class="date"><?php echo date('d-M-Y',strtotime($booking['bookingDate'])); ?></p>
                          </div>
                          <div class="actions">                    
                            <?php if ($booking['statusCode'] == 2):?>
                              <button type="button" class="button" onclick="cancelBooking(<?php echo $booking['venueBookingID'];?>,<?php echo $typeId; ?>)">Pending</button>
                            <?php elseif ($booking['statusCode'] == 4):?>
                              <a class="button" href="<?php echo $booking['paymentURL']; ?>">Pay</a>
                            <?php elseif ($booking['statusCode'] == 6): ?>
                              <button type="button" class="button" onclick="deleteBooking(<?php echo $booking['venueBookingID'];?>,<?php echo $typeId; ?>)">Decline</button>
                            <?php elseif ($booking['statusCode'] == 8): ?>
                              <button type="button" class="button" onclick="deleteBooking(<?php echo $booking['venueBookingID'];?>,<?php echo $typeId; ?>)">Delete</button>
                            <?php endif;?>
                          </div>
                        </div>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
          
        </div>
      </div>      
    </div>

  </div>
</section>

<script>
  $('.tabs-headers').tabs();  
</script>

<div id="myCancelBookingModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="heading-3">Cancel Booking</h3>
      </div>
      <div class="modal-body">
        <div class="modal-message">Are you sure you want to Cancel this Booking?</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default cancel-booking" data-dismiss="modal">Yes</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
      </div>
    </div>
  </div>
</div>

<div id="myDeleteBookingModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="heading-3">Delete Booking</h3>
      </div>
      <div class="modal-body">
        <div class="modal-message">Are you sure you want to Delete this Booking?</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default delete-booking" data-dismiss="modal">Yes</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
      </div>
    </div>
  </div>
</div>

<div id="myConfirmEventModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="heading-3">Accept invitation</h3>
      </div>
      <div class="modal-body">
        <div class="modal-message">Are you sure you want to Accept this Invitation?</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default accept-invite" data-dismiss="modal">Accept</button>
        <button type="button" class="btn btn-default decline-invite" data-dismiss="modal">Decline</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  function cancelBooking(id,bookingType){
    $('#myCancelBookingModal').modal('show');

    $('.cancel-booking').click(function(event) {
      $.ajax({
        type: "GET",
        url: "index.php?option=com_beseated&task=guestrequests.cancelBooking",
        data: "&booking_id="+id+"&booking_type="+bookingType,
        success: function(response){
          if(response == "200")
          {
            location.reload();
          }
          if(response == "400")
          {
            $('#alert-error').show();
            $('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Request Not Deleted.</h4>');
          }
        }
      });
    });
  }

  function deleteBooking(id,bookingType){
    $('#myDeleteBookingModal').modal('show');
    $('.delete-booking').click(function(event) {
      $.ajax({
        type: "GET",
        url: "index.php?option=com_beseated&task=guestrequests.deleteBooking",
        data: "&booking_id="+id+"&booking_type="+bookingType,
        success: function(response){
          if(response == "200")
          {
            location.reload();
          }
          if(response == "400")
          {
            $('#alert-error').show();
            $('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Request Not Deleted.</h4>');
          }
        }
      });
    });
  }

  function confirmEvent(id){
    $('#myConfirmEventModal').modal('show');

    $('.decline-invite').click(function(event) {
      $.ajax({
        type: "GET",
        url: "index.php?option=com_beseated&task=guestrequests.changeEventInvitationStatus",
        data: "&booking_id="+id+"&status_code=6",
        success: function(response){
          if(response == "200")
          {
            location.reload();
          }
          if(response == "400")
          {
            $('#alert-error').show();
            $('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Request Not Deleted.</h4>');
          }
        }
      });
    });
  }
</script>

