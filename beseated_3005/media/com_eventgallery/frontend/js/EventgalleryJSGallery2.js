(function(Eventgallery, jQuery){
    /*
     *    Constructor. Starts up the whole thing :-)
     *
     *    This script is free to use. It has been created by http://www.aplusmedia.de and
     *    can be downloaded on http://www.esteak.net.
     *    License: GNU GPL 2.0: http://creativecommons.org/licenses/GPL/2.0/
     *    Example on: http://blog.aplusmedia.de/moo-gallery2
     *    Known issues:
     *    - preloading does not care about initialIndex param
     *    - hovering to a control over the border of the big image will make the other one flickering
     *    - if you enter and leave the control area very quickly, the control flickers sometimes
     *    - does not work in IE6
     *
     *    @param {Array} thumbs, An array of HTML elements
     *    @param {HTMLelement} bigImageContainer, the full size image
     *    @param {HTMLelement} pageContainer, If you have several pages, put them in this container
     *    @param {Object} options, You have to pass imagesPerPage if you have more than one!
     */
    Eventgallery.JSGallery2 = function(thumbs, bigImageContainer, pageContainer, newOptions) {

        this.options = {
            'prevHandle': null,			//if you pass a previous page handle in here, it will be hidden if it's not needed
            'nextHandle': null,			//like above, but for next page
            'countHandle': null,		//handle of the counter variable
            'titleTarget': null,		//target HTML element where image texts are copied into
            'initialIndex': -1,			//which thumb to select after init. you could create deep links with it.
            'maxOpacity': 0.8,			//maximum opacity before cursor reaches prev/next control, then it will be set to 1 instantly.
            'showSocialMediaButton': true,
            'showCartButton': true,
            'showCartConnector': false,
            'cartConnectorLinkRel': '',
            'activeClass': 'thumbnail-active', // the css class for the active thumbnail
            'loadingClass': 'thumbnail-loading', // the css class for the loading thumbnail
            'lightboxRel': 'lightbo2', // the trigger rel for the lightbox script
            'touchContainerSelector' : '#bigimageContainer'
        };

        this.options = Eventgallery.Tools.mergeObjects(this.options, newOptions);

        var pages = jQuery(pageContainer).children('div');
		
		// defines if thumbs are currently running
		this.running = false;
        this.currentPageNumber = 0;
        this.imageToLoadQueue = [];
        this.blockKeys = false;
        this.imagesPerFirstPage = jQuery(pages[0]).children('div.thumbnail').length;
        this.imagesPerPage = this.imagesPerFirstPage;

        if (pages.length>1 && jQuery(pages[1]).children('div.thumbnail').length>0)  {
            this.imagesPerPage = jQuery(pages[1]).children('div.thumbnail').length;
        }

        this.thumbs = thumbs;

        this.bigImage = jQuery(bigImageContainer);
        this.pageContainer = jQuery(pageContainer);
        this.convertThumbs();
        
        this.lastPage = Math.ceil((this.thumbs.length - this.imagesPerFirstPage) / this.imagesPerPage) + 1;

        var url = document.location;
        var strippedUrl = url.toString().split("#");
        this.initialIndex = 0;
        if (strippedUrl.length > 0) {
            var objRegExp = /(^\d\d*$)/;
            if (objRegExp.test(strippedUrl[1]) === true) {
                this.initialIndex = strippedUrl[1];

            }
        }

        this.createControls();

        this.gotoPage(0);

        if (this.options.initialIndex != -1) {
            this.unBlockKeys();
            this.selectByIndex(this.options.initialIndex);
        } else if (this.initialIndex !== 0) {

            this.unBlockKeys();
            this.selectByIndex(this.initialIndex);
        }
        
        this.loadNextImage();
        
    };

    Eventgallery.JSGallery2.prototype.createControls = function () {
        this.prevLink = jQuery('<a href="#" class="link jsgallery-prev"></a>');
        this.prevLink.bind('touchend click', jQuery.proxy(this.prevImage, this));
        this.prevLink.mouseleave(jQuery.proxy(this.mouseLeaveHandler, this));

        this.prevLink.mouseover( jQuery.proxy( function (e) {
            this.focusControl(e, this.prevLink);
        }, this));

        this.zoomLink = jQuery('<a href="#" class="link jsgallery-zoom"></a>');
        //this.zoomLink.bind('touchend click', jQuery.proxy(this.zoomImage, this));
        this.zoomLink.mouseleave(jQuery.proxy(this.mouseLeaveHandler, this));       
        this.zoomLink.attr('data-eventgallery-lightbox', this.options.lightboxRel);		

        this.zoomLink.mouseover( jQuery.proxy(function (e) {
            this.focusControl(e, this.zoomLink);
        }, this));


        this.nextLink = jQuery('<a href="#" class="link jsgallery-next"></a>');
        this.nextLink.bind('touchend click', jQuery.proxy(this.nextImage, this));
        this.nextLink.mouseleave(jQuery.proxy(this.mouseLeaveHandler, this));

        this.nextLink.mouseover(jQuery.proxy(function (e) {
            this.focusControl(e, this.nextLink);
        }, this));

		this.bigImage.parent().append(this.prevLink);
        this.bigImage.parent().append(this.zoomLink);
        this.bigImage.parent().append(this.nextLink);
        
        if (this.options.showCartButton) {
            this.add2cartLink = jQuery('<a href="#" class="eventgallery-add2cart button-add2cart jsgallery-add2cart"><i class="egfa egfa-2x egfa-cart-plus"></i></a>');
            if (jQuery('.imagetypeselection').is(':visible') === false) {
                this.add2cartLink.hide();
            }
            this.bigImage.parent().append(this.add2cartLink);
            jQuery('body').trigger('updatecartlinks');
        }

        if (this.options.showCartConnector) {

            this.cartConnectorLink = jQuery('<a href="#" id="ajax-cartconnector" class="button-cart-connector jsgallery-cartconnector"><i class="egfa egfa-2x egfa-cart-plus"></i></a>');
            this.cartConnectorLink.attr('rel', this.options.cartConnectorLinkRel);          
            this.bigImage.parent().append(this.cartConnectorLink);
        }

        if (this.options.showSocialMediaButton) {
            this.socialmediabutton = jQuery('<a id="ajax-social-media-button" class="social-share-button social-share-button-open jsgallery-socialmedia" rel="nofollow" href="#"><i class="egfa egfa-2x egfa-share-alt-square"></i></a>');
            this.bigImage.parent().append(this.socialmediabutton);
        }

        jQuery(document).keydown(jQuery.proxy(this.keyboardHandler, this));
        
        		
		var options = {
		  dragLockToAxis: true,
		  dragBlockHorizontal: true,
		  swipeVelocityX: 0.2  
		};



	
		var swipeleft =  jQuery.proxy(function(e) {
		    this.nextImage(e);
		}, this);

		var swiperight = jQuery.proxy(function(e) {
		    this.prevImage(e);
		}, this);

        var tabaction = function(e) {
            event.target.click();
        };

        Eventgallery.Touch.addTouch(this.options.touchContainerSelector,
            swiperight,
            swipeleft,
            tabaction,
            true
        );

        this.mouseLeaveHandler();
    };

    /**
     * Focuses one control
     *
     * @param {Event} event
     * @param {HTMLElement} control
    */
    Eventgallery.JSGallery2.prototype.focusControl = function (event, control) {
        control.css('opacity', 1);        
    };

    /**
     * Hides the controls.
     */
    Eventgallery.JSGallery2.prototype.mouseLeaveHandler = function () {
        jQuery(this.nextLink).css('opacity', 0);
        jQuery(this.prevLink).css('opacity', 0);
        jQuery(this.zoomLink).css('opacity', 0);
    };

    /**
     * Handles keyboard interactions.
     * @param {Event} event
     */
    Eventgallery.JSGallery2.prototype.keyboardHandler = function (event) {
    	
    	if (jQuery.eventgallery_colorbox.isOpen() === true) {
    		if (jQuery.eventgallery_colorbox.element().data('eventgallery-lightbox') !== undefined && jQuery.eventgallery_colorbox.element().data('eventgallery-lightbox').indexOf('cart')>-1) {
    			return;
    		}
    	}
    	
        if (!this.blockKeys) {
            if (event.keyCode >= 49 && event.keyCode <= 57) {
                this.gotoPage(event.key - 1);
            } else if (event.keyCode == 37) {
                this.prevImage(event);
            } else if (event.keyCode == 39) {
                this.nextImage(event);
            }
        }
    };

    /**
     *    Returns the distance to the mouse from the middle of a given element.
     *    @param {HTMLelement} element
     *    @param {Event} event
     *    @return integer
     */
    Eventgallery.JSGallery2.prototype.getDistanceToMouse = function (element, event) {
        var s = jQuery(element);
        var xDiff = Math.abs(event.clientX - (s.position().left + s.width() / 2));
        var yDiff = Math.abs(event.clientY - (s.position().top + s.height() / 2));
        return Math.sqrt(Math.pow(xDiff, 2) + Math.pow(yDiff, 2));
    };

    Eventgallery.JSGallery2.prototype.resetThumbs = function () {        
       	this.running = false;
        this.imageToLoadQueue = [];
		this.convertThumbs();        
		this.loadNextImage();
        //if we like to select another image on that page than the first one
        this.select(this.selectedContainer, true);
    };

    /**
     *    Adds the border to the thumbs and so on. (conversion of static thumbs)
     */
    Eventgallery.JSGallery2.prototype.convertThumbs = function () {
        var currentObject = this;
        jQuery.each(this.thumbs, function (count, thumbContainer) {
            currentObject.convertThumb(thumbContainer, count);
        });
    };

    /**
     * Converts one single thumb.
     * @param {HTMLelement} thumbContainer
     * @param {Integer} count
     */
    Eventgallery.JSGallery2.prototype.convertThumb = function (thumbContainer, count) {
    	
        if (thumbContainer === undefined) {
            return;
        }

        var container = jQuery(thumbContainer);

        container.click(jQuery.proxy(function(e) {
            e.preventDefault();
            this.select(container);
        }, this));


        container.css('position', 'relative');
        container.attr('data-counter', count);
        container.attr('href', '#');
        container.addClass(this.options.loadingClass);
        
        this.imageToLoadQueue.push(container);
    };

    /**
     *    Removes key blocking.
     */
    Eventgallery.JSGallery2.prototype.unBlockKeys = function () {
        this.blockKeys = false;
    };

    /**
     *    Selects a certain image. (You have to pass the outer container of the image)
     *    @param {HTMLelement} container
     */
    Eventgallery.JSGallery2.prototype.select = function (container, forceReload) {
        forceReload = typeof forceReload !== 'undefined' ? forceReload : false;

        if (this.blockKeys || container === null) {
            return false;
        }


        this.blockKeys = true;
        if ( this.selectedContainer !== undefined ) {
            //this prevents an ugly effect if you click on the currently selected item
            if (container == this.selectedContainer && !forceReload) {
                this.unBlockKeys();
                return false;
            }
            this.deselect(this.selectedContainer);
        }

        // handle URL
        document.location.hash = '#' + this.thumbs.index(container);

        //if target image is not on current page, we have to go there first
        var targetPage = Math.floor((jQuery(container).data('counter') - this.imagesPerFirstPage) / this.imagesPerPage) + 1;

        if (this.currentPageNumber != targetPage) {
            this.gotoPage(targetPage, container);
        }
        this.selectedContainer = container;

        jQuery(container).addClass(this.options.activeClass);

        //first link in the container
        var source = jQuery(container).children().first();


        // prepare the add2cart button
        if (this.options.showCartButton) {
            this.add2cartLink.attr('data-id', source.data('id'));
            jQuery('.eventgallery-add2cart').attr('data-id', source.data('id'));
        }

        if (this.options.showCartConnector) {
            this.cartConnectorLink.attr('data-folder', source.data('folder'));
            this.cartConnectorLink.attr('data-file', source.data('file'));
            this.cartConnectorLink.attr('href', decodeURIComponent(source.data('cart-connector-link')));
        }

        if (this.options.showSocialMediaButton) {
            this.socialmediabutton.attr('data-link', decodeURIComponent(source.data('social-sharing-link')) );
        }

        jQuery(document).trigger('updatecartlinks');

        // now lets set the image
        this.setImage(source.attr('rel'), source.attr('longdesc'), source.data('description'), source.data('title'));
    };

    /**
     * Preloads one big image
     */
    Eventgallery.JSGallery2.prototype.loadNextImage = function () {
    	if (this.running === true) {
    		return;
    	}

    	this.running = true;

		var currentImage = this.imageToLoadQueue.shift();
        var thumbContainer = jQuery(currentImage).children().first();

        var imageToLoad = thumbContainer.attr('rel');	
	    var img = jQuery('<img id="tempImg"/>').appendTo('body').css('display','none');
        img.load( jQuery.proxy(this.imageLoaded, this, currentImage) ) ;
        img.attr('src', imageToLoad);
        
    };

    /**
     * Callback after an image has been successfully preloaded.
     * Removes the loading effects from the border div.
     * @param {HTMLElement} thumbContainer the thumb wrapper div
     */
    Eventgallery.JSGallery2.prototype.imageLoaded = function (thumbContainer) {
    	if (this.running === true) {
        	this.running = false;    		
	
	        jQuery('#tempImg').remove();
	        //remove loading styles
	        thumbContainer.removeClass(this.options.loadingClass);
	        if (this.imageToLoadQueue.length >0 ) {
	            this.loadNextImage();
	        }
        }
    };

    /**
     * Selects an image by its thumbnail index.
     * @param {integer} index of the thumbnail, starting with 0
     */
    Eventgallery.JSGallery2.prototype.selectByIndex = function (index) {
        //this.mouseLeaveHandler();
        if (index < 0 || this.thumbs.length <= index) {
            index = 0;
        }
        this.select(this.thumbs[index]);
    };

    /**
     *    Opposite to method above.
     *    @param {HTMLelement} container
     */
    Eventgallery.JSGallery2.prototype.deselect = function (container) {
        jQuery(container).removeClass(this.options.activeClass);
    };

    /**
     *    Changes the full size image to given one.
     *    @param {String} newSrc, new target of the full size image
     *    @param {String} newText, new text for the info element
     */
    Eventgallery.JSGallery2.prototype.setImage = function (newSrc, newFullSizeImage, newText, newTitle) {

        this.bigImage.parent().fadeOut(150, jQuery.proxy(function() {

            this.bigImage.attr('src', newSrc);
            var title =  decodeURIComponent(newTitle);
            this.zoomLink.attr('data-title', title);
            this.zoomLink.attr('rel', 'galleryajax');
            this.zoomLink.attr('href', newFullSizeImage);

			jQuery("a[data-eventgallery-lightbox='gallery']").eventgallery_colorbox({photo: true, maxWidth: '100%', maxHeight: '100%'});
			if (jQuery.eventgallery_colorbox.isOpen() === true) {
				jQuery.eventgallery_colorbox.reload();
			}

            jQuery(this.options.titleTarget).html(decodeURIComponent(newText));


            this.bigImage.parent().fadeIn(350, jQuery.proxy(function(){
                this.unBlockKeys();
            }, this));
        }, this));

    };

    /**
     *    Navigates to previous page.
     */
    Eventgallery.JSGallery2.prototype.prevPage = function () {
        this.gotoPage(this.currentPageNumber - 1);
    };
    /**
     *    Navigates to next page.
     */
    Eventgallery.JSGallery2.prototype.nextPage = function () {
        this.gotoPage(this.currentPageNumber + 1);
    };
    /**
     *    Selects the previous image.
     */
    Eventgallery.JSGallery2.prototype.prevImage = function (e) {
        if (e !== undefined) {
            e.preventDefault();
        }
        this.selectByIndex(this.thumbs.index(this.selectedContainer) - 1);
    };
    /**
     *    Selects the next image.
     */
    Eventgallery.JSGallery2.prototype.nextImage = function (e) {
        if (e !== undefined) {
            e.preventDefault();
        }
        this.selectByIndex(this.thumbs.index(this.selectedContainer) + 1);
    };

    /**
     * Zooms an image
     */
    Eventgallery.JSGallery2.prototype.zoomImage = function (e) {
        if (e !== undefined) {
            e.preventDefault();
        }
    };

    /**
     *    Navigates to given page and selects the first image of it.
     *    Also hides the handles (if set).
     *    @param {Integer} pageNumber, index of the target page (0-n)
     *  @param {HTMLElement} selectImage, optionally receives a particular image to select
     */
    Eventgallery.JSGallery2.prototype.gotoPage = function (pageNumber, selectImage) {
        //if we like to select another image on that page than the first one
        if (pageNumber === 0) {
            selectImage = selectImage === undefined ? this.thumbs[0] : selectImage;
        } else {
            selectImage = [selectImage, this.thumbs[(pageNumber - 1) * this.imagesPerPage + this.imagesPerFirstPage]].pick();
        }

        if (pageNumber >= 0 && pageNumber < this.lastPage) {

            this.pageContainer.css('margin-left',
            this.pageContainer.children().width() * pageNumber * -1);

            // fix height of the page-container
            var maxHeight = 0;
            this.pageContainer.children().each(function (index, page) {
                var height = jQuery(page).height();
                if (height > maxHeight) {
                    maxHeight = height;
                }
            });

            this.pageContainer.css('height', maxHeight);

            this.currentPageNumber = pageNumber;
            this.select(selectImage);
            this.updateHandles();

        }
    };

    Eventgallery.JSGallery2.prototype.updateHandles = function () {
        //update handles
        var dummy;

        if (this.options.prevHandle !== undefined) {
            dummy = this.currentPageNumber === 0 ? this.options.prevHandle.fadeOut():this.options.prevHandle.fadeIn();
        }
        if (this.options.nextHandle !== undefined) {
            dummy = this.currentPageNumber == this.lastPage - 1 ? this.options.nextHandle.fadeOut():this.options.nextHandle.fadeIn();
        }

        if (this.options.countHandle !== undefined) {
            dummy = this.updatePagingBar(this.options.countHandle, this.currentPageNumber, this.lastPage);
        }

    };

    Eventgallery.JSGallery2.prototype.updatePagingBar = function (countHandle, currentPage, pageCount) {
		var i;

        //init the pagingbar
        if (pageCount > 1 && countHandle.html() === '') {

            for (i = 0; i < pageCount; i++) {
                this.createCountLink(this, countHandle, i);
            }
        }

        var pageSpeed = this.options.pageSpeed;


        if (pageCount > 9) {

            for (i = 0; i < pageCount; i++) {
                jQuery('#count' + i).css('display', 'inline');
            }

            var skipFromRight = pageCount;
            var skipFromLeft = 0;

            var spaceToRight = pageCount - currentPage - 1;
            var spaceToLeft = currentPage;

            if (spaceToLeft > 4 && spaceToRight > 4) {
                skipFromLeft = currentPage - 4;
                skipFromRight = currentPage + 5;
            } else {
                if (spaceToLeft <= 4) {
                    skipFromLeft = 0;
                    skipFromRight = currentPage + 5 + (4 - spaceToLeft);
                }
                if (spaceToRight <= 4) {
                    skipFromLeft = currentPage - 4 - (4 - spaceToRight);
                    skipFromRight = pageCount;
                }
            }

            for (i = 0; i < skipFromLeft; i++) {
                jQuery('#count' + i).css('display', 'none');
            }

            for (i = skipFromRight; i < pageCount; i++) {
                jQuery('#count' + i).css('display', 'none');

            }
        }

        jQuery(countHandle).children('.active').removeClass('active');
        jQuery('#count' + currentPage).addClass('active');

    };


    Eventgallery.JSGallery2.prototype.createCountLink = function (gallery, countHandle, currentPageNumber) {

        var myAnchor = jQuery('<a href="#">' + (currentPageNumber + 1) + '</a>');


        myAnchor.click(jQuery.proxy(function (e) {
            this.gotoPage(currentPageNumber);
            return false;
        }, gallery));


        var myListItem = jQuery('<li class="count" id="count' + currentPageNumber + '"></li>');

        myListItem.append(myAnchor);

        countHandle.append(myListItem);

    };

})(Eventgallery, Eventgallery.jQuery);