<?php

namespace Nextras\Kyu;

use DateInterval;


class Kyu
{

	/**
	 * Add message to queue or reinsert failed message back for another try.
	 * If the message does not have any more remaining attempts, it throws.
	 * @param IMessage $message
	 * @throws MessagePermanentlyFailedException
	 */
	public function enqueue(IMessage $message)
	{
		if ($message->getProcessingAttemptsCounter()->getValue() === 0) {
			throw new MessagePermanentlyFailedException($message);
		}
		// TODO
	}


	/**
	 * Return message immediately if bound queue is not empty,
	 * otherwise wait forever until new message is enqueued.
	 */
	public function waitForOne() : IMessage
	{
		// TODO
		/** @var IMessage $retrieved */
		$retrieved = NULL;
		$retrieved->getProcessingAttemptsCounter()->decrement();
		return $retrieved;
	}


	/**
	 * Returns immediately. If queue was empty and no message needs processing,
	 * returns NULL.
	 */
	public function getOneOrReturn() : IMessage
	{
		// TODO
	}


	/**
	 * Insert single messages older than IMessage::getProcessingDurationLimit() in “processing” state back
	 * to the queue if they have retries remaining.
	 * Should be called until no more messages are left and NULL is returned.
	 * Messages without remaining retries will be move to “failed” state instead.
	 * @throws MessagePermanentlyFailedException
	 */
	public function recycleFailed() : IMessage
	{
		// TODO get single one from processing
		if ('empty') {
			return NULL;
		}

		/** @var IMessage $failed */
		$failed = NULL;
		$failed->getProcessingAttemptsCounter()->decrement();
		$this->enqueue($failed);
		return $failed;
	}

}
