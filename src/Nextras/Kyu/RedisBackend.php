<?php

namespace Nextras\Kyu;

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


	public function __construct(Redis $redis)
	{
		$this->redis = $redis;
	}


	public function enqueue(string $channel, IMessage $message)
	{
		$sealed = new SerializedMessageStruct($message);
		$raw = serialize($sealed);
		$ttl = $message->getProcessingDurationLimit();
		$id = $message->getUniqueId();

		// TODO transaction
		$this->redis->set($this->getValueKey($channel, $id), $raw);
		$this->redis->lPush($this->getQueueListKey($channel), $id);
		$this->redis->setex($this->getAliveKey($channel, $id), $ttl, self::VALUE_DOES_NOT_MATTER);
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
	private function loadScript($name)
	{
		return $this->redis->script('load', __DIR__ . "/scripts/$name.lua");
	}


	public function recycleOne(string $channel)
	{
		$sha = $this->loadScript('recycleOne');
		$this->redis->evalSha($sha);

		// TODO atomicity

		// peek right of LIST_PROCESSING (which is oldest)
		// if it is alive: end
		// otherwise decrement retries
		// if retries remaining: move to queue
		// otherwise discard message and throw exception

		// TODO multi & watch PROCESSING

		$list = $this->redis->lRange($this->getProcessingListKey($channel), 0, 0);
		// list is empty array or array of oldest item
		$oldestItem = array_shift($list);

		// TODO should this also remove from value key? probably not
		// because GC will or subsequent enqueue will overwrite

		if ($oldestItem === NULL) {
			return NULL;
		}

		if ($this->redis->exists($this->getAliveKey($channel, "TODO MESSAGE ID"))) {
			return NULL;
		}


		// TODO exec (and retry if WATCH-triggered failure happened)
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
