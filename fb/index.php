<?php
die;
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/test.cred";

header("Content-type:text/plain");
$fb = new Facebook\Facebook($email, $password);
$lg = $fb->login();
var_dump($lg);
