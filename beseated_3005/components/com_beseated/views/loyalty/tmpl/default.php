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
$document->addScript(JURI::root().'components/com_beseated/assets/carousal/carousal.js');
$document->addScript(JURI::root().'components/com_beseated/assets/carousal/jquery.jcarousel.min.js');
$document->addStyleSheet(JURI::root().'components/com_beseated/assets/carousal/carousal.css');
$document->addStyleSheet(JURI::root().'components/com_beseated/assets/tag-it//bootstrap/bootstrap-tagsinput.css');
$document->addScript(JURI::root().'components/com_beseated/assets/tag-it/bootstrap/bootstrap-tagsinput.js');
?>

<div class="main-loyalty wrapper">
    <div class="loyalty-details">
        <div class="loyalty-total-count">
            You Have Total <span class="loyalty-count"><?php echo $this->totalLoyalty;?></span> Coins
        </div>
        <div class="loyalty-history">
            <h4>Check History</h4>
            <a data-toggle="modal" data-target="#myCheckHistoryModal">
                <button class="btn check-history-btn"></button>
            </a>
        </div>
        <div class="loyalty-earn-more">
            <h4>Earn More Coins</h4>
            <form method="post" accept="<?php echo JRoute::_('index.php?option=com_beseated&view=loyalty'); ?>">
                <div class="span12 loyalty-invite">
                    <input type="text" id="invite_user" name="invite_user" placeholder="Enter emails" id="invite_user" value="" data-role="tagsinput" />
                </div>
                <input type="hidden" name="Itemid" value="<?php echo $Itemid;?>">
                <input type="hidden" id="task" name="task" value="loyalty.send_invitation">
                <input type="hidden" id="view" name="view" value="loyalty">
                <input type="submit" class="btn earn-more-btn" value="Refer Friends">
            </form>
        </div>
    </div>
    <div class="reward-details">
        <?php if (count($this->rewards) > 0):?>
            <?php foreach ($this->rewards as $key => $reward):?>
               <div class="jcarousel-wrapper">
                    <div class="jcarousel">
                        <ul>
                            <li>
                                <a href="index.php?option=com_beseated&view=rewarddetail&reward_id=<?php echo $reward->reward_id;?>&Itemid=<?php echo $Itemid; ?>">
                                <img src="<?php echo JURI::root().$reward->image;?>" width="600" height="400" alt="">
                                <div class="reward-details">
                                    <p class="reward-name"><?php echo ucfirst($reward->reward_name);?></p>
                                    <p class="reward-coins"><?php echo BeseatedHelper::currencyFormat('','',$reward->reward_coin)?></p>
                                </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <?php if(count($this->rewards) > 1):?>
                        <a href="#" class="jcarousel-control-prev">&lsaquo;</a>
                        <a href="#" class="jcarousel-control-next">&rsaquo;</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif;?>
    </div>
</div>

<style>
div.modal.fade{display: none;}
div.modal.fade.in{display: block;}
 </style>
<!-- Modal -->
<div id="myCheckHistoryModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-body">
            <div class="modal-loyalty-history">
                <div class="modal-date">Date</div>
                <div class="modal-type">Type</div>
                <div class="modal-coins">Coins</div>
                <?php foreach ($this->loyalty as $key => $loyalty):?>
                    <div class="loyalty-date"><?php echo $loyalty['date'];?></div>
                    <div class="loyalty-type"><?php echo $loyalty['type'];?></div>
                    <div class="loyalty-coins"><?php echo $loyalty['point'];?></div>
                <?php endforeach; ?>
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


