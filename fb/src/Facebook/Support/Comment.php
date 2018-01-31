<?php

namespace Facebook\Support;

use Facebook\Support\Comment\Container;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @package \Facebook\Support
 */
trait Comment
{
	/**
	 * @param string|bigint $postId
	 */
	public function getPostComments($postId)
	{
		$ch = curl_init("https://m.facebook.com/".$postId);
		curl_setopt_array($ch, $this->genOpt([], true));
		$out = curl_exec($ch);
		$info = curl_getinfo($ch);
		if ($ern = curl_errno($ch)) {
			throw new \Exception("Curl Error ({$ern}): ".curl_error($ch), 1);
		}
		
		// $out = file_get_contents("../comment.tmp"); // offline debug

		preg_match_all('/(<div class="\w{2,3}" id="(\d{0,30})">(.*)<h3>(.*)href="\/(.*)\"(.*)<\/h3>(.*)<div class="(\w{2,4})">(.*)<\/div>)/sUi', $out, $matches);
		array_walk($matches[9], function (&$q, $i) use ($matches) {
			$q = new Container(
				[
					"actor" => (substr($matches[5][$i], 0, 12) === "profile.php?" ? explode("&", $matches[5][$i], 2)[0] : explode("?", $matches[5][$i], 2)[0]),
					"text" => trim(strip_tags(str_replace(["\t", "<br />"], ["", "\n"], d($q)))),
					"replies" => []
				]
			);
		});
		$comments = array_combine($matches[2], $matches[9]);
		if (preg_match_all('/<div class="(\w{2,3}\s\w{2,3}\s\w{2,3}\s\w{2,3})">(.*)<a(.*)href="(\/comment\/replies\/\?ctoken=(.*)_(.*)\&(.*))"(.*)<\/div>/Usi', $out, $matches)) {
			array_walk($matches[4], function (&$m, $i) use (&$comments, $matches) {
				if (isset($comments[$matches[6][$i]])) {
					$comments[$matches[6][$i]]['replies'][] = $this->getReplies(d($m));
				}
			});
		}
		return $comments;
	}

	/**
	 * @param string $url
	 */
	private function getReplies($url)
	{
		$ch = curl_init("https://m.facebook.com".$url);
		curl_setopt_array($ch, $this->genOpt());
		$out = curl_exec($ch);
		
		// $out = file_get_contents("../repl.tmp"); // offline debug

		preg_match_all('/(<div class="\w{2,3}" id="(\d{0,30})">(.*)<h3>(.*)href="\/(.*)\"(.*)<\/h3>(.*)<div class="(\w{2,4})">(.*)<\/div>)/sUi', $out, $matches);
		array_walk($matches[9], function (&$q, $i) use ($matches) {
			$q = new Container(
				[
					"actor" => (substr($matches[5][$i], 0, 12) === "profile.php?" ? explode("&", $matches[5][$i], 2)[0] : explode("?", $matches[5][$i], 2)[0]),
					"text" => trim(strip_tags(str_replace(["\t", "<br />"], ["", "\n"], d($q))))
				]
			);
		});
		return array_combine($matches[2], $matches[9]);
	}
}