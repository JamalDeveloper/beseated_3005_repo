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

$input               = JFactory::getApplication()->input;
$Itemid              = $input->get('Itemid', 0, 'int');
$app                 = JFactory::getApplication();
$menu                = $app->getMenu();
$prvCity             = $app->input->cookie->get('search_in_city', '', 'RAW');
$inner_search        = $app->input->cookie->get('inner_search', '');
$myfriends_attending = $app->input->cookie->get('myfriends_attending', 0);
$caption             = $app->input->cookie->get('caption', '');
$near_me             = $app->input->cookie->get('near_me', 0);
$is_smart            = $app->input->cookie->get('is_smart', 0);
$is_casual           = $app->input->cookie->get('is_casual', 0);
$is_food             = $app->input->cookie->get('is_food', 0);
$is_drink            = $app->input->cookie->get('is_drink', 0);
$is_smoking          = $app->input->cookie->get('is_smoking', 0);
$is_ratting          = $app->input->cookie->get('is_ratting', 0);
$is_costly           = $app->input->cookie->get('is_costly', 0);

if($is_smart)
{
	$is_smart_css = "search-smart-attire-btn";
}
else if($is_casual)
{
	$is_smart_css = "search-casual-attire-btn";
}
else
{
	$is_smart_css = "search-attire-gone";
}

if($is_food)
{
	$is_food_css = "search-smart-category-btn";
}
else if($is_drink)
{
	$is_food_css = "search-casual-category-btn";
}
else
{
	$is_food_css = "search-category-gone";
}

if($is_smoking == 1)
{
	$is_smoking_css = "search-smoking-btn";
}
else
{
	$is_smoking_css = "search-no-smoking-btn";
}
$is_ratting_css = "search-ratting-btn".$is_ratting;
$is_costly_css  = "search-costly-btn".$is_costly;
$menuItem       = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubs', true );
$this->user     = JFactory::getUser();
$this->isRoot   = $this->user->authorise('core.admin');
?>
<?php $hasDisplay = 0; ?>
<script type="text/javascript">
	function getLocation(element)
	{
		if(element.checked)
		{
			if (navigator.geolocation) {
		        navigator.geolocation.getCurrentPosition(showPosition);
		    } else {
		       document.cookie="latitude=0.00";
		    	document.cookie="longitude=0.00";
		    }
		}
	}

	function showPosition(position) {
		latitude = position.coords.latitude;
		longitude = position.coords.longitude;
	    document.cookie="latitude="+latitude.toFixed(5);
	    document.cookie="longitude="+longitude.toFixed(5);
	}
</script>
<div class="wrapper">
	<button class="filter-btn" type='button'  id='fltr-icn' value='hide/show'></button>
		<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=clubs&Itemid='.$Itemid);?>" name="adminForm" method="post" class="form-inline" id="search_form">
		<div class="filter-bxwrp">
			<h3>Filter</h3>
			<div class="form-grp">
				<input type="text" name="caption" value="<?php echo $caption; ?>">
			</div>
			<div class="city-dd">
				<!-- <select id="provincesList1" name="country">
					<option value=""><?php echo JText::_('COM_BESEATED_CLUBS_FILTER_VENUES_CITY'); ?></option>
					<?php foreach ($this->cityList as $key => $city): ?>
						<?php if(strtolower($prvCity) == strtolower($city)): ?>
							<option selected="selected" value="<?php echo ucfirst($city); ?>"> <?php echo ucfirst($city); ?> </option>
						<?php else: ?>
							<option value="<?php echo ucfirst($city); ?>"> <?php echo ucfirst($city); ?> </option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select> -->
			</div>
			<!-- <table>
				<tr>
					<td>
						<input id="smart_casual" class="<?php echo $is_smart_css; ?>" type="button">
						<input id="is_smart" type="hidden" value="<?php echo $is_smart; ?>" name="is_smart">
						<input id="is_casual" type="hidden" value="<?php echo $is_casual; ?>" name="is_casual">
					</td>
					<td rowspan="2" align="center" valign="middle" class="centr-img">
						<input id="food_drink" class="<?php echo $is_food_css; ?>" type="button">
						<input id="is_food" type="hidden" value="<?php echo $is_food; ?>" name="is_food">
						<input id="is_drink" type="hidden" value="<?php echo $is_drink; ?>" name="is_drink">
					</td>
					<td align="right">
						<input id="smoking_nosmoking" class="<?php echo $is_smoking_css; ?>" type="button">
						<input id="is_smoking" type="hidden" value="<?php echo $is_smoking; ?>" name="is_smoking">
					</td>
				</tr>
				<tr>
					<td>
						<input id="ratting_yesno" class="<?php echo $is_ratting_css; ?>" type="button">
						<input id="is_ratting" type="hidden" value="<?php echo $is_ratting; ?>" name="is_ratting">
					</td>
					<td align="right">
						<input id="costly_yesno" class="<?php echo $is_costly_css; ?>" type="button">
						<input id="is_costly" type="hidden" value="<?php echo $is_costly; ?>" name="is_costly">
					</td>
				</tr>
			</table> -->
			<div class="control-group flter-option">
					<div class="controls">
						<?php if($this->user->id): ?>
						<div class="control-group">
							<?php if($myfriends_attending): ?>
								<input type="checkbox" checked="checked" value="1" id="c1" name="myfriends_attending" />
							<?php else: ?>
								<input type="checkbox" value="1" id="c1" name="myfriends_attending" />
							<?php endif; ?>
							<label for="c1"> <?php echo JText::_('COM_BESEATED_CLUBS_FILTER_VENUES_FRIENDS_ATTENDING'); ?></label>
						</div>
						<?php endif; ?>
						<div class="control-group">
							<?php if($near_me): ?>
								<input type="checkbox" checked="checked" value="1" onchange="getLocation(this)" id="c2" name="near_me" />
							<?php else: ?>
								<input type="checkbox" value="1" onchange="getLocation(this)" id="c2" name="near_me" />
							<?php endif; ?>
							<label for="c2"><?php echo JText::_('COM_BESEATED_CLUBS_FILTER_VENUES_VENUES_NEAR_ME'); ?></label>
						</div>
					</div>
			</div>
			<div class="control-group">
				<input type="hidden" name="<?php echo $this->serachType; ?>" value="<?php echo $this->serachType; ?>">
				<input type="hidden" name="inner_search" value="1">
				<button type="submit" name="<?php echo $this->serachType; ?>" value="<?php echo $this->serachType; ?>" class="ok-btn small btn btn-block"></button>
			</div>
		</div>
		</form>
   	<div class="span12 filter-result">
		<?php if($this->serachType == 'club'): ?>
			<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubinformation', true );?>
			<?php foreach ($this->items as $key => $result):?>
				<?php $hasDisplay = 1;?>
				<div class="span4 venue_blck ">
					<div class="venue-img">
						<?php if(!empty($result->thumb_image)): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubinformation&club_id='.$result->venue_id.'&Itemid='.$menuItem->id ) ?>"><img src="<?php echo JUri::base().'images/beseated/'. $result->thumb_image; ?>" alt="" /></a>
						<?php elseif(!empty($result->image)): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubinformation&club_id='.$result->venue_id.'&Itemid='.$menuItem->id ) ?>"><img src="<?php echo JUri::base().'images/beseated/'. $result->image; ?>" alt="" /></a>
						<?php else: ?>
							<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubinformation&club_id='.$result->venue_id.'&Itemid='.$menuItem->id) ?>"><img src="images/bcted/default/banner.png" alt="" /></a>
						<?php endif; ?>
						<div class="rating-title">
							<div class="venue-title span6">
								<h4><?php echo $result->venue_name; ?></h4>
								<div class="venue-location"><?php echo $result->location; ?></div>
							</div>
                            <div class="rating-wrp span6">
                            	<?php $overallRating = $result->avg_ratting; ?>
                            	<?php $starValue = floor($result->avg_ratting); ?>
                            	<?php $maxRating = 5; ?>
                            	<?php $printedStart = 0 ;?>
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
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php elseif($this->serachType == "service"): ?>
			<?php foreach ($this->items as $key => $result): ?>
				<?php $hasDisplay = 1; ?>
				<div class="span4 venue_blck ">
					<div class="venue-img">
						<?php if(file_exists($result->company_image)): ?>
							<img src="<?php echo $result->company_image; ?>" alt="" />
						<?php else: ?>
							<img src="images/bcted/default/banner.png" alt="" />
						<?php endif; ?>

						<div class="rating-title">
							<div class="venue-title span6">
								<h4><?php echo $result->company_name; ?></h4>
								<div class="venue-location"><?php echo $result->company_address; ?></div>
							</div>
                            <div class="rating-wrp span6">
								<?php for($i = 1; $i <= floor($result->company_rating);$i++): ?>
									<i class="full"> </i>
								<?php endfor; ?>
								<?php for($i = floor($result->company_rating)+1; $i <= 5; $i++): ?>
									<i class="empty"> </i>
								<?php endfor; ?>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if($hasDisplay == 0): ?>
		<div id="system-message">
			<div class="alert alert-block">
				<button type="button" class="close" data-dismiss="alert">&times;</button>
				<h4><?php echo JText::_('COM_BCTED_USERBOOKINGS_NO_RESULT_FOUND_TITLE'); ?></h4>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
<script type="text/javascript">
	jQuery('#smart_casual').on('click',function(){
		if (jQuery(this).hasClass('search-smart-attire-btn'))
		{
			jQuery('#is_smart').val('0');
			jQuery('#is_casual').val('1');
			jQuery(this).removeClass('search-smart-attire-btn');
			jQuery(this).addClass(' search-casual-attire-btn');
		}
		else if (jQuery(this).hasClass('search-casual-attire-btn'))
		{
			jQuery('#is_smart').val('0');
			jQuery('#is_casual').val('0');
			jQuery(this).removeClass('search-casual-attire-btn');
			jQuery(this).addClass(' search-attire-gone');
		}
		else if (jQuery(this).hasClass('search-attire-gone'))
		{
			jQuery('#is_smart').val('1');
			jQuery('#is_casual').val('0');
			jQuery(this).removeClass('search-attire-gone');
			jQuery(this).addClass(' search-smart-attire-btn');
		}
	});

	jQuery('#food_drink').on('click',function(){
		if (jQuery(this).hasClass('search-smart-category-btn'))
		{
			jQuery('#is_food').val('0');
			jQuery('#is_drink').val('1');
			jQuery(this).removeClass('search-smart-category-btn');
			jQuery(this).addClass(' search-casual-category-btn');
		}
		else if (jQuery(this).hasClass('search-casual-category-btn'))
		{
			jQuery('#is_food').val('0');
			jQuery('#is_drink').val('0');
			jQuery(this).removeClass('search-casual-category-btn');
			jQuery(this).addClass(' search-category-gone');
		}
		else if (jQuery(this).hasClass('search-category-gone'))
		{
			jQuery('#is_food').val('1');
			jQuery('#is_drink').val('0');
			jQuery(this).removeClass('search-category-gone');
			jQuery(this).addClass(' search-smart-category-btn');
		}
	});
	jQuery('#smoking_nosmoking').on('click',function(){
		if (jQuery(this).hasClass('search-smoking-btn'))
		{
			jQuery('#is_smoking').val('2');
			jQuery(this).removeClass('search-smoking-btn');
			jQuery(this).addClass(' search-no-smoking-btn');
		}
		else
		{
			jQuery('#is_smoking').val('1');
			jQuery(this).removeClass('search-no-smoking-btn');
			jQuery(this).addClass(' search-smoking-btn');
		}
	});
	jQuery('#ratting_yesno').on('click',function(){
		if (jQuery(this).hasClass('search-ratting-btn1'))
		{
			jQuery('#is_ratting').val('2');
			jQuery(this).removeClass('search-ratting-btn1');
			jQuery(this).addClass(' search-ratting-btn2');
		}
		else if (jQuery(this).hasClass('search-ratting-btn2'))
		{
			jQuery('#is_ratting').val('3');
			jQuery(this).removeClass('search-ratting-btn2');
			jQuery(this).addClass(' search-ratting-btn3');
		}
		else if (jQuery(this).hasClass('search-ratting-btn3'))
		{
			jQuery('#is_ratting').val('4');
			jQuery(this).removeClass('search-ratting-btn3');
			jQuery(this).addClass(' search-ratting-btn4');
		}
		else if (jQuery(this).hasClass('search-ratting-btn4'))
		{
			jQuery('#is_ratting').val('5');
			jQuery(this).removeClass('search-ratting-btn4');
			jQuery(this).addClass(' search-ratting-btn5');
		}
		else if (jQuery(this).hasClass('search-ratting-btn5'))
		{
			jQuery('#is_ratting').val('0');
			jQuery(this).removeClass('search-ratting-btn5');
			jQuery(this).addClass(' search-ratting-btn0');
		}
		else if (jQuery(this).hasClass('search-ratting-btn0'))
		{
			jQuery('#is_ratting').val('1');
			jQuery(this).removeClass('search-ratting-btn0');
			jQuery(this).addClass(' search-ratting-btn1');
		}
	});
	jQuery('#costly_yesno').on('click',function(){
		if (jQuery(this).hasClass('search-costly-btn1'))
		{
			jQuery('#is_costly').val('2');
			jQuery(this).removeClass('search-costly-btn1');
			jQuery(this).addClass(' search-costly-btn2');
		}
		else if (jQuery(this).hasClass('search-costly-btn2'))
		{
			jQuery('#is_costly').val('3');
			jQuery(this).removeClass('search-costly-btn2');
			jQuery(this).addClass(' search-costly-btn3');
		}
		else if (jQuery(this).hasClass('search-costly-btn3'))
		{
			jQuery('#is_costly').val('0');
			jQuery(this).removeClass('search-costly-btn3');
			jQuery(this).addClass(' search-costly-btn0');
		}
		else if (jQuery(this).hasClass('search-costly-btn0'))
		{
			jQuery('#is_costly').val('1');
			jQuery(this).removeClass('search-costly-btn0');
			jQuery(this).addClass(' search-costly-btn1');
		}

	});
</script>
<?php echo $this->pagination->getListFooter(); ?>


