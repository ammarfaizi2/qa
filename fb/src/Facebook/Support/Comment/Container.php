<?php

namespace Facebook\Support\Comment;

use ArrayAccess;
use JsonSerializable;
use Facebook\Support\ArrayUtils;

class Container implements ArrayAccess, JsonSerializable
{
	use ArrayUtils;

	/**
	 * Constructor.
	 *
	 * @param array $array
	 */
	public function __construct($array)
	{
		$this->arrayContainer = $array;		
	}
}
