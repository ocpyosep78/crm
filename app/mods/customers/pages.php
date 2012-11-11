<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */
	function page_customers($modifier='customers')
	{
		// Allowed modifiers: 'customers', 'potential', 'all'
		return oSnippet()->addSnippet('commonList', 'customers', $modifier);
	}
	function page_potentialCustomers(){					/* Status: 'customers', 'potential', 'all' */
		return page_customers( 'potential' );
	}
	function page_customersInfo( $id ){
		$permits = array('createCustomerContacts', 'editCustomerContacts', 'deleteCustomerContacts');
		oPermits()->setAlias('editCustomers', $permits);
		$contactsList = oLists()->simpleListHTML('customerContacts', $id);

        $contactsHtml = "<div id='customerContacts'><h3>Contacto(s)</h3>{$contactsList}</div>";
        oSmarty()->assign('snippet_extraHTML', $contactsHtml);
        addScript("initializeSimpleList();");

		oSnippet()->addSnippet('viewItem', 'customers', array('filters' => $id));

		return oTabs()->start( false );
	}

	function page_createCustomers()
	{
		return page_editCustomers();		/* We just 'edit' an empty customer */
	}

	function page_editCustomers($id=NULL)
	{
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
			array('id' => 'id_location', 'selected' => $id ? $cust['id_location'] : 29));	/* TEMP : use MAIN_LOCATOIN instead */

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

	function page_sales(){

		return oLists()->printList('sales', 'sale');

	}



	/* TEMP */
	function page_registerSales(){

		/* TEMP: it should always show current date, but for now we're registering OLD sales */
		oSmarty()->assign('tmpDate', isset($_GET['f']) ? "{$_GET['f']}-01" : date('Y-m-d'));

	}