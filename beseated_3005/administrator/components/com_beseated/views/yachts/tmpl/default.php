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
<form action="index.php?option=com_beseated&view=yachts" method="post" id="adminForm" name="adminForm">

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
			<th width="2%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="4%" style="min-width:55px" class="nowrap">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_STATUS', 'published', $listDirn); ?>
			</th>
			<th width="25%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_NAME', 'yacht_name', $listDirn);?>
			</th>
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_ADDRESS', 'location', $listDirn);?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_ACCOUNT_CREATED', 'created', $listDirn);?>
			</th>
			<th width="10%">
				<?php echo JText::_('COM_BESEATED_COMPANY_REVENUE');?>
			</th>
			<th width="10%">
				<?php echo JText::_('COM_BESEATED_COMPANY_REFUND_POLICY_HOURS'); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_PROMOTION_MSG_SENT_COUNT', 'people_count', $listDirn);?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->yachts)) : ?>
				<?php foreach ($this->yachts as $i => $row) :
					$link = JRoute::_('index.php?option=com_beseated&view=yacht&layout=edit&yacht_id=' . $row->yacht_id);
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->yacht_id); ?>
						</td>
						<td width="2%" style="min-width:55px" class="">
							<?php echo JHtml::_('jgrid.published', $row->published, $i, 'yachts.', true, 'cb'); ?>
						</td>
						<td>
							<a href="<?php echo $link ?>">
								<?php echo $row->yacht_name; ?>
							</a>
						</td>
						<td class="">
							<?php echo $row->location; ?>
						</td>

						<td class="">
							<?php echo date('d-m-Y',strtotime($row->created)); ?>
						</td>
						<td class="">
							<?php if(isset($this->revenues[$row->yacht_id])): ?>
								<?php echo number_format($this->revenues[$row->yacht_id],2); ?>
							<?php else: ?>
								0.00
							<?php endif; ?>
						</td>
						<td class="">
							<?php echo $row->refund_policy; ?>
						</td>
						<!-- <td class="">
							<?php //echo $row->deposit_per; ?>
						</td> -->
						<td class="">
							<?php //echo $row->yacht_id; ?>
							<?php echo $row->people_count; ?>
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

