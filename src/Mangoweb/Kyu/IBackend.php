<?php

namespace Mangoweb\Kyu;


interface IBackend
{

	public function enqueue(string $channel, Message $message);

	public function waitForOne(string $channel, int $timeoutInSeconds) : string;

	/**
	 * @return NULL|string
	 */
	public function getOneOrNone(string $channel);

	public function recycleOne(string $channel);

	public function removeFromProcessing(string $channel, string $uniqueId);

	public function startTimeout(string $channel, string $messageId, int $ttl);

}
