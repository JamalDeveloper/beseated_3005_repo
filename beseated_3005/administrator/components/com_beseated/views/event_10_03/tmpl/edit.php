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

//echo "<pre/>";print_r($this->ticketsTypeZone);exit;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHTML::_('behavior.calendar');

$count1 = (count($this->ticketsTypeZone)) ? count($this->ticketsTypeZone) : '1';

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
<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;key=AIzaSyAKf4wfPXxaajH5eB3mH4Bsc7G-jSAhKKI&amp;libraries=places" type="text/javascript"></script>
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
        var latitude         = jQuery('#jform_latitude').val();
        var longitude        = jQuery('#jform_longitude').val();
       // var event_id         = jQuery('#jform_event_id').val();
       // var image            = jQuery('#jform_image').val();

        var ticket_price        = document.getElementsByName('ticket_price[]');
        var ticket_zone         = document.getElementsByName('ticket_zone[]');
        var ticket_type         = document.getElementsByName('ticket_type[]');
        var ticket_type_zone_id = document.getElementsByName('ticket_type_zone_id[]');
        var total_tickets       = document.getElementsByName('total_tickets[]');
        var available_tickets   = document.getElementsByName('available_tickets[]');

       var sources = jQuery(".filedata").map(function() {
            return this.getAttribute('value');
        }).get();

       sources = jQuery.makeArray(sources);

        //console.log(sources[0]);

        var typezoneArr = [];

        for (i1=0; i1<ticket_zone.length; i1++)
        {
            var data = {};

            for (i2=0; i2<ticket_type.length; i2++)
            {
                for (i3=0; i3<ticket_price.length; i3++)
                {
                    for (i4=0; i4<ticket_type_zone_id.length; i4++)
                    {
                        data.ticket_zone         = ticket_zone[i1].value;
                        data.ticket_type         = ticket_type[i1].value;
                        data.ticket_price        = ticket_price[i1].value;
                        data.ticket_type_zone_id = ticket_type_zone_id[i1].value;
                        data.ticket_type_image   = sources[i1];
                        data.total_tickets       = total_tickets[i1].value;
                        data.available_tickets   = available_tickets[i1].value;
                    }
                }
            }

            typezoneArr.push(data);
        }

        //console.log(typezoneArr);


        var dateAr           = event_date.split('-');
        var newDate          = dateAr[2] + '-' + dateAr[1] + '-' + dateAr[0];

        if(page_load == 1)
        {
            var tickettypezoneArr = JSON.stringify(typezoneArr);
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
            jQuery.cookie("latitude",latitude);
            jQuery.cookie("longitude",longitude);
            jQuery.cookie("eventImage", eventImage);
            jQuery.cookie("isNew", isNew);
            jQuery.cookie("tickettypezone", tickettypezoneArr.toString());
            jQuery.cookie("filedata", sources);
            //alert("fgfg");
       }

        var input = document.getElementById('jform_location');
        var autocomplete = new google.maps.places.Autocomplete(input);

        google.maps.event.addListener(autocomplete, 'place_changed', function()
        {
        	var place = autocomplete.getPlace();

            if (!place.geometry)
            {
              return;
            }

            city_name    = '';
            country_name = '';

            for (var i = 0; i < place.address_components.length; i++)
            {
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
        var ticket_price        = document.getElementsByName('ticket_price[]');
        var ticket_zone         = document.getElementsByName('ticket_zone[]');
        var ticket_type         = document.getElementsByName('ticket_type[]');
        var ticket_type_zone_id = document.getElementsByName('ticket_type_zone_id[]');
        var total_tickets       = document.getElementsByName('total_tickets[]');
        var available_tickets   = document.getElementsByName('available_tickets[]');

        var file_image = [];

        var ticket_type_image_data = new FormData();
        var emptydata = new FormData();

        jQuery('.filedata').each(function(index)
        {
            var value = this.id;
            var ticket_type_image = jQuery(jQuery('.filedata')[index]).prop('files')[0];

            var content = '<a id="a"><b id="b">hey!</b></a>'; // the body of the new file...
            var emptyFile    = new Blob([content], { type: "text/xml"});

            if(typeof ticket_type_image == "undefined") 
            {
                //alert("if");
                ticket_type_image_data.append('file_image[undefined_'+index+']', emptyFile);
            }
            else
            {   
                //console.log(test);
                ticket_type_image_data.append('file_image['+value+']', ticket_type_image);
                //oldimage = jQuery(jQuery('.filedata')[index]).prop('files')[0];
            }
            
        });


        var typezoneArr = [];

        for (i1=0; i1<ticket_zone.length; i1++)
        {
            var data = {};

            for (i2=0; i2<ticket_type.length; i2++)
            {
                for (i3=0; i3<ticket_price.length; i3++)
                {
                    for (i4=0; i4<ticket_type_zone_id.length; i4++)
                    {
                        data.ticket_zone         = ticket_zone[i1].value;
                        data.ticket_type         = ticket_type[i1].value;
                        data.ticket_price        = ticket_price[i1].value;
                        data.ticket_type_zone_id = ticket_type_zone_id[i1].value;
                        data.total_tickets       = total_tickets[i1].value;
                        data.available_tickets   = available_tickets[i1].value;
                    }
                }
            }

            typezoneArr.push(data);
        }

        //console.log(typezoneArr);

        var file_data   = jQuery('#jform_image').prop('files')[0];
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
        var latitude         = jQuery('#jform_latitude').val();
        var longitude        = jQuery('#jform_longitude').val();
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
        form_data["latitude"]         = latitude;
        form_data["longitude"]        = longitude;

        jQuery.ajax(
        {
                url: 'index.php?option=com_beseated&task=event.ajax_event_save', // point to server-side PHP script
                type: 'POST',
                 data:  {form_data : form_data} ,
                /*contentType: false,       // The content type used when sending data to the server.
                cache: false,             // To unable request pages to be cached
                processData:false,*/
                dataType : 'json',
                success: function(response)
                {
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
                                success: function(response1)
                                {
                                    //alert(response); // display response from the PHP script, if any
                                    if(response1 == "2"){
                                        alert("invalid type of image");
                                    }
                                    else if(response1 == "3")
                                    {
                                       alert("error in file uploading");
                                    }
                                    else
                                    {
                                        jQuery.ajax({
                                        url: 'index.php?option=com_beseated&task=event.saveTicketTypeZone&event_id='+response, // point to server-side PHP script
                                        type: 'POST',
                                        data:  { ticket_types : typezoneArr} ,
                                        //dataType : 'json',      // The content type used when sending data to the server.
                                       // cache: false,             // To unable request pages to be cached
                                        //processData:false,
                                        //dataType : 'json',
                                        success: function(response1)
                                        {
                                            jQuery('#main').html(response1);
                                         
                                            jQuery.ajax({
                                            url: 'index.php?option=com_beseated&task=event.uploadTicketTypeImage&event_id='+response, // point to server-side PHP script
                                            type: 'POST',
                                            data:  ticket_type_image_data,
                                            contentType: false,       
                                            processData:false,
                                            success: function(response1)
                                            {
                                                 if(response1 == "2")
                                                 {
                                                    alert("invalid type of image");
                                                 }
                                                 else if(response1 == "3")
                                                 {
                                                    alert("error in file uploading");
                                                 }
                                                 else
                                                 {
                                                    jQuery('#uploadTickets').empty().append(response1);
                                                    jQuery('#myModal').modal('show');
                                                 }
                                            }
                                            });
                                        }
                                     });
                                    }
                                }
                            });
                    }
                }
            });
    	}

     jQuery(document).ready(function() 
     {

        jQuery("#ticket_price").keydown(function (e) {

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

    

     

    jQuery(document).ready(function()
    {
        jQuery("#addTicketTypeZone").click(function()
        {
            var cntDiv = jQuery('#countDiv').val();
            cntDiv = parseInt(cntDiv) + 1;

            var x = 666; // can be any number
            var rand = Math.floor(Math.random()*x) + 1;

            var html =  '<div id="container' + rand + '"><input type="button" name="container' + rand + '" id="rgm" class="btn btn-danger" style="float:right;margin-right:1px;" value="Remove">';
                html += '<div class="control-group">';
                html += '<div class="controls1">';
                html += '<input type="text" name="ticket_type[]"  placeHolder="Enter Ticket Type" required>&nbsp;';
                html += '<input type="text" name="ticket_zone[]"  placeHolder="Enter Zone" required>&nbsp;';
                html += '<input type="text" name="ticket_price[]" placeHolder="Enter Price" required>';
                html += '&nbsp;<input type="text" name="total_tickets[]" id="total_tickets" value="0" disabled>';
                html += '&nbsp;<input type="text" name="available_tickets[]" id="available_tickets" value="0" disabled>';
                html += '&nbsp;<input class="filedata" type="file" name="filedata[]" value="" />';
                html += '<input type="hidden" name="ticket_type_zone_id[]"  value="">';
                html += '</div>';
                html += '</div>';
                html += '</div>';
                jQuery("#main").append(html);
                jQuery("#countDiv").val(cntDiv);
        });

        jQuery(document).on('click','#rgm',function()
        {
           var removedivId = jQuery(this).parent().attr('id');
           var removedivId1 =  'div#' +removedivId;

           var removedivId2 = jQuery(this).attr('name');

           var countVal = jQuery("#countDiv").val();

            if(countVal > 1)
            {
                jQuery('div#' +removedivId2).remove();
                var cntDiv = countVal-1;
                jQuery("#countDiv").val(cntDiv);
            }

        });

       jQuery(document).on('click','#rmv',function()
       {
            var countVal = jQuery("#countDiv").val();

            var removedivId = jQuery(this).parent().attr('id');

            if(parseInt(countVal) > 1)
            {
                jQuery('#'+removedivId).remove();
                var cntDiv = countVal-1;
                jQuery("#countDiv").val(cntDiv);
            }
       });
    });


    Joomla.submitbutton = function(task)
    {
        var event_time       = jQuery('#jform_event_time').val();
        var event_date       = jQuery('#jform_event_date').val();

        var ticket_price = document.getElementsByName('ticket_price[]');
        var ticket_zone = document.getElementsByName('ticket_zone[]');
        var ticket_type = document.getElementsByName('ticket_type[]');

        var emptyTicketPrice = 0;
        var emptyTicketTypeZone = 0;

        for (i=0; i<ticket_price.length; i++)
        {
           if(ticket_price[i].value == "")
            {
                emptyTicketPrice = 1
            }
        }

        for (i1=0; i1<ticket_zone.length; i1++)
        {
            for (i2=0; i2<ticket_type.length; i2++)
            {
                if(ticket_zone[i1].value == "" && ticket_type[i1].value == "")
                {
                    emptyTicketTypeZone = 1;
                }
            }
        }

        var arrDate = event_date.split("-");
        var arrTime = event_time.split(":");

        var today = new Date();
        useDate = new Date(arrDate[2], arrDate[1] - 1, arrDate[0],arrTime[0],arrTime[1]);

        var fullDate      = new Date();


        var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(event_time);

        if(task != 'event.cancel')
        {
            if (isValid)
            {
            	if (useDate < fullDate)
		        {
		            alert("Date and Time must be in the current or future");
		        }
                else if(parseInt(emptyTicketPrice) == 1)
                {
                    alert("Please Enter Ticket Price");
                }
                else if(parseInt(emptyTicketTypeZone) == 1)
                {
                    alert("Please Enter Ticket Type Or Zone");
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

    jQuery(document).on('click','#test',function(e)
    {
        var ticket_price = document.getElementsByName('ticket_price[]');
        var ticket_zone = document.getElementsByName('ticket_zone[]');
        var ticket_type = document.getElementsByName('ticket_type[]');

        var emptyTicketPrice = 0;
        var emptyTicketTypeZone = 0;

        for (i=0; i<ticket_price.length; i++)
        {
           if(ticket_price[i].value == "")
            {
                emptyTicketPrice = 1
            }
        }

        for (i1=0; i1<ticket_zone.length; i1++)
        {
            for (i2=0; i2<ticket_type.length; i2++)
            {
                if(ticket_zone[i1].value == "" && ticket_type[i1].value == "")
                {
                    emptyTicketTypeZone = 1;
                }
            }
        }
        // uncomment
        if(parseInt(emptyTicketPrice) == 1)
        {
            alert("Please Enter Ticket Price");
        }
        else if(parseInt(emptyTicketTypeZone) == 1)
        {
            alert("Please Enter Ticket Type Or Zone");
        }
        else
        {
            setTimeout(function()
            {
                  jQuery("#addTicketImages").trigger('click');
            },5000);
        }

       // jQuery("#addTicketImages").trigger('click');

    });

    google.maps.event.addDomListener(window, 'load', initialize);
</script>

<button id="test" style="float:right; margin-bottom:15px;" onclick="addTicket_ajax();">
    Add Tickets
</button>

<!-- <button id="addTicketImages" data-toggle="modal" href="#myModal" style="float:right; margin-bottom:15px;display:none;">
</button> -->

<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=event&layout=edit&event_id=' . (int) $this->item->event_id); ?>"
    method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
    <div class="form-horizontal">

    	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_BESEATED_EVENT_DETAIL', true)); ?>
	        <fieldset>
	            <div class="span12">
	                <legend><?php echo JText::_('COM_BESEATED_EVENT_DETAIL'); ?></legend>

	                <div class="span8">
	                    <div class="control-group" id="event_name">
	                        <div class="control-label"><?php echo $this->form->getLabel('event_name'); ?></div>
	                        <div class="controls"><?php echo $this->form->getInput('event_name'); ?></div>
	                        <div class="controls"><?php echo $this->form->getInput('published'); ?></div>
	                        <div class="controls"><?php echo $this->form->getInput('city'); ?></div>
	                        <div class="controls"><?php echo $this->form->getInput('latitude'); ?></div>
	                        <div class="controls"><?php echo $this->form->getInput('longitude'); ?></div>
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
	                    <!-- <div class="control-group">
	                        <div class="control-label"><?php echo $this->form->getLabel('price_per_ticket'); ?></div>
	                        <div class="controls"><?php echo $this->form->getInput('price_per_ticket'); ?></div>
	                    </div> -->
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

                    <!-- for ticket img display-->
	                <!--<div class="span4">
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
	                </div>-->


	            </div>
	            <?php echo JHtml::_('form.token'); ?>
	        </fieldset>

	    <?php echo JHtml::_('bootstrap.endTab');?>
            <?php echo JHtml::_('bootstrap.addTab','myTab','Item1',JText::_('Add Tickets Type Zone', true)); ?>
            <div class="item_lef">
                   <div id="main">
                        <?php if(empty($this->ticketsTypeZone)): ?>
                          <div id="container">
                             <input type="button" id="rmv" class="btn btn-danger" style="float:right;margin-right:1px;" value="Remove">
                                  <div class="control-group">
                                     <div class="controls1">
                                         <input type="text" name="ticket_type[]"   id="ticket_type" placeHolder="Enter Ticket Type" required><input type="hidden" id="rgm">
                                         <input type="text" name="ticket_zone[]"   id="ticket_zone" placeHolder="Enter Zone" required>
                                         <input type="text" name="ticket_price[]"  id="ticket_price" placeHolder="Enter Price" id="price_per_ticket" required>
                                         <input type="text" name="total_tickets[]" id="total_tickets"  value="0" disabled>
                                         <input type="text" name="available_tickets[]" id="available_tickets" value="0" disabled>
                                         <input class="filedata" type="file" name="filedata[]"/>
                                         <input type="hidden" name="ticket_type_zone_id[]" value="">
                                     </div>
                                  </div>
                          </div>
                        <?php else: ?>
                            <?php foreach ($this->ticketsTypeZone as $key => $ticket) : ?>
                               <div id="container<?php echo $ticket->ticket_type_zone_id; ?>">
                                    <input type="button" id="rmv" class="btn btn-danger" style="float:right;margin-right:1px;" value="Remove">
                                    <div class="control-group">
                                     <div class="controls1">
                                         <input type="text" name="ticket_type[]" id="ticket_type" value="<?php echo $ticket->ticket_type; ?>" placeHolder="Enter Ticket Type" required><input type="hidden" id="rgm">
                                         <input type="text" name="ticket_zone[]" id="ticket_zone" value="<?php echo $ticket->ticket_zone; ?>" placeHolder="Enter Zone" required>
                                         <input type="text" name="ticket_price[]" id="ticket_price" value="<?php echo $ticket->ticket_price; ?>" placeHolder="Enter Price" id="price_per_ticket" required>
                                         <input type="text" name="total_tickets[]" id="total_tickets" value="<?php echo $ticket->total_tickets; ?>" disabled>
                                         <input type="text" name="available_tickets[]" id="available_tickets" value="<?php echo $ticket->available_tickets; ?>" disabled>
                                         <input class="filedata" type="file" id="<?php echo $ticket->ticket_type_zone_id; ?>" value="<?php echo $ticket->ticket_type_image; ?>" name="filedata[]" />
                                         <input type="hidden" name="ticket_type_zone_id[]" value="<?php echo $ticket->ticket_type_zone_id; ?>">
                                     </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                   </div>
            </div>
            <div>
               <button type="button" id="addTicketTypeZone" class="btn btn-success" style="float:left">Add More</button>
            </div>
            <input type="hidden" id="countDiv" value="<?php echo $count1; ?>">
          <?php echo JHtml::_('bootstrap.endTab'); ?>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="unique_code" value="<?php echo $this->uniqueCode; ?>" />

</form>



<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class='span12'>
        <div class="modal-header">
        Note :  You should be upload only jpg,jpeg or png file
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>

                <form action="<?php echo JUri::base(); ?>index.php?option=com_beseated&amp;task=event.upload_images&amp;tmpl=component; ?>" id="uploadForm" class="form-horizontal" name="uploadForm" method="post" enctype="multipart/form-data">
                    <div id="uploadform" class="well">
                        <fieldset id="upload-noflash" class="actions">
                            <div class="control-group">
                                <div class="control-label">
                                    <label for="upload-file" class="control-label">Select Ticket Type</label>
                                    <select name="uploadTickets" id="uploadTickets">
                                        <?php foreach ($this->ticketsTypeZone as $key => $ticket) : ?>
                                            <?php if($ticket->ticket_type && $ticket->ticket_zone && $ticket->ticket_price): ?>
                                                <option value="<?php echo $ticket->ticket_type_zone_id; ?>"><?php echo $ticket->ticket_type.'-'.$ticket->ticket_zone.'-'.$ticket->ticket_price; ?></option>
                                            <?php elseif($ticket->ticket_type && $ticket->ticket_price): ?>
                                                <option value="<?php echo $ticket->ticket_type_zone_id; ?>"><?php echo $ticket->ticket_type.'-'.$ticket->ticket_price; ?></option>
                                            <?php elseif($ticket->ticket_zone && $ticket->ticket_price): ?>
                                                <option value="<?php echo $ticket->ticket_type_zone_id; ?>"><?php echo $ticket->ticket_zone.'-'.$ticket->ticket_price; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
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


