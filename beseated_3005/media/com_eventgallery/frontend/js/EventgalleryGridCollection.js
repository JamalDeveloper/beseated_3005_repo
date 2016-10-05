(function(Eventgallery, jQuery){

// create a grid layout and centers images in a tile
Eventgallery.GridCollection = function(newOptions) {
    this.options = {
        tiles: jQuery('#events-tiles .event'),
        tilesContainer: jQuery('#events-tiles .event-tiles'),
        thumbSelector: '.event-thumbnail',
        thumbContainerSelector: '.event-thumbnails'
    };
            
 	this.options = Eventgallery.Tools.mergeObjects(this.options, newOptions);
	this.tiles = this.options.tiles;
	this.tilesContainer = this.options.tilesContainer;
};

Eventgallery.GridCollection.prototype.calculate = function() {

    var tilesPerRow = 1;
    var currentObject = this;
     // reset grid to support resize and media queries
    this.tiles.css({
        visibility: 'hidden',
        position: 'static',
        'float': 'left'
    });

    // calculate tiles per row    
    var y =jQuery(this.tiles[0]).position().top;

    for(var i=1; i<this.tiles.length; i++) {
        if (jQuery(this.tiles[i]).position().top != y) {
            break;
        }
        tilesPerRow++;
    }


    var columnWidth = jQuery(this.tiles[0]).width();
    var currentColumn = 0;
    var currentRow = 0;
    
    // doing this loop multiple times increases the performance due to 
    // the fact that we can avoid size recalculations.
    jQuery.each(this.tiles, function() {
        jQuery(this).css({
            'left': currentColumn*columnWidth,
            'top': currentRow*columnWidth,
            'height':  columnWidth
        });
        
        currentColumn++;
        if (currentColumn+1>tilesPerRow) {
            currentColumn = 0;                
            currentRow++;
        }
        
        
    });
    
    // calculate center images date
    jQuery.each(this.tiles, function() {
        var tile = jQuery(this);
        var thumb = tile.find(currentObject.options.thumbSelector);
        var thumbContainer = tile.find(currentObject.options.thumbContainerSelector);

        var tileWidth = tile.width() - Eventgallery.Tools.calcBorderWidth([tile], ['padding-right', 'margin-right', 'border-width', 'padding-left', 'margin-left', 'border-width']);
        
        var adjustX = Math.floor((tileWidth - thumb.width())/2);
        var adjustY = Math.floor((tile.height() - thumb.height())/2);
        
        thumbContainer.data('adjust-x', adjustX);
        thumbContainer.data('adjust-y', adjustY);
    });
    
    // center images
    jQuery.each(this.tiles, function() {
    	var thumbContainer = jQuery(this).find(currentObject.options.thumbContainerSelector);
        thumbContainer.css('left', thumbContainer.data('adjust-x')+'px' );
        thumbContainer.css('top',  thumbContainer.data('adjust-y')+'px' );
    });        

    var overallHeight = Math.ceil(this.tiles.length/tilesPerRow)*columnWidth;
    this.tilesContainer.css('height', overallHeight);

    this.tiles.css({
        visibility: 'visible',
        position: 'absolute',
        'float': 'none'
    });

};

})(Eventgallery, Eventgallery.jQuery);