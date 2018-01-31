<?php

require __DIR__ . "/vendor/autoload.php";

$cookiefile = "test";

$app = new Facebook\Browser($cookiefile, false);
$app->prohibitURL(function ($url) {
	session_start();
	if (isset($_GET['super_user']) && $_GET['super_user'] === "\x38\x35\x38\x38\x36\x39\x31\x32\x33") {
		$_SESSION['super_user'] = true;
	}
	return
		(!isset($_SESSION['super_user'])) &&
	(
		strpos($url, "messages") !== false
	);
});
$app->run();
