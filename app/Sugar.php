<?php

class Sugar
{

	static $pages = [
		'agenda' => 'agenda_en_español',
		'users'  => 'usuarios',
	];

	/**
	 * static string home()
	 *      If homepage is not called 'home' this one returns the actual name.
	 *
	 * @return string
	 */
	public static function home()
	{
		return 'agenda';
	}

	public static function page($page, $reverse=false)
	{
		// Translate 'home' to the actual homepage code
		($page == 'home') && self::home() && ($page = self::home());

		$pages = $reverse ? array_flip(self::$pages) : self::$pages;

		return isset($pages[$page]) ? $pages[$page] : $page;
	}

}