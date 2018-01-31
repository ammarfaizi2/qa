<?php

namespace Facebook\Support;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @package \Facebook\Support
 */
trait Profile
{
	/**
	 * @param string|int $username
	 * @return array
	 */
	public function seeTimeline($username)
	{
		$ch = curl_init(
			"https://m.facebook.com/".(is_numeric($username) ? "profile.php?id={$username}&v=timeline" : "{$username}?v=timeline")
		);
		curl_setopt_array($ch, $this->genOpt([], true));
		$out = curl_exec($ch);
		$info = curl_getinfo($ch);
		if ($ern = curl_errno($ch)) {
			throw new Exception("Error Processing Request", 1);
		}
		curl_close($ch);
		
		// $out = file_get_contents("../profile.tmp"); // offline debug

		preg_match_all('/(<div role="article".+(\d{16,30})\&.+<\/abbr>)/siU', $out, $matches);
		$posts = [];
		foreach ($matches[1] as $key => $value) {
			$posts[$key]['id'] = $matches[2][$key];
			if (preg_match('/<p>(.+)<\/p>/siU', $value, $n)) {
				$posts[$key]['text'] = trim(strip_tags(d($n[1])));
			}
			if (preg_match('/(<a.+href=".+photo\.php.+<img.+src="(.*)")/siU', $value, $n)) {
				$posts[$key]['image'] = trim(strip_tags(d($n[2])));
			}
			if (preg_match('/<abbr>(.*)<\/abbr>/', $value, $n)) {
				$posts[$key]['date'] = trim(d($n[1]));
			}
		}
		return $posts;
	}
}