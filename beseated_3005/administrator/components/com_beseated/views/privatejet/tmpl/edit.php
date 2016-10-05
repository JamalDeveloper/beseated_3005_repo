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

<form action="<?php echo JRoute::_('index.php?option=com_beseated&view=privatejet&layout=edit&private_jet_id=' . (int) $this->item->private_jet_id); ?>"
    method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
    <div class="form-horizontal">
        <fieldset>
            <div class="span12">
                <legend><?php echo JText::_('COM_BESEATED_PRIVATE_JET_DETAIL'); ?></legend>
                <div class="control-group">
                    <div class="controls"><?php echo $this->form->getInput('private_jet_id'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('city'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('latitude'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('longitude'); ?></div>

                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('company_name'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('company_name'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('owner_email'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('owner_email'); ?></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
                    <div class="controls"><?php echo $this->form->getInput('image'); ?></div>
                </div>
            </div>
            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />

</form>
