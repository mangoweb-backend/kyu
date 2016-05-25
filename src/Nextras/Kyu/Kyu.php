<?php

namespace Nextras\Kyu;

use DateInterval;


class Kyu
{

	/**
	 * Add message to queue.
	 */
	public function enqueue(IMessage $message)
	{

	}


	/**
	 * Return message immediately if bound queue is not empty,
	 * otherwise wait forever until new message is enqueued.
	 */
	public function waitForOne() : IMessage
	{

	}


	/**
	 * Returns immediately. If queue was empty and no message needs processing,
	 * returns NULL.
	 */
	public function getOneOrReturn() : IMessage
	{

	}


	/**
	 * Insert all messages other than given interval in “processing” state back
	 * to the queue if they have retries remaining.
	 * Messages without remaining retries will be move to “failed” state instead.
	 */
	public function recycleFailed(DateInterval $olderThan)
	{

	}


	/**
	 * Immediately returns failed message (which did not succeed in getRetriesRemaining() retries).
	 * This method should be called periodically at least to remove the messages from redis.
	 * It should also be used to log the failed messages for further debugging and resolving issues.
	 */
	public function getOneFailedOrReturn() : IMessage
	{

	}

}
