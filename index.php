<?php

// Debugging function
function db($var, $die=true)
{
	headers_sent() || header('Content-Type: application/json');
	$var ? print_r($var) : var_dump($var);
	echo "\n";
	$die && die();
}

require_once 'migrate/migrate.php';

/**
 * When migration is over, this file should just include public/index.php
 */