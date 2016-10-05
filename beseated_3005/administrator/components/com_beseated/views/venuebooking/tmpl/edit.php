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
    	var is_day_club;
    	var tableID;
        var totalGuest;
        var input = document.getElementById('jform_meetup_location');
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            if (!place.geometry)
            {
              return;
            }
        });

         jQuery("#jform_total_guard,#jform_male_guest,#jform_female_guest,#jform_bill_post_amount").keydown(function (e) {

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
            getTableCapacity();

            venue_id = jQuery("#jform_venue_id").val();
            jQuery.ajax({
                        url:'index.php?option=com_beseated&task=venuebooking.getVenueServcies',
                        type: "POST",
                        data: {
                            'venue_id': venue_id
                        }
                    }).done(function(services) {

                       jQuery("#service_name_chzn").html(services);
                       jQuery(".service_name_chzn").chosen();

                    });

            jQuery.ajax({
                        url:'index.php?option=com_beseated&task=venuebooking.isDayClub',
                        type: "POST",
                        data: {
                            'venue_id': venue_id
                        }
                    }).done(function(isDayClub) {

                     if(isDayClub == '0')
                     {
                     	 is_day_club = 0;
                         jQuery('#jform_booking_time').prop('readonly', true);
                     }
                     else
                     {
                     	 is_day_club = 1;
                        jQuery('#jform_booking_time').prop('readonly', false);

                     }

                    });

             jQuery("#jform_venue_id").change(function()
            {
                venue_id = jQuery("#" + this.id).val();

                jQuery.ajax({
                        url:'index.php?option=com_beseated&task=venuebooking.getVenueServcies',
                        type: "POST",
                        data: {
                            'venue_id': venue_id
                        }
                    }).done(function(services) {

                       jQuery("#service_name_chzn").html(services);
                       jQuery(".service_name_chzn").chosen();

                    })
                })

             });

             jQuery("#jform_venue_id").change(function()
            {
                 jQuery.ajax({
                        url:'index.php?option=com_beseated&task=venuebooking.isDayClub',
                        type: "POST",
                        data: {
                            'venue_id': venue_id
                        }
                    }).done(function(isDayClub) {

                     if(isDayClub == '0')
                     {
                     	is_day_club = 0;
                         jQuery('#jform_booking_time').prop('readonly', true);

                     }
                     else
                     {
                     	is_day_club = 1;
                        jQuery('#jform_booking_time').prop('readonly', false);
                     }

                    });
            });

        function getTableCapacity()
        {
            setTimeout(function()
            {
              var table_id = jQuery( "#service_name_chzn option:selected" ).val();

              tableID = table_id;

             jQuery.ajax({
                        url:'index.php?option=com_beseated&task=venuebooking.getTableCapacity',
                        type: "POST",
                        data: {
                            'table_id': table_id
                        }
                    }).done(function(capacity) {

                     totalGuest = capacity;
                    })

         }, 2000);
        }

        jQuery("#service_name_chzn").change(function()
        {
            var table_id = jQuery( "#service_name_chzn option:selected" ).val();

            tableID = table_id;

             jQuery.ajax({
                        url:'index.php?option=com_beseated&task=venuebooking.getTableCapacity',
                        type: "POST",
                        data: {
                            'table_id': table_id
                        }
                    }).done(function(capacity) {

                     totalGuest = capacity;
                    })


        });

        function timeOut()
        {
            setTimeout(function()
            {
                //alert('hii');

            }, 5000);
        }


     Joomla.submitbutton = function(task)
    {
    	var tableID = jQuery( "#service_name_chzn option:selected" ).val();

        var booking_time = jQuery('#jform_booking_time').val();
        var booking_date = jQuery('#jform_booking_date').val();
        var venue_id     = jQuery('#jform_venue_id').val();
        var male_guest   = jQuery('#jform_male_guest').val();
        var female_guest = jQuery('#jform_female_guest').val();

        var totalMaleFemale = parseInt(male_guest)+parseInt(female_guest);

        var arrDate = booking_date.split("-");
        var arrTime = booking_time.split(":");

        if ( jQuery('#jform_booking_time').is('[readonly]') )
        {
            var booking_time = '23:59:59';
            var arrTime = booking_time.split(":");
        }


        var today = new Date();
        useDate = new Date(arrDate[2], arrDate[1] - 1, arrDate[0],arrTime[0],arrTime[1]);

        var fullDate      = new Date();
        var dateValid     = 1;
        var venueOpen     = 1;

        var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(booking_time);

        if ( jQuery('#jform_booking_time').is('[readonly]') )
        {
            var isValid = 1;
        }


        if(task !== 'venuebooking.cancel')
        {
            var isAlreadyBooked;

            if (isValid)
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
                                //alert('Please select another date because selected venue closed on your booking date');
                             }
                             else if(parseInt(totalMaleFemale) > parseInt(totalGuest))
                             {
                                alert('Guest Capacity of selected table is : '+ totalGuest);
                             }
                             else
                             {
                                jQuery.ajax({
                                url: 'index.php?option=com_beseated&task=venuebooking.checkForVenueTableAvaibility',
                                type: 'GET',
                                data: 'booking_date='+booking_date+'&booking_time='+booking_time+'&venue_id='+venue_id+'&table_id='+tableID+'&is_day_club='+is_day_club,
                                success: function(response1){
                                     if(parseInt(response1) == 0)
                                     {
                                        alert('Please select another date because selected table already booked on your booking date and time');
                                     }
                                     else
                                     {
                                       Joomla.submitform(task, document.getElementById('adminForm'));
                                     }
                                }
                                });

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

<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=yachtbooking&layout=edit&venue_table_booking_id=' . (int) $this->item->venue_table_booking_id); ?>"
    method="post" name="adminForm" id="adminForm" >
    <div class="form-horizontal">
        <fieldset>
            <div class="span12">
                <legend><?php echo JText::_('COM_BESEATED_VENUE_TABLE_BOOKING_DETAIL'); ?></legend>

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
                     <div class="control-label">Service Name</div>
                       <div class="controls">
                            <select name="service_name" id="service_name" class="service_name_chzn">
                            </select>
                        </div>
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
                      <div class="control-label"><?php echo $this->form->getLabel('bill_post_amount'); ?></div>
                      <div class="controls"><?php echo $this->form->getInput('bill_post_amount'); ?></div>
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


