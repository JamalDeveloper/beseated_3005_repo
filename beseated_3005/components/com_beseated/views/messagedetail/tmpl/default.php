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

$input     = JFactory::getApplication()->input;
$Itemid    = $input->get('Itemid', 0, 'int');
$loginUser = JFactory::getUser();
$userGroup = BeseatedHelper::getUserType($loginUser->id);

?>
<div class="table-wrp">
	<div class="req-detailwrp msg-detailwrp">
		<h2><?php echo $this->otherUser->name; ?></h2>
		<div class="message-detail-list">
			<?php foreach ($this->messages as $key => $message):?>
				<?php if($loginUser->id == $message->from_user_id): ?>
				<div class="message-detail-client right">
				<?php else: ?>
				<div class="message-detail-client left">
				    <?php endif; ?>
					<div class="message-detail-inner">
						<div class="message-detail-body">
							<p><?php echo ucfirst($message->message_body);?></p>
						</div>
						<div class="message-detail-date-time">
							<p class="message-detail-date"><?php echo date('d-M-Y',strtotime($message->created)); ?></p>
							<p class="message-detail-time"><?php echo gmdate('H:i',$message->time_stamp); ?></p>
						</div>
					</div>
					<div class="message-detail-client-img">
						<span class="msg-client-img"></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php echo $this->pagination->getListFooter(); ?>
