<?php

/**
 * @package     Beseated.Plugin
 * @subpackage  com_beseated
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * Beseated CCAvenue Payment plugin
 *
 * @since  0.0.1
 */
class plgBeseatedCcavenue extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  0.0.1
	 */
	protected $autoloadLanguage = true;


	/**
	 * Plugin method with the same name as the event will be called automatically.
	 *
	 * @return boolean
	 */
	function onCCAvenuePayment()
	{
		require_once ("cbdom_main.php");
		$jinput  = JFactory::getApplication()->input;
		$bookingID   = $jinput->get('booking_id', 0, 'int');
		$bookingType = $jinput->get('booking_type', '', 'string');
		$payBalance  = $jinput->get('pay_balance', 0, 'int');
		$user        = JFactory::getUser();
		$userDetail  = $this->getUserDetail($user->id);

		/*if(!$bookingID)
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?success=false&error_code=invalid-booking-id');
			$app->close();
		}

		if(empty($bookingType))
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?success=false&error_code=invalid-booking-type');
			$app->close();
		}*/

		require_once JPATH_SITE.'/components/com_beseated/helpers/beseated.php';
		$beseatedParams = BeseatedHelper::getExtensionParam();
		/*echo "<pre>";
		print_r($beseatedParams);
		echo "</pre>";
		exit;*/
		//[merchantId] =>
   		//[encryptionKey] =>
    	//[accessCode] =>
    	//[loyalty] => 1
		//merchantId : 43381
		//encryptionKey : E5A51EDC7C791E2E062214FE10453211
		//accessCode : AVVW02CE27BX11WVXB

		$merchantId = $beseatedParams->merchantId;
		$autoApprove = $beseatedParams->auto_approve;


		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_beseated/tables');
		$tblPayment = JTable::getInstance('Payment', 'BeseatedTable');
		$tblPayment->load(0);

		$totalPrice   = 0;
		$currencyCode = '';
		if(strtolower($bookingType) == 'protection')
		{
			$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
			$tblProtectionBooking->load($bookingID);
			$totalPrice           = $tblProtectionBooking->total_price;
			$currencyCode         = $tblProtectionBooking->booking_currency_code;

			if($tblProtectionBooking->is_splitted)
			{
				$totalPrice = $tblProtectionBooking->each_person_pay;
			}

			if($payBalance)
			{
				$totalPrice = $tblProtectionBooking->remaining_amount;
			}
		}
		else if (strtolower($bookingType == 'protection.split'))
		{
			$tblProtectionBookingSplit = JTable::getInstance('ProtectionBookingSplit', 'BeseatedTable');
			$tblProtectionBooking = JTable::getInstance('ProtectionBooking', 'BeseatedTable');
			$tblProtectionBookingSplit->load($bookingID);
			$tblProtectionBooking->load($tblProtectionBookingSplit->protection_booking_id);

			$totalPrice           = $tblProtectionBookingSplit->splitted_amount;
			if($payBalance)
			{
				$totalPrice = $tblProtectionBooking->remaining_amount;
			}
			$currencyCode         = $tblProtectionBooking->booking_currency_code;
		}
		else if(strtolower($bookingType) == 'venue')
		{
			$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
			$tblVenueBooking->load($bookingID);
			if($tblVenueBooking->has_bottle){
				$totalPrice           = $tblVenueBooking->final_price;
			}else{
				$totalPrice           = $tblVenueBooking->total_price;
			}

			$currencyCode         = $tblVenueBooking->booking_currency_code;
			if($tblVenueBooking->is_splitted)
			{
				$totalPrice = $tblVenueBooking->each_person_pay;
			}

			if($payBalance)
			{
				$totalPrice = $tblVenueBooking->remaining_amount;
			}
		}
		else if (strtolower($bookingType == 'venue.split'))
		{
			$tblVenueBookingSplit = JTable::getInstance('VenueBookingSplit', 'BeseatedTable');
			$tblVenueBooking = JTable::getInstance('VenueBooking', 'BeseatedTable');
			$tblVenueBookingSplit->load($bookingID);
			$tblVenueBooking->load($tblVenueBookingSplit->venue_table_booking_split_id);

			$totalPrice           = $tblVenueBookingSplit->splitted_amount;
			if($payBalance)
			{
				$totalPrice = $tblVenueBooking->remaining_amount;
			}
			$currencyCode         = $tblVenueBooking->booking_currency_code;
		}
		else if(strtolower($bookingType) == 'chauffeur')
		{
			$tblChauffeurBooking       = JTable::getInstance('ChauffeurBooking', 'BeseatedTable');
			$tblChauffeurBooking->load($bookingID);
			$totalPrice           = $tblChauffeurBooking->total_price;
			$currencyCode         = $tblChauffeurBooking->booking_currency_code;

			if($tblChauffeurBooking->is_splitted)
			{
				$totalPrice = $tblChauffeurBooking->each_person_pay;
			}

			if($payBalance)
			{
				$totalPrice = $tblChauffeurBooking->remaining_amount;
			}
		}
		else if (strtolower($bookingType == 'chauffeur.split'))
		{
			$tblChauffeurBooking      = JTable::getInstance('ChauffeurBooking', 'BeseatedTable');
			$tblChauffeurBookingSplit = JTable::getInstance('ChauffeurBookingSplit', 'BeseatedTable');
			$tblChauffeurBookingSplit->load($bookingID);
			$tblChauffeurBooking->load($tblChauffeurBookingSplit->protection_booking_id);
			$totalPrice               = $tblChauffeurBookingSplit->splitted_amount;
			if($payBalance)
			{
				$totalPrice = $tblChauffeurBooking->remaining_amount;
			}
			$currencyCode             = $tblChauffeurBooking->booking_currency_code;
		}
		else if(strtolower($bookingType) == 'yacht')
		{
			$tblYachtBooking = JTable::getInstance('YachtBooking', 'BeseatedTable');
			$tblYachtBooking->load($bookingID);
			$totalPrice      = $tblYachtBooking->total_price;
			$currencyCode    = $tblYachtBooking->booking_currency_code;

			if($tblYachtBooking->is_splitted)
			{
				$totalPrice = $tblYachtBooking->each_person_pay;
			}

			if($payBalance)
			{
				$totalPrice = $tblYachtBooking->remaining_amount;
			}
		}
		else if (strtolower($bookingType == 'yacht.split'))
		{
			$tblYachtBooking      = JTable::getInstance('YachtBooking', 'BeseatedTable');
			$tblYachtBookingSplit = JTable::getInstance('YachtBookingSplit', 'BeseatedTable');
			$tblYachtBookingSplit->load($bookingID);
			$tblYachtBooking->load($tblYachtBookingSplit->protection_booking_id);
			$totalPrice           = $tblYachtBookingSplit->splitted_amount;
			if($payBalance)
			{
				$totalPrice = $tblYachtBooking->remaining_amount;
			}
			$currencyCode         = $tblYachtBooking->booking_currency_code;
		}
		else if (strtolower($bookingType == 'event'))
		{
			$tblTicketBooking = JTable::getInstance('TicketBooking', 'BeseatedTable');
			$tblEvent         = JTable::getInstance('Event', 'BeseatedTable');
			$tblTicketBooking->load($bookingID);
			$tblEvent->load($tblTicketBooking->event_id);
			$totalPrice       = $tblTicketBooking->total_price;
			$currencyCode     = $tblEvent->currency_code;
		}

		/*echo "<br />totalPrice : " . $totalPrice;
		echo "<br />currencyCode : " . $currencyCode;
		die('<br />Final Exit');*/

		$paymentPost = array();
		$paymentPost['booking_id']   = $bookingID;
		$paymentPost['booking_type'] = $bookingType;
		$paymentPost['user_id']      = $userDetail->user_id;
		$paymentPost['amount']       = $totalPrice;

		/*echo "<pre>";
		print_r($paymentPost);
		echo "</pre>";
		exit;*/

		$tblPayment->bind($paymentPost);
		$tblPayment->store();
		$address = $this->getAddressDetail($userDetail->location);
		if(!isset($address['full_address'])){
			$address['full_address'] = '';
		}
		if(!isset($address['city'])){
			$address['city'] = '';
		}
		if(!isset($address['state'])){
			$address['state'] = '';
		}
		if(!isset($address['postal_code'])){
			$address['postal_code'] = '';
		}
		if(!isset($address['country'])){
			$address['country'] = '';
		}
		/*echo "<pre>";
		print_r($address);
		echo "</pre>";
		exit;*/

		if(strtolower($bookingType) == 'protection'){
			$this->calculateLoyaltyPoint($totalPrice,$currencyCode,$beseatedParams->loyalty,'purchase.protection',$tblPayment->payment_id,0);
		}else if (strtolower($bookingType == 'protection.split')){
			$this->calculateLoyaltyPoint($totalPrice,$currencyCode,$beseatedParams->loyalty,'purchase.splited.protection',$tblPayment->payment_id,0);
		}else if(strtolower($bookingType) == 'venue'){
			$this->calculateLoyaltyPoint($totalPrice,$currencyCode,$beseatedParams->loyalty,'purchase.venue',$tblPayment->payment_id,0);
		}else if(strtolower($bookingType) == 'venue.split'){
			$this->calculateLoyaltyPoint($totalPrice,$currencyCode,$beseatedParams->loyalty,'purchase.splited.venue',$tblPayment->payment_id,0);
		}else if(strtolower($bookingType) == 'chauffeur'){
			$this->calculateLoyaltyPoint($totalPrice,$currencyCode,$beseatedParams->loyalty,'purchase.chauffeur',$tblPayment->payment_id,0);
		}else if (strtolower($bookingType == 'chauffeur.split')){
			$this->calculateLoyaltyPoint($totalPrice,$currencyCode,$beseatedParams->loyalty,'purchase.splited.chauffeur',$tblPayment->payment_id,0);
		}else if(strtolower($bookingType) == 'yacht'){
			$this->calculateLoyaltyPoint($totalPrice,$currencyCode,$beseatedParams->loyalty,'purchase.yacht',$tblPayment->payment_id,0);
		}else if (strtolower($bookingType == 'yacht.split')){
			$this->calculateLoyaltyPoint($totalPrice,$currencyCode,$beseatedParams->loyalty,'purchase.splited.yacht',$tblPayment->payment_id,0);
		}else if (strtolower($bookingType == 'event')){
			$this->calculateLoyaltyPoint($totalPrice,$currencyCode,$beseatedParams->loyalty,'purchase.event',$tblPayment->payment_id,0);
		}


		$cancelUrl   = JURI::base() . "index.php?success=false&error_code=cancel";
		$redirectUrl = JURI::base() . 'index.php?option=com_beseated&payment_id='.$tblPayment->payment_id.'&task=payment.ipn';
		$post_variables = Array(
			"merchant_id"			=> $merchantId,
			"order_id"				=> $tblPayment->payment_id,
			"amount"				=> $totalPrice,
			"currency"				=> $currencyCode,
			"redirect_url"			=> $redirectUrl,
			"cancel_url"			=> $cancelUrl,
			"language"				=> 'EN',
			"billing_name" 			=> $userDetail->full_name,
			"billing_address"  		=> ($address['full_address'])?$address['full_address']:$userDetail->location,
			"billing_city"  		=> ($address['city'])?$address['city']:$userDetail->city,
			"billing_state"  		=> ($address['state'])?$address['state']:$userDetail->city,
			"billing_zip"  			=> ($address['postal_code'])?$address['postal_code']:'',
			"billing_country"  		=> ($address['country'])?$address['country']:'',
			"billing_tel" 			=> $userDetail->phone,
			"billing_email" 		=> $userDetail->email,
			"delivery_name"  		=> $userDetail->full_name,
			"delivery_address" 		=> ($address['full_address'])?$address['full_address']:$userDetail->location,
			"delivery_city" 		=> ($address['city'])?$address['city']:$userDetail->city,
			"delivery_state"  		=> ($address['state'])?$address['state']:$userDetail->city,
			"delivery_zip"  		=> ($address['postal_code'])?$address['postal_code']:'',
			"delivery_country" 		=> ($address['country_short'])?$address['country_short']:'',
			"delivery_tel"  		=> $userDetail->phone,
			"merchant_param1"    	=> $tblPayment->payment_id
		);

/*echo "<pre>";
print_r($post_variables);
echo "</pre>";
exit;*/

		$goForCCAvenue = 1;
		if ($goForCCAvenue)
		{
			$customer_info_array = array();
			foreach ($post_variables as $key => $value)
			{
				$customer_info_array[] = $key.'='.urlencode($value);
			}
			$customer_info  = implode("&",$customer_info_array);
			$encrypted_data = $this->encrypt($customer_info,$beseatedParams->encryptionKey);
			$access_code    = $beseatedParams->accessCode;

			if($autoApprove == 0)
			{
				echo '<form action="https://secure.ccavenue.ae/transaction/transaction.do" method="post" id="paymentform" name="paymentform">
					<input type="hidden" name="encRequest" id="encRequest" value="'.$encrypted_data.'" />
					<input type="hidden" name="access_code" id="access_code" value="'.$access_code.'" />
					<input type="hidden" name="command"    id="command" value="initiateTransaction"/>
				</form>';
				echo '<script type="text/javascript">document.paymentform.submit()</script>';
			}
			else
			{
				//http://thebeseated.com/dev/index.php?option=com_bcted&task=packageorder.packagePurchased&booking_id=117&booking_type=venue&Itemid=220&

				$res_variables = Array(
					"option"           => "com_beseated",
					"payment_entry_id" => $tblPayment->payment_id,
					"task"             => "payment.ipn",
					"encResp"          => "bfcffea2e131bd124435273d17b003745478cfbc11766708615a2c8c0198997c9b06e28cd41700c0be8467d6be22719308c06139360d058792e21975dc67abde756fd71734cf6c3dbfaea5d2841831c1634caf67c5fef08730f594e344f2c5d43b57f30e974b2fcd50258323e31a7e422cb735046f52ebc267d84a9bf3aedbb8aca1b21a392a4743f51891eccdef46713671e916f0cc86a8c21f6c7c9012b5274fafb67c2ece95a128ef3407ddaa1393deb36c3d4e711fecd7ed10d549c9ab6718707cb776ed40be259dec22dedec8e173a1454e22f2065fa138d93c84f356e27df2ad9c94d9ad7f587c5fcb3bdb5c99f566f261d9301c0bfb69fa9d5c0af0e6d626c0ccdcd6bdedb907d5c4ad356f95e75326783875ee84933d23122bec8e22ac116e84ce82f694d5c8cc624c1c2e9b7794d85630cacd42e3b3eebc7650e26c148e3715cb4ce44ebebe3806334dca2155ebf847b284973e70caa50c14c2f6f175315db3a00443f20bea6bd01ed3b4326097a3ef88bb5bf7bef93c88339be52a532f5955186bf01c6fea2e6efa57374d18a014469bbfe77eeaa16802ed81cbb753ee27384cfd091cf4cd3a568953f63dcb66c93d220eab112401cd3b643e5f82db00cadaae4f6fd713b5bd!
 d98931d22a683a347c37f708ab7cd9e404cf66582dea7426e2595c7a5afa4a34fbe759c08ff456f50898bbc2f05fb981e48769c3d566c79e2400d3146b6633f361b3b43cdcf7c29e2353819470c4d688da7a72c128a1c221e08eefb38673b812d47760e2cf7809be3e56a357311a7c3bba1b6ceae2ac9688c2a70de3ebd9db84e99764abc516824516ec944b57ba3168482b6fe6f44cdfb9fbf50f15d2d7b6fd36d4efa90abbe59ef2680c6bb5e0db707cc10f941324164ac6acc150af08218897f86e528cf5711d5ef9c5284a33f379470dcadf812d13fad8735bcd1e3db16a32be7f721830c56e6f2253c220144299c8702efa25555ea9e35e50c3174025e06372064625c69c8d95b0052816c7d66c13fa843180"
				);

				echo '<form action="'.JUri::base().'index.php?option=com_beseated&payment_id='.$tblPayment->payment_id.'&task=payment.ipn&auto_approve=1&pay_balance='.$payBalance.'" method="post" name="autopayment" id="autopayment">';
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
		/*die('In CCAvenue payment');*/

		return true;
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

	public function getUserDetail($userID)
	{
		// Initialiase variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*')
			->from($db->quoteName('#__beseated_user_profile'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userID));

		// Set the query and load the result.
		$db->setQuery($query);

		return $db->loadObject();

	}

	public function calculateLoyaltyPoint($amount,$currencyCode,$loyalty,$app,$cid,$isValid = 0)
	{
		$amountInUSD = $this->convertCurrencyGoogle($amount,$currencyCode,'USD');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_beseated/tables');
		$tblLoyaltyPoint = JTable::getInstance('LoyaltyPoint', 'BeseatedTable');
		$tblLoyaltyPoint->load(0);
		$user = JFactory::getUser();

		//echo "<pre/>";print_r($amountInUSD);exit;

		$loyaltyPoint = (($amountInUSD * $loyalty)/100)*10;
		$loyaltyPost['user_id']    = $user->id;
		$loyaltyPost['money_used'] = $amount;
		$loyaltyPost['money_usd']  = $amountInUSD;
		$loyaltyPost['earn_point'] = $loyaltyPoint;
		$loyaltyPost['point_app']  = $app;
		$loyaltyPost['cid']        = $cid;
		$loyaltyPost['is_valid']   = $isValid;
		$loyaltyPost['time_stamp'] = time();
		$loyaltyPost['money_used'] = $amount;
		$tblLoyaltyPoint->bind($loyaltyPost);
		$tblLoyaltyPoint->store();

		return $tblLoyaltyPoint->loyalty_point_id;
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
		if(count($converted) == 0){
			return 1;
		}
		$converted = preg_replace("/[^0-9.]/", "", $converted[1]);

		return round($converted, 2);
	}


}
