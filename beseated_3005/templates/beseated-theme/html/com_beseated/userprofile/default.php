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
$input = JFactory::getApplication()->input;
$ratingData = BeseatedHelper::getUserLastBookingDetail($this->user->id);
?>

<script type="text/javascript">
function checkPasswordMatch() {
    var password = $("#jform_password").val();
    var confirmPassword = $("#jform_password2").val();

    if (password != confirmPassword) {
        $(".passwordMatchInfo").html("Passwords do not match!");
        $("#submit-btn").prop("disabled", true);
    }
    else {
        $(".passwordMatchInfo").html("Passwords match.");
        $("#submit-btn").prop("disabled", false);
    }
}
$(document).ready(function () {
   $("#jform_password2").keyup(checkPasswordMatch);
});
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
    jQuery("#jform_phoneno").focusout(function() {
        var mobno = document.getElementById('jform_phoneno');
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
</script>
<script type="text/javascript">
       function initialize() {
               var input = document.getElementById('jform_city');
               var autocomplete = new google.maps.places.Autocomplete(input);
                google.maps.event.addListener(autocomplete, 'place_changed', function() {
                    var place = autocomplete.getPlace();
                    if (!place.geometry) {
                      return;
                    }
                    jQuery('#jform_latitude').val(place.geometry.location.lat());
                    jQuery('#jform_longitude').val(place.geometry.location.lng());
                });
       }
       google.maps.event.addDomListener(window, 'load', initialize);
</script>

<section class="page-section page-user-profile">
    <div class="container">

        <?php include_once('templates/beseated-theme/partials/guest-profile-menu.php') ?>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <form method="post" action="<?php echo JRoute::_('index.php?option=com_beseated');?>">
                <img src="templates/beseated-theme/images/about-you-icon.png" alt="">
                <h3 class="heading-3">About You</h3>
                    <div class="bordered-box about-you">
                        <div class="field fullname-icon">
                            <?php echo $this->form->getInput('full_name'); ?>
                            <?php echo $this->form->getInput('userid'); ?>
                            <?php echo $this->form->getInput('latitude'); ?>
                            <?php echo $this->form->getInput('longitude'); ?>
                        </div>
                        <div class="field mobile-icon" onkeypress="return numbersonly(event);">
                            <span id="mobilenoid" style="display:none ;margin-top: 10px;color: red;">
                                Invalid Phone number.
                            </span>
                            <?php echo $this->form->getInput('phone'); ?>
                        </div>
                        <div class="field birthdate-icon">
                            <?php echo $this->form->getInput('email'); ?>
                        </div>
                        <div class="field city-icon">
                            <?php echo $this->form->getInput('city'); ?>
                        </div>
                    </div>

                    <img src="templates/beseated-theme/images/password-icon.png" alt="">
                    <h3 class="heading-3">Password</h3>
                    <div class="bordered-box password">
                        <div class="field">
                            <?php echo $this->form->getInput('password'); ?>
                        </div>
                        <div class="field">
                            <?php echo $this->form->getInput('password2'); ?>
                            <div class="passwordMatchInfo"></div>
                        </div>

                    </div>

                    <?php echo JHtml::_('form.token'); ?>
                    <button id="submit-btn" type="submit" class="button">Update Profile</button>
                    <input type="hidden" id="task" name="task" value="userprofile.save">
                </form>
            </div>   
        </div>
    </div>  
</section>

<script>
    $('input').addClass('form-control');
</script>