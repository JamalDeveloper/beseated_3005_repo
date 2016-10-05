<?php
/**
 * @package     Pass.Administrator
 * @subpackage  com_pass
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$input      = JFactory::getApplication()->input;
$serviceID  = $input->getInt('service_id');
$yachtID    = $input->getInt('yacht_id');
$Itemid     = $input->getInt('Itemid');
$unique_code = $input->getstring('unique_code','');


if(isset($_COOKIE['page_load']) && !empty($_COOKIE['page_load']))
{
    $page_load =  $input->cookie->get( 'page_load', null);
    $page_load++;
    $input->cookie->set( 'page_load',  $page_load);
}
else
{
    $input->cookie->set( 'page_load', '1');
}

$page_load =  $input->cookie->get( 'page_load', null);


if($page_load == 1 && $serviceID)
{

    // Initialiase variables.
    $db    = JFactory::getDbo();
    $query = $db->getQuery(true);

    // Create the base select statement.
    $query->select('image_id')
        ->from($db->quoteName('#__beseated_element_images'))
        ->where($db->quoteName('is_default') . ' = ' . $db->quote('1'))
        ->where($db->quoteName('service_id') . ' = ' . $db->quote($serviceID))
        ->where($db->quoteName('element_type') . ' = ' . $db->quote('yacht.service'));

    // Set the query and load the result.
    $db->setQuery($query);

    $yachtSerDefaultImg = $db->loadResult();

    $input->cookie->set( 'yacht_ser_default_img_id',  $yachtSerDefaultImg);
    $input->cookie->set( 'service_id',  $serviceID);
}

?>

<script type="text/javascript">
    jQuery('#file_upload_error_msg').hide();
</script>
<script type="text/javascript">
    jQuery(document).ready(function() 
    {
        jQuery("#jform_price_per_hours").keydown(function (e) {
            // Allow: backspace, delete, tab, escape, enter and . 46, 8, 9, 27, 13, 110, 190
            if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                 // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                 // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                     // let it happen, don't do anything
                     return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
        jQuery("#jform_min_hours").keydown(function (e) {
            // Allow: backspace, delete, tab, escape, enter and . 46, 8, 9, 27, 13, 110, 190
            if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                 // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                 // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                     // let it happen, don't do anything
                     return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
        jQuery("#jform_capacity").keydown(function (e) {
            // Allow: backspace, delete, tab, escape, enter and . 46, 8, 9, 27, 13, 110, 190
            if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                 // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                 // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                     // let it happen, don't do anything
                     return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    });
</script>
<div class="table-wrp edit-tblwrp">
    <div>
        <div id="file_upload_error_msg" class="alert alert-error">Invalid File width Or Height</div>
    </div>
    <div class="inner-guest-wrp">
        <form class="form-horizontal tbl-editfrm" method="post" enctype="multipart/form-data" method="post" accept="<?php echo JRoute::_('index.php?option=com_beseated&view=yachtownerservices'); ?>">
            <div class="span12">
                <h2>Add/Edit Services</h2>
                <div class="row-fluid prof-locatnwrp">
                    <?php
                        $module = JModuleHelper::getModule('mod_serviceslider','mod_serviceslider');
                        echo JModuleHelper::renderModule($module);
                    ?>
                </div>
                
            </div>
            
            <div class="span6">
                <?php echo $this->form->getInput('service_id'); ?>
                <?php echo $this->form->getInput('yacht_id'); ?>
               <div class="control-group">
                    <div class="controls">
                        <?php echo $this->form->getLabel('service_name'); ?>
                        <?php echo $this->form->getInput('service_name'); ?>
                    </div>
                </div>
                 <div class="control-group">
                    <div class="controls">
                        <?php echo $this->form->getLabel('service_type'); ?>
                        <?php echo $this->form->getInput('service_type'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <?php echo $this->form->getLabel('price_per_hours'); ?>
                        <?php echo $this->form->getInput('price_per_hours'); ?>
                    </div>
                </div>
                 <div class="control-group">
                    <div class="controls">
                        <?php echo $this->form->getLabel('min_hours'); ?>
                        <?php echo $this->form->getInput('min_hours'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <?php echo $this->form->getLabel('dock'); ?>
                        <?php echo $this->form->getInput('dock'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <?php echo $this->form->getLabel('capacity'); ?>
                        <?php echo $this->form->getInput('capacity'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <?php echo JHtml::_('form.token'); ?>
                        <button type="submit" class="btn btn-block btn-primary">Save</button>
                        <input type="hidden" id="task" name="task" value="yachtownerserviceedit.save" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">

jQuery(document).ready(function() {
        jQuery('#file_upload_error_msg').hide();
    });

jQuery(function()
{
    var _URL = window.URL || window.webkitURL;
    var service_id  = '<?php echo $serviceID; ?>';
    var yacht_id    = '<?php echo $yachtID; ?>';
    var Itemid    = '<?php echo $Itemid; ?>';
    var unique_code    = '<?php echo $unique_code; ?>';
   
    // Add events
    jQuery('input[type=file]').on('change', fileUpload);

    function fileUpload(event)
    {
        jQuery("#uploading_msg").html("<p>"+event.target.value+" uploading...</p>");
        jQuery('#spinner').fadeIn('fast');
        files = event.target.files;
        var data  = new FormData();
        var error = 0;

        for (var i = 0; i < files.length; i++) 
        {
            var file = files[i];
            image    = new Image();
            image.onload = function()
            {
                if(this.width <= 500 || this.height<=350)
                {
                    document.getElementById("venue_image").value = "";
                    jQuery('#file_upload_error_msg').show();
                    //image dimenssions must be greater than 500px width and 350px height
                    error=1;
                    jQuery('#spinner').stop().fadeOut('fast');
                    return false;
                }
                else
                {
                    jQuery('#file_upload_error_msg').hide();
                    if(!error){
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'index.php?option=com_beseated&task=yachtownerserviceedit.uploadServiceImage&detail_page=1&unique_code='+unique_code+'&yacht_id='+yacht_id+'&service_id='+service_id+'&Itemid='+Itemid, true);
                        xhr.send(data);
                        xhr.onload = function () 
                        {
                            jQuery('#spinner').stop().fadeOut('fast');
                            //location.reload();
                            var redirect_url =  xhr.responseText;

                            window.location.href = redirect_url;
                            
                        };
                    }
                    else
                    {
                        alert('Error'  + error);
                    }
                }
            };

            var ext = jQuery('#venue_image').val().split('.').pop().toLowerCase();

            if (jQuery.inArray(ext, ['mp4','3gp','mov']) != -1)
            {

                jQuery.each(jQuery('#venue_image')[0].files, function(i, file) 
                {
                    data.append('image', file);
                });

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'index.php?option=com_beseated&task=yachtownerserviceedit.uploadServiceImage&detail_page=1&unique_code='+unique_code+'&yacht_id='+yacht_id+'&service_id='+service_id+'&Itemid='+Itemid, true);
                xhr.send(data);
                xhr.onload = function () 
                {
                    jQuery('#spinner').stop().fadeOut('fast');
                    //location.reload();

                    var redirect_url =  xhr.responseText;
                    window.location.href = redirect_url;
                };
            };

            image.src = _URL.createObjectURL(file);
            console.log(file.size);
            console.log("File Pritned fewllow");
            console.log(file.type);

            if(!file.type.match('image.*') && !file.type.match('video.*')) 
            {
                jQuery("#drop-box").html("<p> Images only. Select another file</p>");
                error = 1;
            }
            else
            {
                data.append('image', file, file.name);
            }
        }
    }

});


var _URL = window.URL || window.webkitURL;

jQuery(document).ready(function()
{
    function readURL(input) 
    {
        if (input.files && input.files[0]) 
        {
            var reader = new FileReader();
            console.log(reader);
            reader.onload = function (e) 
            {
                jQuery('#display_venue_image').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    jQuery("#venue_image").change(function()
    {
        var image, file;
        /*var profileType = '<?php echo $this->profile->licence_type; ?>';*/
        var ext = jQuery('#venue_image').val().split('.').pop().toLowerCase();
        if ((file = this.files[0])) 
        {
        }
        readURL(this);
    });

});

</script>


