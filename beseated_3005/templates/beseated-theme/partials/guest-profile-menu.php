<?php
    $app = JFactory::getApplication();
    $input = $app->input;
    $view = $input->get('view');
    $Itemid = $input->get('Itemid', 0, 'int'); 
    $menu = $app->getMenu();
?>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="menu-box">
            <ul class="guest-profile-menu">

                <li class="<?php if ($view == 'userprofile') echo 'active' ?>">
                    <a href="<?php echo JRoute::_('index.php?option=com_beseated&view=userprofile&Itemid='.$Itemid); ?>"><img src="templates/beseated-theme/images/profile-icon<?php if ($view == 'userprofile') echo '-active' ?>.png" /><span class="name">Profile</span></a>
                </li>

                <li class="<?php if ($view == 'loyalty') echo 'active' ?>">
                    <?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=loyalty', true );?>
                    <a href="<?php echo JRoute::_('index.php?option=com_beseated&view=loyalty&Itemid='.$menuItem->id); ?>"><img src="templates/beseated-theme/images/icon-loyalty<?php if ($view == 'loyalty') echo '-active' ?>.png" /><span class="name">My Loyalty</span></a>
                </li>

                <li class="<?php if ($view == 'favourites') echo 'active' ?>">
                    <img src="" />
                    <?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=favourites', true );?>
                    <a href="<?php echo JRoute::_('index.php?option=com_beseated&view=favourites&Itemid='.$menuItem->id); ?>"><img src="templates/beseated-theme/images/icon-reviews<?php if ($view == 'favourites') echo '-active' ?>.png" /><span class="name">Favourites</span></a>
                </li>

                <?php $active = in_array($view, array('messages', 'messagedetail')); ?>
                <li class="<?php if ($active) echo 'active' ?>">
                    <img src="" />
                      <?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=messages', true );?>
                    <a href="<?php echo JRoute::_('index.php?option=com_beseated&view=messages&Itemid='.$menuItem->id); ?>"><img src="templates/beseated-theme/images/icon-messages<?php if ($active) echo '-active' ?>.png" /><span class="name">Messages</span></a>
                </li>

                <li class="<?php if ($view == 'userbookings') echo 'active' ?>">
                    <img src="" />
                      <?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userbookings', true );?>
                    <a href="<?php echo JRoute::_('index.php?option=com_beseated&view=userbookings&Itemid='.$menuItem->id); ?>"><img src="templates/beseated-theme/images/icon-requests<?php if ($view == 'userbookings') echo '-active' ?>.png" /><span class="name">Bookings</span></a>
                </li>
                
                <?php $active = in_array($view, array('guestrequests', 'guestrequestsdetail')); ?>
                <li class="<?php if ($active) echo 'active' ?>">
                    <img src="" />
                      <?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=guestrequests', true );?>
                    <a href="<?php echo JRoute::_('index.php?option=com_beseated&view=guestrequests&Itemid='.$menuItem->id); ?>"><img src="templates/beseated-theme/images/rsvp-icon<?php if ($active) echo '-active' ?>.png" /><span class="name">RSVP</span></a>
                </li>

            </ul>
        </div>
    </div>
</div>