<?php

define('OLD_VERSION', true, true);


if (OLD_VERSION)
{
	isset($_GET['chatCheck']) && die();

	require_once dirname(__FILE__) . '/../app/index.php';
}
else
{
	require_once dirname(__FILE__) . '/../public/index.php';
}