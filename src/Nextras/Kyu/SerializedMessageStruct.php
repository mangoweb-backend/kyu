<?php

namespace Nextras\Kyu;

use ReflectionClass;


class SerializedMessageStruct
{

	/** @var string class name to unserialize to */
	private $className;

	/** @var string */
	private $raw;


	/**
	 * @param IMessage $message
	 */
	public function __construct(IMessage $message)
	{
		$this->className = get_class($message);
		$this->raw = $message->serialize();
	}


	public function unserialize()
	{
		$ref = new ReflectionClass($this->className);
		/** @var IMessage $instance */
		$instance = $ref->newInstanceWithoutConstructor();
		$instance->unserialize($this->raw);
		return $instance;
	}

}
