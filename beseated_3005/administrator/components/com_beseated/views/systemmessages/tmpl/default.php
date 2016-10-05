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
<form action="index.php?option=com_beseated&view=systemmessages" method="post" id="adminForm" name="adminForm">

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
			<th width="10%"><?php echo JText::_('COM_BESEATED_NUM'); ?></th>
			<th width="2%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="30%" class="nowrap">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_MESSAGES_MESSAGE', 'message', $listDirn);?>
			</th>
			<th width="30%" class="nowrap">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_MESSAGES_CITY', 'city', $listDirn);?>
			</th>
			<th width="20%" class="nowrap">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_MESSAGES_CREATED', 'created', $listDirn);?>
			</th>
			<th width="10%" class="nowrap">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_MESSAGES_ID', 'message_id', $listDirn); ?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :
					$link = JRoute::_('index.php?option=com_beseated&view=systemmessage&layout=edit&message_id=' . $row->message_id);
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->message_id); ?>
						</td>
						<td>
							<?php echo $row->message; ?>
						</td>
						<td class="nowrap">
							<?php /* if(strlen($row->location) >= 50): ?>
								<?php  echo substr($row->location,0,50).'....'; ?>
							<?php else: ?>
								<?php  echo $row->location; ?>
							<?php endif; */ ?>
							<?php  echo $row->city; ?>
						</td>
						<td class="nowrap">
							<?php echo date('d-m-Y',strtotime($row->created));?>
						</td>
						<td class="nowrap">
							<?php echo $row->message_id; ?>
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

