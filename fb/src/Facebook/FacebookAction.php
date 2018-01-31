<?php

namespace Facebook;

use Facebook\Facebook;

abstract class FacebookAction
{
	/**
	 * @var \Facebook\Facebook $fb
	 */
	protected $fb;

	/**
	 * @param \Facebook\Facebook $fb
	 */
	public function __construct(Facebook $fb)
	{
		$this->fb = $fb;
	}
}
