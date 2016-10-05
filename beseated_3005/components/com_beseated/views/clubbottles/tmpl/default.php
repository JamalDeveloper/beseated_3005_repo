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

$app          = JFactory::getApplication();
$input        = JFactory::getApplication()->input;
$tableId      = $input->get('table_id', 0, 'int');
$clubID       = $input->get('club_id', 0, 'int');
$this->user   = JFactory::getUser();
$this->isRoot = $this->user->authorise('core.admin');
$menu         = $app->getMenu();
$menuItem     = $menu->getItems( 'link', 'index.php?option=com_beseated&view=userbookings', true );
$Itemid       = $menuItem->id;
?>
<div id="alert-error" class="alert alert-error"></div>
<div class="bottle-wrp">
<H2><?php echo JText::_('COM_BESEATED_CLUB_BOTTLES_LIST_USER_SIDE_BOTTLES_VIEW_TITLE'); ?></H2>
<?php foreach ($this->items as $key => $item):?>
	<div class="media">
		<div class="pull-left bottle-img span6">
			<?php if(!empty($item->image)): ?>
				<img src="<?php echo JUri::root().'images/beseated/'.$item->image; ?>">
			<?php else: ?>
				<img src="<?php echo JUri::root().'images/beseated/default/banner.png';?>">
			<?php endif; ?>
        </div>
		<div class="media-body span6">
			<div class="control-group bottle-type">
				<h4 class="bottle-type-inner">
					<?php echo ucfirst($item->bottle_type);?>
				</h4>
			</div>
			<div class="control-group brand-name">
				<h4 class="brand-name-inner">
					<?php echo ucfirst($item->brand_name);?>
				</h4>
			</div>
			<div class="control-group">
				<h4 class="bottle-size"><?php echo JText::_('COM_BESEATED_CLUB_BOTTLES_LIST_USER_SIDE_TABLE_SIZE') . ' : ' . $item->size; ?></h4>
				<h4 class="bottle-price"><?php echo JText::_('COM_BESEATED_CLUB_BOTTLES_LIST_USER_SIDE_TABLE_PRICE') . ' : ' . BeseatedHelper::currencyFormat($item->currency_code,$item->currency_sign,$item->price); ?></h4>
			</div>
			<div class="final-price" id="final-price_<?php echo $item->bottle_id;?>"></div>
			<div class="bottle-minus_<?php echo $item->bottle_id;?> bottle_minus" onclick="decrease(<?php echo $item->bottle_id ?>)">minus</div>
			<div class="bottle-qty_<?php echo $item->bottle_id;?> bottle_qty">0</div>
			<div class="bottle-plus_<?php echo $item->bottle_id;?> bottle_plus" onclick="increse(<?php echo $item->bottle_id ?>)">plus</div>
			<div class="bottleid" value="<?php echo $item->bottle_id;?>"></div>
		</div>
		<div class="two_data">
			<div class="total-qty" id="total-qty_<?php echo $item->bottle_id;?>"></div>
			<div class="total-price" id="total-price_<?php echo $item->bottle_id;?>"></div>
	    </div>
	</div>

	<input type="hidden" name="qty"   id="qty_<?php echo $item->bottle_id; ?>" value="0">
	<input type="hidden" name="price" id="price_<?php echo $item->bottle_id; ?>" value="<?php echo $item->price;?>">
	<input type="hidden" name="brandname" id="brandname_<?php echo $item->bottle_id; ?>" value="<?php echo $item->brand_name;?>">
	<input type="hidden" name="currency_sign" id="currency_sign_<?php echo $item->bottle_id; ?>" value="<?php echo $item->currency_sign;?>">
<?php endforeach; ?>
</div>

<div class="table-detail">
	<div class="span12">
	<!-- 	<div class="table-name">
		<h4><?php echo $this->tableDetail->table_name; ?></h4>
	</div>
	<div class="booking-date">
		<h4><?php echo $this->tableBooked->booking_date;?></h4>
	</div>
	<div class="three_blocks">
			<div class="client-count">
				<h4><?php echo $this->tableBooked->total_guest;?>client(s)</h4>
			</div>
			<div class="male-count">
				<h4><span>&nbsp;</span><?php echo $this->tableBooked->male_guest;?></h4>
			</div>
			<div class="female-count">
				<h4><span>&nbsp;</span><?php echo $this->tableBooked->female_guest;?></h4>
			</div>
	</div> -->
		<div class="total-count">
			<h4>Bottle Service Added</h4>
			<div class="main_total">
				<span class="bottle-total-total"></span>
				<div class="bottle-total-price-currency"></div>
				<div class="bottle-total-price"></div>
			</div>
			<span class="min-table-price"></span>
		</div>
	</div>
</div>

<form id="form_venuebottlebooking" class="form-horizontal" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=userbookings&club_id='.$clubID.'&Itemid='.$Itemid);?>">
	<div class="control-group">
		<div class="controls span6">
			<!-- <button onclick="return checkForVenueTableAvaibility();" type="button" class="btn btn-large span"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_TABLE_BOOK_NOW_BUTTON_LABLE'); ?></button> -->
			<button onclick="return checkForQuantity();" type="button" class="btn btn-large span"><?php echo JText::_('Order'); ?></button>
			<input type="hidden" id="venue_table_booking_id" name="venue_table_booking_id" value="<?php echo $this->tableBooked->venue_table_booking_id; ?>">
			<input type="hidden" id="table_id" name="table_id" value="<?php echo $tableId; ?>">
			<input type="hidden" id="task" name="task" value="clubbottlebooking.bookVenueBottle">
			<input type="hidden" id="view" name="view" value="clubguestlist">
			<input type="hidden" id="clubId" name="club_id" value="<?php echo $clubID; ?>">
			<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
			<input type="hidden" id="min_price" name="min_price" value="<?php echo $this->tableDetail->min_price;?>">
			<input type="hidden" id="bottle_id" name="bottle_id" value="">
			<input type="hidden" id="price" name="price" value="">
			<input type="hidden" id="qty" name="qty" value="">
			<input type="hidden" id="total_price" name="total_price" value="">
		</div>
	</div>
</form>

<script type="text/javascript">
var bottleIds         = [];
var bottlePrices      = [];
var bottleFinalPrices = [];
var bottleQuantity    = [];

jQuery(document).ready(function($) {
	jQuery('#alert-error').hide();
	jQuery('.btn-large').attr('disabled', 'true');
});
function increse (id) {
	var bottleQty     = jQuery('.bottle-qty_'+id);
	var qty           = parseInt(bottleQty.text());
	var tableMinPrice = parseInt(jQuery('#min_price').val());
	bottleQty.text(qty+1);
	if (bottleQty.text() > 0){
		var price         = jQuery('#price_'+id).val();
		var brandName     = jQuery('#brandname_'+id).val();
		var currency_sign = jQuery('#currency_sign_'+id).val();
		var finalPrice    = price * bottleQty.text();

		jQuery('#total-qty_'+id).text(brandName + ' X ' + bottleQty.text());
		jQuery('#total-price_'+id).text(currency_sign + finalPrice);
		/*jQuery('#final-price_'+id).text(currency_sign + finalPrice);*/
	};
	/*jQuery('#qty').val(bottleQty.text());
	jQuery('#total_price').val(finalPrice);*/
	if (jQuery.inArray(id, bottleIds) !== -1){
		var index = jQuery.inArray(id, bottleIds);
		bottleIds.splice(index,1);
		bottlePrices.splice(index,1);
		bottleFinalPrices.splice(index,1);
		bottleQuantity.splice(index,1);
	};

	bottleIds.push(id);
	bottlePrices.push(price);
	bottleFinalPrices.push(finalPrice);
	bottleQuantity.push(bottleQty.text());

	jQuery('#bottle_id').val(bottleIds);
	jQuery('#price').val(bottlePrices);
	jQuery('#total_price').val(bottleFinalPrices);
	jQuery('#qty').val(bottleQuantity);

	var sumtotal = 0;
	jQuery.each(bottleFinalPrices,function() {
	    sumtotal += this;
	});

	if (sumtotal == 0){
		jQuery('.bottle-total-price-currency').text(" ");
		jQuery('.bottle-total-price').text(" ");
		jQuery('.bottle-total-total').text(" ");
	}else{
		jQuery('.bottle-total-price-currency').text(currency_sign);
		jQuery('.bottle-total-price').text(sumtotal);
		jQuery('.bottle-total-total').text("Total");
	}

	if (tableMinPrice > sumtotal){
		jQuery('.min-table-price').text('Table Minimum Not Reached');
		jQuery('.min-table-price').css({
			'color': '#FF0000',
			'margin-left': '295px'
		});
		jQuery('.btn-large').attr('disabled', 'true');
	}else{
		jQuery('.min-table-price').text('Table Minimum Reached');
		jQuery('.min-table-price').css({
			'color': '#009900',
			'margin-left': '318px'
		});
		jQuery('.btn-large').removeAttr('disabled');
	}
}

function decrease (id) {
	var bottleQty     = jQuery('.bottle-qty_'+id);
	var qty           = parseInt(bottleQty.text());
	var tableMinPrice = parseInt(jQuery('#min_price').val());
	if (qty != 0){
		bottleQty.text(qty-1);
		if (bottleQty.text() > 0){
			var price         = jQuery('#price_'+id).val();
			var brandName     = jQuery('#brandname_'+id).val();
			var currency_sign = jQuery('#currency_sign_'+id).val();
			var finalPrice    = price * bottleQty.text();

			jQuery('#total-qty_'+id).text(brandName + ' X ' + bottleQty.text());
			jQuery('#total-price_'+id).text(currency_sign + finalPrice);
			/*jQuery('#final-price_'+id).text(currency_sign + finalPrice);*/
		};

		if (jQuery.inArray(id, bottleIds) !== -1){
			var index = jQuery.inArray(id, bottleIds);
			bottleIds.splice(index,1);
			bottlePrices.splice(index,1);
			bottleFinalPrices.splice(index,1);
			bottleQuantity.splice(index,1);
		};

		bottleIds.push(id);
		bottlePrices.push(price);
		bottleFinalPrices.push(finalPrice);
		bottleQuantity.push(bottleQty.text());

		if (jQuery.inArray('0', bottleQuantity) !== -1){
			var removeIndex = jQuery.inArray('0', bottleQuantity);
			bottleIds.splice(removeIndex,1);
			bottlePrices.splice(removeIndex,1);
			bottleFinalPrices.splice(removeIndex,1);
			bottleQuantity.splice(removeIndex,1);
		};

		jQuery('#bottle_id').val(bottleIds);
		jQuery('#price').val(bottlePrices);
		jQuery('#qty').val(bottleQuantity);
		jQuery('#total_price').val(bottleFinalPrices);
	}

	if (bottleQty.text() == 0){
		jQuery('#total-qty_'+id).text(" ");
		jQuery('#total-price_'+id).text(" ");
		/*jQuery('#final-price_'+id).text("");*/
	}

	var sumtotal = 0;
	jQuery.each(bottleFinalPrices,function() {
	    sumtotal += this;
	});

	if (sumtotal == 0){
		jQuery('.bottle-total-price-currency').text(" ");
		jQuery('.bottle-total-price').text(" ");
		jQuery('.bottle-total-total').text(" ");
	}else{
		jQuery('.bottle-total-price-currency').text(currency_sign);
		jQuery('.bottle-total-price').text(sumtotal);
		jQuery('.bottle-total-total').text("Total");
	}

	if (tableMinPrice > sumtotal){
		jQuery('.min-table-price').text('Table Minimum Not Reached');
		jQuery('.min-table-price').css({
			'color': '#FF0000',
			'margin-left': '295px'
		});
		jQuery('.btn-large').attr('disabled', 'true');
	}else{
		jQuery('.min-table-price').text('Table Minimum Reached');
		jQuery('.min-table-price').css({
			'color': '#009900',
			'margin-left': '318px'
		});
		jQuery('.btn-large').removeAttr('disabled');
	}


	/*jQuery('#qty').val(bottleQty.text());
	jQuery('#total_price').val(finalPrice);*/
}

function checkForQuantity()
{
	var quantity       = jQuery('#qty').val();

	if (quantity){
		jQuery('#alert-error').hide();
	    jQuery('#form_venuebottlebooking').submit();
	}else{
		jQuery('#alert-error').show();
		jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Select bottle Quantity</h4>');
		return false;
	}
}
</script>

