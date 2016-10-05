<?php

/**
 * @package     Pass.Plugin
 * @subpackage  com_pass
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * Pass PaymentsPaypal plugin
 *
 * @since  0.0.1
 */
class plgBctPaypal extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  0.0.1
	 */
	protected $autoloadLanguage = true;

	function onPackageInvitePaymentPaypal()
	{

		require_once ("cbdom_main.php");
		$jinput      = JFactory::getApplication()->input;
		$inviteID    = $jinput->get('package_invite_id', 0, 'int');
		$bookingType = $jinput->get('booking_type', '', 'string');
		$autoApprove = $jinput->get('auto_approve', 0, 'int');
		$bcDollars   = $jinput->get('bc_dollars', 0, 'int');
		$goForPaypal = 1;

		$itemName = "";
		$itemNumber = 0;

		$elemntAmount = 0.00;
		$elemntCurrencySign = '';
		$elemntCurrencyCode = '';

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_bcted/tables');

		$user    = JFactory::getUser();
		/*echo "<pre>";
		print_r($inviteID);
		echo "</pre>";
		exit;*/

		if ($user->id == 0)
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?option=com_users&view=login');
			$app->close();
		}

		if(!$inviteID)
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?option=com_users&view=login');
			$app->close();
		}

		$bctParams = $this->getExtensionParam();
		$merchantId = $bctParams->merchantId;

		if(empty($merchantId))
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?success=false3');
			$app->close();
		}



		$tblPackageInvite = JTable::getInstance('PackageInvite', 'BctedTable');
		$tblPackagePurchased = JTable::getInstance('PackagePurchased', 'BctedTable');
		$tblPackage = JTable::getInstance('Package', 'BctedTable');

		$tblPackageInvite->load($inviteID);
		/*echo "<pre>";
		print_r($tblPackageInvite);
		echo "</pre>";
		exit;*/
		$tblPackagePurchased->load($tblPackageInvite->package_purchase_id);
		$tblPackage->load($tblPackageInvite->package_id);

		$itemNumber   = $tblPackage->package_id;
		$itemName     = $tblPackage->package_name;
		$elemntAmount = $tblPackageInvite->amount_payable;

		/*echo $elemntAmount;
		exit;*/
		$elemntCurrencySign = $tblPackage->currency_sign;
		$elemntCurrencyCode = $tblPackage->currency_code;

		$PaymentStatus = JTable::getInstance('PaymentStatus', 'BctedTable');
		$PaymentStatus->load(0);

		$convertExp = "";

		if($elemntCurrencyCode!="USD")
		{
			$convertExp = $elemntCurrencyCode."_USD";
		}

		$currencyRateUSD = 0;

		if(!empty($convertExp))
		{
			/*$url = "http://www.freecurrencyconverterapi.com/api/v3/convert?q=".$convertExp."&compact=y";
			$ch = curl_init();
			$timeout = 0;
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$rawdata = curl_exec($ch);
			curl_close($ch);
			if(empty($rawdata))
			{
				$app = JFactory::getApplication();
				$app->redirect('index.php?success=false12');
				$app->close();
			}
			$object = json_decode($rawdata);
			$currencyRateUSD = $object->$convertExp->val;*/
			$currencyRateUSD = $this->convertCurrencyGoogle(1,$elemntCurrencyCode,'USD');
		}

		//$depositPer = ($bctParams->deposit_per)?$bctParams->deposit_per:20;
		$loyaltyPer = ($bctParams->loyalty_per)?$bctParams->loyalty_per:1;

		$depositAmount = $elemntAmount;


		/*echo $depositAmount . " || " . $bookingType . " || " .$convertExp;
		exit;*/

		if($currencyRateUSD)
		{
			$convertedElementAmount = $elemntAmount * $currencyRateUSD;
		}
		else
		{
			$convertedElementAmount = $elemntAmount;
		}



		$loyaltyPoints = ($convertedElementAmount * $loyaltyPer) / 100;
		$loyaltyPoints = number_format($loyaltyPoints,2);

		/*echo "Currency RAte : ".$currencyRateUSD."<br />";
		echo "BC deposit Amount : " . $convertedElementAmount . "<br />";
		echo "BC Dollars : " . $loyaltyPoints . "<br />";
		exit;*/

		if($bcDollars)
		{
			$bcdToUsd = $bcDollars / 10;
			$bcdToUsd = number_format($bcdToUsd);

			//echo "BC Dollars : " . $bcdToUsd . "<br />";

			$payAfterBCD = $depositAmount - $bcdToUsd;

			$minusBCD = $bcDollars * 2;

			$minusBCD = $bcDollars - $minusBCD;

			if($payAfterBCD > 0)
			{
				$depositAmountAfertBCD = $payAfterBCD;
				$goForPaypal = 1;
			}
			else
			{
				$goForPaypal = 0;
			}
		}

		/*echo $bcDollars;
		exit;*/

		$paymentData['booked_element_id']   = $inviteID;
		$paymentData['booked_element_type'] = $bookingType;
		$paymentData['currency_code']       = $elemntCurrencyCode;

		$paymentData['currency_sign']       = $elemntCurrencySign;
		$paymentData['customer_paid']       = $elemntAmount;
		$paymentData['element_price']       = $elemntAmount;
		$paymentData['platform_income']     = ($bcDollars)?$depositAmountAfertBCD:$depositAmount;
		$paymentData['element_income']      = $elemntAmount;
		$paymentData['bcd_used']            = $bcDollars;
		$paymentData['paid_status']         = 0;
		$paymentData['time_stamp']          = time();
		$paymentData['created']             = date('Y-m-d H:i:s');

		/*echo "<pre>";
		print_r($paymentData);
		echo "</pre>";
		exit;*/

		//$orderPostData['offer_id'] = $offerID;
		$PaymentStatus->bind($paymentData);



		if(!$PaymentStatus->store())
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?success=false13');
			$app->close();

			return false;
		}

		$paymentEntryID = $PaymentStatus->payment_id;

		/*if($bcDollars)
		{
			if($goForPaypal == 1)
			{
				$this->addLoyaltyPoints($user->id,'Payout',$minusBCD,$paymentEntryID);
			}
			else
			{
				$this->addLoyaltyPoints($user->id,'Payout',$minusBCD,$paymentEntryID,1);
			}
		}

		$this->addLoyaltyPoints($user->id,'purchase.'.$bookingType,$loyaltyPoints,$paymentEntryID);

		*/

		if($bcDollars)
		{
			if($goForPaypal == 1)
			{
				$this->addLoyaltyPoints($user->id,'Payout',$minusBCD,$paymentEntryID);
				$loyaltyPoints = ($payAfterBCD * $loyaltyPer) / 100;

				$lpAmount = $elemntAmount - $depositAmountAfertBCD;
				$loyaltyPoints = ($lpAmount * $loyaltyPer) / 100;

				$this->addLoyaltyPoints($user->id,'purchase.'.$bookingType,($loyaltyPoints * 10),$paymentEntryID);
			}
			else
			{
				$this->addLoyaltyPoints($user->id,'Payout',$minusBCD,$paymentEntryID,1);
			}

			//$payAfterBCD * 10;



		}
		else
		{
			$this->addLoyaltyPoints($user->id,'purchase.'.$bookingType,($loyaltyPoints * 10),$paymentEntryID);
		}



		if($bcDollars)
		{
			$amountToPaypal = $depositAmountAfertBCD;
		}
		else
		{
			$amountToPaypal = $depositAmount;
		}

		if($elemntCurrencyCode=="AED" && $currencyRateUSD)
		{
			$amountToPaypal = $amountToPaypal * $currencyRateUSD;
			$amountToPaypal = number_format($amountToPaypal,2);
			$elemntCurrencyCode = "USD";

			/*echo $amountToPaypal."<br />";
			echo $elemntCurrencyCode."<br />";
			exit;*/
		}

		$userDetail = $this->getUserProfile();
		$address = $this->getAddressDetail($userDetail->city);

		$actionType  = "PAY";
		$cancelUrl   = JURI::base() . "index.php?success=false";
		$redirectUrl = JURI::base() . 'index.php?option=com_bcted&payment_entry_id='.$paymentEntryID.'&task=packageorder.ipninvite';

		$post_variables = Array(
			"merchant_id"			=> $merchantId,
			"order_id"				=> $paymentEntryID,
			"amount"				=> $amountToPaypal,
			"currency"				=> 'AED', //$elemntCurrencyCode,'AED',
			"redirect_url"			=> $redirectUrl,
			"cancel_url"			=> $cancelUrl,
			"language"				=> 'EN',
			"billing_name" 			=> $userDetail->name . ' ' .$userDetail->last_name,
			"billing_address"  		=> ($address['full_address'])?$address['full_address']:$userDetail->city,
			"billing_city"  		=> ($address['city'])?$address['city']:$userDetail->city,
			"billing_state"  		=> ($address['state'])?$address['state']:$userDetail->city,
			"billing_zip"  			=> ($address['postal_code'])?$address['postal_code']:'',
			"billing_country"  		=> ($address['country'])?$address['country']:'',
			"billing_tel" 			=> $userDetail->phoneno,
			"billing_email" 		=> $userDetail->email,
			"delivery_name"  		=> $userDetail->name . ' ' .$userDetail->last_name,
			"delivery_address" 		=> ($address['full_address'])?$address['full_address']:$userDetail->city,
			"delivery_city" 		=> ($address['city'])?$address['city']:$userDetail->city,
			"delivery_state"  		=> ($address['state'])?$address['state']:$userDetail->city,
			"delivery_zip"  		=> ($address['postal_code'])?$address['postal_code']:'',
			"delivery_country" 		=> ($address['country_short'])?$address['country_short']:'',
			"delivery_tel"  		=> $userDetail->phoneno,
			"merchant_param1"    	=> $paymentEntryID
		);

		/*echo "<pre>";
		print_r($post_variables);
		echo "</pre>";
		exit;*/

		/*$post_variables = Array(
			"cmd"            => "_xclick",
			"upload"         => "2",
			"business"       => $paypalEmail,
			"receiver_email" => $paypalEmail,
			"quantity"       => 1,
			"item_name"		 => $itemName,
			"item_number"    => $itemNumber,
			"no_shipping"	 => 0,
			"amount"         => $amountToPaypal,
			"return"         => JURI::base() . "index.php?success=true&payment_entry_id={$paymentEntryID}",
			"notify_url"     => JURI::base() . 'index.php?option=com_bcted&payment_entry_id='.$paymentEntryID.'&task=packageorder.ipninvite',
			"cancel_return"  => JURI::base() . "index.php?success=false",
			"currency_code"  => $elemntCurrencyCode
		);*/

		/*echo $goForPaypal."<br />";
		echo $autoApprove."<br />";
		exit;*/


		if($goForPaypal)
		{
			$customer_info_array = array();
			foreach ($post_variables as $key => $value)
			{
				$customer_info_array[] = $key.'='.urlencode($value);
			}
			$customer_info = implode("&",$customer_info_array);

			$encrypted_data = $this->encrypt($customer_info,$bctParams->encryptionKey);

			$access_code = $bctParams->accessCode;

			if($autoApprove == 0)
			{
				echo '<form action="https://secure.ccavenue.ae/transaction/transaction.do?command=initiateTransaction" method="post" id="paymentform" name="paymentform">
					<input type="hidden" name="encRequest" id="encRequest" value="'.$encrypted_data.'" />
					<input type="hidden" name="access_code" id="access_code" value="'.$access_code.'" />
				</form>';
				echo '<script type="text/javascript">document.paymentform.submit()</script>';
			}
			else
			{
				//http://thebeseated.com/dev/index.php?option=com_bcted&task=packageorder.packagePurchased&booking_id=117&booking_type=venue&Itemid=220&

				$res_variables = Array(
					"option"           => "com_bcted",
					"payment_entry_id" => $paymentEntryID,
					"task"             => "packageorder.ipninvite",
					"encResp"          => "bfcffea2e131bd124435273d17b003745478cfbc11766708615a2c8c0198997c9b06e28cd41700c0be8467d6be22719308c06139360d058792e21975dc67abde756fd71734cf6c3dbfaea5d2841831c1634caf67c5fef08730f594e344f2c5d43b57f30e974b2fcd50258323e31a7e422cb735046f52ebc267d84a9bf3aedbb8aca1b21a392a4743f51891eccdef46713671e916f0cc86a8c21f6c7c9012b5274fafb67c2ece95a128ef3407ddaa1393deb36c3d4e711fecd7ed10d549c9ab6718707cb776ed40be259dec22dedec8e173a1454e22f2065fa138d93c84f356e27df2ad9c94d9ad7f587c5fcb3bdb5c99f566f261d9301c0bfb69fa9d5c0af0e6d626c0ccdcd6bdedb907d5c4ad356f95e75326783875ee84933d23122bec8e22ac116e84ce82f694d5c8cc624c1c2e9b7794d85630cacd42e3b3eebc7650e26c148e3715cb4ce44ebebe3806334dca2155ebf847b284973e70caa50c14c2f6f175315db3a00443f20bea6bd01ed3b4326097a3ef88bb5bf7bef93c88339be52a532f5955186bf01c6fea2e6efa57374d18a014469bbfe77eeaa16802ed81cbb753ee27384cfd091cf4cd3a568953f63dcb66c93d220eab112401cd3b643e5f82db00cadaae4f6fd713b5bd!
 d98931d22a683a347c37f708ab7cd9e404cf66582dea7426e2595c7a5afa4a34fbe759c08ff456f50898bbc2f05fb981e48769c3d566c79e2400d3146b6633f361b3b43cdcf7c29e2353819470c4d688da7a72c128a1c221e08eefb38673b812d47760e2cf7809be3e56a357311a7c3bba1b6ceae2ac9688c2a70de3ebd9db84e99764abc516824516ec944b57ba3168482b6fe6f44cdfb9fbf50f15d2d7b6fd36d4efa90abbe59ef2680c6bb5e0db707cc10f941324164ac6acc150af08218897f86e528cf5711d5ef9c5284a33f379470dcadf812d13fad8735bcd1e3db16a32be7f721830c56e6f2253c220144299c8702efa25555ea9e35e50c3174025e06372064625c69c8d95b0052816c7d66c13fa843180"

				);

				echo '<form action="'.JUri::base().'index.php?option=com_bcted&payment_entry_id='.$paymentEntryID.'&task=packageorder.ipninvite&auto_approve=1" method="post" name="autopayment" id="autopayment">';

				foreach ($res_variables as $name => $value)
				{
					echo "<input type='hidden' name='$name' value='$value' />";
				}



				echo '<INPUT TYPE="hidden" name="charset" value="utf-8">';
				echo "</form>";
				?>
				<script type='text/javascript'>document.autopayment.submit();</script>
				<?php
			}
		}
		else
		{
			$tblPaymentStatus = JTable::getInstance('PaymentStatus', 'BctedTable',array());
			$tblPaymentStatus->load($paymentEntryID);

			if(!$tblPaymentStatus->payment_id)
			{
				$app = JFactory::getApplication();
				$app->redirect("index.php?success=false14");
				$app->close();
			}

			$elementID = $tblPaymentStatus->booked_element_id;
			$elementType = $tblPaymentStatus->booked_element_type;

			$paymentGross = $depositAmount; //$input->get('payment_gross',0.00);
			$paymentFee   = 0.00; //$input->get('payment_fee',0.00);
			$txnID        = 'paybybcd';//$input->get('txn_id','','string');
			$paymentStatusText  = $bookingType;

			$tblPaymentStatus->payment_fee = $paymentFee;
			$tblPaymentStatus->txn_id = $txnID;
			$tblPaymentStatus->payment_status = $paymentStatusText;

			/*if($paymentStatusText == 'Completed')
			{*/
				$tblPaymentStatus->paid_status = 1;

				if($elementType == 'service')
				{
					$tblServiceBooking = JTable::getInstance('ServiceBooking', 'BctedTable');
					$tblServiceBooking->load($elementID);
					$tblServiceBooking->status = 5;
					$tblServiceBooking->user_status = 5;

					$tblServiceBooking->total_price = $tblPaymentStatus->element_price;
					$tblServiceBooking->deposit_amount = $tblPaymentStatus->platform_income;
					$tblServiceBooking->amount_payable = $tblPaymentStatus->element_price - $tblPaymentStatus->platform_income;

					$tblServiceBooking->store();
				}
				else if($elementType == 'venue')
				{
					$tblVenuebooking = JTable::getInstance('Venuebooking', 'BctedTable');
					$tblVenuebooking->load($elementID);
					$tblVenuebooking->status = 5;
					$tblVenuebooking->user_status = 5;

					$tblVenuebooking->total_price = $tblPaymentStatus->element_price;
					$tblVenuebooking->deposit_amount = $tblPaymentStatus->platform_income;
					$tblVenuebooking->amount_payable = $tblPaymentStatus->element_price - $tblPaymentStatus->platform_income;

					$tblVenuebooking->store();
				}
				else if($elementType == 'package')
				{
					$tblPackagePurchased = JTable::getInstance('PackagePurchased', 'BctedTable');
					$tblPackagePurchased->load($elementID);
					$tblPackagePurchased->status = 5;
					$tblPackagePurchased->user_status = 5;
					$tblPackagePurchased->store();
				}

				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base update statement.
				$query->update($db->quoteName('#__bcted_loyalty_point'))
					->set($db->quoteName('is_valid') . ' = ' . $db->quote(1))
					->where($db->quoteName('cid') . ' = ' . $db->quote($paymentEntryID));

				// Set the query and execute the update.
				$db->setQuery($query);
				$db->execute();
			//}

			$tblPaymentStatus->store();

			$app = JFactory::getApplication();
			$app->redirect("index.php?success=true&payment_entry_id={$paymentEntryID}");
			$app->close();
		}
		return true;

	}

	function onPackageInvitePaymentPaypal1()
	{
		$jinput  = JFactory::getApplication()->input;
		$inviteID = $jinput->get('package_invite_id', 0, 'int');
		$bookingType = $jinput->get('booking_type', '', 'string');
		$bcDollars = $jinput->get('bc_dollars', 0, 'int');
		$goForPaypal = 1;
		$itemName = "";
		$itemNumber = 0;
		$elemntAmount = 0.00;
		$elemntCurrencySign = '';
		$elemntCurrencyCode = '';

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_bcted/tables');

		$user    = JFactory::getUser();
		/*echo "<pre>";
		print_r($inviteID);
		echo "</pre>";
		exit;*/

		if ($user->id == 0)
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?option=com_users&view=login');
			$app->close();
		}

		if(!$inviteID)
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?option=com_users&view=login');
			$app->close();
		}

		$bctParams = $this->getExtensionParam();
		$paypalEmail = $bctParams->paypalId;

		if(empty($paypalEmail))
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?success=false3');
			$app->close();
		}

		$tblPackageInvite = JTable::getInstance('PackageInvite', 'BctedTable');
		$tblPackagePurchased = JTable::getInstance('PackagePurchased', 'BctedTable');
		$tblPackage = JTable::getInstance('Package', 'BctedTable');

		$tblPackageInvite->load($inviteID);
		$tblPackagePurchased->load($tblPackageInvite->package_purchase_id);
		$tblPackage->load($tblPackageInvite->package_id);

		$itemNumber   = $tblPackage->package_id;
		$itemName     = $tblPackage->package_name;
		$elemntAmount = $tblPackageInvite->amount_payable;

		/*echo $elemntAmount;
		exit;*/
		$elemntCurrencySign = $tblPackage->currency_sign;
		$elemntCurrencyCode = $tblPackage->currency_code;

		$PaymentStatus = JTable::getInstance('PaymentStatus', 'BctedTable');
		$PaymentStatus->load(0);

		$convertExp = "";

		if($elemntCurrencyCode!="USD")
		{
			$convertExp = $elemntCurrencyCode."_USD";
		}

		$currencyRateUSD = 0;

		if(!empty($convertExp))
		{
			$url = "http://www.freecurrencyconverterapi.com/api/v3/convert?q=".$convertExp."&compact=y";
			$ch = curl_init();
			$timeout = 0;
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$rawdata = curl_exec($ch);
			curl_close($ch);
			if(empty($rawdata))
			{
				$app = JFactory::getApplication();
				$app->redirect('index.php?success=false12');
				$app->close();
			}
			$object = json_decode($rawdata);
			$currencyRateUSD = $object->$convertExp->val;
		}

		//$depositPer = ($bctParams->deposit_per)?$bctParams->deposit_per:20;
		$loyaltyPer = ($bctParams->loyalty_per)?$bctParams->loyalty_per:1;

		$depositAmount = $elemntAmount;


		/*echo $depositAmount . " || " . $bookingType . " || " .$convertExp;
		exit;*/

		if($currencyRateUSD)
		{
			$convertedElementAmount = $elemntAmount * $currencyRateUSD;
		}
		else
		{
			$convertedElementAmount = $elemntAmount;
		}



		$loyaltyPoints = ($convertedElementAmount * $loyaltyPer) / 100;
		$loyaltyPoints = number_format($loyaltyPoints,2);

		/*echo "Currency RAte : ".$currencyRateUSD."<br />";
		echo "BC deposit Amount : " . $convertedElementAmount . "<br />";
		echo "BC Dollars : " . $loyaltyPoints . "<br />";
		exit;*/

		if($bcDollars)
		{
			$bcdToUsd = $bcDollars / 10;
			$bcdToUsd = number_format($bcdToUsd);

			//echo "BC Dollars : " . $bcdToUsd . "<br />";

			$payAfterBCD = $depositAmount - $bcdToUsd;

			$minusBCD = $bcDollars * 2;

			$minusBCD = $bcDollars - $minusBCD;

			if($payAfterBCD > 0)
			{
				$depositAmountAfertBCD = $payAfterBCD;
				$goForPaypal = 1;
			}
			else
			{
				$goForPaypal = 0;
			}
		}

		/*echo $bcDollars;
		exit;*/

		$paymentData['booked_element_id']   = $inviteID;
		$paymentData['booked_element_type'] = $bookingType;
		$paymentData['currency_code']       = $elemntCurrencyCode;

		$paymentData['currency_sign']       = $elemntCurrencySign;
		$paymentData['customer_paid']       = $elemntAmount;
		$paymentData['element_price']       = $elemntAmount;
		$paymentData['platform_income']     = ($bcDollars)?$depositAmountAfertBCD:$depositAmount;
		$paymentData['element_income']      = $elemntAmount;
		$paymentData['bcd_used']            = $bcDollars;
		$paymentData['paid_status']         = 0;
		$paymentData['time_stamp']          = time();
		$paymentData['created']             = date('Y-m-d H:i:s');

		/*echo "<pre>";
		print_r($paymentData);
		echo "</pre>";
		exit;*/

		//$orderPostData['offer_id'] = $offerID;
		$PaymentStatus->bind($paymentData);



		if(!$PaymentStatus->store())
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?success=false13');
			$app->close();

			return false;
		}

		$paymentEntryID = $PaymentStatus->payment_id;

		if($bcDollars)
		{
			if($goForPaypal == 1)
			{
				$this->addLoyaltyPoints($user->id,'Payout',$minusBCD,$paymentEntryID);
			}
			else
			{
				$this->addLoyaltyPoints($user->id,'Payout',$minusBCD,$paymentEntryID,1);
			}
		}

		$this->addLoyaltyPoints($user->id,'purchase.'.$bookingType,($loyaltyPoints * 10),$paymentEntryID);

		if($bcDollars)
		{
			$amountToPaypal = $depositAmountAfertBCD;
		}
		else
		{
			$amountToPaypal = $depositAmount;
		}

		$actionType  = "PAY";
		$cancelUrl   = JURI::base() . "index.php?success=false";
		$redirectUrl = JURI::base() . 'index.php?option=com_bcted&payment_entry_id='.$paymentEntryID.'&task=packageorder.ipninvite';

		/*if($elemntCurrencyCode=="AED" && $currencyRateUSD)
		{
			$amountToPaypal = $amountToPaypal * $currencyRateUSD;
			$amountToPaypal = number_format($amountToPaypal,2);
			$elemntCurrencyCode = "USD";
		}*/

		$post_variables = Array(
			"merchant_id"			=> $merchantId,
			"order_id"				=> $paymentEntryID,
			"amount"				=> $amountToPaypal,
			"currency"				=> 'AED',//$elemntCurrencyCode
			"redirect_url"			=> $redirectUrl,
			"cancel_url"			=> $cancelUrl,
			"language"				=> 'EN',
			"billing_name" 			=> 'vipula',
			"billing_address"  		=> 'Sheikh Mohammed bin Rashid Boulevard',
			"billing_city"  		=> 'Dubai',
			"billing_state"  		=> 'Dubai',
			"billing_zip"  			=> '334155',
			"billing_country"  		=> 'AE',
			"billing_tel" 			=> '0123456',
			"billing_email" 		=> 'test@gmail.com',
			"delivery_name"  		=> 'vipula',
			"delivery_address" 		=> 'Sheikh Mohammed bin Rashid Boulevard',
			"delivery_city" 		=> 'Dubai',
			"delivery_state"  		=> 'Dubai',
			"delivery_zip"  		=> '334155',
			"delivery_country" 		=> 'AE',
			"delivery_tel"  		=> '0123456',
			"merchant_param1"    	=> $paymentEntryID
		);

		if ($goForPaypal)
		{
			$customer_info_array = array();
			foreach ($post_variables as $key => $value)
			{
				$customer_info_array[] = $key.'='.urlencode($value);
			}
			$customer_info = implode("&",$customer_info_array);

			$encrypted_data = $this->encrypt($customer_info,$bctParams->encryptionKey);

			$access_code = $bctParams->accessCode;

		 	echo '<form action="https://secure.ccavenue.ae/transaction/transaction.do?command=initiateTransaction" method="post" id="paymentform" name="paymentform">
					<input type="hidden" name="encRequest" id="encRequest" value="'.$encrypted_data.'" />
					<input type="hidden" name="access_code" id="access_code" value="'.$access_code.'" />
				</form>';
			echo '<script type="text/javascript">document.paymentform.submit()</script>';
		}
		else
		{
			$tblPaymentStatus = JTable::getInstance('PaymentStatus', 'BctedTable',array());
			$tblPaymentStatus->load($paymentEntryID);

			if(!$tblPaymentStatus->payment_id)
			{
				$app = JFactory::getApplication();
				$app->redirect("index.php?success=false14");
				$app->close();
			}

			$elementID = $tblPaymentStatus->booked_element_id;
			$elementType = $tblPaymentStatus->booked_element_type;

			$paymentGross = $depositAmount; //$input->get('payment_gross',0.00);
			$paymentFee   = 0.00; //$input->get('payment_fee',0.00);
			$txnID        = 'paybybcd';//$input->get('txn_id','','string');
			$paymentStatusText  = $bookingType;

			$tblPaymentStatus->payment_fee = $paymentFee;
			$tblPaymentStatus->txn_id = $txnID;
			$tblPaymentStatus->payment_status = $paymentStatusText;

			if($paymentStatusText == 'Completed')
			{
				$tblPaymentStatus->paid_status = 1;

				if($elementType == 'service')
				{
					$tblServiceBooking = JTable::getInstance('ServiceBooking', 'BctedTable');
					$tblServiceBooking->load($elementID);
					$tblServiceBooking->status = 5;
					$tblServiceBooking->user_status = 5;

					$tblServiceBooking->total_price = $tblPaymentStatus->element_price;
					$tblServiceBooking->deposit_amount = $tblPaymentStatus->platform_income;
					//$tblServiceBooking->amount_payable = $tblPaymentStatus->element_price - $tblPaymentStatus->platform_income;

					$tblServiceBooking->store();
				}
				else if($elementType == 'venue')
				{
					$tblVenuebooking = JTable::getInstance('Venuebooking', 'BctedTable');
					$tblVenuebooking->load($elementID);
					$tblVenuebooking->status = 5;
					$tblVenuebooking->user_status = 5;

					$tblVenuebooking->total_price = $tblPaymentStatus->element_price;
					$tblVenuebooking->deposit_amount = $tblPaymentStatus->platform_income;
					$tblVenuebooking->amount_payable = $tblPaymentStatus->element_price - $tblPaymentStatus->platform_income;

					$tblVenuebooking->store();
				}
				else if($elementType == 'package')
				{
					$tblPackagePurchased = JTable::getInstance('PackagePurchased', 'BctedTable');
					$tblPackagePurchased->load($elementID);
					$tblPackagePurchased->status = 5;
					$tblPackagePurchased->user_status = 5;
					$tblPackagePurchased->store();
				}

				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base update statement.
				$query->update($db->quoteName('#__bcted_loyalty_point'))
					->set($db->quoteName('is_valid') . ' = ' . $db->quote(1))
					->where($db->quoteName('cid') . ' = ' . $db->quote($paymentEntryID));

				// Set the query and execute the update.
				$db->setQuery($query);
				$db->execute();
			}

			$tblPaymentStatus->store();

			$app = JFactory::getApplication();
			$app->redirect("index.php?success=true&payment_entry_id={$paymentEntryID}");
			$app->close();
		}
		return true;

	}



	/**
	 * Plugin method with the same name as the event will be called automatically.
	 *
	 * @return boolean
	 */
	function onPreparePaymentPaypal()
	{
		/*echo "call";
		exit;*/
		require_once ("cbdom_main.php");
		$jinput  = JFactory::getApplication()->input;

		$bookingID = $jinput->get('booking_id', 0, 'int');
		$autoApprove = $jinput->get('auto_approve', 0, 'int');
		$bookingType = $jinput->get('booking_type', '', 'string');
		$bcDollars = $jinput->get('bc_dollars', 0, 'int');
		$fullPay  = 0;
		$goForPaypal = 1;
		$fullPay = 0;

		$itemName = "";
		$itemNumber = 0;

		$elemntAmount = 0.00;
		$elemntCurrencySign = '';
		$elemntCurrencyCode = '';

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_bcted/tables');

		$user    = JFactory::getUser();

		if ($user->id == 0)
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?option=com_users&view=login');
			$app->close();
		}

		if ($bookingID == 0)
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?success=false1');
			$app->close();
		}

		if (empty($bookingType))
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?success=false2');
			$app->close();
		}

		$bctParams = $this->getExtensionParam();
		$merchantId = $bctParams->merchantId;

		if(empty($merchantId))
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?success=false3');
			$app->close();
		}

		$commission = 0;
		$invitedIDStr = '';

		if($bookingType == 'package' || $bookingType == 'Package')
		{
			$bookingType = "package";
			$tblBooking  = JTable::getInstance('PackagePurchased', 'BctedTable');
			$tblPackage  = JTable::getInstance('Package', 'BctedTable');
			$fullPay     = $jinput->get('full_payment', 0, 'int');
			$tblBooking->load($bookingID);


			if(!$tblBooking->package_purchase_id)
			{
				$app = JFactory::getApplication();
				$app->redirect('index.php?success=false4');
				$app->close();
			}

			$tblPackage->load($tblBooking->package_id);

			if(!$tblPackage->package_id || $tblPackage->package_price == 0)
			{
				$app = JFactory::getApplication();
				$app->redirect('index.php?success=false5');
				$app->close();
			}

			$itemNumber   = $tblPackage->package_id;
			$itemName     = $tblPackage->package_name;
			//$elemntAmount = $tblBooking->total_price;

			if($fullPay)
			{
				$invitedPersons = $this->checkForInvitedPaymentDone($tblPackage->package_id,$tblBooking->package_purchase_id);

				$invitedPrice = 0;
				$invitedIDs = array();

				if($tblBooking->invited_email_count)
				{
					foreach ($invitedPersons as $key => $personDetail)
					{
						$invitedPrice = $invitedPrice + $personDetail->amount_payable;
						$invitedIDs[] = $personDetail->package_invite_id;
					}

					$elemntAmount = $invitedPrice;

					$invitedIDStr = implode(",", $invitedIDs);
				}
				else
				{
					$elemntAmount = $tblBooking->total_price;
					$invitedIDStr = "";
				}
			}
			else
			{
				$elemntAmount = $tblBooking->total_price;
			}

			$elemntCurrencySign = $tblPackage->currency_sign;
			$elemntCurrencyCode = $tblPackage->currency_code;
		}
		else if($bookingType == 'venue' || $bookingType == 'Venue')
		{
			$bookingType = "venue";
			$tblBooking  = JTable::getInstance('Venuebooking', 'BctedTable');
			$tblVenue    = JTable::getInstance('Venue', 'BctedTable');
			$tblTable    = JTable::getInstance('Table', 'BctedTable');

			$tblBooking->load($bookingID);

			if(!$tblBooking->venue_booking_id)
			{
				$app = JFactory::getApplication();
				$app->redirect('index.php?success=false6');
				$app->close();
			}

			//$tblBooking->user_status = 12;
			$tblBooking->store();
			$tblVenue->load($tblBooking->venue_id);

			if(!$tblVenue->venue_id || $tblVenue->venue_active == 0)
			{
				$app = JFactory::getApplication();
				$app->redirect('index.php?success=false7');
				$app->close();
			}

			$commission         = $tblVenue->commission_rate;
			$elemntCurrencySign = $tblVenue->currency_sign;
			$elemntCurrencyCode = $tblVenue->currency_code;

			$tblTable->load($tblBooking->venue_table_id);

			if(!$tblTable->venue_table_id || $tblTable->venue_table_price == 0 || !$tblTable->venue_table_active || $tblTable->venue_id != $tblVenue->venue_id )
			{
				$app = JFactory::getApplication();
				$app->redirect('index.php?success=false8');
				$app->close();
			}

			$itemNumber   = $tblTable->venue_table_id;
			$itemName     = ($tblTable->premium_table_id)?$tblTable->venue_table_name:$tblTable->custom_table_name;
			$elemntAmount = $tblTable->venue_table_price;
		}
		else if($bookingType == 'service' || $bookingType == 'Service')
		{
			$bookingType       = "service";
			$tblCompany        = JTable::getInstance('Company', 'BctedTable');
			$tblServiceBooking = JTable::getInstance('ServiceBooking', 'BctedTable');
			$tblService        = JTable::getInstance('Service', 'BctedTable');

			$tblServiceBooking->load($bookingID);

			if(!$tblServiceBooking->service_booking_id)
			{
				$app = JFactory::getApplication();
				$app->redirect('index.php?success=false9');
				$app->close();
			}

			$tblCompany->load($tblServiceBooking->company_id);

			if(!$tblCompany->company_id || $tblCompany->company_active==0)
			{
				$app = JFactory::getApplication();
				$app->redirect('index.php?success=false10');
				$app->close();
			}

			//$tblServiceBooking->user_status = 12;
			$tblServiceBooking->store();

			$commission = $tblCompany->commission_rate;
			$elemntCurrencySign=$tblCompany->currency_sign;
			$elemntCurrencyCode=$tblCompany->currency_code;

			$tblService->load($tblServiceBooking->service_id);

			if(!$tblService->service_id || $tblService->service_price <= 0 || !$tblService->service_active || $tblService->company_id != $tblCompany->company_id )
			{
				$app = JFactory::getApplication();
				$app->redirect('index.php?success=false11');
				$app->close();
			}

			$itemNumber   = $tblService->service_id;
			$itemName     = $tblService->service_name;
			$itemName     = $tblService->service_name;
			$elemntAmount = $tblServiceBooking->deposit_amount;
			$elemntAmountForPoints = $tblServiceBooking->total_price;
		}

		$PaymentStatus = JTable::getInstance('PaymentStatus', 'BctedTable');
		$PaymentStatus->load(0);

		$convertExp = "";

		if($elemntCurrencyCode!="USD")
		{
			$convertExp = $elemntCurrencyCode."_USD";
		}

		$currencyRateUSD = 0;

		if(!empty($convertExp))
		{
			/*$url = "http://www.freecurrencyconverterapi.com/api/v3/convert?q=".$convertExp."&compact=y";
			$ch = curl_init();
			$timeout = 0;
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$rawdata = curl_exec($ch);
			curl_close($ch);
			if(empty($rawdata))
			{
				$app = JFactory::getApplication();
				$app->redirect('index.php?success=false12');
				$app->close();
			}
			$object = json_decode($rawdata);
			$currencyRateUSD = $object->$convertExp->val;*/
			$currencyRateUSD = $this->convertCurrencyGoogle(1,$elemntCurrencyCode,'USD');

		}

		/*echo "<pre>";
		print_r($currencyRateUSD);
		echo "</pre>";
		exit;*/

		$depositPer = ($bctParams->deposit_per)?$bctParams->deposit_per:20;
		$depositPer = ($commission)?$commission:$depositPer;
		$loyaltyPer = ($bctParams->loyalty_per)?$bctParams->loyalty_per:1;

		/*echo "deposit Per : " . $depositPer."<br />";
		echo "loyaltyPer Per : " . $loyaltyPer."<br />";
		exit;*/

		if($bookingType == 'package')
		{
			$depositAmount = round($elemntAmount);
		}
		else
		{
			if($bookingType == 'service' || $bookingType == 'Service')
			{
				$depositAmount = $elemntAmount;
			}
			else
			{
				$depositAmount = ($elemntAmount * $depositPer) / 100;

				$depositAmount = round($depositAmount);
			}

			//$depositAmount = $elemntAmount;
		}

		/*echo "Deposit Amount : " . $depositAmount;
		// 100
		exit;*/

		$bctParams = $this->getExtensionParam();
		$merchantId = $bctParams->merchantId;

		if(empty($merchantId))
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?success=false3');
			$app->close();
		}

		/*echo "Element Amount : " .$elemntAmount . "<br />";
		echo "currencyRateUSD : " . $currencyRateUSD . "<br />";*/
		if($currencyRateUSD)
		{
			// 1 ero = 1.13 $
			// 1000 ero = ?
			$convertedElementAmount = $elemntAmount * $currencyRateUSD;
		}
		else
		{
			$convertedElementAmount = $elemntAmount;
		}

		/*echo "Total Element Price Converted : ".$convertedElementAmount . "<br />";
		exit;*/


		if($bookingType == 'service' || $bookingType == 'Service')
		{
			//$elemntAmountForPoints
			if($currencyRateUSD)
			{
				$loyaltyPoints = $elemntAmountForPoints * $currencyRateUSD;
			}
			else
			{
				$loyaltyPoints = $elemntAmountForPoints;
			}

			$loyaltyPoints = ($loyaltyPoints * $loyaltyPer) / 100;

		}
		else
		{
			$loyaltyPoints = ($convertedElementAmount * $loyaltyPer) / 100;
		}

		/*echo $loyaltyPoints;
		exit;*/

		//$loyaltyPoints = number_format($loyaltyPoints,2);

		if($bcDollars)
		{
			$bcdToUsd = $bcDollars / 10;
			$bcdToUsd = number_format($bcdToUsd);

			//$currencyRate = $this->convertCurrency('USD',$elemntCurrencyCode);
			$currencyRate = $this->convertCurrencyGoogle(1,'USD',$elemntCurrencyCode);
			$bcdToUsd = $bcdToUsd * $currencyRate;

			//echo "BC Dollars : " . $bcdToUsd . "<br />";exit;

			$payAfterBCD = $depositAmount - $bcdToUsd;

			$minusBCD = $bcDollars * 2;
			$minusBCD = $bcDollars - $minusBCD;

			if($payAfterBCD > 0)
			{
				$depositAmountAfertBCD = $payAfterBCD;
				$depositAmountAfertBCD = number_format($depositAmountAfertBCD,2);
				$goForPaypal = 1;
			}
			else
			{
				$goForPaypal = 0;
			}

			/*echo $goForPaypal;
			exit;*/
		}


		$paymentData['booked_element_id']   = $bookingID;
		$paymentData['booked_element_type'] = $bookingType;
		$paymentData['currency_code']       = $elemntCurrencyCode;
		$paymentData['currency_sign']       = $elemntCurrencySign;
		$paymentData['customer_paid']       = $elemntAmount;
		$paymentData['element_price']       = $elemntAmount;
		//$paymentData['platform_income']     = ($bcDollars)?$depositAmountAfertBCD:$depositAmount;
		$paymentData['platform_income']     = $depositAmount;
		$paymentData['element_income']      = $elemntAmount;
		$paymentData['bcd_used']            = $bcDollars;
		$paymentData['paid_status']         = 0;
		$paymentData['time_stamp']          = time();
		$paymentData['created']             = date('Y-m-d H:i:s');
		$paymentData['full_payment']        = $fullPay;

		/*echo $depositAmount."<pre>";
		print_r($paymentData);
		echo "</pre>";
		exit;*/

		$PaymentStatus->bind($paymentData);
		if(!$PaymentStatus->store())
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?success=false13');
			$app->close();

			return false;
		}

		$paymentEntryID = $PaymentStatus->payment_id;

		if($bcDollars)
		{
			if($goForPaypal == 1)
			{
				$this->addLoyaltyPoints($user->id,'Payout',$minusBCD,$paymentEntryID);
				$loyaltyPoints = ($payAfterBCD * $loyaltyPer) / 100;

				$lpAmount = $elemntAmount - $depositAmountAfertBCD;
				$loyaltyPoints = ($lpAmount * $loyaltyPer) / 100;

				$this->addLoyaltyPoints($user->id,'purchase.'.$bookingType,($loyaltyPoints * 10),$paymentEntryID);
			}
			else
			{
				$this->addLoyaltyPoints($user->id,'Payout',$minusBCD,$paymentEntryID,1);
			}

			//$payAfterBCD * 10;



		}
		else
		{
			$this->addLoyaltyPoints($user->id,'purchase.'.$bookingType,($loyaltyPoints * 10),$paymentEntryID);
		}




		$actionType  = "PAY";
		$cancelUrl   = JURI::base() . "index.php?success=false";
		$redirectUrl = JURI::base() . 'index.php?option=com_bcted&payment_entry_id='.$paymentEntryID.'&task=packageorder.ipn';
		/*echo $redirectUrl;
		exit;*/
		if($bcDollars)
		{
			$amountToPaypal = $depositAmountAfertBCD;
		}
		else
		{
			$amountToPaypal = $depositAmount;
		}

		/*echo $amountToPaypal;
		exit;*/

		/*if($elemntCurrencyCode=="AED" && $currencyRateUSD)
		{
			$amountToPaypal = $amountToPaypal * $currencyRateUSD;
			$amountToPaypal = number_format($amountToPaypal,2);
			$elemntCurrencyCode = "USD";


			GBP - No
			AUD - No
			CAD - No
			USD - Yes
			AED - Yes
			EUR -No
		}*/

		$userDetail = $this->getUserProfile();

		$address = $this->getAddressDetail($userDetail->city);
		/*echo "<pre>";
		print_r($address);
		echo "</pre>";
		exit;*/

		//$amountToPaypal = "1";
		/*echo $elemntCurrencyCode;
		exit;*/

		$post_variables = Array(
			"merchant_id"			=> $merchantId,
			"order_id"				=> $paymentEntryID,
			"amount"				=> $amountToPaypal,
			"currency"				=> $elemntCurrencyCode,
			"redirect_url"			=> $redirectUrl,
			"cancel_url"			=> $cancelUrl,
			"language"				=> 'EN',
			"billing_name" 			=> $userDetail->name . ' ' .$userDetail->last_name,
			"billing_address"  		=> ($address['full_address'])?$address['full_address']:$userDetail->city,
			"billing_city"  		=> ($address['city'])?$address['city']:$userDetail->city,
			"billing_state"  		=> ($address['state'])?$address['state']:$userDetail->city,
			"billing_zip"  			=> ($address['postal_code'])?$address['postal_code']:'',
			"billing_country"  		=> ($address['country'])?$address['country']:'',
			"billing_tel" 			=> $userDetail->phoneno,
			"billing_email" 		=> $userDetail->email,
			"delivery_name"  		=> $userDetail->name . ' ' .$userDetail->last_name,
			"delivery_address" 		=> ($address['full_address'])?$address['full_address']:$userDetail->city,
			"delivery_city" 		=> ($address['city'])?$address['city']:$userDetail->city,
			"delivery_state"  		=> ($address['state'])?$address['state']:$userDetail->city,
			"delivery_zip"  		=> ($address['postal_code'])?$address['postal_code']:'',
			"delivery_country" 		=> ($address['country_short'])?$address['country_short']:'',
			"delivery_tel"  		=> $userDetail->phoneno,
			"merchant_param1"    	=> $paymentEntryID
		);

		/*$data['button_confirm'] = JText::_ ('redirected to ccavenue.');
		$data['access_code']	= $bctParams->accessCode;

		$passdata 				= array("merchantdata"=>$post_variables,"encryptkey"=>$bctParams->encryptionKey,"data"=>$data);
*/
		/*echo $goForPaypal . " : Go for paypal";
		exit;*/
		/*echo $autoApprove."<pre>";
		print_r($post_variables);
		echo "</pre>";
		exit;*/
		if ($goForPaypal)
		{


			$customer_info_array = array();
			foreach ($post_variables as $key => $value)
			{
				$customer_info_array[] = $key.'='.urlencode($value);
			}
			$customer_info = implode("&",$customer_info_array);

			$encrypted_data = $this->encrypt($customer_info,$bctParams->encryptionKey);

			$access_code = $bctParams->accessCode;

			if($autoApprove == 0)
			{
				echo '<form action="https://secure.ccavenue.ae/transaction/transaction.do?command=initiateTransaction" method="post" id="paymentform" name="paymentform">
					<input type="hidden" name="encRequest" id="encRequest" value="'.$encrypted_data.'" />
					<input type="hidden" name="access_code" id="access_code" value="'.$access_code.'" />
				</form>';
				echo '<script type="text/javascript">document.paymentform.submit()</script>';
			}
			else
			{
				//http://thebeseated.com/dev/index.php?option=com_bcted&task=packageorder.packagePurchased&booking_id=117&booking_type=venue&Itemid=220&

				$res_variables = Array(
					"option"           => "com_bcted",
					"payment_entry_id" => $paymentEntryID,
					"task"             => "packageorder.ipn",
					"encResp"          => "bfcffea2e131bd124435273d17b003745478cfbc11766708615a2c8c0198997c9b06e28cd41700c0be8467d6be22719308c06139360d058792e21975dc67abde756fd71734cf6c3dbfaea5d2841831c1634caf67c5fef08730f594e344f2c5d43b57f30e974b2fcd50258323e31a7e422cb735046f52ebc267d84a9bf3aedbb8aca1b21a392a4743f51891eccdef46713671e916f0cc86a8c21f6c7c9012b5274fafb67c2ece95a128ef3407ddaa1393deb36c3d4e711fecd7ed10d549c9ab6718707cb776ed40be259dec22dedec8e173a1454e22f2065fa138d93c84f356e27df2ad9c94d9ad7f587c5fcb3bdb5c99f566f261d9301c0bfb69fa9d5c0af0e6d626c0ccdcd6bdedb907d5c4ad356f95e75326783875ee84933d23122bec8e22ac116e84ce82f694d5c8cc624c1c2e9b7794d85630cacd42e3b3eebc7650e26c148e3715cb4ce44ebebe3806334dca2155ebf847b284973e70caa50c14c2f6f175315db3a00443f20bea6bd01ed3b4326097a3ef88bb5bf7bef93c88339be52a532f5955186bf01c6fea2e6efa57374d18a014469bbfe77eeaa16802ed81cbb753ee27384cfd091cf4cd3a568953f63dcb66c93d220eab112401cd3b643e5f82db00cadaae4f6fd713b5bd!
 d98931d22a683a347c37f708ab7cd9e404cf66582dea7426e2595c7a5afa4a34fbe759c08ff456f50898bbc2f05fb981e48769c3d566c79e2400d3146b6633f361b3b43cdcf7c29e2353819470c4d688da7a72c128a1c221e08eefb38673b812d47760e2cf7809be3e56a357311a7c3bba1b6ceae2ac9688c2a70de3ebd9db84e99764abc516824516ec944b57ba3168482b6fe6f44cdfb9fbf50f15d2d7b6fd36d4efa90abbe59ef2680c6bb5e0db707cc10f941324164ac6acc150af08218897f86e528cf5711d5ef9c5284a33f379470dcadf812d13fad8735bcd1e3db16a32be7f721830c56e6f2253c220144299c8702efa25555ea9e35e50c3174025e06372064625c69c8d95b0052816c7d66c13fa843180"

				);

				echo '<form action="'.JUri::base().'index.php?option=com_bcted&payment_entry_id='.$paymentEntryID.'&task=packageorder.ipn&auto_approve=1" method="post" name="autopayment" id="autopayment">';

				foreach ($res_variables as $name => $value)
				{
					echo "<input type='hidden' name='$name' value='$value' />";
				}


				echo '<INPUT TYPE="hidden" name="charset" value="utf-8">';
				echo "</form>";
				?>
				<script type='text/javascript'>document.autopayment.submit();</script>
				<?php

				/*$responseData =  array();
 				$responseData['option'] = "com_bcted";
 				$responseData['payment_entry_id'] = $paymentEntryID;
 				$responseData['task'] = "packageorder.ipn";
 				$responseData['encResp'] = "bfcffea2e131bd124435273d17b003745478cfbc11766708615a2c8c0198997c9b06e28cd41700c0be8467d6be22719308c06139360d058792e21975dc67abde756fd71734cf6c3dbfaea5d2841831c1634caf67c5fef08730f594e344f2c5d43b57f30e974b2fcd50258323e31a7e422cb735046f52ebc267d84a9bf3aedbb8aca1b21a392a4743f51891eccdef46713671e916f0cc86a8c21f6c7c9012b5274fafb67c2ece95a128ef3407ddaa1393deb36c3d4e711fecd7ed10d549c9ab6718707cb776ed40be259dec22dedec8e173a1454e22f2065fa138d93c84f356e27df2ad9c94d9ad7f587c5fcb3bdb5c99f566f261d9301c0bfb69fa9d5c0af0e6d626c0ccdcd6bdedb907d5c4ad356f95e75326783875ee84933d23122bec8e22ac116e84ce82f694d5c8cc624c1c2e9b7794d85630cacd42e3b3eebc7650e26c148e3715cb4ce44ebebe3806334dca2155ebf847b284973e70caa50c14c2f6f175315db3a00443f20bea6bd01ed3b4326097a3ef88bb5bf7bef93c88339be52a532f5955186bf01c6fea2e6efa57374d18a014469bbfe77eeaa16802ed81cbb753ee27384cfd091cf4cd3a568953f63dcb66c93d220eab112401cd3b643e5f82db00cadaae4f6fd713b5bd!
 d98931d22a683a347c37f708ab7cd9e404cf66582dea7426e2595c7a5afa4a34fbe759c08ff456f50898bbc2f05fb981e48769c3d566c79e2400d3146b6633f361b3b43cdcf7c29e2353819470c4d688da7a72c128a1c221e08eefb38673b812d47760e2cf7809be3e56a357311a7c3bba1b6ceae2ac9688c2a70de3ebd9db84e99764abc516824516ec944b57ba3168482b6fe6f44cdfb9fbf50f15d2d7b6fd36d4efa90abbe59ef2680c6bb5e0db707cc10f941324164ac6acc150af08218897f86e528cf5711d5ef9c5284a33f379470dcadf812d13fad8735bcd1e3db16a32be7f721830c56e6f2253c220144299c8702efa25555ea9e35e50c3174025e06372064625c69c8d95b0052816c7d66c13fa843180";
 				$responseData['Itemid'] = "";*/
 				// /'index.php?option=com_bcted&payment_entry_id='.$paymentEntryID.'&task=packageorder.ipn';

 				/*echo "call" . json_decode($json);
 				exit;*/

			}

			/*echo "call end";
 			exit;*/

		}
		else
		{
			$tblPaymentStatus = JTable::getInstance('PaymentStatus', 'BctedTable',array());
			$tblPaymentStatus->load($paymentEntryID);

			/*echo "<pre>";
			print_r($tblPaymentStatus);
			echo "</pre>";
			exit;*/

			if(!$tblPaymentStatus->payment_id)
			{
				$app = JFactory::getApplication();
				$app->redirect("index.php?success=false14");
				$app->close();
			}

			$elementID = $tblPaymentStatus->booked_element_id;
			$elementType = $tblPaymentStatus->booked_element_type;

			$paymentGross = $depositAmount; //$input->get('payment_gross',0.00);
			$paymentFee   = 0.00; //$input->get('payment_fee',0.00);
			$txnID        = 'paybybcd';//$input->get('txn_id','','string');
			$paymentStatusText  = $bookingType;

			$tblPaymentStatus->payment_fee = $paymentFee;
			$tblPaymentStatus->txn_id = $txnID;
			$tblPaymentStatus->payment_status = $paymentStatusText;

			/*if($paymentStatusText == 'Completed')
			{*/
				$tblPaymentStatus->paid_status = 1;

				if($elementType == 'service')
				{
					$tblServiceBooking = JTable::getInstance('ServiceBooking', 'BctedTable');
					$tblServiceBooking->load($elementID);
					$tblServiceBooking->status = 5;
					$tblServiceBooking->user_status = 5;

					$tblServiceBooking->total_price = $tblPaymentStatus->element_price;
					$tblServiceBooking->deposit_amount = $tblPaymentStatus->platform_income;
					$tblServiceBooking->deposit_paid_date = date('Y-m-d');
					//$tblServiceBooking->amount_payable = $tblPaymentStatus->element_price - $tblPaymentStatus->platform_income;

					$tblServiceBooking->store();
				}
				else if($elementType == 'venue')
				{
					$tblVenuebooking = JTable::getInstance('Venuebooking', 'BctedTable');
					$tblVenuebooking->load($elementID);
					$tblVenuebooking->status = 5;
					$tblVenuebooking->user_status = 5;

					$tblVenuebooking->total_price = $tblPaymentStatus->element_price;
					$tblVenuebooking->deposit_amount = $tblPaymentStatus->platform_income;
					$tblVenuebooking->amount_payable = $tblPaymentStatus->element_price - $tblPaymentStatus->platform_income;
					$tblVenuebooking->deposit_paid_date = date('Y-m-d');
					/*echo "<pre>";
					print_r($tblVenuebooking);
					echo "</pre>";
					exit;*/
					$tblVenuebooking->store();
				}
				else if($elementType == 'package')
				{
					$tblPackagePurchased = JTable::getInstance('PackagePurchased', 'BctedTable');
					$tblPackagePurchased->load($elementID);
					$tblPackagePurchased->status = 5;
					$tblPackagePurchased->user_status = 5;
					$tblPackagePurchased->store();
				}

				// Initialiase variables.
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Create the base update statement.
				$query->update($db->quoteName('#__bcted_loyalty_point'))
					->set($db->quoteName('is_valid') . ' = ' . $db->quote(1))
					->where($db->quoteName('cid') . ' = ' . $db->quote($paymentEntryID));

				// Set the query and execute the update.
				$db->setQuery($query);
				$db->execute();
			//}

			$tblPaymentStatus->store();

			$app = JFactory::getApplication();
			$app->redirect("index.php?success=true&payment_entry_id={$paymentEntryID}");
			$app->close();

		}
		return true;
	}

	public function checkForInvitedPaymentDone($package_id,$package_purchase_id)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__bcted_package_invite'))
			->where($db->quoteName('package_id') . ' = ' . $db->quote($package_id))
			->where($db->quoteName('package_purchase_id') . ' = ' . $db->quote($package_purchase_id))
			->where($db->quoteName('status') . ' = ' . $db->quote('2'));

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObjectList();

		return $result;
	}

	public function getAddressDetail($cityName)
	{
		$cityName = str_replace(" ", "+", $cityName);
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.trim($cityName).'&sensor=false';
		$json = @file_get_contents($url);
		$data=json_decode($json);
		$status = $data->status;

		$formatedAddress = "";
		$returnAddress  = array();

		if($status=="OK")
		{
			foreach ($data->results as $key => $results)
			{
				$address_components = $results->address_components;
				$returnAddress['full_address'] = $results->formatted_address;

				foreach ($address_components as $key => $single_components)
				{

					$types = $single_components->types;
					$cityName = '';

					if(in_array('locality', $types))
					{
						$returnAddress['city'] = $single_components->long_name;
					}
					elseif(in_array('administrative_area_level_2',$types))
					{
						$returnAddress['city'] = $single_components->long_name;
					}

					if(in_array('administrative_area_level_1',$types))
					{
						$returnAddress['state'] = $single_components->long_name;
					}

					if(in_array('country',$types))
					{
						$returnAddress['country'] = $single_components->long_name;
						$returnAddress['country_short'] = $single_components->short_name;
					}

					if(in_array('postal_code',$types))
					{
						$returnAddress['postal_code'] = $single_components->long_name;
					}

				}

				return $returnAddress;
			}
		}
		else
		{
			return $returnAddress;
		}

	}

	public function getUserProfile()
	{
		$user = JFactory::getUser();
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('a.id,a.name,a.email')
			->from($db->quoteName('#__users','a'))
			->where($db->quoteName('a.id') . ' = ' . $db->quote($user->id));

		$query->select('b.last_name,b.phoneno,b.city')
			->join('LEFT','#__beseated_user_profile AS b ON b.userid=a.id');

		// Set the query and load the result.
		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}

	public 	function encrypt($plainText,$key)
	{
		$secretKey = $this->hextobin(md5($key));

		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);

		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');

		$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');

		$plainPad = $this->pkcs5_pad($plainText, $blockSize);
		if (mcrypt_generic_init($openMode, $secretKey, $initVector) != -1)
		{
			$encryptedText = mcrypt_generic($openMode, $plainPad);
			mcrypt_generic_deinit($openMode);
		}
		return bin2hex($encryptedText);

	}

	public function pkcs5_pad($plainText, $blockSize)
	{
		$pad = $blockSize - (strlen($plainText) % $blockSize);
		return $plainText . str_repeat(chr($pad), $pad);
	}

	public function hextobin($hexString)
	{
		$length = strlen($hexString);
		$binString="";
		$count=0;
		while($count<$length)
		{
			$subString =substr($hexString,$count,2);
			$packedString = pack("H*",$subString);
			if ($count==0)
			{
				$binString=$packedString;
			}
			else
			{
				$binString.=$packedString;
			}
			$count+=2;
		}
		return $binString;
	}

	public function convertCurrencyGoogle($amount = 1, $from, $to)
	{
		$url  = "https://www.google.com/finance/converter?a=$amount&from=$from&to=$to";
		$data = file_get_contents($url);
		preg_match("/<span class=bld>(.*)<\/span>/",$data, $converted);
		$converted = preg_replace("/[^0-9.]/", "", $converted[1]);

		return round($converted, 4);
	}

	private function convertCurrency($from,$to)
	{
		$convertExp = $from."_".$to;

		$currencyRate = 0;

		if(!empty($convertExp))
		{
			$url = "http://www.freecurrencyconverterapi.com/api/v3/convert?q=".$convertExp."&compact=y";
			$ch = curl_init();
			$timeout = 0;
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$rawdata = curl_exec($ch);
			curl_close($ch);
			if(empty($rawdata))
			{
			    return $currencyRate;
			}

			$object = json_decode($rawdata);
			$currencyRate = $object->$convertExp->val;
        }

        return $currencyRate;
	}

	private function addLoyaltyPoints($userID,$activity,$totalPrice,$paymentEntryID,$isValid = 0)
	{
		$tblLoyaltyPoint = JTable::getInstance('LoyaltyPoint', 'BctedTable');
		$tblLoyaltyPoint->load(0);

		$lpPost['user_id']    = $userID;
		$lpPost['earn_point'] = $totalPrice;
		$lpPost['point_app']  = $activity;
		$lpPost['cid']        = $paymentEntryID;
		$lpPost['is_valid']   = $isValid;
		$lpPost['created']    = date('Y-m-d H:i:s');
		$lpPost['time_stamp'] = time();


		$tblLoyaltyPoint->bind($lpPost);
		$tblLoyaltyPoint->store();
	}

	private function getExtensionParam()
	{
		$app    = JFactory::getApplication();

		$option = "com_bcted";
		$db     = JFactory::getDbo();

		$option = '%' . $db->escape($option, true) . '%';

		// Initialiase variables.
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->qn('#__extensions'))
			->where($db->qn('name') . ' LIKE ' . $db->q($option))
			->where($db->qn('type') . ' = ' . $db->q('component'))
			->order($db->qn('ordering') . ' ASC');

		// Set the query and load the result.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();

			$params = json_decode($result->params);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), 500);
		}

		return $params;
	}


}
