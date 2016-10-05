<?php
/**
 * Visform form view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

//no direct access
 defined('_JEXEC') or die('Restricted access'); ?>

<?php 
	jimport( 'joomla.html.editor' ); 
	JHtml::_('behavior.formvalidation');
    JHtml::_('behavior.keepalive');
    JHtml::_('formbehavior.chosen', 'select');
    
    //Check im TinyMCE editor is enable. If not we have to hide the editor buttons
    $db = JFactory::getDbo();
    // Build the query.
    $query = $db->getQuery(true)
        ->select('element')
        ->from('#__extensions')
        ->where('element = ' . $db->quote('tinymce'))
        ->where('folder = ' . $db->quote('editors'))
        ->where('enabled = 1');

    // Check of the editor exists.
    $db->setQuery($query, 0, 1);
    $editor = $db->loadResult();

    // If no editor is found stop tinyMCE is disabled.
    if (!$editor)
    {
        //hide editor button div 
        $css = '#editor-xtd-buttons {display: none;}';
        $doc = JFactory::getDocument();
        $doc->addStyleDeclaration($css);
    }
?>



<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'visform.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form id="item-form" class="form-validate" action="<?php echo JRoute::_('index.php?option=com_visforms&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm">
    <?php if (!empty( $this->sidebar)) : ?>
<div id="j-sidebar-container" class="span2">
    <?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
    
    <div class="form-inline form-inline-header">
	<?php
	echo $this->form->getControlGroup('title');
	echo $this->form->getControlGroup('name');
	?>
</div>
    <div class="form-horizontal">
	<?php  $formFieldSets = $this->form->getFieldsets(); ?>
    <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'visform-basic-details')); ?>
	<?php 
    //custom layout for first tab
    foreach ($formFieldSets as $name => $fieldSet) 
    { ?> 
        <?php if ($name === 'visform-basic-details') 
        { ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', $name, JText::_($fieldSet->label)); ?>
		<div class="row-fluid">
			<div class="span12">
				<fieldset class="adminform">				
					<?php foreach ($this->form->getFieldset($name) as $field) 
                    { 
                        echo $field->getControlGroup();
                    } ?>
			
				</fieldset>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
			
        <?php 
        } ?>
    <?php 
    } ?>
	
    <?php 
    //layout for all other tabs except the permissions tab
    foreach ($formFieldSets as $name => $fieldSet) { ?> 
        <?php if ( (!($name === 'access-rules')) && (!($name === 'visform-basic-details')) && (!($name === 'form_title'))) { ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', $name, JText::_($fieldSet->label)); ?>
        <div class="row-fluid form-horizontal-desktop">
        <?php 
        //custom layout for plugin mailattachments tab display attachment selection on top with full width; display pdf and csv attachment options below in two colums
        if ($name === 'visforms-extension-mailattachments') { ?>
            <div class="span12" style="padding-right: 20px;">
                <?php foreach ($this->form->getFieldset($name) as $field) 
                {  
                    if (!($field->group == 'visformsmailattachments_params.exportsettings'))
                    {
                        if (($field->fieldname == 'f_attachment_list_hidden') || ($field->fieldname == 'visformsmailattachemtsspacer'))
                        {
                            echo $field->getControlGroup();
                        }
                    }
                } ?>    
            </div>
            <div class="span6">
            <?php foreach ($this->form->getFieldset($name) as $field) 
            {  
                if (!($field->group == 'visformsmailattachments_params.exportsettings'))
                {
                    if ((!($field->fieldname == 'f_attachment_list_hidden')) && (!($field->fieldname == 'visformsmailattachemtsspacer')))
                    {
                        echo $field->getControlGroup();
                    }
                }
            } ?>    
        </div>
            <div class="span6">
                <?php foreach ($this->form->getFieldset($name) as $field) 
                {  
                    if ($field->group == 'visformsmailattachments_params.exportsettings')
                    {
                        echo $field->getControlGroup(); 
                    }
                } ?>    
            </div>
        <?php } else { 
            //all the other tabs
            ?>		
            <div class="span12">
            <?php foreach ($this->form->getFieldset($name) as $field) 
            {  
                 echo $field->getControlGroup(); 
            } ?>                   
            </div>
        <?php } ?>
        </div>

        <?php echo JHtml::_('bootstrap.endTab'); ?>				
        <?php } ?>
    <?php } ?>
			
    <div class="clr"></div>
    <?php
    //layout for permissions tab
    foreach ($formFieldSets as $name => $fieldSet) 
    {  
         if ($name === 'access-rules') 
        {
             if ($this->canDo->get('core.admin')) 
            {
             echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_VISFORMS_FIELDSET_FORM_RULES', true)); 
             echo $this->form->getInput('rules'); 
             echo JHtml::_('bootstrap.endTab'); 
            } 
        } 
    } 
     echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>

<input type="hidden" name="task" value="" />

<?php echo JHtml::_('form.token'); ?>
    </div>
</div>
</form>
 <?php JHtml::_('visforms.creditsBackend'); ?>