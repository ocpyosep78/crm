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
		$html = preg_replace('/[\n\r]/', '\n', '"' . addslashes($html) . '"');

		if ((int)$append < 0)
		{
			$html = "$('{$selector}').html() + {$html}";
		}
		elseif ((int)$append > 0)
		{
			$html = "{$html} + $('{$selector}').html()";
		}

		self::js("$('{$selector}').html({$html})");
	}

	/**
	 * static void content(string $x[, bool $isHtml = false])
	 *      Update content (#main_box div).
	 *
	 * @param string $x         Either a template name/path or directly html
	 * @param bool $isHtml      Whether $x is the html or just a template path
	 * @return void
	 */
	public static function content($x, $isHtml=false)
	{
		$html = $isHtml ? $x : Template::one()->fetch($x);
		self::html('#main_box', $html);
	}

	public static function call()
	{
		$args = func_get_args();
		$fn = array_shift($args);

		$args = array_map(array(get_class(), 'php2js'), $args);
		$str_args = join(', ', $args);

		self::js("{$fn}({$str_args})");
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

	public static function say($msg=NULL, $type='', $img='')
	{
		if (is_null($msg))
		{
			empty($_SESSION['say'])
				? false
				: call_user_func_array('self::say', $_SESSION['say']);
			return ($_SESSION['say'] = NULL);
		}

		self::call('say', $msg, $type, $img);
	}

	public static function sayLater($msg, $type='', $img='')
	{
		$_SESSION['say'] = [$msg, $type, $img];
	}

	public static function alert($alert)
	{
		self::call('alert', addslashes($alert));
	}

	public static function showMenu()
	{
		self::call('showMenu');
	}

	public static function hideMenu()
	{
		self::call('hideMenu');
	}

	public static function debug($msg)
	{
		self::call('debug', addslashes($msg));
	}

	public static function assign($var, $val)
	{
		self::js("{$var} = " . self::php2js($val));
	}

	public static function importConst()
	{
		foreach (func_get_args() as $var)
		{
			self::assign($var, constant($var));
		}
	}

	public static function reload($url=NULL)
	{
		if (!is_null($url))
		{
			$url = trim($url, '/');
			(strpos(BBURL, $url) !== 0) && ($url = BBURL . '/' . $url);
		}

		self::js('location.href = ' . ($url ? "'$url'" : 'location.href'));
	}

	public static function page($page, $atts=[], $msg='', $msgtype=0, $img='')
	{
		$msg && self::sayLater($msg, $msgtype, $img);
		self::reload($page); # TODO : load only content (not page reload)
	}
}