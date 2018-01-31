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
$app->browser()->init(true)->run();