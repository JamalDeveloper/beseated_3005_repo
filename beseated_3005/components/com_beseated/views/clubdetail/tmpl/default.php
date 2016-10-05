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

$input = JFactory::getApplication()->input;
$Itemid = $input->get('Itemid', 0, 'int');

?>
<div class="venuedetail">
	<div class="venue-img">
		<?php if(file_exists($this->club->venue_image)): ?>
			<img src="<?php echo $this->club->venue_image; ?>" alt="" />
		<?php else: ?>
			<img src="images/bcted/default/banner.png" alt="" />
		<?php endif; ?>
		<div class="rating-title">
			<div class="favourite-wrp span5">
            	<button type="button" class="fav-btn"></button>
			</div>
			<div class="rating-wrp span5">
				<?php for($i = 1; $i <= $this->club->venue_rating;$i++): ?>
					<i class="full"> </i>
				<?php endfor; ?>
				<?php for($i = $this->club->venue_rating+1; $i <= 5; $i++): ?>
					<i class="empty"> </i>
				<?php endfor; ?>
			</div>
			<div class="category-wrp span5">
				<a href="#"></a>
                <a href="#"></a>
                <a href="#"></a>
                <a href="#"></a>
			</div>
			<div class="sign-wrp span5">
				$$$
			</div>
		</div>
	</div>
	<div class="venue-text">
		<?php echo $this->club->venue_about; ?>
	</div>
</div>




