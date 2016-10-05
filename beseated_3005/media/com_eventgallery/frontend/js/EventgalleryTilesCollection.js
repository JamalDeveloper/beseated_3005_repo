(function(Eventgallery, jQuery){

// create a tile layout and centers images in a tile
Eventgallery.TilesCollection = function(newOptions) {
    this.options = {
        tiles: jQuery('#events-tiles .event'),
        tilesContainer: jQuery('#events-tiles .event-tiles')
    };
	
 	this.options = Eventgallery.Tools.mergeObjects(this.options, newOptions);
    this.tiles = this.options.tiles;
    this.tilesContainer = this.options.tilesContainer;
	
};

Eventgallery.TilesCollection.prototype.calculate = function() {
		var i;
		var currentObject = this;
    	var tilesPerRow = 1;
        var tile = jQuery(this.tiles[0]);
    	 // reset grid to support resize and media queries
        jQuery.each(this.tiles, function(index, tile) {
            jQuery(tile).css( {
                'visibility' : 'hidden',
                'position' : 'static',
                'float' : 'left'
            });
        });

        // calculate tiles per row    
        var y = tile.position().top;


        for(i=1; i<this.tiles.length; i++) {
            if (jQuery(this.tiles[i]).position().top != y) {
                break;
            }
            tilesPerRow++;

        }

        // create array of height values for the columns
        var columnHeight = [];
        for (i=0; i<tilesPerRow; i++) {
            columnHeight.push(0);
        }

        var columnWidth = tile.outerWidth();

        jQuery.each(this.tiles, function(index, tile) {
            var $tile = jQuery(tile);
            $tile.data('height', $tile.outerHeight());
        });

        jQuery.each(this.tiles, function(index, tile) {

            var $tile =  jQuery(tile),
                smallestColumn = currentObject.getSmallestColumn(columnHeight);

            $tile.css({
                'left' : smallestColumn * columnWidth,
                'top' : columnHeight[smallestColumn]
            });

            columnHeight[smallestColumn] = columnHeight[smallestColumn] + $tile.data('height');
        });
        
        jQuery.each(this.tiles, function(index, tile) {
            jQuery(tile).css({
                'visibility' : 'visible',
                'position' : 'absolute',
                'float' : 'none'
            });
        });

        jQuery(this.tilesContainer).css('height', columnHeight[this.getHighestColumn(columnHeight)]);

};
    
/* 
* returns the position of the smallest value in the array
*/	
Eventgallery.TilesCollection.prototype.getSmallestColumn = function(columnHeight) {

    var smallestColumnValue = columnHeight[0];
    var smallestColumnNumber = 0;
    
    for(var i=0; i<columnHeight.length; i++) {
        if (smallestColumnValue>columnHeight[i]) {
            smallestColumnValue=columnHeight[i];
            smallestColumnNumber = i;
        }
    
    }
    return smallestColumnNumber;

};
    
/* 
* returns the position of the highest value in the array
*/
Eventgallery.TilesCollection.prototype.getHighestColumn = function(columnHeight) {

    var columnValue = columnHeight[0];
    var columnNumber = 0;
    
    for(var i=0; i<columnHeight.length; i++) {
        if (columnValue<columnHeight[i]) {
            columnValue=columnHeight[i];
            columnNumber = i;
        }
    
    }
    return columnNumber;

};

})(Eventgallery, Eventgallery.jQuery);   