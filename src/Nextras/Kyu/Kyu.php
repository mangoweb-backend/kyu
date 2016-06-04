<?php

namespace Nextras\Kyu;


class Kyu
{

	const NO_TIMEOUT = 0;

	/** @var string */
	private $channel;

	/** @var IBackend */
	private $backend;

	/** @var int passed to blocking operations */
	private $timeoutInSeconds = self::NO_TIMEOUT;


	public function __construct(string $channel, IBackend $backend)
	{
		$this->channel = $channel;
		$this->backend = $backend;
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

		$this->backend->enqueue($this->channel, $message);
	}


	/**
	 * = ack
	 * TODO
	 */
	public function removeSuccessful(IMessage $message)
	{
		$this->backend->removeFromProcessing($this->channel, $message->getUniqueId());
	}


	/**
	 * Return message immediately if bound queue is not empty,
	 * otherwise wait forever until new message is enqueued.
	 */
	public function waitForOne() : IMessage
	{
		$raw = $this->backend->waitForOne($this->channel, $this->timeoutInSeconds);
		return $this->processRawMessage($raw);
	}


	/**
	 * Returns immediately. If queue was empty and no message needs processing,
	 * returns NULL.
	 * @return NULL|IMessage
	 */
	public function getOneOrNone()
	{
		$raw = $this->backend->getOneOrNone($this->channel);
		if (!$raw) {
			return NULL;
		}
		return $this->processRawMessage($raw);
	}


	private function processRawMessage(string $raw) : IMessage
	{
		/** @var SerializedMessageStruct $retrieved */
		$retrieved = unserialize($raw, [
			'allowed_classes' => [SerializedMessageStruct::class],
		]);
		// TODO handle FALSE
		$message = $retrieved->unserialize();

		$message->getProcessingAttemptsCounter()->decrement();
		return $message;
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
		$this->backend->recycleOne($this->channel);
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
