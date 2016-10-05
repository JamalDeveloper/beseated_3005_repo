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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHTML::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');


//$this->clubDetail = BeseatedHelper::getVenueDetail($this->clubID);

//echo JUri::base(); //http://istage.website/beseated-ii/administrator/
?>

<!-- <script src="http://code.jquery.com/jquery-2.1.1.js" type="text/javascript"></script> -->
<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js" type="text/javascript"></script>


<link rel="stylesheet" type="text/css" href="<?php echo JUri::base().'components/com_beseated/assets/multiuploadfile.css'; ?>">
<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places" type="text/javascript"></script>
<script type="text/javascript">

    function initialize()
    {
         jQuery("#jform_total_guard,#jform_male_guest,#jform_female_guest").keydown(function (e) {

            // Allow: backspace, delete, tab, escape, enter and .
            if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                 // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                 // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40 ) ) {
                     // let it happen, don't do anything
                     return;
            }
            if(jQuery(this).val().length>5){
                return false;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });



    }

    Joomla.submitbutton = function(task)
    {
        var booking_time       = jQuery('#jform_booking_time').val();
        var booking_date       = jQuery('#jform_booking_date').val();
        var venue_id           = jQuery('#jform_venue_id').val();

        var arrDate = booking_date.split("-");

        if(!booking_time)
        {
             var booking_time = '23:59:59';
        }

        var arrTime = booking_time.split(":");

        var today = new Date();
        useDate = new Date(arrDate[2], arrDate[1] - 1, arrDate[0],arrTime[0],arrTime[1]);

        var fullDate      = new Date();
        var dateValid     = 1;

        if(task != 'guestlistbooking.cancel')
        {
            if (useDate < fullDate)
            {
                dateValid = 0;
                alert("Date and Time must be in the current or future");
            }
            else if(dateValid == 1)
            {
               jQuery.ajax({
                    url: 'index.php?option=com_beseated&task=guestlistbooking.checkForVenueClosed',
                    type: 'GET',
                    data: 'booking_date='+booking_date+'&venue_id='+venue_id,
                    success: function(response){
                             if(response == 1)
                             {
                                alert('Please select another date because selected venue closed on your booking date');
                             }
                             else
                             {
                                Joomla.submitform(task, document.getElementById('adminForm'));
                             }

                    }
                });
            }
            else
            {
                Joomla.submitform(task, document.getElementById('adminForm'));
            }
        }
        else
        {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    }

    google.maps.event.addDomListener(window, 'load', initialize);
</script>

<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=guestlistbooking&layout=edit&guest_booking_id=' . (int) $this->item->guest_booking_id); ?>"
    method="post" name="adminForm" id="adminForm" >
    <div class="form-horizontal">
        <fieldset>
            <div class="span12">
                <legend><?php echo JText::_('COM_BESEATED_VENUE_GUEST_LIST_REQUEST_DETAIL'); ?></legend>

                <div class="span8">
                     <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('user_id'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('user_id'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('venue_id'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('venue_id'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('male_guest'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('male_guest'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('female_guest'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('female_guest'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('booking_date'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('booking_date'); ?></div>
                    </div>
                </div>


            </div>
            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />

</form>


