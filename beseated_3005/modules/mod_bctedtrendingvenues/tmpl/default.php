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

//http://192.168.5.10/MobileProject/bc-tedlive/index.php?option=com_bcted&view=clubinformation&club_id=1&Itemid=128

//require_once(JURI::base() .'components/com_bcted/helpers/bcted.php');

//$Itemid = BctedHelper::getBctedMenuItem('user-clubinformation');


$app      = JFactory::getApplication();
$menu     = $app->getMenu();
$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubinformation', true );
$Itemid   = $menuItem->id;
/*echo "<pre>";
print_r($venues);
echo "</pre>";
die;*/
?>
<div class="wrapper">
	<h2>Trending Venues</h2>
	<div class="span12">

		<?php foreach ($venues as $key => $venue) : ?>
			<div class="span4 venue_blck ">
				<div class="venue-img">
					<?php if(!empty($venue->thumb_image)):?>
						<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubinformation&club_id='.$venue->venue_id.'&Itemid='.$Itemid) ?>"><img src="<?php echo JUri::base().'images/beseated/' . $venue->thumb_image; ?>" alt="" /></a>
					<?php elseif(!empty($result->image)): ?>
						<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubinformation&club_id='.$venue->venue_id.'&Itemid='.$Itemid) ?>"><img src="<?php echo JUri::base().'images/beseated/' . $venue->image; ?>" alt="" /></a>
					<?php else: ?>
						<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubinformation&club_id='.$venue->venue_id.'&Itemid='.$Itemid) ?>"><img src="<?php echo JUri::base().'images/venue-1.jpg'; ?>" alt="" /></a>
					<?php endif; ?>

					<?php $venue_rating = floor($venue->avg_ratting);
					      $printed_star = 0;
					?>
					<div class="rating-wrp">
						<?php for($i = 1; $i <=$venue_rating; $i++):

						       $printed_star++;
						?>
							<i class="full"> </i>

						<?php endfor; ?>

						<?php if($venue_rating < $venue->avg_ratting)
							  {
							  	$printed_star++;
							  ?>
									<i class="half"> </i>
						<?php }

						if($printed_star < $venue->avg_ratting)
						{
							for($i = $venue->avg_ratting-$printed_star; $i <= $venue->avg_ratting; $i++)
							{
						?>
								<i class="empty"> </i>
						<?php
							}
						}
						?>

						<?php for($i = $venue->avg_ratting+1; $i <= 5; $i++): ?>

						<?php endfor; ?>
					</div>
				</div>
				<div class="venue-title">
				<h4><?php echo $venue->venue_name; ?></h4>
				<div class="venue-location"><?php echo $venue->city; ?></div>
				</div>
				<div class="venue-book">
					<!-- <a href="#">Book now</a> -->
					<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubtablebooking', true ); ?>
					<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubtablebooking&show_table_list=1&club_id=' . $venue->venue_id . '&venue_id='. $venue->venue_id .'&Itemid='.$Itemid); ?>">Book Now</a>
				</div>
			</div>
		<?php endforeach; ?>

		<!--<div class="span4 venue_blck ">
			<div class="venue-img"><img src="images/venue-1.jpg" alt="" />
				<div class="rating-wrp">
					<i class="full"> </i>
					<i class="full"> </i>
					<i class="full"> </i>
					<i class="empty"> </i>
					<i class="empty"> </i>
				</div>
			</div>
			<div class="venue-title">
			<h4>Bar blue</h4>
			<div class="venue-location">Dubai</div>
			</div>
			<div class="venue-book"><a href="#">Book now</a></div>
		</div>
		<div class="span4 venue_blck">
			<div class="venue-img"><img src="images/venue-1.jpg" alt="" />
				<div class="rating-wrp">
					<i class="full"> </i>
					<i class="full"> </i>
					<i class="full"> </i>
					<i class="empty"> </i>
					<i class="empty"> </i>
				</div>
			</div>
			<div class="venue-title">
			<h4>Bar blue</h4>
			<div class="venue-location">Dubai</div>
			</div>
			<div class="venue-book"><a href="#">Book now</a> </div>
		</div>-->
	</div>
</div>



