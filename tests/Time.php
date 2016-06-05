<?php

namespace Tests\Mangoweb;


class Time
{

	const ms = 1e3;

	/** @var float microtime */
	private static $start;


	public static function start()
	{
		self::$start = microtime(TRUE);
	}


	/**
	 * @return float microtime
	 */
	public static function get()
	{
		return microtime(TRUE) - self::$start;
	}


	/**
	 * @param float $microtime
	 */
	public static function blockUntil($microtime)
	{
		usleep(max(0, $microtime - self::get()));
	}

}
