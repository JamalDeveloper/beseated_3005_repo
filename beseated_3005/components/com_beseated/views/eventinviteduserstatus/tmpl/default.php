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

$input    = JFactory::getApplication()->input;
$Itemid              = $input->get('Itemid', 0, 'int');
$event_id            = $input->get('event_id', 0, 'int');
$ticket_booking_id   = $input->get('ticket_booking_id', 0, 'int');
$remainingTickets    = $input->get('remaining_ticket', 0, 'int');

$document = JFactory::getDocument();
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/confirm-js/jquery.confirm.js');
$user     = JFactory::getUser();
$userType = BeseatedHelper::getUserType($user->id);

?>
    <div class="invite-friends-details-main">
        <?php foreach ($this->invitationDetails as $key => $invitation) : ?>
            
            <div class="message-details-main" id="msg_<?php echo $thread->connection_id; ?>">
                <?php  $pos = strpos($thumbImage, 'facebook');?>
                <div class="user-image">
                        <img src="<?php echo $invitation['thumbAvatar'];?>" alt="" />
                </div>
                <div class="message-details-inner">
                        <div class="message-details">
                            <p><?php echo ucfirst($invitation['fullName']);?></p>
                        </div>
                </div>
                 <div class="invite-status">
                    <?php if( $invitation['statusCode'] == 1): ?>
                        <input type="button" class="set-status-btn request" value="Pending">
                    <?php elseif ($invitation['statusCode'] == 11) : ?>
                        <input type="button" class="set-status-btn request" value="Accepted">
                    <?php endif; ?>
                   
                </div>
            </div>
                
        <?php endforeach; ?>
    </div>

    <?php if( $remainingTickets == 1): ?>
        <div class="invite-friends">
                <div class="controls">
                    <a href="index.php?option=com_beseated&view=eventinvitation&event_id=<?php echo $event_id; ?>&ticket_booking_id=<?php echo $ticket_booking_id; ?>&remaining_ticket=<?php echo $remainingTickets;?>&Itemid=<?php echo $Itemid; ?>">
                            <button type="button" class="btn btn-large span">SEND TICKET</button>
                    </a>
                </div>
        </div>
    <?php elseif($remainingTickets > 1): ?>
        <div class="invite-friends">
                <div class="controls">
                    <a href="index.php?option=com_beseated&view=eventinvitation&event_id=<?php echo $event_id; ?>&ticket_booking_id=<?php echo $ticket_booking_id; ?>&remaining_ticket=<?php echo $remainingTickets;?>&Itemid=<?php echo $Itemid; ?>">
                            <button type="button" class="btn btn-large span">INVITE</button>
                    </a>
                </div>
        </div>
    <?php endif; ?>




