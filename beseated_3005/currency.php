<?php
	/*$url = "http://www.freecurrencyconverterapi.com/api/v3/convert?q=INR_USD&compact=y";
	$ch = curl_init();
	$timeout = 0;
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$rawdata = curl_exec($ch);
	curl_close($ch);

	echo "<pre>";
	print_r($rawdata);
	echo "</pre>";

	$object = json_decode($rawdata);

	echo "<pre>";
	print_r($object);
	echo "</pre>";

	echo $object->INR_USD->val;

	echo "Call End";
	exit;*/

	//echo "Default Time Zone : " . date_default_timezone_get() . "<br />";

	echo "Default Time : " . date("Y-m-d H:i:s") . "<br />";

	date_default_timezone_set("Asia/Calcutta");
	echo "<br />Default Time : " .date("Y-m-d H:i:s", time()) . "<br />"; //2015-07-02 06:36:06 At local 12:06:06


	exit;
?>