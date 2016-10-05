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
	function deleteTable(serviceID)
	{
		jQuery.ajax({
			type: "GET",
			url: "index.php?option=com_beseated&task=protectionownerservices.deleteservice",
			data: "&service_id="+serviceID,
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
			<?php $link = JRoute::_('index.php?option=com_beseated&view=protectionownerserviceedit&protection_id='.$this->elementDetail->protection_id.'&service_id=0&Itemid='.$Itemid); ?>
			<a class="club-add-table" href="<?php echo $link; ?>"><?php echo JText::_('COM_BCTED_CHAUFFEUROWNER_ADD_NEW_SERVICE'); ?></a>
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
					<?php echo ucfirst($item->service_name);?>
				</h4>
			</div>
			<div class="control-group">
				<h4 class="media-heading">
					<?php echo BeseatedHelper::currencyFormat($item->currency_code,$item->currency_code,$item->price_per_hours).'/Hr'; ?>
				</h4>
			</div>
            <div class="tbl-actn-btn">
                <button onclick="deleteTable('<?php echo $item->service_id; ?>')">Delete</button>
                <?php $link = JRoute::_("index.php?option=com_beseated&view=protectionownerserviceedit&service_id=".$item->service_id."&Itemid=".$Itemid); ?>
                <a href="<?php echo $link; ?>"><button>Edit</button></a>
            </div>

		</div>
		</div>
	<?php endforeach; ?>
</div>
