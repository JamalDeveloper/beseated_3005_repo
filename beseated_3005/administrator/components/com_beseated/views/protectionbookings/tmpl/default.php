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
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
?>
<form action="index.php?option=com_beseated&view=protectionbookings" method="post" id="adminForm" name="adminForm">

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
			<!-- <th width="2%">
				<?php //echo JHtml::_('grid.checkall'); ?>
			</th> -->
			<th width="25%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_NAME', 'protection_name', $listDirn);?>
			</th>
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROTECTION_BOOKING_PROTECTION_TYPE','service_name',$listDirn);?>
			</th>
			<th width="25%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROTECTION_BOOKING_FULL_NAME', 'full_name', $listDirn);?>
			</th>

			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROTECTION_BOOKING_BOOKING_DATE','booking_date',$listDirn);?>
			</th>

			<th width="5%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROTECTION_BOOKING_HOURS','total_hours',$listDirn);?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROTECTION_BOOKING_TOTAL_GUARD','total_guard',$listDirn);?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROTECTION_BOOKING_MEETUP_LOCATION','meetup_location',$listDirn);?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROTECTION_BOOKING_CURRENCY','booking_currency_code',$listDirn);?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROTECTION_BOOKING_BILL_AMOUNT','total_price',$listDirn);?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :
					$link = JRoute::_('index.php?option=com_beseated&view=protectionbooking&layout=edit&protection_booking_id=' . $row->protection_booking_id);
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<!-- <td>
							<?php //echo JHtml::_('grid.id', $i, $row->protection_booking_id); ?>
						</td> -->
						<td>
							<?php echo ucfirst($row->protection_name); ?>
						</td>
						<td class="nowrap">
							<?php echo ucfirst($row->service_name); ?>
						</td>
						<td>
							<!-- <a href="<?php// echo $link ?>"><?php //echo ucfirst($row->full_name); ?></a> -->
							<?php echo ucfirst($row->full_name); ?>
						</td>

						<td class="nowrap">
							<?php echo date('d-m-Y',strtotime($row->booking_date)); ?>
						</td>

						<td class="nowrap">
							<?php echo $row->total_hours; ?>
						</td>
						<td class="nowrap">
							<?php echo $row->total_guard; ?>
						</td>
						<td class="nowrap">
							<?php echo $row->meetup_location; ?>
						</td>
						<td class="nowrap">
							<?php echo $row->booking_currency_code; ?>
						</td>
						<td class="nowrap">
							<?php echo number_format($row->total_price,0); ?>
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

