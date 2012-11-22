<?php

function page_customers()         { return SNP::snp('commonList', 'Customer.Confirmed'); }
function page_potentialCustomers(){ return SNP::snp('commonList', 'Customer.Potential'); }

function page_createCustomers()   { return SNP::snp('createItem', 'Customer'); }

function page_customersInfo($id)  { return SNP::snp('simpleItem', 'Customer'); }



function page_sales()
{
	return oLists()->printList('sales', 'sale');
}

function page_registerSales()
{
	/* TEMP: it should always show current date, but for now we're registering OLD sales */
	oSmarty()->assign('tmpDate', isset($_GET['f']) ? "{$_GET['f']}-01" : date('Y-m-d'));
}