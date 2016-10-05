(function(Eventgallery){
/* determines the size of an image so a image server can deliver it. */

Eventgallery.SizeCalculator = function(newOptions) {
	this.options = {
        // to be able to handle internal and google picasa images, we need to restrict the availabe image sizes.
        availableSizes: new Array(32, 48, 64, 72, 94, 104, 110, 128, 144, 150, 160, 200, 220, 288, 320, 400, 512, 576, 640, 720, 800, 912, 1024, 1152, 1280, 1440)
    };
    this.options = Eventgallery.Tools.mergeObjects(this.options, newOptions);   
};

Eventgallery.SizeCalculator.prototype.adjustImageURL = function (url, size) {
    url = url.replace(/width=(\d*)/, 'width=' + size);
    url = url.replace(/\/s(\d*)\//, '/s' + size + '/');
    url = url.replace(/\/s(\d*)-c\//, '/s' + size + '-c/');

    return url;
};

Eventgallery.SizeCalculator.prototype.getSize = function (width, height, ratio) {
	
    var googleWidth = this.options.availableSizes[0];
	
	for(var index=0; index < this.options.availableSizes.length; index++) {
		var item = 	this.options.availableSizes[index];
    	var widthOkay;
        var heightOkay;
        
        if (googleWidth > 32){
        	break;
        }
        
        var lastItem = index == this.options.availableSizes.length - 1;

        if (ratio >= 1) {
            widthOkay = item > width;
            heightOkay = item / ratio > height;

            if ((widthOkay && heightOkay) || lastItem) {
                googleWidth = item;
            }
        } else {
            heightOkay = item > height;
            widthOkay = item * ratio > width;

            if ((widthOkay && heightOkay) || lastItem) {
                googleWidth = item;
            }
        }
    }
	
    return googleWidth;
};

})(Eventgallery);   