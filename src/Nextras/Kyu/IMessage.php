<?php

namespace Nextras\Kyu;


interface IMessage
{

	public function serialize() : string;

	public function unserialize(string $raw);


	/**
	 * Unique ID for this message in a set of all possible messages.
	 * TODO explain better
	 */
	public function getUniqueId() : string;

	/**
	 * Returns how many times should we try to process this message
	 * until it is permanently failed.
	 * Each retry must decrement this counter.
	 * New messages must have this counter set to at least 1, otherwise
	 * they would be never be processed.
	 */
	public function getProcessingAttemptsCounter() : Counter;


	/**
	 * Returns maximum duration the message can spend in “processing” state
	 * until it is retried. Does not include the time waiting in the queue.
	 * @return int seconds
	 */
	public function getProcessingDurationLimit() : int;

}
