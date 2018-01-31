<?php

namespace Facebook\Support;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @package \Facebook\Support
 */
trait Login
{
	/**
	 * Login
	 */
	public function login()
	{
		$ch = curl_init("https://m.facebook.com/");
		curl_setopt_array($ch, $this->genOpt());
		$out = curl_exec($ch);
		$info = curl_getinfo($ch);
		if ($ern = curl_errno($ch)) {
			throw new \Exception("Curl Error ({$ern}): ".curl_error($ch), 1);
		}
		curl_close($ch);
		$this->currentUrl = isset($info['url']) ? $info['url'] : "https://m.facebook.com/";
		$out = file_get_contents("../login.tmp");
		if (preg_match("~<form(.*)action=\"(.*)\"~Usi", $out, $action)) {
			$action = d($action[2]);
			$out = explode("<form", $out, 2);
			$out = explode("</form>", $out[1], 2);
			if (preg_match_all("~<input(.*)type=\"hidden\"(.*)>~Usi", $out[0], $matches)) {
				$post = [];
				array_walk($matches[2], function ($m) use (&$post) {
					$a = explode("name=\"", $m, 2);
					if (isset($a[1])) {
						$a = explode("\"", $a[1], 2);
						$b = explode("value=\"", $m, 2);
						if (isset($b[1])) {
							$b = explode("\"", $b[1], 2);
							$post[d($a[0])] = d($b[0]);
						} else {
							$post[d($a[0])] = "";
						}
					}
				});
				$post['email'] = $this->email;
				$post['pass']  = $this->pass;
				$post['login'] = "Login";
				$ch = curl_init($action);
				curl_setopt_array($ch, $this->genOpt(
					[
						CURLOPT_POST => true,
						CURLOPT_POSTFIELDS => http_build_query($post)
					]
				));
				$out = curl_exec($ch);
				print $out;
				return true;
			}
		} else {
			throw new \Exception("Error Processing Request", 1);
		}
	}
}