<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	if( !DEVELOPPER_MODE && getSes('id_profile') != 1 ) die();

	ob_start();
	
	oSmarty()->display('_debug/statsHead.tpl');
	
	echo "<h3 style='width:200px;'>QUEUED MSG</h3>";
	print_r( $_SESSION['queuedMsg'] );
	
	echo "<h3 style='width:200px;'>SESSION</h3>";
	print_r( $_SESSION['crm'] );
	
	echo "<h3>NAV</h3>";
	print_r( $_SESSION['nav'] );
	
	echo "<h3>PERMITS</h3>";
	print_r( $_SESSION['Permits'] );
	
	oSmarty()->display('_debug/statsFoot.tpl');
	
	ob_flush();
	
	die();