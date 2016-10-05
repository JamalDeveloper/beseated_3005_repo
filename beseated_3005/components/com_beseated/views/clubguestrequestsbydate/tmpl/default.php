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

?>
<script type="text/javascript">
function updateRemainingGuest()
{
	var guestlistID   = jQuery('#guestlistID').val();
    var updatedGuest = jQuery('#updatedGuest').val();
    jQuery.ajax({
	    type: "GET",
	    url: "index.php?option=com_beseated&task=clubguestlist.updateremainingguest",
	    data: "guestlist_request_id="+guestlistID+"&updated_guest="+updatedGuest,
	    success: function(data){
	        location.reload();
	    }
    });
}

function setModelValue(guestlistID,totalRmaining)
{
	 jQuery('#guestlistID').val(guestlistID);
	 options = "<select name='updatedGuest' id='updatedGuest'>";
	 for(i=1;i<=totalRmaining;i++)
	 {
	 	options = options + "<option value='"+i+"'>"+i+"</option>";
	 }
	 options = options + "</select>";
	 jQuery('#guestlistDropdown').html(options);
}
</script>
<div class="bct-summary-container">
	<div class="summary-list guest-list">
		<ul>
		<?php if($this->bookings): ?>
			<?php foreach ($this->bookings as $key => $booking):?>
				<?php $userDetail = BeseatedHelper::guestUserDetail($booking->user_id);?>
				<li id="guestlist_<?php echo $booking->guest_booking_id;?>">
					<div class="guestlist-detail">
						<div class="guestlist-detail-inner">
							<div class="guestlist-user-name">
								<?php echo ucfirst($userDetail->full_name);?>
							</div>
							<div class="guestlist-guest-count">
								<?php echo $booking->total_guest .'('.$booking->male_guest.'M&nbsp;-&nbsp;'.$booking->female_guest.'F)';?>
							</div>
						</div>
						<div class="guestlist-remaining-count">
							<?php echo 'Remaining Guest(s):&nbsp;' . $booking->remaining_guest;?>
						</div>
					</div>
					<div class="bk-single-right span4">
						<?php if($booking->remaining_guest > 0): ?>
							<a href="#myModal" role="button" data-toggle="modal" onclick="setModelValue('<?php echo $booking->guest_booking_id; ?>','<?php echo $booking->remaining_guest; ?>')"><button class="del-btn"  type="button">-</button></a>
						<?php endif; ?>
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
<!-- Modal -->
<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Remaining Guest</h3>
    </div>
    <div class="modal-body">
        <div class="large-rating-wrp">
        </div>
        <p id="guestlistDropdown">
        </p>
    </div>
    <div class="modal-footer">
        <input type="hidden" name="guestlistID" id="guestlistID" value="1">
        <button class="btn btn-primary" onclick="updateRemainingGuest()">Update Guestlist</button>
    </div>
</div>
