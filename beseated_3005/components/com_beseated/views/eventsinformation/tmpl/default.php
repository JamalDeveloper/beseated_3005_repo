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
JHtml::_('bootstrap.modal');
$input       = JFactory::getApplication()->input;
$Itemid      = $input->get('Itemid', 0, 'int');
$clubID      = $input->get('club_id', 0, 'int');
$app         = JFactory::getApplication();
$menu        = $app->getMenu();
$menuItem    = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
$loginItemid = $menuItem->id;
$bctParams   = BeseatedHelper::getExtensionParam();
$accessLevel = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
$access      = array('access','link');
$property    = array($accessLevel,'index.php?option=com_beseated&view=clubinformation');
$menuItem2   = $menu->getItems( $access, $property, true );
$link2       = 'index.php?option=com_beseated&view=clubinformation&club_id='.$clubID.'&Itemid='.$menuItem2->id;
$loginLink   = 'index.php?option=com_users&view=login&Itemid='.$loginItemid.'&return='.base64_encode($link2);
$document    = JFactory::getDocument();
$document->addStylesheet(JUri::root().'components/com_beseated/assets/css/bootstrap-toggle.min.css');
$document->addScript(JUri::root().'components/com_beseated/assets/confirm-js/bootstrap-toggle.min.js');
$document->addScript(JUri::root().'modules/mod_profileslider/media/js/html5gallery.js');
$imagesCount = count($this->images);

$ticketTypeDetail = $this->ticketTypeDetail;

//echo "<pre>";print_r($ticketTypeDetail);echo "<pre/>";exit();
?>

<div class="eventdetail clb-infowrp">
	<div class="event-image">
		<img src="<?php echo $ticketTypeDetail->image;?>" alt="" />
	</div>

	<div class="event-ticket-price">
		<div class="ticket-detail">
			<div class="ticket-type">
				<?php echo $ticketTypeDetail->ticket_type;?>
			</div>
			<div class="ticket-type">
				<?php echo $ticketTypeDetail->ticket_zone;?>
			</div>
			<div class="ticket-type">
				<?php echo BeseatedHelper::currencyFormat($ticketTypeDetail->currency_code,$ticketTypeDetail->currency_sign,$ticketTypeDetail->ticket_price);?>
			</div>
		</div>
	</div>
	<div class="event-detail">
		<div class="event-name">
			<?php echo $this->event->event_name; ?>
		</div>
		<div class="event-location">
			<?php echo $this->event->location;?>
		</div>
		<div class="event-date-time">
			<div class="event-date">
				<?php echo date('d M Y',strtotime($this->event->event_date));?>
			</div>
			<div class="event-time">
				<?php echo $this->event->event_time;?>
			</div>
		</div>
	</div>
	<div class="event-ticket-price">
		<?php echo BeseatedHelper::currencyFormat($this->ticketTypeDetail->currency_code,$this->ticketTypeDetail->currency_sign,$this->ticketTypeDetail->ticket_price);?>
	</div>
	<div class="event-booking-section">
		<div class="final-price" id="final-price">
			<div class="event-minus">minus</div>
			<div class="event-ticket-qty">0</div>
			<div class="event-plus">plus</div>
		</div>
		<div class="remaining">
			(<span class="remaining-tickets-title">Remaining x</span>
			<span class="remaining-tickets"><?php echo $this->ticketTypeDetail->available_tickets;?></span>)
		</div>
	</div>
	<div class="two_data">
		<span class="total-ticket-qty"></span>
		<span id="total-ticket-price" class="total-ticket-price"></span>
	</div>

	<div class="event-data">
		<form id = "form_eventinfo" class="form-horizontal prf-form" enctype="multipart/form-data" method="post" accept="<?php echo JRoute::_('index.php?option=com_beseated&view=eventsinformation'); ?>">
			<div class="control-group">
				<div class="controls span6">
					<button type="button" disabled="true" class="btn btn-large span">Beseated</button>
					<input type="hidden" id="event_id" name="event_id" value="<?php echo $this->ticketTypeDetail->event_id; ?>">
					<input type="hidden" id="ticket_price" name="ticket_price" value="<?php echo $this->ticketTypeDetail->ticket_price; ?>">
					<input type="hidden" id="booking_currency_code" name="booking_currency_code" value="<?php echo $this->ticketTypeDetail->currency_code;?>">
					<input type="hidden" id="booking_currency_sign" name="booking_currency_sign" value="<?php echo $this->ticketTypeDetail->currency_sign; ?>">
					<input type="hidden" id="ticket_type_id" name="ticket_type_id" value="<?php echo $this->ticketTypeDetail->ticket_type_zone_id; ?>">
					<input type="hidden" id="task" name="task" value="eventsinformation.bookEventTicket">
					<input type="hidden" id="view" name="view" value="eventsinformation">
					<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
					<input type="hidden" id="total_ticket" name="total_ticket" value="">
					<input type="hidden" id="total_price" name="total_price" value="">
					<input type="hidden" id="available_ticket" name="available_ticket" value="<?php echo $this->ticketTypeDetail->available_tickets; ?>">
				</div>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery('.event-plus').click(function(event) {
		var ticket           = jQuery('.event-ticket-qty');
		var ticketQty        = parseInt(ticket.text());
		var remainingTickets = '<?php echo $this->ticketTypeDetail->available_tickets;?>';
		var ticketPrice      = jQuery('#ticket_price').val();
		var currency_sign    = '<?php echo $this->ticketTypeDetail->currency_sign;?>';

		if (parseInt(remainingTickets) > ticket.text()){
			ticket.text(ticketQty+1);
			jQuery('#total_ticket').val(ticket.text());
			if (ticket.text() > 0) {
				jQuery('.btn-large').attr('disabled', false);
				var remainingTicket    = jQuery('.remaining-tickets');
				var remainingTicketQty = parseInt(remainingTicket.text());
				remainingTicket.text(remainingTicketQty-1);
				jQuery('#total_price').val(ticketPrice*ticket.text());
				jQuery('#available_ticket').val(remainingTicket.text());
				jQuery('.total-ticket-qty').text(ticket.text() + 'Ticket(s)');
				jQuery('.total-ticket-price').text(ticket.text() + ' X ' + currency_sign + ticketPrice + '=' + ticketPrice*ticket.text());
			}
		};
	});

	jQuery('.event-minus').click(function(event) {
		var ticket        = jQuery('.event-ticket-qty');
		var ticketQty     = parseInt(ticket.text());
		var ticketPrice   = jQuery('#ticket_price').val();
		var currency_sign = '<?php echo $this->ticketTypeDetail->currency_sign;?>';
		console.log(ticketQty);
		if (ticketQty != 0){
			ticket.text(ticketQty-1);
			jQuery('#total_ticket').val(ticket.text());
			if (ticket.text() > 0) {
				var remainingTicket    = jQuery('.remaining-tickets');
				var remainingTicketQty = parseInt(remainingTicket.text());
				remainingTicket.text(remainingTicketQty+1);
				jQuery('#total_price').val(ticketPrice*ticket.text());
				jQuery('#available_ticket').val(remainingTicket.text());
				console.log(ticket.text());
				jQuery('.total-ticket-qty').text(ticket.text() + 'Ticket(s)');
				console.log(ticket.text());
				jQuery('#total-ticket-price').text(ticket.text() + 'X' + currency_sign + ticketPrice + '=' + ticketPrice*ticket.text());
			}else{
				jQuery('.btn-large').attr('disabled', true);
				var remainingTicket    = jQuery('.remaining-tickets');
				var remainingTicketQty = parseInt(remainingTicket.text());
				remainingTicket.text(remainingTicketQty+1);
				jQuery('#total_price').val(ticketPrice*ticket.text());
				jQuery('#available_ticket').val(remainingTicket.text());
				jQuery('.total-ticket-qty').text(" ");
				jQuery('#total-ticket-price').text(" ");
			}
		}
	});

	jQuery('button').click(function(event) {
		jQuery('#form_eventinfo').submit();
	});

});
</script>



