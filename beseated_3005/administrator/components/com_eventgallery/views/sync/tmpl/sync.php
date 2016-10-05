<?php

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
?>

<p>
    <?php echo JText::_('COM_EVENTGALLERY_SYNC_START2_DESC'); ?>
</p>

<form class="form-horizontal" name="items">

    <div class="control-group">
        <div class="controls2">    
            <div class="btn-group">
                <button class="btn checkall"><?php echo JText::_('COM_EVENTGALLERY_SYNC_CHECK_ALL');?></button>
                <button class="btn uncheckall" ><?php echo JText::_('COM_EVENTGALLERY_SYNC_CHECK_NONE');?></button>
                <button class="btn btn-danger start"><?php echo JText::_('COM_EVENTGALLERY_SYNC_START');?></button>
            </div>
        </div>
    </div>
    <progress id="syncprogress" value="0" max="100"></progress>

    <div class="control-group">
        <div class="controls2">
            <?php FOREACH ($this->folders as $foldername):?> 
                <label class="checkbox folder">
                    <input type="checkbox" name="images" checked="checked" value="<?php echo htmlentities($foldername, ENT_QUOTES, "UTF-8");  ?>"> <?php echo $foldername;  ?>
                    <div class="status"></div>
                </label>
            <?php ENDFOREACH; ?>
        </div>
    </div>

    <div class="control-group">
        <div class="controls2">
            <button class="btn btn-danger start"><?php echo JText::_('COM_EVENTGALLERY_SYNC_START');?></button>
        </div>
    </div>

</form>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
    <input type="hidden" name="option" value="com_eventgallery" />
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>


<style type="text/css">
    #syncprogress {
        display:none; 
        height: 20px;
        margin: 20px 0;
        width: 100%;
    }

    .eventgallery-folder {
        float: left;
        margin: 10px;
        padding: 10px;
        border: 1px solid #DDD;
        -webkit-box-shadow: 1px 1px 1px rgba(50, 50, 50, 0.75);
        -moz-box-shadow:    1px 1px 1px rgba(50, 50, 50, 0.75);
        box-shadow:         1px 1px 1px rgba(50, 50, 50, 0.75);

        box-sizing:border-box;
        -moz-box-sizing:border-box; /* Firefox */
    }

    .done {
        -webkit-box-shadow: 0px 0px 0px rgba(50, 50, 50, 0.75);
        -moz-box-shadow:    0px 0px 0px rgba(50, 50, 50, 0.75);
        box-shadow:         0px 0px 0px rgba(50, 50, 50, 0.75);
    }

    .sync {
        background-color: darkseagreen;
    }

    .no-sync {
        background-color: lightblue;
    }

    .deleted {
        background-color: lightsalmon;
    }
</style>

<script type="text/javascript">

(function(jQuery) {

    function checkAll(formname, checktoggle)
    {
        var checkboxes = new Array();
        checkboxes = document.forms[formname].getElementsByTagName('input');

        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].type === 'checkbox') {
                checkboxes[i].checked = checktoggle;
            }
        }

        return true;
    }

    var folderContainers  = new Array();
    var max = 0;

    function syncFolder() {

        updateProcess();

        if (folderContainers.length==0) {
            done();
            return;
        }

        var myElement = jQuery(folderContainers.pop());
        jQuery(myElement.children(".status")[0]).text('loading...');                

        var jqxhr = jQuery.ajax( '<?php echo JRoute::_('index.php?option=com_eventgallery&format=raw&task=sync.process&'.JSession::getFormToken().'=1', false);?>' ,
            {
                data : 'folder=' + myElement.children('input')[0].value,
                dataType  : 'json'
            })
            .done(function(data, textStatus, jqXHR) {
                var text = "";
                var cssClass = "";
                var responseJSON = jqXHR.responseJSON;

                myElement.addClass('done');
                jQuery(myElement.children(".status")[0]).text('');

                if (responseJSON.status == 'sync') {
                    text = responseJSON.folder + " synced";
                    cssClass = "sync";
                }

                if (responseJSON.status == 'deleted') {
                    text = responseJSON.folder + " deleted";
                    cssClass = "deleted";
                }

                if (responseJSON.status == 'nosync') {
                    text = responseJSON.folder + " not synced";
                    cssClass = "no-sync";
                }
                myElement.addClass(cssClass);
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                myElement.addClass('failed');
                jQuery(myElement.children(".status")[0]).text('Sorry, your request failed :('+jqXHR.status+')');
                console.log(jqXHR, textStatus, errorThrown);
            })
            .always(function() {
                syncFolder();
            });

    }

    function start() {

        var syncProgressElement = document.getElementById('syncprogress');

        jQuery('form#items button').attr('disabled', 'disabled');

        jQuery(".folder").each(function(index, item){
            if (jQuery(this).children('input')[0].checked) {
                folderContainers.push(this);
            }
        });

        max = folderContainers.length;

        syncProgressElement.setAttribute('max', max);
        syncProgressElement.setAttribute('value', 0);
        syncProgressElement.style.display = 'block';

        syncFolder();
    }

    function updateProcess() {

        document.getElementById('syncprogress').setAttribute('value', max-folderContainers.length);
    }

    function done() {
        jQuery('form#items button').removeAttr('disabled');
        alert('Done.');
    }

    jQuery( document ).on( "click", "button.checkall", function(e) {
        e.preventDefault();
        checkAll('items', true);
    });

    jQuery( document ).on( "click", "button.uncheckall", function(e) {
        e.preventDefault();
        checkAll('items', false);
    });

    jQuery( document ).on( "click", "button.start", function(e) {
        e.preventDefault();
        start();
    });

})(eventgallery.jQuery);


</script>