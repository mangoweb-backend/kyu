<?php

namespace Nextras\Kyu;


class Kyu
{

	/** @var string */
	private $channel;
	/** @var IBackend */
	private $backend;


	public function __construct(string $channel, IBackend $backend = NULL)
	{
		$this->channel = $channel;
		$this->backend = $backend ?? new RedisBackend;
	}


	/**
	 * Add message to queue or reinsert failed message back for another try.
	 * If the message does not have any more remaining attempts, it throws.
	 * @param IMessage $message
	 * @return void
	 * @throws MessagePermanentlyFailedException
	 */
	public function enqueue(IMessage $message)
	{
		if ($message->getProcessingAttemptsCounter()->getValue() === 0) {
			throw new MessagePermanentlyFailedException($message);
		}
		$this->backend->enqueue($message);
	}


	/**
	 * Return message immediately if bound queue is not empty,
	 * otherwise wait forever until new message is enqueued.
	 */
	public function waitForOne() : IMessage
	{
		/** @var IMessage $retrieved */
		$retrieved = $this->backend->waitForOne();
		$retrieved->getProcessingAttemptsCounter()->decrement();
		return $retrieved;
	}


	/**
	 * Returns immediately. If queue was empty and no message needs processing,
	 * returns NULL.
	 * @return NULL|IMessage
	 */
	public function getOneOrNone() : IMessage
	{
		return $this->backend->getOneOrNone();
	}


	/**
	 * Insert single messages older than IMessage::getProcessingDurationLimit() in “processing” state back
	 * to the queue if they have retries remaining.
	 * Should be called until no more messages are left and NULL is returned.
	 * Messages without remaining retries will be move to “failed” state instead.
	 * @throws MessagePermanentlyFailedException
	 */
	public function recycleOneFailed() : IMessage
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
