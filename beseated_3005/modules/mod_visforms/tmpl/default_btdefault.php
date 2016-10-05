<?php
/**
 * Mod Visforms Form
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   mod_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 
	
if ($visforms->published != '1') 
{
    return;
}

//retrieve helper variables from params
$nbFields=$params->get('nbFields');
$required = $params->get('required');
$upload = $params->get('upload');
$textareaRequired = $params->get('textareaRequired');
$hasHTMLEditor = $params->get('hasHTMLEditor');

JHTMLVisforms::includeScriptsOnlyOnce(array('visforms' => false, 'bootstrapform' => $visforms->usebootstrapcss));

?>

        <form action="<?php echo JRoute::_($formLink) ; ?>" method="post" name="visform" id="mod-visform<?php echo $visforms->id; ?>" class="visform <?php echo $visforms->formCSSclass; echo ($visforms->formlayout == "bthorizontal") ? " form-horizontal " : "";?>"<?php if($upload == true) { ?> enctype="multipart/form-data"<?php } ?>>
		<fieldset>
		
<?php 
	//Explantion for * if at least one field is requiered at the top of the form
	if ($required == true && $visforms->required == 'top')
	{
        echo JHtml::_('visforms.getRequired', $visforms);
     } 
 
	//first hidden fields at the top of the form
	for ($i=0;$i < $nbFields; $i++)
	{ 
		$field = $visforms->fields[$i];
		if ($field->typefield == "hidden")
		{
            echo $field->controlHtml;
		}
	}

	//then inputs, textareas, selects and fieldseparators
	for ($i=0;$i < $nbFields; $i++)
	{ 
            $field = $visforms->fields[$i];
            if ($field->typefield != "hidden" && !isset($field->isButton))
            {
                 echo $field->controlHtml;
            }   	
    }
	

	//Explantion for * if at least one field is requiered above captcha
	if ($required == true && $visforms->required == 'captcha')
	{
        echo JHtml::_('visforms.getRequired', $visforms);
    }
    if (isset($visforms->captcha) && ($visforms->captcha == 1 || $visforms->captcha == 2))
	{
        echo JHtml::_('visforms.getCaptchaHtml', $visforms);
	} 

	//Explantion for * if at least one field is requiered above submit
	if ($required == true && $visforms->required == 'bottom')
	{
       echo JHtml::_('visforms.getRequired', $visforms);   
    } 
?>
    
    <div class="form-actions">
	<?php 
	//all button on the bottom of the form
	for ($i=0;$i < $nbFields; $i++)
	{ 
		$field = $visforms->fields[$i];
		if (isset($field->isButton) && $field->isButton === true)
		{
            echo $field->controlHtml; 
		}
	}


?>
	</div>
    </fieldset>
    <input type="hidden" name="return" value="<?php echo $return; ?>" />
	<input type="hidden" value="<?php echo $visforms->id; ?>" name="postid" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
