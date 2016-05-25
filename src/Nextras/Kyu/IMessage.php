<?php

namespace Nextras\Kyu;


interface IMessage extends \Serializable
{

	/**
	 * Returns how many times should we try to process this message
	 * until it is permanently failed.
	 * Each retry must decrement this counter.
	 * New messages must have this counter set to at least 1, otherwise
	 * they would be never be processed.
	 */
	public function getProcessingAttemptsCounter() : Counter;


	public function getRetryTimeout() : \DateInterval;

}
