<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
$document = JFactory::getDocument();

require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';

?>

<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=ticket&layout=edit'); ?>"
    method="post" name="adminForm" id="adminForm">
    <div class="form-horizontal">
        <fieldset>
            <div class="span12">
                <legend><?php echo JText::_('COM_BESEATED_TICKET_DETAIL'); ?></legend>

                <?php

                foreach ($this->UserTicketDetail as $key => $TicketDetail)
                {?>
                   <div class="control-group">
                        <div class="control-label">Event Name</div>
                        <div class="controls"><input type="text" name="eventName" value="<?php echo $TicketDetail->event_name; ?>" readOnly/></div>
                   </div>
                   <div class="control-group">
                        <div class="control-label">User Name</div>
                        <div class="controls"><input type="text" name="userName" value="<?php echo $TicketDetail->bookingOwner; ?>" readOnly/></div>
                   </div>
                   <div class="control-group">
                        <div class="control-label">Total Tickets</div>
                        <div class="controls"><input type="text" name="totalTickets" value="<?php echo count($this->UserTicketDetail); ?>" readOnly/></div>
                   </div>
                    <div class="control-group">
                        <div class="control-label">Event Date</div>
                        <div class="controls"><input type="text" name="userName" value="<?php echo date('d-m-Y',strtotime($TicketDetail->event_date)); ?>" readOnly/></div>
                   </div>
                   <div class="control-group">
                        <div class="control-label">Purchase Date/Time</div>
                        <div class="controls"><input type="text" name="totalTickets" value="<?php echo date('d-m-Y H:m ',strtotime($TicketDetail->purchaseDate)); ?>" readOnly/></div>
                   </div>
                <?php break;
               }
                ?>

                <legend></legend>

            </div>
             <div class="span4">
                    <div style="position:relative;">
                    <?php

                foreach ($this->UserTicketDetail as $key => $TicketDetail)
                {
                    if($key == 0)
                    {?>
                    <img src="<?php echo JURI::Root().'images/beseated/'.$TicketDetail->image ?>"  style="margin:3px;" width="100px" height="100px">
                    <div class="control-group">
                        <div class="control-label"><b>Invitee Name</b></div>
                        <div class="controls"><input type="text" name="inviteeName" value="<?php echo $TicketDetail->bookingOwner; ?>" readOnly/></div>
                    </div>
                <?php
                    }
                    else
                    {
                        $userDetail = BeseatedHelper::getBeseatedUserProfile($TicketDetail->inviteeUserID);

                        $model = $this->getModel();

                        if($userDetail)
                        {
                            $username   = $userDetail->full_name;
                        }
                        else
                        {
                             $username   =  $model->getInviteeDetail($TicketDetail->ticket_booking_id);
                        }


                ?>

                    <img src="<?php echo JURI::Root().'images/beseated/'.$TicketDetail->image ?>"  style="margin:3px;" width="100px" height="100px">
                    <div class="control-group">
                        <div class="control-label"><b>Invitee Name</b></div>
                        <div class="controls"><input type="text" name="inviteeName" value="<?php echo $username; ?>" readOnly/></div>
                    </div>
                    <?php
                    }

                }
                ?>
                    </div>
            </div>

            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />

</form>
