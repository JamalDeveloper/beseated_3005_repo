<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input    = JFactory::getApplication()->input;
$Itemid   = $input->get('Itemid', 0, 'int');
$user     = JFactory::getUser();
$corePath = JUri::base().'images/beseated/';

?>

<section class="page-section page-messages">
  <div class="container">
    
    <div class="submenu">
      <?php include_once('templates/beseated-theme/partials/guest-profile-menu.php') ?>
    </div>

    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <h2 class="heading-3">Messages</h2>
        <?php foreach ($this->messages as $key => $thread):
            if($user->id == $thread->from_user_id){
                $otherUserID = $thread->to_user_id;
            }else if ($user->id == $thread->to_user_id){
                $otherUserID = $thread->from_user_id;
            }?>
            <?php $otherUserProfile = BeseatedHelper::guestUserDetail($otherUserID);?>
            <?php $link = JRoute::_("index.php?option=com_beseated&view=messagedetail&user_id=" . $otherUserProfile->user_id . "&connection_id=" . $thread->connection_id . "&Itemid=" . $Itemid); ?>
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
          <div class="row">
            <div class="col-md-12">
              <div class="bordered-box item" id="message_<?php echo $thread->connection_id; ?>">

                <div class="row">
                  <div class="col-md-4">
                    <div class="image" style="background-image: url(<?php echo $thumbImage ?>);">       
                      </div>
                  </div>
                  <div class="col-md-8">
                    <div class="description">
                      <p class="name"><?php echo $threadTitle; ?></p>
                      <p class="content"><?php echo $thread->message_body;?></p>
                      <p class="date"><?php echo date('d-M-Y',strtotime($thread->created)); ?></p>
                    </div>
                    <div class="actions">
                      <button class="button" onclick="deleteMessage(<?php echo $thread->connection_id;?>, <?php echo $thread->from_user_id;?>)">Delete</button>
                      <a href="<?php echo $link ?>" class="button">Details</a>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>   
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<div id="myDeleteThreadModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="heading-3">Delete message</h3>
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

<script type="text/javascript">

  function deleteMessage(connectionId, fromUserId) {
    $('#myDeleteThreadModal').modal('show');
    $('.delete-thread').click(function(event) {
      $.ajax({
        type: "GET",
        url: 'index.php?option=com_beseated&task=message.delete_message',
        data: '&from_user_id=' + fromUserId + '&connection_id=' + connectionId,
        success: function(response){
          if(response == "200")
          {
            $('#message_' + connectionId).remove();
          }
        }
      });
    });
  }

</script>