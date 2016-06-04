<?php

use Nextras\Kyu\IBackend;
use Nextras\Kyu\IMessage;


class MemoryBackend implements IBackend
{

	/** @var Threaded */
	public $queue;

	/** @var Threaded */
	public $processing;

	/** @var Threaded */
	public $failed;

	/** @var Semaphore */
	private $semaphore;


	/**
	 * MemoryBackend constructor.
	 */
	public function __construct()
	{
		$this->semaphore = new Semaphore(random_int(PHP_INT_MIN, PHP_INT_MAX), 0);

		$this->queue = new Threaded();
		$this->processing = new Threaded();
		$this->failed = new Threaded();
	}


	public function enqueue(IMessage $message)
	{
		$this->queue[] = $message;
		$this->semaphore->post();
	}


	public function waitForOne() : IMessage
	{
		$this->semaphore->wait();
		$msg = $this->queue->shift();
		assert($msg !== NULL);
		return $msg;
	}


	/**
	 * @return NULL|IMessage
	 */
	public function getOneOrNone()
	{
		return $this->queue->shift();
	}

}
