<?php

namespace Nextras\Kyu;


interface IBackend
{

	public function enqueue(IMessage $message);

	public function waitForOne() : IMessage;


	/**
	 * @return NULL|IMessage
	 */
	public function getOneOrNone();

}
