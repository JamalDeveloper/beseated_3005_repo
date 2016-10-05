(function(Eventgallery, jQuery){
/**
 * Touch navigation
 */

Eventgallery.Touch = function(newOptions) {

    this.options = {
    };
    this.options = Eventgallery.Tools.mergeObjects(this.options, newOptions);
};

Eventgallery.Touch.removeTouch = function (elementSelector) {
    jQuery( elementSelector ).unbind( 'touchstart');
    jQuery( elementSelector ).unbind( 'touchmove');
    jQuery( elementSelector ).unbind( 'touchend');
};

Eventgallery.Touch.addTouch = function (elementSelector, leftAction, rightAction, tabAction, allowScrolling) {

    var index,
        hDistance,
        vDistance,
        hDistanceLast,
        vDistanceLast,
        hDistancePercent,
        vSwipe = false,
        hSwipe = false,
        hSwipMinDistance = 10,
        vSwipMinDistance = 50,
        startCoords = {},
        endCoords = {},
        winWidth = window.innerWidth ? window.innerWidth : $( window ).width(),
        winHeight = window.innerHeight ? window.innerHeight : $( window ).height();

    jQuery( elementSelector ).bind( 'touchstart', function( event ) {

        jQuery( this ).addClass( 'touching' );

        endCoords = event.originalEvent.targetTouches[0];
        startCoords.pageX = event.originalEvent.targetTouches[0].pageX;
        startCoords.pageY = event.originalEvent.targetTouches[0].pageY;



        jQuery( '.touching' ).bind( 'touchmove',function( event ) {
            if (allowScrolling !== true) {
                event.preventDefault();
                event.stopPropagation();
            }
            endCoords = event.originalEvent.targetTouches[0];

            if ( ! hSwipe ) {
                vDistanceLast = vDistance;
                vDistance = endCoords.pageY - startCoords.pageY;
                if ( Math.abs( vDistance ) >= vSwipMinDistance || vSwipe ) {
                    vSwipe = true;
                }
            }

            hDistanceLast = hDistance;
            hDistance = endCoords.pageX - startCoords.pageX;
            hDistancePercent = hDistance * 100 / winWidth;

            if ( ! hSwipe && ! vSwipe && Math.abs( hDistance ) >= hSwipMinDistance ) {
                hSwipe = true;
            }

        } );

        return allowScrolling===true;

    } ).bind( 'touchend',function( event ) {

        event.preventDefault();
        event.stopPropagation();

        vDistance = endCoords.pageY - startCoords.pageY;
        hDistance = endCoords.pageX - startCoords.pageX;
        hDistancePercent = hDistance*100/winWidth;

        // Swipe to bottom to close
        if ( vSwipe ) {
            vSwipe = false;
            if ( Math.abs( vDistance ) >= 2 * vSwipMinDistance && Math.abs( vDistance ) > Math.abs( vDistanceLast ) ) {
            } else {

            }

        } else if ( hSwipe ) {

            hSwipe = false;

            // swipeLeft
            if( hDistance >= hSwipMinDistance && hDistance >= hDistanceLast) {
                leftAction();

                // swipeRight
            } else if ( hDistance <= -hSwipMinDistance && hDistance <= hDistanceLast) {
                rightAction();
            }

        } else { // Top and bottom bars have been removed on touchable devices
            if (undefined !== tabAction)
            {
                tabAction();
            } else {
                jQuery(event.target).trigger("click");
            }
        }


        jQuery( '.touching' ).off( 'touchmove' ).removeClass( 'touching' );

    } );

};

})(Eventgallery, Eventgallery.jQuery);
