<?php


trait Object
{

	use Connect;

	use Singleton;


	public static function logged()
	{
		return loggedIn();
	}

	public static function ajax($id=NULL)
	{
//		db($_GET);
		return isset($_GET['ajax']);
	}
}