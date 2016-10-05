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

$input       = JFactory::getApplication()->input;
$Itemid      = $input->get('Itemid', 0, 'int');
$clubID      = $input->get('club_id', 0, 'int');
$app         = JFactory::getApplication();
$menu        = $app->getMenu();
$menuItem    = $menu->getItems( 'link', 'index.php?option=com_users&view=login', true );
$loginItemid = $menuItem->id;
$bctParams   = BeseatedHelper::getExtensionParam();
$accessLevel = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
$access      = array('access','link');
$property    = array($accessLevel,'index.php?option=com_beseated&view=clubinformation');
$menuItem2   = $menu->getItems( $access, $property, true );
$link2       = 'index.php?option=com_beseated&view=clubinformation&club_id='.$clubID.'&Itemid='.$menuItem2->id;
$loginLink   = 'index.php?option=com_users&view=login&Itemid='.$loginItemid.'&return='.base64_encode($link2);
$document = JFactory::getDocument();
$document->addStylesheet(JUri::root().'components/com_beseated/assets/css/bootstrap-toggle.min.css');
$document->addScript(JUri::root().'components/com_beseated/assets/confirm-js/bootstrap-toggle.min.js');
?>
<script type="text/javascript">
	function addVenueToFavourite(venueID,userID)
	{
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=venues.addtofavourite',
			type: 'GET',
			data: '&venue_id='+venueID+'&user_id='+userID,
			success: function(response){
	        	if(response == "1" || response == "2")
	        	{
	        		jQuery('#favourite-add').hide();
	        		jQuery('#favourite-remove').show();
	        	}
	        	else if(response == "3")
	        	{
	        		window.location.href="<?php echo $loginLink; ?>";
	        	}
	        }
		})
		.done(function() {
		})
		.fail(function() {
		})
		.always(function() {
		});
	}

	function removeVenueFromFavourite(venueID,userID)
	{
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=venues.removefromfavourite',
			type: 'GET',
			data: '&venue_id='+venueID+'&user_id='+userID,
			success: function(response){
	        	if(response == "1" || response == "2")
	        	{
	        		jQuery('#favourite-add').show();
	        		jQuery('#favourite-remove').hide();
	        	}
	        }
		})
		.done(function() {
		})
		.fail(function() {
		})
		.always(function() {
		});
	}
</script>
<div class="venuedetail clb-infowrp">
	<div class="venue-img">
		<div class="info-image-only">
			<?php if($this->club->is_video > 0): die;?>
				<video width="700" height="460" controls>
					<source src="<?php echo JUri::base().$this->club->venue_video_webm; ?>" type='video/webm;codecs="vp8, vorbis"'/>
					<source src="<?php echo JUri::base().$this->club->venue_video; ?>" type="video/mp4">
					Your browser does not support HTML5 video.
				</video>
			<?php elseif(!empty($this->club->image)):?>
				<img src="<?php echo JUri::base().'images/beseated/'.$this->club->image; ?>" alt="" />
			<?php else: ?>
				<img src="<?php echo JUri::base()."images/beseated/default/banner.png";?>" alt="" />
			<?php endif; ?>
		</div>
		<div class="rating-title">
			<div class="favourite-wrp span6">
					<button id="favourite-add" onclick="addVenueToFavourite('<?php echo $this->club->venue_id; ?>','<?php echo $this->user->id; ?>')" type="button" class="fav-btn"></button>
					<button id="favourite-remove" onclick="removeVenueFromFavourite('<?php echo $this->club->venue_id; ?>','<?php echo $this->user->id; ?>')" type="button" class="fav-btn active"></button>
				<?php if(!$this->isFavourite):?>
					<script type="text/javascript">
						jQuery('#favourite-add').show();
		        		jQuery('#favourite-remove').hide();
					</script>
				<?php else: ?>
					<script type="text/javascript">
						jQuery('#favourite-add').hide();
	        			jQuery('#favourite-remove').show();
	        		</script>
				<?php endif; ?>
			</div>
			<div class="rating-wrp span6">
				<?php $overallRating = $this->club->avg_ratting; ?>
            	<?php $starValue     = floor($this->club->avg_ratting); ?>
            	<?php $maxRating     = 5; ?>
            	<?php $printedStart  = 0 ;?>
				<?php for($i = 1; $i <= $starValue;$i++): ?>
					<i class="full"> </i>
					<?php $printedStart = $printedStart + 1; ?>
				<?php endfor; ?>
				<?php if($starValue<$overallRating): ?>
					<i class="half"> </i>
					<?php $printedStart = $printedStart + 1; ?>
				<?php endif; ?>
				<?php if($printedStart < $maxRating): ?>
					<?php for($i = $maxRating-$printedStart; $i > 0;$i--): ?>
						<i class="empty"> </i>
					<?php endfor; ?>
				<?php endif; ?>
			</div>
			<!-- <div class="category-wrp span6">
				<?php if($this->club->is_smart): ?>
					<a><img src="images/bcted/default/amenity_smart.png"></a>
				<?php endif; ?>
				<?php if($this->club->is_casual): ?>
					<a><img src="images/bcted/default/amenity_casual.png"></a>
				<?php endif; ?>
				<?php if($this->club->is_food): ?>
					<a><img src="images/bcted/default/amenity_food.png"></a>
				<?php endif; ?>
				<?php if($this->club->is_drink): ?>
					<a><img src="images/bcted/default/amenity_drink.png"></a>
				<?php endif; ?>
				<?php if($this->club->is_smoking): ?>
					<a><img src="images/bcted/default/amenity_smoking.png"></a>
				<?php else: ?>
					<a><img src="images/bcted/default/amenity_nosmoking.png"></a>
				<?php endif; ?>
			</div> -->
		<!-- 	<div class="sign-wrp span6">
			<?php if($this->club->venue_signs == 1): ?>
				<?php echo '$'; ?>
			<?php elseif($this->club->venue_signs == 2): ?>
				<?php echo '$$'; ?>
			<?php elseif($this->club->venue_signs == 3): ?>
				<?php echo '$$$'; ?>
			<?php endif; ?>
		</div> -->
		</div>
	</div>
	<div class="venue-text">
		<?php echo JText::_('Currency') . ': ' . $this->club->currency_sign;?>
	</div>
	<div class="venue-text">
		<?php echo JText::_('Location') . ': ' . $this->club->location;?>
	</div>
	<div class="venue-text">
		<?php echo JText::_('Type') . ': ' . $this->club->venue_type;?>
	</div>
	<div class="venue-text">
		<?php echo JText::_('Music') . ': ' . $this->club->music;?>
	</div>
	<div class="venue-text">
		<?php echo $this->club->description; ?>
	</div>
<!-- 	<div class="venue-text">
	<?php echo JText::_('COM_BCTED_CLUB_INFORMATION_WORKING_DAYS');?>
	<?php
		if(!empty($this->club->working_days))
		{
			$days = explode(",", $this->club->working_days);
			$workingDay = array();
			if(in_array(7, $days))
			{
				$workingDay[] = JText::_('COM_BCTED_SUNDAY');
			}
			if(in_array(1, $days))
			{
				$workingDay[] = JText::_('COM_BCTED_MONDAY');
			}
			if(in_array(2, $days))
			{
				$workingDay[] = JText::_('COM_BCTED_TUESDAY');
			}
			if(in_array(3, $days))
			{
				$workingDay[] = JText::_('COM_BCTED_WEDNESDAY');
			}
			if(in_array(4, $days))
			{
				$workingDay[] = JText::_('COM_BCTED_THURSDAY');
			}
			if(in_array(5, $days))
			{
				$workingDay[] = JText::_('COM_BCTED_FRIDAY');
			}
			if(in_array(6, $days))
			{
				$workingDay[] = JText::_('COM_BCTED_SATURDAY');
			}
			echo ' : '. implode(", ", $workingDay);
		}
	?>
</div> -->

		<div class="control-group">
			<div class="controls span12">
			<div class="venue-text">
				<?php echo JText::_('COM_BCTED_CLUB_INFORMATION_WORKING_DAYS');?>
			</div>
			<div class="four_days span6">
				<ul class="all_day_list">
					<?php if(!empty($this->club->working_days)): ?>
						<?php $days = explode(",", $this->club->working_days); ?>
					<?php else: ?>
						<?php $days = array(); ?>
					<?php endif; ?>
					<li>
						<label class="venueinfo" for="c1">Monday</label>
						<?php if(in_array(1, $days)): ?>
							<input disabled data-toggle="toggle" data-style="info success" data-size="normal" type="checkbox">
						<?php else: ?>
							<input disabled data-toggle="toggle" data-style="info danger" data-size="normal" type="checkbox">
						<?php endif; ?>
					</li>
					<li>
						<label class="venueinfo" for="c2">Tuesday</label>
						<?php if(in_array(2, $days)): ?>
							<input disabled data-toggle="toggle" data-style="info success" data-size="normal" type="checkbox">
						<?php else: ?>
							<input disabled data-toggle="toggle" data-style="info danger" data-size="normal" type="checkbox">
						<?php endif; ?>
					</li>
					<li>
						<label class="venueinfo" for="c3">Wednesday</label>
						<?php if(in_array(3, $days)): ?>
							<input disabled data-toggle="toggle" data-style="info success" data-size="normal" type="checkbox">
						<?php else: ?>
							<input disabled data-toggle="toggle" data-style="info danger" data-size="normal" type="checkbox">
						<?php endif; ?>
					</li>
					<li>
						<label class="venueinfo" for="c4">Thursday</label>
						<?php if(in_array(4, $days)): ?>
							<input disabled data-toggle="toggle" data-style="info success" data-size="normal" type="checkbox">
						<?php else: ?>
							<input disabled data-toggle="toggle" data-style="info danger" data-size="normal" type="checkbox">
						<?php endif; ?>
					</li>
				</ul>
			</div>
			<div class="three_days span6">
				<ul class="all_day_list">
					<li>
						<label class="venueinfo" for="c5">Friday</label>
						<?php if(in_array(5, $days)): ?>
							<input disabled data-toggle="toggle" data-style="info success" data-size="normal" type="checkbox">
						<?php else: ?>
							<input disabled data-toggle="toggle" data-style="info danger" data-size="normal" type="checkbox">

						<?php endif; ?>
					</li>
					<li>
						<label class="venueinfo" for="c6">Saturday</label>
						<?php if(in_array(6, $days)): ?>
							<input disabled data-toggle="toggle" data-style="info success" data-size="normal" type="checkbox">
						<?php else: ?>
							<input disabled data-toggle="toggle" data-style="info danger" data-size="normal" type="checkbox">
						<?php endif; ?>
					</li>
					<li>
						<label class="venueinfo" for="c6">Sunday</label>
						<?php if(in_array(7, $days)): ?>
							<input disabled data-toggle="toggle" data-style="info success" data-size="normal" type="checkbox">
						<?php else: ?>
							<input disabled data-toggle="toggle" data-style="info danger" data-size="normal" type="checkbox">
						<?php endif; ?>
					</li>
				</ul>
			</div>
			</div>
		</div>
</div>




