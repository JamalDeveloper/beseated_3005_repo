<?php

/**
 * @package     Sven.Bluege
 * @subpackage  com_eventgallery
 *
 * @copyright   Copyright (C) 2005 - 2013 Sven Bluege All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>
<section class="page-section page-event-gallery">
	<div class="container">
			<div class="row">
				<?php echo $this->loadSnippet('event/tiles'); ?>
			</div>
	</div>
</section>
<script>
	$(".eventgallery-tile").removeClass("eventgallery-tile").addClass( "col-md-4 event-gallery-item" );
	$(".thumbnail-container").removeClass("thumbnail-container");
</script>
<?php 
