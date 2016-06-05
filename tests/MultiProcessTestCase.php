<?php

namespace Tests\Mangoweb;

use Tester;


abstract class MultiProcessTestCase extends TestCase
{

	final public function run($method = NULL)
	{
		$pid = pcntl_fork();
		if ($pid < 0) {
			throw new \RuntimeException('Forking failed');

		} elseif ($pid === 0) {
			$this->sideProcess();

		} else {
			$this->setUp();
			$this->mainProcess($pid);
			pcntl_waitpid($pid, $status);
			if ($status !== 0) {
				Tester\Assert::fail('Side process errored');
			}
			$this->tearDown();
		}
	}


	abstract public function mainProcess(int $pid);

	abstract public function sideProcess();

}
