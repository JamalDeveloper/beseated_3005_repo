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
		if (task == 'tabletype.cancel') {
			Joomla.submitform(task, document.getElementById('tabletype-form'));
		}
		else {

			if (task != 'tabletype.cancel' && document.formvalidator.isValid(document.id('tabletype-form'))) {

				Joomla.submitform(task, document.getElementById('tabletype-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}

</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_beseated&layout=edit&table_type_id=' . (int) $this->item->table_type_id); ?>"
	method="post" name="adminForm" id="tabletype-form" class="form-validate">

	<div class="form-horizontal">

		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">

					<input type="hidden" name="jform[table_type_id]" value="<?php echo $this->item->table_type_id; ?>" />
					<legend><?php echo JText::_('COM_BESEATED_REWARD_DETAIL'); ?></legend>

					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('table_type_name'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('table_type_name'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('table_type_desc'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('table_type_desc'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('published'); ?></div>
					</div>
				</fieldset>
			</div>
		</div>

		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>

	</div>
</form>
