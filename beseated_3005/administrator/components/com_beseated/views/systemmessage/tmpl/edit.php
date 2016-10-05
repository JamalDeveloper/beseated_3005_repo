<?php
/**
 * @package     Beseated.Administrator
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=systemmessage&layout=edit&message_id=' . (int) $this->item->message_id); ?>"
    method="post" name="adminForm" id="adminForm">
    <div class="form-horizontal">
        <fieldset>
            <div class="span12">
                <legend><?php echo JText::_('COM_BESEATED_SYSTEM_MESSAGE_DETAIL'); ?></legend>
                <div class="control-group">
                    <div class="controls"><?php echo $this->form->getInput('message_id'); ?></div>
                </div>

                 <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('city'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('city'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('message'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('message'); ?></div>
                </div>
            </div>
            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />

</form>
