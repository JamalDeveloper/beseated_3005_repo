<?php
/**
 * Visforms default view for Visforms
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

	$this->nbFields=count($this->visforms->fields );
	//get some infos to look whether it's neccessary to add Javascript or special HTML-Code or not
	//variables are set to true if they are true for at least one field
	$this->required = false;
	$this->upload = false;
	$this->textareaRequired = false;
    $this->hasHTMLEditor = false;
    //helper, used to set focus on first visible field
    $this->firstControl = true;
	
	for ($i=0;$i < $this->nbFields; $i++)
	{ 
		$field = $this->visforms->fields[$i];
		//set the controll variables
		if (isset($field->attribute_required) && ($field->attribute_required == "required")) 
		{
			$this->required = true;
		}
		if (isset($field->typefield) && $field->typefield == "file")
		{
			$this->upload = true;
		}
		if (isset($field->textareaRequired) && $field->textareaRequired === true) 
		{
			//we have some work to do to use Javascript to validate that the textarea has content
			$this->textareaRequired = true;
		}
                if (isset($field->hasHTMLEditor) && $field->hasHTMLEditor == true)
                {
                    $this->hasHTMLEditor = true;
                }
	}
?>

<div class="visforms-form<?php echo $this->menu_params->get( 'pageclass_sfx' ); ?>" id="visformcontainer">
    <?php if (isset($this->visforms->errors) && is_array($this->visforms->errors) && count($this->visforms->errors) > 0)
    {
        $layout = new JLayoutFile('visforms.error.messageblock', null);
        $layout->setOptions(array('component' => 'com_visforms'));
        $html = $layout->render(array('errormessages' => $this->visforms->errors, 'context' => 'form'));
        echo $html;
    }
    ?>
<?php 
if ($this->menu_params->get('show_page_heading') == 1) 
	{ 
		if (!$this->menu_params->get('page_heading') == "") { ?>
			<h1><?php echo $this->menu_params->get('page_heading'); ?></h1>
	<?php 
	}
	else 
	{ ?>
		<h1><?php echo $this->visforms->title; ?></h1>
	<?php
	}
}?>

<script type="text/javascript">


jQuery(document).ready( function(){
    <?php 
	
	if ($this->textareaRequired == true ||$this->hasHTMLEditor == true)
	{ 
            //we need an editor and create a simple tinyMCE editor 
            VisformsEditorHelper::initEditor();
?>
            // Create a simple plugin
            tinymce.create('tinymce.plugins.TestPlugin', {
                TestPlugin : function(ed, url) {
                    //add function that will update content of tinyMCE on change (is only called, when user clicks outside editor        
                     ed.on ("change", function(ed) {
                          updateText(ed);
                      });
                     //add function that will update content of tinyMCE on submit
                     ed.on("submit", function(ed)
                     {
                          return updateText(ed);
                     });
                }
            });

            // Register plugin using the add method
            tinymce.PluginManager.add('test', tinymce.plugins.TestPlugin);

            //copy content of editor into a textarea field and validate content of that textarea
            function updateText(ed) {
                //get id of textarea which belongs to the editor
                var inputId = ed.target.id;
                //copy editor content into textarea
                tinyMCE.triggerSave();
                <?php if ($this->textareaRequired == true) { 
                //validate content of textarea
                echo 'return jQuery("#" + inputId).valid();';
                 } ?>
            };
<?php 
	}
?>    
        var validator = jQuery(document).ready(function() {
            jQuery('#visform<?php echo $this->visforms->id; ?>').validate({
                wrapper: "p",
                //absolutly necessary when working with tinymce!
                ignore: ".ignore",
                rules: {
<?php
                    //insert rules that we cannot put into html attributes because they are no valid attributs or valid attribute values
                    for ($i=0;$i < $this->nbFields; $i++)
                    { 
                        $field = $this->visforms->fields[$i];
                        if(isset($field->validateArray))
                        {
                            if (isset($field->typefield) && ($field->typefield == "select" || $field->typefield == "multicheckbox"))
                            {
								echo "\"" . $field->name . "[]\": {";
							}
							else
							{
								echo "\"". $field->name . "\" : {";
							}
                            foreach ($field->validateArray as $n => $v)
                            {
                               if (($n == "equalTo") || ($n == "remote"))
                                {
                                    echo $n . ": \"" . $v . "\","; 
                                }
                                else
                                {
                                    echo $n . ": " . $v . ","; 
                                }  
                            }
                            echo "},";
                            unset($n);
                            unset($v);
                        }
?>
<?php
                    }
                    //recaptcha code comes from google api. Because we use the joomla recaptcha plugin we cannot change much field attribute values... and have to include a rule for the captcha
                    if (isset($this->visforms->captcha) && ($this->visforms->captcha == 2))
                    {
                        echo 'recaptcha_response_field : { required : true},';
                    }
?>
                },
                messages: {
<?php
                    //Include custom error messages
                    for ($i=0;$i < $this->nbFields; $i++)
                    { 
                        $field = $this->visforms->fields[$i];
                        //Custom Error Messages for date fields
                        if (isset($field->typefield) && $field->typefield == "date" && !(isset($field->customErrorMsgArray)))
                        {
                            if(isset($field->dateFormatJs))
                            {
                                switch ($field->dateFormatJs)
                                {
                                    case "%d.%m.%Y":
                                    echo "\"". $field->name . "\" : { dateDMY: \"" . JText::_('COM_VISFORMS_ENTER_VALID_DATE')  . "\" },";
                                    break;
                                    case "%m/%d/%Y":
                                    echo "\"". $field->name . "\" : { dateMDY: \"" . JText::_('COM_VISFORMS_ENTER_VALID_DATE')  . "\" },";
                                    break;
                                    case "%Y-%m-%d":
                                    echo "\"". $field->name . "\" : { dateYMD: \"" . JText::_('COM_VISFORMS_ENTER_VALID_DATE')  . "\" },";
                                    break;
                                }
                            }
                        }
                        //Custom Error Messages
                        if(isset($field->customErrorMsgArray))
                        {
                            //Custom Error Messages for Selects and multicheckboxes
                            if (isset($field->typefield) && ($field->typefield == "select" || $field->typefield == "multicheckbox"))
                            {
                            echo "\"" . $field->name . "[]\": {";
                            foreach ($field->customErrorMsgArray as $n => $v)
                            {
                               echo  $n . ": \"" . $v . "\","; 
                            }
                            echo "},";
                            }
                            else
                            {
                                //Custom Error Messages for 'normal' fields
                                echo "\"" . $field->name . "\": {";
                                foreach ($field->customErrorMsgArray as $n => $v)
                                {
                                   echo  $n . ": \"" . $v . "\","; 
                                }
                                echo "},";
                            }
                        }
                        else
                        {
                            //Adapat Error message for multicheckbox minlength, maxlength if we use the default message texts
                            if (isset($field->typefield) &&  ($field->typefield == "multicheckbox"))
                            {
                                echo "\"" . $field->name . "[]\": {";
                                echo "minlength: jQuery.format('". JText::_( 'COM_VISFORMS_ENTER_VAILD_MINLENGTH_MULTICHECKBOX' )."'),";
                                echo "maxlength: jQuery.format('". JText::_( 'COM_VISFORMS_ENTER_VAILD_MAXLENGTH_MULTICHECKBOX' )."')";
                                echo "},";
                            }
                        }
                        //Custom Captcha Error Message
                        if(isset($this->visforms->captchacustomerror) && $this->visforms->captchacustomerror != "")
                        {
                            echo "\"recaptcha_response_field\": {"; 
                            echo  "required" . ": \"" . $this->visforms->captchacustomerror . "\","; 
                            echo "},";
                        }
                    }
?>
                },
                errorPlacement: function (error, element){
                    var errorfieldid = element.attr("id");
                    errorfieldid = errorfieldid.replace(/_\d+$/, '_0');
                    error.appendTo('div.fc-tbx' + errorfieldid);
                    error.addClass("errorcontainer");
                },

            });
                
        });
        jQuery.extend(jQuery.validator.messages, {
        required: '<?php echo addslashes(JText::_( 'COM_VISFORMS_ENTER_REQUIRED' )); ?>',
		remote: "Please fix this field.",
		email: '<?php echo addslashes(JText::_( 'COM_VISFORMS_ENTER_VALID_EMAIL' )); ?>',
		url: '<?php echo addslashes(JText::_( 'COM_VISFORMS_ENTER_VALID_URL' )); ?>',
		date: '<?php echo addslashes(JText::_( 'COM_VISFORMS_ENTER_VALID_DATE' )); ?>',
		dateISO: "Please enter a valid date (ISO).",
		number: '<?php echo addslashes(JText::_( 'COM_VISFORMS_ENTER_VALID_NUMBER' )); ?>',
		digits: '<?php echo addslashes(JText::_( 'COM_VISMORMS_ENTER_VALID_DIGIT' )); ?>',
		creditcard: "Please enter a valid credit card number.",
		equalTo: '<?php echo addslashes(JText::_( 'COM_VISFORMS_ENTER_CONFIRM' )); ?>',
		maxlength: jQuery.validator.format('<?php echo addslashes(JText::_( 'COM_VISFORMS_ENTER_VAILD_MAXLENGTH' )); ?>'),
		minlength: jQuery.validator.format('<?php echo addslashes(JText::_( 'COM_VISFORMS_ENTER_VAILD_MINLENGTH' )); ?>'),
		rangelength: jQuery.validator.format('<?php echo addslashes(JText::_( 'COM_VISMORMS_ENTER_VAILD_LENGTH' )); ?>'),
		range: jQuery.validator.format('<?php echo addslashes(JText::_( 'COM_VISFORMS_ENTER_VAILD_RANGE' )); ?>'),
		max: jQuery.validator.format('<?php echo addslashes(JText::_( 'COM_VISFORMS_ENTER_VAILD_MAX_VALUE' )); ?>'),
		min: jQuery.validator.format('<?php echo addslashes(JText::_( 'COM_VISFORMS_ENTER_VAILD_MIN_VALUE' )); ?>')
        });
        
        <?php
        //add custom validation with regex
        for ($i=0;$i < $this->nbFields; $i++)
        { 
            $field = $this->visforms->fields[$i];
            if((isset($field->addMethod)) && (is_array($field->addMethod)))
            {
         ?>
                 jQuery.validator.addMethod("<?php echo $field->addMethod['methodname']; ?>", function(value, element) {
                     var re = /<?php echo $field->addMethod['regex']; ?>/;
                     return this.optional(element) || re.test(value);
                 }, <?php echo (isset($field->customerror) && $field->customerror != "") ? "\"" . $field->customerror. "\"" : "\"" . JText::_('COM_VISFORMS_INVALID_INPUT')  . "\""; ?>);
           <?php
            }
        }
        ?>
                
        jQuery.validator.addMethod("dateDMY", function(value, element) {
            var check = false;
            var re = /^(0[1-9]|[12][0-9]|3[01])[\.](0[1-9]|1[012])[\.]\d{4}$/;
            if( re.test(value)) {
                    var adata = value.split('.');
                    var day = parseInt(adata[0],10);
                    var month = parseInt(adata[1],10);
                    var year = parseInt(adata[2],10);
                    if (day == 31 && (month == 4 || month == 6 || month == 9 || month == 11)) {
                        check = false; // 31st of a month with 30 days
                        } else if (day >= 30 && month == 2) {
                        check = false; // February 30th or 31st
                        } else if (month == 2 && day == 29 && ! (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0))) {
                        check = false; // February 29th outside a leap year
                        } else {
                        check = true; // Valid date
                        }
                    }
            return this.optional(element) || check;
        }, <?php echo (isset($field->customerror) && $field->customerror != "") ? "\"" . $field->customerror. "\"" : "\"" . JText::_('COM_VISFORMS_ENTER_VALID_DATE')  . "\""; ?>);
        jQuery.validator.addMethod("dateMDY", function(value, element) {
            var check = false;
            var re = /^(0[1-9]|1[012])[\/](0[1-9]|[12][0-9]|3[01])[\/]\d{4}$/;
            if( re.test(value)) {
                    var adata = value.split('/');
                    var month = parseInt(adata[0],10);
                    var day = parseInt(adata[1],10);
                    var year = parseInt(adata[2],10);
                    if (day == 31 && (month == 4 || month == 6 || month == 9 || month == 11)) {
                        check = false; // 31st of a month with 30 days
                        } else if (day >= 30 && month == 2) {
                        check = false; // February 30th or 31st
                        } else if (month == 2 && day == 29 && ! (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0))) {
                        check = false; // February 29th outside a leap year
                        } else {
                        check = true; // Valid date
                        }
                    }
            return this.optional(element) || check;
        }, <?php echo (isset($field->customerror) && $field->customerror != "") ? "\"" . $field->customerror. "\"" : "\"" . JText::_('COM_VISFORMS_ENTER_VALID_DATE')  . "\""; ?>);
        jQuery.validator.addMethod("dateYMD", function(value, element) {
            var check = false;
            var re = /^\d{4}[\-](0[1-9]|1[012])[\-](0[1-9]|[12][0-9]|3[01])$/;
            if( re.test(value)) {
                    var adata = value.split('-');
                    var year = parseInt(adata[0],10);
                    var month = parseInt(adata[1],10);
                    var day = parseInt(adata[2],10);
                    if (day == 31 && (month == 4 || month == 6 || month == 9 || month == 11)) {
                        check = false; // 31st of a month with 30 days
                        } else if (day >= 30 && month == 2) {
                        check = false; // February 30th or 31st
                        } else if (month == 2 && day == 29 && ! (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0))) {
                        check = false; // February 29th outside a leap year
                        } else {
                        check = true; // Valid date
                        }
                    }
            return this.optional(element) || check;
        }, <?php echo (isset($field->customerror) && $field->customerror != "") ? "\"" . $field->customerror. "\"" : "\"" . JText::_('COM_VISFORMS_ENTER_VALID_DATE')  . "\""; ?>);

        jQuery('.captcharefresh<?php echo $this->visforms->id; ?>').bind({
            'click' : function() {
                if (jQuery('#captchacode<?php echo $this->visforms->id; ?>')) 
                {
                    jQuery('#captchacode<?php echo $this->visforms->id; ?>').attr('src', 'index.php?option=com_visforms&task=visforms.captcha&sid=' + Math.random() + '&id=<?php echo $this->visforms->id; ?>');
                }
            }
        });	
});

jQuery(document).ready( function(){
    jQuery(document).displayChanger();
    <?php 
        $restrictData = array();
        for ($i=0;$i < $this->nbFields; $i++)
        { 
            $field = $this->visforms->fields[$i];
            if(isset($field->showWhenForForm) && (is_array($field->showWhenForForm)))
            {
                $restrictData[] = 'field' . $field->id . ' : ' .  '"' . implode(', ', $field->showWhenForForm) . '"';
            }
        }
        $restrictDataString = "{" . implode(", ", $restrictData) . "}";
    ?>
    jQuery(".conditional").on("checkConditionalState", {restricts : <?php echo $restrictDataString; ?>}, function (e) {
           jQuery(this).toggleDisplay(e.data.restricts);
       });
        
});

//fix placeholder for IE7, IE8, IE9
jQuery(document).ready(    
    function () {
        if (!jQuery.support.placeholder) {
            jQuery("[placeholder]").focus(function () {
                if (jQuery(this).val() == jQuery(this).attr("placeholder")) jQuery(this).val("");
            }).blur(function () {
                if (jQuery(this).val() == "") jQuery(this).val(jQuery(this).attr("placeholder"));
            }).blur();

            jQuery("[placeholder]").parents("form").submit(function () {
                jQuery(this).find('[placeholder]').each(function() {
                    if (jQuery(this).val() == jQuery(this).attr("placeholder")) {
                        jQuery(this).val("");
                    }
                });
            });
        }    
});

</script>
  <?php if (strcmp ( $this->visforms->description , "" ) != 0) { ?>
	<div class="category-desc"><?php 
		JPluginHelper::importPlugin('content');
		echo JHtml::_('content.prepare', $this->visforms->description); 
	?>
	</div>
  <?php } 
  
  //display form with appropriate layout
  
  switch($this->visforms->formlayout)
  {
      case 'btdefault' :
      case 'bthorizontal' :
          echo $this->loadTemplate('btdefault');
          break;
      case  'mcindividual' :
          echo $this->loadTemplate('mcindividual');
          break;
      default :
        echo $this->loadTemplate('visforms');
          break;
  }
  
  
  if ($this->visforms->poweredby == '1') { ?>
	<?php JHtml::_('visforms.creditsFrontend'); ?>
<?php } ?>

</div>
