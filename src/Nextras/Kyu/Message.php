<?php

namespace Nextras\Kyu;


class Message implements IMessage
{

	/**
	 * @var int <0; PHP_INT_MAX> inclusive
	 */
	protected $retriesRemaining;


	/**
	 * Returns how many times should this message be inserted back
	 * into processing queue after processing failure.
	 * Each retry must decrement this counter by calling decreaseRetriesRemaining()
	 */
	public function getRetriesRemaining() : int
	{
		return $this->retriesRemaining;
	}


	/**
	 * Decrement internal retry counter to a minimum of zero.
	 */
	public function decreaseRetriesRemaining() : void
	{
		$this->retriesRemaining = max(0, $this->retriesRemaining - 1);
	}

}
