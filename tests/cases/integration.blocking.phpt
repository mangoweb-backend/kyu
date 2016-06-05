<?php

namespace Tests\Mangoweb\Integration;

require __DIR__ . '/../../vendor/autoload.php';

use Mangoweb\Kyu\Message;
use Tester\Assert;
use Tests\Mangoweb\MultiProcessTestCase;
use Tests\Mangoweb\TestCase;
use Tests\Mangoweb\Time;

class IntegrationBlocking extends MultiProcessTestCase
{

	public function mainProcess(int $pid)
	{
		$kyu = $this->getKyu();

		// let sideProcess
		// getOneOrNone() return NULL and
		// waitForOne() block for a while
		usleep(100 * 1e3);

		$kyu->enqueue(new Message('first', 1));
		$kyu->enqueue(new Message('second', 1));
	}


	public function sideProcess()
	{
		$kyu = $this->getKyu();

		/** @var Message $msg */
		$msg = $kyu->getOneOrNone();
		Assert::null($msg, 'getOneOrNone should not block until messages are send');

		$msg = $kyu->waitForOne();
		Assert::notSame('second', $msg->getPayload(), 'queue is FIFO, should be LIFO');
		Assert::same('first', $msg->getPayload());
		$kyu->removeSuccessful($msg);

		$msg = $kyu->waitForOne();
		Assert::same('second', $msg->getPayload());
		$kyu->removeSuccessful($msg);

		// simulate processing duration timeout
		usleep(($msg->getProcessingDurationLimit() + 0.1) * 1e6);
		$failed = $kyu->recycleOneFailed();
		Assert::null($failed, 'message should be removed from processing queue after calling removeSuccessful()');
		$msg = $kyu->getOneOrNone();
		Assert::null($msg, 'message should not be requeued after removeSucessful()');
	}

}

(new IntegrationBlocking())->run();
