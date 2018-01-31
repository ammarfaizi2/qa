<?php

namespace Facebook\Support;

use Facebook\Browser\Browser as BrowserFoundation;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @package \Facebook\Support
 */
trait Browser
{
	public function browser($baseUrl = "https://m.facebook.com")
	{
		return new BrowserFoundation($this, $baseUrl);
	}
}