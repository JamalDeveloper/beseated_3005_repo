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
	function deleteBottle(bottleID)
	{
		jQuery.ajax({
			type: "GET",
			url: "index.php?option=com_beseated&task=clubownerbottles.deletebottle",
			data: "&bottle_id="+bottleID,
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
			<?php $link = JRoute::_('index.php?option=com_beseated&view=clubownerbottleedit&venue_id='.$this->elementDetail->venue_id.'&table_id=0&Itemid='.$Itemid); ?>
			<a class="club-add-table" href="<?php echo $link; ?>"><?php echo JText::_('COM_BCTED_CLUBOWNERTABLES_ADD_NEW_BOTTLE'); ?></a>
		</div>
	</div>
	<?php foreach ($this->items as $key => $item):?>
		<div class="media row-fluid">
		<div class="pull-left table-img span6">
			<?php if(!empty($item->thumb_image)): ?>
				<img src="<?php echo JUri::root() .'images/beseated/'. $item->thumb_image; ?>">
			<?php else: ?>
				<img src="images/tabl-img.jpg">
			<?php endif; ?>
        </div>
		<div class="media-body span6">
			<div class="control-group">
				<h4 class="media-heading">
					<?php echo ucfirst($item->bottle_type); ?>
				</h4>
			</div>
			<div class="control-group">
				<h4 class="media-heading">
					<?php echo ucfirst($item->brand_name); ?>
				</h4>
			</div>
			<div class="control-group">
				<h4 class="media-heading"><?php echo 'Price : '.$item->currency_sign.' '.$item->price; ?></h4>
				<h4 class="media-heading"><?php echo 'Capacity : ' .$item->size; ?></h4>
			</div>
            <div class="tbl-actn-btn">
                <button onclick="deleteBottle('<?php echo $item->bottle_id; ?>')">Delete</button>
                <?php $link = JRoute::_("index.php?option=com_beseated&view=clubownerbottleedit&bottle_id=".$item->bottle_id."&Itemid=".$Itemid); ?>
                <a href="<?php echo $link; ?>"><button>Edit</button></a>
            </div>

		</div>
		</div>
	<?php endforeach; ?>
</div>
