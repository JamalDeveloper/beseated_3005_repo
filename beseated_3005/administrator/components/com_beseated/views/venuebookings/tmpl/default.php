<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');
JHtml::_('script', 'system/core.js', false, true);

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
?>
<form action="index.php?option=com_beseated&view=venuebookings" method="post" id="adminForm" name="adminForm">

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
<?php
	// Search tools bar
	echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
?>
	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th width="4%"><?php echo JText::_('COM_BESEATED_NUM'); ?></th>
			<th width="5%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_VENUE_BOOKING_VENUE_NAME','venue_name',$listDirn);?>
			</th>
			<th width="5%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_VENUE_BOOKING_TABLE_NAME','table_name',$listDirn);?>
			</th>
			<?php /*<th width="2%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th> */ ?>
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_VENUE_BOOKING_FULL_NAME', 'full_name', $listDirn);?>
			</th>

			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_VENUE_BOOKING_BOOKING_DATE','booking_date',$listDirn);?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_VENUE_BOOKING_GUEST','total_guest',$listDirn);?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_VENUE_BOOKING_CURRENCY','booking_currency_code',$listDirn);?>
			</th>
			<th width="10%">
				<?php echo JText::_('COM_BESEATED_VENUE_BOOKING_TOTAL_PRICE'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :

					$link = JRoute::_('index.php?option=com_beseated&view=venuebooking&layout=edit&venue_table_booking_id=' . $row->venue_table_booking_id);
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<?php /*<td>
							<?php echo JHtml::_('grid.id', $i, $row->protection_booking_id); ?>
						</td> */?>
						<td class="">
							<?php echo ucfirst($row->venue_name); ?>
						</td>
						<td class="">
							<?php echo ucfirst($row->table_name); ?>
						</td>
						<td>
							<?php echo ucfirst($row->full_name); ?>
						</td>

						<td class="">
							<?php echo date('d-m-Y',strtotime($row->booking_date)); ?>
						</td>
						<td class="">
							<?php echo $row->total_guest. '('.$row->male_guest.'M/'.$row->female_guest.'F)'; ?>
						</td>
						<td class="">
							<?php echo strtoupper($row->booking_currency_code); ?>
						</td>
						<td class="">
							<?php echo ($row->is_bill_posted) ? number_format($row->bill_post_amount) : number_format($row->total_price+$row->pay_deposite,0); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<?php echo $this->pagination->getListFooter(); ?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
