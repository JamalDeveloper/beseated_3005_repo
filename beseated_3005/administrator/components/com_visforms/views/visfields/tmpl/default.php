<?php
/**
 * Visfields default view for Visforms
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
$saveOrder	= $listOrder == 'a.ordering';
$user		= JFactory::getUser();
$userId		= $user->get('id');
$sortFields = $this->getSortFields();
$app = JFactory::getApplication();

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_visforms&task=visfields.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
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

<form action="<?php echo JRoute::_('index.php?option=com_visforms&view=visfields&fid=' . $app->input->getInt('fid', -1)); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
    <?php else : ?>
        <div id="j-main-container">
    <?php endif;?>
    <?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
	<div class="clr"> </div>

	<table class="table table-striped" id="articleList">
	<thead>
		<tr>
            <th width="1%" class="nowrap center hidden-phone">
                <?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
			</th>
			<th width="3%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('grid.checkall'); ?>
			</th>				
			<th width="40%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_LABEL', 'a.label', $listDirn, $listOrder); ?>
			</th>
            <th width="5%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_PUBLISHED', 'a.published', $listDirn, $listOrder); ?>
            </th>					
			<th width="10%" class="nowrap center">
				<?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_TYPE', 'a.typefield', $listDirn, $listOrder); ?>
			</th>
			<th width="3%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_ID', 'a.id', $listDirn, $listOrder); ?>
			</th>
		</tr>			
	</thead>
	<?php
	foreach ($this->items as $i => $row) 
	{
		$row->max_ordering = 0; //without the change between ascending and descending ordering doesn't work properly
        $fid = $app->input->getInt('fid', -1);
		$ordering	= ($listOrder == 'a.ordering');
		$checked 	= JHtml::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=com_visforms&task=visfield.edit&id='. $row->id.'&fid='.$fid);
		$canEdit	= $user->authorise('core.edit',			'com_visforms.visform.' . $fid . '.visfield.' . $row->id);
		$canCheckin	= $user->authorise('core.manage',		'com_visforms') || $row->checked_out == $userId || $row->checked_out == 0;
		$canChange	= $user->authorise('core.edit.state',			'com_visforms.visform.' . $fid . '.visfield.' . $row->id) && $canCheckin;
		$canEditOwn	= $user->authorise('core.edit.own',		'com_visforms.visform.' . $fid . '.visfield.' . $row->id) && $row->created_by == $userId;
		$published	= JHtml::_('jgrid.published', $row->published, $i, 'visfields.', $canChange  );
		?>
		<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $fid; ?>">
            <td class="order nowrap center hidden-phone">
							<?php
							$iconClass = '';
							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
							elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
							}
							?>
							<span class="sortable-handler<?php echo $iconClass ?>">
								<i class="icon-menu"></i>
							</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="width-20 text-area-order " />
							<?php endif; ?>
						</td>
			<td class="center hidden-phone">
				<?php echo $checked; ?>
			</td>
			<td class="has-context">
                <div class="pull-left">
                    <?php if ($row->checked_out) { ?>
                            <?php echo JHtml::_('jgrid.checkedout', $i, $user->name, $row->checked_out_time, 'visfields.', $canCheckin); ?>
                    <?php } ?>
                    <?php if ($canEdit || $canEditOwn) { ?>
                    <a href="<?php echo $link; ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>"><?php echo $row->label; ?></a>
                    <p class="small">
                        <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->name));?>
                    </p>
                    <?php } else { ?>
                    <?php echo $this->escape($row->label); ?>
                    <p class="small">
                        <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->name));?>
                    </p>
                    <?php } ?>
                </div>
			</td>
            <td class="center">
                <?php echo $published;?>
            </td>
	
			<td class="center nowrap">
				<?php echo $this->escape($row->typefield); ?>
			</td>
			<td class="center hidden-phone">
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
	}
	?>
    
    <tfoot>
    <tr>
      <td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
    </tr>
  	</tfoot>
    
	</table>
	<?php //Load the batch processing form. ?>
	<?php 
		echo $this->loadTemplate('batch'); 
	 ?>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
        </div>
        </div>
</form>
 <?php JHtml::_('visforms.creditsBackend'); ?>