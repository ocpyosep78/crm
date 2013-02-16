<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */



/***************

	USAGE
	
	Tools:
		oFormTable()->clear();									// Resets FormTable object
		
	Special rows:
		oFormTable()->addSeparator();						// Adds one row with an HR tag (colspan 2)
		oFormTable()->addTitle( 'someTitle' );	// Adds one title row (colspan 2)
	
	Regular rows:
		Code				HTML Type/Tag					Shortcut
		* separator			<hr />							->addSeparator()
		* title				Title (text)					->addTitle( $title )
		* row				(text)							->addRow( $field, $value )
		* input				(input type='text')				->addInput( $field, $atts )
		* password			(input type='password')			->addInput( $field, $atts, 'password' )
		* check				(input type='checkbox')			->addInput( $field, $atts, 'check' )
		* radio				(input type='radio')			->addInput( $field, $atts, 'radio' )
		* combo				(select)						->addCombo( $field, $values, $atts )
		* submit			(input type='submit')			->addSubmit( $value, $atts )
		* button			(input type='button')			->addSubmit( $value, $atts, 'button' )
	
	Form/Table attributes:
		* addFormAtt($att, $val)						To set id, name, class, etc.
		* addTableAtt($att, $val)						To set id, name, class, etc.
		* formSubmit( (string)$xajaxFunction )			Sets action property of the form
		* xajaxSubmit( (string)$xajaxFunction )			Submits form to this php function through xajax
		
***************/
	
	class FormTable{
		
		public $data;
		
		public $preText;
		
		public $frameTitle;
		public $formAtts;
		public $tableAtts;
		
		public $pfx;
		
		public $emptyTxt;		/* Text to fill empty fields with */
		
		public $hiddenRow;
		
		public function __construct(){
			$this->clear();
		}
		
		public function __toString(){
			oSmarty()->assign('FT', $this);
			return oSmarty()->fetch( $this->getTemplatePath() );
		}
		
		public function clear(){
		
			$this->data = array();
			
			$this->preText = array();
			$this->frameTitle = '';
			$this->formAtts = array();
			$this->tableAtts = array(
				'class'			=> 'FormTable',
				'border'		=> '0',
				'cellspacing'	=> '0',
				'cellpadding'	=> '0',
			);
			$this->emptyTxt = '';
			
			$this->prefix = '';
			
		}
		
		public function hiddenRow(){
			$this->hiddenRow = true;
		}
		
		public function setPrefix( $pfx ){
			$this->pfx = $pfx;
		}
		
		public function addPreText( $text ){
			$this->preText[] = $text;
		}
		
		public function setFrameTitle( $title ){
			$this->hasFrame = true;
			$this->frameTitle = $title;
		}
		
		public function addFormAtt($att, $val){
			if( empty($this->formAtts) ){
				$this->formAtts = array(
					'action'		=> 'javascript:void(0);',
					'method'		=> 'POST',
					'autocomplete'	=> 'autocomplete',
				);
			}
			$this->formAtts[$att] = $val;
		}
		
		public function addTableAtt($att, $val){
			$this->tableAtts[$att] = $val;
		}
		
		public function formSubmit( $action ){
			$this->addFormAtt('action', $action);
		}
		
		public function xajaxSubmit( $xajaxFunction, $sendDisabled=false ){
			$params = "this, '{$xajaxFunction}', ".($sendDisabled ? 'true' : 'false');
			$this->addFormAtt('onsubmit', "xajaxSubmit({$params});");
		}
		
		public function pajaxSubmit( $pajaxFunction, $timeOut='null' ){
			$params = "this, '{$pajaxFunction}', {$timeOut}";
			$this->addFormAtt('action', '');
			$this->addFormAtt('onsubmit', "return Pajax.submitForm({$params});");
		}
		
		public function getData(){
			return $this->data;
		}
		
		public function addSpacer(){
			$this->createRow();
		}
		
		public function addSeparator(){
			$this->createRow( 'separator' );
		}
		
		public function addTitle( $title='' ){
			$this->createRow('title', '', $title);
		}
		
		public function addRow($field='', $value='', $atts=array()){
			if( $this->emptyTxt && $value == '' ){
				$value = "<span style='color:#808080;'>{$this->emptyTxt}</span>";
			}
			$this->createRow( 'row', $field, $value, $atts );
		}
		
		public function addInput( $field, $atts=array(), $type='text' ){
			$this->createRow( $type, $field, '', $atts );
		}
		
		public function addArea( $field, $atts=array() ){
			$this->createRow( 'area', $field, '', array('value'=>'') + $atts );
		}
		
		public function addCombo( $field, $values=array(), $atts=array() ){
			$this->createRow( 'combo', $field, $values, $atts );
		}
		
		public function addFile( $field, $atts=array() ){
			$this->createRow('file', $field, '', ($atts ? $atts : array()));
			$this->addFormAtt('enctype', 'multipart/form-data');
		}
		
		public function addSubmit( $value, $atts=array(), $type='submit' ){
			$this->createRow($type, '', '', array('value' => $value, 'class' => 'button') + ($atts ? $atts : array()));
		}
		
		public function addTip( $tip='' ){
			if( $cnt=count($this->data) ) $this->data[$cnt-1]['tip'] = $tip;
		}
		
		public function addNote( $value, $atts=array() ){
			$this->createRow('note', '', $value, $atts);
		}
		
		public function addRowHTML( $value ){
			$this->createRow('free', '', $value);
		}
		
		public function hasFrame(){
			return !!$this->frameTitle;
		}
		
		public function hasForm( ){
			return !empty($this->formAtts);
		}
		
		public function getTemplatePath(){
			return dirname(__FILE__).'/FormTable.tpl';
		}
		
		public function getTemplate(){
			return is_file($path=dirname(__FILE__).'/FormTable.tpl') ? oSmarty()->fetch($path) : '';
		}
		
		private function createRow($type, $field='', $value='', $atts=array()){
		
			if( isset($atts['id']) && !isset($atts['name']) ){
				$atts['name'] = $atts['id'];
			}
			if( !empty($atts['id']) ) $atts['id'] = "{$this->pfx}{$atts['id']}";
			if( !empty($atts['name']) ) $atts['name'] = "{$this->pfx}{$atts['name']}";
			
			$selected = !empty($atts['selected']) ? $atts['selected'] : '';
			if( empty($atts['class']) && ($type == 'text' || $type == 'password') ) $atts['class'] = 'input';
			unset($atts['selected']);
			
			$this->data[] = array(
				'field'			=> $field,
				'type'			=> $type,
				'value'			=> $value,
				'selected'		=> $selected,
				'hidden'		=> intval($this->hiddenRow),
				'atts'			=> $atts + array('id' => '', 'name' => ''),
			);
			
			$this->hiddenRow = false;
			
		}
		
		public function fillValues( $data ){		# Fill values for each row
		
			$pfxLen = strlen($this->pfx);
			foreach( $this->data as $key => $row ){
				if(!$row['atts']['id'] || !isset($this->data[$key])) continue;
				if( !isset($data[substr($row['atts']['id'], $pfxLen)]) ) continue;
				$val = $data[substr($row['atts']['id'], $pfxLen)];
				$this->data[$key]['type'] == 'combo'
					? $this->data[$key]['selected'] = $val
					: $this->data[$key]['atts']['value'] = $val;
			}
			
		}
		
	}