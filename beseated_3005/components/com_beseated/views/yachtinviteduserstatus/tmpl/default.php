<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
$templateDir = Juri::base().'templates/'.JFactory::getApplication()->getTemplate();




// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$input             = JFactory::getApplication()->input;
$Itemid            = $input->get('Itemid', 0, 'int');
$booking_id        = $input->get('booking_id', 0, 'int');
$viewByInvitedUser = $input->get('viewByInvitedUser', 0, 'int');

$invitedUserDetail = $this->invitedUserDetail;

//echo "<pre>";print_r($invitedUserDetail);echo "<pre/>";exit();


$document = JFactory::getDocument();
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/confirm-js/jquery.confirm.js');
$document->addStylesheet($templateDir.'/font-awesome/css/font-awesome.css');

?>


<div class="invited-user-status">

    <?php foreach ($invitedUserDetail as $key => $userDetail) : ?>  
      <div class="invited-user-list">
         
          <img src="<?php echo $userDetail['thumbAvatar'];?>" alt="" />

          <b><?php echo ucfirst($userDetail['fullName']); ?></b>
        
          <?php if($userDetail['statusCode'] == 2) : ?>
            <input type="button" class="set-status-btn" value="Pending">
          <?php elseif($userDetail['statusCode'] == 10) : ?>
            <input type="button" class="set-status-btn" value="Not Going">
          <?php elseif($userDetail['statusCode'] == 9) : ?>
            <input type="button" class="set-status-btn" value="Going">
          <?php elseif($userDetail['statusCode'] == 12) : ?>
            <input type="button" class="set-status-btn" value="Maybe">
          <?php endif; ?> 
      </div>
    <?php endforeach; ?>

    <?php if($viewByInvitedUser == 0) : ?>
      <a href="<?php echo JUri::root().'index.php?option=com_beseated&view=yachtsendinvitation&booking_id='.$booking_id.'&Itemid='.$Itemid; ?>">
              <input type="button" class="btn btn-large" name="payment" value="Invite Friends">
      </a>
    <?php endif; ?> 

</div>










