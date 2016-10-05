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
<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=premiumtable&layout=edit&premium_id=' . (int) $this->item->premium_id); ?>"
    method="post" name="adminForm" id="adminForm">
    <div class="form-horizontal">
        <fieldset>
            <div class="span12">
                <legend><?php echo JText::_('COM_BESEATED_PREMIUM_TABLE_DETAIL'); ?></legend>
                <div class="control-group">
                    <div class="controls"><?php echo $this->form->getInput('premium_id'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('created'); ?></div>

                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('premium_table_name'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('premium_table_name'); ?></div>
                </div>
            </div>
            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />

</form>