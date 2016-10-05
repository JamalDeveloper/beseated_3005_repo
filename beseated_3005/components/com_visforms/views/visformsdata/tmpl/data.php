<?php
/**
 * Visformsdata data view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 
	
	$listOrder	= $this->escape($this->state->get('list.ordering'));
	$listDirn	= $this->escape($this->state->get('list.direction'));
	
	if ($this->form->published != '1') return;
	$document = JFactory::getDocument();
	$document->addCustomTag('<style type="text/css"><!-- #maincolumn  {	overflow:auto !important;} --></style>');
    $document->addCustomTag('<link type="text/css" href="' . JURI::root(true) . '/media/com_visforms/css/visforms.min.css" rel="stylesheet">');
	$document->addCustomTag('<link type="text/css" href="' . JURI::root(true) . '/media/com_visforms/css/visforms.css" rel="stylesheet">');
?>

<div class="visforms-form<?php echo $this->menu_params->get( 'pageclass_sfx' ); ?>">
<?php if ($this->menu_params->get('show_page_heading') == 1) { 
		if (!$this->menu_params->get('page_heading') == "") { ?>
			<h1><?php echo $this->menu_params->get('page_heading'); ?></h1>
	<?php }
		else { 
			if (isset($this->form->fronttitle) == false || strcmp ($this->form->fronttitle, "") == 0)
			{
			echo '<h1>' . $this->form->title . '</h1>';
			} 
			else {
			echo '<h1>' . $this->form->fronttitle . '</h1>'; 
			}
		}
	}?>


<?php 
	if (isset($this->form->frontdescription) == false || strcmp($this->form->frontdescription, "") == 0) 
	{
		JPluginHelper::importPlugin('content'); 
		echo '<div class="category-desc">' . JHtml::_('content.prepare', $this->form->description) . '</div>';
	} else {
		JPluginHelper::importPlugin('content'); 
		echo '<div class="category-desc">' . JHtml::_('content.prepare', $this->form->frontdescription).'</div>';
	}
?>

<form action="<?php echo JRoute::_('index.php?option=com_visforms&view=visformsdata&layout=data&id=' . $this->id);?>" method="post" name="adminForm" id="<?php echo $this->context; ?>adminForm">
<?php
if (!empty($this->menu_params['show_filter']))
{
    echo JLayoutHelper::render('visforms.searchtools.default', array('view' => $this, 'options' => array ('context' => $this->context))); 
}
?>
        <div class="clr"> </div>
<table class="visdatatable jlist-table<?php if (isset($this->menu_params['show_tableborder']) && $this->menu_params['show_tableborder'] == 1) {echo " visdatatableborder";} ?> <?php if (isset($this->menu_params['viewclass'])) {echo $this->menu_params['viewclass'];} ?>">

<?php if (isset($this->menu_params['show_columnheader']) && $this->menu_params['show_columnheader'] == 1)
{
?>	

<thead>
    <tr>
<?php $k = 0;
	$n=count( $this->fields );

?>
		 
			<?php 
            if (!(isset($this->form->displaydetail) == false) && $this->form->displaydetail == true)
            { 
                ?>
                <th class="data-id">
                <?php
                if (isset($this->form->displayid) && (($this->form->displayid == "1") || ($this->form->displayid == "2")))
                {
                    echo JHTMLVisforms::sort( 'COM_VISFORMS_ID', 'a.id', $listDirn, $listOrder, 'visformsdata.display', 'asc', '', $this->context . 'adminForm', 'vftableOrdering'); 
                }
                else
                {
                    echo " &nbsp; "; 
                }
                ?>
                </th>
                <?php 
            }
            else if (isset($this->form->displayid) && (($this->form->displayid == "1") || ($this->form->displayid == "2")))
            {
                echo '<th class="data-id">';
                echo JHTMLVisforms::sort( 'COM_VISFORMS_ID', 'a.id', $listDirn, $listOrder, 'visformsdata.display', 'asc', '', $this->context . 'adminForm', 'vftableOrdering'); 
                echo "</th>";
            }
			?>
<?php 
	for ($i=0; $i < $n; $i++)
	{
		$rowField = $this->fields[$i];
		
		if (isset($rowField->frontdisplay) && ($rowField->frontdisplay == 1 || $rowField->frontdisplay == 2))
		{			
?>
			<th>
				<?php echo JHTMLVisforms::sort($rowField->label, 'a.F'. $rowField->id, $listDirn, $listOrder, 'visformsdata.display', 'asc', '', $this->context . 'adminForm', 'vftableOrdering'); ?>
			</th>
<?php	
		}
	}
 	
    if (isset($this->form->displayip) && (($this->form->displayip == "1") || ($this->form->displayip == "2"))) 
    {
 ?>
		<th class="data-ip">
			<?php echo JHTMLVisforms::sort( 'COM_VISFORMS_IP', 'a.ipaddress', $listDirn, $listOrder, 'visformsdata.display', 'asc', '', $this->context . 'adminForm', 'vftableOrdering'); ?>
		</th>
<?php
	} 
    if ((isset($this->form->displayismfd)) && (($this->form->displayismfd == "1") || ($this->form->displayismfd == "2"))) 
    {
 ?>
		<th class="data-mfd">
			<?php echo JHTMLVisforms::sort( 'COM_VISFORMS_MODIFIED', 'a.ismfd', $listDirn, $listOrder, 'visformsdata.display', 'asc', '', $this->context . 'adminForm', 'vftableOrdering'); ?>
		</th>
<?php
	} 
    if ((isset($this->form->displaycreated)) && (($this->form->displaycreated == "1") || ($this->form->displaycreated == "2"))) 
    {
 ?>
		<th class="data-created">
			<?php echo JHTMLVisforms::sort( 'COM_VISFORMS_SUBMISSIONDATE', 'a.created', $listDirn, $listOrder, 'visformsdata.display', 'asc', '', $this->context . 'adminForm', 'vftableOrdering'); ?>
		</th>
<?php
	} 
?>
        
	</tr>			
</thead>
<?php	} ?>

<?php
	
	$k = 0;
    $n = (is_array($this->items)) ? count($this->items) : 0;
	for ($i=0; $i < $n; $i++)
	{	
	
		$row = $this->items[$i];
        if (empty($row->published))
        {
            continue;
        }
		$link = JRoute::_( 'index.php?option=com_visforms&view=visformsdata&layout=detail&id='.$this->id.'&cid='.$row->id.'&Itemid='.$this->itemid );
?>
		<tr class="sectiontableentry1">
		

 
	
<?php 
    if (!(isset($this->form->displaydetail) == false) && $this->form->displaydetail == true)
    {
        if(isset($this->form->displayid) && (($this->form->displayid == "1") || ($this->form->displayid == "2")))
        {
            if (isset($row->id))
            {
                echo "<td><a class=\"hasTooltip\" href=\"".$link."\" data-original-title=\"".JText::_('COM_VISFORMS_VIEW_DETAIL')."\">".$row->id."</a></td>"; 
            }
             else 
            { 
                echo "<td><a class=\"hasTooltip\" href=\"".$link."\" data-original-title=\"".JText::_('COM_VISFORMS_VIEW_DETAIL')."\"> $nbsp; </a></td>";  
            }
        }
        else
        {
            echo "<td><a class=\"hasTooltip\" href=\"".$link."\" data-original-title=\"".JText::_('COM_VISFORMS_VIEW_DETAIL')."\"><i class=\"visicon-download\" ></i></a></td>" ;
        }
     }
     else if(isset($this->form->displayid) && (($this->form->displayid == "1") || ($this->form->displayid == "2")))
	{
		if (isset($row->id))
		{
			echo "<td>" . $row->id . "</td>"; 
		}
		 else 
		{ 
			echo "<td>$nbsp;</td> ";  
		}
	}
     
?>
        
<?php
	$z=count( $this->fields );
	for ($j=0; $j < $z; $j++)
	{
		$rowField = $this->fields[$j];
		
		if (isset($rowField->frontdisplay) && ($rowField->frontdisplay == 1 || $rowField->frontdisplay == 2))
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
			
			if ($rowField->typefield == 'file' && isset($row->$prop))
			{
                //info about uploaded files are stored in a JSON Object. Earlier versions just have a string.
                if (isset($rowField->showlink) && $rowField->showlink == true)
                {
                    $texte = JHtml::_('visforms.getUploadFileLink', $texte);
                }
                else
                {
                    $texte = JHtml::_('visforms.getUploadFileName', $texte);
                }
			}
			
			else if ($rowField->typefield == 'email') {				
                            $texte = '<a href="mailto:'.$texte.'">'.$texte.'</a>';
			}
            else if (isset($rowField->urlaslink) && ($rowField->urlaslink == true) && ($rowField->typefield == 'url') && ($texte != ""))
			{				
                            $texte = '<a href="'.$texte.'" target="_blank">'.$texte.'</a>';
			}
			else
			{
                            if (strlen($texte) > 255) {
				$texte = substr($texte,0,255)."...";
                            }
			}

 ?>
 
	<td>
<?php 
	JPluginHelper::importPlugin('content');
    echo JHtml::_('content.prepare', $texte);
	?>
	</td> 
    
<?php	
		}
	}

    if ($this->form->displayip == '1') 
    {
 ?>
			<td>
				<?php 
					echo $row->ipaddress; 
				?>
			</td>
<?php } 
    if(isset($this->form->displayismfd) && (($this->form->displayismfd == "1") || ($this->form->displayismfd == "2")))
    {
        if (isset($row->ismfd) && ($row->ismfd == true))
        {
            echo "<td>" . JText::_('JYES') . "</td>"; 
        }
         else 
        { 
            echo "<td>". JText::_('JNO') ."</td> ";  
        }
    }
    if ((isset($this->form->displaycreated)) && (($this->form->displaycreated == "1") || ($this->form->displaycreated == "2"))) 
    {
        $date = new JDate($row->created);
 
		if (isset($this->form->displaycreatedtime) && (($this->form->displaycreatedtime == "1") || ($this->form->displaycreatedtime == "2")))
        {
            echo "<td>" .$date->format(JText::_('DATE_FORMAT_LC4') . " H:i:s") . "</td>"; 
        }
         else 
        { 
            echo "<td>". $date->format(JText::_('DATE_FORMAT_LC4')) ."</td> ";  
        }
	} 
?>

		</tr>
		<?php
		$k = 1 - $k;
	}
	?>


</table> 
<?php
    echo '<div class="pagination"><p class="counter">' . $this->pagination->getPagesCounter() . '</p>' . $this->pagination->getPagesLinks() . '</div>';
?>

<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="<?php echo $this->context; ?>filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="<?php echo $this->context; ?>filter_order_Dir" value="<?php echo $listDirn; ?>" />
		
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<?php if ($this->form->poweredby == '1') { ?>
	<div id="vispoweredby"><a href="http://vi-solutions.de" target="_blank"><?php echo JText::_( 'COM_VISFORMS_POWERED_BY' ); ?></a></div>
<?php } ?>
</div>
<script>
    vftableOrdering = function( order, dir, task, form ) {
		if ( typeof form  === 'undefined' ) {
			form = document.getElementById( 'adminForm' );
		}

		form.<?php echo $this->context; ?>filter_order.value = order;
		form.<?php echo $this->context; ?>filter_order_Dir.value = dir;
		Joomla.submitform( task, form );
	};
</script>