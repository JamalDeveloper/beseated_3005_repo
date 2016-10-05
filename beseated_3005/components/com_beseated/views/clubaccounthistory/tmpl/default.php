<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$input        = JFactory::getApplication()->input;
$Itemid       = $input->get('Itemid', 0, 'int');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
$messageMenu  = BeseatedHelper::getBeseatedMenuItem('club-messages');
$messageLink  = $messageMenu->link.'&Itemid='.$messageMenu->id;
?>

<script type="text/javascript">
function add_to_blacklist(userID,venueID)
{
    jQuery.ajax({
        url: 'index.php?option=com_beseated&task=clubaccounthistory.add_user_in_blacklist',
        type: 'GET',
        data: '&user_id='+userID+'&venue_id='+venueID,

        success: function(response){

            if(response == "200")
            {
            	window.location.reload();
            }
        }
    })
    .done(function() {
    })
    .fail(function() {
    })
    .always(function() {
    });

}
function remove_from_blacklist(userID,venueID)
{

    jQuery.ajax({
        url: 'index.php?option=com_beseated&task=clubaccounthistory.remove_user_from_blacklist',
        type: 'GET',
        data: '&user_id='+userID+'&venue_id='+venueID,

        success: function(response){

            if(response == "200")
            {
            	window.location.reload();
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
<?php
	$blackListedUsers = BeseatedHelper::getBlackList($this->elementDetail->venue_id,'Venue');
	$isArray          = is_array($blackListedUsers);

	if (is_array($blackListedUsers)){
		$blackListedUsers = $blackListedUsers;
	}else{
		$blackListedUsers = array();
	}

	/* $app = JFactory::getApplication(); 
	 $menu = $app->getMenu();

	 $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=venuemessages', true );

	 echo "<pre/>";print_r($menuItem);exit;*/

?>
<div class="table-wrp">
	<div class="acnt-histry-tbl">
		<table class="activity" id="accordion">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_BCTED_DATE'); ?></th>
					<th><?php echo JText::_('COM_BCTED_SERVICE'); ?></th>
					<th><?php echo JText::_('COM_BCTED_AMOUNT'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $processIDs = array(); ?>
				<?php foreach ($this->history as $key => $value):?>
					<?php //echo "<pre/>";print_r($value);exit; ?>

					<?php //if(!$value->payment_id){ continue; } ?>
					<?php if(in_array($value->venue_table_booking_id, $processIDs)){ continue; } ?>
					<?php $processIDs[] = $value->venue_table_booking_id; ?>
					<tr id="row<?php echo $value->venue_table_booking_id; ?>" class="accordion" data-toggle="collapse" data-target="#demo<?php echo $value->venue_table_booking_id; ?>">
						<td><?php echo date('d-m-Y',strtotime($value->booking_date)); ?></td>
						<td>
						<?php $tableDetail = BeseatedHelper::getVenueTableDetail($value->table_id);?>
						<?php echo ucfirst($tableDetail->table_name); ?></td>
						<td><?php echo $this->elementDetail->currency_sign.' '.number_format($value->total_price,0); ?></td>
					</tr>
					<tr class="expand-data">
						<td class="hiddenRow" colspan="3">
							<div class="collapse" id="demo<?php echo $value->venue_table_booking_id; ?>">
								<table class="tbl-hdndata">
									<tbody>
										<tr>
											<td><?php echo JText::_('COM_BCTED_NAME') ?>:</td>
											<td><?php echo JText::_('COM_BCTED_NO_OF_PEOPLE') ?>:</td>
											<td rowspan="2" valign="bottom">
												<?php $isLiveUser = BeseatedHelper::isLiveUser($value->user_id); ?>
												<?php if($isLiveUser): ?>
													<?php $app = JFactory::getApplication(); ?>
													<?php $menu = $app->getMenu(); ?>
													<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=venuemessages', true ); ?>
													<?php $Itemid = $menuItem->id; ?>
													<?php $connectionID = BeseatedHelper::getMessageConnection($this->elementDetail->user_id,$value->user_id); ?>
													<?php $link = JRoute::_("index.php?option=com_beseated&view=venuemessagedetail&user_id=".$value->user_id."&connection_id=".$connectionID."&Itemid=".$Itemid); ?>
													<a href="<?php echo $link; ?>">
														<button class="msg-btn" style="background:#fcb829 none repeat scroll 0 0">
															<img src="./images/message_btn_normal.png" alt="" />
														</button>
													</a>
												<?php else: ?>
													&nbsp;
												<?php endif; ?>
											</td>
										</tr>
										<tr>
											<td><?php echo ucfirst($value->full_name); ?></td>
											<td><?php echo $value->total_guest.'('.$value->male_guest.'M/'.$value->female_guest.'F)'; ?></td>
										</tr>
										<tr>
											<td colspan="3">
												<?php if(in_array($value->user_id, $blackListedUsers)): ?>
													<center><button onclick="remove_from_blacklist('<?php echo $value->user_id; ?>','<?php echo $this->elementDetail->venue_id; ?>')" class="btn btn-primary">Remove from blacklist</button></center>
												<?php else: ?>
													<center><button onclick="add_to_blacklist('<?php echo $value->user_id; ?>','<?php echo $this->elementDetail->venue_id; ?>')" class="btn btn-primary">Blacklist User</button></center>
												<?php endif; ?>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
<script type="text/javascript">
	jQuery('.collapse').on('show.bs.collapse', function () {
		jQuery('.in.collapse').collapse('hide');
	});

	jQuery(document).ready(function() {
		jQuery('#accordion tr').click(function() {
			if(jQuery(this).hasClass('active_row'))
			{
				jQuery('#accordion tr').removeClass("active_row");
				jQuery(this).removeClass('active_row');
			}
			else
			{
				jQuery('#accordion tr').removeClass("active_row");
				jQuery(this).addClass(' active_row');
			}
		});
	});
</script>
<?php echo $this->pagination->getListFooter(); ?>
