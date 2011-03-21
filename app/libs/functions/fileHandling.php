<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	function getDirFiles( $path, $match=NULL ){
					
		$files = array();
		
		if( is_dir($path) && ($rh=dir($path)) && $rh != '.svn' ){
		
			while( $res=$rh->read() ){
				if($res == '.' || $res == '..') continue;
				if( is_dir($dir=$path.'/'.$res) ){
					$files = array_merge($files, getDirFiles($dir, $match) );
				}elseif(!$match || preg_match($match, $res)){
					$files[] = "{$path}/{$res}";
				}
			}
			
			$rh->close();
			
		}
		
		return $files;
		
	}
	
	function uploadAnalylize($file, $noFileReturn=NULL){
		
		switch( $file['error'] ){
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

?>