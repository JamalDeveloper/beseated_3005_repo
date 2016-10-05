<?php
session_start();
define('FACEBOOK_SDK_V4_SRC_DIR', __DIR__ . '/facebook/');
require_once __DIR__ . '/facebook/autoload.php';
//require_once __DIR__ . '/facebook/Helpers/FacebookRedirectLoginHelper.php';

$fb = new Facebook\Facebook([
  'app_id' => '1014814245235568',
  'app_secret' => '080859eeba3385a964db5317b0b4bf0c',
  'default_graph_version' => 'v2.5',
]);
$helper      = $fb->getRedirectLoginHelper();

try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (isset($accessToken)) {

  $fb->setDefaultAccessToken($accessToken);
	 try {
	  $response = $fb->get('/me');
	  $userNode = $response->getGraphUser();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	  // When Graph returns an error
	  echo 'Graph returned an error: ' . $e->getMessage();
	  exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	  // When validation fails or other local issues
	  echo 'Facebook SDK returned an error: ' . $e->getMessage();
	  exit;
	}

	$plainOldArray  = $response->getDecodedBody();
	$responseFriend = $fb->get('/me/friends', $accessToken);
	$graphEdge      = $responseFriend->getGraphEdge()->asArray();
	echo "<pre>";
	print_r($graphEdge);
	echo "</pre>";
	die;
}