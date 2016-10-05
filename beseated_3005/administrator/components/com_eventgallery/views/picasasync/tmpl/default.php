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

<?php echo JText::_( 'COM_EVENTGALLERY_PICASASYNC_DESC' ); ?>

 </p>

<form id="upload" action="<?php echo JRoute::_("index.php?option=com_eventgallery&task=picasasync.sync",false); ?>" method="POST" enctype="multipart/form-data">
<fieldset>  

	<div>  
	    <label for="username"><?php echo JText::_( 'COM_EVENTGALLERY_PICASASYNC_USERNAME_LABEL' ); ?>:</label>  
	    <input type="text" id="username" name="username" />  
	    
	</div>  
	<!--<div>  
	    <label for="key"><?php echo JText::_( 'COM_EVENTGALLERY_PICASASYNC_KEY_LABEL' ); ?>:</label>  
	    <input type="text" id="key" name="key" />  	    
	</div>  -->
	<div id="submitbutton">  
	    <button type="submit"><?php echo JText::_( 'COM_EVENTGALLERY_PICASASYNC_SYNCBUTTON_LABEL' ); ?></button>  
	</div>  
</fieldset>  
</form>  

<ul id="progress" class="thumbnails"></ul>


<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">

<input type="hidden" name="option" value="com_eventgallery" />
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>

</form>
