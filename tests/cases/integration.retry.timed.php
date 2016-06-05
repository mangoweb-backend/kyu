<?php

require __DIR__ . '/../../vendor/autoload.php';

use Nextras\Kyu\Counter;
use Nextras\Kyu\Kyu;
use Nextras\Kyu\Message;
use Nextras\Kyu\RedisBackend;
use Tester\Assert;

define('KEY', __FILE__);

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$backend = new RedisBackend($redis);

$kyu = new Kyu(KEY, $backend);

$original = new Message('first', 1, new Counter(2));
$kyu->enqueue($original);

/** @var Message $received1 */
$received1 = $kyu->waitForOne();
Assert::notSame(NULL, $received1);
Assert::same($original->getUniqueId(), $received1->getUniqueId());

// simulate processing duration timeout
usleep(($received1->getProcessingDurationLimit() + 0.1) * 1e6);

$failed1 = $kyu->recycleOneFailed();
Assert::same($original->getUniqueId(), $failed1->getUniqueId());
Assert::same(1, $failed1->getProcessingAttemptsCounter()->getValue());
Assert::false($failed1->isFailedPermanently());

$received2 = $kyu->waitForOne();
Assert::notSame(NULL, $received1);
Assert::same($original->getUniqueId(), $received2->getUniqueId());

// simulate processing duration timeout
usleep(($received1->getProcessingDurationLimit() + 0.1) * 1e6);

$failed2 = $kyu->recycleOneFailed();
Assert::same($original->getUniqueId(), $failed1->getUniqueId());
Assert::same(0, $failed1->getProcessingAttemptsCounter()->getValue());
Assert::true($failed1->isFailedPermanently());

Assert::null($kyu->getOneOrNone(), 'message without remaining processing attemps should not inserted back to queue');
