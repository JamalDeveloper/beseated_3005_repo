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
$document     = JFactory::getDocument();
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/confirm-js/jquery.confirm.js');
$resultUpcomingBookings = array();
$resultHistoryBookings  = array();
?>
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
<?php
foreach ($this->bookings as $key => $bookings)
{
	if(BeseatedHelper::isPastDate($bookings->booking_date))
	{
		$resultHistoryBookings[]  = $bookings;
	}
	else
	{
		$resultUpcomingBookings[] = $bookings;
	}
}
?>
<div class="bct-summary-container">
	<ul class="nav nav-tabs book-tab">
		<li class="active" id="table1"><a href="#table" data-toggle="tab">Upcoming</a></li>
		<li id="packages1"><a href="#packages" data-toggle="tab">History</a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="table">
			<div class="summary-list">
				<ul>
					<?php if(count($resultUpcomingBookings) > 0): ?>
						<div class="booking-count">Bookings <span class="total-booking-count"><?php echo count($resultUpcomingBookings);?></span></div>
						<?php foreach ($resultUpcomingBookings as $key => $booking):?>
						<li id="booking_<?php echo $booking->yacht_booking_id; ?>">
							<div class="main-booking">
							<?php if (!empty($booking->avatar)):?>
								<div class="booking-image">
									<?php  $pos = strpos($booking->thumb_avatar, 'facebook');?>
									<?php if ($pos > 0):?>
										<a href="<?php echo 'https://www.facebook.com/'.$booking->fb_id;?>" target="_blank">
										<img src="<?php echo $booking->thumb_avatar;?>" alt="" />
										</a>
									<?php else:?>
										<a data-toggle="modal" data-target="#myFacebookFriendsModal">
										<img src="<?php echo JURI::root().'/images/beseated/'.$booking->thumb_avatar;?>" alt="" />
										</a>
									<?php endif; ?>
								</div>
							<?php endif;?>
								<div class="booking-details">
									<a href="index.php?option=com_beseated&view=clubbookingdetail&booking_id=<?php echo $booking->venue_table_booking_id; ?>&Itemid=<?php echo $Itemid; ?>">
										<div class="booking-name">
											<?php echo ucfirst($booking->full_name);?>
										</div>
										<div class="booking-service-name">
											<?php echo ucfirst($booking->table_name).'&nbsp;-&nbsp;'.BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?>
										</div>
										<div class="booking-date-time">
											<div class="booking-date">
												<?php echo date('d-M-Y',strtotime($booking->booking_date));?>
											</div>
											<div class="booking-time">
											   <?php if ($booking->is_day_club):?>
												<?php echo BeseatedHelper::convertToHM($booking->booking_time); ?>
											    <?php endif; ?>
											</div>
										</div>
									</a>
								</div>
							</div>
						</li>
						<?php endforeach; ?>
					<?php else: ?>
						<div id="system-message">
			                <div class="alert alert-block">
			                    <button type="button" class="close" data-dismiss="alert">&times;</button>
			                    <h4><?php echo JText::_('COM_BCTED_USERBOOKINGS_NO_BOOKING_FOUND_GENERAL_TITLE'); ?></h4>
			                    <div><p><?php echo JText::_('COM_BCTED_USERBOOKINGS_NO_BOOKING_FOUND_FOR_GENERAL_DESC'); ?></p></div>
			                </div>
			            </div>
					<?php endif; ?>
				</ul>
			</div>
		</div>
		<div class="tab-pane" id="packages">
			<div class="summary-list">
				<ul>
					<?php if(count($resultHistoryBookings > 0)): ?>
						<div class="booking-count">Bookings <span class="total-booking-count"><?php echo count($resultHistoryBookings);?></span></div>
						<?php foreach ($resultHistoryBookings as $key => $booking):?>
						<li id="booking_<?php echo $booking->protection_booking_id; ?>">
							<div class="main-booking">
								<?php if (!empty($booking->avatar)):?>
									<div class="booking-image">
										<?php  $pos = strpos($booking->thumb_avatar, 'facebook');?>
										<?php if ($pos > 0):?>
											<a href="<?php echo 'https://www.facebook.com/'.$booking->fb_id;?>" target="_blank">
											<img src="<?php echo $booking->thumb_avatar;?>" alt="" />
											</a>
										<?php else:?>
											<a data-toggle="modal" data-target="#myFacebookFriendsModal">
											<img src="<?php echo JURI::root().'/images/beseated/'.$booking->thumb_avatar;?>" alt="" />
											</a>
										<?php endif; ?>
									</div>
								<?php endif; ?>
								<div class="booking-details">
									<a href="index.php?option=com_beseated&view=clubbookingdetail&booking_id=<?php echo $booking->venue_table_booking_id; ?>&Itemid=<?php echo $Itemid; ?>">
										<div class="booking-name">
											<?php echo ucfirst($booking->full_name);?>
										</div>
										<div class="booking-service-name">
											<?php echo ucfirst($booking->table_name).'&nbsp;-&nbsp;'.BeseatedHelper::currencyFormat($booking->booking_currency_code,$booking->booking_currency_sign,$booking->total_price);?>
										</div>
										<div class="booking-date-time">
											<div class="booking-date">
												<?php echo date('d-M-Y',strtotime($booking->booking_date));?>
											</div>
											<div class="booking-time">
											    <?php if ($booking->is_day_club):?>
												<?php echo BeseatedHelper::convertToHM($booking->booking_time); ?>
												<?php endif; ?>
											</div>
										</div>
									</a>
								</div>
							</div>
						</li>
						<?php endforeach; ?>
					<?php else: ?>
						<div id="system-message">
			                <div class="alert alert-block">
			                    <button type="button" class="close" data-dismiss="alert">&times;</button>
			                    <h4><?php echo JText::_('COM_BCTED_USERBOOKINGS_NO_BOOKING_FOUND_GENERAL_TITLE'); ?></h4>
			                    <div><p><?php echo JText::_('COM_BCTED_USERBOOKINGS_NO_BOOKING_FOUND_FOR_GENERAL_DESC'); ?></p></div>
			                </div>
			            </div>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
</div>

<style>
div.modal.fade{display: none;}
div.modal.fade.in{display: block;}
</style>
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
