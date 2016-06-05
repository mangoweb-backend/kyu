<?php

namespace Tests\Mangoweb\Integration;

require __DIR__ . '/../../vendor/autoload.php';

use Mangoweb\Kyu\Message;
use Tester\Assert;
use Tests\Mangoweb\TestCase;


class IntegrationRetryManual extends TestCase
{

	public function testManual()
	{
		$kyu = $this->getKyu();
		$kyu->enqueue(new Message('first'));

		/** @var Message $msg */
		$msg = $kyu->waitForOne();
		// first processing
		Assert::same(2, $msg->getProcessingAttemptsCounter());
		$kyu->enqueue($msg);

		$msg = $kyu->waitForOne();
		// second processing
		Assert::same(1, $msg->getProcessingAttemptsCounter());
		$kyu->enqueue($msg);

		$msg = $kyu->waitForOne();
		// third processing
		Assert::same(0, $msg->getProcessingAttemptsCounter());

		//Assert::exception(function() use ($kyu, $msg) {
		//	$kyu->enqueue($msg);
		//}, \Mangoweb\Kyu\MessagePermanentlyFailedException::class);

		Assert::null($kyu->getOneOrNone(), 'message with depleted attempts counter should not be inserted to queue');
	}

}

(new IntegrationRetryManual())->run();
