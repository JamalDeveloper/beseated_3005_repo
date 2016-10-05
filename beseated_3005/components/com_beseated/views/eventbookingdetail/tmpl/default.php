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

$input         = JFactory::getApplication()->input;
$Itemid        = $input->getInt('Itemid');
$currentItemid = $input->get('Itemid', 0, 'int');
$this->user    = JFactory::getUser();
$this->isRoot  = $this->user->authorise('core.admin');
$document      = JFactory::getDocument();
$document->addScript(JUri::root().'modules/mod_profileslider/media/js/html5gallery.js');
$imagesCount   = count($this->ticketImages);

$remainingTickets = $imagesCount;

?>

<div class="event-booking-slider-wrp">
	<div class="row-fluid book-dtlwrp">
		<div class="info-image-only">
			<?php if ($imagesCount > 0): ?>
				<div style="display:none;" class="html5gallery" data-skin="gallery" data-width="500" data-height="300" >
					<?php for ($i = 0; $i < $imagesCount; $i++): ?>
						<?php if(!empty($this->ticketImages[$i]->image)):?>
							<a href="<?php echo JUri::base()."images/beseated/".$this->ticketImages[$i]->image; ?>"><img src="<?php echo JUri::base()."images/beseated/".$this->ticketImages[$i]->image; ?>"></a>
						<?php endif; ?>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<div class="event-booking-details">
	<div class="event-booking-details-inner">
		<div class="event-details">
			<span class="event-booking-user-name"><?php echo ucfirst($this->bookingDetails->full_name);?></span>
			<span class="event-booking-event-name"><?php echo ucfirst($this->bookingDetails->event_name);?></span>
			<span class="event-booking-location"><?php echo $this->bookingDetails->location;?></span>
		</div>

		<?php if($this->bookingDetails->bookedType == 'booking'): ?>
		<div class="event-total-tickets">
			<span class="event-total-tickets-count"><?php echo count($this->ticketImages); ?></span>
		</div>
		<?php endif; ?>

		<div class="event-date-time">
			<span class="event-booking-date"><?php echo date('d M Y',strtotime($this->bookingDetails->event_date));?></span>
			<span class="event-booking-time"><?php echo $this->bookingDetails->event_time;?></span>
		</div>

		<?php if($this->bookingDetails->bookedType == 'booking'):?>
		<div class="event-invite-btn">
				<a href="index.php?option=com_beseated&view=eventinviteduserstatus&event_id=<?php echo $this->bookingDetails->event_id; ?>&ticket_booking_id=<?php echo $this->bookingDetails->ticket_booking_id; ?>&remaining_ticket=<?php echo $remainingTickets;?>&Itemid=<?php echo $Itemid; ?>">
					<?php if($remainingTickets == 1): ?>
						<button type="button" class="btn btn-large span">SEND TICKET</button>
					<?php else: ?>
						<button type="button" class="btn btn-large span">INVITE</button>
					<?php endif; ?>
				</a>
		</div>
		<?php endif; ?>
	</div>
</div>