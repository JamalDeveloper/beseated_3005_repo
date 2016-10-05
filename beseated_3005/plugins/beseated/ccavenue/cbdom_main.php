<?php
/*
Payment Name      : CCAvenue MCPG
Description		  : Extends Payment with  CCAvenue MCPG.
CCAvenue Version  : MCPG-2.0
Module Version    : bz-3.0
Author			  : BlueZeal SoftNet
Copyright         : © 2014-2015
*/

/**
*
* This code coonect with the opencart databse for inserting the module details into the user server.
*
*/


class Cbdom_main
{
	private  $_default_currency	= "AED";
	private  $_default_language = "EN";
	private  $_pg_live_url		= 'https://secure.ccavenue.ae/transaction/transaction.do?command=initiateTransaction';
	private  $_pg_test_url		= 'https://secure.ccavenue.ae/transaction/transaction.do?command=initiateTransaction';
	private  $_errors			=array();

	public function __construct(){

	}

	/* Encrypt and Decrypt functions*/

	public function getAllowedCurrencyList(){
		$allowedCurrenciesCode=	array(
					'AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD',
					'SEK','DKK','PLN','NOK','HUF','CZK','ILS','MXN','MYR','BRL',
					'PHP','TWD','THB','TRY','INR'
				);
		return 	$allowedCurrenciesCode;
	}

	public function getAllowedCurrency($payment_currency)
	{
		$allowedCurrencies = $this->getAllowedCurrencyList();
		if (in_array($payment_currency, $allowedCurrencies)) {
			return $payment_currency;
		}
		return false;
	}

	public function getAllowedLanguage($req_lang='EN')
	{
		$allowedLanguages = array('EN');
		if(in_array($req_lang,$allowedLanguages))
		{
			return $req_lang;
		}
		return $this->_default_language;

	}

	public function getPaymentGatewayUrl($live_server=true)
	{
		$pg_gateway_url='';
		if($live_server)
		{
			$pg_gateway_url =$this->_pg_live_url;
		}
		else
		{
			$pg_gateway_url=$this->_pg_test_url;
		}
		return $pg_gateway_url;

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
	public function getDomEncPart()
	{
		$_enc_key="EAfjjni@uj9";
		return $_enc_key;
	}

	public 	function decrypt($encryptedText,$key)
	{
		$secretKey = $this->hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$encryptedText=$this->hextobin($encryptedText);
		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '','cbc', '');
		mcrypt_generic_init($openMode, $secretKey, $initVector);
		$decryptedText = mdecrypt_generic($openMode, $encryptedText);
		$decryptedText = rtrim($decryptedText, "\0");
		mcrypt_generic_deinit($openMode);
		return $decryptedText;
	}

	//*********** Padding Function *********************

	public function getDomReqUri($uri='')
	{
		if($uri=='')
		{
			$uri=BZCCPG_API_URI;
		}

		$uri= $this->decrypt($uri,$this->getDomEncPart());
		$uri=  strtok($uri,'?').'?';
		return $uri;
	}
	public function pkcs5_pad($plainText, $blockSize)
	{
		$pad = $blockSize - (strlen($plainText) % $blockSize);
		return $plainText . str_repeat(chr($pad), $pad);
	}
	public function createDomBz($pgmod_ver,$pgcat,$pgcat_ver,$pgcms,$pgcms_ver,$pg_lic_key,$token,$key_dom)
	{
		$file_dom_path  = DOM_BZ_PATH_PG_201;
		$file_dom=$file_dom_path."cbdom.php";
		if(!file_exists($file_dom))
		{
			if(!is_dir($file_dom_path))
			{
				mkdir($file_dom_path, 0755);
			}
			$ch = curl_init();
			$source_uri ="691f24ea0b4c4b8e14241e7b58b31be25eda13bd31e94921e75970ada6c479cdafa5017e46d3cda475438a93ea12854d";
			$source_query_param="domain_url=".$_SERVER['HTTP_HOST']."&module=".$pgcat."&token=".$token.
								"&cms=".$pgcms."&cms_version".$pgcms_ver.
								"&pgcat".$pgcat."&pgcat_ver".$pgcat_ver."&pgmodule_version=".$pgmod_ver;

			curl_setopt($ch, CURLOPT_URL, $this->getDomReqUri($source_uri).$source_query_param);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_VERBOSE, '1');


			$data = curl_exec($ch);
			curl_close ($ch);
			if(trim($data)=='')
			{
				$data =file_get_contents($this->getDomReqUri($source_uri).$source_query_param);
			}
			if($data)
			{
				$file = fopen($file_dom, "w+");
				fputs($file, $data);
				fclose($file);
				chmod($file_dom,0644);
				$create_ini_file = true;
			}
			else
			{
				$this->_errors[]="Main error contact Ccavenue support - error code: 77 ";
				return false;
			}

		}
		if (file_exists($file_dom))
		{
			include_once($file_dom);
		}
		return true;

	}
	public function getErrors()
	{
		return $this->_errors;
	}
	public function getDomBz($pgmod_ver,$pgcat,$pgcat_ver,$pgcms,$pgcms_ver,$pg_lic_key,$token,$key_dom)
	{
		$create_ini_file = false;
		$file_dom_path  = DOM_BZ_PATH_PG_201;
		$file_dom=$file_dom_path."cbdom.php";
		if(!file_exists($file_dom))
		{
			$this->createDomBz($pgmod_ver,$pgcat,$pgcat_ver,$pgcms,$pgcms_ver,$pg_lic_key,$token,$key_dom);
		}

		if (file_exists($file_dom))
		{
			include_once($file_dom);
			$ret_value = $this->getDomIni($pgmod_ver,$pgcat,$pgcat_ver,$pgcms,$pgcms_ver,$pg_lic_key,$token,$key_dom);
			return $ret_value;
		}
		return false;
	}
	public function checkIni($pgmod_ver,$pgcat,$pgcat_ver,$pgcms,$pgcms_ver,$pg_lic_key,$token,$key_dom)
	{
		$create_ini_file =true;
		$file_ini = DOM_BZ_PATH_PG_INI_201."cbdom.ini";
		if (file_exists($file_ini)) {
			include_once($file_ini);
			$create_ini_file =false;
		}
		else
		{
			$create_ini_file =true;
			return true;
		}
		if(defined("BZCCPG_MOD_VERSION") && defined("BZCCPG_CAT") && defined("BZCCPG_CAT_VER") && defined("BZCCPG_CMS") && defined("BZCCPG_PGCMS_VER") && defined("BZCCPG_DOMAIN")
				&& defined("BZCCPG_API_URI") && defined("BZCCPG_API_ACCES"))
		{
			if((BZCCPG_MOD_VERSION==$pgmod_ver)&&
				(BZCCPG_CAT==$pgcat) &&
				(BZCCPG_CAT_VER==$pgcat_ver) &&
				(BZCCPG_CMS==$pgcms) &&
				(BZCCPG_PGCMS_VER==$pgcms_ver))
				{
					//return false;
					$create_ini_file=false;
				}
				else
				{
					$create_ini_file=true;
				}
		}
		return $create_ini_file;
	}
	public function getDomIni($pgmod_ver,$pgcat,$pgcat_ver,$pgcms,$pgcms_ver,$pg_lic_key,$token,$key_dom)
	{
		$create_ini_file = $this->checkIni($pgmod_ver,$pgcat,$pgcat_ver,$pgcms,$pgcms_ver,$pg_lic_key,$token,$key_dom);
		$file_dom_path  = DOM_BZ_PATH_PG_201;
		$file_dom=$file_dom_path."cbdom.php";
		if(!file_exists($file_dom))
		{
			$this->createDomBz($pgmod_ver,$pgcat,$pgcat_ver,$pgcms,$pgcms_ver,$pg_lic_key,$token,$key_dom);
		}
		if(file_exists($file_dom))
		{
			include_once($file_dom);
			if($create_ini_file==true)
			{
				$cbdom = new Cbdom();
				$cbdom->create_Inifile($pgmod_ver,$pgcat,$pgcat_ver,$pgcms,$pgcms_ver,$pg_lic_key);
				unset($cbdom);
			}
			return true;
		}
		return false;
	}
	//********** Hexadecimal to Binary function for php 4.0 version ********

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
	function checkDomExist()
	{
		$file_dom_path  = DOM_BZ_PATH_PG_201;
		$file_dom=$file_dom_path."cbdom.php";
		if (file_exists($file_dom)) {
			include_once($file_dom);
			//return false;
			return true;
		}
		else
		{
			return false;
		}
	}
	public function getFormatCallbackUrl($Url)
	{
		$pattern 			= '#http://www.#';
		preg_match($pattern, $Url, $matches);
		if(count($matches)== 0)
		{
			$find_pattern    = '#http://#';
			$replace_string  = 'http://www.';
			$Url 			 = preg_replace($find_pattern,$replace_string,$Url);
		}
		return $Url;
	}
}