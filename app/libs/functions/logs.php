<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	function saveLog($typeID, $objectID, $extra='', $user=NULL){
	
		# Decide whether to save logs in `logs` table or `history` table
		$data = array(
			'logType'	=> $typeID,
			'objectID'	=> $objectID,
			'user'		=> $user ? $user : getSes('user'),
			'extra'		=> $extra,
		);
		
		$ans = oSQL()->registerLog('logs_history', $data);
		if( !$ans->error && isAlertActive($typeID) ) $ans = oSQL()->registerLog('logs', $data);
		if( !$ans->error ) return true;
		
		# Error handling, with file logging when DB logging fails
		$msg = date('Y-m-d H:i:s').
			" - Error logging '{$typeID}' event, for object '{$objectID}': ".
			" ({$ans->error}) {$ans->errDesc}\r\n";
		if( ($fh=@fopen('logs/loggingErrors.txt', 'a')) && @fwrite($fh, $msg) ) fclose($fh);
		
	}
	
	function isAlertActive( $id ){
		return oSQL()->isAlertActive( $id );
	}

?>