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
$caption             = $app->input->cookie->get('caption', '');
$near_me             = $app->input->cookie->get('near_me', 0);
$menuItem            = $menu->getItems( 'link', 'index.php?option=com_beseated&view=yachts', true );
$this->user          = JFactory::getUser();
$this->isRoot        = $this->user->authorise('core.admin');
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
		<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=yachts&Itemid='.$Itemid);?>" name="adminForm" method="post" class="form-inline" id="search_form">
		<div class="filter-bxwrp">
			<h3>Filter</h3>
			<div class="form-grp">
				<input type="text" name="caption" value="<?php echo $caption; ?>">
			</div>
			<div class="control-group flter-option">
					<div class="controls">
						<div class="control-group">
							<?php if($near_me): ?>
								<input type="checkbox" checked="checked" value="1" onchange="getLocation(this)" id="c2" name="near_me" />
							<?php else: ?>
								<input type="checkbox" value="1" onchange="getLocation(this)" id="c2" name="near_me" />
							<?php endif; ?>
							<label for="c2"><?php echo JText::_('COM_BESEATED_YACHTS_FILTER_YACHTS_NEAR_ME'); ?></label>
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
		<?php if ($this->user->id > 0){
					$bctParams        = BeseatedHelper::getExtensionParam();
					$accessLevel      = BeseatedHelper::getGroupAccessLevel($bctParams->beseated_guest);
					$access           = array('access','link');
					$property         = array($accessLevel,'index.php?option=com_beseated&view=protectioninformation');
					$menuItem        = $menu->getItems( $access, $property, true );
   				}else{
					$menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=protectioninformation', true );
   			}?>
			<?php foreach ($this->items as $key => $result):?>
				<?php $hasDisplay = 1;?>
				<div class="span4 venue_blck ">
					<div class="venue-img">
						<?php if(!empty($result->thumb_image)): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=yachtinformation&yacht_id='.$result->yacht_id.'&Itemid='.$menuItem->id ) ?>"><img src="<?php echo JUri::base().'images/beseated/'. $result->thumb_image; ?>" alt="" /></a>
						<?php elseif(!empty($result->image)): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=yachtinformation&yacht_id='.$result->yacht_id.'&Itemid='.$menuItem->id ) ?>"><img src="<?php echo JUri::base().'images/beseated/'. $result->image; ?>" alt="" /></a>
						<?php else: ?>
							<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=yachtinformation&yacht_id='.$result->yacht_id.'&Itemid='.$menuItem->id) ?>"><img src="images/bcted/default/banner.png" alt="" /></a>
						<?php endif; ?>
						<div class="rating-title">
							<div class="venue-title span6">
								<h4><?php echo $result->yacht_name; ?></h4>
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
<?php echo $this->pagination->getListFooter(); ?>


