<?php 

$link = JRoute::_("index.php?option=com_eventgallery&view=event&folder=".$this->entry->getFolderName()."&Itemid=".$this->currentItemid);

?>

<!--suppress HtmlUnknownTarget -->
<div class="eventgallery-tile">
	<div class="wrapper">
		<a href="<?php echo $link ?>">
			<div class="event-thumbnails">
				<?php
		            $files = $this->eventModel->getEntries($this->entry->getFolderName(), -1, 1, 1);
				?>
				
				<?php
		            /**
		            * @var EventgalleryLibraryFile $file
		            */?>

					<div class="event-thumbnail">
						<?php IF (($this->params->get('hide_mainimage_for_password_protected_event', 0) == '1' && !$this->entry->isAccessible()) ||
                                  ($this->params->get('hide_mainimage_for_usergroup_protected_event', 0) == '1' && !$this->entry->isVisible()) ): ?>
							<img class="locked-event" data-width="1000" data-height="1000" src="<?php echo JURI::root(true)?>/media/com_eventgallery/frontend/images/locked.png">
						<?php ELSE: ?>
							<?php if (isset($files[0])) echo $files[0]->getLazyThumbImgTag(50,50, "", false); ?>	
						<?php ENDIF; ?>
					</div>											
			</div>
			<div class="content">				
				<div class="data">
					<?php IF($this->params->get('show_date',1)==1):?><div class="date"><small class="muted"><?php echo JHTML::Date($this->entry->getDate());?></small></div><?php ENDIF ?>
					<div class="title"><h2><?php echo $this->entry->getDisplayName();?></h2></div>
					<?php IF($this->params->get('show_text',1)==1):?><div class="text"><?php echo JHtml::_('content.prepare', $this->entry->getIntroText(), '', 'com_eventgallery.events'); ?></div><?php ENDIF ?>
					<?php IF($this->params->get('show_imagecount',1)==1 || $this->params->get('show_eventhits',0)==1 ): ?><hr /><?php ENDIF ?>
					<?php IF($this->params->get('show_imagecount',1)==1):?><div class="imagecount"><small class="muted"><?php echo JText::_('COM_EVENTGALLERY_EVENTS_LABEL_IMAGECOUNT') ?> <?php echo $this->entry->getFileCount();?></small></div><?php ENDIF ?>
					<?php IF($this->params->get('show_eventhits',0)==1):?><div class="eventhits"><small class="muted"><?php echo JText::_('COM_EVENTGALLERY_EVENTS_LABEL_HITS') ?> <?php echo $this->entry->getHits();?></small></div><?php ENDIF ?>				
					<?php IF ($this->entry->isCommentingAllowed() && $this->params->get('use_comments')==1 && $this->params->get('show_commentcount',1)==1):?><div class="comment"><small class="muted"><?php echo JText::_('COM_EVENTGALLERY_EVENTS_LABEL_COMMENTCOUNT') ?> <?php echo $this->entry->getCommentCount();?></small></div><?php ENDIF ?>
					<div style="clear:both"></div>
				</div>

			</div>					
		</a>
	</div>	
</div>
