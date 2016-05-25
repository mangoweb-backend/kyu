<?php

namespace Nextras\Kyu;


interface IMessage
{

	/**
	 * Returns how many times should this message be inserted back
	 * into processing queue after processing failure.
	 * Each retry must decrement this counter by calling decreaseRetriesRemaining()
	 */
	public function getRetriesRemaining() : int;


	/**
	 * Decrement internal retry counter to a minimum of zero.
	 */
	public function decreaseRetriesRemaining() : void;

}
