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
<form action="index.php?option=com_beseated&view=privatejetbookings" method="post" id="adminForm" name="adminForm">

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
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_BOOKING_COMPANY_NAME','company_name',$listDirn);?>
			</th>
			<th width="25%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_BOOKING_FULL_NAME', 'person_name', $listDirn);?>
			</th>
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_BOOKING_DATE','created',$listDirn);?>
			</th>
			<th width="5%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_BOOKING_DEPARTURE_DATE','flight_date',$listDirn);?>
			</th>
			<th width="5%">
				<?php echo JText::_('COM_BESEATED_PRIVATE_JET_BOOKING_DEPARTURE_TIME');?>
			</th>
			<th width="5%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_BOOKING_RETURN_DEPARTURE_DATE','return_flight_date',$listDirn);?>
			</th>
			<th width="5%">
				<?php echo JText::_('COM_BESEATED_PRIVATE_JET_BOOKING_RETURN_DEPARTURE_TIME');?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_BOOKING_EMAIL','email',$listDirn);?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_BOOKING_TOTAL_GUEST','total_guest',$listDirn);?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_BOOKING_FROM_LOCATION','from_location',$listDirn);?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_BOOKING_TO_LOCATION','to_location',$listDirn);?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_BOOKING_DESCRIPTION','extra_information',$listDirn);?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :
					$link = JRoute::_('index.php?option=com_beseated&view=privatejetbooking&layout=edit&private_jet_booking_id=' . $row->private_jet_booking_id);
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<!-- <a href="<?php //echo $link ?>"><?php echo ucfirst($row->company_name); ?></a> -->
							<?php echo ucfirst($row->company_name); ?>
						</td>
						<td>
							<?php echo ucfirst($row->person_name); ?>
						</td>
						<td class="nowrap">
							<?php echo date('d-m-Y',strtotime($row->created)); ?>
						</td>
						<td class="nowrap">
							<?php echo date('d-m-Y',strtotime($row->flight_date)); ?>
						</td>
						<td class="nowrap">
							<?php echo date('H:i',strtotime($row->flight_time)); ?>
						</td>
						<td class="nowrap">
							<?php
							$return_flight_date = ($row->return_flight_date == '0000-00-00') ? '-' : date('d-m-Y',strtotime($row->return_flight_date));
							echo $return_flight_date; ?>
						</td>
						<td class="nowrap">
							<?php
							$return_flight_time = ($row->return_flight_time) ? date('H:i',strtotime($row->return_flight_time)) : '-';
							echo $return_flight_time;
							?>
						</td>
						<td class="nowrap">
							<?php echo $row->email; ?>
						</td>
						<td class="nowrap">
							<?php echo $row->total_guest; ?>
						</td>
						<td class="nowrap">
							<?php echo $row->from_location; ?>
						</td>
						<td class="nowrap">
							<?php echo $row->to_location; ?>
						</td>
						<td class="nowrap">
							<?php echo $row->extra_information; ?>
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

