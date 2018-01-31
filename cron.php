<?php

require __DIR__.'/autoload.php';

define('name', 'ammarfaizi2');
define('config', __DIR__.'/config/', true);
define('queue', __DIR__.'/queue/'.name, true);
define("FACEBOOK_DATA_DIR", __DIR__ . "/data");
define('data', FACEBOOK_DATA_DIR, true);

$cred = explode("\n", file_get_contents(__DIR__.'/config/credentials.txt'));
$email = $cred[0];
$pass = $cred[1];
use Facebook\Facebook;
$fb = new Facebook($email, $pass);
// $fb->reaction('2031457776883873', 'WOW');

$app = new Autolike($fb);
for ($i=0; $i < 8; $i++) { 
	$app->run();
//	sleep(2);
}
