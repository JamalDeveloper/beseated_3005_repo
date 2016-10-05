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

//$Itemid = 147;

$app = JFactory::getApplication();
$view = $app->input->get('view','','string');

$menu = $app->getMenu();


?>
<?php /*echo "<pre>";
			print_r($menu);
			echo "</pre>";
			exit; */?>
<?php if($userType == 'BeseatedGuest'): ?>
<div class="moduletable user-dispay-menu">
	<ul class="nav menu remove-menu-space">
		<?php if($view == 'userbookings' || $view == 'userbookingdetail'): ?>
			<li class="current active">
		<?php else: ?>
			<li class="">
		<?php endif; ?>
			<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userbookings', true ); ?>
			<?php $Itemid = $menuItem->id; ?>
            <a href="<?php echo JRoute::_('index.php?option=com_beseated&view=userbookings&Itemid='.$Itemid); ?>">Bookings</a>
		</li>

		<?php if($view == 'guestrequests'): ?>
			<li class="current active">
		<?php else: ?>
			<li class="">
		<?php endif; ?>
			<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=guestrequests', true ); ?>
			<?php $Itemid   = $menuItem->id; ?>
            <a href="<?php echo JRoute::_('index.php?option=com_beseated&view=guestrequests&Itemid='.$Itemid); ?>">RSVP</a>
		</li>

		<?php if($view == 'userpackageinvitations'): ?>
			<li class="current active">
		<?php else: ?>
			<li class="">
		<?php endif; ?>

			<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userpackageinvitations', true ); ?>
			<?php $Itemid = $menuItem->id; ?>
           <!--  <a href="<?php echo JRoute::_('index.php?option=com_beseated&view=userpackageinvitations&Itemid='.$Itemid); ?>">Package Invitations</a> -->
		</li>

		<?php if($view == 'favourites'): ?>
			<li class="current active">
		<?php else: ?>
			<li class="">
		<?php endif; ?>

			<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=favourites', true ); ?>
			<?php $Itemid = $menuItem->id; ?>
			<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=favourites&Itemid='.$Itemid); ?>">Favorites</a>
		</li>

		<?php if($view == 'messages'): ?>
			<li class="current active">
		<?php else: ?>
			<li class="">
		<?php endif; ?>
			<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=messages', true ); ?>
			<?php $Itemid = $menuItem->id; ?>
			<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=messages&Itemid='.$Itemid); ?>">Messages</a>
		</li>

		<?php if($view == 'loyalty'): ?>
			<li class="current active">
		<?php else: ?>
			<li class="">
		<?php endif; ?>
			<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=loyalty', true ); ?>
			<?php $Itemid = $menuItem->id; ?>
			<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=loyalty&Itemid='.$Itemid); ?>">My Loyalty</a>
		</li>

		<?php if($view == 'userprofile'): ?>
			<li class="current active">
		<?php else: ?>
			<li class="">
		<?php endif; ?>
			<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userprofile', true ); ?>

			<?php $Itemid = $menuItem->id; ?>
			<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=userprofile&Itemid='.$Itemid); ?>">Profile</a>
		</li>

		<?php if($view == 'clientcontact'): ?>
			<li class="current active">
		<?php else: ?>
			<li class="">
		<?php endif; ?>
			<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clientcontact', true ); ?>

			<?php $Itemid = $menuItem->id; ?>
			<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clientcontact&Itemid='.$Itemid); ?>">Contact</a>
		</li>
	</ul>
</div>
<?php endif; ?>



