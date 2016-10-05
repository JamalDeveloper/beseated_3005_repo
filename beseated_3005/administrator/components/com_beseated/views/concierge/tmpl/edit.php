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


JHtml::_('behavior.framework');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_beseated/assets/css/beseated.css');
?>

<script type="text/javascript">
 jQuery(document).ready(function() {
        jQuery("#jform_phone_no").keydown(function (e) {

            // Allow: backspace, delete, tab, escape, enter and .
            if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                 // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                 // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                     // let it happen, don't do anything
                     return;
            }
            if(jQuery(this).val().length>20){
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
    action="<?php echo JRoute::_('index.php?option=com_beseated&view=concierge&layout=edit&concierge_id=' . (int) $this->item->concierge_id); ?>"
    method="post" name="adminForm" id="adminForm" class="form-validate">

    <div class="form-horizontal">

        <div class="row-fluid">
            <div class="span10 form-horizontal">
                <fieldset class="adminform">

                    <input type="hidden" name="jform[concierge_id]" value="<?php echo $this->item->concierge_id; ?>" />
                    <legend><?php echo JText::_('COM_BESEATED_CONCIERGE_DETAIL'); ?></legend>

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('city'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('city'); ?></div>
                    </div>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('phone_no'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('phone_no'); ?></div>
                    </div>
                   <!--  <div class="control-group">
                       <div class="control-label"><?php //echo $this->form->getLabel('is_default'); ?></div>
                       <div class="controls"><?php //echo $this->form->getInput('is_default'); ?></div>
                   </div> -->
                </fieldset>
            </div>
        </div>

        <input type="hidden" name="task" value=""/>
        <?php echo JHtml::_('form.token'); ?>

    </div>
</form>
