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

$app    = JFactory::getApplication();
$Itemid = $app->input->getInt('Itemid');
?>

<div class="guest-request-detail wrapper">
	<div class="guest-request-main">
		<div class="table-detail article_wrp">
			<div class="table-name">
				<h4><?php echo ucfirst($this->venueRsvpDetail->table_name);?></h4>
			</div>
			<div class="booking-date">
				<h4><?php echo date('d-M-Y',strtotime($this->venueRsvpDetail->booking_date));?></h4>
			</div>
			<div class="three_blocks">
					<div class="client-count">
						<h4><?php echo $this->venueRsvpDetail->total_guest;?>client(s)</h4>
					</div>
					<div class="male-count">
						<h4><span>&nbsp;</span><?php echo $this->venueRsvpDetail->male_guest;?></h4>
					</div>
					<div class="female-count">
						<h4><span>&nbsp;</span><?php echo $this->venueRsvpDetail->female_guest;?></h4>
					</div>
			</div>
		</div>
		<div class="request-table-amount">
			<?php echo "Minimum Spend&nbsp;&nbsp;" . BeseatedHelper::currencyFormat($this->venueRsvpDetail->booking_currency_code,$this->venueRsvpDetail->booking_currency_sign,$this->venueRsvpDetail->min_price);?>
		</div>
		<div class="request-table-note">
			<p class="request-note">Note</p>
			<p class="request-note-detail">No Deposite Required, Pay Bill Amount At Venue</p>
		</div>
		<div class="request-table-btn">
			<?php if ($this->vanueHasBottle > 0):?>
			<div class="bottle-add">
				<h2> Want to add Bottle Service? </h2>
				<a href="index.php?option=com_beseated&view=clubbottles&club_id=<?php echo $this->venueRsvpDetail->venue_id;?>&table_id=<?php echo $this->venueRsvpDetail->table_id;?>&Itemid=<?php echo $itemId; ?>">
					<button class="table-yes-btn">Yes</button>
				</a>
				<button class="table-no-btn">No</button>
			</div>
			<?php else:?>
				<form id="form_confirmbooking" class="form-horizontal" method="post" a action="<?php echo JRoute::_('index.php?option=com_beseated&view=guestrequestsdetail&Itemid='.$Itemid);?>">
					<button class="table-book-btn">Beseated</button>
					<button class="table-cancel-btn">Cancel</button>
					<input type="hidden" id="task" name="task" value="GuestRequestsDetail.confirmBooking">
					<input type="hidden" id="view" name="view" value="GuestRequestsDetail">
					<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
					<input type="hidden" id="booking_id" name="booking_id" value="<?php echo $this->venueRsvpDetail->venue_table_booking_id; ?>">
				</form>
			<?php endif;?>
			<div class="bottle-book-btn">
				<form id="form_confirmbooking" class="form-horizontal" method="post" a action="<?php echo JRoute::_('index.php?option=com_beseated&view=guestrequestsdetail&Itemid='.$Itemid);?>">
					<button class="table-book-btn">Beseated</button>
					<button class="table-cancel-btn">Cancel</button>
					<input type="hidden" id="task" name="task" value="GuestRequestsDetail.confirmBooking">
					<input type="hidden" id="view" name="view" value="GuestRequestsDetail">
					<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
					<input type="hidden" id="booking_id" name="booking_id" value="<?php echo $this->venueRsvpDetail->venue_table_booking_id; ?>">
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery('.bottle-book-btn').hide();

	jQuery('.table-no-btn').click(function(event) {
		jQuery('.bottle-book-btn').show();
		jQuery('.bottle-add').hide();
	});

	jQuery('.table-book-btn').click(function(event) {
		jQuery('#form_confirmbooking').submit();
	});

});
</script>