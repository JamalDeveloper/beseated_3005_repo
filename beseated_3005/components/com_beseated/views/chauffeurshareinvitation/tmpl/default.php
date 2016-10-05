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

require_once JPATH_SITE.'/components/com_fbconnct/controller.php';
require_once JPATH_SITE.'/components/com_fbconnct/inc/facebook.php';

$input       = JFactory::getApplication()->input;
$app         = JFactory::getApplication();
$menu        = $app->getMenu();
$menuItem    = $menu->getItems('link','index.php?option=com_beseated&view=userbookings',true);
$itemid      = $menuItem->id;

$bookingID        = $input->getInt('booking_id',0);
$invitationID     = $input->getInt('invitation_id',0);

$Itemid      = $input->get('Itemid', 0, 'int');
$this->user  = JFactory::getUser();
$userDetail  = BeseatedHelper::guestUserDetail($this->user->id);

if($invitationID)
{
	$total_split_count = 1;
}
else
{
	$total_split_count  = 0;
	if($this->chauffeurBookingDetail->splitted_count)
	{
		$total_split_count = $this->chauffeurBookingDetail->total_split_count - $this->chauffeurBookingDetail->splitted_count;
	}
	elseif ($this->chauffeurBookingDetail->total_split_count && empty($this->chauffeurBookingDetail->splitted_count)) 
	{

	    $total_split_count = $this->chauffeurBookingDetail->total_split_count - 1;
	}
	
}

//echo "<pre>";print_r($total_split_count);echo "<pre/>";exit();

$link =  JURI::root()."index.php?option=com_beseated&view=chauffeurrequestpay&chauffeur_booking_id=".$bookingID."&Itemid=".$Itemid;

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root().'components/com_beseated/assets/tag-it//bootstrap/bootstrap-tagsinput.css');
$document->addScript(JURI::root().'components/com_beseated/assets/tag-it/bootstrap/bootstrap-tagsinput.js');

//usefull
if($userDetail->is_fb_user)
{
	$facebook = new Facebook(array(
			'appId' => '786715428074218',
			'secret' => 'fb5f9e5cf6488f43cda6c37b8ded12ff',
	));

	$me = $facebook->api('/me');

	$friends = $facebook->api('/'.$me['id'].'/friends');

	$facebookFriendList =  $friends['data'];
}


if (isset($state)):?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		jQuery('ul.nav-tabs > .facebook > a').tab('show')
	});
</script>
<?php endif;?>

<script type="text/javascript">
jQuery(document).ready(function($) {
	jQuery('#friend-invite-error').hide();
	jQuery("ul.nav-tabs a").click(function (e) {
	  e.preventDefault();
	    jQuery(this).tab('show');
	});
});
</script>

<!DOCTYPE html>
<html>
<body>

<div id="alert-error" class="alert alert-error"></div>
<div class="bct-summary-container">
	<div class="tabbable boxed parentTabs">
	    <ul class="nav nav-tabs">
	        <li class="active"><a href="#set1">Email</a></li>
	        <?php if($userDetail->is_fb_user == 1) : ?>
	        <li class=" facebook"><a href="#set2">Facebook</a></li>
	        <?php endif; ?>
	    </ul>
	    <div class="tab-content">
	        <div class="tab-pane fade active in" id="set1">
		        <form method="post" id="shareuser" action="<?php echo JRoute::_('index.php?option=com_beseated&view=chauffeurshareinvitation'); ?>">
					<div class="span12 package-invite">
						<input type="text" id="invite_user" name="invite_user" placeholder="Enter emails" id="invite_user" value="" data-role="tagsinput" />
					</div>

					<?php if($invitationID): ?>
						<input type="hidden" id="task" name="task" value="chauffeurshareinvitation.replaceShareInvitee">
					<?php else: ?>
						<input type="hidden" id="task" name="task" value="chauffeurshareinvitation.inviteUser">
					<?php endif; ?>
					
					<input type="hidden" id="view" name="view" value="chauffeurshareinvitation">
					<input type="hidden" id="booking_id" name="booking_id" value="<?php echo $bookingID;?>">
					<input type="hidden" id="invitation_id" name="invitationID" value="<?php echo $invitationID;?>">
					<input type="hidden" id="invite_type" name="invite_type" value="email">
					<button type="submit" class="btn span invite-user-email" onclick="checkValidShareUser();">Invite</button>
				</form>
	        </div>
	        <div class="tab-pane fade" id="set2">
	        	<div id="friend-invite-error"></div>
	        	<div class="userinvite">
					<?php
					
					$countFacebookFriendList = count($facebookFriendList);
					$invitedEmails = array();
					$unInvitedFBUser = array();

					if ($countFacebookFriendList > 0)
					{
						foreach ($facebookFriendList as $key => $facebookFriend)
						{
							$userEmail                         = BeseatedHelper::getUserEmail($facebookFriend['id']);

							if(empty($userEmail))
							{
								unset($facebookFriendList[$key]);
							}
							else
							{
								$facebookFriendList[$key]['email'] = $userEmail;
							}
						}

						$alreadyInvited = BeseatedHelper::getSplitedDetail($bookingID,'Chauffeur');

						for ($i = 0; $i < count($alreadyInvited); $i++)
						{
							$invitedEmails[] = $alreadyInvited[$i]->email;
						}

						if (count($alreadyInvited) > 0)
						{
							for ($i = 0; $i < $countFacebookFriendList; $i++)
							{
								if (in_array($facebookFriendList[$i]['email'],$invitedEmails))
								{
									unset($facebookFriendList[$i]);
								}

							}
						}
					}
					?>
					<?php if (count($facebookFriendList) > 0):?>
						<form class="form-horizontal prf-form" id="sharefbuser" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=chauffeurshareinvitation'); ?>">
							<?php foreach ($facebookFriendList as $key => $friend):?>
								<div class="invitation-user-details">
									<div class="user-invite-name"><?php echo ucfirst($friend['name']);?></div>
									<div class="invitation-check">
										<input type="checkbox" value="<?php echo $friend['id'];?>" id="<?php echo $friend['id'];?>" name="check" class="invite_fb_user"/>
										<label for="<?php echo $friend['id'];?>" style="color:#fff;">&nbsp;</label>
									</div>
								</div>
							<?php endforeach;?>
								<div class="invite-user-facebook" >
									
								<?php if($invitationID): ?>
									<input type="hidden" id="task" name="task" value="chauffeurshareinvitation.replaceShareInvitee">
								<?php else: ?>
									<input type="hidden" id="task" name="task" value="chauffeurshareinvitation.inviteUser">
								<?php endif; ?>
									
					                <input type="hidden" id="view" name="view" value="chauffeurshareinvitation">
									<input  type="hidden" id="fb_id" name="fb_id" value="">
									<input  type="hidden" id="booking_id" name="booking_id" value="<?php echo $bookingID;?>">
									<input type="hidden" id="invitation_id" name="invitationID" value="<?php echo $invitationID;?>">	
									<input  type="hidden" id="invite_type" name="invite_type" value="facebook">
									<button type="submit" class="btn span invite-user-facebook">Invite</button>
								</div>
						</form>
					<?php endif; ?>
				</div>
	        </div>
	    </div>
	</div>
</div>
</body>
</html>

<script type="text/javascript">
	var fbIds        = [];
	var remainingshareUser  = '<?php echo $total_split_count; ?>';

	//var remainingshareUser = total_split_count-1;
	//var shareUserCount     = total_split_count-1;

jQuery('input[type="checkbox"][name="check"]').change(function() 
{
	if(this.checked) 
	{
		var fbID = this.value;
		if(jQuery.inArray(fbID, fbIds) === -1)
		{
			fbIds.push(fbID);
		}
		else
		{
			fbIds.pop(fbID);
		}

		fbIdsLength = fbIds.length;
	}
	else
	{
		var fbID = this.value;
		fbIds.pop(fbID);
		fbIdsLength = fbIds.length;
	}


	if(parseInt(remainingshareUser) === 0)
	{
		jQuery('.modal-body').children('.modal-message').text('You Can Not Invite More Than '+ remainingshareUser + ' Person(s)');
		jQuery('#myinviteModal').modal('show');
		jQuery(this).attr('checked', false);
		//fbIds.pop(fbID);
	}
	else if (parseInt(fbIdsLength) > parseInt(remainingshareUser))
	{
		jQuery('.modal-body').children('.modal-message').text('You Can Not Invite More Than '+ remainingshareUser + ' Person(s)');
		jQuery('#myinviteModal').modal('show');
		jQuery(this).attr('checked', false);
		fbIds.pop(fbID);
	}
	else
	{
		jQuery('#fb_id').val(fbIds);
	}
});

jQuery("#shareuser").submit(function(e) 
{
	var sharedEmails =  jQuery('#invite_user').val();

	var sharedEmailsLength = sharedEmails.split(",").length;

	var sharedEmails  = sharedEmails.split(",");

	var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

	for (var i = 0; i < sharedEmails.length; i++) 
	{
	     if(!regex.test(sharedEmails[i]))
	     {
	     	 jQuery('#alert-error').show();
	    	 jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Invalid Email(s) You Have Entered</h4>');
	         e.preventDefault();
	     }
	}

	if(sharedEmails.length == 0)
	{
		e.preventDefault();
	}

	if(sharedEmailsLength > remainingshareUser)
	{
		jQuery('.modal-body').children('.modal-message').text('You Can Not Invite More Than '+ remainingshareUser + ' Person(s)');
	    jQuery('#myinviteModal').modal('show');
		e.preventDefault();
	}
});


jQuery("#sharefbuser").submit(function(e) 
{
	var sharedFBUsers =  jQuery('#fb_id').val();

	if(sharedFBUsers == '')
	{
		var sharedFBUsersLength = 0;
	}
	else
	{
		var sharedFBUsersLength = sharedFBUsers.split(",").length;
	}
	
	if(sharedFBUsersLength == 0)
	{
		e.preventDefault();
	}

	if(sharedFBUsersLength > remainingshareUser)
	{
		jQuery('.modal-body').children('.modal-message').text('You Can Not Invite More Than '+ remainingshareUser + ' Person(s)');
	    jQuery('#myinviteModal').modal('show');
		e.preventDefault();
	}
});

jQuery(document).ready(function($) 
{
	jQuery('#alert-error').hide();
});

</script>

<style>
  div.modal.fade{display: none;}
  div.modal.fade.in{display: block;}
</style>
<!-- Modal -->
<div id="myinviteModal"  class="modal fade" role="dialog">
	<div class="modal-dialog">
 	<!-- Modal content-->
      	<div class="modal-content">
	        <div class="modal-header">
	           <button type="button" class="close" data-dismiss="modal"></button>
	         </div>
	         <div class="modal-body">
	           <div class="modal-message"></div>
	         </div>
	         <div class="modal-footer">
	           <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	          </div>
		</div>
	</div>
</div>
