<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



/* TEMP : this script is being used as a testing suite of pages for the development of Modules library */
	
	function page_customers( $modifier='customers' ){	/* Status: 'customers', 'potential', 'all' */
		return oSnippet()->addSnippet('commonList', 'customers', $modifier);
	}
	function page_potentialCustomers(){					/* Status: 'customers', 'potential', 'all' */
		return page_customers( 'potential' );
	}
	function page_customersInfo( $id ){
		oSnippet()->addSnippet('info', 'customers', $id);
		return oTabs()->start( false );
	}
	function page_editCustomers( $id=NULL ){
		return oSnippet()->addSnippet('edit', 'customers', $id);
	}
	function page_createCustomers(){
		return page_editCustomers();
	}
	
	
	
	function page_sales(){
		return oSnippet()->ajaxPrintPage('salesCommonList', 'sale');
	}
	function page_installs(){
		return oSnippet()->ajaxPrintPage('salesCommonList', 'install');
	}
	function page_techVisits(){
		return oSnippet()->ajaxPrintPage('salesCommonList', 'service');
	}
	function page_salesInfo( $id ){
		return oSnippet()->ajaxPrintPage('salesInfo', 'sale', $id);
	}
	function page_createSales(){
		return page_editSales();
	}
	function page_editSales( $id=NULL ){
		return oSnippet()->ajaxPrintPage('salesEdit', NULL, $id);
	}

?>