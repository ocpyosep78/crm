<?php

class Sugar
{

	public static function page($page, $reverse=false)
	{

		$pages = [
			'home' => 'inicio',
		];

		$reverse && ($pages = array_flip($pages));

		return isset($pages[$page]) ? $pages[$page] : $page;
	}

}