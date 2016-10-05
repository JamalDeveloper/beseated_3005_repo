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
	
if ($visforms->published != '1') {return;}

//retrieve helper variables from params
$nbFields=$params->get('nbFields');
$required = $params->get('required');
$upload = $params->get('upload');
$textareaRequired = $params->get('textareaRequired');
$hasHTMLEditor = $params->get('hasHTMLEditor');

JHTMLVisforms::includeScriptsOnlyOnce(array('visforms' => false, 'bootstrapform' => $visforms->usebootstrapcss));

?>

    <form action="<?php echo JRoute::_($formLink) ; ?>" method="post" name="visform" id="mod-visform<?php echo $visforms->id; ?>" class="mcindividual visform <?php echo $visforms->formCSSclass; ?>"<?php if($upload == true) { ?> enctype="multipart/form-data"<?php } ?>>
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
    $counter = 0;
    echo '<div class="row-fluid">';
	for ($i=0;$i < $nbFields; $i++)
	{
        $field = $visforms->fields[$i];
        $bt_size = (isset($field->bootstrap_size) && ($field->bootstrap_size > 0)) ? $field->bootstrap_size : 6;
        if ($field->typefield != "hidden" && !isset($field->isButton))
        {	
            $counter += $bt_size;
            if ($counter > 12)
            {
                echo '</div>';
                $counter = $bt_size;
                echo '<div class="row-fluid">';
            }
            echo $field->controlHtml;
            $test = true;
        }   	
    }
    echo '</div>';
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
    <div class="clearfix"></div>
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
<script type="text/javascript">
    //Window resize
    jQuery( window ).resize(function() {
        setFormPosition();
    });

    function setFormPosition () {
        jQuery("#mod-visform<?php echo $visforms->id; ?> div[class^='fc-tbxfield']").each( function (i, el) {
            var errorDivClass = jQuery(el).attr('class');
            var fieldid = errorDivClass.replace('fc-tbx', '');
            if (fieldid.indexOf('_') > 0)
            {
                var split = fieldid.split('_')
                if ((jQuery.isArray(split)) && (split.length > 0))
                {
                    fieldid = split[0];
                }
            }
            jQuery("." + fieldid).each(function(idx, control) {
                var position = jQuery(control).position();
                
            jQuery(el).css({
                "position" : "absolute",
                "bottom": "100%",
                "left" : position.left
             });
            });
        });
    }
            
        
    jQuery(document).ready(function () {
        setFormPosition();
        jQuery(".displayChanger").on('change', function (e) {
            setFormPosition();
        });
    });
</script>