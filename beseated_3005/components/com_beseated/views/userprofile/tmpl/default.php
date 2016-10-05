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

$task = $input->getstring('task');

$userDetail = BeseatedHelper::guestUserDetail($this->user->id);
$ratingData = BeseatedHelper::getUserLastBookingDetail($this->user->id);

$user = JFactory::getUser();
$userid = $user->id;

?>

<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

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
    jQuery("#update_password_div").hide();

    var userid = '<?php echo $userid; ?>';
    var task = '<?php echo $task; ?>';


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

    jQuery.ajax({
        url: 'index.php?option=com_beseated&task=userprofile.checkUserBigspender',
        type: 'POST',
        data: 'user_id='+userid,

        success: function(response){

            if(response == "1")
            {
                jQuery('#toggle-demo').bootstrapToggle('on');
            }
            else
            {
                jQuery('#toggle-demo').bootstrapToggle('off');
            }
        }
    })
    .done(function() {
    })
    .fail(function() {
    })
    .always(function() {
    });

    jQuery.ajax({
        url: 'index.php?option=com_beseated&task=userprofile.checkShowFriendsOnly',
        type: 'POST',
        data: 'user_id='+userid,

        success: function(response){

            if(response == "1")
            {
                jQuery('#toggle-demo1').bootstrapToggle('on');
            }
            else
            {
                jQuery('#toggle-demo1').bootstrapToggle('off');
            }
        }
    })
    .done(function() {
    })
    .fail(function() {
    })
    .always(function() {
    });

   jQuery('#toggle-demo2').change(function() 
   {
       if(this.checked == true)
       {
            jQuery("#update_password_div").show();
       }
       else
       {
            jQuery("#update_password_div").hide();
       }
    });

   if(task === 'fb_login')
   {
        jQuery("#change_password").hide();
   }
   else
   {
        jQuery("#change_password").show();
   }
      
});
</script>
<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places&amp;key=AIzaSyAKf4wfPXxaajH5eB3mH4Bsc7G-jSAhKKI" type="text/javascript"></script>
<script type="text/javascript">
       function initialize() 
       {
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


<div class="guest-club-wrp register-wrp userprofile">

    <div class="inner-guest-wrp">
        <form class="form-horizontal" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated');?>">
            <div class="control-group">
                <label class="control-label span5"><?php echo $this->form->getLabel('full_name'); ?></label>
                <div class="controls span7">
                    <?php echo $this->form->getInput('full_name'); ?>
                    <?php echo $this->form->getInput('userid'); ?>
                    <?php echo $this->form->getInput('latitude'); ?>
                    <?php echo $this->form->getInput('longitude'); ?>
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
                <label class="control-label span5"><?php echo $this->form->getLabel('phone'); ?></label>
                <div class="controls span7" onkeypress="return numbersonly(event);">
                    <?php echo $this->form->getInput('phone'); ?>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label span5"><?php echo $this->form->getLabel('birthdate'); ?></label>

                <div class="controls span7" onkeypress="return numbersonly(event);">
                    <?php echo $this->form->getInput('birthdate'); ?>
                </div>
            </div>

            <div class="control-group">
                <label class="control-label span5"><?php echo JText::_('COM_BCTED_PROFILE_USER_BIGGEST_SPENDER_LABEL'); ?></label>
                <div class="controls span7">
                    <input id="toggle-demo" type="checkbox" data-toggle="toggle" name="bigspender">
                </div>
            </div>

            <div class="control-group">
                <label class="control-label span5"><?php echo JText::_('COM_BCTED_PROFILE_USER_SHOW_FRIENDS_ONLY_LABEL'); ?></label>
                <div class="controls span7">
                    <input id="toggle-demo1" type="checkbox" data-toggle="toggle" name="showfriends">
                </div>
            </div>

            <?php if($userDetail->is_fb_user == 0) : ?>
            <div class="control-group" id="change_password">
                <label class="control-label span5"><?php echo JText::_('COM_BCTED_PROFILE_USER_UPDATE_PASSWORD_LABEL'); ?></label>
                <div class="controls span7">
                    <input id="toggle-demo2" type="checkbox" data-toggle="toggle" name="changepwd">
                </div>
            </div>
            <?php endif; ?>

            <div id="update_password_div">
                <div class="control-group">
                    <label class="control-label span5"><?php echo $this->form->getLabel('oldpassword'); ?></label>
                    <div class="controls span7">
                        <?php echo $this->form->getInput('oldpassword'); ?>
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

            </div>

            <div class="control-group">
                <div class="control-label span5"></div>
                <div class="controls span7">
                <?php echo JHtml::_('form.token'); ?>
                    <button type="submit" class="btn btn-block btn-primary">Update Profile</button>
                    <input type="hidden" id="task" name="task" value="userprofile.save">
                </div>
            </div>
            
        </form>
    </div>
</div>

<?php
$venueRating   = count($ratingData['venues']);
$companyRating = count($ratingData['service']);
if($venueRating)
{
    ?>
    <script type="text/javascript">
        jQuery(window).load(function(){
            jQuery('#myModal').modal('show');
        });

        jQuery(document).ready(function() {
            jQuery('#myModal').modal({
                    keyboard: false,
                    backdrop:true,
                    backdrop: 'static'
                });
            jQuery('#myModalLabel').text("Rate for <?php echo $ratingData['venues'][0]['venueName']; ?>");
            jQuery('#elementType').val('venue');
            jQuery('#bookingID').val("<?php echo $ratingData['venues'][0]['bookingID']; ?>");
            jQuery('#elementID').val("<?php echo $ratingData['venues'][0]['venueID']; ?>");
        });
    </script>
    <?php
}
else if($companyRating)
{
   ?>
    <script type="text/javascript">
        jQuery(window).load(function(){
            jQuery('#myModal').modal('show');
        });
        jQuery(document).ready(function() {
            jQuery('#myModal').modal({
                    keyboard: false,
                    backdrop:true,
                    backdrop: 'static'
                });
            jQuery('#myModalLabel').text("Rate for <?php echo $ratingData['service'][0]['companyName']; ?>");
            jQuery('#elementType').val('service');
            jQuery('#bookingID').val("<?php echo $ratingData['service'][0]['bookingID']; ?>");
            jQuery('#elementID').val("<?php echo $ratingData['service'][0]['companyID']; ?>");
        });
    </script>
    <?php
}
?>
<script type="text/javascript">
    function giveRate(rateValue)
    {
        if(rateValue == 5)
        {
            jQuery('#star1,#star2,#star3,#star4,#star5').removeClass('empty full');
            jQuery('#star1,#star2,#star3,#star4,#star5').addClass('full');
        }
        else if(rateValue == 4)
        {
            jQuery('#star1,#star2,#star3,#star4,#star5').removeClass('empty full');
            jQuery('#star1,#star2,#star3,#star4').addClass('full');
            jQuery('#star5').addClass('empty');
        }
        else if(rateValue == 3)
        {
            jQuery('#star1,#star2,#star3,#star4,#star5').removeClass('empty full');
            jQuery('#star1,#star2,#star3').addClass('full');
            jQuery('#star5,#star4').addClass('empty');
        }
        else if(rateValue == 2)
        {
            jQuery('#star1,#star2,#star3,#star4,#star5').removeClass('empty full');
            jQuery('#star1,#star2').addClass('full');
            jQuery('#star5,#star4,#star3').addClass('empty');
        }
        else if(rateValue == 1)
        {
            jQuery('#star1,#star2,#star3,#star4,#star5').removeClass('empty full');
            jQuery('#star1').addClass('full');
            jQuery('#star5,#star4,#star3,#star2').addClass('empty');
        }

        jQuery('#rateValue').val(rateValue);
    } // End of give Rate

    function giveRatingToElement()
    {
        var rateValue   = jQuery('#rateValue').val();
        var elementType = jQuery('#elementType').val();
        var ratedUserID = jQuery('#ratedUserID').val();
        var bookingID   = jQuery('#bookingID').val();
        var userMessage = jQuery('#userMessage').val();
        var elementID   = jQuery('#elementID').val();

        userMessage = userMessage.trim();
        if(userMessage.length == 0)
        {
            jQuery( "#userMessage" ).focus();
            return false;
        }
        else
        {
            jQuery.ajax({
                type: "GET",
                url: "index.php?option=com_beseated&task=userprofile.giverating",
                data: "rate_value="+rateValue+"&element_type="+elementType+"&rated_user_id="+ratedUserID+"&booking_id="+bookingID+"&user_message="+userMessage+"&element_id="+elementID,
                success: function(data){
                    //console.log(data);
                    location.reload();
                }
            });
        }
    }
</script>
<!-- Modal -->
<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Rate for </h3>
    </div>
    <div class="modal-body">
        <div class="large-rating-wrp">
            <i id="star1" onClick="giveRate(1)" class="full"> </i>
            <i id="star2" onClick="giveRate(2)" class="empty"> </i>
            <i id="star3" onClick="giveRate(3)" class="empty"> </i>
            <i id="star4" onClick="giveRate(4)" class="empty"> </i>
            <i id="star5" onClick="giveRate(5)" class="empty"> </i>
        </div>
        <p><textarea id="userMessage" style="width=100%" cols="200" placeholder="enter your feedback" required="true"></textarea></p>
    </div>
    <div class="modal-footer">
        <input type="hidden" name="rateValue" id="rateValue" value="1">
        <input type="hidden" name="elementType" id="elementType" value="">
        <input type="hidden" name="ratedUserID" id="ratedUserID" value="<?php echo $this->user->id; ?>">
        <input type="hidden" name="bookingID" id="bookingID" value="0">
        <input type="hidden" name="elementID" id="elementID" value="0">
        <button class="btn btn-primary" onclick="giveRatingToElement()">Rate It</button>
    </div>
</div>
