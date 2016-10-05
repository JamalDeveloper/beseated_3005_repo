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

/*
Red	Declined
Yellow	Accepted
Orange	Awaiting Payment
Green	Paid

circle_pending
circle_awaiting_payment
circle_booked
circle_decline
circle_canceled
*/

?>
<style type="text/css">
	.cls_circle{
		width: 20px;
		height: 20px;
		border-radius: 50%;
		display: inline-block;
	}
	.circle_pending{
		background: #000;
	}
	.circle_awaiting_payment{
		background: orange;
	}
	.circle_booked{
		background: green;
	}
	.circle_decline{
		background: red;
	}
	.circle_canceled{
		background: #000;
	}
	.circle_confirmed{
		background: #0000FF;
	}
	.circle_accept{
		background: #0000FF;
	}
	.circle_request{
		background: green;
	}
</style>
<form action="index.php?option=com_beseated&view=guestlistrequests" method="post" id="adminForm" name="adminForm">

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
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_REQUESTS_COMPANY', 'venue_name',$listDirn); ?>
			</th>
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_REQUESTS_FULL_NAME', 'full_name',$listDirn); ?>
			</th>
			<th width="5%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_VENUE_BOOKING_TOTAL_GUEST', 'total_guest',$listDirn); ?>
			</th>
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_REQUESTS_REQUEST_DATE', 'request_date_time',$listDirn); ?>
			</th>
			<th width="15%">
				<?php echo JText::_('COM_BESEATED_REQUESTS_REQUEST_TIME');?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_REQUESTS_RESPONSE_DATE', 'response_date_time',$listDirn); ?>
			</th>
			<th width="5%">
				<?php echo JText::_('COM_BESEATED_REQUESTS_RESPONSE_TIME'); ?>
			</th>
			<th width="10%">
				<?php echo JText::_('COM_BESEATED_REQUESTS_TIME_TO_RESPONSE'); ?>
			</th>
			<th width="10%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_BOOKING_DATE', 'booking_date',$listDirn); ?>
			</th>
			<th width="15%">
				<?php echo JHtml::_('searchtools.sort', 'COM_BESEATED_REQUESTS_STATUS', 'venue_status',$listDirn); ?>
			</th>

		</tr>
		</thead>
		<tbody>
			<?php if (!empty($this->items)) : ?>
				<?php
				/*echo "<pre>";
				print_r($this->items);
				echo "</pre>";*/

				?>
				<?php foreach ($this->items as $i => $row) : ?>
					<?php if($row->guest_booking_id): ?>
						<tr>
							<td><?php echo $i+1; //$this->pagination->getRowOffset($i); ?></td>
							<td class="">
								<?php echo ucfirst($row->venue_name); ?>
							</td>
							<td>
								<?php echo ucwords($row->full_name); ?>
							</td>
							<td>
								<?php echo $row->total_guest; ?>
							</td>
							<td class="">
								<?php echo date('d-m-Y',strtotime($row->request_date_time)); ?>
							</td>
							<td class="">
								<?php echo date('H:i',strtotime($row->request_date_time)); ?>
							</td>
							<td class="">
								<?php echo ($row->response_date_time == '0000-00-00 00:00:00')? ' - ' : date('d-m-Y',strtotime($row->response_date_time)); ?>
							</td>
							<td class="">
								<?php echo ($row->response_date_time == '0000-00-00 00:00:00')? ' - ' : date('H:i',strtotime($row->response_date_time)); ?>
							</td>
							<td class="">
								<?php
									$seconds =strtotime($row->response_date_time) - strtotime($row->request_date_time) ;

									$hours = floor($seconds / (60 * 60));

									$hours = (strlen($hours) == '1') ? '0'.$hours : $hours;

								    // extract minutes
								    $divisor_for_minutes = $seconds % (60 * 60);
								    $minutes = floor($divisor_for_minutes / 60);

								    $minutes = (strlen($minutes) == '1') ? '0'.$minutes : $minutes;

									echo ($row->response_date_time == '0000-00-00 00:00:00')? ' - ' : $hours .':'.$minutes;
								?>
							</td>
							<td class="">
								<?php echo date('d-m-Y',strtotime($row->booking_date)); ?>
							</td>
							<td class=" cls_status" >
								<?php if($row->venue_status == 1): ?>
									<div class="cls_circle circle_pending">&nbsp; </div> Pending
								<?php elseif($row->venue_status == 3): ?>
									<div class="cls_circle circle_awaiting_payment">&nbsp; </div> Awaiting Payment
								<?php elseif($row->venue_status == 5): ?>
									<div class="cls_circle circle_booked">&nbsp; </div> Paid
								<?php elseif($row->venue_status == 6): ?>
									<div class="cls_circle circle_decline">&nbsp; </div> Decline
								<?php elseif($row->venue_status == 8): ?>
									<div class="cls_circle circle_canceled">&nbsp; </div> Canceled
								<?php elseif($row->venue_status == 11): ?>
									<div class="cls_circle circle_accept">&nbsp; </div> Accept
								<?php elseif($row->venue_status == 2): ?>
									<div class="cls_circle circle_request">&nbsp; </div> Request
								<?php endif; ?>
							</td>

						</tr>
					<?php elseif($row->guest_booking_id): ?>

					<?php endif; ?>
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

