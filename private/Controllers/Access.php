<?php


class Access
{

	use Object;


	/**
	 * static boolean can(string $what)
	 *     Whether current user has the permission codenamed $what.
	 *
	 * @param string $what
	 * @return boolean
	 */
	public static function can($what)
	{
		return (devMode() || isset(self::accesslist()[$what]));
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
	 * static boolean canLoad(string $model, string $page)
	 *     Whether current user has access to given page.
	 *
	 * @param int $pageid
	 * @return boolean
	 */
	public static function canLoad($pageid)
	{
		$pages = PageController::pages();

		// Is the page defined (in the database)?
		if (!$pageid || empty($pages[$pageid]))
		{
			return false;
		}

		$page = $pages[$pageid];

		// Access is granted without further ado for homepage and to superusers
		if (devMode() || PageController::isHome($pageid))
		{
			return true;
		}

		// Are the defined model and method valid (i.e. callable)?
		if (!is_callable(PageController::handler($pageid)))
		{
			return false;
		}

		// Deny if parent exists (page's module) and is not allowed
		if ($page['parent'] && !self::canLoad($page['parent']))
		{
			return false;
		}

		// Read credentials, if set
		if (is_callable([$Hnd, 'pagecredentials']))
		{
			$credentials = (array)$Hnd->pagecredentials();

			if (!empty($credentials[$method]))
			{
				// Explicitly allowed if set to true
				if ($credentials[$method] === true)
				{
					return true;
				}
				else
				{
					// Explicitly denied if any related permission is denied
					foreach ((array)$credentials[$method] as $permission)
					{
						if (!self::can($permission))
						{
							return false;
						}
					}
				}
			}
		}


		// If not explicitly allowed/denied, use default access
		return !empty($Hnd->pageaccess);
	}

	/**
	 * static boolean cantLoad(string $model, string $page)
	 *      Just a shortcut to !self::canLoad($model, $page)
	 *
	 * @param string $model
	 * @param string $page
	 * @return boolean
	 */
	public static function cantLoad($model, $page)
	{
		return !self::canLoad($model, $page);
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

	public static function accesslist()
	{
		static $access = [];

		if (!$access && self::logged() && !devMode())
		{
			// Get all relevant access rules
			list($userid, $profile) = [getSes('userid'), getSes('id_profile')];

			$sql = "SELECT *,
			               IF(`access` = 'allow', 1, 0) AS 'switch',
					       IF(`object` = 'global', 0, `objectid`) AS 'objectid'
			        FROM `crm_access`
			        WHERE (`object` = 'user' AND `objectid` = '{$userid}')
			           OR (`object` = 'profile' AND `objectid` >= '{$profile}')
			           OR (`object` = 'global')
			        ORDER BY `objectid` DESC";
			$res = self::s_query($sql);

			if ($res->failed)
			{
				$msg = "Ocurrió un error con la información de permisos";
				throw new Exception($msg);
			}

			if ($res->rows)
			{
				while ($data=mysql_fetch_assoc($res->res))
				{
					$rules[$data['object']][$data['objectid']][] = $data;
				}

				$rules += ['global' => [], 'user' => [], 'profile' => []];

				foreach ($rules['global'][0] as $rule)
				{
					$access[$rule['code']] = (int)$rule['switch'];
				}

				// Profile rules are inherited: we need to keep the closest rule
				foreach ($rules['profile'] as $ruleslist)
				{
					foreach ($ruleslist as $rule)
					{
						$access[$rule['code']] = (int)$rule['switch'];
					}
				}
			}

			$access = array_filter($access);
		}

		return $access;
	}

}