<?php

namespace Facebook\Support;

trait ArrayUtils
{	
	/**
	 * @var array
	 */
	private $arrayContainer = [];

	/**
     * @param mixed $offset
     * @return mixed
     */
    public function &offsetGet($offset)
    {
        return $this->arrayContainer[$offset];
    }

	/**
	 * @param int|string $offset
	 * @param mixed	$value
	 */
	public function offsetSet($offset, $value)
	{
		if ($offset === null) {
			$this->arrayContainer[] = $value;
		} else {
			$this->arrayContainer[$offset] = $value;
		}
	}

	/**
	 * @param int|string $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($key, $this->arrayContainer);
	}

	/**
	 * @param int|string $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->arrayContainer[$offset]);
	}

	/**
	 * @var array
	 */
	public function jsonSerialize()
	{
		return $this->arrayContainer;
	}

	/**
	 * @param mixed $offset
	 * @return null
	 */
	public function __get($offset)
	{
		return null;
	}
}
