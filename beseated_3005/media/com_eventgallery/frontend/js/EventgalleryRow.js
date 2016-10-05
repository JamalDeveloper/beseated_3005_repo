(function(Eventgallery, jQuery){
	
/* processes a row is a image list */
Eventgallery.Row = function(newOptions) {

	this.options = {
        maxWidth: 960,
        maxHeight: 250,
        heightJitter: 0,
        adjustHeight: true,
        doFillLastRow: true
    };
    this.options = Eventgallery.Tools.mergeObjects(this.options, newOptions);
    this.isLastRow = false;
    this.images = [];
    this.width = 0;
    if (this.options.heightJitter > 0) {
        this.options.maxHeight = Math.floor(this.options.maxHeight + (Math.random() * 2 * this.options.heightJitter) - this.options.heightJitter);
    }
};

Eventgallery.Row.prototype.add = function (eventgalleryImage) {
    var imageWidth =eventgalleryImage.width / eventgalleryImage.height * this.options.maxHeight;

    // determine the number of images per line. return false if the row if full.

    var addThisImage = this.width + imageWidth <= this.options.maxWidth || this.images.length === 0;

    if (!addThisImage) {
        var gap = Math.abs(this.options.maxWidth - this.width - imageWidth) / this.options.maxWidth;
        if (gap<0.2) {
            addThisImage = true;
        }
    }

    // determine the number of images per line. return false if the row if full.
    if (addThisImage) {
        this.images.push(eventgalleryImage);
        eventgalleryImage.calculatedWidth = imageWidth;
        this.width = this.width + imageWidth;
        return true;
    } else {
        return false;
    }
};

Eventgallery.Row.prototype.processRow = function () {
    var gap, rowHeight, i;

    gap = this.options.maxWidth - this.width;

    if (this.isLastRow && this.options.doFillLastRow === false) {
        if (gap>=0) {
            gap = 0;
        }
        rowHeight = this.options.maxHeight;
    } else {

        rowHeight = this.options.maxHeight / (this.width/this.options.maxWidth);
    }

    if (this.options.adjustHeight === false) {
        rowHeight = this.options.maxHeight;
    }

    for (i=0; i < this.images.length; i++) {
        var image = this.images[i];

        var calculatedWidth = image.calculatedWidth;

        // how much of the gap does this element need to fill?
        var gapToClose = (calculatedWidth / this.width ) * gap;

        image.setSize(calculatedWidth + gapToClose, rowHeight, this.options.adjustHeight === true);
    }


};

})(Eventgallery, Eventgallery.jQuery);