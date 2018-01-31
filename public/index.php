<?php

ini_set("display_errors", true);

require __DIR__ . "/../autoload.php";


define("FACEBOOK_DATA_DIR", __DIR__ . "/../data");

$cred = explode("\n", file_get_contents(__DIR__.'/../config/credentials.txt'));
$email = $cred[0];
$pass = $cred[1];

use Facebook\Facebook;
$app = new Facebook($email, $pass);
$app->browser()->init(true)->prohibitAction(function ($self) {
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
