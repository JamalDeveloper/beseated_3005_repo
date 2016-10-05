(function(Eventgallery, jQuery){

/* processes a list of images and tries to resize separately*/
Eventgallery.EventsTiles = function(newOptions) {
    Eventgallery.Imagelist.call(this, newOptions);
};

Eventgallery.EventsTiles.prototype = new Eventgallery.Imagelist();
Eventgallery.EventsTiles.prototype.constructor = Eventgallery.EventsTiles;

Eventgallery.EventsTiles.prototype.processList = function() {
    var width = this.width;
    var currentObject = this;
      	 
    jQuery.each(this.images, function () {
        var newHeight = Math.round(this.height / this.width * width);
        var newWidth = width;
        if (currentObject.options.adjustMode == "height" && this.height>this.width) {
            newHeight = width;
            newWidth = Math.round(this.width / this.height * newHeight);
        }

        this.setSize(newWidth, newHeight, true);
        
    });
};

})(Eventgallery, Eventgallery.jQuery);

