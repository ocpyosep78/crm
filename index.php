<?php

// Debugging function
function db($var=NULL, $die=true)
{
	headers_sent() || header('Content-Type: application/json');
	func_num_args() || ($var = debug_backtrace());
	$var ? print_r($var) : var_dump($var);
	echo "\n";
	$die && die();
}

require_once 'migrate/migrate.php';

/**
 * When migration is over, this file should just include public/index.php
 */