<?php

namespace Facebook\Contracts;

use Facebook\Facebook;

interface Browser
{
	public function __construct(Facebook $fb);
}