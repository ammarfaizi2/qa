<?php

namespace Facebook\Browser;

use Facebook\Facebook;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @package \Facebook\Browser
 */
class ActionUrlGenerator
{
	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var bool
	 */
	private $asRouter;

	/**
	 * @var string
	 */
	private $pathInfo;

	/**
	 * @var string|null
	 */
	private $customPathInfo;

	/**
	 * Constructor.
	 *
	 * @param bool $asRouter
	 */
	public function __construct($asRouter, $baseUrl, $customPathInfo = null)
	{
		$this->asRouter = $asRouter;
		$this->baseUrl  = $baseUrl;
		$this->customPathInfo = $customPathInfo;
	}

	/**
	 * Generate url.
	 */
	public function generate()
	{
		if ($this->asRouter) {
			$this->pathInfo = is_null($this->customPathInfo) ? (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : "") :$this->customPathInfo;
			$this->routerGenerate();
		} else {
			$this->pathInfo = is_null($this->customPathInfo) ? (isset($_GET['_url']) ? rawurldecode($_GET['_url']) : "") : $this->customPathInfo;
			$this->manualGenerate();
		}
	}

	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Generate rotuer url.
	 */
	private function routerGenerate()
	{
		$this->url = "{$this->baseUrl}/".(trim($this->pathInfo, "/")).(count($_GET) ? "?" . http_build_query($_GET) : "");
	}

	/**
	 * Generate manual url.
	 */
	private function manualGenerate()
	{
		$this->url = substr($this->pathInfo, 0, 4) === "http" ? $this->pathInfo : $this->baseUrl."/".$this->pathInfo;
	}
}
