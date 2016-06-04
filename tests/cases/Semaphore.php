<?php


class Semaphore extends Threaded
{

	const TIMEOUT = -6823;

	/** @var int */
	private $count;

	private $countMutex;
	private $waitingMutex;


	public function __construct(int $initialCount)
	{
		$this->countMutex = new Mutex();
		$this->waitingMutex = new Mutex();

		$this->count = $initialCount;
	}


	public function wait(int $timeout = self::TIMEOUT)
	{
		assert($timeout === self::TIMEOUT, 'Timeout parameter not supported');

		$this->countMutex->lock();
		if ($this->count > 0) {
			$this->count -= 1;
			$this->countMutex->unlock();
			return;
		}
		$this->waitingMutex->lock();
		$this->countMutex->unlock();

		// this will block until post is called
		$this->waitingMutex->lock();
		$this->countMutex->lock();
		assert($this->count > 0);
		$this->count -= 1;
		$this->countMutex->unlock();
	}


	public function post()
	{
		$this->countMutex->lock();
		$this->count += 1;
		$this->countMutex->unlock();

		$this->waitingMutex->unlock();
	}

}
