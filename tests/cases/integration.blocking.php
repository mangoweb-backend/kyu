<?php

require __DIR__ . '/../../vendor/autoload.php';

use Mangoweb\Kyu\Kyu;
use Mangoweb\Kyu\Message;
use Mangoweb\Kyu\RedisBackend;
use Tester\Assert;
use Tests\Mangoweb\Time;

define('KEY', __FILE__);
$pid = pcntl_fork();

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$backend = new RedisBackend($redis);

$kyu = new Kyu(KEY, $backend);

Time::start();
if ($pid !== 0) {
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

} else {
	Time::blockUntil(50 * Time::ms);
	$kyu->enqueue(new Message('first', 1));
	$kyu->enqueue(new Message('second', 1));
}

$redis->close();
