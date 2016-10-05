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
?>
<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places" type="text/javascript"></script>
<script type="text/javascript">
    function initialize() {
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
            //alert(city_name);

            jQuery('#jform_city').val(city_name);
            //jQuery('#jform_only_country').val(country_name);
            jQuery('#jform_latitude').val(place.geometry.location.lat());
            jQuery('#jform_longitude').val(place.geometry.location.lng());
        });
    }
    google.maps.event.addDomListener(window, 'load', initialize);
</script>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#jform_deposit_per,#jform_refund_policy").keydown(function (e) {

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
</script>
<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=yacht&layout=edit&yacht_id=' . (int) $this->item->yacht_id); ?>"
    method="post" name="adminForm" id="adminForm">
    <div class="form-horizontal">
        <fieldset>
            <div class="span12">
                <legend><?php echo JText::_('COM_BESEATED_YACHT_MANAGER'); ?></legend>
                <div class="control-group">
                    <div class="controls"><?php echo $this->form->getInput('yacht_id'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('city'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('latitude'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('longitude'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('currency_sign'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('yacht_name'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('yacht_name'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('location'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('location'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('currency_code'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('currency_code'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('deposit_per'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('deposit_per'); ?></div>
                </div>
                 <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('refund_policy'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('refund_policy'); ?></div>
                </div>
            </div>
            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />

</form>
