(function(Eventgallery, jQuery){
	"use strict";
    jQuery( document ).ready(function() {

		/*
		* GRID LIST OF EVENTS
		*/

        jQuery('.eventgallery-events-gridlist').each(function(index, container){	    
        	var $container = jQuery(container);
	    	var $thumbnails = $container.find('.event-thumbnails .event-thumbnail');
	    	
	        var options = {
	            rowHeightPercentage: 100,
	            imagesetContainer: $container.find('.event-thumbnails').first(),
	            imageset: $thumbnails,
	            lazyloader: null,
	            initComplete: function () {
	                var lazyLoadOptions = {container: container};
                    options.eventgalleryLazyloader = new Eventgallery.Lazyload(lazyLoadOptions);
	            },
	            resizeStart: function () {
	                $container.find('.event-thumbnails .event-thumbnail img.eventgallery-lazyme').removeClass('eventgallery-lazyload-loaded').addClass('eventgallery-lazyload-loading');
	            },
	            resizeComplete: function () {
	                options.eventgalleryLazyloader.initialize();
	                jQuery(window).trigger('scroll');
	            }
	        };

	        // initialize the imagelist
	        
	        if ($thumbnails.length>0) {
	        	new Eventgallery.EventsList(options);
	        }
        });
    

		/*
		* TILE LIST OF EVENTS
		*/
  
	    jQuery('.eventgallery-events-tiles-list').each(function(index, container){
	    	var $container = jQuery(container);

	        var options = {
	            imagesetContainer: $container.find('.event-thumbnails').first(),
	            imageset: $container.find('.event-thumbnail'),
	            eventgalleryTilesCollection: null,
	            eventgalleryLazyloader: null,	
	            initComplete: function () {
	                var lazyLoadOptions = {container: container};
                    options.eventgalleryLazyloader = new Eventgallery.Lazyload(lazyLoadOptions);
	                
	                var tilesOptions = {
	                    tiles: $container.find('.eventgallery-tiles .eventgallery-tile'),
	                    tilesContainer: $container.find('.eventgallery-tiles')
	                };
	                options.eventgalleryTilesCollection = new Eventgallery.TilesCollection(tilesOptions);
	                options.eventgalleryTilesCollection.calculate();
	                // we need to recalculate the whole thing because it might happen that a font loads
	                // and the size of a tile changes. 
	                jQuery(window).load( function(){
	                    options.eventgalleryTilesCollection.calculate();
	                });

	            },
	            resizeStart: function () {
	                $container.find('.event-thumbnails .event-thumbnail img.eventgallery-lazyme').removeClass('eventgallery-lazyload-loaded').addClass('eventgallery-lazyload-loading');
	            },
	            resizeComplete: function () {
	                options.eventgalleryLazyloader.initialize();
	                options.eventgalleryTilesCollection.calculate();
	                jQuery(window).trigger('scroll');
	            }
	        };

	        // initialize the imagelist
	        new Eventgallery.EventsTiles(options);
    
	    });

		/*
		* TILES LIST OF IMAGES
		*/

	    jQuery('.eventgallery-event-tiles-list').each(function(index, container){
	    	var $container = jQuery(container);
	        var options = {
	            imagesetContainer: $container.find('.event-thumbnails').first(),
	            imageset: $container.find('.event-thumbnail'),
	            adjustMode: 'width',
	            eventgalleryLazyloader: null,
	            eventgalleryTilesCollection: null,
	            initComplete: function () {
	                var lazyLoadOptions = {container: container};
                    options.eventgalleryLazyloader = new Eventgallery.Lazyload(lazyLoadOptions);
	                
	                var tilesOptions = {
	                    tiles: $container.find('.eventgallery-tiles .eventgallery-tile'),
	                    tilesContainer: $container.find('.eventgallery-tiles')
	                };
	                options.eventgalleryTilesCollection = new Eventgallery.TilesCollection(tilesOptions);
	                options.eventgalleryTilesCollection.calculate();
	                // we need to recalculate the whole thing because it might happen that a font loads
	                // and the size of a tile changes. 
	                jQuery(window).load( function(){
	                    options.eventgalleryTilesCollection.calculate();
	                });

	            },
	            resizeStart: function () {
	                $container.find('.event-thumbnails .event-thumbnail img.eventgallery-lazyme').removeClass('eventgallery-lazyload-loaded').addClass('eventgallery-lazyload-loading');
	            },
	            resizeComplete: function () {
	                options.eventgalleryLazyloader.initialize();
	                options.eventgalleryTilesCollection.calculate();
	                jQuery(window).trigger('scroll');
	            }
	        };

	        // initialize the imagelist
	        new Eventgallery.EventsTiles(options);
    	});

		/*
		* SIMPLE IMAGE LIST
		*/
		jQuery('.eventgallery-event-gridlist').each(function(index, container){	
			var $container = jQuery(container);
			var options = {
                imagesetContainer: $container.find('.event-thumbnails').first(),
                imageset: $container.find('.event-thumbnail'),
                adjustMode: 'height',
            	eventgalleryGridCollection: null,
            	eventgalleryLazyloader: null,
                initComplete: function () {
                	var lazyLoadOptions = {container: container};
                    options.eventgalleryLazyloader = new Eventgallery.Lazyload(lazyLoadOptions);
                    
                    var gridOptions = {
                        tiles: $container.find('.eventgallery-simplelist .eventgallery-simplelist-tile'),
                        tilesContainer: $container.find('.eventgallery-simplelist'),
                        thumbSelector: '.event-thumbnail',
                        thumbContainerSelector: '.event-thumbnails'
                    };

                    options.eventgalleryGridCollection = new Eventgallery.GridCollection(gridOptions);
                    options.eventgalleryGridCollection.calculate();
                    // we need to recalculate the whole thing because it might happen that a font loads
                    // and the size of a tile changes. 
                    jQuery(window).load( function(){
                        options.eventgalleryGridCollection.calculate();
                    });

                },
                resizeStart: function () {
                    $container.find('.event-thumbnails .event-thumbnail img.eventgallery-lazyme').removeClass('eventgallery-lazyload-loaded').addClass('eventgallery-lazyload-loading');
                },
                resizeComplete: function () {
                    options.eventgalleryLazyloader.initialize();
                    options.eventgalleryGridCollection.calculate();
                    jQuery(window).trigger('scroll');
                }
            };

            // initialize the imagelist
            new Eventgallery.EventsTiles(options);
		});

		/*
		* IMAGE LIST
		*/
		
        jQuery('.eventgallery-imagelist').each(function(index, imagesetContainer){
			var $imagesetContainer = jQuery(imagesetContainer);
            var options = {
                rowHeight: $imagesetContainer.data('rowheight'),
                rowHeightJitter: $imagesetContainer.data('rowheightjitter'),
                firstImageRowHeight: $imagesetContainer.data('firstimagerowheight'),                
                doFillLastRow: $imagesetContainer.data('dofilllastrow') === true,                
                imagesetContainer: imagesetContainer,
                imageset: $imagesetContainer.find('.thumbnail'),
                eventgalleryLazyloader: null,

                initComplete: function () {
                	var lazyLoadOptions = {container: imagesetContainer};
                    options.eventgalleryLazyloader = new Eventgallery.Lazyload(lazyLoadOptions);
                },
                resizeStart: function () {
                    $imagesetContainer.find('.thumbnail img.eventgallery-lazyme').removeClass('eventgallery-lazyload-loaded').addClass('eventgallery-lazyload-loading');
                },
                resizeComplete: function () {
                    options.eventgalleryLazyloader.initialize();
                    jQuery(window).trigger('scroll');
                }
            };
            
            // initialize the imagelist
            new Eventgallery.Imagelist(options);
        });


    }); //end domready
})(Eventgallery, Eventgallery.jQuery);