<?php
/**
 * @package     Besated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$user		= JFactory::getUser();
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=musictables'); ?>" method="post" id="adminForm" name="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>

<?php
	// Search tools bar
	echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
?>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th width="2%"><?php echo JText::_('COM_BESEATED_NUM'); ?></th>
				<th width="2%">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th width="2%" style="min-width:55px" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'published', $listDirn); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_MUSIC_NAME_CAPTION', 'music_name', $listDirn);?>
				</th>

				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_MUSIC_HEADING_CREATED', 'created', $listDirn);?></th>
				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_MUSIC_HEADING_ID', 'music_id', $listDirn);?></th>
			</tr>
		</thead>

		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :
					$link = JRoute::_('index.php?option=com_beseated&view=musictable&layout=edit&music_id=' . $row->music_id); ?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->music_id); ?>
						</td>
						<td class="center">
							<div class="btn-group">
								<?php if($row->published): ?>
									<a title="" href="<?php echo JRoute::_('index.php?option=com_beseated&task=musictable.unpublish&cid='.$row->music_id); ?>" class="btn btn-micro hasTooltip" data-original-title="Unpublish Item"><span class="icon-publish"></span></a>
								<?php else: ?>
									<a title="" href="<?php echo JRoute::_('index.php?option=com_beseated&task=musictable.publish&cid='.$row->music_id); ?>" class="btn btn-micro hasTooltip" data-original-title="Publish Item"><span class="icon-unpublish"></span></a>
								<?php endif; ?>
							</div>
						</td>

						<td><a href="<?php echo $link ?>"><?php echo $row->music_name; ?></a></td>

						<td><?php echo date('d-m-Y',strtotime($row->created)); ?></td>
						<td><?php echo $row->music_id; ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
	</table>
	<?php //echo $this->pagination->getListFooter(); ?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>