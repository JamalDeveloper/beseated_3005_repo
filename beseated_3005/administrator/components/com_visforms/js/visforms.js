/* 
 *Creates dynamically view visfield appearance
 *according to selected field type and subtype

 *for Joomla 2.5
*/

var editValue = null;

if (window.addEventListener)
{
	window.addEventListener("load", initPage, false);
} 
else if (window.attachEvent)
{ 
	var r = window.attachEvent("onload", initPage); 
} 
else 
{
	window.alert("Problem to add EventListener to Window Object !");  
}

function initPage() 
{	
	typeFieldInit();
}

//hide parameters from "defaultvalue" for all field types
function hiddenProperties() 
{
	$('visf_text').setStyle('display','none');
	$('visf_email').setStyle('display','none');
	$('visf_date').setStyle('display','none');
	$('visf_url').setStyle('display','none');
	$('visf_number').setStyle('display','none');
	$('visf_password').setStyle('display','none');
	$('visf_hidden').setStyle('display','none');
	$('visf_textarea').setStyle('display','none');
	$('visf_checkbox').setStyle('display','none');
    $('visf_multicheckbox').setStyle('display','none');
	$('visf_radio').setStyle('display','none');
	$('visf_select').setStyle('display','none');
	$('visf_file').setStyle('display','none');
	$('visf_image').setStyle('display','none');
	$('visf_reset').setStyle('display','none');
	$('visf_submit').setStyle('display','none');
	$('visf_fieldsep').setStyle('display','none');

}

//initialise field, display parameters for selected field type 
function typeFieldInit() 
{	
	hiddenProperties();
	
	var ffield = 'visf_' + $('jform_typefield').getProperty('value');
	
    //no type set yet
    //or sumit or reset which have not hidden properties and so nothing to display
	if ((ffield != 'visf_0') && (ffield != 'visf_submit') && (ffield != 'visf_reset'))
	{
		$(ffield).setStyle('display','');
	}
    setRequiredAsterix ();
    editOnlyFieldChange();
}

//perform actions which are necessary when the type of a field is changed
function typeFieldChange() 
{
	
	hiddenProperties();
	
	var ffield = 'visf_' + $('jform_typefield').getProperty('value');
	
    //no type set yet
    //or sumit or reset which have not hidden properties and so nothing to display
	if ((ffield != 'visf_0') && (ffield != 'visf_submit') && (ffield != 'visf_reset'))
	{
		$(ffield).setStyle('display','');
	}
    //Insert an asterix for required options
    setRequiredAsterix ()
    //Handle restsricts
    resetEqualToList ();

}

function formatFieldDateChange(text) 
{
    if (text != "")
    {
        alert(text);
    }
    else
    {
        // set selected in dateformat select list to new value
        formatFieldDateChangeSelected ();

        // setup calendar with correct dateformat
        formatDateCalendarChange ();

        // display date in input field in correct format
        formatDateChangeInputValue ();
    }	
}

function formatDateChangeInputValue () 
{

	// get value of initial Date field
	var date = $('jform_defaultvalue_tdate_calender').getProperty('value');
	
	// if there is a date value set, change date format acording to selected listbox value
	if (! date == "") 
	{	
		// find date delimiter
		var date_delimiter = date.match(/\/|-|\./);
		var date_parts = date.split(date_delimiter[0]);

		// get date parts. Each date_delimiter represents a defined date format and a fix position of date parts
		switch (date_delimiter[0]) {
			case "/" :
				var month = date_parts[0];
				var day = date_parts[1];
				var year = date_parts[2];
				break;
			case "-" :
				var year = date_parts[0];
				var month = date_parts[1];
				var day = date_parts[2];
				break;
			case "." :
				var day = date_parts[0];
				var month = date_parts[1];
				var year = date_parts[2];
				break;
		}

		// get new date output format
		var d_format = $('jform_defaultvalue_tdateformat_row').getProperty('value');
	
		//find date format delimiter
		var d_format_delimiter = d_format.match(/\/|-|\./);
		
		// construct the formated date string. Each date format delimiter represents a defined date format and a fix position on date parts
		switch (d_format_delimiter[0]) 
		{
			case '/' :
				var formatted_date = month + d_format_delimiter + day + d_format_delimiter + year;
				break;
			case '-' :
				var formatted_date = year + d_format_delimiter + month + d_format_delimiter + day;
				break;
			case '.' :
				var formatted_date = day + d_format_delimiter + month + d_format_delimiter + year;
				break;
		}
		// set date value with formatted date string
		$('jform_defaultvalue_tdate_calender').setProperty('value', formatted_date);
	}
}

function formatFieldDateChangeSelected () 
{
	for(i=$('jform_defaultvalue_tdateformat_row').options.length-1;i>=0;i--) {
		if($('jform_defaultvalue_tdateformat_row').options[i].getAttribute('selected')) {
			$('jform_defaultvalue_tdateformat_row').options[i].removeAttribute('selected');
		}
		if($('jform_defaultvalue_tdateformat_row').options[i].selected) {
			$('jform_defaultvalue_tdateformat_row').options[i].setAttribute('selected', 'selected');
		}
	}
}

function formatDateCalendarChange () 
{
	// get new date output format
	var d_format = $('jform_defaultvalue_tdateformat_row').getProperty('value');
	
	// get dateformat for php and for javascript
	d_format = d_format.split(';');
	
	Calendar.setup({
		// Id of the input field
		inputField: "jform_defaultvalue_tdate_calender",
		// Format of the input field
		ifFormat: d_format[1], //"%d.%m.%Y",
		// Trigger for the calendar (button ID)
		button: "jform_defaultvalue_tdate_calender_img",
		// Alignment (defaults to "Bl")
		align: "Tl",
		singleClick: true,
		firstDay: 0
	}); 
}

//we need to restict some actions for fields which are restrictors and give an error message
function fieldUsed(o, ffield, msg)
{
    if (o.id.indexOf('editonlyfield') > 0)
    {
        var selected = $(o.id).getSelected();
        var selectedValue = selected[0].value;
        if (selectedValue == "0")
        {
            return true;
        }
    }
    var selectbox = document.getElementById(o.id);
    var optlength = selectbox.options.length;
    for (var i = 0; i < optlength; i++) 
    {
        if (selectbox.options[i].value == ffield) {
            selectbox.options[i].selected = true;
            jQuery('#' + o.id).trigger('liszt:updated');
        }
    }
    window.alert(msg);
    return false;
}

//delete a specific option from an option list
function deleteOption(value, index, ar)
{
    if (value.value != 0)
    {
        value.dispose();
    }
}

//on typefield change we have to reset the equal to list
 function resetEqualToList ()
{
    var o = $('jform_typefield');
    //remove all options exept the default option from the field list in parameter equalTo
    var fieldtype = $(o).getSelected();
    var equalToList = document.id('jform_defaultvalue_f_' + fieldtype[0].value + '_validate_equalTo');
    if (equalToList)
    {
    var options = $(equalToList).getElements('option');
    options.each(deleteOption);
    }
    //remove handler
    var changehandler = $(o).get('onchange');
    var newChangehandler = changehandler.replace(/resetEqualToList\(this\);/, '');
    $(o).set('onchange', newChangehandler);
}

//set asterix in labels for parameters which are required
//we cannot use Joomla! form field attribute required because we get an error when a hidden parameter which is required is not set and we try to save the visforms field
function setRequiredAsterix ()
{
   var ft = document.getElementById('jform_typefield');
   var idx = ft.selectedIndex;
   var sel = ft[idx].value;
    switch (sel)
    { 
        case 'checkbox' :
            var el = [document.getElementById('jform_defaultvalue_f_checkbox_attribute_value-lbl')];
            break;
        case 'image':
            var el = [document.getElementById('jform_defaultvalue_f_image_attribute_alt-lbl')];
            el.push (document.getElementById('jform_defaultvalue_f_image_attribute_src-lbl')); 
            break;
        case 'multicheckbox' :
            var el = [document.getElementById('jform_defaultvalue_f_multicheckbox_list_hidden-lbl')];
            break;
        case 'select' :
            var el = [document.getElementById('jform_defaultvalue_f_select_list_hidden-lbl')];
            break;
        case 'radio' :
            var el = [document.getElementById('jform_defaultvalue_f_radio_list_hidden-lbl')];
            break;
        default :
            break;
    }
    if (el)
    {
    el.each (changeLabel);
    }
}


//insert asterix in label
function changeLabel (el, index, arr)
{
     var label = el.get('text') + '<span class="star"> *</span>';
     el.set('html', label);
}

//we use jQuery here
function editOnlyFieldChange()
{
    var o = $('jform_typefield');
    //remove all options exept the default option from the field list in parameter equalTo
    var fieldtype = $(o).getSelected();
    var editonly = document.id('jform_' + 'editonlyfield');
    if (editonly)
    {
        var equalToList = document.id('jform_defaultvalue_f_' + fieldtype[0].value + '_validate_equalTo');
        var showWhenList = document.id('jform_defaultvalue_f_' + fieldtype[0].value + '_showWhen');
        var selected = $(editonly).getSelected();
        if (selected[0].value === "1")
        {
            //hide equalto and conditional fields
            if (equalToList)
            {
                equalToList.getParents('.control-group').set('style', 'display: none;');
            }
            if (showWhenList)
            {
                showWhenList.getParents('.control-group').set('style', 'display: none;');
            }
        }
        else
        {
            //show equalto and conditional fields
            if (equalToList)
            {
                equalToList.getParents('.control-group').removeProperty('style');
            }
            if (showWhenList)
            {
                showWhenList.getParents('.control-group').removeProperty('style');
            }
        }
    }
}

