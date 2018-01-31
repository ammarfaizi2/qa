<?php

namespace Facebook\Browser;

use Facebook\Facebook;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @package \Facebook\Browser
 */
class HeaderUrlGenerator
{	

	/**
	 * @var bool
	 */
	private $asRouter;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $baseUrl;

	/**
	 * Constructor.
	 *
	 * @param bool $asRouter
	 */
	public function __construct($asRouter, $baseUrl, $url)
	{
		$this->asRouter = $asRouter;
		$this->baseUrl  = $baseUrl;
		$this->url		= $url;
	}

	/**
	 * Generate url.
	 */
	public function generate()
	{
		if ($this->asRouter) {
			if (substr($this->url, 0, 4) === "http") {
				
				$this->url = preg_replace("/^http.+\/\/.+\//Usi", "/", $this->url);
			}
		} else {
			if (substr($this->url, 0, 4) === "http") {
				$this->url = "?_url=".urlencode(rawurlencode(preg_replace("/^http.+\/\/.+\//Usi", "/", $this->url)));
			} else {
				$this->url = "?_url=".urlencode(rawurlencode($this->url));
			}
		}
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}
}
