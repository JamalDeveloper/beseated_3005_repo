<?php
/**
 * @package     Bcted.Administrator
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('bootstrap.framework');
JHtml::_('formbehavior.chosen', 'select');

$user_ids = array();
$checkedValues = array();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$user		= JFactory::getUser();
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

require_once JPATH_SITE . '/components/com_ijoomeradv/extensions/beseated/helper.php';
$this->helper            = new beseatedAppHelper;

?>
<script type="text/javascript">

	jQuery(document).ready(function()
	{
		var user_IDs = jQuery('#userIDs').val();
		var isUnchecked = jQuery('#isUnchecked').val();

		if(isUnchecked == 1)
		{
			//jQuery('#checkallbox').val(0);
			jQuery('#checkallbox').removeAttr('checked');
			jQuery('#checkallbox').attr("unchecked", true);
		}
		else
		{
			jQuery('#checkallbox').removeAttr('unchecked');
			jQuery('#checkallbox').attr("checked", true);
		}

		jQuery('#checkallbox').change(function()
		{
			if (this.checked)
			{
				var checkBoxVal = 1;

        		jQuery.ajax({
					url: 'index.php?option=com_beseated&task=guests.updateShowPublicTableValue',
					type: 'GET',
					data: 'user_id='+jQuery.parseJSON(user_IDs)+'&checkBoxVal='+checkBoxVal,
					success: function(response){
							jQuery('.checkAll').removeAttr('unchecked');
							jQuery('.checkAll').attr("checked", true);

			        }
				});
    		}
    		else
    		{
    			var checkBoxVal = 0;

        		jQuery.ajax({
					url: 'index.php?option=com_beseated&task=guests.updateShowPublicTableValue',
					type: 'GET',
					data: 'user_id='+jQuery.parseJSON(user_IDs)+'&checkBoxVal='+checkBoxVal,
					success: function(response){

							jQuery('.checkAll').removeAttr('checked');
							jQuery('.checkAll').attr("unchecked", true);
			        }
				});
    		}
		})


	});

	function setValForModel(userName,userID)
	{
		//jQuery('#user_id').val(userID);
		jQuery('#myModalLabel').html('Loyalty Points Of '+userName);
		jQuery('#loyalty_list').html('Some text and markup');
		jQuery('#user_id').val(userID);

		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=guests.getUserLoyaltyList',
			type: 'GET',
			data: 'user_id='+userID,

			success: function(response){

				jQuery('#loyalty_list').html(response);
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

	function updateShowPublicTableValue(userID)
	{
		var checkBoxVal = jQuery('#showPublicTable-'+userID);

		if (jQuery(checkBoxVal).attr('checked') == 'checked')
		{
			var checkBoxValue = 1;
		}
		else
		{
			var checkBoxValue = 0;
		}

		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=guests.updateShowPublicTableValue',
			type: 'GET',
			data: 'user_id='+userID+'&checkBoxVal='+checkBoxValue,
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

	function changePoint(type)
	{
		var user_id = jQuery('#user_id').val();
		var admin_point = jQuery('#admin_point').val();
		var point_type = type;

		// Ajax call to send message....
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=guests.changePoint',
			type: 'GET',
			data: 'user_id='+user_id+'&admin_point='+admin_point+'&point_type='+point_type,

			success: function(response){

				if(response == "200")
				{
					//jQuery('#booking_'+bookingID).remove();
					 location.reload();
				}
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
<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=guests'); ?>" method="post" id="adminForm" name="adminForm">
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
				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_HEADING_BESEATEDGUEST_FULL_NAME', 'full_name', $listDirn); ?></th>
				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_HEADING_BESEATEDGUEST_EMAIL', 'email', $listDirn); ?></th>
				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_HEADING_BESEATEDGUEST_CITY', 'city', $listDirn); ?></th>
				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_HEADING_BESEATEDGUEST_PHONE_NUMBER', 'phone', $listDirn); ?></th>
				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_HEADING_BESEATEDGUEST_BIRTH_DATE', 'birthdate', $listDirn); ?></th>
				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_HEADING_BESEATEDGUEST_LAST_VISITED', 'lastvisitDate', $listDirn); ?></th>
				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_HEADING_BESEATEDGUEST_CREATED', 'registerDate', $listDirn); ?></th>
				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_HEADING_LOYALTY_POINTS', 'totalLoyaltyPoint', $listDirn); ?></th>
				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_HEADING_LOYALTY_POINTS_TIER_NAME', 'tier_name', $listDirn); ?></th>
				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_HEADING_USER_TOTAL_BOOKNG', 'totalBookings', $listDirn); ?></th>
				<th width="2%"><?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_HEADING_ALL_BOOKING_TOTAL_AMOUNT', 'totalAmount', $listDirn); ?></th>
				<th width="2%"><?php echo JText::_('COM_BESEATED_HEADING_SHOW_PUBLIC_TABLE'); ?><input type="checkbox"  id="checkallbox"></th>


			</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php foreach ($this->items as $i => $row) :
					$link = JRoute::_('index.php?option=com_users&task=user.edit&id=' . $row->user_id); ?>
					<tr>
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						<td><?php echo $row->name; ?></td>
						<td><?php echo $row->email; ?></td>
						<td><?php echo $row->city; ?></td>
						<td><?php echo $row->phone; ?></td>
						<td><?php echo ($row->birthdate == '0000-00-00') ? ' - ' : date('d-m-Y',strtotime($row->birthdate)); ?></td>
						<td><?php echo ($row->lastvisitDate == '0000-00-00 00:00:00') ? ' - ' : date('d-m-Y H:i',strtotime($row->lastvisitDate)); ?></td>
						<td><?php echo date('d-m-Y H:i',strtotime($row->registerDate)); ?></td>
						<td>
							<a href="#myModal" onclick="setValForModel('<?php echo $row->name; ?>','<?php echo $row->user_id; ?>');" role="button" class="btn" data-toggle="modal">
							<?php
							  //$totalLoyaltyPoint = $this->model->get_user_sum_of_loyalty_point($row->user_id);
							   echo  $this->helper->currencyFormat('',$row->totalLoyaltyPoint);
							 //  $this->helper->currencyFormat('',$row->totalLoyaltyPoint);
							?>
							</a>

						</td>
						<td>
								<?php echo $row->tier_name; ?>
						</td>
						<td>
								<?php //$bookingDetail  = $this->model->getTotalBookingAndAmount($row->user_id);
								   echo $row->totalBookings;
								?>
						</td>
						<td>
								<?php echo number_format($row->totalAmount,0); ?>
						</td>
						<td>
								<?php $checkbox          = ($row->show_public_table) ? 'checked' : 'unchecked';
								      $show_public_table = ($row->show_public_table) ? '0':'1';
								      $user_ids[]        =  $row->user_id;
								      $checkedValues[]   =  $checkbox;


								      //$test = '123';
								?>

								<input type="checkbox"  class="checkAll" id="showPublicTable-<?php echo $row->user_id; ?>" onclick="updateShowPublicTableValue('<?php echo $row->user_id; ?>');" value="<?php echo $show_public_table; ?>" <?php echo $checkbox ?>>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php
			//echo "<pre>";print_r("hi");echo "</pre>";exit;
			endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
	</table>
	<?php
	$isUnchecked       =  in_array('unchecked', $checkedValues);
    ?>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" id="userIDs" name="userIDs" value='<?php echo json_encode($user_ids); ?>' />
	<input type="hidden" id="isUnchecked" name="isUnchecked" value="<?php echo $isUnchecked; ?>" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#admin_point").keydown(function (e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                 // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                 // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                     // let it happen, don't do anything
                     return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    });
</script>

<style type="text/css">
.acnt-histry-tbl table thead {
	padding-bottom: 30px;
	border-bottom: 2px solid #000;
	height: 50px;
	vertical-align: top;
}
.acnt-histry-tbl table th {
	text-align: left;
	font-size: 18px;
	vertical-align: top;
	padding: 5px 20px;
}
.acnt-histry-tbl table td {
	text-align: left;
	font-size: 18px;
	padding: 20px;
	width: 23%;
}
</style>

<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="myModalLabel"></h3>
	</div>
	<div class="modal-body"  style="overflow-y: scroll;">
		<div class="span12 acnt-histry-tbl loyalty-tbl" id="loyalty_list">
		</div>
		<input type="hidden" name="user_id" id="user_id">


	</div>
	<div class="modal-footer">
		<span>Points : </span><input type="text" value="" name="admin_point" id="admin_point">

		<button class="btn btn-primary" id="saveButton" onclick="changePoint('add');">Add Point</button>
		<button class="btn btn-primary" id="saveButton" onclick="changePoint('sub');">Substract Point</button>
	</div>
</div>