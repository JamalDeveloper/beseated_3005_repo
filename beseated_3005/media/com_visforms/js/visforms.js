/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function($) {
    
    $.extend($.fn, {
        //public plugin funtions
        displayChanger : function (options) {
            //Attach onchange event handler to displayChanger Element
            $(".displayChanger").on('change', function (e) {
                $(".conditional").trigger("checkConditionalState");
            });
        },
        //perform the code which is necessary to toggle the display state of one form element
        toggleDisplay : function (data) {
            //el is a div class="conditional"
            var el = $(this);
            //data is a list of all conditional fields 
            //index is id of field
            //value is a comma separated string of all fieldids and values the command the conditional field to be visible
            $.each(data, function (index, value) {
                //a conditional field may have different other fields that make it visible
                //we will not hide it, when at least one condition is true
                var hide = true;
                //find the right set of conditions for the div conditional that is actually processed
                if (elId = el.attr('class').match(index))
                {
                    //split the condition string
                    var  showWhens = value.split(', ');
                    $.each(showWhens, function (i, v) {
                        //split the condition into a field id and a value that, if selected , will command the field to be visible
                        var showWhen = v.split('__');
                        if (showWhen.length >= 2)
                        {
                            var fieldId = showWhen[0];
                            var conditionalValue = showWhen[1];
                            //Restrictor elements that determine whether field is shown or hidden
                            //we first look if we have a single control with a matching ID
                            var restrictors = $('#' + fieldId);
                            //if not, we deal with a radio or a multi checkbox. Id's are there followed by _n
                            if (restrictors.length < 1)
                            {
                                var restrictors = $("[id^='" + fieldId + "_']");
                            }                            
                            //rel is restrictor element
                            //check if we have a value in a retrictor element that will command field to be shown
                            $.each(restrictors, function (ri, rel) 
                            {
                                //only use values of elements that are enabled
                                if ($(rel).is(':enabled'))
                                {
                                    var tagname = rel.tagName.toLowerCase();
                                    switch (tagname)
                                    {
                                        case "input" :
                                            //selected values have checked=checked set
                                            if ($(rel).is(':checked'))
                                            {
                                                if ($(rel).val() == conditionalValue)
                                                {
                                                    hide = false;
                                                    return hide;
                                                }
                                            }
                                            break;
                                        case "select" :
                                            var vals = $(rel).find(':selected');
                                            $.each(vals, function (valindex, selectedValue) 
                                            {
                                                if ($(selectedValue).val() == conditionalValue)
                                                 {
                                                     hide = false;
                                                 }
                                                 return hide;
                                            });
                                            break;
                                        default :
                                            break;
                                    }
                                }
                                return hide;
                            });

                            return hide;
                        }
                    })

                    //controls of element to be shown or hidden
                    //we first look for a control with matching id
                    var controls = $('#' + index);
                    //if not, we deal with a radio or a multi checkbox. Id's are there followed by _n
                    if (controls.length < 1)
                    {
                        var controls = el.find("[id^='" + index + "_']");
                    }
					var ctagname = '';
                    if (controls.get(0))
                    {
                        var ctagname = controls.get(0).tagName.toLowerCase();
                    }
                    if (hide === false)
                    {
                        if ($(controls).is(':disabled') || ((ctagname == 'hr') && $(controls).hasClass('ignore')))
                        {
                            //enable controls, remove class ignore and disabled, show div conditional
                            showControls(controls);
                            //check if control is displaychanger
                            if(controls.hasClass('displayChanger'))
                            {
                                //check if depending fields must be displayed too
                                toggleChild (data, index);
                            }
                            return false;
                        }
                    }
                    else
                    {
                        if(($(controls).is(':enabled') || (($(controls).is(':disabled')) && $((controls).attr('data-disabled') != undefined))) || ((ctagname == 'hr') && $(controls).hasClass('ignore') == false))
                        {
                            //disable controls, set class ignore, hide div conditional
                            hideControls(controls);
                            //check if control is displaychanger
                            if(controls.hasClass('displayChanger'))
                            {
                                //check if depending fields must be hidden too
                                toggleChild (data, index);
                            }
                            return false;
                        }
                    }
                }
            });
            
            //additional protected class variables can be declared here.
            
            //protected helper functions for toggleDisplay
            
            /**
             * Methode to enable controls, remove class ignore and disabled, show div conditional
             * @param {jQuery selection} controls
             * @returns {Boolean}
             */
            function showControls (controls) {
                if (controls.length < 1)
                {
                    //no controls found, do nothing
                    return false;
                }
                $.each(controls, function (cindex, control) {
                    //only enable multicheckbox option us the have no data-disabled attribute
                    if ($(control).attr('data-disabled') == undefined)
                    {
                        $(control).removeAttr('disabled');
                        $(control).removeClass('ignore');
                    }
                    //no radio or checkbox group
                    if (cindex === 0)
                    {   
                        if ($(control).is('[readonly]') == false)
                        {
                            $(control).parents("div.conditional").find("button").show();
                        }
                        $(control).parents("div.conditional").show();
                    }
                });
            }
            
            /**
             * Methode to disable controls, set class ignore, hide div conditional
             * @param {jquery selection} controls
             * @returns {Boolean}
             */
            function hideControls (controls) {
                if (controls.length < 1)
                {
                    //no controls found, do nothing
                    return false;
                }
                $.each(controls, function (cindex, control) {
                    $(control).attr('disabled', 'disabled');
                    $(control).addClass('ignore');
                    //no radio or checkbox group
                    if (cindex === 0)
                    {
                        $(control).parents("div.conditional").hide();
                    }
                });
            }
            
            /**
             * Basically we use the data object to find all conditional fields, who's display state depends on the state of the control with the id, given as param.
             * We then find the parent html element with class=conditional for each conditional field and trigger the checkConditionalState event on it
             * The toggleDisplay function is then performed once again for the conditional field
             * @param {string} restricts list of all conditionla fields and the field__values that trigger there display
             * @param {string} id id/class name of parent control
             * @returns {undefined}
             */
            function toggleChild (restricts, id)
            {
                $.each(restricts, function (index, list) {
                    //split the restriction string
                    var  showWhens = list.split(', ');
                    $.each(showWhens, function (i, v) {
                        //split the restriction into a field id and a value that, if selected , will command the field to be visible
                        var showWhen = v.split('__');
                        if (showWhen.length >= 2)
                        {
                            //we have a depending child
                            if (showWhen[0] == id)
                            {
                                //find parent element with class=conditional
                                var conditional = $('.' + index);
                                //check the child
                                conditional.trigger('checkConditionalState');
                            }
                        }
                    });
                });
            }
        }
    });
}(jQuery));

//mend missing placeholder support in some browsers
(function ($) {
    $.support.placeholder = ('placeholder' in document.createElement('input'));
})(jQuery);
