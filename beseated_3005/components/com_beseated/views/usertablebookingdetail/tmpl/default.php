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

$input         = JFactory::getApplication()->input;
$currentItemid = $input->get('Itemid', 0, 'int');
$this->user    = JFactory::getUser();
$this->isRoot  = $this->user->authorise('core.admin');
$defaultPath   =  JUri::base();
$document      = JFactory::getDocument();
$document->addStyleSheet($defaultPath.'components/com_bcted/assets/tag-it//bootstrap/bootstrap-tagsinput.css');
$document->addScript($defaultPath.'components/com_bcted/assets/tag-it/bootstrap/bootstrap-tagsinput.js');
?>
<script type="text/javascript">
	function removeVenuePastBooking(bookingID)
	{
		jQuery.ajax({
			url: 'index.php?option=com_bcted&task=clubbookings.deletePastBooking',
			type: 'GET',
			data: '&user_type=user&booking_id='+bookingID,
			success: function(response){
	        	if(response == "200")
	        	{
	        		window.location = "index.php?option=com_bcted&view=userbookings&Itemid=<?php echo $currentItemid; ?>";
	        	}
	        }
		})
		.done(function() {
		})
		.fail(function() {
		})
		.always(function() {
		});
	}
</script>
<?php
	$app = JFactory::getApplication();
	$menu = $app->getMenu();
?>
<?php
	$fromTime = explode(":", $this->booking->booking_from_time);
	$toTime = explode(":", $this->booking->booking_to_time);
?>
<div class="table-wrp">
    <div class="row-fluid book-dtlwrp">
    	<div class="span6">
	    	<?php if($this->booking->user_status == 2 || $this->booking->user_status == 5  || $this->booking->user_status == 4  || $this->booking->user_status == 7  || $this->booking->user_status == 10 || $this->booking->user_status == 11): ?>
				<h4><b>Club Name : </b><?php echo $this->booking->venue_name; ?></h4>
				<h4><b>Table Name : </b><?php echo ($this->booking->premium_table_id)?$this->booking->venue_table_name:$this->booking->custom_table_name; ?></h4>
				<h4><b>Price : </b><?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$this->booking->venue_table_price); ?></h4>
				<h4><b>Date : </b><?php echo date('d-m-Y',strtotime($this->booking->venue_booking_datetime)); ?></h4>
				<h4><b>From : </b><?php echo $fromTime[0].':'.$fromTime[1]; ?></h4>
				<h4><b>To : </b><?php echo $toTime[0].':'.$toTime[1]; ?></h4>
				<h4><b>Status : </b><?php echo $this->booking->user_status_text; ?></h4>
			<?php elseif($this->booking->user_status == 3): ?>
				<h4><?php echo ucfirst($this->booking->venue_name); ?> has confirmed your booking for <?php echo ($this->booking->premium_table_id)?ucfirst($this->booking->venue_table_name):ucfirst($this->booking->custom_table_name); ?> </h4>
	            <h4>The total price for this is <?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$this->booking->venue_table_price); ?></h4>
	            <h4>To secure your booking, you will need to pay a deposit.</h4>
	            <h4>Remaining amount to be paid to venue.</h4>
			<?php endif; ?>
        </div>
        <div class="bk-dtl-list">
            <div class="span6 bk-status">
                Status: <?php echo $this->booking->user_status_text; ?>
            </div>
            <div class="span6 bk-dtl-img">
                <img src="<?php echo ($this->booking->venue_table_image)?JUri::base().$this->booking->venue_table_image:'images/tabl-img.jpg'; ?>">
            </div>
        </div>
    </div>
    <div class="booklst-tbl">
         <div class="span12">
         	<?php if($this->booking->user_status == 3): ?>
	            <form method="post" accept="index.php?option=com_bcted&view=userbookings">
	           		<?php $depositePer = ($this->booking->commission_rate)?$this->booking->commission_rate:$this->bctConfig->deposit_per; ?>
	            	<?php $diposit = (($this->booking->venue_table_price * $depositePer)/100); ?>
	            	<?php
			        		$menuItem = $menu->getItems( 'link', 'index.php?option=com_bcted&task=packageorder.packagePurchased', true );
							$Itemid = $menuItem->id;
						?>
					<?php if($this->bctConfig->auto_approve ==1): ?>
						<a href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->venue_booking_id; ?>&booking_type=venue&auto_approve=1">
		                	<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$diposit); ?></button>
		                </a>
					<?php else: ?>
						<a href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->venue_booking_id; ?>&booking_type=venue">
	                	<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$diposit); ?></button>
	                </a>
					<?php endif; ?>
	                <?php $usdRate = BctedHelper::convertCurrencyGoogle(1,$this->booking->currency_code,'USD'); ?>
	                <?php $currencyRate = BctedHelper::convertCurrencyGoogle(1,'USD',$this->booking->currency_code); ?>
	                <?php if($this->loyaltyPoints['totalPoint']>=10): ?>
		                <?php $totalPoint = floor($this->loyaltyPoints['totalPoint']); ?>
		                <?php $roundedPoint = ($totalPoint-($totalPoint%10)); ?>
		                <?php $bcDollers = $roundedPoint/10; ?>
		                <?php $bcDollersInCurrencyCode = $bcDollers * $currencyRate; ?>
		                <?php

		                if($bcDollersInCurrencyCode > $diposit)
		                {
		                	$bcdUsed = $diposit * 10;
		                	$amoutWithPoint = 0;
		                }
		                else
		                {
		                	$bcdUsed = $roundedPoint;
		                	$amoutWithPoint = ($diposit - $bcDollersInCurrencyCode);
		                }
						?>
						<br /><br />
						<h4>Or, save money and use your points</h4>
						<br />

		               	<?php
			        		$menuItem = $menu->getItems( 'link', 'index.php?option=com_bcted&task=packageorder.packagePurchased', true );
							$Itemid = $menuItem->id;
						?>

						<?php if($this->bctConfig->auto_approve ==1): ?>
							<a href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&bc_dollars=<?php echo $bcdUsed; ?>&booking_id=<?php echo $this->booking->venue_booking_id; ?>&booking_type=venue&auto_approve=1">
								<?php if($amoutWithPoint): ?>
									<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$amoutWithPoint) . ' & ' . $bcdUsed.' Points'; ?></button>
								<?php else: ?>
									<button type="button" class="btn btn-primary"><?php echo $bcdUsed.' Points'; ?></button>
								<?php endif; ?>
			                </a>
						<?php else: ?>
							<a href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&bc_dollars=<?php echo $bcdUsed; ?>&booking_id=<?php echo $this->booking->venue_booking_id; ?>&booking_type=venue">
							<?php if($amoutWithPoint): ?>
			                	<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$amoutWithPoint) . ' & ' . $bcdUsed.' Points'; ?></button>
			                <?php else: ?>
			                	<button type="button" class="btn btn-primary">Pay <?php echo $bcdUsed.' Points'; ?></button>
			                <?php endif; ?>

			                </a>
						<?php endif; ?>
	            	<?php endif; ?>
						<?php if($this->booking->user_status == 3): ?>
							<a href="index.php?option=com_bcted&task=userbookings.cancelBooking&bookingType=venue&booking_id=<?php echo $this->booking->venue_booking_id; ?>&Itemid=<?php echo $currentItemid; ?>">
								<button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_CANCEL_BUTTON_TEXT'); ?></button>
							</a>
						<?php endif; ?>
	            </form>
	        <?php elseif($this->booking->user_status == 2 || $this->booking->user_status == 5  || $this->booking->user_status == 4  || $this->booking->user_status == 7  || $this->booking->user_status == 10 || $this->booking->user_status == 11): ?>
				<?php
					$menuItem = $menu->getItems( 'link', 'index.php?option=com_bcted&view=clubinformation', true );
					$Itemid = $menuItem->id;
				?>
				<a href="index.php?option=com_bcted&view=clubinformation&club_id=<?php echo $this->booking->venue_id; ?>&Itemid=<?php echo $Itemid; ?>">
					<button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_VIEW_PROFILE_BUTTON_TEXT'); ?></button>
				</a>

				<?php if($this->booking->user_status == 11): ?>
					<button type="button" onclick="removeVenuePastBooking(<?php echo $this->booking->venue_booking_id; ?>)" class="btn btn-primary"><?php echo JText::_('COM_BCTED_VIEW_DELETE_BUTTON_TEXT'); ?></button>
				<?php endif; ?>
				<?php
					$bookingTS = $this->booking->venue_booking_datetime.' '.$this->booking->booking_from_time;
					$before24 = strtotime("-24 hours", strtotime($bookingTS));
					$currentTime = time();
					if(($currentTime<=$before24 && $this->booking->status == 5) || $this->booking->user_status == 2 )
					{
						?>
						<a href="index.php?option=com_bcted&task=userbookings.cancelBooking&bookingType=venue&booking_id=<?php echo $this->booking->venue_booking_id; ?>&Itemid=<?php echo $currentItemid; ?>">
						<button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_CANCEL_BUTTON_TEXT'); ?></button>
						</a>
						<?php
					}
				?>
        	<?php endif; ?>
        </div>
    </div>
</div>
<script type="text/javascript">
	jQuery('#invite_user').on('beforeItemAdd', function(event) {
	    var pattern = new RegExp(/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i);
	    if(pattern.test(event.item))
	    {
	    }
	    else
	    {
	    	event.cancel=true;
	    }
	});
</script>