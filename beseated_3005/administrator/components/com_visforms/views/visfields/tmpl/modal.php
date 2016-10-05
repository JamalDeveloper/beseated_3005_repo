<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

if (JFactory::getApplication()->isSite()) {
	JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
}

//require_once JPATH_ROOT . '/components/com_content/helpers/route.php';

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.framework', true);

$function	= JFactory::getApplication()->input->getCmd('function', 'jSelectVisformfield');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
//Add field types that should not be available as placeholder to this list
$hiddenFieldTypes = array('submit', 'reset', 'image', 'fieldsep');

?>
<form action="<?php echo JRoute::_('index.php?option=com_visforms&view=visfields&fid=' . JFactory::getApplication()->input->getInt('fid', -1) . '&layout=modal&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1');?>" method="post" name="adminForm" id="adminForm" class="form-inline"> 
	<fieldset class="filter clearfix">
		<div class="btn-toolbar">
			<div class="btn-group pull-left">
			<label for="filter_search">
				<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
			</label>
            </div>
			<div class="btn-group pull-left">
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" size="30" title="<?php echo JText::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>" />
            	<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>" data-placement="bottom">
					<span class="icon-search"></span><?php echo '&#160;' . JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" data-placement="bottom" onclick="document.id('filter_search').value='';this.form.submit();">
					<span class="icon-remove"></span><?php echo '&#160;' . JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
			<div class="clearfix"></div>
		</div>
		<hr class="hr-condensed" />
			

		<div class="filters pull-left">
			<select name="filter_published" class="input-medium" onchange="this.form.submit()">
                <?php $options = array(); 
                $options[] = JHtml::_('select.option', '', JText::_('JOPTION_SELECT_PUBLISHED'));
                $options[] = JHtml::_('select.option', '1', JText::_('JPUBLISHED'));
                $options[] = JHtml::_('select.option', '0', JText::_('JUNPUBLISHED'));
                $options[] = JHtml::_('select.option', '*', JText::_('JALL'));
                ?>
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
		</div>
	</fieldset>

	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th width="1%"class="center nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
				<th class="center nowrap">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.label', $listDirn, $listOrder); ?>
				</th>					
                <th width="10%" class="nowrap center">
                    <?php echo JHtml::_('grid.sort', 'COM_VISFORMS_TYPE', 'a.typefield', $listDirn, $listOrder); ?>
                </th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
            <?php if(!(in_array($item->typefield, $hiddenFieldTypes)))
                  { ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center">
                            <?php echo (int) $item->id; ?>
                        </td>
                        <td class="center">
                            <a href="javascript:void(0)" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $this->escape(addslashes($item->name)); ?>');">
                                <?php echo $this->escape($item->label); ?></a>
                        </td>
                        <td class="center nowrap">
                            <?php echo $this->escape($item->typefield); ?>
                        </td>
                    </tr>
           <?php } ?>
        <?php endforeach; ?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
