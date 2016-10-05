<?php
/**
	 * @package   AppImage Slider
	 * @version   1.0
	 * @author    Erwin Schro (http://www.joomla-labs.com)
	 * @author	  Based on BxSlider jQuery plugin script
	 * @copyright Copyright (C) 2013 J!Labs. All rights reserved.
	 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
	 *
	 * @copyright Joomla is Copyright (C) 2005-2013 Open Source Matters. All rights reserved.
	 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
	 */


defined('_JEXEC') or die('Restricted access');

$doc 	= JFactory::getDocument();


$modbase 	= JURI::base(true) .'/modules/mod_bctedtitle'; /* juri::base(true) will not added full path and slash at the path end */
// add style
//$doc->addStyleSheet($modbase . '/assets/css/style.css');
// add javascript
/*$doc->addScript($modbase . '/assets/js/libs/prototype.js');
$doc->addScript($modbase . '/assets/js/libs/scriptaculous.js');
$doc->addScript($modbase . '/assets/js/libs/sizzle.js');
$doc->addScript($modbase . '/assets/js/loupe.js');*/
$input  = JFactory::getApplication()->input;
$Itemid = $input->get('Itemid', 0, 'int');

$app    = JFactory::getApplication();
$menu   = $app->getMenu();
?>
<h2 class="heading-1"><?php echo $firstLine; ?></h2>
<?php if(($userType == 'Registered' || $userType == 'beseated_guest') && $companyID==0): ?>
	<div class="menu-box">
		<ul class="venue-menu">			
			<li class="<?php if ($view == 'clubinformation') echo 'active' ?>">
				<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubinformation&club_id=' . $clubID . '&Itemid='.$Itemid); ?>">
					<img src="templates/beseated-theme/images/icon-info<?php if ($view == 'clubinformation') echo '-active' ?>.png" />
					Info
				</a>
			</li>
			<li class="<?php if ($view == 'clubguestlist') echo 'active' ?>">
				<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubguestlist', true );?>
				<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubguestlist&club_id=' . $clubID . '&Itemid='.$menuItem->id); ?>">
					<img src="templates/beseated-theme/images/icon-guestlist<?php if ($view == 'clubguestlist') echo '-active' ?>.png" />
					Guestlist
				</a>
			</li>
			<li class="<?php if (($view == 'clubtables') or ($view == 'clubtablebooking')) echo 'active' ?>">
				<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubtables', true );?>
				<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubtables&club_id=' . $clubID . '&Itemid='.$menuItem->id); ?>">
					<img src="templates/beseated-theme/images/icon-reservations<?php if (($view == 'clubtables') or ($view == 'clubtablebooking')) echo '-active' ?>.png" />
					Reservations
				</a>
			</li>
			<?php /* <li class="<?php if ($view == 'clubfriendsattending') echo 'active' ?>">
				<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubfriendsattending', true ); ?>
				<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubfriendsattending&club_id=' . $clubID . '&Itemid='.$menuItem->id); ?>">
					<img src="templates/beseated-theme/images/icon-friendattending<?php if ($view == 'clubtablesclubfriendsattending') echo '-active' ?>.png" />
					Friends Attending
				</a>
			</li> */ ?>
			<li class="<?php if ($view == 'clubratings') echo 'active' ?>">
				<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubratings', true ); ?>
				<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubratings&club_id=' . $clubID . '&Itemid='.$menuItem->id); ?>">
					<img src="templates/beseated-theme/images/icon-reviews<?php if ($view == 'clubratings') echo '-active' ?>.png" />
					Reviews
				</a>
			</li>
		</ul>
		
	</div>

<?php elseif(($userType == 'Registered' || $userType == 'beseated_guest') && $clubID==0): die;?>
<div class="moduletable user-dispay-menu">
	<ul class="nav menu">
		<?php if($view == 'companyinformation'): ?>
			<li class="current active">
		<?php else: ?>
			<li class="">
		<?php endif; ?>
			<i class="info-icn"></i>
            <!-- <a href="/MobileProject/bc-tedlive/index.php?option=com_beseated&amp;view=clubinformation&amp;club_id=<?php //echo $clubID; ?>&amp;Itemid=128">Information</a> -->
            <?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=companyinformation', true ); ?>
            <a href="<?php echo JRoute::_('index.php?option=com_beseated&view=companyinformation&company_id=' . $companyID . '&Itemid='.$menuItem->id); ?>">Information</a>
		</li>

		<?php if($clubDetail->company_type!="jet"): ?>
			<?php if($view == 'companyservices' || $view == 'companyservicebooking'): ?>
				<li class="current active">
			<?php else: ?>
				<li class="">
			<?php endif; ?>
	       		<i class="srv-icn"></i>
				<!-- <a href="/MobileProject/bc-tedlive/index.php?option=com_beseated&amp;view=clubtables&amp;club_id=<?php //echo $clubID; ?>&amp;Itemid=130">Tables</a> -->
				<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=companyservices', true ); ?>
				<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=companyservices&company_id=' . $companyID . '&Itemid='.$menuItem->id); ?>">Services</a>
			</li>

			<?php if($view == 'companyratings'): ?>
				<li class="current active">
			<?php else: ?>
				<li class=" ">
			<?php endif; ?>
				<i class="rate-icn"></i>
				<!-- <a href="/MobileProject/bc-tedlive/index.php?option=com_beseated&amp;view=clubratings&amp;club_id=<?php //echo $clubID; ?>&amp;Itemid=132">User Ratings</a> -->
				<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=companyratings', true ); ?>
				<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=companyratings&company_id=' . $companyID . '&Itemid='.$menuItem->id); ?>">Reviews</a>
			</li>
		<?php endif; ?>

		<?php /*if($view == 'clubfriendsattending'): ?>
			<li class="item-131 current active">
		<?php else: ?>
			<li class="item-131 ">
		<?php endif; ?>
        	<i class="frnd-icn"></i>
			<!-- <a href="/MobileProject/bc-tedlive/index.php?option=com_beseated&amp;view=clubfriendsattending&amp;club_id=<?php //echo $clubID; ?>&amp;Itemid=131">Friends Attending</a> -->
			<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubfriendsattending&company_id=' . $companyID . '&Itemid=131'); ?>">Friends Attending</a>
		</li> */ ?>

		<?php if($clubDetail->company_type=="jet"): ?>
			<li class=" book-now-btn">
				<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=companyservices', true ); ?>
				<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=jetservicebooking&company_id=' . $companyID . '&Itemid='.$menuItem->id); ?>">Request Quote</a>
			</li>
		<?php else: ?>
			<li class=" book-now-btn">
				<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=companyservices', true ); ?>
				<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=companyservicebooking&show_service_list=1&company_id=' . $companyID . '&Itemid='.$menuItem->id); ?>">BESEATED</a>
			</li>
		<?php endif; ?>

	</ul>
</div>
<?php endif; ?>



