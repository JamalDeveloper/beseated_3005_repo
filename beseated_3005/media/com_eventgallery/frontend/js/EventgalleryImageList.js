(function(Eventgallery, jQuery){

Eventgallery.Imagelist = function(newOptions) {

    this.options = {
        rowHeightPercentage: 100,
        rowHeight: 150,
        rowHeightJitter: 0,
        minImageWidth: 150,
        // resize the last image to full width
        doFillLastRow: false,
        // the object where we try to get the width from 
        imagesetContainer: null,
		// the object containing all the images elements. Usually they are retieved with a selector like '.imagelist a',
        imageset: null,       
        firstImageRowHeight: 2,
        initComplete: function () {
        },
        resizeStart: function () {
        },
        resizeComplete: function () {
        }
    };
	this.images = Array();
	// used to compare for width changes
	this.eventgalleryPageWidt = 0;
	// the width of the container. This is kind of tricky since there can be many containers or just one.
	this.width = null;
    this.options = Eventgallery.Tools.mergeObjects(this.options, newOptions);
    if (undefined !== newOptions) {
        this.initialize();
    }
};

Eventgallery.Imagelist.prototype.initialize = function () {
    var currentObject = this;
    this.width = jQuery(this.options.imagesetContainer).width();
    
    var images_tags = this.options.imageset;
    this.images = Array();

    jQuery.each(images_tags, function (index, item) {
        currentObject.images.push(new Eventgallery.Image(item, index));
    });

    jQuery(window).resize( jQuery.proxy(function () {
        window.clearTimeout(this.eventgalleryTimer);

        this.eventgalleryTimer = setTimeout(jQuery.proxy( (function () {
            var new_width = jQuery(this.options.imagesetContainer).width();
            this.width = new_width;
            if (this.eventgalleryPageWidth != new_width) {
                this.options.resizeStart();
                this.eventgalleryPageWidth = new_width;
                this.processList();
                this.options.resizeComplete();
            }
        }), this), 500);

    }, this) );

	jQuery(this.options.imagesetContainer).css('min-height', this.options.rowHeight*this.images.length);
		
    this.processList();

	jQuery(this.options.imagesetContainer).css('min-height','0px');
	
	//add a tiny timeout. This prevents some issue with lazyload
	//where images didn't load since the offset was wrong.
    window.setTimeout(this.options.initComplete, 1);
};

/*calculated the with of an element*/
Eventgallery.Imagelist.prototype.getRowWidth = function () {
    var rowWidth = this.width;

    /* fix for the internet explorer if width if 45.666% == 699.87px*/
    if (window.getComputedStyle) {
        rowWidth = Math.floor(parseFloat(window.getComputedStyle(this.options.imagesetContainer).width) ) - 1;
    } else {
        rowWidth = rowWidth - 1;
    }

    return rowWidth;
};

/* processes the image list*/
Eventgallery.Imagelist.prototype.processList = function () {

	var options;

    /* find out how much space we have*/
    var rowWidth = this.getRowWidth();


    /* get a copy of the image list because we will pop the image during iteration*/
    var imagesToProcess = this.images.slice(0);

    /* display the first image larger */
    if (this.options.firstImageRowHeight > 1) {
        var image = imagesToProcess.shift();

        /*if we have a large image, we have to hide it to get the real available space*/
        image.tag.css('display', 'none');
        rowWidth = this.getRowWidth();
        image.tag.css('display', 'block');

        var imageHeight = this.options.firstImageRowHeight * this.options.rowHeight;
        var imageWidth = Math.floor(image.width / image.height * imageHeight);

        if (imageWidth + this.options.minImageWidth >= rowWidth) {
            imageWidth = rowWidth;
            rowsLeft = 0;
        }

        image.setSize(imageWidth, imageHeight);

        options = {
            maxWidth: rowWidth - imageWidth,
            maxHeight: this.options.rowHeight,
            adjustHeight: false
        };

        if (options.maxWidth > 0) {
            this.generateRows(imagesToProcess, this.options.firstImageRowHeight, options, false);
        }
    }

    options = {
        maxWidth: rowWidth,
        maxHeight: this.options.rowHeight,
        heightJitter: this.options.rowHeightJitter,
        doFillLastRow: this.options.doFillLastRow
    };

    this.generateRows(imagesToProcess, 99999, options, true);

};

/**
* @param bool finalRows defines if this set is the last set of rows.
*/
Eventgallery.Imagelist.prototype.generateRows = function (imagesToProcess, numberOfRowsToCreate, options, finalRows) {
	var currentRow = new Eventgallery.Row(options);

    while (imagesToProcess.length > 0 && numberOfRowsToCreate > 0) {
        var addSuccessfull = currentRow.add(imagesToProcess[0]);
        if (addSuccessfull) {
            imagesToProcess.shift();
        } else {
            currentRow.processRow();
            numberOfRowsToCreate--;
            if (numberOfRowsToCreate === 0) break;
            currentRow = new Eventgallery.Row(options);
        }
    }
	
	if (finalRows) {currentRow.isLastRow = true;}
    currentRow.processRow();
};

})(Eventgallery, Eventgallery.jQuery);