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

<script type="text/javascript">

	jQuery(document).ready(function()
	{
		jQuery('#contacted').change(function()
		{
			var user_ID = jQuery('#contacted').val();

			if (this.checked)
			{
				var checkBoxVal = 1;

        		jQuery.ajax({
					url: 'index.php?option=com_beseated&task=birthdays.contacted',
					type: 'GET',
					data: 'user_id='+user_ID,
					success: function(response){
							 location.reload();

			        }
				});
    		}

		})


	});

</script>

<form action="index.php?option=com_beseated&view=birthdays" method="post" id="adminForm" name="adminForm">

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
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_USER_FULL_NAME', 'name', $listDirn);?>
			</th>
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_USER_EMAIL_ID', 'email', $listDirn);?>
			</th>
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_USER_BIRTHDATE', 'birthdate', $listDirn);?>
			</th>
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_USER_PHONE_NO', 'phone', $listDirn);?>
			</th>
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_ACCOUNT_CREATED', 'registerDate', $listDirn);?>
			</th>
			<th width="15%">
				<?php echo JText::_('COM_BESEATED_USER_CONTACTED');?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :

				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td width="2%" style="min-width:55px" class="">
							<?php echo $row->name; ?>
						</td>
						<td>
							<?php echo $row->email; ?>
						</td>
						<td class="">
							<?php echo date('d-m-Y',strtotime($row->birthdate)); ?>
						</td>
						<td class="">
							<?php echo $row->phone; ?>
						</td>
						<td class="">
							<?php echo date('d-m-Y',strtotime($row->registerDate)); ?>
						</td>
						<td class="">
							<input type="checkbox"  id="contacted" value="<?php echo $row->id; ?>">
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

