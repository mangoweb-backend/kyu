<?php

namespace Nextras\Kyu;


class Message implements IMessage
{

	/**
	 * @var Counter
	 */
	protected $retryCounter;


	public function __construct()
	{
		$this->retryCounter = new Counter(3);
	}


	/**
	 * Returns how many times should this message be inserted back
	 * into processing queue after processing failure.
	 * Each retry must decrement this counter.
	 */
	public function getProcessingAttemptsCounter() : Counter
	{
		return $this->retryCounter;
	}

}
