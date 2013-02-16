<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	return array(
		'saveEstimate',
		'deleteEstimates',
		'suggestProduct',
		'printQuote',
		'addEntryToPlan',
		'removeEntryFromPlan',
		'createEstimates_pack',
		'addEstimate2Pack',
	);

	function saveEstimate($data, $products, $id=NULL){
		
		foreach( $data as &$v ) if( empty($v) ) $v = NULL;
		
		# Validate input
		if( ($ans=oValidate()->test($data, 'estimates')) !== true ){
			return showStatus('Los datos ingresados no son válidos.');
		}
		
		foreach( $products as &$product ){
			$product = array(
				'id_product'	=> $product['id'],
				'price'			=> $product['price'],
				'amount'		=> $product['amount'],
			);
			unset( $product['product'] );
		}
		
		# Pass the right message of success to SQL object
		$msg = empty($data['orderNumber'])
			? "La cotización '{$data['estimate']}' fue guardada correctamente."
			: "El presupuesto '{$data['estimate']}' (orden #{$data['orderNumber']}) fue guardado correctamente.";
		oSQL()->setOkMsg( $msg );
		
		# If ID is given we're updating, so we need to delete old data and insert the new one
		$ans = empty( $id )
			? oSQL()->createEstimates($data, $products)
			: oSQL()->updateEstimates($data, $products, $id);
		
		if( !$ans->error && ($id || $ans->ID) ){		/* Answer is new estimate's ID */
			return oNav()->getPage('estimatesInfo', array($id ? $id : $ans->ID), $ans->msg, 1);
		}
		else return showStatus($ans->msg, $ans->successCode);
	
	}
	
	function deleteEstimates($id, $isQuote=false){
		
		# Request query and catch answer, then return it to the user
		oSQL()->setOkMsg("El presupuesto fue eliminado correctamente.");
		$ans = oSQL()->deleteEstimates( $id );
		
		if( $ans->error ) return showStatus( $ans->msg );
		else return oNav()->getPage($isQuote ? 'quotes' : 'estimates', array(), $ans->msg, 1);
		
	}
	
	function suggestProduct($txt, $reqID){
		
		$all = oSQL()->getProductSuggest(mysql_real_escape_string($txt));
		
		$list = array();
		foreach( $all as $product ) $list[] = toJson($product);
		
		return addScript("Suggest.processList([".join(',', $list)."], '{$reqID}');");
		
	}
	
	function printQuote( $id ){
	
		# Generate all info as in estimatesInfo page
		page_estimatesInfo( $id );
		
		oSmarty()->assign('miniHeader', true);
		$page = oSmarty()->fetch('estimates/estimatesInfo.tpl');
		
		addAssign('tmpDivToPrint', 'innerHTML', $page);
		
		return addScript("printQuote();");
		
	}
	
	function addEntryToPlan( $data ){
	
		$maxAmount = (int)oSQL()->getRemainingProducts($data['id_estimate'], $data['id_product']);
		
		# Make sure we have enough of this product defined in this estimate
		if( !$maxAmount || $data['amount'] > $maxAmount ){
			if( $maxAmount < 1 ) return showStatus('No hay más elementos disponibles de este tipo.');
			else{
				$s = ($maxAmount == 1) ? '' : 's';			/* Plurales */
				$n = ($maxAmount == 1) ? '' : 'n';			/* Plurales */
				return showStatus("Queda{$n} disponible{$s} solamente {$maxAmount} producto{$s} de este tipo.");
			}
		}
		
		# Make sure at least one product was selected
		if( empty($data['amount']) ) return showStatus("Debe seleccionar una cantidad mayor o igual que uno.");
		
		# Make sure a description was given
		if( empty($data['position']) ){
			return showStatus('Debe ingresar una descripción para esta entrada del Plan de Obras.');
		}
		
		# Attempt to save the new entry
		$ans = oSQL()->insert($data, 'estimates_plan');
		
		# If no error occurred, reload page_installPlan() (adding the id of the last inserted product)
		return !$ans->error
			? oNav()->reloadPageWithAtts(array($data['id_estimate'], $data['id_product']))
			: showStatus('No se pudo guardar la entrada. Verifique sus datos e inténtelo nuevamente.');
		
	}
	
	function removeEntryFromPlan( $id ){
	
		$ans = oSQL()->removeEntryFromPlan( $id );
		
		if( !$ans->error ) return oNav()->reloadPage();
		else return oXajaxResp();
		
	}
	
	function createEstimates_pack( $data ){
	
		$data['created'] = date('Y-m-d H:i:s');
	
		$ans = oSQL()->insert($data, 'estimates_pack');
		
		return $ans->error
			? showStatus('Ocurrió un error al intentar crear este elemento.')
			: oNav()->getPage('estimates_packInfo', (array)$ans->ID);
	
	}
	
	function addEstimate2Pack( $data ){
	
		$ans = oSQL()->update($data, 'estimates', array('id_estimate'));
		
		return $ans->error
			? showStatus('Ocurrió un error al intentar agregar el presupuesto.')
			: oNav()->reloadPage();
	
	}