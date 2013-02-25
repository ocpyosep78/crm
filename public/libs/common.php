<?php

function devMode()
{
	return (defined('DEVMODE') && DEVMODE) || (getSes('id_profile') == 1);
}

function safeDiv($a , $b , $def=0)
{
	return $b ? $a/$b : $def;
}

function getPercent($val, $total, $dec=0)
{
	return ($total) ? round($val * 100 / $total, $dec) : 0;
}

function win2unix($path)
{
	return str_replace( '\\', '/', $path );
}

function isWinOS()
{
	return !!( strtoupper(substr(PHP_OS,0,3)) === 'WIN' );
}

function checkTime($str)
{
	return !!preg_match('/^(2[0-3]|[01]\d):[0-5]\d$/', $str);
}

function canonicalize_time($time)
{
	return preg_match('/[\s0]*(\d|1[0-2]):(\d{2})\s*([AaPp][Mm])/xms', $time, $match)
		? sprintf('%02d:%d%s', $match[1], $match[2], strtoupper($match[3]))
		: false;
}

function format_time($h, $m=NULL)
{
	$time = !$m ? (strstr($h, ':') ? $h : "{$h}:00") : "{$h}:{$m}";
	preg_match('/^(2[0-3]|[01]?\d):([0-5]?\d)$/xms', $time, $m);
	return preg_match('/^(2[0-3]|[01]?\d):([0-5]?\d)$/xms', $time, $match)
		? sprintf('%02d:%02d', $match[1], $match[2])
		: false;
}

function format_date($y, $m, $d)
{
	return preg_match('/[^\d]+/', $y.$m.$d) ? false : date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
}

function checkTimeStamp($str='')
{
	return date('Y-m-d H:i:s', strtotime($str)) === $str;
}

function mySqlDate($time=NULL)
{
	return date('Y-m-d H:i:s', $time);
}

/**
 * Uses array $a2 keys to sort matching keys in $a1.
 *
 * For example:
 *	$a1 = array('one' => 'uno', 'two' => 'dos', 'three' => 'tres', 'four' => 'cuatro');
 *	$a2 = array('two' => 'anything', 'one' => 'does not matter', 'four' => NULL);
 *	array_sort_keys($a1, $a1);
 *	# Now $a1 is:
 *		array('two' => 'dos', 'one' => 'uno', 'four' => 'cuatro', 'three' => 'tres')
 */
function array_sort_keys(&$a1, $a2)
{
	foreach (array_intersect_key($a2, $a1) as $k => $v)
	{
		$new[$k] = isset($a1[$k]) ? $a1[$k] : NULL;
	}

	foreach (array_diff_key($a1, $a2) as $k => $v)
	{
		$new[$k] = $v;
	}

	$a1 = isset($new) ? $new : $a1;
}

/**
 * function toJson(array $arr[, boolean $forceObj = false])
 *      Converts an array to a JSON string. If not $forceObj, numeric arrays are
 * returned as JS arrays (i.e. with [] delimiters instead of {}).
 *
 * @param array $arr
 * @param boolean $forceObj
 * @return string
 */
function toJson($arr, $forceObj=false)
{
	if (!is_array($arr) || !count($arr))
	{
		return $forceObj ? '{}' : '[]';
	}

	$onlyNum = true;

	foreach ($arr as $k => $v)
	{
		$onlyNum = $onlyNum && is_numeric($k);
	}

	foreach( $arr as $k => $v ){
		$key = $onlyNum ? '' : '"'.$k.'":';
		$val = is_array($v)
			? toJson($v)
			: (is_numeric($v) ? $v : '"'.addslashes($v).'"');
		$json[] = "{$key}{$val}";
	}

	$content = join(",", $json);

	return ($onlyNum && !$forceObj) ? "[{$content}]" : "{{$content}}";
}

function toJS($mixed)
{
	if (is_null($mixed))
	{
		return 'null';
	}
	elseif (is_array($mixed) || is_object($mixed))
	{
		return toJson($mixed, true);
	}
	elseif (is_string($mixed))
	{
		if ($mixed === 'undefined')
		{
			return '';
		}
		else
		{
			return '"' . preg_replace('_\s+_', ' ', addslashes($mixed)) . '"';
		}
	}
	else
	{
		return $mixed;
	}
}

function uploadAnalylize($file, $noFileReturn=NULL)
{
	switch ($file['error'])
	{
		case UPLOAD_ERR_OK:				# No error
			return true;

		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			return 'El tamaño del archivo supera el máximo permitido.';

		case UPLOAD_ERR_PARTIAL:
			return 'No se pudo comprobar la integridad del archivo. Inténtelo nuevamente.';

		case UPLOAD_ERR_NO_FILE:
			return $noFileReturn;

		case UPLOAD_ERR_NO_TMP_DIR:
		case UPLOAD_ERR_CANT_WRITE:
		case UPLOAD_ERR_EXTENSION:
			return 'La configuración de la aplicación o del servidor no permite subir este archivo.';
	}

	return 'Ocurrió un error desconocido al intentar subir el archivo.';
}


function saveLog($typeID, $objectID, $extra='', $user=NULL)
{
	// Decide whether to save logs in `logs` table or `history` table
	$data = ['logType'  => $typeID,
	         'objectID' => $objectID,
	         'user'     => $user ? $user : getSes('user'),
	         'extra'    => $extra];

	$ans = oSQL()->registerLog('logs_history', $data);

	if (!$ans->error && isAlertActive($typeID))
	{
		$ans = oSQL()->registerLog('logs', $data);
	}

	if (!$ans->error)
	{
		return true;
	}

	// Error handling, with file logging when DB logging fails
	$msg = date('Y-m-d H:i:s').
		" - Error logging '{$typeID}' event, for object '{$objectID}': ".
		" ({$ans->error}) {$ans->errDesc}\r\n";

	$fh = @fopen(LOGS_PATH . '/loggingErrors.txt', 'a');
	$fh && (@fwrite($fh, $msg) & @fclose($fh));
}

function isAlertActive($id)
{
	return oSQL()->isAlertActive($id);
}

function sync($user='', $params=array())
{
	/* Check alerts */
	seekAlerts($params);

	/* Check reminders */
	seekReminders($params);

	return Response;
}

function seekAlerts($params=array())
{
	$user = getSes('user');

	if ($user)
	{
		$logsFrom = empty($params['from']) ? 0 : $params['from'];

		oAlerts()->browseLogs($logsFrom);
		oAlerts()->processLogs();

		$alerts = oAlerts()->getAlerts();

		addScript('sync.process('.toJson($alerts).');');
	}
}

function seekReminders($params=array())
{
	$reminders = oSQL()->seekReminders();

	# See which reminders are still active
	$keep = array();

	foreach ($reminders as $reminder)
	{
		# List reminders
		if (!isset($keep[$reminder['id_reminder']]))
		{
			$keep[$reminder['id_reminder']] = false;
		}

		# Inactive reminders (event already happened) are ignored and removed
		$active = strtotime($reminder['ini']) > time();

		# Add reminder (open event for current user)
		if ($active && $reminder['user'] == getSes('user'))
		{
			addScript("eventInfo('{$reminder['id_event']}');");
			$filter = array('id_reminder_user' => $reminder['id_reminder_user']);
			oSQL()->delete('reminders_users', $filter);
		}
		# Do not delete reminders that have other users left to remind
		elseif ($active)
		{
			$keep[$reminder['id_reminder']] = true;
		}
	}

	# Remove reminders that do not have more users to remind
	foreach ($keep as $id => $keepReminder)
	{
		if (!$keepReminder)
		{
			oSQL()->delete('reminders', array('id_reminder' => $id));
		}
	}
}


/******************************************************************************/
/********************************** A J A X ***********************************/
/******************************************************************************/

function say($msg, $type='', $img='')
{
	return Response::say($msg, $type, $img);
}

function addAlert($alert)
{
	return Response::alert($alert);
}

function addScript($js)
{
	return Response::js($js);
}

/**
 * dialog(string $content, string $element[, array $atts])
 *      Creates $element if it doesn't exist, make $content it's inner html, and
 * call jQuery-ui dialog() on it.
 *
 * @param string $content       Template name (ending on '.tpl') or html
 * @param string $element       Valid jQuery selector for an id (including #)
 * @param array $atts           List of properties to be passed to dialog()
 * @return XajaxResponse
 */
function dialog($content, $selector, $atts=array())
{
	// Send the html (fetch the template first, if $content's a template name)
	$isTemplate = preg_match('_\.tpl$_', $content);
	$html = $isTemplate ? Template::one()->fetch($content) : $content;

	jQuery($selector)->touch()->html($html)->dialog($atts);

	return addScript("$('.ui-widget-overlay').click(function(){
		\$('{$selector}').dialog('close');
	});");
}


/******************************************************************************/
/******************************** J Q U E R Y *********************************/
/******************************************************************************/

class jQuery
{

	private $selector;

	public function __construct($selector)
	{
		$this->selector = $selector;
	}

	public function __call($method, $arguments)
	{
		$selector = toJS($this->selector);
		$args = join(', ', array_map('toJS', $arguments));

		addScript("\$({$selector}).{$method}({$args})");

		return $this;
	}

}


function jQuery($selector='undefined')
{
	return new jQuery($selector);
}


/******************************************************************************/
/******************************* S E S S I O N ********************************/
/******************************************************************************/

function regSes($key, $val)
{
	$_SESSION['crm'][$key] = $val;
}

function getSes($key)
{
	return isset($_SESSION['crm'][$key]) ? $_SESSION['crm'][$key] : NULL;
}

function clearSes($key)
{
	regSes($key, NULL);
}

function loggedIn()
{
	// Keep session alive by cookies
	if (!getSes('user') && !empty($_COOKIE['crm_user']))
	{
		$user = substr($_COOKIE['crm_user'], 0, -40);
		$cookie = substr($_COOKIE['crm_user'], -40);

		$info = oSQL()->getUser($user);

		if ($info && ($info['cookie'] == $cookie))
		{
			acceptLogin($info);
			header('Refresh:0');
		}
	}

	return getSes('user');
}

function acceptLogin($info, $persist=true)
{
	$ip = $_SERVER['REMOTE_ADDR'];

	if ($persist)
	{
		$cookie = sha1(time() . rand(1, time()));
		$expire = time() + (3600*24*30);
		setcookie('crm_user', "{$info['user']}{$cookie}", $expire);
	}
	elseif (!in_array(substr($ip, 0, 3), array('192', '127')))
	{
		$fp = @fopen(LOGS_PATH . '/remoteAccess.txt', 'a');

		if ($fp)
		{
			$date = date('d/m/Y H:i:s');
			$log = "{$date}: Usuario {$info['user']} loguea desde {$ip}\n\n";
			@fwrite($fp, $log);
			@fclose($fp);
		}
	}

	oSQL()->saveLastAccess($info['user'], isset($cookie) ? $cookie : NULL);

	foreach ($info as $key => $val)
	{
		regSes($key, $val);
	}

	oSQL()->removeOldAlerts(getSes('user'), MAX_ALERTS_PER_USER);
	oSQL()->removeOldLogs(MAX_LOGS_GLOBAL);
}

function errorName($errno)
{
	$errors = ['E_ERROR', 'E_WARNING', 'E_PARSE', 'E_NOTICE', 'E_CORE_ERROR',
	           'E_CORE_WARNING', 'E_COMPILE_ERROR', 'E_COMPILE_WARNING',
	           'E_USER_ERROR', 'E_USER_WARNING', 'E_USER_NOTICE', 'E_STRICT',
	           'E_RECOVERABLE_ERROR', 'E_DEPRECATED', 'E_USER_DEPRECATED'];

	foreach ($errors as $err)
	{
		if (constant($err) === $errno)
		{
			return $err;
		}
	}

	return NULL;
}

function error_handler($no, $str, $file, $line)
{
	if (strstr($file,'temp/es^'))
	{
		$line = '0';

		$fp = fopen($file , 'r');
		$content = file($fp);
		$regex = "/\s*compiled from ([^\s]+) \*\/ \?\>\s+/";

		$fileName = preg_replace($regex, '$1', $content[1]);

		if (substr($str,0,18) == 'Undefined index:  ')
		{
			$file = TEMPLATES_PATH . "/{$fileName}";
			$smartyVar = substr($str,18);
			$str = 'Undeclared Smarty Variable \''.substr($str,18).'\'';
		}
		else
		{
			$file .= ", line {$line})<br />&nbsp;&nbsp;&nbsp;&nbsp;";
			$file .= TEMPLATES_PATH . "/{$fileName})";
		}
	}

	switch ($no)
	{
		case E_USER_ERROR:
			echo "<b>ERROR</b>: [{$no}] {$str}<br />\n";
			echo "  Fatal error on line {$line} in file {$file}";

			($line !== '0') && print(", line {$line}");

			echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";

			echo "Aborting...";
			exit(1);
			break;

		case E_USER_WARNING:
			$msg = "<b>WARNING</b>: [{$no}] {$str} in {$file}";
			($line != '0') && ($msg .= ", line {$line}");
			$msg .= ")";
			break;

		case E_USER_NOTICE:
			$msg = "<b>NOTICE</b>: {$str}";

			if (substr($str,0,3) != 'SQL')
			{
				$msg .= " in {$file}.";
				($line != '0') && ($msg .= ", line {$line}");
			}
			else
			{
				$msg .= '.';
			}
			break;

		default:
			$name = errorName($no);
			$msg = "{$name}: {$str} in {$file}";
			($line != '0') && ($msg .= ", line {$line}");
			break;
	}

	// Register error string with the Page
	$cnt = count(Template::one()->retrieve('errMsgs'));
	Template::one()->append('errMsgs', ($cnt >= 10) ? '.' : "<div>{$msg}</div>");

	return true;
}