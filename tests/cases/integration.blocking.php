<?php

require __DIR__ . '/../../vendor/autoload.php';

use Nextras\Kyu\Kyu;
use Nextras\Kyu\RedisBackend;
use Tester\Assert;
use Tests\Nextras\Time;

define('KEY', __FILE__);
$pid = pcntl_fork();

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$backend = new RedisBackend($redis);

$kyu = new Kyu(KEY, $backend);

Time::start();
if ($pid !== 0) {
	/** @var WordMessage $msg */
	$msg = $kyu->getOneOrNone();
	Assert::null($msg, 'getOneOrNone should not block until messages are send');

	$msg = $kyu->waitForOne();
	Assert::notSame('second', $msg->getWord(), 'queue is FIFO, should be LIFO');
	Assert::same('first', $msg->getWord());
	$kyu->removeSuccessful($msg);

	$msg = $kyu->waitForOne();
	Assert::same('second', $msg->getWord());
	$kyu->removeSuccessful($msg);

} else {
	Time::blockUntil(50 * Time::ms);
	$kyu->enqueue(new WordMessage('first'));
	$kyu->enqueue(new WordMessage('second'));
}

$redis->close();
