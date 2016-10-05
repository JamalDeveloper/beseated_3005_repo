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
$this->isRoot = $this->user->authorise('core.admin');
?>
<div class="table-wrp">
<H2><?php echo JText::_('COM_BESEATED_CLUB_TABLES_LIST_USER_SIDE_TABLES_VIEW_TITLE'); ?></H2>
<?php foreach ($this->items as $key => $item): ?>
	<div class="media">
		<div class="pull-left table-img span6">
			<?php if(!empty($item->image)): ?>
				<img src="<?php echo JUri::root().'images/beseated/'.$item->image; ?>">
			<?php else: ?>
				<img src="<?php echo JUri::root().'images/beseated/default/banner.png';?>">
			<?php endif; ?>
			<?php $link = JRoute::_('index.php?option=com_beseated&view=clubtablebooking&club_id='.$item->venue_id.'&venue_id='.$item->venue_id.'&table_id='.$item->table_id.'&Itemid='.$Itemid); ?>
        	<a class="book-tbl" href="<?php echo $link; ?>"><?php echo JText::_('COM_BESEATED_CLUB_TABLES_LIST_USER_SIDE_BOOK_BUTTON'); ?></a>
        </div>
		<div class="media-body span6">
			<div class="control-group">
				<h4 class="media-heading">
					<?php
						if($item->premium_table_id)
						{
							echo ucfirst($item->table_name);
						}
						else
						{
							echo ucfirst($item->table_name);
						}
					?>
				</h4>
				<h4 class="media-heading"></h4>
			</div>
			<div class="control-group">
				<h4 class="media-heading"><?php echo JText::_('COM_BESEATED_CLUB_TABLES_LIST_USER_SIDE_TABLE_CAPACITY') . ' : ' . $item->capacity; ?></h4>
				<h4 class="media-heading"><?php echo JText::_('COM_BESEATED_CLUB_TABLES_LIST_USER_SIDE_TABLE_PRICE') . ' : ' . BeseatedHelper::currencyFormat($item->currency_code,$item->currency_sign,$item->min_price); ?></h4>
			</div>
			<?php //echo $item->venue_table_description; ?>
		</div>
	</div>
<?php endforeach; ?>
</div>
