<?php

trait Singleton
{

	public static function one()
	{
		static $instance = NULL;

		$instance || ($instance = new self);

		return $instance;
	}

}