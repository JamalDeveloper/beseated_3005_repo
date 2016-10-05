<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$input    = JFactory::getApplication()->input;
$Itemid   = $input->get('Itemid', 0, 'int');
$document = JFactory::getDocument();
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/confirm-js/jquery.confirm.js');
$user     = JFactory::getUser();
$userType = BeseatedHelper::getUserType($user->id);

?>

<div id="alert-error" class="alert alert-error"></div>
<div class="msg-detailwrp-venue-promotion">
        <div class="controls">
            <input type="text" id="subject" name="subject" placeholder="Subject">
            <textarea rows="15" cols="100" name="message"  id="message" placeholder="Message..." ></textarea>
            <button type="submit" class="btn btn-large span" id="send_msg">Submit</button>
        </div>

</div>

</body>



<script type="text/javascript">
    jQuery("#send_msg").click(function() {
       
        var msg     = jQuery('#message').val();
        var subject = jQuery('#subject').val();

        msg     = jQuery.trim(msg);
        subject = jQuery.trim(subject);

        if(msg.length == 0 && subject.length == 0)
        { 
            return 0;
        }

        // Ajax call to send message....
        jQuery.ajax({
            url: 'index.php?option=com_beseated&task=message.send_promotion_message',
            type: 'GET',
            data: 'subject='+subject+'&message='+msg,

            success: function(response){

                if(response == 400)
                {
                    jQuery('#alert-error').show();
                    jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please enter city for send message</h4>');
                    return false;
                }
                else if(response == 500)
                {
                    jQuery('#alert-error').show();
                    jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Error while sending a message</h4>');
                    return false;
                }
                else if(response == 704)
                {
                    jQuery('#alert-error').show();
                    jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please Login .Session Expired</h4>');
                    return false;
                }
                else
                {
                    jQuery('#message').val('') ;
                    jQuery('#subject').val('') ;
                    jQuery('#alert-error').show();
                    jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Message send successfully</h4>');
                    return true;
                }
            }
        })
        .done(function() {
            //console.log("success");
        })
        .fail(function() {
            //console.log("error");
        })
        .always(function() {
            //console.log("complete");
        });
    });

    jQuery(document).ready(function($) {
        jQuery('#alert-error').css('display', 'none');
    });

</script>






