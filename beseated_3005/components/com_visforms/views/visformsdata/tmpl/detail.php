<?php 
/**
 * Visformsdata detail view for Visforms
 *
 * @author       Aicha Vack
 * @package      Joomla.Site
 * @subpackage   com_visforms
 * @link         http://www.vi-solutions.de 
 * @license      GNU General Public License version 2 or later; see license.txt
 * @copyright    2012 vi-solutions
 * @since        Joomla 1.6 
 */

//no direct access
defined('_JEXEC') or die('Restricted access'); 
    $app = JFactory::getApplication();
    if (empty($this->item))
    {
        $app->enqueueMessage(JText::_('COM_VISFORMS_FORM_DATA_RECORD_SET_MISSING'), 'error');
        return;
    }
    if (empty($this->item->published))
    {
        $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
        return;
    }

    $document = JFactory::getDocument();
    $document->addCustomTag('<link type="text/css" href="' . JURI::root(true) . '/media/com_visforms/css/visforms.css" rel="stylesheet">');
    $menuitems = $app->getMenu()->getItems('link', 'index.php?option=com_visforms&view=visformsdata&layout=data&id='.$this->id);
?>
<div class="visforms-form<?php echo $this->menu_params->get( 'pageclass_sfx' ); ?>">
<?php if ($this->menu_params->get('show_page_heading') == 1) { 
		if (!$this->menu_params->get('page_heading') == "") { ?>
			<h1><?php echo $this->menu_params->get('page_heading'); ?></h1>
	<?php }
	else { if (isset($this->form->fronttitle) == false || strcmp ($this->form->fronttitle, "") == 0)
		{
			echo '<h1>' . $this->form->title . '</h1>';
			} else {
			echo '<h1>' . $this->form->fronttitle . '</h1>'; 
		}
	}
}
    foreach ($menuitems as $item)
    {
        if (isset($item->id) && ($item->id == $this->itemid))
        {
            $linkback = "index.php?option=com_visforms&view=visformsdata&layout=data&Itemid=". $this->itemid ."&id=". $this->id;
            echo '<a class="btn" href="' . JRoute::_($linkback) . '">';
            echo JText::_( 'COM_VISFORMS_BACK_TO_LIST' );
            echo '</a>';
            break;
        }
    }
?>
 
<table class="visdatatable">
    <?php
    if (isset($this->form->displayid) && ($this->form->displayid == 1 || $this->form->displayid == 3))
    	{
?>
        <tr>
            <td class="visfrontlabel">
                   <?php echo JText::_( 'COM_VISFORMS_ID' ) . " :"; ?>
            </td>
            <td>
                <?php echo $this->item->id; ?>
            </td>
        </tr>
<?php } ?>
         
<?php	$k = 0;
	$n=count( $this->fields );
	for ($i=0; $i < $n; $i++)
	{
		$rowField = $this->fields[$i];

		if (isset($rowField->frontdisplay) && ($rowField->frontdisplay == 1 || $rowField->frontdisplay == 3))
		{			
			$prop="F".$rowField->id;
			if (isset($this->item->$prop) == false)
			{
				$prop=$rowField->name;
			}
			if (isset($this->item->$prop))
			{
				$texte = $this->item->$prop;
			} else {
				$texte = "";
			}
?>
    
<tr>
	<td class="visfrontlabel">
    
        <?php echo  $rowField->label; ?> :
    
    </td>
    <td>
<?php             
			if ($rowField->typefield == 'email') {
				$texte = '<a href="mailto:'.$texte.'">'.$texte.'</a>';
			}
            else if (isset($rowField->urlaslink) && ($rowField->urlaslink == true) && ($rowField->typefield == 'url') && ($texte != ""))
			{				
                $texte = '<a href="'.$texte.'" target="_blank">'.$texte.'</a>';
			}
			
			if ($rowField->typefield == 'file' && isset($texte))
			{
                //info about uploaded files are stored in a JSON Object. Earlier versions just have a string.
                if (isset($rowField->showlink) && $rowField->showlink == true)
                {
                    //show the link
                    $texte = JHtml::_('visforms.getUploadFileLink', $texte);
                }
                else
                {
                    //show the filename
                    $texte = JHtml::_('visforms.getUploadFileName', $texte);
                    $texte = basename($texte);
                }
			}
            JPluginHelper::importPlugin('content');
            echo JHtml::_('content.prepare', $texte); 
 ?>
    </td>
</tr>
            
<?php	
			}
		}
        if (isset($this->form->displayip) && ($this->form->displayip == 1 || $this->form->displayip == 3))
    	{
?>
        <tr>
            <td class="visfrontlabel">
                   <?php echo JText::_( 'COM_VISFORMS_IP_ADDRESS' ) . " :"; ?>
            </td>
            <td>
                <?php echo $this->item->ipaddress; ?>
            </td>
        </tr>
<?php }
    if (isset($this->form->displayismfd) && ($this->form->displayismfd == 1 || $this->form->displayismfd == 3))
    {
        ?>
        <tr>
            <td class="visfrontlabel">
                   <?php echo JText::_( 'COM_VISFORMS_MODIFIED' ) . " :"; ?>
            </td>
            <?php
        if (isset($this->item->ismfd) && ($this->item->ismfd == true))
        {
            echo "<td>" . JText::_('JYES') . "</td>"; 
        }
         else 
        { 
            echo "<td>". JText::_('JNO') ."</td> ";  
        }
        ?>
        </tr>
        <?php
    }
        if (isset($this->form->displaycreated) && ($this->form->displaycreated == 1 || $this->form->displaycreated == 3))
        {
            ?>
        <tr>
            <td class="visfrontlabel">
                   <?php echo JText::_( 'COM_VISFORMS_SUBMISSIONDATE' ) . " :"; ?>
            </td>
        <?php
        $date = new JDate($this->item->created);
 
        if (isset($this->form->displaycreatedtime) && ($this->form->displaycreatedtime == 1 || $this->form->displaycreatedtime == 3))
        {
            echo "<td>" .$date->format(JText::_('DATE_FORMAT_LC4') . " H:i:s") . "</td>"; 
        }
         else 
        { 
            echo "<td>". $date->format(JText::_('DATE_FORMAT_LC4')) ."</td> ";  
        }
        ?>
        </tr>
        <?php
	} 
?>       
</table>
<?php if ($this->form->poweredby == '1') { ?>
	<div id="vispoweredby"><a href="http://vi-solutions.de" target="_blank"><?php echo JText::_( 'COM_VISFORMS_POWERED_BY' ); ?></a></div>
<?php } ?>
</div>