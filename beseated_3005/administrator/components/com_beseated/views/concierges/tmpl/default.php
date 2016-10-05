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

$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
$canOrder	    = $user->authorise('core.edit.state', 'com_beseated');
?>

<script type="text/javascript">

	function isDefault (value,concierge_id)
	{
		jQuery.ajax({
		      url: "index.php?option=com_beseated&view=concierges&task=concierges.isDefault&value="+value+"&concierge_id="+concierge_id
		    }).done(function(data)
		    {
		      console.log(data);

		      if(data == '0')
		      {
		      	alert("Please Select another concierge default set");
		      	window.location = 'index.php?option=com_beseated&view=concierges';
		      }
		      else
		      {
		      	 window.location = data;
		      }


		    });
	}
</script>

<form action="index.php?option=com_beseated&view=concierges" method="post" id="adminForm" name="adminForm">

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
			<th width="2%">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>
			<th width="25%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_CONCIERGES_CITY', 'city', $listDirn);?>
			</th>
			<th width="30%" class="nowrap center">
			    <?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_CONCIERGES_CONTACT_NO', 'phone_no', $listDirn);?>
			</th>
			<th width="30%" class="nowrap center">
			    <?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_CONCIERGES_IS_DEFAULT', 'is_default', $listDirn);?>
			</th>
			<th width="30%" class="nowrap center">
				<!-- <?php //echo JText::_('COM_BESEATED_CONCIERGES_ID'); ?> -->
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_CONCIERGES_ID', 'concierge_id', $listDirn);?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>

				<?php foreach ($this->items as $i => $row) :
					$link = JRoute::_('index.php?option=com_beseated&view=concierge&layout=edit&concierge_id=' . $row->concierge_id);
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->concierge_id); ?>
						</td>

						<td class="nowrap center">
							<?php echo $row->city; ?>
						</td>
						<td class="nowrap center">
							<a href="<?php echo $link ?>"><?php echo $row->phone_no; ?></a>
						</td>
						<td class="nowrap center">
							<input type="radio" onclick="isDefault(1,<?php echo $row->concierge_id; ?>);"  name="default<?php echo $i;?>" value="1" <?php if ($row->is_default == '1') { echo 'checked="checked"';} ?> >Yes
							<input type="radio" onclick="isDefault(0,<?php echo $row->concierge_id; ?>);"  name="default<?php echo $i;?>" value="0"  <?php if ($row->is_default == '0') { echo 'checked="unchecked"';} ?> >No
						</td>
						<td class="nowrap center">
							<?php  echo $row->concierge_id; ?>
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

