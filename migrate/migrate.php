<?php

$migrated = ['home', 'agenda'];

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
elseif (!empty($_GET['nav']))
{
	($page = $_SESSION['nav'][$_GET['nav']]['page']) || ($page = 'home');
	($atts = $_SESSION['nav'][$_GET['nav']]['atts']) || ($atts = []);
	$args = array_merge([$page], $atts);
}
// New regular page load
elseif (!empty($_GET['args']))
{
	$args = explode('/', $_GET['args']);
	$page = $args[0];
}
// Old ajax call for content
elseif ($_POST['xajax'] === 'getPage')
{
	$args = $_POST['xajaxargs'];
	$page = $args[0];
}
// New ajax call for content
elseif ($_POST['id'] === 'content')
{
	$args = $_POST['args'];
	$page = $args[0];
}
// Old ajax calls, except content
elseif ($_POST['xajax'])
{
	// Don't migrate yet
}
// New ajax calls, except content
elseif ($_POST['id'])
{
	migrate();
}
else
{
	ñ('wtf');
}

if (isset($page))
{
	if (in_array($page, $migrated))
	{
		$_POST = ['id' => 'content', 'args' => $args];
		migrate();
	}
	elseif (($_POST['id'] === 'content') || !empty($_GET['args']))
	{
		$code = time().microtime();
		$_SESSION['nav'][$code]['page'] = $page;
		$_SESSION['nav'][$code]['atts'] = array_slice($args, 1);

		$bburl = dirname(strtolower(current(explode('/', $_SERVER['SERVER_PROTOCOL'])))
		       . "://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
		$href = "{$bburl}/index.php?nav={$code}";

		if ($_POST['id'] === 'content')
		{
			die(json_encode(["location.href = '{$href}'"]));
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









function ñ($var, $die=true)
{
	headers_sent() || header('Content-Type: application/json');
	$var ? print_r($var) : var_dump($var);
	echo "\n";
	$die && die();
}

function migrate($old=false)
{
	define('OLD_VERSION', $old);
}