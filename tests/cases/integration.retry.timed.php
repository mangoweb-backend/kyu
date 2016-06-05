<?php

require __DIR__ . '/../../vendor/autoload.php';

use Mangoweb\Kyu\Kyu;
use Mangoweb\Kyu\Message;
use Mangoweb\Kyu\RedisBackend;
use Tester\Assert;

define('KEY', __FILE__);

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$backend = new RedisBackend($redis);

$kyu = new Kyu(KEY, $backend);

$original = new Message('first', 1, 2);
$kyu->enqueue($original);


$received1 = $kyu->getOneOrNone();
Assert::notSame(NULL, $received1);
Assert::same(1, $received1->getProcessingAttemptsCounter(), 'processing attempt should decrement processing attempts counter');
Assert::same($original->getUniqueId(), $received1->getUniqueId());

// simulate processing duration timeout
usleep(($received1->getProcessingDurationLimit() + 0.1) * 1e6);

$failed1 = $kyu->recycleOneFailed();
Assert::same($original->getUniqueId(), $failed1->getUniqueId());
Assert::same(1, $failed1->getProcessingAttemptsCounter(), 'recycling should decrement processing attempts counter');
Assert::false($failed1->isFailedPermanently());



$received2 = $kyu->getOneOrNone();
Assert::notSame(NULL, $received2, 'message with remaining processing attempts should be reinserted to queue');
Assert::same($original->getUniqueId(), $received2->getUniqueId());
Assert::same(0, $received2->getProcessingAttemptsCounter());

// simulate processing duration timeout
usleep(($received1->getProcessingDurationLimit() + 0.1) * 1e6);

$failed2 = $kyu->recycleOneFailed();
Assert::same($original->getUniqueId(), $failed2->getUniqueId());
Assert::same(0, $failed2->getProcessingAttemptsCounter());
Assert::true($failed2->isFailedPermanently());

Assert::null($kyu->getOneOrNone(), 'message without remaining processing attempts should not inserted back to queue');
