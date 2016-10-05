(function(Eventgallery, jQuery){
	
/*
 Class to manage an image. This can be the img tag or a container. It has to manage glue itself.
 */
Eventgallery.Image = function(image, index, newOptions) {
	
    this.options = {
        maxImageHeight: 800
    };
	
    this.options = Eventgallery.Tools.mergeObjects(this.options, newOptions);
    this.tag = jQuery(image);
    this.index = index;
    this.calculatedWidth = 0;
    this.calcSize();
};

Eventgallery.Image.prototype.calcSize = function () {
    // glue includes everything but the image width/heigt: margin, padding, border       
    var image = this.tag.find('img').first();
    
    if (image.length === 0) {
		return;
    }
    
    var elements = [this.tag, image];

    this.glueLeft = Eventgallery.Tools.calcBorderWidth(elements, ['padding-left', 'margin-left', 'border-left-width']);
    this.glueRight = Eventgallery.Tools.calcBorderWidth(elements, ['padding-right', 'margin-right', 'border-right-width']);
    this.glueTop = Eventgallery.Tools.calcBorderWidth(elements, ['padding-top', 'margin-top', 'border-top-width']);
    this.glueBottom = Eventgallery.Tools.calcBorderWidth(elements, ['padding-bottom', 'margin-bottom', 'border-bottom-width']);    

    // get image size from data- attributes
   
    this.width = image.data("width");
    this.height = image.data("height");


    // fallback of data- attributes are not there
    if (this.width === undefined) {
        this.width = this.tag.width() - this.glueLeft - this.glueRight;
    }

    if (this.height === undefined) {
        this.height = this.tag.height() - this.glueTop - this.glueBottom;
    }
};


Eventgallery.Image.prototype.setSize = function (width, height, doScaleBackground) {

    // limit the maxium height of an image
    if (height > this.options.maxImageHeight) {
        width = Math.round(width / height * this.options.maxImageHeight);
        height = this.options.maxImageHeight;
    }


    var newWidth = width - this.glueLeft - this.glueRight;
    var newHeight = height - this.glueTop - this.glueBottom;


    if (this.width < newWidth) {
        newWidth = this.width;
    }


    if (this.height < newHeight) {
        newHeight = this.height;

    }


    var ratio = this.width / this.height;

    //console.log("the size of the image should be: "+width+"x"+height+" so I have to set it to: "+newWidth+"x"+newHeight);


    var sizeCalculator = new Eventgallery.SizeCalculator();
    var googleWidth = sizeCalculator.getSize(newWidth, newHeight, ratio);


    //adjust background images
    var image = this.tag.find('img');
	if (image.length === 0) {
		return;
	}
    // set a new background image
    var backgroundImageStyle = image.css('background-image');
    var longDesc = image.attr('longDesc');
    if (!longDesc) {
        longDesc = "";
    }

    backgroundImageStyle = sizeCalculator.adjustImageURL(backgroundImageStyle, googleWidth);
    longDesc = sizeCalculator.adjustImageURL(longDesc, googleWidth);

    image.css('background-image', backgroundImageStyle);
    image.attr('longDesc', longDesc);
    image.css('background-position', '50% 50%');
    image.css('background-repeat', 'no-repeat');
    image.css('display', 'block');
    image.css('margin', 'auto');
    if (doScaleBackground) {
        image.css('background-size', ' 100% 100%');
    }

	// IE8 fix: check the width/height first
	if (newWidth>0) {
    	image.css('width', newWidth);
    }
    if (newHeight>0) {
    	image.css('height', newHeight);
    }

};

})(Eventgallery, Eventgallery.jQuery);