<?php
/**
 * Visforms default view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

//no direct access
 defined('_JEXEC') or die('Restricted access');
 
 JHtml::_('bootstrap.tooltip');
 JHtml::_('behavior.multiselect');
 JHtml::_('formbehavior.chosen', 'select');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));

$user		= JFactory::getUser();
$userId		= $user->get('id');
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_visforms');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
<?php if (isset($this->update_message)) {echo $this->update_message;} ?>
<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
	<div class="clr"> </div>

	<table class="table table-striped" id="articleList">
	<thead>
		<tr>
			<th width="3%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>			
			<th width="40%" class="nowrap center">
                <?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_TITLE', 'a.title', $listDirn, $listOrder); ?>
			</th>
            <th width="5%" class="nowrap center">
                <?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_PUBLISHED', 'a.published', $listDirn, $listOrder); ?>	
            </th>
			<th width="10%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
			</th>
			<th width="5%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_FIELDS', 'nbfields', $listDirn, $listOrder); ?>
			</th>
			<th width="5%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_AUTHOR', 'username', $listDirn, $listOrder); ?>
			</th>
			<th width="10%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_DATE', 'a.created', $listDirn, $listOrder); ?>
			</th>
			<th width="15%" class="nowrap center">
				<?php echo JText::_( 'COM_VISFORMS_DATA' ); ?>
			</th>
			<th width="5%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_HITS', 'a.hits', $listDirn, $listOrder); ?>
			</th>
			<th width="5%" class="nowrap center hidden-phone">
					<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
				</th>
			<th width="3%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_ID', 'a.id', $listDirn, $listOrder); ?>
			</th>
		</tr>			
	</thead>
	<?php foreach ($this->items as $i => $row) :
        $row->max_ordering = 0; //??
        $ordering   = ($listOrder == 'a.title');
		$row = &$this->items[$i];
		$checked 	= JHtml::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=com_visforms&task=visform.edit&id='. $row->id );
		$fields		= JRoute::_( 'index.php?option=com_visforms&view=visfields&fid='. $row->id );
		$savedData 	= JRoute::_( 'index.php?option=com_visforms&view=visdatas&fid='. $row->id );
		$canCreate	= $user->authorise('core.create',		'com_visforms.visform.'.$row->id);
		$canEdit	= $user->authorise('core.edit',			'com_visforms.visform.'.$row->id);
		$canCheckin	= $user->authorise('core.manage',		'com_visforms') || $row->checked_out == $userId || $row->checked_out == 0;
		$canEditOwn	= $user->authorise('core.edit.own',		'com_visforms.visform.'.$row->id) && $row->created_by == $userId;
		$canChange	= $user->authorise('core.edit.state',	'com_visforms.visform.'.$row->id) && $canCheckin;
		$published =  JHtml::_('jgrid.published', $row->published, $i, 'visforms.', $canChange, 'cb');
		
		?>
        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $row->id; ?>">
			<td class="center hidden-phone">
				<?php echo $checked; ?>
			</td>
			<td class="has-context">
                <div class="pull-left">
                    <?php if ($row->checked_out) : ?>
                            <?php echo JHtml::_('jgrid.checkedout', $i, $user->name, $row->checked_out_time, 'visforms.', $canCheckin); ?>
                    <?php endif; ?>
                    <?php if ($canEdit || $canEditOwn) : ?>
                    <a href="<?php echo $link; ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>"><?php echo $this->escape($row->title); ?></a>
                    <p class="small">
                        <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->name));?>
                    </p>
                    <?php else : ?>
                    <?php echo $this->escape($row->title); ?>
                    <p class="small">
                        <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->name));?>
                    </p>
                    <?php endif; ?>
                </div>
			</td>
            <td class="center">
                <?php echo $published;?>
            </td>
			<td class="small center hidden-phone">
					<?php echo $this->escape($row->access_level); ?>
				</td>
            <td class="center hidden-phone">
				<?php if ($canEdit || $canEditOwn) : ?>
					<a href="<?php echo $fields; ?>"><?php echo $row->nbfields; ?></a>
				<?php else : ?>
					<?php echo $row->nbfields; ?>
				<?php endif; ?>

            </td>
			<td class="small center hidden-phone">
                <a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $row->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
								<?php echo $this->escape($row->username); ?></a>
			</td>
			<td class="nowrap small center hidden-phone">
				<?php echo JHtml::_('date', $row->created, JText::_('DATE_FORMAT_LC4')); ?>
			</td>
			<td class="center">
            <?php 
				if ($canEdit || $canEditOwn) :
					if ($row->saveresult == '1')
					{
				?>
					<a href="<?php echo $savedData; ?>"><?php echo JText::_( 'COM_VISFORMS_DISPLAY_DATA' ); ?></a>
				<?php 
					} else {
				?>
					&nbsp;
				<?php 
					}
				?>
				<?php else : ?>
					&nbsp;
				<?php endif; ?>
			</td>			
           	<td class="center">
				<?php echo $row->hits; ?>
			</td>
			<td class="small center hidden-phone">
					<?php if ($row->language=='*'):?>
						<?php echo JText::alt('JALL', 'language'); ?>
					<?php else:?>
						<?php echo $row->language_title ? $this->escape($row->language_title) : JText::_('JUNDEFINED'); ?>
					<?php endif;?>
				</td>
           	<td class="hidden-phone">
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php endforeach; 
	?>
    
    
    <tfoot>
    <tr>
      <td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
    </tr>
  	</tfoot>
    
	</table>
	<?php //Load the batch processing form. ?>
	<?php
	if ($this->canDo->get('core.create')) {
		echo $this->loadTemplate('batch'); 
	}	
	?>
<div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</div>
    </div>
</form>

 <?php JHtml::_('visforms.creditsBackend'); ?>