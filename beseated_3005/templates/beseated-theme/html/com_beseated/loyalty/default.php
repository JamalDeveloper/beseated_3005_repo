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

$input    = JFactory::getApplication()->input;
$Itemid   = $input->get('Itemid', 0, 'int');
$document = JFactory::getDocument();
?>
<section class="page-section page-loyalty">
    <div class="container">

        <?php include_once('templates/beseated-theme/partials/guest-profile-menu.php') ?>
    
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <img src="templates/beseated-theme/images/coin-icon.png" alt="" />
                <h3 class="heading-1">Coins</h3>
                <span>Be Seated, Be Treated</span>

                <div class="bordered-box coins-info">
                    <h3 class="heading-1">You Have <span class="coins-count"><?php echo $this->totalLoyalty;?></span> coins</h3>
                    <hr>
                    <div class="refer-friends">
                        <form method="post" accept="<?php echo JRoute::_('index.php?option=com_beseated&view=loyalty'); ?>">
                            <div class="span12 loyalty-invite">
                                <input type="text" id="invite_user" name="invite_user" placeholder="Enter emails" id="invite_user" value="" data-role="tagsinput" class="form-control" />
                            </div>
                            <input type="hidden" name="Itemid" value="<?php echo $Itemid;?>">
                            <input type="hidden" id="task" name="task" value="loyalty.send_invitation">
                            <input type="hidden" id="view" name="view" value="loyalty">
                            <input type="submit" class="button" value="Refer Friends & Earn Coins">
                        </form>
                    </div>
                </div>

            </div>
        </div> 

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <img src="templates/beseated-theme/images/history-icon.png" alt="" />
                <h3 class="heading-1">History</h3>
                <div class="bordered-box table-responsive loyalty-history">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                            </tr>    
                        </thead>
                        <tbody>
                            <?php foreach ($this->loyalty as $key => $loyalty): ?>
                                <tr>
                                    <td><?php echo $loyalty['date'];?></td>
                                    <td><?php echo $loyalty['type'];?></td>
                                    <td class="amount"><?php echo $loyalty['point'];?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <img src="templates/beseated-theme/images/shop-icon.png" alt="" />
                <h3 class="heading-1">Shop</h3>
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <div class="rewards-carousel">        
                            <?php if (count($this->rewards) > 0):?>             
                                <div class="owl-carousel">
                                    <?php foreach ($this->rewards as $key => $reward):?>
                                        <a href="index.php?option=com_beseated&view=rewarddetail&reward_id=<?php echo $reward->reward_id;?>&Itemid=<?php echo $Itemid; ?>">
                                        <div class="reward-image" style="background-image: url(<?php echo JURI::root().$reward->image;?>);"></div>
                                        <div class="reward-details">
                                            <span class="reward-name heading-4"><?php echo ucfirst($reward->reward_name);?></span>
                                            <span class="reward-coins heading-4"><img src="templates/beseated-theme/images/coin-icon-small.png" /><?php echo BeseatedHelper::currencyFormat('','',$reward->reward_coin)?></span>
                                        </div>
                                        </a>
                                    <?php endforeach; ?>  
                                </div>                        
                            <?php endif;?>
                        </div>
                    </div>
                </div>   
            </div>    
        </div>

    </div>
</section>        

<script>
    $(document).ready(function(){
        $('.owl-carousel').owlCarousel({
            loop: true,
            nav: true,
            navText: [
                  "<i class='icon-arrow-left'></i>",
                  "<i class='icon-arrow-right'></i>"
                  ],
            dots: false,
            autoplay: true,
            items: 1
        })
    });
</script>