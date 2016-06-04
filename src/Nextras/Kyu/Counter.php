<?php

namespace Nextras\Kyu;


class Counter implements \Serializable
{

	/**
	 * @var int <0, PHP_INT_MAX> inclusive range
	 */
	private $value;


	/**
	 * @param int $initialValue <0, PHP_INT_MAX> inclusive range
	 * @throws CounterValueOutOfRangeException
	 */
	public function __construct(int $initialValue)
	{
		$this->setValue($initialValue);
	}


	/**
	 * @param int $value <0, PHP_INT_MAX> inclusive range
	 * @throws CounterValueOutOfRangeException
	 * @return void
	 */
	public function setValue(int $value)
	{
		if ($value < 0) {
			throw new CounterValueOutOfRangeException($value);
		}
		$this->value = $value;
	}


	/**
	 * Decreases counter value by one to a minimum of 0.
	 * If counter is already at 0, this function does nothing.
	 */
	public function decrement() : void
	{
		$this->value = max(0, $this->value - 1);
	}


	//
	// Serializable
	//

	/**
	 * String representation of object
	 * @link  http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		return (string) $this->value;
	}


	/**
	 * Constructs the object
	 * @link  http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized The string representation of the object.
	 * @return void
	 * @throws CounterValueOutOfRangeException
	 */
	public function unserialize($serialized)
	{
		$this->setValue($serialized);
	}


	public function getValue() : int
	{
		return $this->value;
	}

}
