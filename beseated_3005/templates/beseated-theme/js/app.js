// ============================================================================
// DATE TIME PICKER

(function ($, window, document, undefined) {
  'use strict';

  var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  var daysShort = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

  var template = '<div class="date-time-picker-control">' +
      '<div class="datepicker-header">' +
        '<span class="prev-month-button"></span>' +
        '<span class="caption">' +
          '<span class="year"></span>' +
          '<span class="month"></span>' +
        '</span>' +
        '<span class="next-month-button"></span>' +
      '</div>' +
      '<div class="datepicker-body">' +
        '<div class="current-selection">' +
          '<span class="day"></span>' +
          '<span class="clock-icon"></span>' +
          '<span class="day-name"></span>' +
          '<span class="time">@ 22:00</span>' +
        '</div>' +
        '<table class="calendar"></table>' +
        '<div class="timepicker">' +
          '<div class="hour">' +
            '<span class="next-button"></span>' +
            '<span class="number">22</span>' +
            '<span class="prev-button"></span>' +
          '</div>' +
          '<div class="minute">' +
          '<span class="next-button"></span>' +
          '<span class="number">05</span>' +
          '<span class="prev-button"></span>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';

  var pluginName = 'dateTimePicker',
    defaults = {
      date: new Date(),
      minDate: new Date(),
      time: '12:00',
      template: template,
      weekStart: 1,
      months: months,
      availableDays: [0,1,2,3,4,5,6],
      days: days,
      daysShort: daysShort,
      disableTimePicker: false
    };

  function Plugin(element, options) {
    this.element = $(element);

    this.options = $.extend({}, defaults, options);
    this._defaults = defaults;
    this._name = pluginName;
    this.init();
  }

  Plugin.prototype = {
    init: function () {
      this.options.minDate.setHours(0,0,0,0);
      this.parseInputs();

      // Move selected date to first available day of the week
      for (var i = 0; i < 7; i++) {
        var dayIndex = this.date.getDay();
        var weekStart = this.options.weekStart;
        dayIndex = dayIndex - weekStart < 0 ? 7 - dayIndex - weekStart : dayIndex - weekStart;

        if(this.options.availableDays.indexOf(dayIndex) != -1) {
          break;
        } else {
          this.date = new Date(this.date.getFullYear(), this.date.getMonth(), this.date.getDate() + 1);
        }
      };

      this.year  = this.date.getFullYear();
      this.month = this.date.getMonth();
      this.day   = this.date.getDate();

      var timeUnits = this.time.split(':');
      this.hour     = parseInt(timeUnits[0]);
      this.minute   = parseInt(timeUnits[1]);

      // Move time
      var currentTime = new Date() 
      this.hour = currentTime.getHours();
      this.minute = Math.ceil(currentTime.getMinutes() / 15) * 15;
      if(this.minute > 45) {
        this.minute = 45
      }
      this.selectTime();

      this.element.append($(this.options.template));

      this.renderCalendar();
      this.renderTimepicker();
      this.updateSelection();

      this.updateDateInput();
      this.updateTimeInput();

      this.element.on('click','.next-month-button', this.nextMonth.bind(this));
      this.element.on('click','.prev-month-button', this.prevMonth.bind(this));
      this.element.on('click', '.calendar span.available', this.selectDate.bind(this));      
      this.element.on('click','.hour .prev-button', this.prevHour.bind(this));
      this.element.on('click','.hour .next-button', this.nextHour.bind(this));
      this.element.on('click','.minute .prev-button', this.prevMinute.bind(this));
      this.element.on('click','.minute .next-button', this.nextMinute.bind(this));
    },
    parseInputs: function () {
      var dateInput = this.element.find('input[data-date-input]');
      var timeInput = this.element.find('input[data-time-input]');

      if (dateInput.length == 1) {
        this.dateInput = dateInput;
      }

      if(this.dateInput && dateInput.val().length > 0) {
        this.date = new Date(Date.parse(dateInput.val()))
      } else {
        this.date = this.options.date;
      }

      if (timeInput.length == 1) {
        this.timeInput = timeInput;
      }

      if(this.timeInput && timeInput.val().length > 0) {
        this.time = timeInput.val();
      } else {
        this.time = this.options.time;
      }
    },
    updateDateInput: function() {
      if(this.dateInput) {
        var dateString = this.year + '-' + ('0' + (this.month + 1)).substr(-2) + "-" + ('0' + this.day).substr(-2);
        this.dateInput.val(dateString);
      }
    },
    selectDate: function(event) {
      this.day = $(event.currentTarget).data('day');
      this.date = new Date(this.year, this.month, this.day);
      this.updateDateInput();
      this.updateSelection();
    },
    updateSelection: function() {
      if(this.month == this.date.getMonth()) {
        this.element.find('.calendar span').removeClass('selected');
        this.element.find('.calendar .day-' + this.day).addClass('selected')
      }
      
      this.element.find('.current-selection .day').html(this.day);
      this.element.find('.current-selection .day-name').html(this.options.days[this.date.getDay()]);
      this.element.find('.current-selection .time').html('@ ' + this.time);
      this.element.find('.hour .number').html(this.time.substring(0, 2));
      this.element.find('.minute .number').html(this.time.substr(-2));
    },
    nextMonth: function() {
      if(this.month + 1 == 12) {
        this.month = 0;
        this.year++;
      } else {
        this.month++;
      }
      this.renderCalendar();
      this.updateSelection();
    },
    prevMonth: function() {
      if(this.month - 1 < 0) {
        this.month = 11;
        this.year--;
      } else {
        this.month--;
      }
      this.renderCalendar();
      this.updateSelection();
    },
    renderCalendar: function() {
      var weekStart      = this.options.weekStart;
      var startDay       = new Date(this.year, this.month, 1).getDay();
      var offset         = startDay - weekStart < 0 ? 7 - startDay - weekStart : startDay - weekStart;
      var monthLength    = new Date(this.year, this.month + 1, 0).getDate();
      var numberOfCells  = Math.ceil((monthLength + offset) / 7) * 7;

      var table = $('<table>').addClass('calendar');
      var tr    = $('<tr>');
      table.append(tr);

      for (var i = 0; i < 7; i++) {
        var index = i + weekStart < 7 ? i + weekStart : i - 7 + weekStart;
        tr.append($('<th>').html(this.options.daysShort[index]));
      }

      var dayIndex = 0;

      for (var i = 0; i < numberOfCells; i++) {
        if(i % 7 == 0) {
          var tr = $('<tr>');
          table.append(tr);
          dayIndex = 0;
        }

        var td = $('<td>');
        tr.append(td);

        var day = i - offset + 1;
        
        if(i - offset >= 0 && day <= monthLength) {
          var span = $('<span>').html(day).data('day', day).addClass('day-' + day);

          if (this.options.availableDays.indexOf(dayIndex) != -1 && new Date(this.year, this.month, day) >= this.options.minDate) {
            span.addClass('available');
          }

          td.append(span);
        }

        dayIndex++;
      }

      this.element.find('.calendar').replaceWith(table);
      this.element.find('.year').html(this.year);
      this.element.find('.month').html(this.options.months[this.month]);
    },    
    renderTimepicker: function() {
      if(this.options.disableTimePicker) {
        this.element.children().find('[class*=time], .clock-icon').remove();
      }
    },
    prevHour: function() {
      if(this.hour > 0)
        this.hour--;
      this.selectTime();
      this.updateTimeInput();
      this.updateSelection();
    },
    nextHour: function() {
      if(this.hour < 23)
        this.hour++;
      this.selectTime();
      this.updateSelection();
      this.updateTimeInput();
    },
    prevMinute: function() {
      if(this.minute >= 15)
        this.minute -= 15;
      this.selectTime();
      this.updateSelection();
      this.updateTimeInput();
    },
    nextMinute: function() {
      if(this.minute < 45)
        this.minute += 15;
      this.selectTime();
      this.updateSelection();
      this.updateTimeInput();
    },
    selectTime: function() {
      var hour = ('0' + this.hour).substr(-2);
      var minute = ('0' + this.minute).substr(-2);
      this.time = hour + ':' + minute;
    },
    updateTimeInput: function() {
      if(this.timeInput) {
        this.timeInput.val(this.time);
      }
    }
  };

  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, pluginName)) {
        $.data(this, pluginName, new Plugin(this, options));
      }
    });
  };

})(jQuery, window, document);

// ============================================================================
// COUNTER

(function ($, window, document, undefined) {
  'use strict';

  var template = '<div class="counter-control">' +
    '<button type="button" class="increase-button"></button>' +
    '<span class="number"></span>' +
    '<button type="button" class="decrease-button"></button>' +
  '</div>';

  var pluginName = 'counter',
    defaults = {
      min: null,
      max: null,
      onChange: null,
      step: 1,
      value: 0,
      template: template
    };

  function Plugin(element, options) {
    this.element = $(element);

    this.options = $.extend({}, defaults, options);
    this._defaults = defaults;
    this._name = pluginName;
    this.init();
  }

  Plugin.prototype = {
    init: function () {
      this.min  = this.options.min;
      this.max  = this.options.max;
      this.step = this.options.step;

      this.element.append($(this.options.template))

      this.parseInput();
      this.updateInput();
      this.updateSelection();

      this.element.find('.increase-button').on('click', this.increase.bind(this));
      this.element.find('.decrease-button').on('click', this.decrease.bind(this));
    },
    parseInput: function () {
      var input = this.element.find('input');

      if (input.length == 1) {
        this.input = input;
      }

      if(this.input && input.val().length > 0) {
        this.value = parseInt(input.val());
      } else {
        this.value = this.options.value;
      }
    },
    updateInput: function() {
      if(this.input) {
        this.input.val(this.value);
      }
    },
    updateSelection: function() {
      this.element.find('.number').html(this.value);      
    },
    setValue: function(value) {
      this.value = value;
      this.updateSelection();
      this.updateInput();

      if(this.options.onChange !== null) {
        this.options.onChange(this, value);
      }
    },
    increase: function() {
      var canIncrease = true;
    
      if(this.max !== null) {
        if(typeof this.max === 'function') {
          canIncrease = this.max(this, this.value + this.step)
        } else {
          canIncrease = this.value + this.step <= this.max;
        }
      }

      if(canIncrease) {
        this.setValue(this.value + this.step);
      }
    },
    decrease: function() {
      var canDecrease = true;
    
      if(this.min !== null) {
        if(typeof this.min === 'function') {
          canDecrease = this.min(this, this.value - this.step)
        } else {
          canDecrease = this.value - this.step >= this.min;
        }
      }

      if(canDecrease) {
        this.setValue(this.value - this.step);
      }
    }
  };

  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, pluginName)) {
        $.data(this, pluginName, new Plugin(this, options));
      }
    });
  };

})(jQuery, window, document);

// ============================================================================
// TABS

(function ($, window, document, undefined) {
  'use strict';

  var pluginName = 'tabs',
    defaults = {};

  function Plugin(element, options) {
    this.element = $(element);

    this.options = $.extend({}, defaults, options);
    this._defaults = defaults;
    this._name = pluginName;
    this.init();
  }

  Plugin.prototype = {
    init: function () {      
      this.element.find('.tab-header').on('click', this.activate.bind(this));
    },
    activate: function(event) {
      var newHeader = $(event.currentTarget);
      var newTab = $('#' + newHeader.data('target'));
      var currentHeader = this.element.find('.active')
      var currentTab = $('#' + currentHeader.data('target'));

      currentHeader.removeClass('active');
      newHeader.addClass('active')

      currentTab.removeClass('active');
      newTab.addClass('active');
    }
  };

  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, pluginName)) {
        $.data(this, pluginName, new Plugin(this, options));
      }
    });
  };

})(jQuery, window, document);

// ============================================================================
// NOTYFICATIONS

$(document).ready(function() {

  var element = $('#system-message-container'),
      noMessages = element.children().length == 0;

  if(noMessages) {
    return;
  }

  element.hide();

  var title =  element.find('.alert-heading').text(),
      message = element.find('.alert-message').last().text();

  var n = noty({
    layout: 'topRight',
    theme: 'relax',
    text: '<strong>' + title + '</strong><br>' + message,
  });
})


// ============================================================================
// HAMBURGER MENU

jQuery(function($){
  $('#nav-icon').click(function(){
    $(this).toggleClass('open');
    $('.topmenu').toggleClass('open');
  });
});

// ============================================================================
// VISFORM WIZARD

(function ($, window, document, undefined) {
  'use strict';

  var pluginName = 'wizard',
  defaults = {
    steps: []
  };

  function Step(form, fields) {
    this.form = form;
    this.fields = fields;
  }

  Step.prototype = {
    validate: function() {
      this.alterErrorPlacement();
      var result = true;
      for (var i = 0; i < this.fields.length; i++) {
        var field = this.fields[i];

        if (field.skip) {
          continue;
        }

        if (this.form.validate().element('[name="' + field.name + '"]') == false) {
          result = false;
        }
      };      
      return result;
    },
    hide: function() {
      this.fields.map(function(field) {
        $('[name="' + field.name + '"]').closest('[class^=field]').hide();
      });
    },
    show: function() {
      this.fields.map(function(field) {
        $('[name="' + field.name + '"]').closest('[class^=field]').show();
      });
    },
    alterErrorPlacement: function() {
      this.form.data('validator').settings.errorPlacement = function(error, element) {
        element.closest('[class^=field]').append(error);
      }
    }
  }

  function Wizard(form, options) {
    this.form = $(form);

    this.options = $.extend({}, defaults, options);        
    this._defaults = defaults;
    this._name = pluginName;
    this.init();
  }

  Wizard.prototype = { 
    init: function() {
      this.submitButton = this.form.find('[type=submit]');
      
      this.createSteps();
      this.addNextStepButton();      
      this.submitButton.hide();
      this.insertIndicator();
    },
    next: function() {
      var stepIndex = this.steps.indexOf(this.step)
      if(this.step.validate() && this.steps.length - 1 > stepIndex) {
        stepIndex++;
        this.step.hide();
        this.step = this.steps[stepIndex];
        this.step.show();
      }

      if (this.steps.length - 1 ==  stepIndex) {
        this.nextStepButton.hide();
        this.submitButton.show();
      }

      this.stepIndicator.find('span').html(stepIndex + 1);
    },
    addNextStepButton: function() {
      this.nextStepButton = $('<button type="button" class="button">Next</button>');
      this.nextStepButton.on('click', this.next.bind(this));
      this.form.find('.form-actions').append(this.nextStepButton)      
    },
    createSteps: function() {
      this.steps = [];

      for (var i = 0; i < this.options.steps.length; i++) {
        var step = new Step(this.form, this.options.steps[i]);
        step.hide();
        this.steps.push(step);
      };

      this.step = this.steps[0];
      this.step.show();
    },
    insertIndicator: function() {
      this.stepIndicator = $('<p>Step <span>1</span> of ' +  this.steps.length + '</p>');
      this.form.before(this.stepIndicator);
    }
  }

  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, pluginName)) {
        $.data(this, pluginName, new Wizard(this, options));
      }
    });
  };

})(jQuery, window, document);


// ============================================================================
// VISFORMS - STEP FORM

$(document).ready(function() {
  
  $('.visform select').each(function() {
    var select = $(this);
    var label = select.prev();

    label.hide();
    select.children().first().html('Choose - ' + label.html());
  });

  $('.visform select').select2({
    minimumResultsForSearch: Infinity,
    width: '100%'
  });

  $(".visform select").on("select2:close", function (e) {
    $(this).closest('form').data('validator').settings.errorPlacement = function(error, element) {
      element.closest('[class^=field]').append(error);
    }
    $(this).valid(); 
  });
  
  $('.visform input').iCheck({
    checkboxClass: 'icheckbox_minimal',
    radioClass: 'iradio_minimal'
  });

  $('.sign-up-venue').wizard({
    steps: [
      [
        { name: 'venuename' },
        { name: 'location' },
        { name: 'currency[]' },
        { name: 'venuetype[]' },
      ],
      [
        { name: 'fullname' },
        { name: 'email' },
        { name: 'mobilenumber' },
        { name: 'preferredcontacttime[]' },
      ],
    ]
  });  

  $('.sign-up-event').wizard({
    steps: [
      [
        { name: 'eventname' },
        { name: 'location' },
        { name: 'currency[]' },
        { name: 'eventsize[]' },
      ],
      [
        { name: 'fullname' },
        { name: 'email' },
        { name: 'mobilenumber' },
        { name: 'preferredcontacttime[]' },
      ],
    ]
  });

  $('.sign-up-luxury').wizard({
    steps: [
      [
        { name: 'companyname' },
        { name: 'location' },
        { name: 'currency[]' },
        { name: 'select-luxury[]' },
      ],
      [
        { name: 'fullname' },
        { name: 'email' },
        { name: 'mobilenumber' },
        { name: 'preferredcontacttime[]' },
      ],
    ]
  });

  $('.sign-up-promoter [type=submit]').on('click', function() {
    $('.sign-up-promoter').data('validator').settings.errorPlacement = function(error, element) {
      element.closest('[class^=field]').append(error);
    }
  });

});


