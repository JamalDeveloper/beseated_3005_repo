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
<section class="page-section page-messagedetail">
	<div class="container">

		<?php include_once('templates/beseated-theme/partials/guest-profile-menu.php') ?>

		<div class="row">
			<div class="col-md-12">

				<h3 class="heading-1"><?php echo $this->otherUser->name; ?></h3>

				<div class="row">
				<?php foreach ($this->messages as $key => $message):?>
					<div class="col-md-6 col-md-offset-3">
						<div class="bordered-box bubble">
							<div class="msg-detail">
								<?php echo date('d-M-Y',strtotime($message->created)); ?> @ <?php echo gmdate('H:i',$message->time_stamp); ?>
							</div>
							<div class="msg-body">
								<?php echo ucfirst($message->message_body);?>
							</div>	
						</div>
					</div>
				<?php endforeach; ?>
				</div>	

			</div>
		</div>

	</div>
</section>	

<?php echo $this->pagination->getListFooter(); ?>
