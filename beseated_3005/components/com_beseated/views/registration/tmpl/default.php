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
$input  = JFactory::getApplication()->input;
$lib_id =$input->get('library_id',0,'int');
?>
<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places" type="text/javascript"></script>
<script type="text/javascript">
function numbersonly(events){
    var unicodes=events.charCode? events.charCode :events.keyCode;
    if (unicodes!=8)
    {
        if( ((unicodes>47 && unicodes<58) || unicodes == 43 || unicodes == 46 || unicodes == 9 )){
            return true;
        }else{
            return false;
        }
    }
}

jQuery(document).ready(function()
{
    jQuery("#jform_mobile").focusout(function() {
        var mobno = document.getElementById('jform_mobile');
        if (mobno.value.length <10)
        {
            jQuery('#mobilenoid').css('display', 'block');
            jQuery('#mobilenoid').addClass(' invalid');

            return true;
        }
        else
        {
            jQuery('#mobilenoid').css('display', 'none');
            return false;
        }
    });
});


function initialize() {
    var input = document.getElementById('jform_city');
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

        jQuery('#jform_only_city').val(city_name);
        jQuery('#jform_only_country').val(country_name);
        jQuery('#jform_latitude').val(place.geometry.location.lat());
        jQuery('#jform_longitude').val(place.geometry.location.lng());
    });
}
google.maps.event.addDomListener(window, 'load', initialize);
</script>
<div class="guest-club-wrp register-wrp">
    <div class="inner-guest-wrp">
        <form class="form-horizontal" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated');?>">
            <div class="control-group">
                <label class="control-label span5"><?php echo $this->form->getLabel('first_name'); ?></label>
                <div class="controls span7">
                    <?php echo $this->form->getInput('first_name'); ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label span5"><?php echo $this->form->getLabel('city'); ?></label>
                <div class="controls span7">
                    <?php echo $this->form->getInput('city'); ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label span5"><?php echo $this->form->getLabel('email'); ?></label>
                <div class="controls span7">
                    <?php echo $this->form->getInput('email'); ?>
                </div>
            </div>
            <div class="control-group">
                <span id="mobilenoid" style="display:none ;margin-top: 10px;color: red;">
                    Invalid Phone number.
                </span>
            </div>
            <div class="control-group">
                <label class="control-label span5"><?php echo $this->form->getLabel('mobile'); ?></label>

                <div class="controls span7" onkeypress="return numbersonly(event);">
                    <?php echo $this->form->getInput('mobile'); ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label span5"><?php echo $this->form->getLabel('birthdate'); ?></label>

                <div class="controls span7" onkeypress="return numbersonly(event);">
                    <?php echo $this->form->getInput('birthdate'); ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label span5"><?php echo $this->form->getLabel('password'); ?></label>
                <div class="controls span7">
                    <?php echo $this->form->getInput('password'); ?>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label span5"><?php echo $this->form->getLabel('password2'); ?></label>
                <div class="controls span7">
                    <?php echo $this->form->getInput('password2'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label span5"></div>
                <div class="controls span7">
                <?php echo JHtml::_('form.token'); ?>
                    <button type="submit" class="btn btn-block btn-primary"><?php echo JText::_('COM_BCTED_CLUB_REGISTRATION_REGISTRATION_BUTTON'); ?></button>
                    <input type="hidden" id="task" name="task" value="registration.save">
                </div>
            </div>
        </form>
    </div>
</div>
