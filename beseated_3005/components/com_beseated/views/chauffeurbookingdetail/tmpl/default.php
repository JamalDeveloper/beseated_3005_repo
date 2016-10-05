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
    <div id="system-message">
        <div class="alert alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h4><?php echo JText::_('COM_BCTED_USERBOOKINGS_NO_BOOKING_FOUND_GENERAL_TITLE'); ?></h4>
            <div><p><?php echo JText::_('COM_BCTED_USERBOOKINGS_NO_BOOKING_FOUND_FOR_GENERAL_DESC'); ?></p></div>
        </div>
    </div>
    <div class="row-fluid book-dtlwrp">
    	<div class="span10">
            <div class="booking-inner-details">
                <div class="booking-inner-name booking-inner">
                    <?php echo ucfirst($this->booking->full_name);?>
                </div>
                <div class="booking-inner-service-name booking-inner">
                    <?php echo ucfirst($this->booking->service_name);?>
                </div>
                <div class="booking-inner-date booking-inner">
                    <?php echo date('d M Y',strtotime($this->booking->booking_date)); ?>
                </div>
                <div class="booking-inner-time booking-inner">
                    <?php echo BeseatedHelper::convertToHM($this->booking->booking_time); ?>
                </div>
                <div class="booking-inner-pickup booking-inner">
                    <?php echo $this->booking->pickup_location; ?>
                </div>
                <div class="booking-inner-drop booking-inner">
                    <?php echo $this->booking->dropoff_location; ?>
                </div>
                <div class="booking-inner-price booking-inner">
                    <span class="amount-title">Total Amount</span>
                    <span class="amount">
                        <?php echo BeseatedHelper::currencyFormat($this->booking->booking_currency_code,$this->booking->booking_currency_sign,$this->booking->total_price);?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
