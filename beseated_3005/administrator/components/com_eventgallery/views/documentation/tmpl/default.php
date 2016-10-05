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

<?php if (!empty( $this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
<?php else : ?>
    <div id="j-main-container">
<?php endif;?>
        <div id="documentation">
            <h3>Documentation and some tutorial videos are available here:</h3>
            <p><a href="http://www.svenbluege.de/joomla-event-gallery/event-gallery-manual">www.svenbluege.de/joomla-event-gallery/event-gallery-manual</a></p>
        </div>      
    </div>