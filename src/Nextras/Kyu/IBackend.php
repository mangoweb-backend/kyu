<?php

namespace Nextras\Kyu;


interface IBackend
{

	public function enqueue(string $raw);

	public function waitForOne() : string;

	/**
	 * @return NULL|string
	 */
	public function getOneOrNone();

}
