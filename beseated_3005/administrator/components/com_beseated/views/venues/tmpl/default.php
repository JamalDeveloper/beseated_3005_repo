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

<script type="text/javascript">
function updateHasGuestlistValue(venueID)
	{
		var checkBoxVal = jQuery('.checkGuest-'+venueID);

		if (jQuery(checkBoxVal).attr('checked') == 'checked')
		{
			var checkBoxValue = 1;
		}
		else
		{
			var checkBoxValue = 0;
		}

		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=venues.updateHasGuestlistValue',
			type: 'GET',
			data: 'venue_id='+venueID+'&checkBoxVal='+checkBoxValue,
			success: function(response){

	        }
		})
		.done(function() {
			//console.log("success");
		})
		.fail(function() {
			//console.log("error");
		})
		.always(function() {
			//console.log("complete");
		});
	}

	function updateHasActivePayments(venueID)
	{
		var checkBoxVal = jQuery('.checkPayment-'+venueID);

		if (jQuery(checkBoxVal).attr('checked') == 'checked')
		{
			var checkBoxValue = 1;
		}
		else
		{
			var checkBoxValue = 0;
		}

		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=venues.updateHasActivePayments',
			type: 'GET',
			data: 'venue_id='+venueID+'&checkBoxVal='+checkBoxValue,
			success: function(response){
	        }
		})
		.done(function() {
			//console.log("success");
		})
		.fail(function() {
			//console.log("error");
		})
		.always(function() {
			//console.log("complete");
		});
	}

</script>

<form action="index.php?option=com_beseated&view=venues" method="post" id="adminForm" name="adminForm">

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
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_NAME', 'venue_name', $listDirn);?>
			</th>
			<th width="25%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_HAS_GUEST_LIST', 'has_guestlist', $listDirn);?>
			</th>
			<th width="25%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_PAYMENTS', 'active_payments', $listDirn);?>
			</th>
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_ADDRESS', 'location', $listDirn); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_ACCOUNT_CREATED', 'created', $listDirn); ?>
			</th>
			<th width="10%">
				<?php echo JText::_('COM_BESEATED_COMPANY_REVENUE'); ?>
			</th>
			<th width="10%">
				<?php echo JText::_('COM_BESEATED_COMPANY_REFUND_POLICY_HOURS'); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_COMMISSION_RATE', 'deposit_per', $listDirn); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_COMPANY_PROMOTION_MSG_SENT_COUNT', 'people_count', $listDirn); ?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->venues)) : ?>
				<?php foreach ($this->venues as $i => $row) :
					$link = JRoute::_('index.php?option=com_beseated&view=venue&layout=edit&venue_id=' . $row->venue_id);
				?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->venue_id); ?>
						</td>
						<td width="2%" style="min-width:55px" class="nowrap">
							<?php echo JHtml::_('jgrid.published', $row->published, $i, 'venues.', true, 'cb'); ?>
						</td>
						<td>
							<a href="<?php echo $link ?>">
								<?php echo $row->venue_name; ?>
							</a>
						</td>
						<td>
								<?php $checkbox          = ($row->has_guestlist) ? 'checked' : 'unchecked';
								     // $has_guestlist     = ($row->has_guestlist) ? '0':'1'; ?>
								<input type="checkbox"  class="checkGuest-<?php echo $row->venue_id; ?>" id="hasGuestlist" onclick="updateHasGuestlistValue('<?php echo $row->venue_id; ?>');" <?php echo $checkbox ?>>
						</td>
						<td>
								<?php $checkbox1              = ($row->active_payments) ? 'checked' : 'unchecked'; ?>
								<input type="checkbox" class="checkPayment-<?php echo $row->venue_id; ?>" id="hasActivePayments" onclick="updateHasActivePayments('<?php echo $row->venue_id; ?>');" <?php  echo $checkbox1 ?>>
						</td>
						<td class="">
							<?php echo $row->location; ?>
						</td>
						<td class="">
							<?php echo date('d-m-Y',strtotime($row->created)); ?>
						</td>
						<td class="">
							<?php if(isset($this->revenues[$row->venue_id])): ?>
								<?php echo number_format($this->revenues[$row->venue_id],2); ?>
							<?php else: ?>
								0.00
							<?php endif; ?>
						</td>
						<td class="">
							<?php echo $row->refund_policy; ?>
						</td>
						<td class="">
							<?php echo $row->deposit_per; ?>
						</td>
						<td class="">
							<?php //echo $row->venue_id; ?>
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

