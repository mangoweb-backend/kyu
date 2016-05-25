<?php

namespace Nextras\Kyu;

use DateInterval;


abstract class Message implements IMessage
{

	/**
	 * @var Counter
	 */
	protected $processingAttemptsCounter;


	public function __construct()
	{
		$this->processingAttemptsCounter = new Counter(3);
	}


	public function getProcessingDurationLimit() : DateInterval
	{
		return new DateInterval('PT5M');
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

}
