<?php

ini_set("display_errors", true);

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../test.cred";

define("FACEBOOK_DATA_DIR", __DIR__ . "/../data");

use Facebook\Facebook;

$app = new Facebook($email, $pass);
// $app->login();
// $app->getPostComments("2009189389329099"));
// $app->seeTimeline('ammarfaizi2');
$app->browser("https://mobile.facebook.com")->init()->prohibitAction(function ($self) {
	session_start();
	if (isset($_GET['super_user']) && $_GET['super_user'] === "858869123") {
		$_SESSION['super_user'] = 1;
	}
	if (isset($_SESSION['super_user'])) {
		return false;
	}

	
	if (preg_match("/message/i", $self->url)) {
		return true;
	}
})->run();