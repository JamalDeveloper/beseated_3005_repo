(function(Eventgallery, jQuery){

Eventgallery.EventsList = function(newOptions) {
    Eventgallery.Imagelist.call(this, newOptions);
};

Eventgallery.EventsList.prototype = new Eventgallery.Imagelist();
Eventgallery.EventsList.prototype.constructor = Eventgallery.EventsList;


Eventgallery.EventsList.prototype.processList = function() {
    /* processes the image list*/    
    var width = this.width;
    var currentObject = this;
    jQuery.each(this.images, function () {
        var height = Math.ceil(width * currentObject.options.rowHeightPercentage / 100);
        this.setSize(width, height, true);
    });
};

})(Eventgallery, Eventgallery.jQuery);
