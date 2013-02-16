<?php

/**
 * Given a request, build the response bits (js calls, dynamic css rules, alerts
 * or any other actions), disregarding if the request is ajax or not.
 */
class Response
{

/******************************************************************************/
/*********************************** B A S E **********************************/
/******************************************************************************/

	public static function js($js)
	{
		Template::one()->append('js', $js);
	}

	public static function css($css)
	{
		Template::one()->append('css', $css);
	}


/******************************************************************************/
/****************************** S H O R T C U T S *****************************/
/******************************************************************************/

	/**
	 * static self html(string $selector, string $html[, int $append])
	 *
	 * @param string $selector
	 * @param string $html
	 * @param int $append           <0 := prepend | 0 := replace | >0 := append
	 *
	 * @return object self
	 */
	public static function html($selector, $html, $append=0)
	{
		$html = addslashes($html);

		if ((int)$append < 0)
		{
			$html = "$('{$selector}').html() + '{$html}'";
		}
		elseif ((int)$append > 0)
		{
			$html = "'{$html}' + $('{$selector}').html()";
		}
		else
		{
			$html = "'{$html}'";
		}

		return self::js("$('{$selector}').html({$html})");
	}

	public static function call()
	{
		$args = func_get_args();
		$fn = array_shift($args);

		$args = array_map(array(get_class(), 'php2js'), $args);
		$str_args = join(', ', $args);

		return self::js("{$fn}({$str_args})");
	}

	public static function php2js($val)
	{
		if (is_bool($val))
		{
			$val = $val ? 'true' : 'false';
		}
		elseif (is_null($val))
		{
			$val = 'null';
		}
		elseif (is_string($val))
		{
			$val = '"' . preg_replace('_\s+_', ' ', addslashes($val)) . '"';
		}
		elseif (!is_scalar($val))
		{
			$val = json_encode($val);
		}

		return $val;
	}

	public static function say($msg, $type='', $img='')
	{
		return self::call('say', $msg, $type, $img);
	}

	public static function alert($alert)
	{
		$alert = addslashes($alert);
		return self::call('alert', $alert);
	}

	public static function showMenu()
	{
		return self::call('showMenu');
	}

	public static function hideMenu()
	{
		return self::call('hideMenu');
	}

	public static function debug($msg)
	{
		$msg = addslashes($msg);
		return self::call('debug', $msg);
	}

	public static function assign($var, $val)
	{
		return self::js("{$var} = " . self::php2js($val));
	}

	public static function importConst()
	{
		foreach (func_get_args() as $var)
		{
			self::assign($var, constant($var));
		}
	}
}