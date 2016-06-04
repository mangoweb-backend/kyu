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


	public function serialize() : string
	{
		return serialize([$this->id, $this->processingAttemptsCounter->getValue(), $this->word]);
	}


	public function unserialize(string $serialized)
	{
		$data = unserialize($serialized, ['allowed_classes' => FALSE]);

		$this->id = $data[0];
		$this->processingAttemptsCounter = new Kyu\Counter($data[1]);
		$this->word = $data[2];
	}


	/**
	 * @return string
	 */
	public function getWord()
	{
		return $this->word;
	}

}
