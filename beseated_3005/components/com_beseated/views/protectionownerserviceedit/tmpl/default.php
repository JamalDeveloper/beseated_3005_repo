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

$input  = JFactory::getApplication()->input;
?>
<script type="text/javascript">
    jQuery('#file_upload_error_msg').hide();
</script>
<script type="text/javascript">
    jQuery(document).ready(function() {
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
    });
</script>
<div class="table-wrp edit-tblwrp">
    <div>
        <div id="file_upload_error_msg" class="alert alert-error">Invalid File width Or Height</div>
    </div>
    <div class="inner-guest-wrp">
        <form class="form-horizontal tbl-editfrm" method="post" enctype="multipart/form-data" method="post" accept="<?php echo JRoute::_('index.php?option=com_beseated&view=protectionservices'); ?>">
            <div class="span12">
                 <h2>Add/Edit Services</h2>
                <div class="control-group">
                    <div class="controls">
                        <?php if(!empty($this->item->image)): ?>
                            <img id="display_venue_table_image" src="<?php echo JUri::root().'images/beseated/'. $this->item->image; ?>" alt="" />
                        <?php else: ?>
                            <img id="display_venue_table_image" src="images/tabl-img.jpg" alt="">
                        <?php endif; ?>
                        <?php echo $this->form->getInput('image'); ?>
                    </div>
                </div>
            </div>
            <div class="span6">
                <?php echo $this->form->getInput('service_id'); ?>
                <?php echo $this->form->getInput('protection_id'); ?>
               <div class="control-group">
                    <div class="controls">
                        <?php echo $this->form->getLabel('service_name'); ?>
                        <?php echo $this->form->getInput('service_name'); ?>
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
                        <?php echo JHtml::_('form.token'); ?>
                        <button type="submit" class="btn btn-block btn-primary">Save</button>
                        <input type="hidden" id="task" name="task" value="protectionownerserviceedit.save" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    jQuery("#jform_image").hide();
    jQuery("#display_venue_table_image").click(function() {
        jQuery("#jform_image").click();
    });

    var _URL = window.URL || window.webkitURL;
    jQuery(document).ready(function(){
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    jQuery('#display_venue_table_image').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }
        jQuery("#jform_image").change(function(){
            var image, file;
            if ((file = this.files[0])) {
                image = new Image();
                image.onload = function() {
                    if(this.width <= 500 || this.height<=350)
                    {
                        document.getElementById("jform_image").value = "";
                        jQuery('#file_upload_error_msg').show();
                        //image dimenssions must be greater than 500px width and 350px height
                        return false;
                    }
                    else
                    {
                        jQuery('#file_upload_error_msg').hide();
                    }
                };
              image.src = _URL.createObjectURL(file);
            }

            readURL(this);
        });
    });

   jQuery(document).ready(function() {
        jQuery('#file_upload_error_msg').hide();
    });
</script>

