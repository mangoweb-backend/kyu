<?php

namespace Nextras\Kyu;

use Redis;


class RedisBackend implements IBackend
{

	const QUEUE = 'queue';
	const PROCESSING = 'processing';


	/** @var Redis */
	private $redis;


	public function __construct(Redis $redis)
	{
		$this->redis = $redis;
	}


	public function enqueue(string $raw)
	{
		$this->redis->lPush(self::QUEUE, $raw);
	}


	public function waitForOne(int $timeoutInSeconds) : string
	{
		return $this->redis->brpoplpush(self::QUEUE, self::PROCESSING, $timeoutInSeconds);
	}


	/**
	 * @return NULL|IMessage
	 */
	public function getOneOrNone()
	{
		return $this->redis->rpoplpush(self::QUEUE, self::PROCESSING);
	}

}
