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
		$this->channel = md5($channel);
		$this->backend = $backend;
	}


	/**
	 * Add message to queue or reinsert failed message back for another try.
	 * If the message does not have any more remaining attempts, it throws.
	 * @param Message $message
	 * @return void
	 */
	public function enqueue(Message $message)
	{
		$this->backend->enqueue($this->channel, $message);
	}


	/**
	 * = ack
	 * TODO
	 */
	public function removeSuccessful(Message $message)
	{
		$this->backend->removeFromProcessing($this->channel, $message->getUniqueId());
	}


	/**
	 * Return message immediately if bound queue is not empty,
	 * otherwise wait forever until new message is enqueued.
	 */
	public function waitForOne() : Message
	{
		$raw = $this->backend->waitForOne($this->channel, $this->timeoutInSeconds);
		return $this->processRawMessage($raw);
	}


	/**
	 * Returns immediately. If queue was empty and no message needs processing,
	 * returns NULL.
	 * @return NULL|Message
	 */
	public function getOneOrNone()
	{
		$raw = $this->backend->getOneOrNone($this->channel);
		if (!$raw) {
			return NULL;
		}
		return $this->processRawMessage($raw);
	}


	private function processRawMessage(string $rawJson) : Message
	{
		// TODO this should probably be atomic and scripted
		$message = Message::unserializeFromJson($rawJson);
		$this->backend->startTimeout($this->channel, $message->getUniqueId(), $message->getProcessingDurationLimit());
		$message->decrementProcessingAttemptsCounter();
		return $message;
	}


	/**
	 * Insert single messages older than IMessage::getProcessingDurationLimit() in “processing” state back
	 * to the queue if they have retries remaining.
	 * Should be called until no more messages are left and NULL is returned.
	 * Messages without remaining retries will be move to “failed” state instead.
	 *
	 * Message returned must not be processed and should only be used for logging, maintenance
	 * and similar purposes! Unless Message::isFailedPermanently(), this Message will be returned
	 * on some subsequent call to waitForOne() or getOneOrNone().
	 *
	 * @return NULL|Message
	 */
	public function recycleOneFailed()
	{
		$raw = $this->backend->recycleOne($this->channel);
		if ($raw === NULL) {
			return NULL;
		}

		return Message::unserializeFromJson($raw);
	}

}
