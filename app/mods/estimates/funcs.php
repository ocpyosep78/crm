<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	function getEstimate( $id=NULL ){
		
		if( $id ){
			$estimate = oSQL()->getEstimate( $id );
			if( $estimate ) $estimate = $estimate + array('detail' => oSQL()->getEstimateDetail($id));
		}
		
		return (isset($estimate) && count($estimate)) ? $estimate : array();
		
	}
	
	/**
	 *	Returns data array with following keys:
	 *		['customers']		Customers array with full info
	 *		['products']		Products array with full info
	 *		['systems']			Systems hash, id_system => system
	 *		['customersList']		Customers hash, id_customer => customer, sorted by customer
	 *		['arrProducts']		Products array with full info, grouped (indexed) by id_system
	 *		['pUnitPrices']		Hash, id_product => price
	 *		['mUnitPrices']		Hash, id_material => price
	 */
	function estimates_commonData(){
		
		$customers = oSQL()->getCustomers();
		$products = oLists()->products();
		$systems = oLists()->systems();
		
		$customersList = array();
		$arrProducts = array();
		$prices = array();
		
		foreach( $customers as $customer ) $customersList[$customer['id_customer']] = $customer['customer'];
		foreach( $systems as $key => $system ) $arrProducts[$key] = array();
		foreach( $products as $product ){
			$arrProducts[$product['id_system']][$product['id_product']] = $product;
			$pPrices[$product['id_product']] = $product['price'];
		}
		
		return array(
			'customers'		=> $customers,
			'products'		=> $products,
			'systems'		=> $systems,
			'customersList'	=> $customersList,
			'arrProducts'	=> $arrProducts,
		);
		
	}
	
	function isQuote( $x ){
		# Returns whether an estimate qualifies as quote (lacking required data)
		return empty($x['orderNumber']) || empty($x['id_customer']) || empty($x['id_system']);
		
	}