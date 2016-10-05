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

if(isset($_GET['event_id']) && !empty($_GET['event_id']))
{
    // Initialiase variables.
    $db    = JFactory::getDbo();

    // $db    = $this->getDbo();
    $query = $db->getQuery(true);

    // Create the base select statement.
    $query->select('image')
        ->from($db->quoteName('#__beseated_event'))
        ->where($db->quoteName('event_id') . ' = ' . $db->quote($_GET['event_id']));

    // Set the query and load the result.
    $db->setQuery($query);
    $eventImage = $db->loadResult();

    $isNew = 0;
}
else
{
    $eventImage = "";
    $isNew = 1;
}


if(isset($_COOKIE['page_load']) && !empty($_COOKIE['page_load']))
{
    $page_load = $_COOKIE['page_load'];
}
else
{
  //  echo "<pre/>";print_r("hi");exit;
    $page_load = 0;
}

$document->addScriptDeclaration('
        var eventImage = "' . $eventImage . '";
        var isNew = "' . $isNew . '";
    ');


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
        page_load = <?php echo $page_load+1 ?>

        jQuery.cookie("page_load", page_load);

        var file_data        = jQuery('#jform_image').prop('files')[0];

        var event_name       = jQuery('#jform_event_name').val();
        var event_desc       = jQuery('#jform_event_desc').val();
        var location         = jQuery('#jform_location').val();
        var ticket_price     = jQuery('#jform_price_per_ticket').val();
        var currency_code    = jQuery('#jform_currency_code').val();
        var total_ticket     = jQuery('#jform_total_ticket').val();
        var available_ticket = jQuery('#jform_available_ticket').val();
        var event_date       = jQuery('#jform_event_date').val();
        var event_time       = jQuery('#jform_event_time').val();
        var city             = jQuery('#jform_city').val();
       // var event_id         = jQuery('#jform_event_id').val();
       // var image            = jQuery('#jform_image').val();

        var dateAr           = event_date.split('-');
        var newDate          = dateAr[2] + '-' + dateAr[1] + '-' + dateAr[0];

        if(page_load == 1)
        {
            jQuery.cookie("event_name", event_name);
            jQuery.cookie("event_desc", event_desc);
            jQuery.cookie("location", location);
            jQuery.cookie("ticket_price", ticket_price);
            jQuery.cookie("currency_code", currency_code);
            jQuery.cookie("total_ticket", total_ticket);
            jQuery.cookie("available_ticket", available_ticket);
            jQuery.cookie("event_date", event_date);
            jQuery.cookie("event_time", event_time);
            jQuery.cookie("city", city);
            jQuery.cookie("eventImage", eventImage);
            jQuery.cookie("isNew", isNew);
       }

        var input = document.getElementById('jform_location');
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            if (!place.geometry) {
              return;
            }

            city_name    = '';
            country_name = '';

            for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
                if (addressType == 'locality') {
                    city_name = place.address_components[i]['long_name'];
                }
                if (addressType == 'country') {
                    country_name = place.address_components[i]['long_name'];
                }
            }

            jQuery('#jform_city').val(city_name);
            //jQuery('#jform_only_country').val(country_name);
            jQuery('#jform_latitude').val(place.geometry.location.lat());
            jQuery('#jform_longitude').val(place.geometry.location.lng());
        });



    }

    function addTicket_ajax()
    {
       var file_data        = jQuery('#jform_image').prop('files')[0];

       var form_data1 = new FormData();
       form_data1.append("file", file_data);


        var event_name       = jQuery('#jform_event_name').val();
        var event_desc       = jQuery('#jform_event_desc').val();
        var location         = jQuery('#jform_location').val();
        var ticket_price     = jQuery('#jform_price_per_ticket').val();
        var currency_code    = jQuery('#jform_currency_code').val();
        var total_ticket     = jQuery('#jform_total_ticket').val();
        var available_ticket = jQuery('#jform_available_ticket').val();
        var event_date       = jQuery('#jform_event_date').val();
        var event_time       = jQuery('#jform_event_time').val();
        var city             = jQuery('#jform_city').val();
        var event_id         = jQuery('#jform_event_id').val();
        var dateAr           = event_date.split('-');
        var newDate          = dateAr[2] + '-' + dateAr[1] + '-' + dateAr[0];

        var form_data = {};
        form_data["event_name"]       = event_name;
        form_data["event_desc"]       = event_desc;
        form_data["location"]         = location;
        form_data["ticket_price"]     = ticket_price;
        form_data["currency_code"]    = currency_code;
        form_data["total_ticket"]     = total_ticket;
        form_data["available_ticket"] = available_ticket;
        form_data["event_date"]       = newDate;
        form_data["event_time"]       = event_time;
        form_data["city"]             = city;
        form_data["event_id"]         = event_id;


         jQuery.ajax({
                    url: 'index.php?option=com_beseated&task=event.ajax_event_save', // point to server-side PHP script
                    type: 'POST',
                     data:  {form_data : form_data } ,
                    /*contentType: false,       // The content type used when sending data to the server.
                    cache: false,             // To unable request pages to be cached
                    processData:false,*/
                    dataType : 'json',
                    success: function(response){
                        //alert(response); // display response from the PHP script, if any
                        if(response != "0"){
                            jQuery('#jform_event_id').val(response);
                            jQuery('#unique_code').val(response);
                            jQuery('#event_id').val(response);

                             jQuery.ajax({
                                    url: 'index.php?option=com_beseated&task=event.uploadEventImage&event_id='+response, // point to server-side PHP script
                                    type: 'POST',
                                    data:  form_data1,
                                    contentType: false,       // The content type used when sending data to the server.
                                   // cache: false,             // To unable request pages to be cached
                                    processData:false,
                                    //dataType : 'json',
                                    success: function(response){
                                        //alert(response); // display response from the PHP script, if any
                                        if(response == "2"){
                                            alert("invalid type of image");
                                        }
                                        else if(response == "3")
                                        {
                                           alert("error in file uploading");
                                        }
                                    }
                                });

                        }
                    }
                });

    }

     jQuery(document).ready(function() {
        jQuery("#jform_price_per_ticket").keydown(function (e) {

            // Allow: backspace, delete, tab, escape, enter and .
            if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                 // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                 // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
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
    });

    Joomla.submitbutton = function(task)
    {
        var event_time       = jQuery('#jform_event_time').val();
        var event_date       = jQuery('#jform_event_date').val();

        var arrDate = event_date.split("-");

        var today = new Date();
        useDate = new Date(arrDate[2], arrDate[1] - 1, arrDate[0]);

       // alert(useDate);

        var fullDate      = new Date();


        var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(event_time);

        if(task != 'event.cancel')
        {
            if (isValid)
            {

            	if (useDate < fullDate)
		        {
		            alert("Date must be in the future");
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

<button id="test" data-toggle="modal" href="#myModal" style="float:right; margin-bottom:15px;" onclick="addTicket_ajax();">
    Add Tickets
</button>

<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=event&layout=edit&event_id=' . (int) $this->item->event_id); ?>"
    method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
    <div class="form-horizontal">
        <fieldset>
            <div class="span12">
                <legend><?php echo JText::_('COM_BESEATED_EVENT_DETAIL'); ?></legend>

                <div class="span8">
                    <div class="control-group" id="event_name">
                        <div class="control-label"><?php echo $this->form->getLabel('event_name'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('event_name'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('published'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('city'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('event_id'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('event_desc'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('event_desc'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('location'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('location'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('price_per_ticket'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('price_per_ticket'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('currency_code'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('currency_code'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('image'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('total_ticket'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('total_ticket'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('available_ticket'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('available_ticket'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('event_date'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('event_date'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('event_time'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('event_time'); ?></div>
                    </div>
                </div>
                <div class="span4">
                    <div style="position:relative;">

                    <?php
                    foreach ($this->eventTickets as $key => $Tickets)
                    {
                        if( $key%3 == 0 )
                        {
                            $left = 2;
                        }
                        else
                        {
                             $left += 110;
                        }
                    ?>
                        <img src="<?php echo JURI::Root().'images/beseated/'.$Tickets->image ?>"  style="margin:3px;" width="100px" height="100px">

                        <?php if($Tickets->ticket_booking_id)
                        {?>
                             <img src="<?php echo JURI::Root().'images/beseated/Ticket/select_img.png'; ?>" style="position:absolute;left:<?php echo $left; ?>px" />
                        <?php
                        }
                    }
                    ?>
                    </div>
                </div>


            </div>
            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="unique_code" value="<?php echo $this->uniqueCode; ?>" />

</form>



<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class='span12'>
            <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>

                    <form action="<?php echo JUri::base(); ?>index.php?option=com_beseated&amp;task=event.upload_images&amp;tmpl=component; ?>" id="uploadForm" class="form-horizontal" name="uploadForm" method="post" enctype="multipart/form-data">
                        <div id="uploadform" class="well">
                            <fieldset id="upload-noflash" class="actions">
                                <div class="control-group">
                                    <div class="control-label">
                                        <label for="upload-file" class="control-label">Select Image</label>
                                    </div>
                                    <div class="controls">
                                        <input type="file" id="upload_file" name="Filedata[]" multiple /><button class="btn btn-primary" id="upload-submit" ><i class="icon-upload icon-white"></i>Upload Images</button>
                                    </div>
                                </div>
                            </fieldset>
                                    <input type="hidden" name="return-url" value="<?php echo base64_encode('index.php?option=com_beseated&amp;view=event&amp;layout=modal&amp;tmpl=component'); ?>" />
                                    <input type="hidden" id="unique_code" name="unique_code" value="<?php echo $this->uniqueCode; ?>" />
                                    <input type="hidden" id="event_id" name="event_id" value="<?php echo $this->item->event_id; ?>" />
                        </div>
                    </form>
            </div>
    </div>
</div>


