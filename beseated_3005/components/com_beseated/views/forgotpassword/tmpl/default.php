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

$input  = JFactory::getApplication()->input;
$Itemid = $input->get('Itemid', 0, 'int');

?>
<div class="login">
	<form class="form-horizontal frgt-frm" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&Itemid='.$Itemid);?>">
		<fieldset>
			<p><?php echo JText::_('COM_BCTED_VIEW_FORGOT_PASSWORD_INSTRAUCTION_TEXT'); ?></p>
			<div class="control-group">
				<label class="control-label span5">Email ID:</label>
				<div class="controls span6">
					<input type="text" name="email" class="span8" id="email" value="">
				</div>
			</div>
		</fieldset>
		<div class="control-group">
			<label class="control-label span5"></label>
			<div class="controls span6">
				<button type="submit" class="btn btn-primary validate">Submit</button>
				<input type="hidden" name="task" value="forgotpassword.setpassword">
				<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>">
				<input type="hidden" name="option" value="com_bcted">
			</div>
		</div>
	</form>
</div>

