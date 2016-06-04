<?php

namespace Nextras\Kyu;


abstract class Message implements IMessage
{

	/**
	 * @var Counter
	 */
	protected $processingAttemptsCounter;

	/**
	 * @var string
	 */
	protected $id;


	public function __construct()
	{
		$this->processingAttemptsCounter = new Counter(3);
		$this->id = md5(random_bytes(16));
	}


	/**
	 * Returns maximum duration the message can spend in “processing” state
	 * until it is retried. Does not include the time waiting in the queue.
	 * @return int seconds
	 */
	public function getProcessingDurationLimit() : int
	{
		return 10;
	}


	/**
	 * Returns how many times should this message be inserted back
	 * into processing queue after processing failure.
	 * Each retry must decrement this counter.
	 */
	public function getProcessingAttemptsCounter() : Counter
	{
		return $this->processingAttemptsCounter;
	}


	public function getUniqueId() : string
	{
		return $this->id;
	}

}
