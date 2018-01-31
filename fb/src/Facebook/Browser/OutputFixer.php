<?php

namespace Facebook\Browser;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @package \Facebook\Browser
 */
class OutputFixer
{
	/**
	 * @var string
	 */
	private $out;

	/**
	 * @var bool
	 */
	private $asRouter;

	/**
	 * @var string
	 */
	private $baseUrl;

	/**
	 * Constructor.
	 *
	 * @param string $out
	 * @param bool $asRouter
	 */
	public function __construct($out, $asRouter, $baseUrl)
	{
		$this->out 		= $out;
		$this->asRouter = $asRouter;
		$this->baseUrl	= $baseUrl;
		$this->fixHref();
		$this->fixAction();
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return is_string($this->out) ?  $this->out : "";
	}

	private function fixHref()
	{
		if ($this->asRouter) {
			$len = strlen($this->baseUrl);
			if (preg_match_all("/href=\"(.*)\"/Usi", $this->out, $matches)) {
				$r1 = $r2 = [];
				foreach ($matches[0] as $key => $val) {
					if (substr($matches[1][$key], 0, $len) === $this->baseUrl) {
						$r1[] = $val;
						$r2[] = "href=\"".substr($matches[1][$key], $len)."\"";
					}
				}
				$this->out = str_replace($r1, $r2, $this->out);
			}
		} else {
			$len = strlen($this->baseUrl);
			if (preg_match_all("/href=\"(.*)\"/Usi", $this->out, $matches)) {
				$r1 = $r2 = [];
				foreach ($matches[0] as $key => $val) {
					$r1[] = $val;
					$r2[] = "href=\"?_url=".htmlspecialchars(urlencode(rawurlencode(d($matches[1][$key]))))."\"";
				}
				$this->out = str_replace($r1, $r2, $this->out);
			}
		}
	}

	private function fixAction()
	{
		if ($this->asRouter) {
			$len = strlen($this->baseUrl);
			if (preg_match_all('/<form.+action="(.*)"/Usi', $this->out, $matches)) {
				$r1 = $r2 = [];
				foreach ($matches[0] as $key => $val) {
					if (substr($matches[1][$key], 0, 4) === "http") {
						$r1[] = $val;
						$r2[] = preg_replace("/http.+\/\/.+\//Usi", "/", $val);
					}
				}
				$this->out = str_replace($r1, $r2, $this->out);
			}
		} else {
			if (preg_match_all('/<form.+action="(.*)"/Usi', $this->out, $matches)) {
				$r1 = $r2 = [];
				foreach ($matches[0] as $key => $val) {
					$r1[] = $val;
					$r2[] = str_replace($matches[1][$key], "?_url=".htmlspecialchars(urlencode(rawurlencode(d($matches[1][$key])))), $val);
				}
				$this->out = str_replace($r1, $r2, $this->out);
			}
		}
	}
}