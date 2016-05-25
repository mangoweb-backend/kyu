<?php

namespace Nextras\Kyu;


class CounterValueOutOfRangeException extends \LogicException implements CounterException
{

	/**
	 * @param int             $invalidValue
	 * @param \Exception|NULL $previous
	 */
	public function __construct(int $invalidValue, \Exception $previous = NULL)
	{
		parent::__construct("Counter value '$invalidValue' is out of range, expected int in <0, PHP_INT_MAX> inclusive range.", NULL, $previous);
	}

}
