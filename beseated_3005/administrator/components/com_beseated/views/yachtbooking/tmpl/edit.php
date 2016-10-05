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
        var org_hours;
        var input = document.getElementById('jform_meetup_location');
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            if (!place.geometry)
            {
              return;
            }
        });

         jQuery("#jform_total_hours").keydown(function (e) {

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

        jQuery(document).ready(function()
        {
            setServiceHours();

            yacht_id = jQuery("#jform_yacht_id").val();
            jQuery.ajax({
                        url:'index.php?option=com_beseated&task=yachtbooking.getYachtServcies',
                        type: "POST",
                        data: {
                            'yacht_id': yacht_id
                        }
                    }).done(function(services) {

                       jQuery("#service_name_chzn").html(services);
                       jQuery(".service_name_chzn").chosen();

                    });

             jQuery("#jform_yacht_id").change(function()
            {
                setServiceHours();

                yacht_id = jQuery("#" + this.id).val();

                jQuery.ajax({
                        url:'index.php?option=com_beseated&task=yachtbooking.getYachtServcies',
                        type: "POST",
                        data: {
                            'yacht_id': yacht_id
                        }
                    }).done(function(services) {

                       jQuery("#service_name_chzn").html(services);
                       jQuery(".service_name_chzn").chosen();

                    })
                })

        });

        function setServiceHours()
        {
            setTimeout(function()
            {
             var service_id = jQuery( "#service_name_chzn option:selected" ).val();

             jQuery.ajax({
                        url:'index.php?option=com_beseated&task=yachtbooking.getServiceHour',
                        type: "POST",
                        data: {
                            'service_id': service_id
                        }
                    }).done(function(hours) {

                         jQuery("#jform_total_hours").val(hours);
                         org_hours = hours;
                    })
         }, 1500);
        }

        jQuery("#service_name_chzn").change(function()
        {
            var service_id = jQuery( "#service_name_chzn option:selected" ).val();

             jQuery.ajax({
                        url:'index.php?option=com_beseated&task=yachtbooking.getServiceHour',
                        type: "POST",
                        data: {
                            'service_id': service_id
                        }
                    }).done(function(hours) {

                     jQuery("#jform_total_hours").val(hours);
                     org_hours = hours;
                    })


        });



     Joomla.submitbutton = function(task)
    {
        var booking_time       = jQuery('#jform_booking_time').val();
        var booking_date       = jQuery('#jform_booking_date').val();
        var new_hours          = jQuery('#jform_total_hours').val();

        var arrDate = booking_date.split("-");
        var arrTime = booking_time.split(":");

        var today = new Date();
        useDate = new Date(arrDate[2], arrDate[1] - 1, arrDate[0],arrTime[0],arrTime[1]);

        var fullDate      = new Date();
        var dateValid     = 1;


        var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(booking_time);

        if(task != 'yachtbooking.cancel')
        {
           if (isValid)
           {
                if (useDate < fullDate)
                {
                     dateValid     = 0;
                    alert("Date and Time must be in the current or future");
                }
                else if(dateValid == 1)
                {

                    if(parseInt(new_hours) < parseInt(org_hours))
                    {

                        alert("Hours should be greater than or equal to default hours");
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
            else
            {

                alert("Invalid Time format");
            }

        }
        else
        {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    }}

    google.maps.event.addDomListener(window, 'load', initialize);
</script>

<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=yachtbooking&layout=edit&yacht_booking_id=' . (int) $this->item->yacht_booking_id); ?>"
    method="post" name="adminForm" id="adminForm" >
    <div class="form-horizontal">
        <fieldset>
            <div class="span12">
                <legend><?php echo JText::_('COM_BESEATED_YACHT_DETAIL'); ?></legend>

                <div class="span8">
                     <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('user_id'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('user_id'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('yacht_id'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('yacht_id'); ?></div>
                    </div>
                    <div class="control-group">
                     <div class="control-label">Service Name</div>
                       <div class="controls">
                            <select name="service_name" id="service_name" class="service_name_chzn">
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('total_hours'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('total_hours'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('booking_date'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('booking_date'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('booking_time'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('booking_time'); ?></div>
                    </div>
                </div>


            </div>
            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />

</form>


