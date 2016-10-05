<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Beseated
 * @author     jamal <derdiwalanawaz@gmail.com>
 * @copyright  Copyright (C) 2015. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');


// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_beseated/assets/css/beseated.css');
?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {

	});

	Joomla.submitbutton = function (task) {
		if (task == 'reward.cancel') {
			Joomla.submitform(task, document.getElementById('reward-form'));
		}
		else {

			if (task != 'reward.cancel' && document.formvalidator.isValid(document.id('reward-form'))) {

				Joomla.submitform(task, document.getElementById('reward-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}

    jQuery(document).ready(function() {
        jQuery("#jform_reward_coin").keydown(function (e) {

            // Allow: backspace, delete, tab, escape, enter and .
            if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                 // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                 // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                     // let it happen, don't do anything
                     return;
            }
            if(jQuery(this).val().length>5){
                return false;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    });
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_beseated&layout=edit&reward_id=' . (int) $this->item->reward_id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="reward-form" class="form-validate">

	<div class="form-horizontal">

		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">

					<input type="hidden" name="jform[reward_id]" value="<?php echo $this->item->reward_id; ?>" />
					<legend><?php echo JText::_('COM_BESEATED_REWARD_DETAIL'); ?></legend>

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('reward_name'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('reward_name'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('reward_desc'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('reward_desc'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('reward_coin'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('reward_coin'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('published'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('image'); ?></div>
					</div>
				</fieldset>
			</div>
		</div>

		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>

	</div>
</form>
