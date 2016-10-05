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
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
$document     = JFactory::getDocument();
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/confirm-js/jquery.confirm.js');
?>
<script type="text/javascript">
function changeRequestStatus(status)
{
	var owner_message = jQuery('#owner_message').val();
	jQuery.ajax({
		type: "GET",
		url: "index.php?option=com_beseated&task=clubrequestdetail.changeRequstStatus",
		data: "&request_id=<?php echo $this->booking->venue_table_booking_id; ?>&status="+status+"&owner_message="+owner_message,
		success: function(response){
			if(response == "1")
			{
                jQuery('#already-system-message').hide();
                window.location.href='index.php?option=com_beseated&view=clubrequests&Itemid=<?php echo $Itemid; ?>';
			}

            if(response == "3")
            {
                jQuery('#already-system-message').show();
                jQuery('#system-message').hide();
            }
		}
    });
}

function removeRequestBooking(bookingID,userID)
{
    jQuery.ajax({
        url: 'index.php?option=com_beseated&task=clubbookings.deletePastBooking',
        type: 'GET',
        data: '&user_type=venue&booking_id='+bookingID+'&user_id='+userID,
        success: function(response){
            if(response == "200")
            {
                jQuery('#booking_'+bookingID).remove();
                window.location.href='index.php?option=com_beseated&view=clubrequests&Itemid=<?php echo $Itemid; ?>';
            }
        }
    })
    .done(function() {
    })
    .fail(function() {
    })
    .always(function() {
    });
}
</script>
<script type="text/javascript">
    jQuery(function(){
        jQuery('#system-message').hide();
        jQuery('#already-system-message').hide();
    });
</script>
<div class="table-wrp">
<div id="already-system-message">
    <div class="alert alert-block">
        <h4><?php echo JText::_('COM_BCTED_TABLE_TIME_SLOT_ALREADY_BOOKED_TITLE'); ?></h4>
        <div><p><?php echo JText::_('COM_BCTED_TABLE_TIME_SLOT_ALREADY_BOOKED_DESC'); ?></p></div>
    </div>
</div>

<h1>Request Detail</h1>
    <div class="req-detailwrp" id="booking_<?php echo $this->booking->venue_booking_id; ?>">
    <!-- <?php
        $fromTime = explode(":", $this->booking->booking_from_time);
        $toTime = explode(":", $this->booking->booking_to_time);
    ?> -->
    	<h2> <?php echo ucfirst($this->booking->name); ?>  has requested the following booking:</h2>
        <h4>
            <?php if($this->booking->premium_table_id)
                    echo '<b>Table Name : </b>'.ucfirst($this->booking->table_name);
                else
                    echo '<b>Table Name : </b>'.ucfirst($this->booking->table_name);

            ?>
        </h4>
        <h4><b>Date : </b><?php echo date('d-m-Y',strtotime($this->booking->booking_date)); ?></h4>
        <!-- <h4><b>Time : From </b><?php echo $fromTime[0].':'.$fromTime[1]; ?><b> To </b> <?php echo  $toTime[0].':'.$toTime[1]; ?></h4> -->
        <h4><b>Number of Guests : </b><?php echo $this->booking->total_guest .'('.$this->booking->male_guest .'M/'.$this->booking->female_guest.'F)'; ?></h4>
        <h4><b>Status : </b><?php echo ucfirst($this->booking->status_text); ?></h4>
        <h4><?php echo ucfirst($this->booking->description); ?></h4>
        <?php if($this->booking->venue_status != '9' && $this->booking->venue_status != '11'){ ?>
           <!--  <textarea id="owner_message" rows="7" placeholder="Enter message here"></textarea> -->
        <?php } ?>

        <div class="row-fluid req-btnwrp">
        	<?php
            if($this->booking->venue_status == '9' || $this->booking->venue_status == '11' || $this->booking->venue_status == '7')
            {?>
                <?php if($this->booking->venue_status == '7'): ?>
                    <div class="span4"><button id="cancelConfirmVenueBooking" class="btn btn-primary" type="button">Decline</button> </div>
                    <script type="text/javascript">
                        jQuery("#cancelConfirmVenueBooking").click(function() {
                            jQuery.confirm({
                                title: "Please confirm",
                                text: "Please confirm you wish to Accept this request. This cannot be undone.",
                                confirm: function() {
                                    changeRequestStatus('cancel');
                                },
                                cancel: function() {
                                }
                            });
                        });
                    </script>
                <?php else: ?>
                    <div class="span4"><button id="deleteRequestBookingConfirm" class="btn btn-primary"  onclick="" type="button" >Delete</button> </div>
                    <script type="text/javascript">
                        jQuery("#deleteRequestBookingConfirm").click(function() {
                            jQuery.confirm({
                                title: "Please confirm",
                                text: "Please confirm you wish to Delete this request. This cannot be undone.",
                                confirm: function() {
                                    removeRequestBooking('<?php echo $this->booking->venue_booking_id; ?>','<?php echo $this->booking->user_id; ?>')
                                },
                                cancel: function() {
                                }
                            });
                        });
                    </script>
                <?php endif; ?>
        	<?php }
            else
            { ?>
        		<div class="span4"><button id="cancelConfirmVenueBooking" onclick="" class="btn btn-primary" type="button">Decline</button> </div>
                <script type="text/javascript">
                    jQuery("#cancelConfirmVenueBooking").click(function() {
                        jQuery.confirm({
                            title: "Please confirm",
                            text: "Please confirm you wish to Decline this request. This cannot be undone.",
                            confirm: function() {
                                changeRequestStatus('cancel');
                            },
                            cancel: function() {
                            }
                        });
                    });
                </script>
        	<?php }
            ?>

            <?php
            if($this->booking->venue_status != '9' && $this->booking->venue_status != '11' && $this->booking->venue_status != '6' )
            {
                if($this->booking->venue_status == 7 )
                {?>
    				<!-- <div class="span4"><button class="btn btn-primary" type="button" style=" border: 1px solid red; cursor: unset;">Waiting List</button> </div> -->
                <?php }
                else
                {?>
                	<!-- <div class="span4"><button onclick="changeRequestStatus('waiting')" class="btn btn-primary" type="button">Waiting List</button> </div> -->
                <?php }
                ?>
    			<?php
                if($this->booking->venue_status == 6 )
                {?>
                	<div class="span4"><button class="btn btn-primary pull-right" type="button" style=" border: 1px solid red; cursor: unset;">Accept</button> </div>
                <?php }
                else
                {?>
    					<div class="span4"><button id="acceptConfirmVenueBooking" class="btn btn-primary pull-right" type="button">Accept</button> </div>
                        <script type="text/javascript">
                            jQuery("#acceptConfirmVenueBooking").click(function() {
                                jQuery.confirm({
                                    title: "Please confirm",
                                    text: "Please confirm you wish to Accept this request. This cannot be undone.",
                                    confirm: function() {
                                        changeRequestStatus('ok');
                                    },
                                    cancel: function() {
                                    }
                                });
                            });
                        </script>
    			<?php }
            }
            ?>
        </div>
    </div>
</div>
