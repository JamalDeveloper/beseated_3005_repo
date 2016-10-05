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
<form action="index.php?option=com_beseated&view=events" method="post" id="adminForm" name="adminForm">

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
			<th width="2%"><?php echo JText::_('COM_BESEATED_NUM'); ?></th>
			<th width="4%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="2%" style="min-width:55px" class="nowrap center">
				<!-- <?php //echo JHtml::_('grid.sort', 'COM_BESEATED_EVENT_PUBLISHED', 'a.published', $listDirn, $listOrder); ?> -->
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_STATUS', 'published', $listDirn); ?>
			</th>
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
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_EVENT_LOCATION', 'location', $listDirn);?>
			</th>
			<th width="10%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_EVENT_CITY', 'city', $listDirn);?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :
					$link = JRoute::_('index.php?option=com_beseated&view=event&layout=edit&event_id=' . $row->event_id);
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->event_id); ?>
						</td>
						<td width="2%" style="min-width:55px" class="nowrap">
							<?php echo JHtml::_('jgrid.published', $row->published, $i, 'events.', true, 'cb'); ?>
						</td>
						<td class="nowrap center">
							<a href="<?php echo $link ?>" ><?php echo $row->event_name; ?></a>
						</td>
						<td class="nowrap center">
							<?php echo date('d-m-Y',strtotime($row->event_date)); ?>
						</td>
						<td class="nowrap center">
							<?php echo $row->event_time; ?>
						</td>
						<td class="nowrap center">
							<?php if(strlen($row->location) >= 50): ?>
								<?php  echo substr($row->location,0,50).'....'; ?>
							<?php else: ?>
								<?php  echo $row->location; ?>
							<?php endif; ?>
						</td>
						<td class="nowrap center">
							<?php echo $row->city; ?>
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

