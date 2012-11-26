<?php


trait Object
{

	use Connect;

	use Singleton;


	public static function logged()
	{
		return loggedIn();
	}
}