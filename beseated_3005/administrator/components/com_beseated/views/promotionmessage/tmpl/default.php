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


?>

<form action="index.php?option=com_beseated&view=promotionmessage" method="post" id="adminForm" name="adminForm">

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
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROMOTION_ELEMENT_NAME', 'element_name', $listDirn);?>
			</th>
			<th width="30%" class="nowrap center">
			    <?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROMOTION_SUBJECT', 'subject', $listDirn);?>
			</th>
			<th width="30%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROMOTION_MESSAGE', 'message', $listDirn);?>
			</th>
			<th width="30%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROMOTION_CITY', 'city', $listDirn);?>
			</th>
			<th width="30%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROMOTION_SENT_MSG_PEOPLE_COUNT', 'people_count', $listDirn);?>
			</th>
			<th width="30%" class="nowrap center">
				<!-- <?php //echo JText::_('COM_BESEATED_CONCIERGES_ID'); ?> -->
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_PROMOTION_MSG_DATE', 'created', $listDirn);?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->promotionDetails)) : ?>

				<?php foreach ($this->promotionDetails as $i => $row) :
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td class="nowrap center">
							<?php echo $row->element_name; ?>
						</td>
						<td class="nowrap center">
							<?php echo $row->subject; ?>
						</td>
						<td class="nowrap center">
							<?php  echo $row->message; ?>
						</td>
						<td class="nowrap center">
							<?php echo $row->city; ?>
						</td>
						<td class="nowrap center">
							<?php echo $row->people_count; ?>
						</td>
						<td class="nowrap center">
							<?php  echo date('d-m-Y',strtotime($row->created)); ?>
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

