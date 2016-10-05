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
$document->addStyleSheet($defaultPath.'components/com_beseated/assets/tag-it//bootstrap/bootstrap-tagsinput.css');
$document->addScript($defaultPath.'components/com_beseated/assets/tag-it/bootstrap/bootstrap-tagsinput.js');
$document->addScript(Juri::root(true) . '/components/com_beseated/assets/confirm-js/jquery.confirm.js');
?>
<script type="text/javascript">
	function removeVenuePastBooking(bookingID)
	{
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=clubbookings.deletePastBooking',
			type: 'GET',
			data: 'booking_type=venue&user_type=user&booking_id='+bookingID,
			success: function(response){
	        	if(response == "200")
	        	{
	        		window.location = "index.php?option=com_beseated&view=userbookings&Itemid=<?php echo $currentItemid; ?>";
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

	function removePackagePastBooking(bookingID)
	{
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=clubbookings.deletePastBooking',
			type: 'GET',
			data: 'booking_type=package&user_type=user&booking_id='+bookingID,
			success: function(response){
	        	if(response == "200")
	        	{
	        		window.location = "index.php?option=com_beseated&view=userbookings&type=packages&Itemid=<?php echo $currentItemid; ?>";
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

	function removeCompanyPastBooking(bookingID)
	{
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=companybookings.deletePastBooking',
			type: 'GET',
			data: '&user_type=user&booking_id='+bookingID,
			success: function(response){
	        	if(response == "200")
	        	{
	        		window.location = "index.php?option=com_beseated&view=userbookings&type=companyservices&Itemid=<?php echo $currentItemid; ?>";
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

	function refundBooking(bookingID,bookingType)
	{
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=userbookings.refundBooking_ajax',
			type: 'GET',
			data: '&booking_type='+bookingType+'&booking_id='+bookingID,
			success: function(response){
	        	if(response == "200")
	        	{
	        		if(bookingType == 'service')
	        		{
	        			window.location = "index.php?option=com_beseated&view=userbookings&type=companyservices&Itemid=<?php echo $currentItemid; ?>";
	        		}
	        		else
	        		{
		        		window.location = "index.php?option=com_beseated&view=userbookings&Itemid=<?php echo $currentItemid; ?>";
	        		}
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

	function cancelBooking(bookingID,bookingType)
	{
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=userbookings.cancelBooking_ajax',
			type: 'GET',
			data: '&booking_type='+bookingType+'&booking_id='+bookingID,
			success: function(response){
	        	if(response == "200")
	        	{
	        		if(bookingType == 'service')
	        		{
	        			window.location = "index.php?option=com_beseated&view=userbookings&type=companyservices&Itemid=<?php echo $currentItemid; ?>";
	        		}
	        		if(bookingType == 'package')
	        		{
	        			window.location = "index.php?option=com_beseated&view=userbookings&type=pacakges&Itemid=<?php echo $currentItemid; ?>";
	        		}
	        		else
	        		{
	        			window.location = "index.php?option=com_beseated&view=userbookings&Itemid=<?php echo $currentItemid; ?>";
	        		}

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

	function cancelPackageBooking(bookingID)
	{
		jQuery.ajax({
			url: 'index.php?option=com_beseated&task=userbookings.cancelPacakgeBooking_ajax',
			type: 'GET',
			data: '&package_booking_id='+bookingID,
			success: function(response){
				console.log(response);
	        	if(response == "200")
	        	{
	        		window.location = "index.php?option=com_beseated&view=userbookings&type=packages&Itemid=<?php echo $currentItemid; ?>";
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
	$app  = JFactory::getApplication();
	$menu = $app->getMenu();
?>
<?php if($this->bookingType == 'service'): ?>
<div class="table-wrp">
	<div class="row-fluid book-dtlwrp">
		<div class="span6">
			<?php
				$fromTime = explode(":", $this->booking->booking_from_time);
				$toTime = explode(":", $this->booking->booking_to_time);
			?>
			<?php if($this->booking->user_status == 2 || $this->booking->user_status == 5 || $this->booking->user_status == 4 || $this->booking->user_status == 7 || $this->booking->user_status == 10 || $this->booking->user_status == 11): ?>
				<h4><b>Company Name : </b><?php echo $this->booking->company_name; ?></h4>
				<h4><b>Service Name : </b><?php echo $this->booking->service_name; ?></h4>
				<h4><b>Price : </b><?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$this->booking->service_price) . '/hr'; ?> </h4>
				<h4><b>Date : </b><?php echo date('d-m-Y',strtotime($this->booking->service_booking_datetime)); ?></h4>
				<h4><b>From : </b><?php echo $fromTime[0].':'.$fromTime[1]; ?></h4>
				<h4><b>To : </b><?php echo $toTime[0].':'.$toTime[1]; ?></h4>
				<h4><b>Location : </b><?php echo $this->booking->service_location; ?></h4>
				<h4><?php echo '<b>'.$this->booking->service_booking_number_of_guest.' People Attending ('.$this->booking->male_count.' M / '.$this->booking->female_count.' F)</b>'; ?></h4>
			<?php elseif($this->booking->user_status == 3): ?>
				<h4><?php echo $this->booking->company_name; ?> has confirmed your booking for <?php echo $this->booking->service_name; ?> </h4>
				<h4>The total price for this is <?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$this->booking->total_price); ?></h4>
			<?php endif; ?>
		</div>
		<div class="bk-dtl-list">
			<div class="span6 bk-status">
				Status: <?php echo $this->booking->user_status_text; ?>
			</div>
			<!-- <div class="span6 bk-dtl-img">
				<img src="<?php echo ($this->booking->service_image)?JUri::base().$this->booking->service_image:'images/bcted/default/banner.png'; ?>">
			</div> -->
		</div>
	</div>
	<div class="booklst-tbl">
		<div class="span12">
			<?php if($this->booking->user_status == 3 || $this->booking->user_status == 12): ?>
				<?php $diposit = $this->booking->deposit_amount; ?>
				<form method="post" accept="index.php?option=com_beseated&view=userbookings">
					<?php if($this->bctConfig->auto_approve ==1): ?>
						<a href="index.php?option=com_beseated&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->service_booking_id; ?>&booking_type=service&auto_approve=1">
							<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$this->booking->deposit_amount); ?></button>
						</a>
					<?php else: ?>
						<a href="index.php?option=com_beseated&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->service_booking_id; ?>&booking_type=service">
							<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$this->booking->deposit_amount); ?></button>
						</a>
					<?php endif; ?>
					<?php $usdRate = BctedHelper::convertCurrencyGoogle(1,$this->booking->currency_code,'USD'); ?>
					<?php $currencyRate = BctedHelper::convertCurrencyGoogle(1,'USD',$this->booking->currency_code); ?>
					<?php if($this->loyaltyPoints['totalPoint']>=10): ?>
						<?php $totalPoint = floor($this->loyaltyPoints['totalPoint']); ?>
						<?php $roundedPoint = ($totalPoint-($totalPoint%10)); ?>
						<?php $bcDollers = $roundedPoint/10; ?>
						<?php $bcDollersInCurrencyCode = $bcDollers * $currencyRate; ?>
						<?php $amoutWithPoint = ($this->booking->deposit_amount - $bcDollersInCurrencyCode); ?>
						<?php if($bcDollersInCurrencyCode > $this->booking->deposit_amount)
						{
							$depositInUSD = $this->booking->deposit_amount * $usdRate;
							$bcdUsed = $depositInUSD * 10;
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
						<?php if($this->bctConfig->auto_approve ==1): ?>
							<a href="index.php?option=com_beseated&view=dummypayment&task=packageorder.packagePurchased&bc_dollars=<?php echo $roundedPoint; ?>&booking_id=<?php echo $this->booking->service_booking_id; ?>&booking_type=service&auto_approve=1">
								<?php if($amoutWithPoint): ?>
									<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$amoutWithPoint) . ' & ' . $bcdUsed.' Points'; ?></button>
								<?php else: ?>
									<button type="button" class="btn btn-primary"><?php echo $bcdUsed.' Points'; ?></button>
								<?php endif; ?>
							</a>
						<?php else: ?>
							<a href="index.php?option=com_beseated&view=dummypayment&task=packageorder.packagePurchased&bc_dollars=<?php echo $roundedPoint; ?>&booking_id=<?php echo $this->booking->service_booking_id; ?>&booking_type=service">
								<?php if($amoutWithPoint): ?>
									<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$amoutWithPoint) . ' & ' . $bcdUsed.' Points'; ?></button>
								<?php else: ?>
									<button type="button" class="btn btn-primary"><?php echo $bcdUsed.' Points'; ?></button>
								<?php endif; ?>
							</a>
						<?php endif; ?>
						<?php
							$bookingTS = $this->booking->service_booking_datetime.' '.$this->booking->booking_from_time;
							$before24 = strtotime("-24 hours", strtotime($bookingTS));
							$currentTime = time();
						?>
						<?php if($currentTime<=$before24): ?>
						<?php endif; ?>
					<?php endif; ?>
					<a id="cancelConfirmServiceBooking" href="#">
						<button type="button" class="btn btn-primary"><?php echo JText::_('com_beseated_CANCEL_BUTTON_TEXT'); ?></button>
					</a>
					<script type="text/javascript">
                        jQuery("#cancelConfirmServiceBooking").click(function() {
                           jQuery.confirm({
                                title: "Cancel",
                                text: "Are you sure you want to cancel this booking? This action cannot be undone.",
                                confirm: function() {
                                    cancelBooking(<?php echo $this->booking->service_booking_id; ?>,'service');
                                },
                                cancel: function() {

                                }
                            });
                        });
                    </script>
				</form>
			<?php elseif($this->booking->user_status == 2 || $this->booking->user_status == 5 || $this->booking->user_status == 4  || $this->booking->user_status == 7 || $this->booking->user_status == 10 || $this->booking->user_status == 11): ?>
				<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=companyinformation', true ); ?>
				<?php $Itemid = $menuItem->id; ?>
				<a href="index.php?option=com_beseated&view=companyinformation&company_id=<?php echo $this->booking->company_id; ?>&Itemid=<?php echo $Itemid; ?>">
					<button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_VIEW_PROFILE_BUTTON_TEXT'); ?></button>
				</a>
				<?php if($this->booking->user_status == 11): ?>
					<button type="button" id="dataConfirmService1_<?php echo $this->booking->service_booking_id; ?>" class="btn btn-primary"><?php echo JText::_('COM_BCTED_VIEW_DELETE_BUTTON_TEXT'); ?></button>
					<script type="text/javascript">
                        jQuery("#dataConfirmService1_<?php echo $this->booking->service_booking_id; ?>").click(function() {
                           jQuery.confirm({
                                title: "Delete Booking",
                                text: "Are you sure you want to delete this booking? This action cannot be undone.",
                                confirm: function() {
                                    removeCompanyPastBooking(<?php echo $this->booking->service_booking_id; ?>)
                                },
                                cancel: function() {
                                }
                            });
                        });
                    </script>
				<?php endif; ?>
				<?php $timestampOfBooking = strtotime($this->booking->service_booking_datetime.' '.$this->booking->booking_from_time); ?>
				<?php $currentTimeStamp = time(); ?>
				<?php if($this->booking->user_status == 5 && $currentTimeStamp>$timestampOfBooking): ?>
					<button type="button" id="dataConfirmService2_<?php echo $this->booking->service_booking_id; ?>" class="btn btn-primary"><?php echo JText::_('COM_BCTED_VIEW_DELETE_BUTTON_TEXT'); ?></button>
					<script type="text/javascript">
                        jQuery("#dataConfirmService2_<?php echo $this->booking->service_booking_id; ?>").click(function() {
                           jQuery.confirm({
                                title: "Delete Booking",
                                text: "Are you sure you want to delete this booking? This action cannot be undone.",
                                confirm: function() {
                                    removeCompanyPastBooking(<?php echo $this->booking->service_booking_id; ?>)
                                },
                                cancel: function() {
                                }
                            });
                        });
                    </script>
                <?php elseif($this->booking->user_status == 4): ?>
					<button type="button" id="dataConfirmService3_<?php echo $this->booking->service_booking_id; ?>" class="btn btn-primary"><?php echo JText::_('COM_BCTED_VIEW_DELETE_BUTTON_TEXT'); ?></button>
					<script type="text/javascript">
                        jQuery("#dataConfirmService3_<?php echo $this->booking->service_booking_id; ?>").click(function() {
                           jQuery.confirm({
                                title: "Delete",
                                text: "Are you sure you want to delete this booking? This action cannot be undone.",
                                confirm: function() {
                                    removeCompanyPastBooking(<?php echo $this->booking->service_booking_id; ?>)
                                },
                                cancel: function() {
                                }
                            });
                        });
                    </script>
				<?php endif; ?>
				<?php $bookingTS = $this->booking->service_booking_datetime.' '.$this->booking->booking_from_time; ?>

				<?php $before24 = strtotime("-24 hours", strtotime($bookingTS)); ?>
				<?php $currentTime = time(); ?>
				<?php if($currentTime<=$before24 && $this->booking->status == 5): ?>
					<a id="confirmRefund_Service" href="#">
						<button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_REFUND_REQUEST_BUTTON_TEXT'); ?></button>
					</a>
					<script type="text/javascript">
                        jQuery("#confirmRefund_Service").click(function() {
                           jQuery.confirm({
                                title: "Refund request",
                                text: "Are you sure you want to refund this booking? This action cannot be undone.",
                                confirm: function() {
                                    refundBooking(<?php echo $this->booking->service_booking_id; ?>,'service');
                                },
                                cancel: function() {
                                }
                            });
                        });
                    </script>
				<?php endif; ?>
				<?php if($this->booking->status == 5): ?>
					<form method="post" action="index.php?option=com_bcted&task=userbookings.invitetomybookedservice&bookingType=company">
						<div class="span12 package-invite">
							<input type="text" name="invite_user" placeholder="Enter emails" id="invite_user" value="" data-role="tagsinput" />
							<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myFacebookFriendsModal"><?php echo JText::_('Invite Facebook Friends'); ?></button>
						</div>
						<input type="hidden" name="booking_id" id="booking_id" value="<?php echo $this->booking->service_booking_id; ?>">
						<input type="hidden" name="Itemid" value="<?php echo $currentItemid; ?>" id="Itemid">
						<input type="hidden" name="fbids" value="" id="fbids">
						<button type="submit" class="btn btn-primary"><?php echo JText::_('COM_BCTED_INVITE_USER'); ?></button>
					</form>
				<?php endif; ?>
				<?php if(($currentTime>=$before24 && $this->booking->status == 5)): ?>
					<a id="cancelConfirmServiceBooking" href="#">
					    <button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_CANCEL_BUTTON_TEXT'); ?></button>
					</a>
					<script type="text/javascript">
                        jQuery("#cancelConfirmServiceBooking").click(function() {
                           jQuery.confirm({
                                title: "Cancel",
                                text: "Are you sure you want to cancel this booking? If you cancel the booking at this time, no refunds will be possible as per company policies",
                                confirm: function() {
                                    cancelBooking(<?php echo $this->booking->service_booking_id; ?>,'service');
                                },
                                cancel: function() {
                                }
                            });
                        });
                    </script>
                <?php elseif($this->booking->status == 1): ?>
                	<a id="cancelConfirmServiceBooking" href="#">
					    <button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_CANCEL_BUTTON_TEXT'); ?></button>
					</a>
					<script type="text/javascript">
                        jQuery("#cancelConfirmServiceBooking").click(function() {
                           jQuery.confirm({
                                title: "Cancel",
                                text: "Are you sure you want to cancel this booking? This action cannot be undone.",
                                confirm: function() {
                                    cancelBooking(<?php echo $this->booking->service_booking_id; ?>,'service');
                                },
                                cancel: function() {
                                }
                            });
                        });
                    </script>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php elseif($this->bookingType == 'table'): ?>
	<?php
		//$fromTime = explode(":", $this->booking->booking_from_time);
		//$toTime = explode(":", $this->booking->booking_to_time);
	?>
<div class="table-wrp">
	<div class="row-fluid book-dtlwrp">
		<div class="span6">
		<?php if($this->booking->user_status == 2 || $this->booking->user_status == 5  || $this->booking->user_status == 4  || $this->booking->user_status == 7  || $this->booking->user_status == 10 || $this->booking->user_status == 11): ?>
			<h4><b>Club Name : </b><?php echo $this->booking->venue_name; ?></h4>
			<h4><b>Table Name : </b><?php echo $this->booking->table_name; ?></h4>
			<h4><b>Price : </b><?php echo BeseatedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$this->booking->min_price); ?></h4>
			<h4><b>Date : </b><?php echo date('d-m-Y',strtotime($this->booking->booking_date)); ?></h4>
			<!-- <h4><b>From : </b><?php echo $fromTime[0].':'.$fromTime[1]; ?></h4> -->
			<!-- <h4><b>To : </b><?php echo $toTime[0].':'.$toTime[1]; ?></h4> -->
			<h4><?php echo '<b>'.$this->booking->total_guest.' People Attending ('.$this->booking->male_guest.' M / '.$this->booking->female_guest.' F)</b>'; ?></h4>
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
				<img src="<?php echo ($this->booking->image)?JUri::base().'images/beseated/'.$this->booking->image:'images/beseated/default/banner.png'; ?>">
			</div>
		</div>
	</div>
	<div class="booklst-tbl">
		<div class="span12">
			<?php if($this->booking->user_status == 3): ?>
				<form method="post" accept="index.php?option=com_beseated&view=userbookings">
					<?php $depositePer = ($this->booking->commission_rate)?$this->booking->commission_rate:$this->bctConfig->deposit_per; ?>
					<?php $diposit = (($this->booking->venue_table_price * $depositePer)/100); ?>
					<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&task=packageorder.packagePurchased', true ); ?>
					<?php $Itemid = $menuItem->id; ?>
					<?php if($this->bctConfig->auto_approve ==1): ?>
						<a href="index.php?option=com_beseated&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->venue_booking_id; ?>&booking_type=venue&auto_approve=1">
							<button type="button" class="btn btn-primary">Pay <?php echo BeseatedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$diposit); ?></button>
						</a>
					<?php else: ?>
						<a href="index.php?option=com_beseated&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->venue_booking_id; ?>&booking_type=venue">
							<button type="button" class="btn btn-primary">Pay <?php echo BeseatedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$diposit); ?></button>
						</a>
					<?php endif; ?>
					<?php $usdRate      = BeseatedHelper::convertCurrencyGoogle(1,$this->booking->currency_code,'USD'); ?>
					<?php $currencyRate = BeseatedHelper::convertCurrencyGoogle(1,'USD',$this->booking->currency_code); ?>
			    	<?php if($this->loyaltyPoints['totalPoint']>=10): ?>
						<?php $totalPoint              = floor($this->loyaltyPoints['totalPoint']); ?>
						<?php $roundedPoint            = ($totalPoint-($totalPoint%10)); ?>
						<?php $bcDollers               = $roundedPoint/10; ?>
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
							<a href="index.php?option=com_beseated&view=dummypayment&task=packageorder.packagePurchased&bc_dollars=<?php echo $bcdUsed; ?>&booking_id=<?php echo $this->booking->venue_booking_id; ?>&booking_type=venue&auto_approve=1">
								<?php if($amoutWithPoint): ?>
									<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$amoutWithPoint) . ' & ' . $bcdUsed.' Points'; ?></button>
								<?php else: ?>
									<button type="button" class="btn btn-primary"><?php echo $bcdUsed.' Points'; ?></button>
								<?php endif; ?>
							</a>
						<?php else: ?>
							<a href="index.php?option=com_beseated&view=dummypayment&task=packageorder.packagePurchased&bc_dollars=<?php echo $bcdUsed; ?>&booking_id=<?php echo $this->booking->venue_booking_id; ?>&booking_type=venue">
								<?php if($amoutWithPoint): ?>
									<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->currency_code,$this->booking->currency_sign,$amoutWithPoint) . ' & ' . $bcdUsed.' Points'; ?></button>
								<?php else: ?>
									<button type="button" class="btn btn-primary">Pay <?php echo $bcdUsed.' Points'; ?></button>
								<?php endif; ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>
					<?php if($this->booking->user_status == 3): ?>
						<a id="cancelConfirmVenueBooking" href="#">
							<button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_CANCEL_BUTTON_TEXT'); ?></button>
						</a>
						<script type="text/javascript">
	                        jQuery("#cancelConfirmVenueBooking").click(function() {
	                           jQuery.confirm({
	                                title: "Cancel",
	                                text: "Are you sure you want to cancel this booking? This action cannot be undone.",
	                                confirm: function() {
	                                    cancelBooking(<?php echo $this->booking->venue_booking_id; ?>,'venue');
	                                },
	                                cancel: function() {
	                                }
	                            });
	                        });
	                    </script>
					<?php endif; ?>
				</form>
			<?php elseif($this->booking->user_status == 2 || $this->booking->user_status == 5  || $this->booking->user_status == 4  || $this->booking->user_status == 7  || $this->booking->user_status == 10 || $this->booking->user_status == 11): ?>
				<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubinformation', true ); ?>
				<?php $Itemid   = $menuItem->id; ?>
				<a href="index.php?option=com_beseated&view=clubinformation&club_id=<?php echo $this->booking->venue_id; ?>&Itemid=<?php echo $Itemid; ?>">
					<button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_VIEW_PROFILE_BUTTON_TEXT'); ?></button>
				</a>
				<?php if($this->booking->user_status == 4 || $this->booking->user_status == 7 || $this->booking->user_status == 11): ?>
				<?php $menuItem = $menu->getItems( 'link', 'index.php?option=com_beseated&view=clubbottles', true );?>
				<?php $Itemid   = $menuItem->id;?>
				<a href="index.php?option=com_beseated&view=clubbottles&club_id=<?php echo $this->booking->venue_id; ?>&table_id=<?php echo $this->booking->table_id; ?>&Itemid=<?php echo $Itemid; ?>">
					<button type="button" id="dataConfirmVenue_<?php echo $this->booking->venue_booking_id; ?>" class="btn btn-primary"><?php echo JText::_('COM_BCTED_VIEW_CONFIRM_BUTTON_TEXT'); ?></button>
				</a>
					<!-- <script type="text/javascript">
					                        jQuery("#dataConfirmVenue_<?php echo $this->booking->venue_booking_id; ?>").click(function() {
					                           jQuery.confirm({
					                                title: "Delete",
					                                text: "Are you sure you want to delete this booking?",
					                                confirm: function() {
					                                    removeVenuePastBooking(<?php echo $this->booking->venue_booking_id; ?>);
					                                },
					                                cancel: function() {
					                                }
					                            });
					                        });
					                    </script> -->
				<?php endif; ?>
				<?php
					$bookingTS   = $this->booking->booking_date.' '.$this->booking->booking_time;
					$before24    = strtotime("-24 hours", strtotime($bookingTS));
					$currentTime = time();
				?>
				<?php if($currentTime<=$before24 && $this->booking->venue_status == 5): ?>
					<a id="confirmRefund_Venue" href="#">
						<button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_REFUND_REQUEST_BUTTON_TEXT'); ?></button>
					</a>
					<script type="text/javascript">
                        jQuery("#confirmRefund_Venue").click(function() {
                           jQuery.confirm({
                                title: "Refund request",
                                text: "Are you sure you want to refund this booking? This action cannot be undone.",
                                confirm: function() {
                                    refundBooking(<?php echo $this->booking->venue_booking_id; ?>,'venue');
                                },
                                cancel: function() {
                                }
                            });
                        });
                    </script>
				<?php elseif($this->booking->venue_status == 7): ?>
					<a id="cancelConfirmVenueBooking" href="#">
							<button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_CANCEL_BUTTON_TEXT'); ?></button>
						</a>
						<script type="text/javascript">
	                        jQuery("#cancelConfirmVenueBooking").click(function() {
	                           jQuery.confirm({
	                                title: "Cancel",
	                                text: "Are you sure you want to cancel this booking? This action cannot be undone.",
	                                confirm: function() {
	                                    cancelBooking(<?php echo $this->booking->venue_booking_id; ?>,'venue');
	                                },
	                                cancel: function() {
	                                }
	                            });
	                        });
	                    </script>
				<?php endif; ?>
				<?php if($this->booking->venue_status == 5): ?>
					<form method="post" action="index.php?option=com_bcted&task=userbookings.invitetomybookedtable&bookingType=venue&booking_id=&">
						<div class="span12 package-invite">
							<input type="text" name="invite_user" placeholder="Enter emails" id="invite_user" value="" data-role="tagsinput" />
							<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myFacebookFriendsModal"><?php echo JText::_('Invite Facebook Friends'); ?></button>
						</div>
						<input type="hidden" name="booking_id" id="booking_id" value="<?php echo $this->booking->venue_booking_id; ?>">
						<input type="hidden" name="Itemid" value="<?php echo $currentItemid; ?>" id="Itemid">
						<input type="hidden" name="fbids" value="" id="fbids">
						<button type="submit" class="btn btn-primary"><?php echo JText::_('COM_BCTED_INVITE_USER'); ?></button>
					</form>
				<?php endif; ?>
				<?php if(($currentTime>=$before24 && $this->booking->venue_status == 5) || $this->booking->user_status == 2): ?>
					<a id="cancelConfirmVenueBooking" href="#">
						<button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_CANCEL_BUTTON_TEXT'); ?></button>
					</a>
					<script type="text/javascript">
                        jQuery("#cancelConfirmVenueBooking").click(function() {
                           jQuery.confirm({
                                title: "Cancel Booking",
                                text: "If you cancel the table at this time, no refunds will be possible as per company policies,however you will not be considered as a no show.",
                                confirm: function() {
                                    cancelBooking(<?php echo $this->booking->venue_booking_id; ?>,'venue');
                                },
                                cancel: function() {
                                }
                            });
                        });
                    </script>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php elseif($this->bookingType == 'package'): ?>
<?php $packageTime = explode(":", $this->booking->package_time); ?>
<?php $bookingDT   = $this->booking->package_datetime . ' ' . $this->booking->package_time; ?>
<?php $bookingTS   = strtotime($bookingDT); ?>
<?php $before24    = strtotime("-24 hours", strtotime($bookingDT)); ?>
<?php $currentTime = time(); ?>
<div class="table-wrp">
    <div class="row-fluid book-dtlwrp">
    	<div class="span6">
	    	<?php if($this->booking->user_status == 2 || $this->booking->user_status == 11 || $this->booking->user_status == 5  || $this->booking->user_status == 4 || $this->booking->user_status == 6 || $this->booking->user_status == 10): ?>
	    		<?php if($this->booking->venue_id): ?>
	    			<h4><b>Club Name : </b><?php echo $this->booking->venue_name; ?></h4>
	    		<?php elseif($this->booking->company_id): ?>
	    			<h4><b>Company Name : </b><?php echo $this->booking->company_name; ?></h4>
	    		<?php endif; ?>
				<h4><b>Package Name : </b><?php echo $this->booking->package_name; ?></h4>
				<?php if($this->booking->package_currency_code): ?>
					<h4><b>Price : </b><?php echo BctedHelper::currencyFormat($this->booking->package_currency_code,$this->booking->package_currency_sign,$this->booking->package_price * $this->booking->package_number_of_guest); ?>(PP)</h4>
				<?php else:?>
					<h4><b>Price : </b><?php echo BctedHelper::currencyFormat($booking->pakcge_current_currency_code,$this->booking->pakcge_current_currency_sign,$this->booking->package_price * $this->booking->package_number_of_guest); ?>(PP)</h4>
				<?php endif; ?>
				<h4><b>No. Of Guests : </b><?php echo $this->booking->package_number_of_guest; ?>
				<h4><b>Date : </b><?php echo date('d-m-Y',strtotime($this->booking->package_datetime)); ?></h4>
				<h4><b>Time : </b><?php echo $packageTime[0].':'.$packageTime[1]; ?></h4>
				<h4><b>Status : </b><?php echo $this->booking->user_status_text; ?></h4>
			<?php elseif($this->booking->user_status == 3): ?>
				<?php if($this->booking->venue_id): ?>
					<h4><?php echo ucfirst($this->booking->venue_name); ?> has confirmed your booking for <?php echo ucfirst($this->booking->package_name); ?> </h4>
				<?php elseif($this->booking->company_id): ?>
					<h4><?php echo ucfirst($this->booking->company_name); ?> has confirmed your booking for <?php echo ucfirst($this->booking->package_name); ?> </h4>
				<?php endif; ?>
	            <h4>The total price for this is <?php echo BctedHelper::currencyFormat($this->booking->pakcge_current_currency_code,$this->booking->pakcge_current_currency_sign,($this->booking->package_price * $this->booking->package_number_of_guest)); ?></h4>
	            <?php $diposit = $this->booking->total_price; ?>
	            <?php if($this->bctConfig->auto_approve ==1): ?>
	            	<?php if($currentTime>=$before24): ?>
	            		<a onclick="return confirm('if you proceed, NO REFUNDS is possible at this time.');" href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package&auto_approve=1">
	            	<?php else: ?>
	            		<a href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package&auto_approve=1">
	            	<?php endif; ?>
	                	<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->pakcge_current_currency_code,$this->booking->pakcge_current_currency_sign,$diposit); ?></button>
	                </a>
	            <?php else:?>
	            	<?php if($currentTime>=$before24): ?>
	            		<a onclick="return confirm('if you proceed, NO REFUNDS is possible at this time.');" href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package">
	            	<?php else: ?>
	            		<a href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package">
	            	<?php endif; ?>
	                	<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->pakcge_current_currency_code,$this->booking->pakcge_current_currency_sign,$diposit); ?></button>
	                </a>
	            <?php endif; ?>
			<?php endif; ?>
			<?php if($this->booking->user_status == 6 && $this->booking->booked_user_paid == 0): ?>
				<?php $diposit = $this->booking->total_price; ?>
	            <?php if($this->bctConfig->auto_approve ==1): ?>
	            	<?php if($currentTime>=$before24): ?>
	            		<a onclick="return confirm('if you proceed, NO REFUNDS is possible at this time.');" href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package&auto_approve=1">
	            	<?php else: ?>
	            		<a href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package&auto_approve=1">
	            	<?php endif; ?>
	                	<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->pakcge_current_currency_code,$this->booking->pakcge_current_currency_sign,$diposit); ?></button>
	                </a>
	            <?php else:?>
	            	<?php if($currentTime>=$before24): ?>
	            		<a onclick="return confirm('if you proceed, NO REFUNDS is possible at this time.');" href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package">
	            	<?php else: ?>
	            		<a href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package">
	            	<?php endif; ?>
	                	<button type="button" class="btn btn-primary">Pay <?php echo BctedHelper::currencyFormat($this->booking->pakcge_current_currency_code,$this->booking->pakcge_current_currency_sign,$diposit); ?></button>
	                </a>
	            <?php endif; ?>
			<?php endif; ?>
        </div>
        <div class="span6">
        	<img src="<?php echo ($this->booking->package_image)?JUri::base().$this->booking->package_image:'images/bcted/default/banner.png'; ?>">
        </div>
    </div>
    <div class="booklst-tbl">
         <div class="span12 pck-bking">
         	<?php if($this->booking->user_status == 3): ?>
         		<?php $invitationsSent = BctedHelper::getPackageInvitedUserDetail($this->booking->package_purchase_id); ?>
         		<?php if($this->booking->can_invite && count($invitationsSent) < ($this->booking->package_number_of_guest - 1 )): ?>
					<h4>Or, Invite your friends to split amount</h4>
					<form method="post" action="index.php?option=com_bcted&task=userbookings.inviteuserinpackage">
						<div class="span12 package-invite">
							<input type="text" name="invite_user" placeholder="Enter emails" id="invite_user" value="" data-role="tagsinput" />
							<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myFacebookFriendsModal"><?php echo JText::_('Invite Facebook Friends'); ?></button>
						</div>
						<input type="hidden" name="package_purchase_id" id="package_purchase_id" value="<?php echo $this->booking->package_purchase_id; ?>">
						<input type="hidden" name="Itemid" value="<?php echo $currentItemid; ?>" id="Itemid">
						<input type="hidden" name="fbids" value="" id="fbids">
						<button type="submit" class="btn btn-primary"><?php echo JText::_('COM_BCTED_INVITE_USER'); ?></button>
					</form>
				<?php endif; ?>
				<?php $invitationsSent = BctedHelper::getPackageInvitedUserDetail($this->booking->package_purchase_id); ?>
        		<?php if($invitationsSent): ?>
					<table class="table table-striped">
						<tr>
							<th>Email</th>
							<th>Status</th>
						</tr>
						<?php foreach ($invitationsSent as $key => $invitedDetail) : ?>
							<tr>
								<td><?php echo $invitedDetail->invited_email; ?></td>
								<td>
									<?php if($invitedDetail->status == 3): ?>
										<?php echo "Not Paid"; ?>
									<?php elseif($invitedDetail->status == 5): ?>
										<?php echo "Paid"; ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
					<?php if($this->booking->user_status == 6 && $this->booking->booked_user_paid == 1): ?>
						<?php if($this->bctConfig->auto_approve ==1): ?>
							<?php if($currentTime>=$before24): ?>
			            		<a onclick="return confirm('if you proceed, NO REFUNDS is possible at this time.');" href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package&full_payment=1&auto_approve=1">
			            	<?php else: ?>
			            		<a href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package&full_payment=1&auto_approve=1">
			            	<?php endif; ?>
								<button type="button" class="btn btn-primary">Pay Full Amount</button>
							</a>
						<?php else: ?>
							<?php if($currentTime>=$before24): ?>
			            		<a onclick="return confirm('if you proceed, NO REFUNDS is possible at this time.');" href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package&full_payment=1">
			            	<?php else: ?>
			            		<a href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package&full_payment=1">
			            	<?php endif; ?>
								<button type="button" class="btn btn-primary">Pay Full Amount</button>
							</a>
						<?php endif; ?>
					<?php endif; ?>
        		<?php endif; ?>

        	<?php elseif($this->booking->user_status == 2 || $this->booking->user_status == 5  || $this->booking->user_status == 4 || $this->booking->user_status == 6 || $this->booking->user_status == 11): ?>
        		<?php $invitationsSent = BctedHelper::getPackageInvitedUserDetail($this->booking->package_purchase_id); ?>
        		<?php if($invitationsSent): ?>
					<table class="table table-striped">
						<tr>
							<th>Email</th>
							<th>Status</th>
						</tr>
						<?php foreach ($invitationsSent as $key => $invitedDetail) : ?>
							<tr>
								<td><?php echo $invitedDetail->invited_email; ?></td>
								<td>
									<?php if($invitedDetail->status == 3): ?>
										<?php echo "Not Paid"; ?>
									<?php elseif($invitedDetail->status == 5): ?>
										<?php echo "Paid"; ?>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
        		<?php endif; ?>
				<div>
					<?php if($this->booking->user_status == 6 && $this->booking->booked_user_paid == 1): ?>
						<?php if($this->bctConfig->auto_approve ==1): ?>
							<?php if($currentTime>=$before24): ?>
			            		<a onclick="return confirm('if you proceed, NO REFUNDS is possible at this time.');" href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package&full_payment=1&auto_approve=1">
			            	<?php else: ?>
			            		<a href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package&full_payment=1&auto_approve=1">
			            	<?php endif; ?>
								<button type="button" class="btn btn-primary">Pay Full Amount</button>
							</a>
						<?php else: ?>
							<?php if($currentTime>=$before24): ?>
			            		<a onclick="return confirm('if you proceed, NO REFUNDS is possible at this time.');" href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package&full_payment=1">
			            	<?php else: ?>
			            		<a href="index.php?option=com_bcted&view=dummypayment&task=packageorder.packagePurchased&booking_id=<?php echo $this->booking->package_purchase_id; ?>&booking_type=package&full_payment=1">
			            	<?php endif; ?>
								<button type="button" class="btn btn-primary">Pay Full Amount</button>
							</a>
						<?php endif; ?>
					<?php endif; ?>

					<?php if(($this->booking->user_status == 6 || $this->booking->user_status == 5) && $this->booking->booked_user_paid == 1 ): ?>
						<?php $bookingDT = $this->booking->package_datetime . ' ' . $this->booking->package_time; ?>
						<?php $bookingTS = strtotime($bookingDT); ?>
						<?php $before24 = strtotime("-24 hours", strtotime($bookingDT)); ?>
						<?php $currentTime = time(); ?>
						<?php if($currentTime<=$before24): ?>
							<a id="confirmRefund_Package" href="index.php?option=com_bcted&task=userbookings.send_request_refund&booking_id=<?php echo $this->booking->package_purchase_id; ?>&Itemid=<?php echo $currentItemid; ?>">
								<button type="button" class="btn btn-primary">Request Refund</button>
							</a>
						<?php endif; ?>
					<?php endif; ?>
					<?php
		        		$menuItem = $menu->getItems( 'link', 'index.php?option=com_bcted&view=clubinformation', true );
						$Itemid = $menuItem->id;
					?>
		        	<a href="index.php?option=com_bcted&view=clubinformation&club_id=<?php echo $this->booking->venue_id; ?>&Itemid=<?php echo $Itemid; ?>">
		               <button type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_VIEW_PROFILE_BUTTON_TEXT'); ?></button>
		           	</a>
		           	<?php if($this->booking->user_status == 2): ?>
			           <button id="cancelConfirmPackageBooking" type="button" class="btn btn-primary"><?php echo JText::_('COM_BCTED_CANCEL_BUTTON_TEXT'); ?></button>
		               	<script type="text/javascript">
							jQuery("#cancelConfirmPackageBooking").click(function() {
								jQuery.confirm({
									title: "Cancel Booking",
									text: "Are you sure you want to cancel this booking? This action cannot be undone.",
									confirm: function() {
										cancelPackageBooking(<?php echo $this->booking->package_purchase_id; ?>,'package');
									},
									cancel: function() {
									}
								});
							});
						</script>
					<?php elseif($this->booking->user_status == 11 || $this->booking->user_status == 4): ?>
			           <button id="deleteConfirmPackageBooking" type="button" class="btn btn-primary"><?php echo JText::_('Delete'); ?></button>
		               	<script type="text/javascript">
							jQuery("#deleteConfirmPackageBooking").click(function() {
								jQuery.confirm({
									title: "Delete",
									text: "Are you sure you want to Delete this booking? This action cannot be undone.",
									confirm: function() {
										removePackagePastBooking(<?php echo $this->booking->package_purchase_id; ?>);
									},
									cancel: function() {
									}
								});
							});
						</script>
	           		<?php endif; ?>
				</div>
        	<?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<style>
div.modal.fade{display: none;}
div.modal.fade.in{display: block;}
</style>
<!-- Modal -->
<div id="myFacebookFriendsModal"  class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Facebook Friends</h4>
      </div>
      <div class="modal-body">
        <table>
        	<thead>
	        	<tr>
	        		<th>&nbsp;</th>
	        		<th>&nbsp;</th>
        		</tr>
        	</thead>
        	<tbody>
        		<?php $idx = 0; ?>
        		<?php foreach ($this->facebook_friends as $key => $value): ?>
        			<?php if(!$value->id){ continue; } ?>
        			<?php $idx = $idx + 1; ?>
        			<tr>
	        			<td><img src="http://graph.facebook.com/<?php echo $value->fbid; ?>/picture" alt="" /></td>
	        			<td>
	        				<div class="controls">
								<ul style="list-style:none;">
									<li>
										<input type="checkbox" value="<?php echo $value->email; ?>" id="fbuser_<?php echo $value->fbid; ?>" name="fbuser_<?php echo $value->fbid; ?>"><label for="fbuser_<?php echo $value->fbid; ?>"><?php echo ucfirst($value->first_name) . ' ' . ucfirst($value->last_name); ?></label>
									</li>
								</ul>
							</div>
						</td>
	        		</tr>
        		<?php endforeach; ?>
        	</tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
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
<script type="text/javascript">
	jQuery('input[type="checkbox"][id^="fbuser_"]').click(function() {
		var fbids = "";
		jQuery('input[type="checkbox"][id^="fbuser_"]').each( function() {
			if(jQuery(this).attr('checked')) {
				if (!fbids.trim())
				{
					 fbids = jQuery(this).val();
				}
				else
				{
					fbids = fbids + "," + jQuery(this).val();
				}
			}
		});
		jQuery('#fbids').val(fbids);
	});
</script>

<script type="text/javascript">
	jQuery('#confirmRefund_Package').on('click', function () {
        return confirm('You can refund the amount you paid for this package minus associated fees. Refunds can take upto 7 days.');
    });
</script>