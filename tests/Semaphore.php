<?php


class Semaphore
{

	/** @var string path to file handled by ftok */
	private $file;

	/** @var resource */
	private $sem;


	/**
	 * @param mixed $key            arbitrary string-convertible key
	 * @param int   $initialValue   non-negative
	 */
	public function __construct($key, int $initialValue)
	{
		assert($initialValue >= 0, 'Initial value must be non-negative integer');

		$this->file = sys_get_temp_dir() . '/mikulas-' . md5($key) . '.sem';
		touch($this->file);

		$key = ftok($this->file, 'm');
		$this->sem = sem_get($key, $initialValue, 0666, TRUE);
	}


	public function __destruct()
	{
		sem_remove($this->sem);
		unlink($this->file);
	}


	public function wait()
	{
		return sem_acquire($this->sem);
	}


	public function post()
	{
		return sem_release($this->sem);
	}

}
