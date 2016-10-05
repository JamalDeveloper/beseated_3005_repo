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
        var input = document.getElementById('jform_dropoff_location');
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            if (!place.geometry)
            {
              return;
            }
        });

        var input = document.getElementById('jform_pickup_location');
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            if (!place.geometry)
            {
              return;
            }
        });

         jQuery("#jform_total_price").keydown(function (e) {

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
            chauffeur_id = jQuery("#jform_chauffeur_id").val();
            jQuery.ajax({
                        url:'index.php?option=com_beseated&task=chauffeurbookingrequest.getChauffeurServcies',
                        type: "POST",
                        data: {
                            'chauffeur_id': chauffeur_id
                        }
                    }).done(function(services) {

                       jQuery("#service_name_chzn").html(services);
                       jQuery(".service_name_chzn").chosen();

                    });

            jQuery("#jform_chauffeur_id").change(function()
            {
                chauffeur_id = jQuery("#" + this.id).val();

                jQuery.ajax({
                        url:'index.php?option=com_beseated&task=chauffeurbookingrequest.getChauffeurServcies',
                        type: "POST",
                        data: {
                            'chauffeur_id': chauffeur_id
                        }
                    }).done(function(services) {

                       jQuery("#service_name_chzn").html(services);
                       jQuery(".service_name_chzn").chosen();

                    })
                })
        });
    }

    Joomla.submitbutton = function(task)
    {
        var booking_time       = jQuery('#jform_booking_time').val();
        var booking_date       = jQuery('#jform_booking_date').val();

        var arrDate = booking_date.split("-");
        var arrTime = booking_time.split(":");

        var today = new Date();
        useDate = new Date(arrDate[2], arrDate[1] - 1, arrDate[0],arrTime[0],arrTime[1]);

        var fullDate      = new Date();


        var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(booking_time);

        if(task != 'chauffeurbookingrequest.cancel')
        {
            if (isValid)
            {
                if (useDate < fullDate)
                {
                    alert("Date and Time must be in the current or future");
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
    }


    google.maps.event.addDomListener(window, 'load', initialize);
</script>

<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=chauffeurbookingrequest&layout=edit&chauffeur_booking_id=' . (int) $this->item->chauffeur_booking_id); ?>"
    method="post" name="adminForm" id="adminForm" >
    <div class="form-horizontal">
        <fieldset>
            <div class="span12">
                <legend><?php echo JText::_('COM_BESEATED_CHAUFFEUR_BOOKING_REQUEST_DETAIL'); ?></legend>

                <div class="span8">
                     <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('user_id'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('user_id'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('chauffeur_id'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('chauffeur_id'); ?></div>
                    </div>
                    <div class="control-group">
                     <div class="control-label">Service Name</div>
                       <div class="controls">
                            <select name="service_name" id="service_name" class="service_name_chzn">
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('pickup_location'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('pickup_location'); ?></div>
                    </div>
                     <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('dropoff_location'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('dropoff_location'); ?></div>
                    </div>
                    <!-- <div class="control-group">
                        <div class="control-label"><?php //echo $this->form->getLabel('total_price'); ?></div>
                        <div class="controls"><?php //echo $this->form->getInput('total_price'); ?></div>
                    </div> -->
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


