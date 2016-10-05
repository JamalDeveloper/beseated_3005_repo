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

$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
$canOrder	    = $user->authorise('core.edit.state', 'com_beseated');



?>
<form action="index.php?option=com_beseated&view=ticketbookings" method="post" id="adminForm" name="adminForm">

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
			<th width="5%"><?php echo JText::_('COM_BESEATED_NUM'); ?></th>
			<th width="25%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_EVENT_NAME', 'event_name', $listDirn);?>
			</th>
			<th width="12%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_EVENT_DATE', 'event_date', $listDirn);?>
			</th>
			<th width="12%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_EVENT_TIME_LABEL', 'event_time', $listDirn);?>
			</th>
			<th width="30%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_TICKET_BOOKING_USER', 'full_name', $listDirn);?>
			</th>
			<th width="30%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_TOTAL_TICKET', 'total_ticket', $listDirn);?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php

				$ordering	= ($listOrder == 'a.ordering');

	//		$orderkey = array_search($item->id, $this->ordering[$item->testmeid]);
			$canCreate	= $user->authorise('core.create',		'com_beseated');
			$canEdit	= $user->authorise('core.edit',			'com_beseated');
			$canCheckin	= $user->authorise('core.manage',		'com_beseated');
			$canChange	= $user->authorise('core.edit.state',	'com_beseated');


				?>
				<?php foreach ($this->items as $i => $row) :

					$link = JRoute::_('index.php?option=com_beseated&view=ticketbooking&layout=edit&event_id=' . $row->event_id.'&user_id='.$row->user_id.'&ticket_booking_id='.$row->ticket_booking_id);
					$event_link = JRoute::_('index.php?option=com_beseated&view=event&layout=edit&event_id=' . $row->event_id);
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>

						<td class="nowrap center">
							<a href="<?php echo $event_link ?>"><?php echo $row->event_name; ?></a>
						</td>
						<td class="nowrap center">
							<?php echo date('d-m-Y',strtotime($row->event_date)); ?>
						</td>
						<td class="nowrap center">
							<?php echo $row->event_time; ?>
						</td>
						<td class="nowrap center">
							<?php echo $row->full_name; ?></a>
						</td>
						<td class="nowrap center">
							<a href="<?php echo $link ?>"><?php  echo $row->total_ticket; ?></a>
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

