(function(jQuery){

jQuery(document).ready(function() {
    var resizeTimer,
        fadeNavigationTimer,
        navElementSelector = '#eventgallery_cboxTitle, #eventgallery_cboxContent>button',
        isMobile = navigator.userAgent.match( /(iPad)|(iPhone)|(iPod)|(Android)|(PlayBook)|(BB10)|(BlackBerry)|(Opera Mini)|(IEMobile)|(webOS)|(MeeGo)/i ),
        isTouch = isMobile !== null || document.createTouch !== undefined || ( 'ontouchstart' in window ) || ( 'onmsgesturechange' in window ) || navigator.msMaxTouchPoints;

	//legacy call to old lightbox 
	jQuery("a[rel^='lightbo2']").eventgallery_colorbox({maxWidth:"100%", maxHeight:"100%"});

	jQuery("a[data-eventgallery-lightbox='gallery']").eventgallery_colorbox({
        slideshow: EventGallerySlideShowConfiguration.slideshow,
        slideshowAuto: EventGallerySlideShowConfiguration.slideshowAuto,
        slideshowSpeed: EventGallerySlideShowConfiguration.slideshowSpeed,
        slideshowStart: EventGallerySlideShowConfiguration.slideshowStart,
        slideshowStop: EventGallerySlideShowConfiguration.slideshowStop,
        className: 'eventgallery_colorbox',
        photo: true,
        maxWidth: '100%',
        maxHeight: '100%',
        opacity: 0.9
    });


	jQuery("a[data-eventgallery-lightbox='cart']").eventgallery_colorbox({photo: true, maxWidth: '90%', maxHeight: '90%', rel: 'cart'});
	jQuery("button[data-eventgallery-lightbox='content']").eventgallery_colorbox({inline:true, maxWidth: '80%'});
	jQuery("a[data-eventgallery-lightbox='content']").eventgallery_colorbox({inline:true, maxWidth: '80%'});

    jQuery('.singleimage-zoom').click(function(e){
        e.preventDefault();
        jQuery('#bigimagelink').click();
    });

    /**
     * Append some buttons the the lightbox.
     */
    jQuery(document).bind('eventgallery_cbox_complete', function(){
		var $cboxTitle = jQuery('#eventgallery_cboxTitle'),
			$cboxCurrent = jQuery('#eventgallery_cboxCurrent'),
            $element = jQuery.fn.eventgallery_colorbox.element(),
            $cartButton = $element.siblings('.eventgallery-add2cart'),
            $shareButton = $element.siblings('.social-share-button-open'),
            showIcons = false;

        $cboxCurrent.hide();

        if ($cartButton.is(':visible')) {
            showIcons = true;
        }

        if ($shareButton.is(':visible')) {
            showIcons = true;
        }

        if (showIcons) {
            $cboxTitle.html(jQuery('<table class="table"><tr><td class="icons"></td><td class="content">' + $cboxTitle.html() + '</td><td class="current">' + $cboxCurrent.html() + '</td></tr></table>').html());
            $cboxTitle.find('.icons').append($cartButton.clone());
            $cboxTitle.find('.icons').append($shareButton.clone());
        } else {
            $cboxTitle.html(jQuery('<table class="table"><tr><td class="icons"></td><td class="content">' + $cboxTitle.html() + '</td><td class="current">' + $cboxCurrent.html() + '</td></tr></table>').html());
        }

	});

    /**
     * Adds support touch navigation
     * Adds support for history.back() to close the lightbox.
     * Adds support for navigation hiding.
     */
    jQuery(document).bind('eventgallery_cbox_open', function(){
        Eventgallery.Touch.addTouch('body',
            jQuery.eventgallery_colorbox.prev,
            jQuery.eventgallery_colorbox.next
        );

        // handle the browser back button
        if (window.history && history.pushState) {
            jQuery(window).on("popstate.eventgallery_colorbox", function(e) {
                e.preventDefault();
                if (window.location.href.indexOf('lightbox=1') > -1) {
                    return;
                }
                jQuery.eventgallery_colorbox.close();
            });
            history.pushState('', '', Eventgallery.Tools.addUrlParameter(window.location.href, 'lightbox', '1'));
        }


        if (!isMobile) {
            jQuery('#eventgallery_colorbox').on('mouseenter.eventgallery_colorbox', navElementSelector, function (e) {
                e.stopPropagation();
                showNavigation();
                clearHideNavigationTimeout();
            });

            jQuery('#eventgallery_colorbox').on('mouseenter.eventgallery_colorbox', function () {
                showNavigation();
                setHideNavigationTimeout();
            });

            jQuery('#eventgallery_colorbox').on('mouseleave.eventgallery_colorbox', navElementSelector, function () {
                setHideNavigationTimeout();
            });


            setHideNavigationTimeout();
        }

    });

    function setHideNavigationTimeout() {
        var delay = EventGalleryLightboxConfiguration.navigationFadeDelay;

        clearHideNavigationTimeout();
        if (delay > 0) {
            fadeNavigationTimer = setTimeout(function () {
                hideNavigation();
            }, delay);
        }
    }

    function clearHideNavigationTimeout() {
        if (fadeNavigationTimer) {
            clearTimeout(fadeNavigationTimer);
        }
    }

    function hideNavigation() {
        jQuery(navElementSelector).animate({'opacity': 0});
        jQuery(navElementSelector).removeClass('eventgallery-colorbox-nav-visible');
    }

    function showNavigation() {
        jQuery(navElementSelector).animate({'opacity': 1});
        jQuery(navElementSelector).addClass('eventgallery-colorbox-nav-visible');
    }

    jQuery(document).bind('eventgallery_cbox_closed', function(){
        Eventgallery.Touch.removeTouch('body');
        // handle the browser back button
        if (window.history && history.pushState) {
            jQuery(window).off("popstate.eventgallery_colorbox");
            history.replaceState('', '', Eventgallery.Tools.removeUrlParameter(window.location.href, 'lightbox'));
        }

        jQuery('#eventgallery_colorbox').off('mouseenter.eventgallery_colorbox mouseleave.eventgallery_colorbox');
        clearHideNavigationTimeout();

    });

    /**
     * resize the colorbox if the window size changes. We need this to support
     * the mobile device rotation
     */

    // the following code is from https://github.com/jackmoore/colorbox/issues/158#issuecomment-33762614
    // I commented out the addEventListener function to orientationchange
    jQuery(window).resize(function(){
        // Resize Colorbox when resizing window or changing mobile device orientation
        resizeColorBox();
        //window.addEventListener("orientationchange", resizeColorBox, false);
    });


    function resizeColorBox() {
        if (resizeTimer) {
            clearTimeout(resizeTimer);
        }
        resizeTimer = setTimeout(function() {
            if (jQuery('#eventgallery_cboxOverlay').is(':visible')) {
                jQuery.eventgallery_colorbox.reload();
            }
        }, 300);
    }

    /**
     * add the click protection
     */
    if (EventGallerySlideShowConfiguration.slideshowRightClickProtection === true)
    {
        jQuery( document ).on( "contextmenu", "img.eventgallery_cboxPhoto", function(e) {
            e.preventDefault();
            return false;
        });
    }



});

})(Eventgallery.jQuery, Eventgallery);