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
$corePath = JUri::base().'images/beseated/';
$this->messages  = BeseatedHelper::getMessageThread();
$msgThreadCount = count($this->messages);

?>
<script type="text/javascript">
function deleteMessage(connectionID, fromUserId)
{
    jQuery('#myDeleteThreadModal').modal('show');
    jQuery('.delete-thread').click(function(event) {
        jQuery.ajax({
            type: "GET",
            url: 'index.php?option=com_beseated&task=message.delete_message',
            data: '&from_user_id='+fromUserId+'&connection_id='+connectionID,
            success: function(response){
                if(response == "200")
                {
                    jQuery('#msg_'+connectionID).remove();
                }
                else if(response == "400")
                {
                    jQuery('#alert-error').show();
                    jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Error while deleteing a message</h4>');
                    return false;
                }
                else if(response == "204")
                {
                    jQuery('#msg_'+connectionID).remove();
                    
                    jQuery('#alert-error').show();
                    jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>No Message Found</h4>');
                    return false;  
                }
            }
        });
    });
}

jQuery(document).ready(function($) 
{
    var msgThreadCount = '<?php echo $msgThreadCount; ?>';

    if(msgThreadCount == 0)
    {
        jQuery('#alert-error').show();
        jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>No Message Found</h4>');
        return false;
    }

    jQuery('#alert-error').css('display', 'none');
});
</script>

<div id="alert-error" class="alert alert-error"></div>
<div class="table-wrp message-wrp">
    <div class="message-list">
        <?php foreach ($this->messages as $key => $thread):
            if($user->id == $thread->from_user_id){
                $otherUserID = $thread->to_user_id;
            }else if ($user->id == $thread->to_user_id){
                $otherUserID = $thread->from_user_id;
            }
            ?>
            <?php $otherUserProfile = BeseatedHelper::guestUserDetail($otherUserID);?>
            <?php if($otherUserProfile->user_type == 'yacht')
            {
                $elementDetail = BeseatedHelper::yachtUserDetail($otherUserID);
                $threadTitle   = ($elementDetail->yacht_name)? $elementDetail->yacht_name :"";
                $location      = ($elementDetail->location)?$elementDetail->location :"";
                $city          = ($elementDetail->city) ? $elementDetail->city :"";
                $images        = BeseatedHelper::getElementDefaultImage($elementDetail->yacht_id,'Yacht');
                $thumbImage    = ($images->thumb_image)? $corePath.$images->thumb_image : '';
                $image         = ($images->image)? $corePath.$images->image:'';
            }
            else if($otherUserProfile->user_type == 'venue')
            {
                $elementDetail = BeseatedHelper::venueUserDetail($otherUserID);
                $threadTitle   = ($elementDetail->venue_name) ? $elementDetail->venue_name :"";
                $location      = ($elementDetail->location)? $elementDetail->location :"";
                $city          = ($elementDetail->city) ? $elementDetail->city :"";
                $images        = BeseatedHelper::getElementDefaultImage($elementDetail->venue_id,'Venue');
                $thumbImage    = ($images->thumb_image)? $corePath.$images->thumb_image : '';
                $image         = ($images->image)? $corePath.$images->image:'';
            }
            else if($otherUserProfile->user_type == 'protection')
            {
                $elementDetail = BeseatedHelper::protectionUserDetail($otherUserID);
                $threadTitle   = ($elementDetail->protection_name)?$elementDetail->protection_name:"";
                $location      = ($elementDetail->location)?$elementDetail->location:"";
                $city          = ($elementDetail->city)?$elementDetail->city:"";
                $images        = BeseatedHelper::getElementDefaultImage($elementDetail->protection_id,'Protection');
                $thumbImage    = ($images->thumb_image)? $corePath.$images->thumb_image : '';
                $image         = ($images->image)? $corePath.$images->image:'';
            }
            else if($otherUserProfile->user_type == 'chauffeur')
            {
                $elementDetail = BeseatedHelper::chauffeurUserDetail($otherUserID);
                $threadTitle   = ($elementDetail->chauffeur_name)?$elementDetail->chauffeur_name:"";
                $location      = ($elementDetail->location)?$elementDetail->location:"";
                $city          = ($elementDetail->city)?$elementDetail->city:"";
                $images        = BeseatedHelper::getElementDefaultImage($elementDetail->chauffeur_id,'Chauffeur');
                $thumbImage    = ($images->thumb_image)? $corePath.$images->thumb_image : '';
                $image         = ($images->image)? $corePath.$images->image:'';
            }
            else if($otherUserProfile->user_type == 'beseated_guest')
            {
                $elementDetail = $otherUserProfile;
                $threadTitle   = ($elementDetail->full_name)?$elementDetail->full_name:"";
                $location      = ($elementDetail->location)?$elementDetail->location:"";
                $city          = ($elementDetail->city)?$elementDetail->city:"";
                $thumbImage    = ($otherUserProfile->thumb_avatar)?BeseatedHelper::getUserAvatar($otherUserProfile->thumb_avatar):'';
                $image         = ($otherUserProfile->avatar)?BeseatedHelper::getUserAvatar($otherUserProfile->avatar):'';
            }
            else if($otherUserProfile->user_type == 'administrator')
            {
                $elementDetail       = $otherUserProfile;
                $elementType         = "Administrator";
                $temp['threadTitle'] = $elementType;
                $temp['location']    = "";
                $temp['city']        = "";

                $temp['thumbImage']  = '';
                $temp['image']       = '';
                $temp['elementID']   = '';
                $temp['fbid']        = '';
            }
            ?>
            <div class="message-details-main" id="msg_<?php echo $thread->connection_id; ?>">
                <?php  $pos = strpos($thumbImage, 'facebook');?>
                <div class="message-user-image">
                    <?php if ($pos > 0):?>
                        <img src="<?php echo $thumbImage;?>" alt="" />
                    <?php else:?>
                        <img src="<?php echo $thumbImage;?>" alt="" />
                    <?php endif; ?>
                </div>
                <?php $link = JRoute::_("index.php?option=com_beseated&view=yachtmessagedetail&user_id=".$otherUserProfile->user_id."&connection_id=".$thread->connection_id."&Itemid=".$Itemid);?>
                <div class="message-details-inner">
                    <a href="<?php echo $link;?>">
                        <div class="message-details">
                            <p><?php echo ucfirst($threadTitle);?></p>
                            <p><?php echo $thread->message_body;?></p>
                        </div>
                        <div class="message-date">
                            <p> <?php echo date('d-M-Y',strtotime($thread->created)); ?></p>
                        </div>
                    </a>
                </div>
                <div class="delete-message">
                    <button class="del-btn" onclick="deleteMessage(<?php echo $thread->connection_id;?>, <?php echo $thread->from_user_id;?>)"> - </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<style>
div.modal.fade{display: none;}
div.modal.fade.in{display: block;}
</style>
<!-- Modal -->
<div id="myDeleteThreadModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="modal-message">Are you sure you want to delete this message? This action cannot be undone.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default delete-thread" data-dismiss="modal">Yes</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
      </div>
    </div>
  </div>
</div>



