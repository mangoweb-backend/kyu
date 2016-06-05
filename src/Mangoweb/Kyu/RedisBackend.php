<?php

namespace Mangoweb\Kyu;

use Redis;


/**
 * Redis keys:
 *   $channel.queue => List<$msgId>
 *   $channel.processing => List<$msgId>
 *   $channel.alive.$msgId => no value, TTL
 *   $channel.value.$msgId => serialized message
 */
class RedisBackend implements IBackend
{

	const VALUE_DOES_NOT_MATTER = '';

	/** @var Redis */
	private $redis;

	/** @var string[] [string $script => string $sha] */
	private $scriptHashCache;


	public function __construct(Redis $redis)
	{
		$this->redis = $redis;
	}


	public function enqueue(string $channel, Message $message)
	{
		$raw = $message->serializeToJson();
		$id = $message->getUniqueId();

		// TODO transaction
		$x=$this->redis->set($this->getValueKey($channel, $id), $raw);
		var_dump($x, $this->redis->getLastError());
		$y=$this->redis->lPush($this->getQueueListKey($channel), $id);
		var_dump($y, $this->redis->getLastError());
	}


	private function getQueueListKey(string $channel)
	{
		return "$channel.queue";
	}


	private function getProcessingListKey(string $channel)
	{
		return "$channel.processing";
	}


	private function getAliveKey(string $channel, $messageId)
	{
		return "$channel.alive.$messageId";
	}


	private function getValueKey(string $channel, $messageId)
	{
		return "$channel.value.$messageId";
	}


	public function waitForOne(string $channel, int $timeoutInSeconds) : string
	{
		$id = $this->redis->brpoplpush($this->getQueueListKey($channel), $this->getProcessingListKey($channel), $timeoutInSeconds);
		return $this->redis->get($this->getValueKey($channel, $id));
	}


	public function startTimeout(string $channel, string $messageId, int $ttl)
	{
		$this->redis->setex($this->getAliveKey($channel, $messageId), $ttl, self::VALUE_DOES_NOT_MATTER);
	}


	/**
	 * @param string $channel
	 * @return NULL|string
	 */
	public function getOneOrNone(string $channel)
	{
		$id = $this->redis->rpoplpush($this->getQueueListKey($channel), $this->getProcessingListKey($channel));
		if (!$id) {
			return NULL;
		}
		return $this->redis->get($this->getValueKey($channel, $id));
	}


	/**
	 * @param string $name
	 * @return string sha1 of script
	 */
	private function prepareScript($name)
	{
		$file = __DIR__ . "/scripts/$name.lua";

		$this->redis->clearLastError();
		$sha = $this->redis->script('load', file_get_contents($file));
		if ($sha === FALSE) {
			throw new \RedisException($this->redis->getLastError()); // TODO our exception
		}
		return $sha;
	}


	private function runScript($name, ...$args)
	{
		if (!isset($this->scriptHashCache[$name])) {
			$this->scriptHashCache[$name] = $this->prepareScript($name);
		}

		$this->redis->clearLastError();
		$response = $this->redis->evalSha($this->scriptHashCache[$name], $args, count($args));
		if ($response === FALSE) {
			throw new \RedisException($this->redis->getLastError()); // TODO our
		}
		return $response;
	}


	public function recycleOne(string $channel)
	{
		$raw = $this->runScript('recycleOne', $channel);
		switch ($raw) {
			case '':
				// this is weird empty response??!
				throw new \RedisException('WEIRD STUFF');
			case -1: // processing list is empty
			case -2: // oldest item in processing list is not timed-out yet
				return NULL;
			default:
				return $raw;
		}
	}


	public function removeFromProcessing(string $channel, string $uniqueId)
	{
		// number of elements to remove from tail to head
		$count = -1;

		// TODO this does not have to be atomic, right? its being removed anyway
		// intentionally removing value first, if this request fails
		// we will delete from processing list in next run
		$this->redis->delete($this->getValueKey($channel, $uniqueId));
		$this->redis->lRemove($this->getProcessingListKey($channel), $uniqueId, $count);
	}

}
