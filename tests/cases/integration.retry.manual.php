<?php

require __DIR__ . '/../../vendor/autoload.php';

use Nextras\Kyu\Kyu;
use Nextras\Kyu\Message;
use Nextras\Kyu\RedisBackend;
use Tester\Assert;

define('KEY', __FILE__);

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$backend = new RedisBackend($redis);

$kyu = new Kyu(KEY, $backend);

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
//}, \Nextras\Kyu\MessagePermanentlyFailedException::class);

Assert::null($kyu->getOneOrNone(), 'message with depleted attemps counter should not be inserted to queue');
