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
<form action="index.php?option=com_beseated&view=tabletypes" method="post" id="adminForm" name="adminForm">

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th width="5%"><?php echo JText::_('COM_BESEATED_NUM'); ?></th>
			<th width="4%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="4%" style="min-width:55px" class="nowrap center">
				<?php echo JHtml::_('grid.sort', 'COM_BESEATED_REWARD_STATUS', 'a.published', $listDirn, $listOrder); ?>
			</th>
			<th width="30%" class="nowrap center">
				<?php echo JHtml::_('grid.sort', 'COM_BESEATED_FORM_LBL_VENUE_TABLE_TYPE_NAME', 'a.table_type_name', $listDirn, $listOrder);?>
			</th>
			<th width="10%" class="nowrap center">
				<?php echo JHtml::_('grid.sort', 'COM_BESEATED_VENUE_TABLE_TYPE_ID', 'a.table_type_id', $listDirn, $listOrder); ?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :
					$link = JRoute::_('index.php?option=com_beseated&view=tabletype&layout=edit&table_type_id=' . $row->table_type_id);
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->table_type_id); ?>
						</td>
						<td width="2%" style="min-width:55px" class="nowrap">
							<?php echo JHtml::_('jgrid.published', $row->published, $i, 'tabletypes.', true, 'cb'); ?>
						</td>
						<td class="nowrap center">
							<a href="<?php echo $link ?>">
								<?php echo $row->table_type_name; ?>
							</a>
						</td>
						<td class="nowrap center">
							<?php echo $row->table_type_id; ?>
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

