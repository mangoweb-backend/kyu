<?php

/**
 * Process and thread exclusive mutex
 */
class Mutex extends Threaded
{

	/** @var resource */
	private $handle;

	/** @var bool */
	private $hasLock = FALSE;


	public function __construct()
	{
		$this->handle = tmpfile();
	}


	public function lock()
	{
		while ($this->hasLock) {
			$this->wait();
		}

		$this->synchronized(function() {
			flock($this->handle, LOCK_EX);
			$this->hasLock = TRUE;
		});
	}


	public function unlock()
	{
		$this->synchronized(function() {
			$this->hasLock = FALSE;
			flock($this->handle, LOCK_UN);
			$this->notify();
		});
	}

}
