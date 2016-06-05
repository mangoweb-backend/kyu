<?php

namespace Tests\Mangoweb;

use Mangoweb\Kyu\Kyu;
use Mangoweb\Kyu\RedisBackend;
use Redis;
use ReflectionClass;
use Tester;


abstract class TestCase extends Tester\TestCase
{

	/** @var Redis */
	private $redis;

	/** @var Kyu */
	private $kyu;


	/**
	 * @return Redis
	 */
	public function getRedis()
	{
		if ($this->redis === NULL) {
			$this->redis = new Redis();
			$this->redis->connect('127.0.0.1', 6379); // TODO configure
		}
		return $this->redis;
	}


	/**
	 * @return Kyu
	 */
	public function getKyu()
	{
		if ($this->kyu === NULL) {
			$backend = new RedisBackend($this->getRedis());
			$this->kyu = new Kyu($this->getChannel(), $backend);
		}
		return $this->kyu;
	}


	protected function setUp()
	{
		$this->flushChannel();
	}


	/**
	 * @return string
	 */
	public function getChannel()
	{
		$ref = new ReflectionClass($this);
		return $ref->getFileName();
	}


	/**
	 * Remove all keys from current redis channel
	 */
	public function flushChannel()
	{
		$keys = $this->getRedis()->keys($this->getChannel() . '.*');
		if ($keys) {
			$this->getRedis()->delete(...$keys);
		}
	}


	protected function tearDown()
	{
		$this->flushChannel();
		$this->redis->close();
	}

}
