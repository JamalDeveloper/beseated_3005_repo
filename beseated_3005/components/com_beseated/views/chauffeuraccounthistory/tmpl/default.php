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
function add_to_blacklist(userID,chauffeurID)
{
    jQuery.ajax({
        url: 'index.php?option=com_beseated&task=chauffeuraccounthistory.add_user_in_blacklist',
        type: 'GET',
        data: '&user_id='+userID+'&chauffeur_id='+chauffeurID,

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
function remove_from_blacklist(userID,chauffeurID)
{

    jQuery.ajax({
        url: 'index.php?option=com_beseated&task=Chauffeuraccounthistory.remove_user_from_blacklist',
        type: 'GET',
        data: '&user_id='+userID+'&chauffeur_id='+chauffeurID,

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
	$blackListedUsers = BeseatedHelper::getBlackList($this->elementDetail->chauffeur_id,'Chauffeur');
	$isArray          = is_array($blackListedUsers);

	if (is_array($blackListedUsers)){
		$blackListedUsers = $blackListedUsers;
	}else{
		$blackListedUsers = array();
	}

?>
<div class="table-wrp">
	<div class="acnt-histry-tbl">
		<table class="activity" id="accordion">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_BCTED_DATE'); ?></th>
					<th><?php echo JText::_('COM_BCTED_SERVICE_NAME'); ?></th>
					<th><?php echo JText::_('COM_BCTED_AMOUNT'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $processIDs = array(); ?>
				<?php foreach ($this->history as $key => $value):?>
					<?php //echo "<pre/>";print_r($value);exit; ?>

					<?php //if(!$value->payment_id){ continue; } ?>
					<?php if(in_array($value->chauffeur_booking_id, $processIDs)){ continue; } ?>
					<?php $processIDs[] = $value->chauffeur_booking_id; ?>
					<tr id="row<?php echo $value->chauffeur_booking_id; ?>" class="accordion" data-toggle="collapse" data-target="#demo<?php echo $value->chauffeur_booking_id; ?>">
						<td><?php echo date('d-m-Y',strtotime($value->booking_date)); ?></td>
						<td>
						<?php echo ucfirst($value->service_name); ?></td>
						<td><?php echo $this->elementDetail->currency_sign.' '.number_format($value->total_price,0); ?></td>
					</tr>
					<tr class="expand-data">
						<td class="hiddenRow" colspan="3">
							<div class="collapse" id="demo<?php echo $value->chauffeur_booking_id; ?>">
								<table class="tbl-hdndata">
									<tbody>
										<tr>
											<td colspan="3">
												<?php if(in_array($value->user_id, $blackListedUsers)): ?>
													<center><button onclick="remove_from_blacklist('<?php echo $value->user_id; ?>','<?php echo $this->elementDetail->chauffeur_id; ?>')" class="btn btn-primary">Remove from blacklist</button></center>
												<?php else: ?>
													<center><button onclick="add_to_blacklist('<?php echo $value->user_id; ?>','<?php echo $this->elementDetail->chauffeur_id; ?>')" class="btn btn-primary">Blacklist User</button></center>
												<?php endif; ?>
											</td>
											<td rowspan="2" valign="bottom">
												<?php $isLiveUser = BeseatedHelper::isLiveUser($value->user_id); ?>
												<?php if($isLiveUser): ?>
													<?php $app = JFactory::getApplication(); ?>
													<?php $menu = $app->getMenu(); ?>
													<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=chauffeurmessages', true ); ?>
													<?php $Itemid = $menuItem->id; ?>
													<?php $connectionID = BeseatedHelper::getMessageConnection($this->elementDetail->user_id,$value->user_id); ?>
													<?php $link = JRoute::_("index.php?option=com_beseated&view=chauffeurmessagedetail&user_id=".$value->user_id."&connection_id=".$connectionID."&Itemid=".$Itemid); ?>
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
