<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	
	function page_customers( $modifier='customers' ){	/* Status: 'customers', 'potential', 'all' */
	
		return oLists()->printList('customers', $modifier);
		
	}
	
	function page_potentialCustomers(){	/* Status: 'customers', 'potential', 'all' */
		
		return page_customers( 'potential' );
		
	}
	
	function page_customersInfo( $id ){
		
		$cust = oSQL()->getCustomer( $id );
		if( empty($cust) ) return oNav()->getPage('customers', array(), 'Cliente no encontrado.');
		
		# Build seller's full name
		$cust['seller'] = "{$cust['seller_name']} {$cust['seller_lastName']}";
		
		# Block Datos Básicos
		oFormTable()->clear();
		oFormTable()->emptyTxt = "(no ingresado)";
		oFormTable()->addRow('Nº Cliente', $cust['number']);
		oFormTable()->addRow('Nombre Comercial', $cust['customer']);
		oFormTable()->addRow('Razón Social', $cust['legal_name']);
		oFormTable()->addRow('R.U.T.', $cust['rut']);
		oFormTable()->addRow('Ingresado', $cust['since']);
		$blocks[] = oFormTable()->getTemplate();
		
		# Block Datos Básicos
		oFormTable()->clear();
		oFormTable()->emptyTxt = "(no ingresado)";
		oFormTable()->addRow('Teléfono', $cust['phone']);
		oFormTable()->addRow('Email', $cust['email']);
		oFormTable()->addRow('Dirección', $cust['address']);
		oFormTable()->addRow('Ciudad', $cust['location']);
		oFormTable()->addRow('Vendedor', $cust['seller']);
		$blocks[] = oFormTable()->getTemplate();
		
		oSmarty()->assign('custID', $id);
		oSmarty()->assign('blocks', $blocks);
		
		oLists()->includeComboList('customers', $cust['since'] ? 'customers' : 'potential', $id);
	
		return oTabs()->start();
		
	}

	function page_editCustomers( $id=NULL ){
		
		$cust = $id ? oSQL()->getCustomer( $id ) : array();
		if( $id && empty($cust) ) return oNav()->getPage('customers', array(), 'Cliente no encontrado.');
		
		oFormTable()->clear();
		oFormTable()->addFormAtt('id', 'createCustomerForm');
		oFormTable()->setPrefix( $id ? 'editCust_' : 'newCust_' );
		
		if( $id ){
			oFormTable()->hiddenRow();
			oFormTable()->addInput('', array('id' => 'id_customer'), 'hidden');
		}
		
		# Block 'Datos de la Empresa'
		oFormTable()->addTitle( 'Datos de la empresa' );
		oFormTable()->addInput('Nombre Comercial', array('id' => 'customer'));
		oFormTable()->addInput('Razón Social', array('id' => 'legal_name'));
		oFormTable()->addInput('R.U.T.', array('id' => 'rut'));

		# Block 'Interno'
		oFormTable()->addTitle( 'Interno' );
		oFormTable()->addInput('Número de Cliente', array('id' => 'number'));
		oFormTable()->addCombo('Vendedor',
			array('' => '(ninguno)') + oLists()->sellers(),
			array('id' => 'seller', 'selected' => $id ? $cust['seller'] : '' ));

		# Block 'Información'
		oFormTable()->addTitle( 'Información' );
		oFormTable()->addInput('Teléfono', array('id' => 'phone'));
		oFormTable()->addInput('Email', array('id' => 'email'));
		oFormTable()->addInput('Dirección', array('id' => 'address'));
		oFormTable()->addCombo('Ciudad',
			array('' => '') + oSQL()->getLocations(),
			array('id' => 'id_location', 'selected' => $id ? $cust['id_location'] : 'Montevideo'));
		
		# Disabled, an input telling PHP to save it as potential customer
		# (sent only if that option is selected)
		oFormTable()->hiddenRow();
		oFormTable()->addInput('', array('name' => 'potential', 'disabled' => 'disabled'));
		
		# Submit line (depends on status and availability of options to change status)
		$submit = '';
		if( !$id ){												/* New customer */
			$submit .= "<input type='submit' class='button' value='Guardar' />";
			$submit .= "<input type='button' class='button freeWidth'".
				"value='Guardar como posible cliente' id='potentialSubmit' />";
		}
		elseif( !empty($cust['since']) ){							/* Confirmed customer */
			$submit .= "<input type='submit' class='button' value='Guardar Cambios' />";
		}
		else{														/* Unconfirmed customer */
			$submit .= "<input type='button' class='button' value='Guardar Cambios' id='potentialSubmit' />";
			$submit .= "<input type='submit' class='button freeWidth' value='Confirmar Cliente y Guardar' />";
		}
		oFormTable()->addRowHTML("<td colspan='2'>{$submit}</td>");
		
		
		# Fill Values
		if( $id ) oFormTable()->fillValues( $cust );
		
		# Submit line
		oFormTable()->xajaxSubmit( $id ? 'editCustomers' : 'createCustomers');
		
		# Attach comboList and get the page
		oLists()->includeComboList('customers', !empty($cust['since']) ? 'customers' : 'potential', $id);
		oSmarty()->assign('editCustomerTbl', oFormTable()->getTemplate());
		
		# Add commands and actions to Xajax response object
		addScript("\$('".($id ? 'editCust' : 'newCust')."_number').focus();");
		
	}
	
	function page_createCustomers(){
	
		return page_editCustomers();		/* We just 'edit' an empty customer */
		
	}
	
	
	
	
	
	
	/* TEMP */
	function page_sales(){
	
		return oModules()->printPage('salesCommonList', 'sale');
	
#		return oLists()->printList('sales', 'sale');
		
	}
	
	/* TEMP */
	function page_salesInfo(){
	
		return oModules()->printPage('salesInfo', 'sale');
	
#		return oLists()->printList('sales', 'sale');
		
	}
	
	/* TEMP */
	function page_registerSales(){
	
		/* TEMP: it should always show current date, but for now we're registering OLD sales */
		oSmarty()->assign('tmpDate', isset($_GET['f']) ? "{$_GET['f']}-01" : date('Y-m-d'));
		
	}

?>