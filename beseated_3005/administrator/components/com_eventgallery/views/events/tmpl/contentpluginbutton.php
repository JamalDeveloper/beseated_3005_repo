<?php 

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access'); 

JHtml::_('behavior.tooltip');
$version =  new JVersion();
if ($version->isCompatible('3.0')) {
	JHtml::_('formbehavior.chosen', 'select');
} else {
}

$this->form = $this->get('ContentPluginButtonForm');

$script  = '

function insertContentTag() {

	var eventElement = document.getElementById("jform_folder_id");
	var event = eventElement.value;

	var attrElement = document.getElementById("jform_attr");
	var attr = attrElement.options[attrElement.selectedIndex].value;

	var type = "";
	if (attr == "text_intro") {
		attr = "text";
		type = "intro";
	}

	if (attr == "text_full") {
		attr = "text";
		type = "full";
	}

	var modeElement = document.getElementById("jform_image_mode");
	var mode = modeElement.options[modeElement.selectedIndex].value;

	var max_images = document.getElementById("jform_max_images").value;
	var offset = document.getElementById("jform_offset").value;
	var thumb_width = document.getElementById("jform_image_width").value;

	var tag   = "{eventgallery ";
	tag = tag + "event=\'" + event +"\' ";
	tag = tag + "attr="+ attr +" ";
	
	if (attr == "text") {
		tag = tag + "type="+ type +" ";
	} 
	
	if (attr == "images") {
		tag = tag + "mode="+ mode +" "; 
		tag = tag + "max_images="+ max_images +" ";
		tag = tag + "thumb_width="+ thumb_width + " ";
		tag = tag + "offset="+ offset + " ";
	}

	tag = tag + "}";

	console.log(tag);

	window.parent.jInsertEditorText(tag, \''.$this->escape(JRequest::getString('e_name')).'\')
	window.parent.SqueezeBox.close()
	return false;
}

';

JFactory::getDocument()->addScriptDeclaration($script);
?>



<?php echo $this->loadSnippet('formfields'); ?>

<button onclick="insertContentTag();"><?php echo JText::_('COM_EVENTGALLERY_CONTENTPLUGINBUTTON_BUTTON_INSERT'); ?></button>

<pre>
Syntax: 

- {eventgallery event="foo" max_images=4 thumb_width=75}

max_images and thumb_width are optional. The value for the event parameter has to be the value of the folder name field of an event. Set max_images=-1 to show all images of an event.

### Display Name

If you want to display the display name of an event use this tag: 

- {eventgallery event="foo" attr="displayname"}

### Text

If you want to display the text of an event use this tag: 

- {eventgallery event="foo" attr="text" type="intro"}
- {eventgallery event="foo" attr="text" type="full"}

If you used a page break tag in your text the intro and full content will be different. 

### Lightbox

You want to use the lightbox instead of a link to the gallery? Just add the parameter mode=lightbox.

- {eventgallery event="foo" max_images=4 thumb_width=75 mode=lightbox}

### Image List / Grid List / Tiles List

If you want to include a image list with the auto layout which is used on event pages too you can use the following tag. Be carefull with this tag. As of now only one event or tag per page is supported with this format. 

- {eventgallery event="foo" max_images=4 offset=3 mode=imagelist}
- {eventgallery event="foo" max_images=4 offset=3 mode=grid}
- {eventgallery event="foo" max_images=4 offset=3 mode=tiles}

### Show more images in the lightbox
	
If you want to include some preview thumbnail in you article but want to show more images in the lightbox you can use the mode=hidden parameter. It will render invisible thumbnails which the lightbox will pick up later on. To make this work you need to include at least one other tag which loads thumbnails for this event.
- {eventgallery event="foo" offset=5 max_images=4 mode=hidden}					
</pre>