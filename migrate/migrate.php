<?php

error_reporting(E_ERROR);

$migrated = ['home',
             'agenda',
//             'editEvent',
             'users'];

define('OLD_VERSION', true, true);

session_start();

// Login page
if (empty($_SESSION['crm']['user']))
{
	migrate();
}
// Home page (set by default)
elseif (empty($_GET))
{
	migrate();
}
// Old regular page load
elseif (empty($_POST['xajax']) && !empty($_GET['nav']))
{
	($atts = $_SESSION['nav'][$_GET['nav']]['atts']) || ($atts = []);
	($page = $_SESSION['nav'][$_GET['nav']]['page']) || ($page = 'home');
	$isNew = false;
}
// Old ajax call for content
elseif ($_POST['xajax'] === 'getPage')
{
	$args = $_POST['xajaxargs'];
	$page = array_shift($args);
	$atts = $args ? xjxArgs2Array(array_shift($args)) : [];
	$isNew = false;
}
// New regular page load
elseif (!empty($_GET['args']))
{
	$atts = explode('/', $_GET['args']);
	$page = array_shift($atts);
	$isNew = true;
}
// New ajax call for content
elseif ($_POST['id'] === 'content')
{
	$args = readAjaxArgs();
	$page = array_shift($args)['page'];
	$atts = (array)array_shift($args);
	$isNew = true;
}
// Old ajax calls, except content
elseif ($_POST['xajax'])
{
	// Don't migrate yet
	$isNew = false;
}
// New ajax calls, except content
elseif ($_POST['id'])
{
	migrate();
	$isNew = true;
}
else
{
	db('wtf');
}


if (isset($page))
{
	$bburl = dirname(strtolower(current(explode('/', $_SERVER['SERVER_PROTOCOL'])))
		   . "://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");

	$tryNew = in_array($page, $migrated);

	// Change from new to old
	if ($isNew && !$tryNew)
	{
		$navcode = (string)time().microtime(true);

		$_SESSION['nav'][$navcode] = compact('page', 'atts');

		$href = "{$bburl}/index.php?nav={$navcode}";

		if ($_POST['id'] === 'content')
		{
			die(json_encode(["location.href = '{$href}'"]));
		}
		else
		{
			header("Location: {$href}");
		}
	}
	elseif ($isNew)
	{
		migrate();
	}
	// Change from old to new
	elseif ($tryNew)
	{
		require_once 'public/Sugar.php';
		$page = Sugar::page($page);

		$href = trim("{$bburl}/{$page}/" . join('/', $atts), '/');

		if ($_POST['xajax'] === 'getPage')
		{
			header('Content-Type: text/xml');
			die('<?xml version="1.0" encoding="utf-8" ?><xjx><cmd n="js">' .
			    "<![CDATA[location.href = '{$href}';]]></cmd></xjx>");
		}
		else
		{
			header("Location: {$href}");
		}
	}

}




if (OLD_VERSION)
{
	isset($_GET['chatCheck']) && die();

	require_once dirname(__FILE__) . '/../app/index.php';
}
else
{
	require_once dirname(__FILE__) . '/../public/index.php';
}









function migrate($old=false)
{
	define('OLD_VERSION', $old);
}

function xjxArgs2Array($sXml)
{
	$aArray = array();

	$sXml = str_replace("<xjxobj>","<xjxobj>|~|",$sXml);
	$sXml = str_replace("</xjxobj>","</xjxobj>|~|",$sXml);
	$sXml = str_replace("<e>","<e>|~|",$sXml);
	$sXml = str_replace("</e>","</e>|~|",$sXml);
	$sXml = str_replace("<k>","<k>|~|",$sXml);
	$sXml = str_replace("</k>","|~|</k>|~|",$sXml);
	$sXml = str_replace("<v>","<v>|~|",$sXml);
	$sXml = str_replace("</v>","|~|</v>|~|",$sXml);
	$sXml = str_replace("<q>","<q>|~|",$sXml);
	$sXml = str_replace("</q>","|~|</q>|~|",$sXml);

	$aObjArray = explode("|~|",$sXml);

	$iPos = 0;

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
							$value = self::_parseObjXml("xjxobj");
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