(function(jQuery){
    "use strict";
    jQuery( document ).ready(function() {

        /**
         * This code is used for the Picasa Folder Type to parse an URL.
         */
        jQuery('#foldertype-1-urlhelper').click(function(e){
            e.preventDefault();

            var url,
                user = null,
                album = null,
                picasakey = null,
                matchResult;

            url = prompt("Picasa or Google plus URL");

            // Examples: https://picasaweb.google.com/103855497268910100628/Maximilian4Jahr?noredirect=1
            // url = "https://picasaweb.google.com/103855497268910100628/Maximilian4Jahr?noredirect=1&authkey=asd&f00=bar";
            // url = "https://plus.google.com/photos/103855497268910100628/albums/5702958212867528369?authkey=CPr93JLu-quwuAE"

            url = url.replace(/https?:\/\//gi, '');

            console.log(url);

            matchResult = url.match(/picasaweb\.google\.com\/(\w+)\/(\w+)/);
            if (matchResult !== null) {
                console.log(matchResult);
                console.log('Picasa Album found');
                user = matchResult[1];
                album = matchResult[2];
                picasakey = '';

                if (url.indexOf('authkey=')>0) {
                    picasakey = url.match(/authkey=([^&]+)/)[1];
                }

                console.log(user, album, picasakey);
            }

            matchResult = url.match(/plus\.google\.com\/photos\/(\w+)\/albums\/(\w+)/);
            if (matchResult !== null) {
                console.log(matchResult);
                console.log('Google Plus Album found');
                user = matchResult[1];
                album = matchResult[2];
                picasakey = '';

                if (url.indexOf('authkey=')>0) {
                    picasakey = 'Gv1sRg' + url.match(/authkey=([^&]+)/)[1];
                }

                console.log(user, album, picasakey);
            }

            if (user !== null && album !== null && picasakey !== null) {
                jQuery('#foldertype-1-user').val(user).trigger('onchange');
                jQuery('#foldertype-1-album').val(album).trigger('onchange');
                jQuery('#foldertype-1-picasakey').val(picasakey).trigger('onchange');
            } else {
                alert('URL format not supported');
            }
        });

        /***********
         * File Ajax edit feature
         *
         * BEGIN
         */
        jQuery(document).off('click', '.filecontent').on('click', '.filecontent', function(e) {
            var currentContainer = jQuery(e.target).closest('div[data-id]');

            e.preventDefault();

            currentContainer.html('Loading.. ');

            jQuery.ajax({
                url: currentContainer.data('editlink')
            }).done(function(data ) {
                currentContainer.html(data);
                console.log('loaded');
            });
        });

        jQuery(document).off('click', '.saveFileContent').on('click', '.saveFileContent', function(e) {
            var fileId = jQuery(e.target).data('id'),
                $form = jQuery(e.target).closest('form'),
                currentContainer = jQuery('div[data-id="' + fileId + '"]'),
                url = $form.attr('action'),
                task = jQuery(e.target).data('task');

            e.preventDefault();
            e.stopPropagation();
            $form.find('input[name="task"]').attr('value', task);

            currentContainer.html('Loading.. ');

            jQuery.post( url, $form.serialize() ).done(function(data ) {
                currentContainer.html(data);
            });
            console.log('saved');
        });

        jQuery(document).off('click', '.closeFileContent').on('click', '.closeFileContent', function(e) {

            var fileId = jQuery(e.target).data('id'),
                currentContainer = jQuery('div[data-id="' + fileId + '"]'),
                url = jQuery(e.target).data('href');

            e.preventDefault();
            e.stopPropagation();

            console.log('try to close now');
            currentContainer.html('Loading.. ');

            jQuery.ajax({
                url: url
            }).done(function(data ) {
                currentContainer.html(data);
                console.log('closed');
            });
        });

        /***********
         * File Ajax edit feature
         *
         * END
         */

    }); //end domready
})(eventgallery.jQuery);