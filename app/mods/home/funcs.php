<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */
	
	function getActivity( $type ){
	
		require_once(CLASSES_PATH.'/Activity/Activity.class.php');
		$activity = new Activity;
		
		oSmarty()->assign('events', $activity->events($type));
		oSmarty()->assign('notes', $activity->notes($type));
		
	}
	
	function openLogs( $file ){
		
		$path = "logs/{$file}.txt";
		
		if( !is_file($path) ) return '(vacío)';
		
		$fp = @fopen($path, 'rb');
		if( !$fp ) return 'Error al abrir el archivo';
		
		$log = '';
		while( $data=fgets($fp) ) $log .= nl2br($data);
		
		return $log ? $log : '(vacío)';
		
	}