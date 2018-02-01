<?php

require __DIR__.'/autoload.php';

// name
define('name', 'ammarfaizi93');

// config dir
define('config', __DIR__.'/config/', true);

/**
 * Config file
 */
define('target', config.'/target/target_ammarfaizi93.txt');
define('token_file', config.'/token/token_ammarfaizi93.txt');
is_dir(config.'/token/') or mkdir(config.'/token/');

// queue
define('queue', __DIR__.'/queue/'.name, true);

// data
define("FACEBOOK_DATA_DIR", __DIR__ . "/data");
define('data', FACEBOOK_DATA_DIR, true);

$cred = explode("\n", file_get_contents(__DIR__.'/config/credentials2.txt'));
$email = $cred[0];
$pass = $cred[1];
use Facebook\Facebook;
$fb = new Facebook(sha1($email.$pass), 1);

$app = new Autolike($fb);
$h = date('H');
if ($h > 20 || $h < 5) {
	$n = 1; // repeat
	$s = 15; // sleep
} else {
	$n = 3; // repeat
	$s = 2; // sleep
}
for ($i=0; $i < $n; $i++) { 
	$app->run();
	sleep($s);
}
