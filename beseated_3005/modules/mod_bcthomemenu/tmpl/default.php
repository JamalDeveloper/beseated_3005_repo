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
$user = JFactory::getUser();

$userType = modBctedHomeMenu::getUserGroup($user->id); //Club OR ServiceProvider OR Registered OR Public
echo $userType;
$modbase 	= JURI::base(true) .'/modules/mod_bcthomemenu'; /* juri::base(true) will not added full path and slash at the path end */

$packageLink = "#";
$favouriteLink = "#";
$bookingLink = "#";
$messageLink = "#";
if($userType == 'Public')
{
	$packageLink = '<a>';
	$favouriteLink = '<a>';
	$bookingLink = '<a>';
	$messageLink = '<a>';
}
else if ($userType == 'Registered')
{
	$packageLink = '<a href="index.php?option=com_bcted&amp;view=packages">';
	$favouriteLink = '<a href="index.php?option=com_bcted&amp;view=favourites">';
	$bookingLink = '<a href="index.php?option=com_bcted&amp;view=userbookings">';
	$messageLink = '<a href="index.php?option=com_bcted&amp;view=messages">';
}
else if ($userType == 'ServiceProvider')
{
	$packageLink = '<a>';
	$favouriteLink = '<a>';
	$bookingLink = '<a>';
	$messageLink = '<a>';
}
else if ($userType == 'Club')
{
	$packageLink = '<a>';
	$favouriteLink = '<a>';
	$bookingLink = '<a>';
	$messageLink = '<a>';
}
?>
<div class="wrapper">
	<div class="span12">
		<div class="serv_blck span3 wow bounceInUp" data-wow-duration="1s">
			<div class="step_blck_img set2"><a href="#"><span class="pkg_icon">&nbsp;</span><span class="srv_title">Packages</span></a></div>
			<div class="step_dscr">Various premium packages are customized on a continuous basis, to allow a party of friends to pay in advance for different Venues and Service, and even a combination of both.</div>
		</div>
		<div class="serv_blck span3 wow bounceInUp" data-wow-duration="2s">
			<div class="step_blck_img set2"><a href="#"><span class="booking_icon">&nbsp;</span><span class="srv_title">Friends</span></a></div>
			<div class="step_dscr">Facebook integration, allows users to view who of their friends have booked to Venues, and can request to be added to their table.</div>
		</div>
		<div class="serv_blck span3 wow bounceInUp" data-wow-duration="3s">
			<div class="step_blck_img set2"><a href="#"><span class="msg_icon">&nbsp;</span><span class="srv_title">Messages</span></a></div>
			<div class="step_dscr">Push-notification system allows customers to receive booking confirmation, information about upcoming events, and special offers available.</div>
		</div>
		<div class="serv_blck span3 wow bounceInUp" data-wow-duration="4s">
			<div class="step_blck_img set2"><a href="#"><span class="fav_icon">&nbsp;</span><span class="srv_title">Rewards</span></a></div>
			<div class="step_dscr">Loyalty Points are awarded to customer when booking with Beseated Platform. Loyalty points can be used to pay for Venue Deposits, Luxury Services, and even pay for packages.</div>
		</div>
	</div>
</div>



