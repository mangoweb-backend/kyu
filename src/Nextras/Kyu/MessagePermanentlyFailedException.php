<?php

namespace Nextras\Kyu;


class MessagePermanentlyFailedException extends \RuntimeException implements KyuException
{

	/** @var IMessage */
	private $failedMessage;


	/**
	 * MessagePermanentlyFailedException constructor.
	 *
	 * @param IMessage   $failedMessage
	 * @param \Exception $previous
	 */
	public function __construct(IMessage $failedMessage, \Exception $previous = NULL)
	{
		parent::__construct("TODO", NULL, $previous);
		$this->failedMessage = $failedMessage;
	}


	/**
	 * @return IMessage
	 */
	public function getFailedMessage() : IMessage
	{
		return $this->failedMessage;
	}

}
