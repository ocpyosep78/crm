<?php

/**
 * Adds a method one() to classes who include this trait, for singleton-like
 * instantiation. It supports namespacing, so that each namespace has a separate
 * "singleton" instance. To create a namespaced singleton, pass a $ns to one().
 */
trait Singleton
{

	public static function one($ns='*')
	{
		static $instances = [];

		isset($instances[$ns]) || ($instances[$ns] = new self($ns));

		return $instances[$ns];
	}

}