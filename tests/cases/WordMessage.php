<?php

use Nextras\Kyu;


class WordMessage extends Kyu\Message
{

	/** @var string */
	private $word;


	/**
	 * @param string $word
	 */
	public function __construct(string $word)
	{
		parent::__construct();
		$this->word = $word;
	}


	/**
	 * String representation of object
	 * @link  http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		return $this->word;
	}


	/**
	 * Constructs the object
	 * @link  http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized The string representation of the object.
	 * @return void
	 */
	public function unserialize($serialized)
	{
		$this->word = (string) $serialized;
	}

}
