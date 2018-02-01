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
	public function comment($text, $postId)
	{
		$a = $this->goTo('https://m.facebook.com/'.$postId);
		// $a['info']['http_code'] = 200;
		// $a['out'] = file_get_contents('c.txt');
		if ($a['info']['http_code'] === 200) {
			// file_put_contents('c.txt', $a['out']);
			if (preg_match('/<form method="post" action=".*(a\/comment.php\?.*)".*>(.*)<\/form>/Usi', $a['out'], $matches)) {
				$action = 'https://m.facebook.com/'.html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
				preg_match_all('/<input.*type="hidden".*name="(.*)".*value="(.*)".*>/Usi', $matches[2], $matches);
				$post = array_combine($matches[1], $matches[2]);
				$post['comment_text'] = $text;
				var_dump($a['info']);
				$q = $this->goTo($action, [
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => http_build_query($post)
				])['info']['http_code'] === 200;
			}
		}
		return false;
	}

	public function replyComment($text, $commentId)
	{
		$a = $this->goTo('https://m.facebook.com/'.$commentId);
		//$a['info']['http_code'] = 200;
		//$a['out'] = file_get_contents('d.txt');
		if ($a['info']['http_code'] === 200) {
			// file_put_contents('d.txt', $a['out']);
			if (preg_match_all('/<a href=".*(comment\/replies\/.*)"/Usi', $a['out'], $matches)) {
				$matches = 'https://m.facebook.com/'.html_entity_decode($matches[1][count($matches[1]) - 1], ENT_QUOTES, 'UTF-8');
				$a = $this->goTo($matches);
				//$a['info']['http_code'] = 200;
				//$a['out'] = file_get_contents('e.txt');
				if ($a['info']['http_code'] === 200) {
					// file_put_contents('e.txt', $a['out']);
					if (preg_match('/<form method="post" action=".*(a\/comment.php\?.*)".*>(.*)<\/form>/Usi', $a['out'], $matches)) {
						$action = 'https://m.facebook.com/'.html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
						preg_match_all('/<input.*type="hidden".*name="(.*)".*value="(.*)".*>/Usi', $matches[2], $matches);
						$post = array_combine($matches[1], $matches[2]);
						$post['comment_text'] = $text;
						return $this->goTo($action, [
							CURLOPT_POST => true,
							CURLOPT_POSTFIELDS => http_build_query($post)
						])['info']['http_code'] === 200;
					}
				}
			}
		}
		return false;
	}

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