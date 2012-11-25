<?php

/**
 * Models and Views are initialized exactly the same. For each namespace, there
 * is a Singleton created by relying on static method get. These named instances
 * are cached on a private static var, to be retrieved on subsequent calls.
 *
 * Since the procedure matches, the aforementioned static var and method come
 * packed within a trait.
 */
trait ModelView
{

	private static $cache;


	/**
	 * final static Model get(string $name[, string $namespace = 'global'])
	 *      Get from cache, or instantiate, a Model for given $name.
	 *
	 * @param string $name
	 * @param string $namespace
	 * @return Model subclass
	 */
	final public static function get($name, $namespace='global')
	{
		$implementer = get_class();

		if (!$name)
		{
			$msg = "Trying to initialize a {$implementer} without a name";
			throw new Exception($msg);
		}

		if (!preg_match('/\w+/', $name))
		{
			$msg = "Illegal character in {$implementer} name: {$name}";
			throw new Exception($msg);
		}

		$ucname = ucfirst($name);

		if (empty(self::$cache[$namespace][$ucname]))
		{
			$hierarchy = [];
			$class = $implementer;

			foreach (explode('.', $name) as $file)
			{
				$parent = $class;
				$hierarchy[] = ucfirst($file);

				$path = "app/{$implementer}/" . join('.', $hierarchy) . '.php';
				$class = "{$implementer}_" . join('', $hierarchy);

				if (!is_file($path))
				{
					eval("class {$class} extends {$parent}{}");
				}
				elseif (!@include $path)
				{
					$msg = "Failed to load {$class} ({$implementer} {$ucname})";
					throw new Exception($msg);
				}
				elseif (!class_exists($class))
				{
					$msg = "{$implementer} class {$class} not found ({$ucname})";
					throw new Exception($msg);
				}
			}

			// Create
			$Instance = new $class($name);

			// Cache
			self::$cache[$namespace][$ucname] = $Instance;

			// Link corresponding counterpart
			$counterpart = ($implementer === 'Model') ? 'View' : 'Model';
			$Instance->$counterpart = $counterpart::get($ucname, $namespace);
		}

		return self::$cache[$namespace][$ucname];
	}

}