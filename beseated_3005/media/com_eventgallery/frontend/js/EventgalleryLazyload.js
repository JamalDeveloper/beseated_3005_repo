(function(Eventgallery, jQuery){
	
Eventgallery.Lazyload = function(newOptions) {
	this.options = {
        range: 200,
        elements: 'img.eventgallery-lazyme',
        container: window,
        startPosition: -1,
        onScroll: function () {
            //console.log('scrolling');
        },
        onLoad: function (img) {
            //console.log('image loaded');
            img.removeClass('eventgallery-lazyload-loading').addClass('eventgallery-lazyload-loaded');
        },
        onComplete: function () {
            //console.log('all images loaded');
        }
    };

    /**
     * This is useful if somebody wants to use an inner div to do the scroll magic.
     * In this case putting a very high value in here would practically disable Layzload.
     * We still need it to add the background images so we can't simply disable it.
     */
    if (typeof EventGalleryLazyloadRange != 'undefined') {
        this.options.range = EventGalleryLazyloadRange;
    }

    this.initialize(newOptions);
};

Eventgallery.Lazyload.prototype.initialize = function(newOptions) { 
    
    this.options = Eventgallery.Tools.mergeObjects(this.options, newOptions);

    this.$container = (this.options.container != window && this.options.container != document.body ? jQuery(this.options.container) : jQuery(document.body));
    this.$elements = this.$container.find(this.options.elements);

    this.containerDimension = jQuery(window).height();
    this.startPosition = this.options.startPosition;
    //var $offset = (this.options.container != window && this.options.container != document.body ? this.$container : jQuery(document.body));
    var $offset = jQuery(document.body);

	//noinspection CoffeeScriptUnusedLocalSymbols
    // this is necessary to make the current object available in new function
    var self = this;
    /* find elements remember and hold on to */
	for(var index = 0; index < this.$elements.length; index++) {
		var el = jQuery(this.$elements[index]);		
	
        /* reset image src IF the image is below the fold and range */
        if (el.attr('longDesc')) {
            el.addClass('eventgallery-lazyload-loading');
        }
    }
	
    this.$elements = this.$elements.filter(function () {
    	var $el = jQuery(this);

        /* reset image src IF the image is below the fold and range */
        if ($el.attr('longDesc')) {
            return true;            
        }
    });
    
    /* create the action function */
    var action = function () {
        var currentPosition = jQuery(window).scrollTop();
        
        if (currentPosition > self.startPosition) {
            self.$elements = self.$elements.filter(function () {
            	var $el = jQuery(this);
		        var elPos = $el.offset().top - $offset.offset().top;
		        //console.log('currentPosition ', (currentPosition + self.options.range + self.containerDimension), ' >= elPos ', elPos);
                if ((currentPosition + self.options.range + self.containerDimension) >= elPos) {
                    if ($el.attr('longDesc')) {
                        $el.css('background-image', 'url("' + $el.attr('longDesc') + '")');
                    }
                    self.options.onLoad.call(self, $el);
                    return false;
                }
                return true;
            });
            self.startPosition = currentPosition;
        }
        
        self.options.onScroll.call();
        /* remove this event IF no elements */
        if (self.$elements.length === 0) {
            jQuery(window).off('scroll', action);
            self.options.onComplete.call();
        }
    };

    /* listen for scroll */
    jQuery(window).on('scroll', action);
    
    action();
        
};
})(Eventgallery, Eventgallery.jQuery);