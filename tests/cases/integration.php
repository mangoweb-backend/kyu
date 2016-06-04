<?php

require __DIR__ . '/../../vendor/autoload.php';

use Nextras\Kyu\IBackend;
use Nextras\Kyu\Kyu;
use Tester\Assert;


define('KEY', __FILE__);

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$backend = new \Nextras\Kyu\RedisBackend($redis);

$kyu = new Kyu(KEY, $backend);


$kyu->enqueue(new WordMessage('first'));
$kyu->enqueue(new WordMessage('second'));

/** @var WordMessage $msg */
$msg = $kyu->waitForOne();
Assert::same('first', $msg->getWord());
$msg = $kyu->waitForOne();
Assert::same('second', $msg->getWord());

$redis->close();
