/**
 *------------------------------------------------------------------------------
 *  com_visforms by vi-solutions for Joomla! 3.x
 *------------------------------------------------------------------------------
 * @package     com_visforms
 * @copyright   Copyright (c) 2014 vi-solutions. All rights preserved
 *
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @author      Aicha Vack
 * @link        http://www.vi-solutions.de
 *
 * @version     1.0.0 2014-04-20
 * @since       1.0
 *------------------------------------------------------------------------------
*/

(function(){
    //public plugin functions go in there
	jQuery.extend(jQuery.fn, {
		visformsItemlistCreator : function (options) {
			var defaults = {
				texts : {
					txtMoveUp: "Move Up",
					txtMoveDown: "Move Down",
					txtChange: "Change",
                    txtChangeItem: "Change",
					txtDelete: "Delete",
					txtClose: "Close",
					txtAddItem: "Add item",
                    txtAddAndNewItem: "Add & New",
                    txtCreate: "Create item",
					txtReset: "Reset",
					txtSave: "Save",
                    txtSaveAndNew: "Save & New",
                    txtJYes : "Yes",
                    txtJNo : "No",
                    txtAlertRequired : "Value and Label are required",
                    txtTitle: "Title",
                    txtItemsImported: "Items imported",
                    txtReaderError : "Unable to read file ",
                    txtNoDataToImport: "No data to import!"
				},
                params : {
                    //HTML field id and names and parameter names in db depend on structure of xml file...
                    //name attribute of field that will contain the items string (from xml file)
                    //this name is composed of f_ plus ctype + plus _list_hidden as a concention but you may change the _list_hidden Extension with dbFieldExt option
                    //ctype is an important option which will control the plugin
                    fieldName : 'f_radio_list_hidden',
                    //Prefix of field ids (from fieldset name in xml file)
                    idPrefix : "",
                    //as a convention, this should be _list_hidden (is used to detemine ctype
                    dbFieldExt : "",
                    //list of fields for the popup
                    //each field has the parmaters frequired (true/false), ftype (text/checkbox), fname (field name in json object, stored in db),
                    //flabel (field label in json object, stored in db), fdesc (field description, tooltip for label
                    hdnMFlds : {},
                    ctype : "",
                    listValueAfter : 'itemDown'
                    
                }
			};
			//use true as first paremeter to merge objects recursivly
			var settings = jQuery.extend(true, {}, defaults, options);
            //create an array with field names
            var hdnMFldNames = jQuery.map(settings.params.hdnMFlds, function (n, i) {
                    return n.fname;
            });
			//var hdnMFldNames = ["listitemvalue", "listitemlabel", "listitemischecked"];
            //HTML field id and names and parameter names in db depend on structure of xml file...
            //Prefix of field ids (from fieldset name in xml file)
            //var idPrefix = settings.params.idPrefix;
            //convention fields that store the 
            //var dbFieldExt = settings.params.dbFieldExt;
            var ctype = getCType();
            var dbFieldName = settings.params.idPrefix + settings.params.fieldName;
            var importField = settings.params.idPrefix + "f_" + ctype + settings.params.importField;
            var importSeparator = settings.params.idPrefix + "f_" + ctype + settings.params.importSeparator;
            var hdnItemListId = "hdnItemList" + ctype;
            var addButtonId = "add" + ctype;
            var itemListContId = "itemListCont" + ctype;
            var itemListId = "itemList" + ctype;
            var idFieldName = settings.params.idPrefix + "f_" + ctype + '_lastId';
            //create an array with names of required fields
			var requiredFields = jQuery.map(settings.params.hdnMFlds, function (n, i) {
				if (n.frequired == true) return n.fname;
			});
            
			
			//Protected Helper functions
            
            //get value of the highest used id in itemlist
            function getLastItemId()
            {
                return jQuery("#" + idFieldName).val();
            }
            
            //increment stored value of highest used id
            function setLastItemId()
            {
                var oldId = jQuery("#" + idFieldName).val();
                var newId = 1 + parseInt(oldId);
                jQuery("#" + idFieldName).attr("value", newId);
            }
			
            
            //extract stringified user inputs from hidden field
			function getItemsStr () {
				var itemsDB = jQuery("#" + dbFieldName).val();
				return itemsDB;
			}
			
			//convert stored user inputs string into an object
            function createItemsObjFromString (itemsDB) {
				if (itemsDB != "") {
                    try {
                        var itemsObj = JSON.parse(itemsDB);
                    }
                    catch (error)
                    {
                        var itemsObj =  {};
                        clearHiddenFields();
                        alert('Option list contains invalid data. Therefore option list was emptied.')
                    }
				}
				else {
					var itemsObj =  {};
				}
				return itemsObj;
			}
			
			function getItemsObj () {
				var itemsStr = getItemsStr();
				var itemsObj = createItemsObjFromString(itemsStr);
				return itemsObj;
			}
			
            //stringify user inputs
			function createItemsStr (obj) {
					return itemsStr = JSON.stringify(obj);
			}
			
            //set stringified user inputs string in hidden field, that is stored in Joomla! database
			function setItemsStr (itemsStr) {
				if (itemsStr != "") {
					jQuery("#" + dbFieldName).attr("value", itemsStr);
				}
			}
			
			//remove some enclosing bracket and create a lean image object that only contains the key/value pairs
			function cleanItemArr (arr) {
				$object = {};
				jQuery.each(arr, function() {
				if ($object[this.name] !== undefined) {
					if (!$object[this.name].push) {
						$object[this.name] = [$object[this.name]];
					}
					$object[this.name].push(this.value || '');
					} else {
					   $object[this.name] = this.value || '';
					}
				});
				return $object;
			}
			
			//return user input in one item list configuration field as string
			function getListItem (i, fieldName) {
				var item = getItemsObj()[i];
                var itemName = item[fieldName];
                var fieldtype = "";
                var fields = settings.params.hdnMFlds;
                jQuery.each(fields, function (i,o) {
                    if (o.fname == fieldName)
                    {
                        fieldtype = o.ftype;
                    }
                });
                if (fieldtype == 'checkbox')
                {
                    if (itemName == '1')
                    {
                        itemName = settings.texts.txtJYes;
                    }
                    else
                    {
                        itemName = settings.texts.txtJNo;
                    }
                }
                
                return itemName;
			}
            
            function removeListItemValues(i)
            {
                var li = jQuery("#" + itemListId + " .liItem").eq(i);
				var container = li.find("span.itemValues");
                container.remove();
            }
			
            //Append user input in item list which is visible for user
			function setListItemValue(text, i) {
				var li = jQuery("#" + itemListId + " .liItem").eq(i);
                //if the list element is the first one, we insert it after the a.itemDown arrow, else after the last list element 
                var itemBefore = li.find("span.itemValues");
                if (itemBefore.length < 1)
                {
                    itemBefore = li.find("a." + settings.params.listValueAfter);
                }
                else
                {
                    itemBefore = itemBefore.last();
                }
                var span = jQuery("<span/>", {
                    "class" : "itemValues",
                    html : text
                })
                span.insertAfter(itemBefore);
            }
			
			//create a list item in ul#itemList
			function createListItem () {
				// Create list entry
				var li = jQuery("<li/>", {
					"class" : "liItem",
				});
				jQuery("#" + itemListId).append(li);
				return li;
			}
			
            //create html elements in visible item list
			function createListItemElements (i) {
				var li = jQuery("#" + itemListId + " .liItem").eq(i);
				jQuery("<a/>", {
					"href" : "#",
					"data-target" : "#" + hdnItemListId,
					"class" : "itemUp",
					html : "<i class=\"icon-arrow-up-3\" title=\"" + settings.texts.txtMoveUp + "\"></i>"
				}).appendTo(li);
				jQuery("<a/>", {
					"href" : "#",
					"data-target" : "#" + hdnItemListId,
					"class" : "itemDown",
					html : "<i class=\"icon-arrow-down-3\" title=\"" + settings.texts.txtMoveDown + "\"></i>"
				}).appendTo(li);
				jQuery("<a/>", {
					"href" : "#",
					"data-target" : "#" + hdnItemListId,
					"class" : "itemChange",
					html: settings.texts.txtChange
				}).appendTo(li);
				jQuery("<a/>", {
					"href" : "#",
					"data-target" : "#" + hdnItemListId,
					"class" : "itemRemove",
					html: settings.texts.txtDelete
				}).appendTo(li);
			}
			
            //Disable itemUp/itemDown arrow with CSS, first list item cannot be moved up, last cannot be moved down
			function setArrowClassDisabled (i, up) {
				if (up) {
					var ancor = jQuery("#" + itemListId + " .liItem").eq(i).find(".itemUp");
				}
				else {
					var ancor = jQuery("#" + itemListId + " .liItem").eq(i).find(".itemDown");
				}
					
				ancor.addClass("disabled");		
			}
			
            //enable itemUp/itemDown arrow with CSS
			function removeArrowClassDisabled (i, up) {
				if (up) {
					var ancor = jQuery("#" + itemListId + " .liItem").eq(i).find(".itemUp");
				}
				else {
					var ancor = jQuery("#" + itemListId + " .liItem").eq(i).find(".itemDown");
				}
					
				ancor.removeClass("disabled");		
			}
            
            function checkRequiredFields(form)
            {
                var valid = true;
                jQuery.each(requiredFields, function (key, value) {
                    if (form.find("." + value + " input").val() == "")
                    {
                        form.find("." + value + " input").addClass("error");
                        valid = false;
                    }
                });
                if (valid == false)
                {
                    alert(settings.texts.txtAlertRequired);
                    return false;
                }
                return true;
            }
            
            function removeClassError (form)
            {
                jQuery.each(requiredFields, function (key, value) {
                   form.find("." + value + " input").removeClass("error");
                });
            }
			
			function createHiddenForm (idx) {				
				//copy master form
				var $clone = jQuery("#formMaster" + ctype).clone();
                //we use clone(), without parameter true, to prevent tooltips from been copied and positioned relatively to the original element
                $clone.removeAttr("id");
                jQuery("#" + hdnItemListId).append($clone);
				
				//create tooltips for copied master elements
				setTips(idx);
				
				// create buttons
				// Close button top
				var p = jQuery("<p/>", {
					html : "<a href=\"#\" class=\"btn closeForm\" title=\"" + settings.texts.txtClose + "\"><span class=\"icon-unpublish\"></span></a><a href=\"#\" class=\"btn newItemClose\" title=\"" + settings.texts.txtClose + "\"><span class=\"icon-unpublish\"></span></a>",
					"class" : "pull-right"
				});
				jQuery("#"+ hdnItemListId + " .itemForm").eq(idx).prepend(p);
				// Reset, Save, Close button bottom
				var btns = '<a href=\"#\" class=\"btn btn-success addItem\">' + settings.texts.txtAddItem + '</a> ';
                btns += '<a href=\"#\" class=\"btn btn-success addAndNewItem\">' + settings.texts.txtAddAndNewItem + '</a> ';
				btns += '<a href=\"#\" class=\"btn btn-danger newItemClose\">' + settings.texts.txtClose + '</a> ';
				btns += '<a href=\"#\" class=\"btn btn-success saveForm\">' + settings.texts.txtSave + '</a> ';
                btns += '<a href=\"#\" class=\"btn btn-success saveAndNewForm\">' + settings.texts.txtSaveAndNew + '</a> ';
				btns += '<a href=\"#\" class=\"btn resetForm\">' + settings.texts.txtReset + '</a> ';
				btns += '<a href=\"#\" class=\"btn btn-danger closeForm\">' + settings.texts.txtClose + '</a>';
				
				var pBtn = jQuery("<span class=\"btnContainer text-center\">" + btns + "</span>");
				jQuery("#"+ hdnItemListId + " .itemForm").eq(idx).append(pBtn);
			
				var form = jQuery("#"+ hdnItemListId + " .itemForm").eq(idx);
				return form;
			}
			
			function setTips (i) {
				var form = jQuery("#"+ hdnItemListId + " .itemForm").eq(i);
					form.find("[data-original-title]").addClass("hasTooltip");
				form.find(".hasTooltip").tooltip();
			}
			
			function setFormPosition (li, idx) {
				var position = li.position();
				//we either recieve a numeric index or an id string! Build id string if a numeric index is given
				var height = jQuery("#"+ hdnItemListId + " .itemForm").eq(idx).height();
				if (position.top < height + 40) { height = position.top - 40}
				jQuery("#"+ hdnItemListId + " .itemForm").eq(idx).css({
					"position" : "absolute",
					"top" : position.top - height,
					"left" : position.left - 25
				});
			}
			
			function setValuesInHiddenForm (i) {
				var item = getItemsObj()[i];
				jQuery.each(item, function (key, value) {
                    var control = jQuery("#"+ hdnItemListId + " .itemForm").eq(i).find("." + key + " .controls ." + key);
                    if (control.attr("type") == 'checkbox')
                    {
                        control.attr("checked", "checked");
                    }
                    else
                    {
                        control.attr("value", value);
                    }
				});
			}
			
			function delItemElements (i) {
				//remove list item
				jQuery("#" + itemListId + " .liItem").eq(i).remove();
				//remove form
				jQuery("#"+ hdnItemListId + " .itemForm").eq(i).remove();
			}
			
			function saveForm($form) {
				var idx = $form.index();
				//get the current items as object
				var rawObj = getItemsObj();
				var itemsObj = buildItemsObj (rawObj, idx);
                setCountOfDefaultOptions(itemsObj);
				//Stingify images object and set the string as value in itemsDB field (so that it will be stored in database, when module is saved)
				var itemsStr = createItemsStr(itemsObj);
				setItemsStr (itemsStr);
			}
            //this is a custom function which is only relevant in the original itemlistcreator for form fields of type radio, select and multicheckbox
            function setCountOfDefaultOptions (itemsObj)
            {
                if (jQuery.inArray(ctype, ['radio', 'select', 'multicheckbox']) > -1)
                {
                    var defaults = jQuery.map(itemsObj, function (n, i) {
                        return n['listitemischecked'];
                    });

                    var count = defaults.length;
                    jQuery("#" + settings.params.idPrefix + "f_" + ctype + "_countDefaultOpts").val(count);
                }
            }
						
			function buildItemsObj (itemsObj, idx) {
				var $form = jQuery("#"+ hdnItemListId + " .itemForm").eq(idx);
                var inputs = $form.find("input");
				var itemArr = inputs.serializeArray();
				//create object of items and prittify the array
				var itemObj = {};
				itemObj[idx] = cleanItemArr(itemArr);
				//push item into items array, overwrite item if it already exists
				var newitemsObj = jQuery.extend(itemsObj, itemObj);
				itemsObj = newitemsObj;
				return itemsObj;
			}
			
			function moveItem (idx, up) {
				var last = jQuery("#" + itemListId + " .liItem").length - 1;
				removeArrowClassDisabled (1, true);
				removeArrowClassDisabled (last, false);
				var li = jQuery("#" + itemListId + " .liItem").eq(idx);
				var form = jQuery("#"+ hdnItemListId + " .itemForm").eq(idx);
				if (up) {
					var prevLi = jQuery("#" + itemListId + " .liItem").eq(idx - 1);
					var prevForm = jQuery("#"+ hdnItemListId + " .itemForm").eq(idx - 1);
					li.insertBefore(prevLi);
					form.insertBefore(prevForm);
				}
				else {
					var nextLi = jQuery("#" + itemListId + " .liItem").eq(idx + 1);
					var nextForm = jQuery("#"+ hdnItemListId + " .itemForm").eq(idx + 1);
					li.insertAfter(nextLi);
					form.insertAfter(nextForm);
				}
				setArrowClassDisabled (1, true);
				setArrowClassDisabled (last, false);
				//We have to newly build the images object from all remaining images with correct index
				var itemsObj = {};
				jQuery("#"+ hdnItemListId + " .itemForm").each(function (idx, el) {
                    if (idx > 0)
                    {
                        itemsObj = buildItemsObj (itemsObj, idx);
                    }
				});
				//Stringify images object and set the string as value in itemsDB field (so that it will be stored in database, when module is saved)
				var itemsStr = createItemsStr(itemsObj);
				setItemsStr (itemsStr);
			}
            
            function getCType () {
                var ctype = "";
                if (settings.params.ctype == "")
                {
                    var leftTrimmed = settings.params.fieldName.replace("f_", "");
                    ctype = leftTrimmed.replace(settings.params.dbFieldExt, "");
                }
                else
                {
                    ctype = settings.params.ctype;
                }
                return ctype;
            }
            
            //create a li as table header of option list
            function createListHeader ()
            {
                var hdnMflds = settings.params.hdnMFlds;
                jQuery("<li/>", {
                    "class" : "listHeader liItem"
                }).appendTo("#" + itemListId);
                jQuery("<span/>", {
                    "class" : "itemUpHeader"
                }).appendTo("#" + itemListId + " .listHeader");
                jQuery("<span/>", { 
                    "class" : "itemDownHeader"
                }).appendTo("#" + itemListId + " .listHeader");
                
                jQuery("<span/>", {  
                    "class" : "itemChangeHeader"
                }).appendTo("#" + itemListId + " .listHeader");
                jQuery("<span/>", {  
                    "class" : "itemRemoveHeader"
                }).appendTo("#" + itemListId + " .listHeader");
                jQuery.each(hdnMflds, function (i, o) {
                    var li = jQuery("#" + itemListId + " .listHeader")
                    var itemBefore = li.find("span.itemlistheader");
                    if (itemBefore.length < 1)
                    {
                        itemBefore = li.find("span." + settings.params.listValueAfter + "Header");
                    }
                    else
                    {
                        itemBefore = itemBefore.last();
                    }
                    jQuery("<span/>", {  
                    "class" : "itemlistheader",
                    text: o.fheader
                    }).insertAfter(itemBefore);
                });
            }
            
            function  createEmptyDivInFormContainer()
            {
                jQuery("<div/>", {   
                    "style" : "display: none;",
                   "class" : "itemForm"
                }).appendTo("#" + hdnItemListId);
            }
            
            function createFormTemplate () {
                var hdnMflds = settings.params.hdnMFlds;
                jQuery("<div/>", {
                    "class" : "itemForm well",
                    "id" : "formMaster" + ctype,
                    "style" : "display: none"
                }).appendTo("#item-form");
                jQuery("<h2/>", {
                    text: settings.texts.txtTitle,                 
                }).appendTo("#formMaster" + ctype).attr('data-original-title', settings.texts.txtTitle);
                jQuery("<div/>", {
                    "class" : "control-group listitemid"
                }).appendTo("#formMaster" + ctype);
                jQuery("<div/>", {
                    "class" : "controls"
                }).appendTo("#formMaster" + ctype + " .listitemid");
                jQuery("<input/>", {
                    "class" : "listitemid",
                    "type" : "hidden",
                    "name" : "listitemid"
                }).appendTo("#formMaster" + ctype + " .listitemid .controls").val("");
                jQuery.each(hdnMflds, function (i, o) {
                    //add an asterix to labels of fields which are required (just for show)
                    if (o.frequired == 1)
                    {
                        o.flabel = o.flabel + "*";
                    }
                    jQuery("<div/>", {
                        "class" : "control-group " + o.fname
                    }).appendTo("#formMaster" + ctype);
                    jQuery("<div/>", {
                        "class" : "control-label"
                    }).appendTo("#formMaster" + ctype + " ." + o.fname);
                    jQuery("<label/>", {
                        "class" : o.fname +"-lbl",
                        "for" : o.fname,
                        text: o.flabel
                    }).appendTo("#formMaster" + ctype + " ." + o.fname + " .control-label").attr('data-original-title', o.fdesc);
                    jQuery("<div/>", {
                        "class" : "controls"
                    }).appendTo("#formMaster" + ctype + " ." + o.fname);
                    jQuery("<input/>", {
                        "class" : o.fname,
                        "type" : o.ftype,
                        "name" : o.fname,
                        "value" : o.fvalue
                    }).appendTo("#formMaster" + ctype + " ." + o.fname + " .controls");
                });
            }

            function initList()
            {
                // Add existing li's to itemList and hidden image list
                if (jQuery("#" + dbFieldName).val() != "") {
                    var itemsObj = getItemsObj();
                    jQuery.each(itemsObj, function (i, o) {
                        //create empty list item
                        var li = createListItem();
                        //create html elements in list item
                        createListItemElements(i);
                        //get property
                        jQuery.each(hdnMFldNames, function (index, value) {
                            var text = getListItem(i, value);
                            //property in list item
                            setListItemValue(text, i);
                        });

                        //create popup form element
                        var form = createHiddenForm(i);
                        //set values in Form element
                        setValuesInHiddenForm(i);
                        // hide addItem button in form
                        form.find("a.addItem").hide();
                        form.find("a.addAndNewItem").hide();
                        form.find("a.newItemClose").hide();
                        form.find("h2").html(settings.texts.txtChangeItem);

                    });
                    //set class disabled on peripheral arrows in last li
                    setArrowClassDisabled (1, true);
                    setArrowClassDisabled (jQuery("#" + itemListId + " .liItem").length - 1, false);
                }
            }

            function browserSupportFileUpload() {
                var isCompatible = false;
                if (window.File && window.FileReader && window.FileList && window.Blob) {
                    isCompatible = true;
                }
                return isCompatible;
            }

            function clearHiddenFields()
            {
                //clear fields listHidden, defaultOptionsCount
                jQuery("#" + dbFieldName).val("{}");
                jQuery("#" + settings.params.idPrefix + "f_" + ctype + "_countDefaultOpts").val(0);
            }

            function updateProgressBar()
            {
                jQuery(".progress .bar").attr("style", "width:50%;");
            }

            function showProgressBar()
            {
                jQuery(".progress").show();
            }

            function hideProgressBar()
            {
                jQuery(".progress").hide();
            }
            // Method that reads and processes the selected file
            function importOptions(evt) {
                if (!browserSupportFileUpload()) {
                    alert('The File APIs are not fully supported in this browser!');
                } else {
                    var data = null;
                    var file = evt.target.files[0];
                    var reader = new FileReader();
                    reader.readAsText(file);
                    reader.onload = function(event) {
                        //var rawData = event.target.result;
                        var csvData = event.target.result;
                        var separator = jQuery("#" + importSeparator).val();
                        var csvoptions = {"separator" : separator};
                        //var itemsObj =  {};
                        try {
                            data = jQuery.csv.toArrays(csvData, csvoptions);
                        }
                        catch(error)
                        {
                            alert(settings.texts.txtReaderError);
                            return;
                        }
                        if (data && data.length > 0) {
                            //clear fields listHidden, defaultOptionsCount
                            clearHiddenFields();
                            //remove old hiddenList and hiddenForm elements
                            jQuery("#" + itemListId + " .liItem").not(".listHeader").remove();
                            jQuery("#" + hdnItemListId + " .itemForm.well").remove();
                            jQuery.each(data, function(idx, option)
                            {
                                //we must have at least 2 values (value and label)
                                if (option.length < 2 )
                                {
                                    //invalide data
                                    return;
                                }
                                if (option[0] === "" || option[1] === "")
                                {
                                    //invalide data
                                    return;
                                }
                                var idx = 0;
                                var $itemList = jQuery("#" + itemListId + " .liItem");
                                if ($itemList.length) {
                                    idx = $itemList.length;
                                }
                                // create empty img list item to use for positioning popup form
                                var li = createListItem();
                                //create html elements in list item
                                createListItemElements(idx);
                                //create empty form
                                var form = createHiddenForm(idx);
                                //set values in form
                                //get highest used id
                                var lastItemId = getLastItemId();
                                form.find(".listitemid input").val(lastItemId);
                                form.find(".listitemvalue input").val(option[0]);
                                form.find(".listitemlabel input").val(option[1]);
                                if (jQuery.type(option[2]) !== "undefined" && jQuery.type(option[2]) !== null && option[2] !== "")
                                {
                                    form.find(".listitemischecked input").prop("checked", true);
                                }
                                //hide default save, close and reset button in form
                                form.find("a.addItem").hide();
                                form.find("a.addAndNewItem").hide();
                                form.find("a.newItemClose").hide();
                                form.find("h2").html(settings.texts.txtChangeItem);
                                // save data in form in hidden input field and hide form
                                saveForm(form);
                                //update pointer with highest used item id
                                setLastItemId();
                                //get form index
                                var idx = form.index();
                                //add user inputs to visible list item
                                jQuery.each(hdnMFldNames, function (index, value) {
                                    var text = getListItem(idx, value);
                                    //property in list item
                                    setListItemValue(text, idx);
                                });

                                //disable arrow up in first element
                                if (idx == 1) {
                                    setArrowClassDisabled (idx, true);
                                }

                                //we have a new last element in image list, so remove class disabled on previous list item
                                if (idx > 0) {
                                    removeArrowClassDisabled (idx - 1, false);
                                }
                                //Disable arrow down on new = last element
                                setArrowClassDisabled (idx, false);

                            });
                            alert(settings.texts.txtItemsImported);

                        } else {
                            alert(settings.texts.txtNoDataToImport);
                        }
                    };
                    reader.onerror = function() {
                        alert(settings.texts.txtReaderError);
                    };
                }
            }
			//End helper functions
            hideProgressBar();
			createFormTemplate();
			// We create button Templates via php (to get translated button texts)
			// Move button template to the bottom of the page
			//jQuery("#buttonMasters").appendTo("body");
			            
            jQuery("<div/>", {
                "id" : itemListContId
            }).insertBefore("#" + dbFieldName);
            
            jQuery("<ul/>", {
                "id" : itemListId
            }).appendTo("#" + itemListContId);
            
            jQuery("<a/>", {
                "id" : addButtonId,
                "class" : "btn",
                "href" : "#",
                text : settings.texts.txtCreateItem
            }).insertAfter("#" + itemListId);
            
            //Create container for hidden forms
			jQuery("<div/>", {
				"id" : hdnItemListId,
			}).insertAfter("#" + itemListContId);
            
            createListHeader();
            createEmptyDivInFormContainer();
            initList();


			// add event listener

            //Fileupload to import options
            jQuery("#" + importField).on('change', importOptions);

			// Add new item to list
			jQuery("#add" + ctype).on("click", function () {
				var idx = 0;
				var $itemList = jQuery("#" + itemListId + " .liItem"); 
				if ($itemList.length) {
					idx = $itemList.length;
				}
				// create empty img list item to use for positioning popup form
				var li = createListItem();
				//create html elements in list item
				createListItemElements(idx);
				//create empty form
				var form = createHiddenForm(idx);
                //set list item id in form
                //get highest used id
                var lastItemId = getLastItemId();
                form.find(".listitemid input").val(lastItemId);
				//hide default save, close and reset button in form
				form.find("a.saveForm").hide();
                form.find("a.saveAndNewForm").hide();
				form.find("a.resetForm").hide();
				form.find("a.closeForm").hide();
				//give form a position which is needed when showing form in a popup
				//var position = li.position();
				setFormPosition(li, idx);
				//show form
				form.show();
                form.find(".listitemvalue input").focus();
			});
			
			//Form Button events			
			// add button in form popup ("Save button for saving new image/article for the first time)
			jQuery("#" + hdnItemListId).on("click", "a.addItem", function (e){
				var $form = jQuery(this).parents(".itemForm");
                //check that required fields contain a value
                if ( checkRequiredFields($form) == false)
                {
                    e.preventDefault();
                    return false;
                }
				// save data in form in hidden input field and hide form
				saveForm($form);
                //update pointer with highest used item id
                setLastItemId();
				//get form index
				var idx = $form.index();
                //get add user inputs to visible list item
                jQuery.each(hdnMFldNames, function (index, value) {
                    var text = getListItem(idx, value);
                    //property in list item
                    setListItemValue(text, idx);
                });
                
                //disable arrow up in first element
                if (idx == 1) {
					setArrowClassDisabled (idx, true);
				}
                
				//we have a new last element in image list, so remove class disabled on previous list item
				if (idx > 0) {
					removeArrowClassDisabled (idx - 1, false);
				}
				//Disable arrow down on new = last element
				setArrowClassDisabled (idx, false);
				// hide a.addItem
				jQuery(this).hide();
				//hide a.newItemClose
				$form.find("a.newItemClose").hide();
                $form.find("a.addAndNewItem").hide();
				// show a.saveForm and a.resetForm button for use of hdnForm
				$form.find("a.saveForm").show();
                $form.find("a.saveAndNewForm").show();
				$form.find("a.resetForm").show();
				$form.find("a.closeForm").show();
                $form.find("h2").html(settings.texts.txtChangeItem);
				//hide the popup form
				$form.hide();
			});
            // add button in form popup ("Save button for saving new image/article for the first time and create new empty form)
			jQuery("#" + hdnItemListId).on("click", "a.addAndNewItem", function (e){
				var $form = jQuery(this).parents(".itemForm");
                //check that required fields contain a value
                if ( checkRequiredFields($form) == false)
                {
                    e.preventDefault();
                    return false;
                }
				// save data in form in hidden input field and hide form
				saveForm($form);
                //update pointer with highest used item id
                setLastItemId();
				//get form index
				var idx = $form.index();
                //get add user inputs to visible list item
                jQuery.each(hdnMFldNames, function (index, value) {
                    var text = getListItem(idx, value);
                    //property in list item
                    setListItemValue(text, idx);
                });
                
                //disable arrow up in first element
                if (idx == 1) {
					setArrowClassDisabled (idx, true);
				}
                
				//we have a new last element in image list, so remove class disabled on previous list item
				if (idx > 0) {
					removeArrowClassDisabled (idx - 1, false);
				}
				//Disable arrow down on new = last element
				setArrowClassDisabled (idx, false);
				// hide a.addItem
				jQuery(this).hide();
				//hide a.newItemClose
				$form.find("a.newItemClose").hide();
                $form.find("a.addItem").hide();
				// show a.saveForm and a.resetForm button for use of hdnForm
				$form.find("a.saveForm").show();
                $form.find("a.saveAndNewForm").show();
				$form.find("a.resetForm").show();
				$form.find("a.closeForm").show();
                $form.find("h2").html(settings.texts.txtChangeItem);
				//hide the popup form
				$form.hide();
                //create new element and show form
                var idx = 0;
				var $itemList = jQuery("#" + itemListId + " .liItem"); 
				if ($itemList.length) {
					idx = $itemList.length;
				}
				// create empty img list item to use for positioning popup form
				var li = createListItem();
				//create html elements in list item
				createListItemElements(idx);
				//create empty form
				var form = createHiddenForm(idx);
                //set list item id in form
                //get highest used id
                var lastItemId = getLastItemId();
                form.find(".listitemid input").val(lastItemId);
				//hide default save, close and reset button in form
				form.find("a.saveForm").hide();
                form.find("a.saveAndNewForm").hide();
				form.find("a.resetForm").hide();
				form.find("a.closeForm").hide();
				//give form a position which is needed when showing form in a popup
				//var position = li.position();
				setFormPosition(li, idx);
				//show form
				form.show();
                form.find(".listitemvalue input").focus();
			});
			
			jQuery("#" + hdnItemListId).on("click", "a.newItemClose", function (e){
				var $form = jQuery(this).parents(".itemForm");
				var idx = $form.index();
				//Remove form and list element from HTML
				delItemElements(idx);
			});
			
			// close buttons in form popup
			jQuery("#" + hdnItemListId).on("click", "a.closeForm", function (e){
				var $form = jQuery(this).parents(".itemForm");
				var idx = $form.index();
				//reset values in Form element
				setValuesInHiddenForm(idx);
                removeClassError($form);
				$form.hide();
			});
			
			// reset button in form popup
			jQuery("#" + hdnItemListId).on("click", "a.resetForm", function (e){
				var $form = jQuery(this).parents(".itemForm");
				var idx = $form.index();
				//set values in Form element
				setValuesInHiddenForm(idx);
                removeClassError($form);
			});
			
			// save button in form popup
			jQuery("#" + hdnItemListId).on("click", "a.saveForm", function (e){
				var $form = jQuery(this).parents(".itemForm");
                //check that required fields contain a value       
                if (checkRequiredFields($form) == false)
                {
                    e.preventDefault();
                    return false;
                }
				saveForm($form);
				var idx = $form.index();
                //remove old values in list
                removeListItemValues(idx);
				//set new values in list
                //get property
                jQuery.each(hdnMFldNames, function (index, value) {
                    var text = getListItem(idx, value);
                    //property in list item
                    setListItemValue(text, idx);
                });
                removeClassError($form);
				$form.hide();
				
			});
            
            // save button and new in form popup
			jQuery("#" + hdnItemListId).on("click", "a.saveAndNewForm", function (e){
				var $form = jQuery(this).parents(".itemForm");
                //check that required fields contain a value       
                if (checkRequiredFields($form) == false)
                {
                    e.preventDefault();
                    return false;
                }
				saveForm($form);
				var idx = $form.index();
                //remove old values in list
                removeListItemValues(idx);
				//set new values in list
                //get property
                jQuery.each(hdnMFldNames, function (index, value) {
                    var text = getListItem(idx, value);
                    //property in list item
                    setListItemValue(text, idx);
                });
                removeClassError($form);
				$form.hide();
				//create new element and show form
                var idx = 0;
				var $itemList = jQuery("#" + itemListId + " .liItem"); 
				if ($itemList.length) {
					idx = $itemList.length;
				}
				// create empty img list item to use for positioning popup form
				var li = createListItem();
				//create html elements in list item
				createListItemElements(idx);
				//create empty form
				var form = createHiddenForm(idx);
                //set list item id in form
                //get highest used id
                var lastItemId = getLastItemId();
                form.find(".listitemid input").val(lastItemId);
				//hide default save, close and reset button in form
				form.find("a.saveForm").hide();
                form.find("a.saveAndNewForm").hide();
				form.find("a.resetForm").hide();
				form.find("a.closeForm").hide();
				//give form a position which is needed when showing form in a popup
				//var position = li.position();
				setFormPosition(li, idx);
				//show form
				form.show();
                form.find(".listitemvalue input").focus();
			});
			
			//List link events
			
			// link itemUp
			jQuery("#" + itemListId).on("click", "a.itemUp", function (e) {
				var li = jQuery(this).parents(".liItem");
				var idx = li.index();
				if (idx == 0) {
					return;
				}
				else {
					moveItem(idx, true);
				}
			});
			
			// link itemDown
			jQuery("#" + itemListId).on("click", "a.itemDown", function (e) {
				var li = jQuery(this).parents(".liItem");
				var idx = li.index();
				var last = jQuery("#" + itemListId + " .liItem").length - 1;
				if (idx == last) {
					return;
				}
				else {
					moveItem(idx, false);
				}
			});
			
			// link (image name) and link "Change" in image list
			jQuery("#" + itemListId).on("click", "a.itemChange", function (e) {
				var li = jQuery(this).parent();
				var idx = li.index();
                setFormPosition(li, idx);
				jQuery("#"+ hdnItemListId + " .itemForm").eq(idx).show();
			});
			
			// link "Delete" in image list
			jQuery("#" + itemListId).on("click", "a.itemRemove", function (e) {
				var li = jQuery(this).parent();
				var idx = li.index();
				//remove class disable on arrows in first and last list item
				removeArrowClassDisabled (1, true);
				removeArrowClassDisabled (jQuery("#" + itemListId + " .liItem").length - 1, false);
				//Remove form and list element from HTML
				delItemElements(idx);
				//set class disabled on arrows in first and last list item in new list
				setArrowClassDisabled (1, true);
				setArrowClassDisabled (jQuery("#" + itemListId + " .liItem").length - 1, false);
				//We have to newly build the images object from all remaining images with correct index
				var itemsObj = {};
				jQuery("#"+ hdnItemListId + " .itemForm").each(function (i, el) {
                    if (i > 0)
                    {
                        itemsObj = buildItemsObj (itemsObj, i);
                    }
				});
				//Stingify images object and set the string as value in itemsDB field (so that it will be stored in database, when module is saved)
				var itemsStr = createItemsStr(itemsObj);
				setItemsStr (itemsStr);
			});           
			
			//Window resize
			jQuery( window ).resize(function() {
				jQuery("#"+ hdnItemListId + " .itemForm").each( function (i, el) {
                    if (i > 0)
                    {
                        var li = jQuery("#" + itemListId + " .liItem").eq(i);
                        setFormPosition(li, i);
                    }
				});
			});
		}
	});
}(jQuery));