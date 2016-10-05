<?php
/**
 * Visforms bootstrap default view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 
	
if ($this->visforms->published != '1') {return;}


JHTMLVisforms::includeScriptsOnlyOnce(array('visforms' => false, 'bootstrapform' => $this->visforms->usebootstrapcss));

?>

        <form action="<?php echo JRoute::_($this->formLink) ; ?>" method="post" name="visform" id="visform<?php echo $this->visforms->id; ?>" class="visform <?php echo $this->visforms->formCSSclass; echo ($this->visforms->formlayout == "bthorizontal") ? " form-horizontal " : "";?>"<?php if($this->upload == true) { ?> enctype="multipart/form-data"<?php } ?>>
		<fieldset>
		
<?php 
	//Explantion for * if at least one field is requiered at the top of the form
	if ($this->required == true && $this->visforms->required == 'top')
	{
        echo JHtml::_('visforms.getRequired', $this->visforms);
     } 
 
	//first hidden fields at the top of the form
	for ($i=0;$i < $this->nbFields; $i++)
	{ 
		$field = $this->visforms->fields[$i];
		if ($field->typefield == "hidden")
		{
            echo $field->controlHtml;
		}
	}

	//then inputs, textareas, selects and fieldseparators
	for ($i=0;$i < $this->nbFields; $i++)
	{ 
            $field = $this->visforms->fields[$i];
            if ($field->typefield != "hidden" && !isset($field->isButton))
            {	
                //set focus to first visible field
                 if (($this->firstControl == true) && ((!(isset($field->isDisabled))) || ($field->isDisabled == false)))
                {
                    $script= '';
                    $script .= 'jQuery(document).ready( function(){';
                    $script .= 'jQuery("#'. $field->errorId.'").focus();';
                    $script .= '});';
                    $doc = JFactory::getDocument();
                    $doc->addScriptDeclaration($script);
                    $this->firstControl = false;
                }
                echo $field->controlHtml;
            }   	
    }
	//Explantion for * if at least one field is requiered above captcha
	if ($this->required == true && $this->visforms->required == 'captcha')
	{
        echo JHtml::_('visforms.getRequired', $this->visforms);
    }
    if (isset($this->visforms->captcha) && ($this->visforms->captcha == 1 || $this->visforms->captcha == 2))
	{
        echo JHtml::_('visforms.getCaptchaHtml', $this->visforms);
	} 

	//Explantion for * if at least one field is requiered above submit
	if ($this->required == true && $this->visforms->required == 'bottom')
	{
       echo JHtml::_('visforms.getRequired', $this->visforms);   
    } 
?>
    
    <div class="form-actions">
	<?php 
	//all button on the bottom of the form
	for ($i=0;$i < $this->nbFields; $i++)
	{ 
		$field = $this->visforms->fields[$i];
		if (isset($field->isButton) && $field->isButton === true)
		{
            echo $field->controlHtml; 
		}
	}


?>
	</div>
    </fieldset>
    <input type="hidden" value="<?php echo $this->visforms->id; ?>" name="postid" />
    <?php
        $input = JFactory::getApplication()->input;
        $tmpl = $input->get('tmpl');
        if (isset($tmpl))
        {
           echo '<input type="hidden" value="' . $tmpl . '" name="tmpl" />';
        }
		$creturn = $input->get('creturn');
        if (isset($creturn))
        {
           echo '<input type="hidden" value="' . $creturn . '" name="creturn" />';
        }
        if (!empty($this->return))
        {
           echo '<input type="hidden" value="' . $this->return . '" name="return" />';
        }
    ?>
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
