<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */
	
	return array(
		'setSeller',
		'registerSale',
		'createCustomers',
		'createCustomerContacts',
		'createCustomerOwners',
		'editCustomers',
		'deleteCustomers',
		'deleteCustomerContacts',
		'deleteCustomerOwners',
	);
	
	
	
	function createCustomers( $atts ){
	
		if( empty($atts['newCust_seller']) ) $atts['seller'] = NULL;
		if( empty($atts['newCust_number']) ) $atts['number'] = NULL;
		
		oValidate()->preProcessInput($atts, "newCust_");
		
		$potential = false;
		if( isset($atts['potential']) ){
			unset( $atts['potential'] );
			$atts['since'] = 'NULL';
			$potential = true;
		}
		
		$ruleSet = $potential ? 'potentialCustomers' : 'customers';
		if( ($ans=oValidate()->test($atts, $ruleSet)) !== true ){
			return addScript("FTshowTip('newCust_{$ans['field']}', '{$ans['tip']}');");
		}
		
		# Register new customer
		oSQL()->setOkMsg("El cliente {$atts['customer']} fue ingresado con éxito".
			($potential ? ' (como posible cliente)' : ''));
		$ans = oSQL()->insert($atts, 'customers');
		
		# On success redirect and show success msg
		if( !$ans->error ) return oNav()->getPage('customersInfo', array($ans->ID), $ans->msg, 1);
		
		# Handled errors
		if( $ans->error == 1062 ){
			if( $ans->column == 'customer' ){
				return showStatus("El nombre {$atts['customer']} ya está registrado en la base de datos.");
			}
			elseif( $ans->column == 'number' ){
				return showStatus("Ya existe un cliente con ese número. Verifique sus datos o inténtelo nuevamente.");
			}
		}
		
		# Unhandled error
		return showStatus( $ans->msg );
		
	}
	
	function editCustomers( $atts ){
		
		oValidate()->preProcessInput($atts, "editCust_");
		
		$potential = false;
		if( isset($atts['potential']) ){
			unset( $atts['potential'] );
			$atts['since'] = 'NULL';
			$potential = true;
		}
		
		# Set seller to NULL if not selected (to avoid MySQL constraints)
		if( empty($atts['seller']) ) $atts['seller'] = NULL;
		
		$ruleSet = $potential ? 'potentialCustomers' : 'customers';
		if( ($ans=oValidate()->test($atts, $ruleSet)) !== true ){
			return addScript("FTshowTip('editCust_{$ans['field']}', '{$ans['tip']}');");
		}
		
		if( $atts['number'] ){
			$res = oSQL()->getCustomers(array('number' => $atts['number']));
			if( count($res) > 1 || (count($res) == 1 && $res[0]['id_customer'] != $atts['id_customer']) ){
				return showStatus("Ya existe un cliente con ese número.\\n".
					"Verifique sus datos o inténtelo nuevamente.");
			}
		}
		
		# Request query and catch answer, then return it to the user
		oSQL()->setOkMsg("El cliente {$atts['customer']} fue modificado con éxito");
		$ans = oSQL()->editCustomers( $atts );
		
		if( $ans->error ){
			return showStatus( $ans->msg, $ans->successCode );
		}else{
			return oNav()->getPage('customersInfo', array($atts['id_customer']), $ans->msg, $ans->successCode);
		}
		
	}
	
	function deleteCustomers($id, $modifier=NULL){
	
		# Request query and catch answer, then return it to the user
		oSQL()->setOkMsg("El cliente fue eliminado correctamente");
		$ans = oSQL()->deleteCustomers( $id );
		
		if( $ans->error ) return showStatus( $ans->msg );
		else return oNav()->getPage('customers', array($modifier), $ans->msg, 1);
		
	}
	
	function setSeller( $id ){
	
		$custInfo = oSQL()->getCustomer( $id );
		$seller = $custInfo['seller'] ? $custInfo['seller'] : '';
		
		return addScript("document.forms['frmOldSales'].setSeller('{$seller}');");
		
	}
	
	function registerSale( $data ){
		
		if( ($res=oValidate()->test($data, 'sales')) !== true ){
			return addScript("showTip('{$res['field']}', '{$res['tip']}');");
		}
		
		# Get type of sale (system, product, service) and get rid of the entry
		# TEMP: type is not used for now, but it might in the future
		unset( $data['saleType'] );
		
		# Get invoice info (invoice id and date) and discard date from sale data
		$invoice = array('invoice' => $data['invoice'], 'date' => $data['date']);
		unset( $data['date'] );
		
		# Use a transaction, because a sale cannot be registered if invoice doesn't exist
		oSQL()->BEGIN();
			# Try to insert new invoice
			$ans1 = oSQL()->insert($invoice, 'invoices');
			# If it failed with an error other than 1062 (duplicate), we abort
			if( $ans1->error && $ans1->error != 1062 ){
				oSQL()->ROLLBACK();
				return showStatus( $ans1->msg );
			}
			# If it either inserted a new invoice, or it already existed, we're ready to go
			oSQL()->setErrMsg('Ocurrió un error al intentar ingresar la venta en la base de datos.');
			oSQL()->setDuplMsg('Ya existe una venta registrada con ese número de factura.');
			$ans2 = oSQL()->insert($data, 'sales');
			if( $ans2->error ){
				oSQL()->ROLLBACK();
				return showStatus( $ans2->msg );
			}
		oSQL()->COMMIT();
		
		# Reset fields
		addScript("document.forms.frmOldSales.restart();");
		
		/* TEMP: it should take you to new sale's page, but for now we're bulk registering sales */
		return showStatus('La venta fue ingresada correctamente en la base de datos.', 1);
		
	}
	
	function createCustomerContacts($data, $modifier){
	
		oValidate()->preProcessInput($data, 'ccc_');
		
		$data['id_customer'] = $modifier;
		$id = empty($data['SL_ID']) ? NULL : $data['SL_ID'];
		unset( $data['SL_ID'] );
		
		if( $id ) $data['id_contact'] = $id;
		
		if( ($valid=oValidate()->test($data, 'customerContacts')) === true ){
			$ans = oSQL()->{$id ? 'update' : 'insert'}($data, 'customers_contacts', array('id_contact'));
			if( !$ans->error ) return oTabs()->switchTab('contacts');
			else return showStatus('No se pudo procesar su consulta. '.
				'Compruebe los datos ingresados y vuelva a intentarlo.');
		}
		else return addScript("FTshowTip('ccc_{$valid['field']}', '{$valid['tip']}');");
				
	}
	
	function deleteCustomerContacts( $id ){
		
		$ans = oSQL()->delete('customers_contacts', array('id_contact' => $id));
		if( !$ans->error ) return oTabs()->switchTab('contacts');
		else return showStatus('Ocurrió un error. El elemento no pudo ser eliminado.');
		
	}
	
	function createCustomerOwners($data, $modifier){
	
		oValidate()->preProcessInput($data, 'cco_');
		
		$data['id_customer'] = $modifier;
		if( $data['docNum'] == '' ) $data['docNum'] = NULL;
		if( $data['phone'] == '' ) $data['phone'] = NULL;
		
		$id = empty($data['SL_ID']) ? NULL : $data['SL_ID'];
		if( $id ) $data['id_owner'] = $id;
		unset( $data['SL_ID'] );
		
		if( ($valid=oValidate()->test($data, 'customerOwners')) === true ){
			$ans = oSQL()->{$id ? 'update' : 'insert'}($data, 'customers_owners', array('id_owner'));
			if( !$ans->error ) return oTabs()->switchTab('owners');
			else return showStatus('No se pudo procesar su consulta. '.
				'Compruebe los datos ingresados y vuelva a intentarlo.');
		}
		else return addScript("FTshowTip('cco_{$valid['field']}', '{$valid['tip']}');");
		
	}
	
	function deleteCustomerOwners( $id ){
		
		$ans = oSQL()->delete('customers_owners', array('id_owner' => $id));
		if( !$ans->error ) return oTabs()->switchTab('owners');
		else return showStatus('Ocurrió un error. El elemento no pudo ser eliminado.');
		
	}

?>