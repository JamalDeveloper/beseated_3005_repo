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

$input        = JFactory::getApplication()->input;
$Itemid       = $input->get('Itemid', 0, 'int');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
?>

<section class="page-section venue-reservations">
	<div class="container">
		<h2 class="heading-1"><?php echo $this->club->venue_name; ?></h2>
		<div class="sub-menu">
			<?php foreach (JModuleHelper::getModules('position-8') as $module) { 
		 		echo JModuleHelper::renderModule($module); 
			} ?>
		</div>
		<div class="row venue-tables">
			<?php foreach ($this->items as $key => $item):
			$image = $item->image ?: $result->image;
			 ?>
				<div class="col-md-8 col-md-offset-2 venue-table">
					<div class="row">
						<div class="col-md-6">
							<div class="image" style="background-image: url(<?php echo $image ? JUri::base().'images/beseated/'.$image : 'images/bcted/default/banner.png' ?>);">								
							</div>							
						</div>
						<div class="col-md-6">
							<h3 class="heading-1"><?php echo $item->table_name; ?></h3>
							<p class="capacity"><?php echo $item->capacity; ?> PPL</p>
							<p class="price"><?php echo BeseatedHelper::currencyFormat($item->currency_code,$item->currency_sign,$item->min_price); ?></p>
							<hr>
							<a href="<?php echo JRoute::_('index.php?option=com_beseated&view=clubtablebooking&club_id='.$item->venue_id.'&venue_id='.$item->venue_id.'&table_id='.$item->table_id.'&Itemid='.$Itemid); ?>" class="button">
								Beseated
							</a>							
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
