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
<form action="index.php?option=com_beseated&view=privatejets" method="post" id="adminForm" name="adminForm">

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
			<th width="2%" style="min-width:55px" class="nowrap">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_PUBLISHED', 'published', $listDirn); ?>
			</th>
			<th width="25%" class="nowrap">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_NAME', 'company_name', $listDirn);?>
			</th>
			<th width="30%" class="nowrap">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_USER_EMAIL_ID', 'owner_email', $listDirn);?>
			</th>
			<!-- <th width="10%" class="nowrap">
				<?php //echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_CITY', 'city', $listDirn);?>
			</th>
			<th width="3%" class="nowrap">
				<?php //echo JHtml::_('searchtools.sort', 'COM_BESEATED_PRIVATE_JET_AVERAGE_RATTING', 'avg_ratting', $listDirn);?>
			</th> -->
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :
					$link = JRoute::_('index.php?option=com_beseated&view=privatejet&layout=edit&private_jet_id=' . $row->private_jet_id);
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->private_jet_id); ?>
						</td>
						<td width="2%" style="min-width:55px" class="">
							<?php echo JHtml::_('jgrid.published', $row->published, $i, 'privatejets.', true, 'cb'); ?>
						</td>
						<td>
							<a href="<?php echo $link ?>"><?php echo $row->company_name; ?></a>
						</td>
						<td class="">
							<?php echo $row->owner_email; ?>
						</td>
						<!-- <td class="">
							<?php //echo $row->city; ?>
						</td>
						<td class="">
							<?php //echo $row->avg_ratting; ?>
						</td> -->

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

