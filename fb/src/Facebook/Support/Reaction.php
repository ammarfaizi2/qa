<?php

namespace Facebook\Support;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @package \Facebook\Support
 */
trait Reaction
{
	public function reaction($postId, $reaction = 'LIKE')
	{
		$a = $this->goTo('https://mobile.facebook.com/'.$postId);
		//$a['info']['http_code'] = 200;
		//$a['out'] = file_get_contents('a.tmp');
		if ($a['info']['http_code'] === 200) {
			preg_match('/(reactions\/picker\/.+")/Usi', $a['out'], $matches);
			if (isset($matches[0])) {
				$matches[0] = html_entity_decode($matches[0], ENT_QUOTES, 'UTF-8');
				$a = $this->goTo('https://mobile.facebook.com/'.$matches[0]);
				// $a['out'] = file_get_contents('b.tmp');
				if (preg_match_all('/(ufi\/reaction.+)".+<span>(.*)<\/span>/Usi', $a['out'], $matches)) {
					if (isset($matches[1], $matches[2])) {
						array_walk($matches[1], function (&$m) {
							$m = 'https://mobile.facebook.com/'.html_entity_decode($m, ENT_QUOTES, 'UTF-8');
						});
						array_walk($matches[2], function (&$m) {
							$m = strtoupper($m);
						});
						$key = array_combine($matches[2], $matches[1]);
						if (isset($key[$reaction])) {
							var_dump($key[$reaction]);
							$a = $this->goTo($key[$reaction], [CURLOPT_REFERER => $a['info']['url']]);
							return $a['info']['http_code'] === 200;
						}
					}
				}
			}
		}
		return false;
	}
}
