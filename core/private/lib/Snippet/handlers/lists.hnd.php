<?php

	class Snippet_hnd_lists extends Snippets_Handlers_Commons{
		
		/**
		 * @overview: commonList at first just prints the frame in which the
		 *            actual list will be loaded, and its initialize function
		 *            will request the list to be loaded (innerCommonList)
		 *            from the client, as soon as the frame is ready.
		 */
		protected function handle_commonList(){
		
			/* TEMP : untill this library handles navigation issues */
			if( $_POST['xajax'] == 'addSnippet' && !$this->params['writeTo'] ){
				$_POST['xajax'] = 'getPage';
				oNav()->getPage($this->code, (array)$this->params['modifier']);
				return '';
			}
		
			return $this->fetch( 'lists/commonList' );
			
		}
		
		/**
		 * @overview: this is the actual list that goes within a commonList.
		 *            There's nothing against calling it on its own as any
		 *            regular element (to present it without the frame or in
		 *            another frame, maybe)
		 */
		protected function handle_innerCommonList(){
			
			$params = $this->params;
			
			# This snippet doesn't include a create button
			$this->disableBtns(array('list', 'create'));
			
			# Get Data
			$data = $this->Source->getData('list', $params['filters']);
			$this->assign('data', $data);
			
			return $this->fetch( 'lists/innerCommonList' );
		
		}
		
		protected function handle_simpleList(){
		
			return $this->fetch( 'lists/simpleList' );
			
		}
		
		/**
		 * Complex lists are multidimensional lists, with a main branch and 0-n
		 * secondary branches coming out, i.e. a list of lists. Currently, complexList
		 * handles only 2-dimensional lists.
		 * A complex list is presented as a vertical list of the main elements, with
		 * the possibility to expand each element to see its properties and members
		 * (the lists within).
		 * The snippet includes filters to see more specific results.
		 */
		protected function handle_complexList(){
		
			$fields = TEMPgetFields();
			$data = TEMPgetData();

			$this->assign('membersName', 'Presupuestos');
			$this->assign('fields', $fields);
			$this->assign('data', $data);
		
			return $this->fetch( 'lists/complexList' );
			
		}
		
	}
	

	/* TEMP data provider - start - */
	function TEMPgetFields(){
	
		return array('customer' => 'Cliente', 'seller' => 'Vendedor');
	
	}
	
	function TEMPgetData(){
	
		$max = 5;
		$data = array();
		for( $i=1 ; $i<=$max ; $i++ ){
			$key = count($data);
			$data[$key] = array(
				'name'			=> 'Presupuesto corporativo '.$i,
				'data'	=> array(
					'customer'		=> array($i => 'Cliente de Prueba '.$i),
					'seller'		=> array($i => 'Usuario '.$i),
				),
				'members' => array(),
			);
			$members = rand(0, 8);
			for( $j=0 ; $j<$members ; $j++ ){
				$rand = rand(1, 5);
				$data[$key]['members'][$rand] = 'Presupuesto '.$rand;
			}
		}
		
		return $data;
		
	}
	/* TEMP data provider - end - */

?>