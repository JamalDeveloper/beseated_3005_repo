<?php
/**
 * @package     Beseated.Site
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

jQuery(document).ready(function($) {
	jQuery('#alert-error').hide();

	jQuery('.bottle-plus').click(function(event) {
		var bottleQty = jQuery('.bottle-qty');
		var qty       = parseInt(bottleQty.text());
		bottleQty.text(qty+1);
		if (bottleQty.text() > 0){
			var price         = '<?php echo $this->bottleDetail->price; ?>';
			var brandName     = '<?php echo $this->bottleDetail->brand_name; ?>';
			var currency_sign = '<?php echo $this->venueDetail->currency_sign; ?>';
			var finalPrice    = price * bottleQty.text();

			jQuery('.total-qty').text(brandName + ' X ' + bottleQty.text());
			jQuery('.total-price').text(currency_sign + finalPrice);
			jQuery('.final-price').text(currency_sign + finalPrice);
		};
		jQuery('#qty').val(bottleQty.text());
		jQuery('#total_price').val(finalPrice);
	});

	jQuery('.bottle-minus').click(function(event) {
		var bottleQty = jQuery('.bottle-qty');
		var qty       = parseInt(bottleQty.text());
		if (qty != 0){
			bottleQty.text(qty-1);
			if (bottleQty.text() > 0){
				var price         = '<?php echo $this->bottleDetail->price; ?>';
				var brandName     = '<?php echo $this->bottleDetail->brand_name; ?>';
				var currency_sign = '<?php echo $this->venueDetail->currency_sign; ?>';
				var finalPrice    = price * bottleQty.text();

				jQuery('.total-qty').text(brandName + ' X ' + bottleQty.text());
				jQuery('.total-price').text(currency_sign + finalPrice);
				jQuery('.final-price').text(currency_sign + finalPrice);
			};
		}

		if (bottleQty.text() == 0){
			jQuery('.total-qty').text(" ");
			jQuery('.total-price').text(" ");
			jQuery('.final-price').text("");
		}

		jQuery('#qty').val(bottleQty.text());
		jQuery('#total_price').val(finalPrice);
	});
});
function checkForQuantity()
{
	var quantity       = jQuery('#qty').val();

	if (quantity == 0)
	{
		jQuery('#alert-error').show();
		jQuery('#alert-error').html('<a class="close" data-dismiss="alert">×</a><h4>Select bottle Quantity</h4>');
		return false;
	}
	else
	{
		jQuery('#alert-error').hide();
	    jQuery('#form_venuebottlebooking').submit();
	}

}
</script>



<div id="alert-error" class="alert alert-error"></div>
<?php if (count($this->bookedTable) == 0):?>
	<h2 style="color: white; margin-top: 200px;">You have not Booked any table please first book table.</h2>
<?php else:?>
<div class="guest-club-wrp">
	<div class="inner-guest-wrp">
		<div class="bottle-detail">
			<div class="control-group">
				<div class="controls span6">
					<div class="bootle-type">
						<?php echo ucfirst($this->bottleDetail->bottle_type); ?>
					</div>
					<div class="bootle-image">
						<img src="<?php echo JURI::Root().'images/beseated/'.$this->bottleDetail->image; ?>"  style="margin:3px;" width="200px" height="200px">
					</div>
					<div class="brand-name">
						<?php echo ucfirst($this->bottleDetail->brand_name); ?>
					</div>
					<div class="bottle-size">
						<?php echo $this->bottleDetail->size; ?>
					</div>
					<div class="bottle-price">
						<?php echo BeseatedHelper::currencyFormat($this->venueDetail->currency_code,$this->venueDetail->currency_sign,$this->bottleDetail->price); ?>
					</div>
				</div>
			</div>
		</div>
		<form id="form_venuebottlebooking" class="form-horizontal" method="post" action="<?php echo JRoute::_('index.php?option=com_beseated&view=userbookings&club_id='.$this->clubID.'&Itemid='.$Itemid);?>">
			<div class="control-group bottle-qty-main">
				<div class="controls span6" >
					<div class="bottle-qty-inner">
						<div class="bottle-minus">minus</div>
						<div class="bottle-qty">0</div>
						<div class="bottle-plus">plus</div>
						<div class="total-qty"></div>
						<div class="total-price"></div>
						<div class="final-price"></div>
						<input type="hidden" id="qty" name="qty" value="0">
						<input type="hidden" id="price" name="price" value="<?php echo  $this->bottleDetail->price;?>">
						<input type="hidden" id="total_price" name="total_price" value="0">
					</div>
				</div>
			</div>
			<div class="control-group">
				<div class="controls span6">
					<!-- <button onclick="return checkForVenueTableAvaibility();" type="button" class="btn btn-large span"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_TABLE_BOOK_NOW_BUTTON_LABLE'); ?></button> -->
					<button onclick="return checkForQuantity();" type="button" class="btn btn-large span"><?php echo JText::_('COM_BESEATED_CLUBTABLEBOOKIN_TABLE_BOOK_NOW_BUTTON_LABLE'); ?></button>
					<input type="hidden" id="venue_table_booking_id" name="venue_table_booking_id" value="<?php echo $this->bookedTable[0]->venue_table_booking_id; ?>">
					<input type="hidden" id="table_id" name="table_id" value="<?php echo $this->bookedTable[0]->table_id; ?>">
					<input type="hidden" id="bottle_id" name="bottle_id" value="<?php echo $this->bottleDetail->bottle_id; ?>">
					<input type="hidden" id="task" name="task" value="clubbottlebooking.bookVenueBottle">
					<input type="hidden" id="view" name="view" value="clubguestlist">
					<input type="hidden" id="Itemid" name="Itemid" value="<?php echo $Itemid; ?>">
				</div>
			</div>
		</form>
	</div>
</div>
<?php endif; ?>
