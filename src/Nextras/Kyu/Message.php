<?php

namespace Nextras\Kyu;

use ReflectionClass;


final class Message
{

	const NOT_AVAILABLE = NULL;

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var int seconds
	 */
	protected $processingDurationLimit;

	/**
	 * @var Counter
	 */
	protected $processingAttemptsCounter;

	/**
	 * @var string arbitrary data format
	 */
	protected $payload;

	/**
	 * Populated when returned from Kyu::recycleOneFailed()
	 * @var self::NOT_AVAILABLE|bool
	 */
	protected $failedPermanently = self::NOT_AVAILABLE;


	/**
	 * @param string  $payload                 arbitrary data format
	 * @param int     $processingDurationLimit seconds
	 * @param Counter $processingAttemptsCounter
	 */
	public function __construct(string $payload, int $processingDurationLimit = NULL, Counter $processingAttemptsCounter = NULL)
	{
		assert($processingDurationLimit === NULL || $processingDurationLimit > 0);

		$this->id = md5(random_bytes(16));
		$this->processingDurationLimit = $processingDurationLimit ?? 10;
		$this->processingAttemptsCounter = $counter ?? new Counter(3);
		$this->payload = $payload;
	}


	/**
	 * Returns maximum duration the message can spend in “processing” state
	 * until it is retried. Does not include the time waiting in the queue.
	 * @return int seconds
	 */
	public function getProcessingDurationLimit() : int
	{
		return $this->processingDurationLimit;
	}


	/**
	 * Returns how many times should this message be inserted back
	 * into processing queue after processing failure.
	 * Each retry must decrement this counter.
	 */
	public function getProcessingAttemptsCounter() : Counter
	{
		return $this->processingAttemptsCounter;
	}


	public function getUniqueId() : string
	{
		return $this->id;
	}


	public function serializeToJson() : string
	{
		return json_encode([
			'id' => $this->id,
			'ttl' => $this->processingDurationLimit,
			'counter' => $this->processingAttemptsCounter->getValue(),
			'payload' => $this->payload,
		]);
	}


	public static function unserializeFromJson(string $raw) : Message
	{
		$data = json_decode($raw, TRUE, NULL, JSON_BIGINT_AS_STRING);
		// TODO handle failure

		/** @var self $instance */
		$instance = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();
		$instance->id = $data['id'];
		$instance->processingDurationLimit = $data['ttl'];
		$instance->processingAttemptsCounter = new Counter($data['counter']);
		$instance->payload = $data['payload'];
		$instance->failedPermanently = $data['failed'] ?? self::NOT_AVAILABLE;
		return $instance;
	}


	/**
	 * Use when message is returned from Kyu::recycleOneFailed()
	 * @return self::NOT_AVAILABLE|bool
	 */
	public function isFailedPermanently()
	{
		return $this->failedPermanently;
	}

}
