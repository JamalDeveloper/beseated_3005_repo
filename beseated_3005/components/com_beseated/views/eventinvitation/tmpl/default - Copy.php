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


//require_once JPATH_SITE.'/components/com_fbconnct/fbconnct.php';
require_once JPATH_SITE.'/components/com_fbconnct/controller.php';

require_once JPATH_SITE.'/components/com_fbconnct/inc/facebook.php';
//require_once( JPATH_COMPONENT.DIRECTORY_SEPARATOR.'controller.php' );



$input       = JFactory::getApplication()->input;
$bookingID   = $input->getInt('booking_id');
$eventID     = $input->getInt('event_id');
$totalTicket = $input->getInt('total_ticket');
$Itemid      = $input->get('Itemid', 0, 'int');
$this->user  = JFactory::getUser();
$userDetail  = BeseatedHelper::guestUserDetail($this->user->id);

///print_r($userDetail);
//exit();

$link        = JURI::root().'index.php?option=com_beseated&view=eventinvitation&event_id='.$eventID.'&booking_id='.$bookingID.'&total_ticket='.$totalTicket.'&Itemid=180';
$state       = $input->get('state');
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root().'components/com_beseated/assets/tag-it//bootstrap/bootstrap-tagsinput.css');
$document->addScript(JURI::root().'components/com_beseated/assets/tag-it/bootstrap/bootstrap-tagsinput.js');

//usefull

/*$facebook = new Facebook(array(
		'appId' => '786715428074218',
		'secret' => 'fb5f9e5cf6488f43cda6c37b8ded12ff',
));

$me = $facebook->api('/me');

$friends = $facebook->api('/'.$me['id'].'/friends');

echo "<pre/>";print_r($friends);exit;*/

// not used
/*$fb = new Facebook\Facebook(array(
	'app_id'                => '786715428074218',
	'app_secret'            => 'fb5f9e5cf6488f43cda6c37b8ded12ff',
	'default_graph_version' => 'v2.2'
));


$helper      = $fb->getRedirectLoginHelper();
$permissions = array('email', 'user_likes'); // optional
$loginUrl    = $helper->getLoginUrl($link, $permissions);*/
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
		        <form method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=eventinvitation'); ?>">
					<div class="span12 package-invite">
						<input type="text" id="invite_user" name="invite_user" placeholder="Enter emails" id="invite_user" value="" data-role="tagsinput" />
					</div>
					<input type="hidden" id="task" name="task" value="eventinvitation.inviteUser">
					<input type="hidden" id="view" name="view" value="eventinvitation">
					<input type="hidden" id="booking_id" name="booking_id" value="<?php echo $bookingID;?>">
					<input type="hidden" id="event_id" name="event_id" value="<?php echo $eventID;?>">
					<input type="hidden" id="total_ticket" name="total_ticket" value="<?php echo $totalTicket;?>">
					<button type="submit" class="btn span invite-user-email">Invite</button>
				</form>
	        </div>
	        <div class="tab-pane fade" id="set2">
	        	<div id="friend-invite-error"></div>
	        	<div class="userinvite">
					<?php
					if (isset($state)):

						$_SESSION['FBRLH_state'] = $state;

						try {
						  $accessToken = $helper->getAccessToken();
						} catch(Facebook\Exceptions\FacebookResponseException $e) {
						  // When Graph returns an error
						  echo 'Graph returned an error: ' . $e->getMessage();
						  exit;
						} catch(Facebook\Exceptions\FacebookSDKException $e) {
						  // When validation fails or other local issues
						  echo 'Facebook SDK returned an error: ' . $e->getMessage();
						  exit;
						}

						if (isset($accessToken)):
						  $fb->setDefaultAccessToken($accessToken);
							 try {
							  $response = $fb->get('/me');
							  $userNode = $response->getGraphUser();
							} catch(Facebook\Exceptions\FacebookResponseException $e) {
							  // When Graph returns an error
							  echo 'Graph returned an error: ' . $e->getMessage();
							  exit;
							} catch(Facebook\Exceptions\FacebookSDKException $e) {
							  // When validation fails or other local issues
							  echo 'Facebook SDK returned an error: ' . $e->getMessage();
							  exit;
							}

							$plainOldArray           = $response->getDecodedBody();
							$responseFriend          = $fb->get('/me/friends', $accessToken);
							$facebookFriendList      = $responseFriend->getGraphEdge()->asArray();
							$countFacebookFriendList = count($facebookFriendList);
							$invitedEmails = array();
							$unInvitedFBUser = array();


							if ($countFacebookFriendList > 0)
							{
								foreach ($facebookFriendList as $key => $facebookFriend):
									$userEmail                         = BeseatedHelper::getUserEmail($facebookFriend['id']);
									$facebookFriendList[$key]['email'] = $userEmail;

								endforeach;

								$alreadyInvited = BeseatedHelper::getInvitationDetail($bookingID,'event');

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
								<form class="form-horizontal prf-form" method="post" accept="<?php echo JRoute::_('index.php?option=com_beseated&view=eventinvitation'); ?>">
									<?php foreach ($facebookFriendList as $key => $friend):?>
										<div class="invitation-user-details">
											<div class="user-invite-name"><?php echo ucfirst($friend['name']);?></div>
											<div class="invitation-check">
												<input type="checkbox" value="<?php echo $friend['id'];?>" id="<?php echo $friend['id'];?>" name="check" />
												<label for="<?php echo $friend['id'];?>" style="color:#fff;">&nbsp;</label>
											</div>
										</div>
									<?php endforeach;?>
										<div class="btn-for-invite">
											<input  type="hidden" id="task" name="task" value="eventinvitation.inviteUser">
											<input  type="hidden" id="view" name="view" value="eventinvitation">
											<input  type="hidden" id="fb_id" name="fb_id" value="">
											<input  type="hidden" id="booking_id" name="booking_id" value="<?php echo $bookingID;?>">
											<input  type="hidden" id="event_id" name="event_id" value="<?php echo $eventID;?>">
											<button type="button" class="btn span invite-user-facebook">Invite</button>
										</div>
								</form>
							<?php endif; ?>
						<?php endif;
					else:?>
							<a href="<?php echo $loginUrl;?>">Login with Facebook</a>
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
	var totalTicket  = '<?php echo $totalTicket; ?>';
	var invitePerson = totalTicket-1;

jQuery('input[type="checkbox"][name="check"]').change(function() {
	if(this.checked) {
		var fbID = this.value;
		if(jQuery.inArray(fbID, fbIds) === -1){
			fbIds.push(fbID);
		}else{
			fbIds.pop(fbID);
		}
		fbIdsLength = fbIds.length;
	}else{
		var fbID = this.value;
		fbIds.pop(fbID);
		fbIdsLength = fbIds.length;
	}
	if (parseInt(fbIdsLength) >= parseInt(totalTicket)){
		jQuery('.modal-body').children('.modal-message').text('You cannot select more than '+ invitePerson + ' persons');
		jQuery('#myinviteModal').modal('show');
		jQuery(this).attr('checked', false);
		fbIds.pop(fbID);
	}else{
		jQuery('#fb_id').val(fbIds);
	}
});

jQuery('.invite-user-facebook').click(function(event) {
	var fbID      = jQuery('#fb_id').val();
	var bookingID = jQuery('#booking_id').val();
	var eventID   = jQuery('#event_id').val();
	jQuery.ajax({
		type: "GET",
		url: "index.php?option=com_beseated&task=eventinvitation.inviteUser",
		data: "&fb_id="+fbID+"&booking_id="+bookingID+"&event_id="+eventID,
		success: function(response){
			console.log(response);
			if(response == "200")
			{
				jQuery('#friend-invite-error').show();
 				jQuery('#friend-invite-error').html('<a class="close" data-dismiss="alert">×</a><h4>User Invited.</h4>');
			}

			if(response == "400")
			{
				jQuery('#friend-invite-error').show();
 				jQuery('#friend-invite-error').html('<a class="close" data-dismiss="alert">×</a><h4>User Not Invited.</h4>');
			}
		}
	});
});

function validateEmails() 
{
	var emails  = jQuery('#invite_user').val().split(",");
	
	if(emails.length == 1)
	{
		jQuery('#friend-invite-error').show();
 		jQuery('#friend-invite-error').html('<a class="close" data-dismiss="alert">×</a><h4>Please Enter Valid Email.</h4>');
		return false;
	}
}
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
