<?php
session_start();
define('FACEBOOK_SDK_V4_SRC_DIR', __DIR__ . '/facebook/');
require_once __DIR__ . '/facebook/autoload.php';

$fb = new Facebook\Facebook([
	'app_id'                => '1014814245235568',
	'app_secret'            => '080859eeba3385a964db5317b0b4bf0c',
	'default_graph_version' => 'v2.5',
]);

$helper      = $fb->getRedirectLoginHelper();
$permissions = ['email', 'user_likes']; // optional
$loginUrl    = $helper->getLoginUrl('http://localhost/facebook/login-callback.php', $permissions);
echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
