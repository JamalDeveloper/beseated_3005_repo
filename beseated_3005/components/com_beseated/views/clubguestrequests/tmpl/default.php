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
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$input        = JFactory::getApplication()->input;
$Itemid       = $input->get('Itemid', 0, 'int');
$this->user   = JFactory::getUser();
$venue        = BeseatedHelper::getUserElementID($this->user->id);
$this->isRoot = $this->user->authorise('core.admin');
?>
<div class="bct-summary-container">
	<div class="summary-list guest-list">
		<?php if (count($this->bookings) > 0):?>
			<?php foreach ($this->bookings as $key => $bookings):?>
				<div class="guest-list-details">
				    <a href="index.php?option=com_beseated&view=clubguestrequestsbydate&venue_id=<?php echo $venue->venue_id;?>&booking_date=<?php echo $bookings['bookingDate'];?>&Itemid=<?php echo $Itemid; ?>">
						<div class="guest-list-date"><?php echo date('M d,Y',strtotime($bookings['bookingDate']));?></div>
						<div class="guest-list-count"><?php echo $bookings['totalGuest'].'&nbsp;Guest(s)';?></div>
					</a>
				</div>
			<?php endforeach;?>
		<?php endif;?>
	</div>
</div>
<?php //echo $this->pagination->getListFooter(); ?>
