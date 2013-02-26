<?php

$migratehome = true;
$migratelogin = true;

$migrated = [
//	'home',
//	'agenda',
//	'editEvent',
//	'users'
];



error_reporting(E_ERROR);
session_start();


/**
 * GENERAL CASES
 */

if (empty($_SESSION['crm']['user']))                 // Login page
{
	migrate($migratelogin);
}
elseif (empty($_GET))                                // Home page set by default
{
	migrate($migratehome);
}


/**
 * PAGE / CONTENT LOADS
 */

// Old regular page load
elseif (empty($_POST['xajax']) && !empty($_GET['nav']))
{
	($atts = $_SESSION['nav'][$_GET['nav']]['atts']) || ($atts = []);
	($oldp = $_SESSION['nav'][$_GET['nav']]['page']) || ($oldp = '');

	migrate(!$oldp ? $migratehome : in_array($oldp, $migrated));
}

// Old ajax call for content
elseif (isset($_POST['xajax']) && ($_POST['xajax'] === 'getPage'))
{
	$args = $_POST['xajaxargs'];
	$oldp = array_shift($args);
	$atts = $args ? xjxArgs2Array(array_shift($args)) : [];

	migrate(!$oldp ? false : in_array($oldp, $migrated));
}

// Old ajax calls, except content
elseif (!empty($_POST['xajax']))
{
	migrate(false);
}

// New regular page load
elseif (!empty($_GET['args']))
{
	$atts = explode('/', $_GET['args']);
	$newp = translatePage(array_shift($atts));

	migrate(!$newp ? $migratehome : in_array($newp, $migrated));
}

// New ajax call for content
elseif (isset($_GET['ajax']) && isset($_POST['id']) && ($_POST['id'] === 'content'))
{
	$args = readAjaxArgs();
	$newp = translatePage(array_shift($args)['page']);
	$atts = (array)array_shift($args);

	migrate(!$newp ? $migratehome : in_array($newp, $migrated));
}

// New ajax calls, except content
elseif (isset($_GET['ajax']) && !empty($_POST['id']))
{
	migrate(true);
}

// Whatta...
else
{
	migrate(false);
}

// When changing from new to old or viceversa, we need to force a page redirect
if (isset($newp) || isset($oldp))
{
	// Base url to the application
	$bburl = dirname(strtolower(current(explode('/', $_SERVER['SERVER_PROTOCOL'])))
		   . "://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");

	// Change from new to old (need to come up with a nav code, for example)
	if (isset($newp) && !$doMigrate)
	{
		$navcode = (string)time().microtime(true);
		$_SESSION['nav'][$navcode] = ['page' => $newp, 'atts' => $atts];
		$href = "{$bburl}/index.php?nav={$navcode}";

		// Was it an ajax call ...
		if ($_POST['id'] === 'content')
		{
			die(json_encode(["location.href = '{$href}'"]));
		}
		// ... or was it a regular page load?
		else
		{
			header("Location: {$href}");
		}
	}

	// Change from old to new
	elseif (isset($oldp) && $doMigrate)
	{
		$newPageUri = newPageFromOld($oldp);
		$href = trim("{$bburl}/{$newPageUri}/" . join('/', $atts), '/');

		// Was it an ajax call ...
		if (!empty($_POST['xajax']))
		{
			header('Content-Type: text/xml');
			die('<?xml version="1.0" encoding="utf-8" ?><xjx><cmd n="js">' .
			    "<![CDATA[location.href = '{$href}';]]></cmd></xjx>");
		}
		// ... or was it a regular page load?
		else
		{
			header("Location: {$href}");
		}
	}

}



function migrate($really=true)
{
	$GLOBALS['doMigrate'] = $really;
}



if ($doMigrate)
{
	require_once dirname(__FILE__) . '/../public/index.php';
}
else
{
	isset($_GET['chatCheck']) && die();

	require_once dirname(__FILE__) . '/../app/index.php';
}

exit();



// Interpret new args
function readAjaxArgs()
{
	$convert = function(&$args) use (&$convert)
	{
		if (!is_scalar($args))
		{
			is_array($args) || ($args = (array)$args);

			foreach ($args as &$arg)
			{
				$convert($arg);
			}
		}
	};

	$args = empty($_POST['args']) ? [] : json_decode($_POST['args']);
	$convert($args);

	return $args;
}


// Interpret old args
function xjxArgs2Array($sXml)
{
	$aArray = array();
	$sXml = str_replace("<$rootTag>","<$rootTag>|~|",$sXml);
	$sXml = str_replace("</$rootTag>","</$rootTag>|~|",$sXml);
	$sXml = str_replace("<e>","<e>|~|",$sXml);
	$sXml = str_replace("</e>","</e>|~|",$sXml);
	$sXml = str_replace("<k>","<k>|~|",$sXml);
	$sXml = str_replace("</k>","|~|</k>|~|",$sXml);
	$sXml = str_replace("<v>","<v>|~|",$sXml);
	$sXml = str_replace("</v>","|~|</v>|~|",$sXml);
	$sXml = str_replace("<q>","<q>|~|",$sXml);
	$sXml = str_replace("</q>","|~|</q>|~|",$sXml);

	$aObjArray = explode("|~|",$sXml);

	return _parseObjXml($aObjArray, 0);
}

function _parseObjXml($sXml, $iPos)
{
	while(!stristr($aObjArray[$iPos],"</xjxobj>"))
	{
		$iPos++;

		if(stristr($aObjArray[$iPos],"<e>"))
		{
			$key = "";
			$value = null;

			$iPos++;
			while(!stristr($aObjArray[$iPos],"</e>"))
			{
				if(stristr($aObjArray[$iPos],"<k>"))
				{
					$iPos++;
					while(!stristr($aObjArray[$iPos],"</k>"))
					{
						$key .= $aObjArray[$iPos];
						$iPos++;
					}
				}
				if(stristr($aObjArray[$iPos],"<v>"))
				{
					$iPos++;
					while(!stristr($aObjArray[$iPos],"</v>"))
					{
						if(stristr($aObjArray[$iPos],"<xjxobj>"))
						{
							$value = _parseObjXml($aObjArray, $iPos);
							$iPos++;
						}
						else
						{
							$value .= $aObjArray[$iPos];
						}
						$iPos++;
					}
				}
				$iPos++;
			}

			$aArray[$key] = $value;
		}
	}

	return $aArray;
}

function translatePage($input)
{
	if (is_numeric($input))
	{
		$sql = "SELECT `oldpage`
		        FROM `crm_tree`
		        WHERE `id` = '{$input}'";
		$res = query($sql);

		return $res ? $res[0]['oldpage'] : '';
	}

	list($m, $p) = (explode(':', $input) + ['', 'main']);

	$sql = "SELECT `oldpage`
	        FROM `crm_tree`
	        WHERE `model` = '{$m}'
	        AND `page` = {$p}";
	$res = query($sql);

	if ($res)
	{
		return $res[0]['oldpage'];
	}

	$sql = "SELECT `alias`,
	               `oldpage`
	        FROM `crm_tree`";

	foreach (query($sql) as $page)
	{
		$aliases = explode('|', $page['alias']);

		if (!strcasecmp(uri(end($aliases)), $input))
		{
			return $page['oldpage'];
		}
	}

	return '';
}

function newPageFromOld($oldp)
{
	$sql = "SELECT `alias`
	        FROM `crm_tree`
	        WHERE `oldpage` = '{$oldp}'";
	$res = query($sql);

	if ($res)
	{
		$aliases = explode('|', $res[0]['alias']);
		return uri(end($aliases));
	}
	else
	{
		return '';
	}
	return $res ? uri() : '';
}

function uri($str)
{
	return strtolower(preg_replace('_[^\wáéíóúÁÉÍÓÚ]_', '_', $str));
}

function query($sql)
{
	static $conn;

	if (is_null($conn))
	{
		$conn = @mysql_connect('localhost', 'crm', 'it707', true)
			or die('Unable to connect to database.');

		@mysql_select_db('crm', $conn)
			or die('Unable to open database.');

		@mysql_set_charset('utf8', $conn);
	}

	$res = @mysql_query($sql, $conn);

	while ($row = @mysql_fetch_assoc($res))
	{
		$data[] = $row;
	}

	return isset($data) ? $data : [];
}