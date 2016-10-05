<?php
/**
* @package 		Facebook Connect Extension (joomla 3.x)
* @copyright	Copyright (C) Computer - http://www.sanwebe.com. All rights reserved.
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @author		Saran Chamling
* @download URL	http://www.sanwebe.com
*/
// No direct access
defined('_JEXEC') or die('Restricted access'); 
jimport( 'joomla.application.application' );
$app 				= JFactory::getApplication();
$document 			= JFactory::getDocument();
$user 				= JFactory::getUser();
$myparams 			= JComponentHelper::getParams('com_fbconnct');					
$document->addStyleSheet('components/com_fbconnct/assets/fbbackend.css');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

if(!$this->items)
{
	if(strlen($this->lists['search'])>1)
	{
	$app->enqueueMessage('No Results found containing "'.htmlspecialchars($this->lists['search']).'"!', 'error');
	}else{
	$app->enqueueMessage('No Connected Users yet', 'message');
	}
}

?>

<form action="<?php echo JRoute::_('index.php?option=com_fbconnct');?>" method="post" name="adminForm" id="adminForm">

    <table class="table table-striped" id="articleList">
    <thead>
        <tr>
            <th width="10">
                 <?php echo JHtml::_('grid.sort', JText::_('ID'), 'id', $listDirn, $listOrder); ?> </th>
            <th>
                <?php echo JHtml::_('grid.sort', JText::_('Full Name'), 'name', $listDirn, $listOrder); ?> </th>
             <th>
				<?php echo JText::_('Username'); ?> </th>
             <th>
                <?php echo JHtml::_('grid.sort', JText::_('Email'), 'email', $listDirn, $listOrder); ?> 
				</th>
             <th>
                <?php echo JText::_('facebook Userid'); ?>
                </th>
             <th>
                 <?php echo JHtml::_('grid.sort', JText::_('Registered Date'), 'registerDate', $listDirn, $listOrder); ?>            </th> 
        </tr>            
    </thead>
   <tbody><?php
    $k = 0;
    for ($i=0, $n=count( $this->items ); $i < $n; $i++)
    {
        $row =& $this->items[$i];
        $checked    = JHTML::_( 'grid.id', $i, $row->id ); 
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td>
               <?php echo $row->id; ?></td>
            <td>
            <?php 
				if(version_compare(JVERSION,'1.6.0','ge')) {
				?>
               <a href="index.php?option=com_users&task=user.edit&id=<?php echo $row->id; ?>&extension=com_fbconnct"><?php echo $row->fullname; ?></a>
               <?php }else{ ?>
               <a href="index.php?option=com_users&view=user&task=edit&cid[]=<?php echo $row->id; ?>&extension=com_fbconnct"><?php echo $row->fullname; ?></a>
               <?php } ?>
               </td>
            <td>
                <?php echo $row->username ; ?></td>
            <td>
                <?php echo $row->email ; ?></td>
            <td>
                <a href="http://www.facebook.com/profile.php?id=<?php echo($row->facebookid); ?>" target="_new"><?php echo($row->facebookid); ?></a>
				 </td>
            <td>
                <?php echo $row->joineddate ; ?></td>
        </tr>
        
          
        
        <?php
        $k = 1 - $k;
    }
    ?>
    </tbody>
    <tfoot><tr><td colspan="9" align="center"><div class="pagination"><?php echo $this->pagination->getListFooter();?></div></td></tr></tfoot>
    </table>

 
		<input type="hidden" name="view" value="users" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
</form>
<div align="center" style="font-size:11px;">Facebook Connect by <a href="http://www.saaraan.com" target="_blank">saaraan.com</a></div>
