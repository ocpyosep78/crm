<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

	function page_corpEstimates(){
	
		return oSnippet()->addSnippet('complexList', 'corpEstimates');
		
	}



/* Serves to generate both estimates and quotes lists
 * depending on $onlyQuotes parameter
**/
	function page_estimates( $modifier='estimates' ){
	
		return oLists()->printList('estimates', $modifier);
		
	}

	function page_quotes(){
	
		return oLists()->printList('estimates', 'quotes');
		
	}

/**
 * Reused for createEstimates and editEstimates pages depending on $id parameter
 */

	function page_createEstimates( $estimate=NULL ){
		
		$id = empty($estimate['id_estimate']) ? NULL : $estimate['id_estimate'];
		$isNew = !$id;
		$isQuote = isQuote($estimate);
		$modifier = $isQuote ? 'quotes' : 'estimates';
	
		oSmarty()->assign('edit', true);
		oSmarty()->assign('isNew', $isNew);
		oSmarty()->assign('isQuote', $isQuote);
		
		$type = $isNew ? 'Nombre' : ($isQuote ? 'Cotización' : 'Presupuesto');
		oSmarty()->assign('estimateType', $type);
		
		$common = estimates_commonData();
		oSmarty()->assign('data', $common);
		
		$system = 1;
		if( $estimate ) foreach( $estimate['detail'] as $row ){
			if( $row['id_system'] ) $system = $row['id_system'];
		}
		oSmarty()->assign('system', $estimate['id_system'] ? $estimate['id_system'] : $system);
		
		# Initialize estimate keys in case it comes empty
		if( empty($estimate) ) $estimate = array(
			'orderNumber'	=> '',
			'id_estimate'	=> '',
			'estimate'		=> '',
			'id_customer'	=> '',
			'customer'		=> '',
			'id_system'		=> '',
			'system'		=> '',
		);
		oSmarty()->assign('estimate', $estimate);
		
		addScript('window.taxes = 0.22;');
		
		# Include a comboList
		oLists()->includeComboList('estimates', $modifier, $id);
		
		/* Return content so it doesn't fail when called as page_editEstimates */
		return oNav()->updateContent( 'estimates/createEstimates.tpl' );
		
	}

	function page_estimatesInfo($id, $estimate=NULL){	/* $estimate's for prefetched estimate info */
		
		/* Estimate could come prefetched (in the case of on-the-fly estimatesInfo) */
		if( !$estimate ) $estimate = getEstimate( $id );
		
		$isQuote = isQuote( $estimate );
		$modifier = $isQuote ? 'quotes' : 'estimates';
		
		oSmarty()->assign('miniHeader', false);
		oSmarty()->assign('edit', false);
		oSmarty()->assign('isQuote', $isQuote);
		oSmarty()->assign('estimateType', $isQuote ? 'Cotización' : 'Presupuesto');
		
		if( empty($estimate) ){		/* Something's wrong, this array should have all info */
			return oNav()->getPage('estimates', array(), 'No se encontró el presupuesto pedido.');
		}
		
		$system = 1;
		$taxes = 0.22;
		$totals = array('subTotal' => 0, 'tax' => 0, 'total' => 0);
		foreach( $estimate['detail'] as $key => $item ){
			$estimate['detail'][$key]['subTotal'] = $item['amount'] * $item['price'];
			$estimate['detail'][$key]['tax'] = $item['amount'] * $item['price'] * $taxes;
			$estimate['detail'][$key]['total'] = $item['amount'] * $item['price'] * ($taxes + 1);
			$totals['subTotal'] += $estimate['detail'][$key]['subTotal'];
			$totals['tax'] += $estimate['detail'][$key]['tax'];
			$totals['total'] += $estimate['detail'][$key]['total'];
			if( $item['id_system'] ) $system = $item['id_system'];
		}
		oSmarty()->assign('system', $estimate['id_system'] ? $estimate['id_system'] : $system);
		
		oSmarty()->assign('data', $estimate);
		oSmarty()->assign('totals', $totals);
		
		# Include a comboList
		oLists()->includeComboList('estimates', $modifier, $id);
	
	}

	function page_editEstimates( $id ){
		
		$estimate = getEstimate( $id );
		
		if( empty($estimate) ){		/* Something's wrong, this array should have all info */
			return oNav()->getPage('estimates', array(), 'No se encontró el presupuesto pedido.');
		}
		
		addScript('window.estimateDetail = '.toJson($estimate['detail']));
	
		return page_createEstimates( $estimate );
	
	}
	
	/**
	 * Design work plan for installing presented estimate
	 */
	function page_installPlan($id, $product=NULL){
		
		oNav()->setJSParams( $id );
	
		oSmarty()->assign('id_estimate', $id);
		
		oSmarty()->assign('data', oSQL()->getInstallPlan($id));
		
		oSmarty()->assign('products', oSQL()->estimateProducts($id));
		oSmarty()->assign('product', $product);

	}
	
	/**
	 * Creates a PDF for an estimate (shown within frame)
	 * First screen shows the already configured final steps, and those pending,
	 * including workPlan (installPlan), pictures, OS (if server included), etc.
	 */
	function page_estimatePDF($id, $straightToPDF=false){
		
		oNav()->setJSParams( $id );
	
		oSmarty()->assign('path', EXPORT_PDF_PATH."estimate.php?id={$id}");
		
	}
	
?>