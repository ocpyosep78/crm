<?php

/**
 * CRMTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



	oSQL();

	class PDF_TechVisits_Provider extends SQL{
	
		private $visit;
	
		/**
		 * Initialize PDF object
		 */
		public function __construct( $id ){
		
			if( empty($id) ) die('Faltan par�metros para procesar su solicitud');
		
			parent::__construct();
		
			$this->visit = $this->getTechVisit( $id );
			
$fill = 'texto texto texto texto texto texto texto texto texto texto texto texto texto texto';
$this->visit2 = array(
	'id_customer'		=> '51',
	'number'			=> $fill,
	'customer'			=> $fill,
	'legal_name'		=> $fill,
	'address'			=> $fill,
	'id_location'		=> '29',
	'phone'				=> $fill,
	'subscribed'		=> 0,
	'custNumber'		=> 1007,
	'invoice'			=> 21389,
	'currency'			=> '$',
	'cost'				=> 452,
	'contact'			=> $fill,
	'id_sale'			=> 62,
	'onSale'			=> '',
	'technician'		=> 'Rodrigo De Los Santos',
	'date'				=> '2009-07-27',
	'starts'			=> '',
	'ends'				=> '',
	'reason'			=> $fill,
	'outcome'			=> $fill,
	'quality'			=> 'good',
	'order'				=> 12345,
	'complete'			=> 0,
	'ifIncomplete'		=> $fill,
	'usedProducts'		=> $fill,
	'pendingEstimate'	=> 1,
	'system'			=> $fill,
);

			if( empty($this->visit['id_sale']) ) die('No se encontr� la constancia t�cnica buscada.');
			
		}
		
		
/***************
** E X T E R N A L   S O U R C E S
***************/

		public function getData(){
			
			return $this->visit;
			
		}
		
		public function getCustomerInfo(){
		
			$v = $this->visit;
			
			# Calculate warranty void status
			if( empty($v['installDate']) || empty($v['warranty']) ) $warranty = false;
			else{
				$void = date('Y-m-d', strtotime("{$v['installDate']} + {$v['warranty']} months"));
				$warranty = $void > $v['date'] ? 1 : 0;
			}
		
			# Fix install date in its parts
			list($insY, $insM, $insD) = $v['installDate']
				? explode('-', $v['installDate'])
				: array('----', '--', '--');
			$insY = substr($insY, 2);
		
			return array(
				'name'			=> $v['customer'],
				'contact'		=> $v['contact'],
				'address'		=> $v['address'],
				'phone'			=> $v['phone'],
				'number'		=> $v['custNumber'],
				'subscribed'	=> $v['subscribed'],
				'warranty'		=> $warranty,
				'installDate'	=> array('d' => $insD, 'm' => $insM, 'y' => $insY),
			);
			
		}
		
		public function getBodyInfo(){
		
			$v = $this->visit;
			
			return array(
				'system'	=> $v['system'],
				'reason'	=> $v['reason'],
				'outcome'	=> $v['outcome'],
				'complete'	=> $v['complete'],
				'excuse'	=> $v['ifIncomplete'],
				'used'		=> $v['usedProducts'],
				'cost'		=> $v['cost'],
				'costUSS'	=> $v['costDollars'],
				'invoice'	=> $v['invoice'],
				'order'		=> $v['order'],
				'pending'	=> $v['pendingEstimate'],
				'quality'	=> $v['quality'],
			);
			
		}
		
		public function getRelatedInvoice(){
		
			$v = $this->visit;
			
			return array(
				'cost'			=> $v['currency'] == '$' ? $v['cost'] : '',
				'costDollars'	=> $v['currency'] == 'U$S' ? $v['cost'] : '',
				'invoice'		=> $v['invoice'],
			);
		
		}
		
		public function getPeriod(){
			
			list($times['startsH'], $times['startsM']) = explode(':', $this->visit['starts']);
			list($times['endsH'], $times['endsM']) = explode(':', $this->visit['ends']);
			
			return $times;
		
		}
		
	}

?>