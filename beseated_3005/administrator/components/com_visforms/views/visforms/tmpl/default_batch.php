<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_('COM_VISFORMS_BATCH_OPTIONS'); ?></h3>
	</div>
    <div class="modal-body modal-batch">
		<div class="row-fluid">
			<div class="control-group span6">
				<div class="controls">
					<?php echo JHtml::_('batch.access'); ?>
				</div>
			</div>
            <div class="control-group span6">
				<div class="controls">
					<?php echo JHtml::_('batch.language'); ?>
				</div>
			</div>
		
	<?php

		// Create the copy/move options.
		$options = array(JHtml::_('select.option', 'c', JText::_('JYes')),
			JHtml::_('select.option', 'n', JText::_('JNo')));

		// Create the batch selector to select whether to copy fields or not.
		$lines = array('<label id="batch-choose-action-lbl" for="batch-choose-action">', JText::_('COM_VISFORMS_COPY_FIELDS'), '</label>',
			'<fieldset id="batch-choose-action" class="combo">',
			//show the radiolist with default 0
			JHtml::_('select.radiolist', $options, 'batch[copy_fields]', '', 'value', 'text', 'c'), '</fieldset>');

		echo implode("\n", $lines);
	?>
            </div>
	</div>
    <div class="modal-footer">
	<button  class="btn" type="button" onclick="document.id('batch-access').value='';document.id('batch-language-id').value=''; document.id('batch[copy_fields]c').checked=true;document.id('batch[copy_fields]n').checked=false"' data-dismiss="modal">
		<?php echo JText::_('JCANCEL'); ?>
	</button>
    <button  class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('visform.batch');">
		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	</button>
    </div>
</div>
