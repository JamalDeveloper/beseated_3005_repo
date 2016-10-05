<?php
/**
 * @package     The Beseated.Site
 * @subpackage  com_bcted
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$input        = JFactory::getApplication()->input;
$Itemid       = $input->get('Itemid', 0, 'int');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');

?>
<script type="text/javascript">
	function deleteTable(tableID,premiumID)
	{
		jQuery.ajax({
			type: "GET",
			url: "index.php?option=com_beseated&task=clubownertables.deletetable",
			data: "&table_id="+tableID+"&premium_id="+premiumID,
			success: function(response){
				if(response == "200")
				{
					location.reload();
				}
			}
	    });
	}
</script>

<div class="table-wrp">
	<div class="media row-fluid">
		<div class="pull-left table-img span6">
			<?php $link = JRoute::_('index.php?option=com_beseated&view=clubownertableedit&venue_id='.$this->elementDetail->venue_id.'&table_id=0&Itemid='.$Itemid); ?>
			<a class="club-add-table" href="<?php echo $link; ?>"><?php echo JText::_('COM_BCTED_CLUBOWNERTABLES_ADD_NEW_TABLE'); ?></a>
		</div>
	</div>
	<?php foreach ($this->items as $key => $item):?>
		<div class="media row-fluid">
		<div class="pull-left table-img span6">
			<?php if(!empty($item->image)): ?>
				<img src="<?php echo JUri::root() .'images/beseated/'. $item->image; ?>">
			<?php else: ?>
				<img src="images/tabl-img.jpg">
			<?php endif; ?>
        </div>
		<div class="media-body span6">
			<div class="control-group">
				<h4 class="media-heading">
					<?php
						if($item->premium_table_id)
						{
							echo ucfirst($item->table_name);
						}
						else
						{
							echo ucfirst($item->table_name);
						}
					?>
				</h4>
			</div>
			<div class="control-group">
				<h4 class="media-heading"><?php echo 'Price : '.$item->currency_sign.' '.$item->min_price; ?></h4>
				<h4 class="media-heading"><?php echo 'Capacity : ' .$item->capacity; ?></h4>
			</div>
            <div class="tbl-actn-btn">
                <button onclick="deleteTable('<?php echo $item->table_id; ?>',0)">Delete</button>
                <?php $link = JRoute::_("index.php?option=com_beseated&view=clubownertableedit&table_id=".$item->table_id."&Itemid=".$Itemid); ?>
                <a href="<?php echo $link; ?>"><button>Edit</button></a>
            </div>

		</div>
		</div>
	<?php endforeach; ?>
</div>
