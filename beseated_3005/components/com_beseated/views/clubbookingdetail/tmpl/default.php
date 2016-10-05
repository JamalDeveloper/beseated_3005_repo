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
    jQuery(function(){
        jQuery('#system-message').hide();
    });
</script>

<div class="table-wrp">
    <div class="error-msg" id="error-msg"></div>
    <div class="row-fluid book-dtlwrp">
        <div class="span10">
            <div class="booking-inner-details">
                <div class="booking-inner-name booking-inner">
                    <?php echo ucfirst($this->booking->full_name);?>
                </div>
                <div class="booking-inner-service-name booking-inner">
                    <?php echo ucfirst($this->booking->table_name);?>
                </div>
                <div class="booking-inner-date booking-inner">
                    <?php echo BeseatedHelper::convertDateFormat($this->booking->booking_date); ?>
                </div>
                <div class="booking-inner-total-guest">
                     <?php echo 'Guests&nbsp;' . $this->booking->total_guest; ?>
                </div>
                <div class="male-female-guest">
                    <div class="booking-inner-male-guest">
                         <?php echo $this->booking->male_guest; ?>
                    </div>
                    <div class="booking-inner-female-guest">
                         <?php echo $this->booking->female_guest; ?>
                    </div>
                </div>
                <div class="booking-inner-price booking-inner">
                    <span class="amount-title">Min. Spend</span>
                    <span class="amount">
                        <?php echo BeseatedHelper::currencyFormat($this->booking->booking_currency_code,$this->booking->booking_currency_sign,$this->booking->min_price);?>
                    </span>
                </div>
                <?php if ($this->booking->venue_status == 13 && $this->booking->is_bill_posted == 0):?>
                    <div class="set-bill-amount">
                        <label>Bill Amount</label>
                        <input type="text" id="set-bill" class="set-bill" id="set-bill">
                    </div>
                    <div class="post-bill-button">
                        <button class="post-bill-btn" id="post-bill-btn">Post Bill</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    jQuery('#error-msg').hide();
    var minSpend  = '<?php echo $this->booking->min_price;?>';
    var bookingID = '<?php echo $this->booking->venue_table_booking_id;?>';
    jQuery('#post-bill-btn').click(function(event) {
        var postBillAmount = jQuery('#set-bill').val();
        if (postBillAmount < minSpend){
            jQuery('#error-msg').html("Please Enter Amount Greater than " + minSpend);
            jQuery('#error-msg').show();
        }else{
            jQuery.ajax({
                type: "GET",
                url: "index.php?option=com_beseated&task=clubbookingsdetail.postBill",
                data: "&postbill_amount="+postBillAmount+"&booking_id="+bookingID,
                success: function(response){
                    if(response == "200")
                    {

                        jQuery('#error-msg').html('Amount Send Successfully');
                        jQuery('#error-msg').show();

                        jQuery('.set-bill-amount').hide();
                        jQuery('.post-bill-button').hide();
                    }

                    if(response == "400")
                    {
                        jQuery('#error-msg').html('Amount not Send Successfully');
                        jQuery('#error-msg').show();
                    }
                }
            });
        }
    });
});
</script>
