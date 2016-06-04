<?php

require __DIR__ . '/../../vendor/autoload.php';

use Nextras\Kyu\IBackend;
use Nextras\Kyu\Kyu;

// Timeline:
//   0: blocking read
//   1:                put
//   1: unblocks
//   1:                put
//   2: blocking read
//   2: unblocks
//   2: non-blocking read
//   3:                put
//   3: non-blocking read

$sem = new Semaphore(3);
$sem->wait();
$sem->wait();
$sem->wait();
var_dump('HERE WE ARE');

$backend = new MemoryBackend();

$writing = new WritingThread($backend);
$reading = new ReadingThread($backend);

//$writing->start();
$reading->start();

//$writing->join();
//$reading->join();

class QueueThread extends Thread
{

	/** @var Kyu */
	protected $k;

	public function __construct(IBackend $backend)
	{
		$this->k = new Kyu('test-channel', $backend);
	}

}

class WritingThread extends QueueThread
{

	public function run()
	{
		// T=0
		usleep(1 * 1e6);

		// T=1
		$this->k->enqueue(new WordMessage('one'));
		usleep(1 * 1e6);

		// T=2
		$this->k->enqueue(new WordMessage('two'));
		usleep(1 * 1e6);

		// T=3
		$this->k->enqueue(new WordMessage('three'));
	}

}
class ReadingThread extends QueueThread
{

	public function run()
	{
		// T=0
		$msg = $this->k->waitForOne();
	}

}
