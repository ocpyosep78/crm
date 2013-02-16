<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	function page_technical(){
	
		return page_techVisits();
		
	}

	function page_techVisits(){
		/* Include code in case it's called with an alias */
		return oLists()->printList('techVisits');
		
	}
	
	function page_installs(){
	
		return oLists()->printList();
		
	}
	
	function page_createTechVisits($id=NULL, $customerid=NULL){
		# If an id was provided, we pass the visit's data, to pre-fill the form (edit/info mode)
		if(!$id && !$customerid){
			$date = explode('-', date('Y-m-d'));
			oNav()->setJSParams( array(
				'day'	=> $date[2],
				'month'	=> $date[1],
				'year'	=> $date[0],
			) );
		}
        elseif( !empty($customerid) ){
            $data['id_customer'] = $customerid;
			oNav()->setJSParams( $data );
        }
/* Array(
    [id_sale] => 62
    [type] => service
    [id_customer] => 51
    [id_system] =>
    [invoice] => 21389
    [date] => 2009-07-27
    [currency] =>
    [cost] =>
    [warranty] =>
    [contact] =>
    [notes] =>
    [number] =>
    [customer] => Balmoral Plaza Hotel
    [legal_name] => Puertosur s.a
    [rut] => 211283900014
    [address] => Plaza Cagancha 1126 (Montevideo)
    [billingaddress] => Plaza Cagancha 1126
    [id_location] => 29
    [phone] => 29022393
    [email] => info@balmoral.com.uy
    [seller] => pcorts
    [since] => 2010-09-24 11:48:52
    [subscribed] => 0
    [custNumber] => 1007
    [onSale] =>
    [technician] => rdelossantos
    [starts] => */
		/* If we're editting, get data and fix special fields */
		elseif( $data=oSQL()->getTechVisit($id) ){
			list($data['year'], $data['month'], $data['day']) = explode('-', $data['date']);
			if( !empty($data['installDate']) ){
				# Part install date and fix warranty (from warranty months to warranty void date)
				list($data['installYear'], $data['installMonth'], $data['installDay']) = explode('-', $data['installDate']);
				$data['warranty'] = strtotime($data['installDate'].' + '.$data['warranty'].' months') > time() ? 1 : 0;
			}
			else $data['warranty'] = 0;
			if( !empty($data['starts']) ) list($data['startsH'], $data['startsM']) = explode(':', $data['starts']);
			if( !empty($data['ends']) ) list($data['endsH'], $data['endsM']) = explode(':', $data['ends']);
			$data['costDollars'] = $data['currency'] == 'U$S' ? $data['cost'] : '';
			$data['cost'] = $data['currency'] == '$' ? $data['cost'] : '';
			
			oNav()->setJSParams( $data );
		}
		else return oNav()->getPage('techVisits', 'No se encontr� la visita pedida.');
		
		oSmarty()->assign('systems', oLists()->systems());
		oSmarty()->assign('technicians', oLists()->technicians());
	
		hideMenu();
		
	}
	
	function page_editTechVisits( $id ){
	
		return page_createTechVisits( $id );
		
	}
	
	function page_techVisitsInfo( $id ){
	
		oSmarty()->assign('id', $id);

        if(oPermits()->can('adminTechNotes')){
            oSmarty()->assign('adminNote', oSql()->getAdminTechNote($id));
        }
		
		oNav()->setJSParams( $id );
		
	}