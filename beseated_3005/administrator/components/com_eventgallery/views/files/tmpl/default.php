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

<?php
	/**
	* adjust the image path
	*/
	$_image_script_path = 'components/com_eventgallery/helpers/image.php';
	$params = JComponentHelper::getParams('com_eventgallery');

	if ($params->get('use_legacy_image_rendering','0')=='1') {
		$_image_script_path = "index.php";
	}


	// Load the modal behavior script.
	JHtml::_('behavior.modal', 'a.modal');
?>


<form method="POST" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		</div>
		<div class="clearfix"> </div>

		<input type="hidden" name="option" value="com_eventgallery" />
		<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>

		<input type="hidden" name="option" value="com_eventgallery" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="folderid" value="<?php echo $this->item->id; ?>" />

		
		<table class="table table-striped adminlist">
		<thead>
			<tr>
				
				<th width="20">			
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>			
				<th width="110">
					<?php echo JText::_( 'COM_EVENTGALLERY_EVENTS_FILENAME' ); ?>
				</th>
				<th width="130">
					<?php echo JText::_( 'COM_EVENTGALLERY_EVENTS_ORDER' ); ?> 
					<?php echo JHTML::_('grid.order',  $this->items, 'filesave.png', 'files.saveorder' ); ?>
				</th>
				<th>
					<?php echo JText::_( 'COM_EVENTGALLERY_EVENTS_OPTIONS' ); ?>
				</th>
					
				<th>
					<?php echo JText::_( 'COM_EVENTGALLERY_EVENTS_DISPLAYNAME' ); ?>
				</th>
				<th>
					<?php echo JText::_( 'COM_EVENTGALLERY_EVENTS_COMMENTS' ); ?>
				</th>
				<th>
					<?php echo JText::_( 'COM_EVENTGALLERY_EVENTS_MODIFIED_BY' ); ?>
				</th>
				
			</tr>			
		</thead>
		<?php

		for ($i=0, $n=count( $this->items ); $i < $n; $i++)
		{
			$row = $this->items[$i];

            /**
             * @var EventgalleryLibraryFactoryFile $fileFactory
             * @var EventgalleryLibraryFile $file
             */

            $fileFactory = EventgalleryLibraryFactoryFile::getInstance();
            $file = $fileFactory->getFile($row->folder, $row->file);

			$editLink = JRoute::_('index.php?option=com_eventgallery&view=file&layout=edit&id='.$row->id);
			$editLinkAjax = $editLink . '&tmpl=component&format=raw';
			$checked 	= JHTML::_('grid.id',   $i, $row->id );
			// TODO: remove due to strange issues with at least on joomla installation $published =  JHTML::_('jgrid.published', $row->published, $i );

			$this->row = $row;
			$this->file = $file;
			$this->editLink = $editLink;
			$this->editLinkAjax = $editLinkAjax;
			?>

			<tr>
				<td>
					<!--<a name="<?php echo $row->id; ?>"></a>-->
					<?php echo $checked; ?>
				</td>
				<td>
					<img class="thumbnail" title="<?php echo $row->id; ?>" src="<?php echo JURI::base().("../$_image_script_path?view=resizeimage&folder=".$row->folder."&file=".$row->file."&option=com_eventgallery&width=100")?>" />
				</td>
				<td class="order">
					<div class="input-prepend">
						<span class="add-on"><?php echo $this->pagination->orderUpIcon( $i, true, 'files.orderdown', 'JLIB_HTML_MOVE_UP', true); ?></span>
						<span class="add-on"><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'files.orderup', 'JLIB_HTML_MOVE_DOWN', true ); ?></span>
						<input title="<?php echo JText::_('COM_EVENTGALLERY_COMMON_ORDERING')?>" type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="width-40 text-area-order" />
					</div>
					<div style="word-wrap: break-word; width: 120px">
						<small style="word-wrap:break-word"><?php echo $file->getFileName()?>


						<br><a href="<?php echo $this->editLink;?>"><?php echo JText::_('COM_EVENTGALLERY_EVENT_FILE_EDIT'); ?></a>
						</small>

					</div>
					
				</td>
				<td>
					<div class="btn-group">
						<a title="<?php echo JText::_( 'COM_EVENTGALLERY_EVENT_IMAGE_ACTION_PUBLISH' ); ?>"
							onClick="return listItemTask('cb<?php echo $i; ?>','<?php echo $row->published==0?"files.publish":"files.unpublish"; ?>')"
					        class="<?php echo $row->published==1? "btn btn-micro active" : "btn btn-micro";?>">
					        <i class="eg-icon-published"></i>
					    </a>

						<a title="<?php echo JText::_( 'COM_EVENTGALLERY_EVENT_IMAGE_ACTION_ALLOWCOMMENTS' ); ?>" onClick="document.location.href='<?php echo JRoute::_("index.php?option=com_eventgallery&view=files&task=".($row->allowcomments==0?"files.allowcomments":"files.disallowcomments")."&folderid=".$this->item->id."&cid[]=".$row->id."&limitstart=".JRequest::getVar('limitstart', '0', '', 'int')."#".$row->id) ?>'"
					        class="<?php echo $row->allowcomments==1? "btn btn-micro active" : "btn btn-micro";?>">
					        <i class="eg-icon-comments"></i>
					    </a>

					    <a title="<?php echo JText::_( 'COM_EVENTGALLERY_EVENT_IMAGE_ACTION_MAINIMAGE' ); ?>" onClick="document.location.href='<?php echo JRoute::_("index.php?option=com_eventgallery&view=files&task=".($row->ismainimage==0?"files.ismainimage":"files.isnotmainimage")."&folderid=".$this->item->id."&cid[]=".$row->id."&limitstart=".JRequest::getVar('limitstart', '0', '', 'int')."#".$row->id) ?>'"
					        class="<?php echo $row->ismainimage==1? "btn btn-micro active" : "btn btn-micro";?>">
					        <i class="eg-icon-mainimage"></i>
					    </a>

					    <a title="<?php echo JText::_( 'COM_EVENTGALLERY_EVENT_IMAGE_ACTION_MAINIMAGEONLY' ); ?>" onClick="document.location.href='<?php echo JRoute::_("index.php?option=com_eventgallery&view=files&task=".($row->ismainimageonly==0?"files.ismainimageonly":"files.isnotmainimageonly")."&folderid=".$this->item->id."&cid[]=".$row->id."&limitstart=".JRequest::getVar('limitstart', '0', '', 'int')."#".$row->id) ?>'"
					        class="<?php echo $row->ismainimageonly==0? "btn btn-micro active" : "btn btn-micro";?>">
					        <i class="eg-icon-mainimageonly"></i>
					    </a>


				    </div>
				</td>
				<td>
					<div class="row-fluid" data-id="<?php echo $this->file->getId(); ?>" data-editlink="<?php echo $this->editLinkAjax; ?>">
						<?php echo $this->loadTemplate('content'); ?>

					</div>
				</td>
				<td class="center">
					<a href="<?php echo JRoute::_( 'index.php?option=com_eventgallery&task=comments&filter=folder='.$row->folder) ?>">
						<?php echo $row->commentCount ?>
					</a>
				</td>
				<td>
					<small>
						<?php $user = JFactory::getUser($row->userid); echo $user->name;?>, <br> 
						<?php echo JText::_( 'COM_EVENTGALLERY_EVENT_FILE_CREATED' ); ?><?php echo JHTML::Date($row->created,JText::_('DATE_FORMAT_LC4')) ?>, <br>
						<?php echo JText::_( 'COM_EVENTGALLERY_EVENT_FILE_MODIFIED' ); ?><?php echo JHTML::Date($row->modified,JText::_('DATE_FORMAT_LC4')) ?>
					</small>
				</td>
				
			</tr>
			<?php
		}
		?>
		</table>
		<input type="hidden" name="limitstart" value="<?php echo $this->pagination->limitstart; ?>" />
		<div class="pagination pagination-toolbar">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>	
	</div>
</form>
