<?php

namespace Facebook;

defined("FACEBOOK_DATA_DIR") or die("FACEBOOK_DATA_DIR is not defined!");

require __DIR__ . "/helpers.php";

use Facebook\Support\Login;
use Facebook\Support\Browser;
use Facebook\Support\Profile;
use Facebook\Support\Comment;
use Facebook\Support\Reaction;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @version 0.0.1
 */
class Facebook
{	
	use Login, Comment, Profile, Browser, Reaction;

	const UA = "Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:56.0) Gecko/20100101 Firefox/56.0";

	/**
	 * @var string
	 */
	private $email;

	/**
	 * @var string
	 */
	private $pass;

	/**
	 * @var string
	 */
	private $hash;

	/**
	 * @var string
	 */
	public $currentUrl = "";

	/**
	 * Constructor.
	 *
	 * @param string $email
	 * @param string $pass
	 */
	public function __construct($email, $pass, $cookiename = null)
	{
		$this->email = $email;
		$this->pass  = $pass;
		$this->hash	 = sha1($email);
		is_dir(FACEBOOK_DATA_DIR) or mkdir(FACEBOOK_DATA_DIR);
		if (! is_dir(FACEBOOK_DATA_DIR)) 
			throw new \Exception("Cannot create directory ".FACEBOOK_DATA_DIR, 1);
		is_dir(FACEBOOK_DATA_DIR."/cookies") or mkdir(FACEBOOK_DATA_DIR."/cookies");
		if (! is_dir(FACEBOOK_DATA_DIR."/cookies")) 
			throw new \Exception("Cannot create directory ".FACEBOOK_DATA_DIR."/cookies", 1);
		$this->cookieFile = realpath(FACEBOOK_DATA_DIR."/cookies")."/".(isset($cookiename) ? $cookiename : $this->hash).".txt";
		file_exists($this->cookieFile) or file_put_contents($this->cookieFile, "");
	}


	public function goTo($url, $opt = [])
	{
		$ch = curl_init($url);
		curl_setopt_array($ch, $this->genOpt($opt, true));
		$out = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		return ['out'=>$out,'info'=>$info];
	}

	/**
	 * Generate curl opt
	 *
	 * @param array $opt
	 * @param bool	$noref
	 */
	public function genOpt($opt = [], $noref = false)
	{
		$defaultOpt = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_CONNECTTIMEOUT => 300,
			CURLOPT_COOKIEFILE => $this->cookieFile,
			CURLOPT_COOKIEJAR  => $this->cookieFile,
			CURLOPT_USERAGENT  => self::UA,
			CURLOPT_TIMEOUT    => 300,
			CURLOPT_FOLLOWLOCATION => true,
		];
		if (! $noref) {
			$defaultOpt[CURLOPT_REFERER] = $this->currentUrl;
		}
		foreach ($opt as $key => $value) {
			$defaultOpt[$key] = $value;
		}
		return $defaultOpt;
	}
}
