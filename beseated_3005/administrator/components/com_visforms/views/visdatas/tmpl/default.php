<?php 
/**
 * Visdatas default view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Administrator
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access' );
 
 JHtml::_('bootstrap.tooltip');
 JHtml::_('behavior.multiselect');
 JHtml::_('formbehavior.chosen', 'select');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$user		= JFactory::getUser();
$userId		= $user->get('id');
$canEditState = $this->canDo->get('core.edit.state');
$canEditData = $this->canDo->get('core.edit.data');
$sortFields = $this->getSortFields();
$fid = JFactory::getApplication()->input->getInt( 'fid', -1 );
?>

<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {
		if (pressbutton == 'visdatas.export') {
            //if datasets are check we submit id's of check datasets as array cid[]
            var form = document.getElementById('adminForm');
            stub = 'cb';
            var cid  = '';
	
            if (form) {
                var j = 0;
                for (var i = 0, n = form.elements.length; i < n; i++) {
                    var e = form.elements[i];
                    if (e.type == 'checkbox') {
                        if (e.id.indexOf(stub) == 0) {
                            if (e.checked == true)
                            {
                                cid += '&cid[' + j + ']=' + e.value;
                                j++;
                                e.checked = false;
                            }
                        }
                    }
                }
            }

 
            window.location = 'index.php?option=com_visforms&view=visdatas&fid=<?php echo $fid;?>&task=visdatas.export' + cid; 
		}  else { 
			submitform( pressbutton );
		}
	}
</script>
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

<style type="text/css">
<!--
#element-box  {
	overflow:auto !important;
}
-->
</style>

<form action="<?php echo JRoute::_('index.php?option=com_visforms&view=visdatas&fid=' . $fid);?>" method="post" name="adminForm" id="adminForm" >

    
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
        <th width="3%" class="nowrap center">
                <?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_ID', 'a.id', $listDirn, $listOrder); ?>
        </th>
        <th width="3%"  class="nowrap center">
                
            <?php echo JHtml::_('grid.checkall'); ?>
        </th>
        <th width="3%">
                <?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_PUBLISHED', 'a.published', $listDirn, $listOrder); ?>		
        </th>	
        <th width="3%">
            <?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_MODIFIED', 'a.ismfd', $listDirn, $listOrder) ; ?>
        </th>
        <th width="4%" class="nowrap center">
                <?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_CREATED_BY', 'a.created_by', $listDirn, $listOrder); ?>
        </th>

                <?php	$k = 0;
                $n=count( $this->fields );
                for ($i=0; $i < $n; $i++)
                {
                        $width = 30;
                        if ($n > 0) {
                                $width = floor(89/$n);
                        }
                        $rowField = $this->fields[$i];
                        if (!($rowField->showFieldInDataView === false))
                        {
                ?>
                        <th width="<?php echo $width ?>%" class="nowrap center">
                                <?php echo JHtml::_('searchtools.sort', $rowField->name, 'a.F'. $rowField->id, $listDirn, $listOrder); ?>
                        </th>
                <?php         
                        }
                }
                ?>
                <th width="4%" class="nowrap center">
                        <?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_IP', 'a.ipaddress', $listDirn, $listOrder); ?>
                </th>
                <th width="4%" class="nowrap center">
                        <?php echo JHtml::_('searchtools.sort', 'COM_VISFORMS_DATE', 'a.created', $listDirn, $listOrder); ?>
                </th>

			
	</tr>			
</thead>
	<?php if (is_array($this->items))
    {
        foreach ($this->items as $i => $row) :
        $row->max_ordering = 0; //??
        $ordering   = ($listOrder == 'a.id');
	
                if ($canEditState)
                {
                    $published	= JHtml::_('jgrid.published', $row->published, $i, 'visdatas.', true );
                }
                else
                {
                    $published	= JHtml::_('jgrid.published', $row->published, $i, 'visdatas.', false );
                }
		$checked = JHtml::_('grid.id',   $i, $row->id );
		$link = JRoute::_( 'index.php?option=com_visforms&task=visdata.edit&fid='.$fid.'&id='. $row->id );
        $canCheckin	= $user->authorise('core.manage', 'com_visforms') || $row->checked_out == $userId || $row->checked_out == 0;
        $canEdit	= $user->authorise('core.edit.data', 'com_visforms.visform.'.$fid);
        $canEditOwn	= $user->authorise('core.edit.own.data', 'com_visforms.visform.' . $fid) && $row->created_by == $userId;
        $modified = ($row->ismfd && $canEditData) ? JHtml::_('jgrid.action', $i, 'visdatas.reset' , $prefix = '', $text = '', $active_title = 'COM_VISFORMS_RESET_DATA', $inactive_title = '', $tip = true, $active_class = 'undo',
		$inactive_class = '', $enabled = true, $translate = true, $checkbox = 'cb') : ($row->ismfd) ? JText::_('JYES'): JText::_('JNO');

		?>
        <tr class="row<?php echo $i % 2; ?>">
                    <td class="has-context">
                        <div class="center">
                            <?php if ($canEdit || $canEditOwn) : ?>
                                <?php echo "<a href=\"".$link."\">".$row->id."</a>"; ?>
                            <?php else : ?>
                                <?php echo $row->id; ?>
                            <?php endif; ?>
                            <?php if ($row->checked_out) : ?>
                                <?php echo JHtml::_('jgrid.checkedout', $i, $user->name, $row->checked_out_time, 'visdatas.', $canCheckin); ?>
                        <?php endif; ?>
                        </div>
                    </td>
                    <td class="center">
                            <?php echo $checked; ?>
                    </td>   
                        
            <td align="center">
                <?php echo $published;?>
            </td>
            <td class="center">
                <?php echo $modified;?>
            </td>
            <td class="center">
                <?php echo $row->created_by;?>
            </td>
<?php
	$z=count( $this->fields );
	for ($j=0; $j < $z; $j++)
	{
		$rowField = $this->fields[$j];
		if (!($rowField->showFieldInDataView === false))
		{
			$prop="F".$rowField->id;
			if (isset($row->$prop) == false)
			{
				$prop=$rowField->name;
			}
			if (isset($row->$prop))
			{
				$texte = $row->$prop;
			} else {
				$texte = "&nbsp;";
			}
			if ($rowField->typefield == 'email')
                        {
				$linkfield = "mailto:".$texte;
                                echo "<td><a href=\"".$linkfield."\">".$texte."</a></td>";
			}
			else if (isset($rowField->defaultvalue['f_url_urlaslink']) && ($rowField->defaultvalue['f_url_urlaslink'] == true) && ($rowField->typefield == 'url') && ($texte != ""))
			{

                echo "<td><a href=\"".$texte."\" target=\"_blank\">".$texte."</a></td>";
			}
			else if ($rowField->typefield == 'file')
                        {
                            //info about uploaded files are stored in a JSON Object. Earlier versions just have a string.
                            $texte = JHtml::_('visforms.getUploadFileLink', $texte);
                            echo "<td>". $texte . "</td>";
                        }
                        else
			{
                            if (strlen($texte) > 255) {
				$texte = substr($texte,0,255)."...";
                            }
                            echo "<td>" . $texte . "</td>";
			}
 	
		}
	}
 ?>

			<td>
				<?php echo $row->ipaddress; ?>
			</td>
            <td>
				<?php echo $row->created; ?>
			</td>
			
		</tr>
		<?php endforeach;
    }
	?>
    
    <tfoot>
    <tr>
      <td colspan="<?php echo (count($this->fields) + 4); ?>"><?php echo $this->pagination->getListFooter(); ?></td>
    </tr>
  </tfoot>

</table> 

   

  
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
    </div>
</form>

<?php JHtml::_('visforms.creditsBackend'); ?>