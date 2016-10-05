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
/**
 * @var JCacheControllerCallback $cache
 */
$cache = JFactory::getCache('com_eventgallery');
?>


<section class="page-section page-event-gallery">
	<div class="container">
		<h2 class="heading-1">Events Gallery</h2>
			<div class="row">
				<?php echo  $this->loadSnippet("events/" . $this->params->get('events_layout','default') ); ?>
			</div>	
	</div>
</section>

<script>
	$(".eventgallery-tile").removeClass("eventgallery-tile").addClass( "col-md-4 event-item" );
	$(".title > h2").addClass("heading-3");
</script>