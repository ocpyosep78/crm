<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	return array(
		'deleteProducts',
		'newMaterial',
		'editMaterial',
		'delMaterial',
	);
	
	function deleteProducts( $id ){
	
		# Handle security issues to the right function (security.php)
		if( !oPermits()->can('deleteProducts') ) return oPermits()->noAccessMsg();
		
		$inUse = oSQL()->isProductUsedInEstimates( $id );
		if( !empty($inUse) ){
			$msg = 'No es posible eliminar este producto porque est� en uso. '.
				'El producto es utilizado en uno o m�s presupuestos ('.join(', ', $inUse).')';
			return showStatus( $msg );
		}
		
		oSQL()->setOkMsg("El art�culo seleccionado fue eliminado correctamente.");
		oSQL()->setErrMsg("No se pudo eliminar el art�culo. Verifique sus permisos e int�ntelo nuevamente.");
			
		$ans = oSQL()->deleteProducts( $id );
		if( !$ans->error ){
			if( is_file($path="app/images/products/{$id}.jpg") ) @unlink( $path );
			return oNav()->reloadPage($ans->msg, 1);
		}
		else return showStatus( $ans->msg );
	
	}

?>