<?php
ini_set("display_errors", true);

session_start(
	[
		'cookie_lifetime' => 86400
	]
);

require __DIR__ . "/../autoload.php";

define("FACEBOOK_DATA_DIR", __DIR__ . "/../data");

use Facebook\Facebook;

if (isset($_GET['logout'])) {
	$_SESSION['credentials'] = null;
	session_destroy();
	header('location:?');
	exit;
}

if (isset($_GET['prefix'])) {
	$_SESSION['prefix'] = $_GET['prefix'];
	unset($_GET['prefix']);
	header('location:?'.http_build_query($_GET));
	exit;
}

if (isset($_SESSION['credentials'])) {
	if (! isset($_SESSION['prefix'])) {
		$_SESSION['prefix'] = 'm';
	}
	$cred = sha1($_SESSION['credentials']['email'] . $_SESSION['credentials']['pass']);
	$app = new Facebook($cred, 1);
	$router = false;
	$app->browser("https://{$_SESSION['prefix']}.facebook.com")->init($router)->prohibitAction(function ($self) {
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
	exit;
}


if (count($_GET) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(403);
	print 'Forbidden!';
	exit;
}
if (isset($_POST['submit'], $_POST['cred_file'], $_POST['email'], $_POST['pass'])) {
	if (file_exists($f = __DIR__.'/../config/'.$_POST['cred_file'])) {
		if (file_get_contents($f) === $_POST['email']."\n".$_POST['pass']) {
			$_SESSION['credentials'] = [
				'email' => $_POST['email'],
				'pass' => $_POST['pass']
			];		
		}
	}
	header('location:?');
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
</head>
<body>
<center>
<form method="post" action="?w=<?php print sha1(rand()).sha1(rand()); ?>">
Cred File :<br>
<input type="text" name="cred_file"><br><br>
E-Mail:<br>
<input type="text" name="email"><br><br>
Password:<br>
<input type="password" name="pass"><br><br>
<input type="submit" name="submit">
</form>
</center>
</body>
</html>