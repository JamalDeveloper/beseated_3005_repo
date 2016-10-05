<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
$templateDir = Juri::base().'templates/'.JFactory::getApplication()->getTemplate();

// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$input           = JFactory::getApplication()->input;
$Itemid          = $input->get('Itemid', 0, 'int');
$firstTimeShare   = $input->get('firstTimeShare', 0, 'int');
$viewByShareUser   = $input->get('viewByShareUser', 0, 'int');

$document = JFactory::getDocument();
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/confirm-js/jquery.confirm.js');
//$document->addStylesheet('//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css');
$document->addStylesheet($templateDir.'/font-awesome/css/font-awesome.css');
$document->addStylesheet($templateDir.'/progressbar/css/bootstrap-progressbar-3.3.4.css');
$document->addStylesheet($templateDir.'/progressbar/css/bootstrap-progressbar-3.3.4.min.css');

$redirectURL = JUri::base().'index.php?option=com_beseated&view=guestrequests&Itemid='.$Itemid;

$user     = JFactory::getUser();
$userType = BeseatedHelper::getUserType($user->id);

$yachtBooking = $this->yachtBookingDetail;

$yachtShareUserDetail = $this->yachtShareUserDetail;
$paidUserPer = $this->percentagePaidSharedUser;

?>

<?php if(!$yachtBooking->is_splitted && $firstTimeShare == 0) : ?>

<link rel="stylesheet" href="bootstrap-progressbar-3.3.4.min.css">
<link rel="stylesheet" href="bootstrap-progressbar-3.3.4.css">

<div class="row">
  <div class="span9 payment-request-detail">
    <h2><?php echo $yachtBooking->yacht_name; ?></h2>
    <div class="payment-request-detail">
      <div class="span6"><?php echo $yachtBooking->service_name; ?></div>
    </div>
  </div>
</div>
<div class="row">
    <div class="payment-request-detail">
      <div class="span6"><i class="fa fa-calendar" aria-hidden="true">&nbsp;&nbsp;</i><?php echo date('d M Y',strtotime($yachtBooking->booking_date)); ?></div>
      <div class="span3"> <i class="fa fa-clock-o" aria-hidden="true">&nbsp;&nbsp;</i><?php echo date('h:i',strtotime($yachtBooking->booking_time)); ?></div>
    </div>
</div>

<div class="row">
    <div class="payment-request-detail">
      <div class="span6"><i class="fa fa-female" aria-hidden="true"><i class="fa fa-male" aria-hidden="true">&nbsp;&nbsp;</i></i><?php echo $yachtBooking->capacity; ?></div>
      <div class="span6"><i class="fa fa-hourglass-start" aria-hidden="true"></i>&nbsp;&nbsp;<?php echo $yachtBooking->total_hours; ?></div>
    </div>
</div>
<div class="row">
    <div class="payment-request-detail">
      <div class="span6">Price Per Hour</div>
      <div class="span3"><?php echo $yachtBooking->booking_currency_sign.' '.number_format($yachtBooking->price_per_hours); ?></div>
    </div>
</div>
<div class="row">
    <div class="payment-request-detail">
      <div class="span6">Total Amount</div>
      <div class="span3"><?php echo $yachtBooking->booking_currency_sign.' '.number_format($yachtBooking->total_price); ?></div>
    </div>
</div>
<div class="row">
    <div class="payment-request-detail">
      <div class="span6"><u>Note</u></div>
    </div>
</div>
<div class="row">
    <div class="payment-request-detail">
      <?php $deposit_per = ($yachtBooking->deposit_per) ? $yachtBooking->deposit_per : '0.00'; ?>
      <div class="span9"><p><?php echo $deposit_per; ?>% Payment Required, Non-Refundable prior <?php echo $yachtBooking->refund_policy; ?> hrs</p></div>
    </div>
</div>

<div class="pay-request">
    <a href="<?php echo JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$yachtBooking->yacht_booking_id.'&booking_type=yacht'; ?>">
        <input type="button" class="btn btn-large" name="payment" value="Pay By Credit Card">
    </a>
    <a href="<?php echo JUri::root().'index.php?option=com_beseated&view=yachtshareamount&booking_id='.$yachtBooking->yacht_booking_id.'&booking_type=yacht&Itemid='.$Itemid; ?>">
    <input type="button" class="btn btn-large" name="payment" value="Share Amount">
    </a>
    <input type="button" class="btn btn-large" name="payment" value="Cancel" onclick="cancelBooking(<?php echo $yachtBooking->yacht_booking_id;?>,'request')">
</div>

</body>
<?php else : ?>
  <?php if($yachtBooking->total_split_count > $yachtBooking->splitted_count) : ?>
  <div class="pay-request">
  <?php else: ?>
  <div class="pay-balance-request">
  <?php endif; ?>

        <input type="text" value="<?php echo $yachtBooking->booking_currency_sign.' '.number_format($yachtBooking->remaining_amount ); ?>" readonly>
        <label>Remaining Amount To Pay</label>

        <div class="progress-div">
            <?php echo $paidUserPer; ?> % Complete
          <progress class="progressbar progress-warning" value="<?php echo $paidUserPer; ?>" max="100"></progress>
        </div>

        <?php foreach ($yachtShareUserDetail as $key => $userDetail) : ?>  
          <div class="splitted-user-list">
             
              <img src="<?php echo $userDetail->thumb_avatar;?>" alt="" />

              <?php if($userDetail->split_payment_status != 7 && $viewByShareUser == 0) : ?>
              <a href="<?php echo JUri::root().'index.php?option=com_beseated&view=yachtshareinvitation&invitation_id='.$userDetail->yacht_booking_split_id.'&booking_id='.$yachtBooking->yacht_booking_id.'&Itemid='.$Itemid; ?>">
                 <div class="splitted-user-name-div">
                    <b><?php echo ucfirst($userDetail->full_name); ?></b> 
                </div>
              </a>
              <?php else: ?> 
                <b><?php echo ucfirst($userDetail->full_name); ?></b>
              <?php endif; ?> 
             
              
              <?php if($userDetail->split_payment_status == 2) : ?>
                <input type="button" class="set-status-btn" value="Pending">
              <?php elseif($userDetail->split_payment_status == 6) : ?>
                <input type="button" class="set-status-btn" value="Declined">
              <?php elseif($userDetail->split_payment_status == 7) : ?>
                <input type="button" class="set-status-btn" value="Paid">
              <?php endif; ?> 
          </div>

        <?php endforeach; ?>
      
        <?php if($viewByShareUser == 0) : ?>
          <a href="<?php echo JUri::root().'index.php?option=com_beseated&task=payment.pay&booking_id='.$yachtBooking->yacht_booking_id.'&booking_type=yacht&pay_balance=1'; ?>">
              <input type="button" class="btn btn-large" name="payment" value="Pay Balance">
          </a>
          <?php if($yachtBooking->total_split_count > $yachtBooking->splitted_count) : ?>
          <a href="<?php echo JUri::root().'index.php?option=com_beseated&view=yachtshareinvitation&booking_id='.$yachtBooking->yacht_booking_id.'&Itemid='.$Itemid; ?>">
              <input type="button" class="btn btn-large" name="payment" value="Invite Friends">
          </a>
          <?php endif;?>

          <input type="button" class="btn btn-large" name="payment" value="Cancel" onclick="cancelBooking(<?php echo $yachtBooking->yacht_booking_id;?>,'booking')">
        <?php endif;?>
</div>
<?php endif; ?>


<div id="myCancelRequestModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>

      <div class="modal-header">Cancel Request</div>

      <div class="modal-body">
        <div class="modal-message">Are You Sure You Want To Cancel This Request?</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-default cancel-booking" data-dismiss="modal">Confirm</button>
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
        <div class="modal-message">Are You Sure You Want To Cancel This Booking?</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-default cancel-booking" data-dismiss="modal">Confirm</button>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">

function cancelBooking(bookingID,type)
{
    if(type == 'request')
    {
        jQuery('#myCancelRequestModal').modal('show');
    }
    else
    {
        jQuery('#myCancelBookingModal').modal('show');
    }  
    
    var redirectURL = '<?php echo $redirectURL; ?>';

    jQuery(".cancel-booking").unbind('click').bind('click', function () { }); 

    jQuery('.cancel-booking').click(function (e) 
    {
        jQuery.ajax({
            type: "GET",
            url: "index.php?option=com_beseated&task=guestrequests.cancelBooking",
            data: "&bookingID="+bookingID+"&bookingType=yacht",
            success: function(response){
                if(response == "200")
                {
                    window.location = redirectURL+'&comp=luxury';
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






