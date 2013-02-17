<?php


class Access
{

	use Object;


	private static $aliases = [];

	/**
	 * static boolean can(string $what)
	 *     Whether a user has the permission codenamed $what.
	 *
	 * @param string $what
	 * @return boolean
	 */
	public static function can($what)
	{
		isset(self::$aliases[$what]) && ($what = self::$aliases[$what]);
		$can = (devMode() || isset(self::permits()[$what]));

		return $can;
	}

	/**
	 * static boolean cant(string $what)
	 *      Just a shortcut to !self::can($what)
	 *
	 * @param string $what
	 * @return boolean
	 */
	public static function cant($what)
	{
		return !self::can($what);
	}

	/**
	 * static void setAlias(string $code, mixed $alias)
	 *   Alias permit codenamed $code (real) to virtual permit $alias. If $alias
	 * is an array, it sets all its values as aliases of $code.
	 *
	 * @param string $code
	 * @param mixed $alias
	 * @return void
	 */
	public static function setAlias($code, $alias)
	{
		if (!is_array($alias))
		{
			self::$aliases[$alias] = $code;
		}
		else
		{
			foreach ($alias as $k)
			{
				self::setAlias($code, $k);
			}
		}
	}

	/**
	 * static void enforce()
	 *      Block page with a generic message if assertions fail. If first param
	 * is a string, it's interpreted as a permit name. Else it's taken for an
	 * assertion. Params beyond the first are always seen as assertions.
	 *
	 * @return void
	 */
	public static function enforce()
	{
		// If first param is omitted, this is a synonym of ::block()
		$args = func_get_args() + [false];

		if (is_string($args[0]))
		{
			self::cant($args[0]) && self::block();
			unset($args[0]);
		}

		foreach ($args as $arg)
		{
			!!$arg || self::block();
		}
	}

	/**
	 * static void block()
	 *      Throws an Exception, with a generic message.
	 *
	 * @throws PublicException
	 */
	public static function block()
	{
		$msg = 'No posee permisos para realizar esta acción.';
		throw new PublicException($msg);
	}

	/**
	 * static array environment(string $pageid)
	 *      Read all relevant info related to the current page state: the area,
	 * page attrs, menu group, etc.
	 *
	 * @param string $pageid
	 * @return array
	 */
	public static function environment($pageid)
	{
		static $state;

		if (empty($state[$pageid]))
		{
			$areaid = self::pageArea($pageid);

			// All areas and pages
			$areas = self::areas();
			$pages = self::pages();

			$page = $pages[$pageid];
			$area = $areas[$areaid];

			$allGroups = self::groups();

			foreach ($pages as $k => &$p)
			{
				if ($p['id_area'] && ($p['module'] == $areaid))
				{
					$groups[$p['id_area']] =& $allGroups[$p['id_area']];
					$groups[$p['id_area']]['pages'][$k] =& $p;
				}
			}

			$areaPages = self::areaPages($areaid);

			$state = compact('areas', 'pages', 'allGroups', 'groups',
			                 'areaPages', 'pageid', 'page', 'areaid', 'area');

			$states[$pageid] = $state;
		}

		return $states[$pageid];
	}


/******************************************************************************/
/******************************* G E T T E R S ********************************/
/******************************************************************************/

	/**
	 * static array permits()
	 *      Return all permissions available to current user.
	 *
	 * @return array
	 */
	private static function permits()
	{
		return devMode() ? self::db_permits() : self::db_userPermits();
	}

	/**
	 * static array pages()
	 *      Return the list of all pages available for current user.
	 *
	 * @return array
	 */
	private static function pages()
	{
		return devMode() ? self::db_pages() : self::db_userPages();
	}

	/**
	 * static array areas()
	 *      Return the list of all existing areas.
	 *
	 * @return array
	 */
	private static function areas()
	{
		$areas = self::db_areas();

		foreach ($areas as $code => &$area)
		{
			$pattern = IMAGES_PATH . "/navButtons/{$code}.{png,gif}";
			$image = current(glob($pattern, GLOB_BRACE));
			$area['image'] = str_replace(BASE, '', $image);
		}

		return $areas;
	}

	/**
	 * static array groups()
	 *      Return the list of all existing menu groups.
	 *
	 * @return array
	 */
	private static function groups()
	{
		return self::db_groups();
	}

	/**
	 * static string pageArea(string $page)
	 *      Given a page id, returns the associated area (formerly module).
	 *
	 * @param string $page
	 * @return string
	 */
	public static function pageArea($page)
	{
		$pages = self::pages();
		return isset($pages[$page]) ? $pages[$page]['module'] : NULL;
	}

	/**
	 * static string areaPages(string $area)
	 *      Given an area id, returns the list of all available pages in that
	 * area, for the currrent user.
	 *
	 * @param string $area
	 * @return string
	 */
	private static function areaPages($area)
	{
		foreach (self::pages() as $page)
		{
			if (($page['module'] == $area) || ($page['id_area'] == 'global'))
			{
				$res[$page['code']] = $page;
			}
		}

		return empty($res) ? [] : $res;
	}


/******************************************************************************/
/****************************** D A T A B A S E *******************************/
/******************************************************************************/

	private static function exec_query($sql)
	{
		static $instance, $results;

		if (empty($results[$sql]))
		{
			// Method query requires an instance, but we're going static so far
			if (empty($instance))
			{
				$instance = new self;
			}

			$res = $instance->query($sql);

			while ($data=mysql_fetch_assoc($res))
			{
				$ret[$data['code']] = $data;
			}

			$results[$sql] = empty($ret) ? [] : $ret;
		}

		return $results[$sql];
	}

	private static function db_permits()
	{
		$sql = "SELECT *
		        FROM `_permissions`";
		return self::exec_query($sql);
	}

	private static function db_userPermits()
	{
		$sql = "SELECT `p`.*
		        FROM `_permissions` `p`
		        LEFT JOIN `_permissions_by_profile` `pbp` USING( `code` )
		        LEFT JOIN `_permissions_by_user` `pbu` USING( `code` )
		        WHERE (`pbu`.`user` = '" . getSes('user') . "' && `pbu`.`type` = 'add')
		        OR (`pbp`.`id_profile` = '" . getSes('id_profile') . "' && IFNULL(`pbu`.`type`, 'add') <> 'sub')";
		return self::exec_query($sql);
	}

	private static function db_pages()
	{
		$sql = "SELECT `g`.*,
		               `a`.`area`,
		               `p`.`name`
		        FROM `_pages` `g`
		        LEFT JOIN `_permissions` `p` USING( `code` )
		        LEFT JOIN `_areas` `a` USING( `id_area` )
		        ORDER BY `a`.`order`, `g`.`order`";
		return self::exec_query($sql);
	}

	private static function db_userPages()
	{
		$sql = "SELECT `g`.*,
		               `a`.`area`,
		               `p`.`name`
		        FROM `_pages` `g`
		        LEFT JOIN `_permissions` `p` USING( `code` )
		        LEFT JOIN `_permissions_by_profile` `pbp` USING( `code` )
		        LEFT JOIN `_permissions_by_user` `pbu` USING( `code` )
		        LEFT JOIN `_areas` `a` USING( `id_area` )
		        WHERE(`pbu`.`user` = '" . getSes('user') . "' && `pbu`.`type` = 'add')
		        OR (`pbp`.`id_profile` = '" . getSes('id_profile') . "' AND IFNULL(`pbu`.`type`, 'add') <> 'sub')
		        ORDER BY `a`.`order`, `g`.`order`";
		return self::exec_query($sql);
	}

	private static function db_areas()
	{
		$sql = "SELECT `p`.`code`,
		               `p`.`name`
		        FROM `_modules` `m`
		        LEFT JOIN `_permissions` `p` USING(`code`)
				WHERE `p`.`type` = 'module'
		        ORDER BY `m`.`order`";
		return self::exec_query($sql);
	}

	private static function db_groups()
	{
		$sql = "SELECT *,
		               `id_area` AS 'code'
		        FROM `_areas`
		        ORDER BY `order`";
		return self::exec_query($sql);
	}

}