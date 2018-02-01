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
//$fb->comment('test', '2033591683337149');
//$fb->replyComment('test', '2033617373334580');
$app = new ShellBinding($fb, 'ammarfaizi2');
$app->run();