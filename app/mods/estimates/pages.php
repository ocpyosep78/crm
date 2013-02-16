<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */

	function page_createEstimates_pack( $modifier=NULL ){
	
		oSmarty()->assign('customers', oLists()->customers());
		
	}

	function page_estimates_pack( $modifier=NULL ){
	
		return oSnippet()->addSnippet('commonList', 'estimates_pack', $modifier);
	
//		return oSnippet()->addSnippet('complexList', 'corpEstimates');
		
		
		
	}

	function page_editEstimates_pack( $id ){
	
		page_estimates_packInfo( $id );
		
		return oNav()->updateContent('estimates/estimates_packInfo.tpl');
		
	}

	function page_estimates_packInfo( $id ){
	
		oSmarty()->assign('id', $id);
		
		oNav()->setJSParams( $id );
		
		$info = oSnippet()->getSnippet('viewItem', 'estimates_pack', array('filters' => $id));
		oSmarty()->assign('info', $info);
		
		$data = oSQL()->getEstimatesDetail( array('pack' => array($id, '=')) );
		foreach( $data as &$v ){
			$v['utility'] = (float)$v['cost']
				? number_format(($v['price']/$v['cost'])*100-100, 2)
				: '--';
		}
		oSmarty()->assign('data', $data);
		
		$left = oSQL()->estimatesLeft( $id );
		oSmarty()->assign('left', $left);
	
	}



/** Serves to generate both estimates and quotes lists
 * depending on $modifier
 */
	function page_estimates( $modifier='estimates' ){
	
		return oLists()->printList('estimates', $modifier);
		
	}

	function page_quotes(){
	
		return oLists()->printList('estimates', 'quotes');
		
	}

/**
 * Reused for createEstimates and editEstimates pages depending on $id parameter
 */

	function page_createEstimates($estimate=NULL, $packID=NULL){
	
		if( empty($estimate) ) $estimate = NULL;
		
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
		
		# Get pack info if estimate belongs to a pack
		if( empty($packID) && $estimate['pack'] ) $packID = $estimate['pack'];
		$pack = $packID ? oSQL()->select('estimates_pack', '*', array('id_estimates_pack' => $packID), 'row') : NULL;
		oSmarty()->assign('pack', $pack);
		
		# Initialize estimate keys in case it comes empty
		if( empty($estimate) ) $estimate = array(
			'orderNumber'	=> '',
			'id_estimate'	=> '',
			'estimate'		=> '',
			'id_customer'	=> '',
			'customer'		=> '',
			'id_system'		=> '',
			'system'		=> '',
			'pack'			=> $packID ? $packID : '',
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
		
		# Get pack info if estimate belongs to a pack
		$packID = $estimate['pack'];
		$pack = $packID ? oSQL()->select('estimates_pack', '*', array('id_estimates_pack' => $packID), 'row') : NULL;
		oSmarty()->assign('pack', $pack);
		
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