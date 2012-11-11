<?php

/**
 * AppTemplate - PHP Framework for building CRM-like applications
 * GitHub https://github.com/dbarreiro/crm_template/
 * Copyright (C) 2011 Diego Barreiro <diego.bindart@gmail.com>
 * Licence: GNU GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/gpl.txt>
 */


	class Snippet_def_calls extends Snippets_Handler_Source{

		protected function getBasicAttributes(){

			return array(
				'name'		=> 'Llamada',
				'plural'	=> 'Llamadas',
				'gender'	=> 'f',
			);

		}


		/**
		 * @overview: List of tables involving this module, and each relevant field in them,
		 *            plus attributes for each field that could be used by Modules.
		 *            Attributes might have the following fields:
         *                name     Screen name for this field
         *                type     text, image, area, combo (defaults to text)
         *                isKey    whether this field is a key
         *                hidden   whether to hide this field in infoPage
         *                FK       i.e. ['sales']['seller']['FK'] = 'users.user'
		 *            Field will be ignored for output when name is empty or hidden is true
		 *            Fields flagged as keys (isKey => true) will be hidden by default. Set
		 *            hidden => false explicitly to override.
		 */
		protected function getDatabaseDefinition(){

			$tables = array(
				'_calls' => array(
					'id_call'		=> array('name' => 'ID', 'isKey' => true),
					'type'			=> array('name' => 'Tipo', 'type' => 'list', 'listSrc' => 'activity_types'),
					'caller'		=> array('name' => 'Quién llama'),
					'date'			=> array('name' => 'Fecha / Hora', 'type' => 'datetime'),
					'detail'		=> array('name' => 'Motivo / Descripción', 'type' => 'area'),
					'id_note'		=> array('name' => 'Nota asociada', 'FK' => '_notes.id_note'),
				),
				'customers' => array(
					'customer'		=> array('name' => 'Cliente asociado'),
					'id_customer'	=> array('name' => 'Cliente asociado', 'type' => 'list', 'listSrc' => 'customers'),
				),
				'_users' => array(
					'user'		=> array('name' =>'Usuario asignado', 'type' => 'list'),
					'assigned'	=> array('name' =>'Usuario asignado', 'aliasOf' => '$name $lastName'),
				),
				'types' => array(
					'typeLabel' => array('name' => 'Tipo', 'frozen' => true),
				),
			);

			foreach( $tables as &$table ) foreach( $table as &$atts ) $atts['frozen'] = true;

			return $tables;

		}

		protected function getFieldsFor( $type ){

			switch( $type ){
				case 'list':
					return array('date', 'detail', 'caller', 'customer', 'assigned', 'type');
				case 'view':
					return array('date', 'detail', 'caller', 'customer', 'assigned', 'typeLabel');
				case 'create':
				case 'edit':
					return array('date', 'detail', 'caller', 'id_customer', 'user', 'type');
			}

		}

		protected function getTools(){

			return array('view', 'create', 'edit', 'delete');

		}

/*		protected function checkFilter( &$filters ){
		}/**/

/*		protected function checkData( &$data ){
		}/**/

		protected function prefetchUserInput(&$data)
		{
			# Date comes as an array [date, time], we need a timestamp
			$data['date'] = "{$data['date_date']} {$data['date_time']}";
			unset($data['date_date'], $data['date_time']);

			# If no customer is picked, set it to null
			$data['id_customer'] || ($data['id_customer'] = NULL);
		}

		protected function getValidationRuleSet(){

			return array(
				'date'			=> array('datetime'),
				'detail'		=> array('open', 2, 600),
				'caller'		=> array('text', 2, 120),
//				'id_customer'	=> array('selection'),
				'user'			=> array('selection'),
			);

		}/**/

/*		protected function strictValidation(){
			return true;
		}/**/

/* TEMP : All these methods below should be automatically created based on the definition */

		private function globalFilters( &$filters ){

			$srch = $filters['*'];
			$filters = array();

			$fields = array_diff($this->getFieldsFor('view'), (array)'>');

			foreach( $fields as $field ) $filters["`{$field}`"] = $srch;

		}

		protected function getListData($filters=array(), $join='AND'){
			if( isset($filters['*']) ){
				$this->globalFilters( $filters );
				$join = 'OR';
			}
			$this->fixFilters($filters, array(
				'assigned'	=> "CONCAT(`u`.`name`,' ', `u`.`lastName`)",
				'typeLabel'	=> "IF(`ll`.`type` = 'technical', 'Técnica', 'Ventas')",
		//		'email'			=> '`c`.`email`',
			));

			$sql = "SELECT	`c`.`customer`,
							CONCAT(`u`.`name`,' ', `u`.`lastName`) AS 'assigned',
							`ll`.*,
							CONCAT(`ll`.`date`, ' - ', `caller`,
								IF(ISNULL(`ll`.`id_customer`), '',
								CONCAT(' (', `c`.`customer`, ')'))) AS 'tipToolText',
							IF(`ll`.`type` = 'technical', 'Técnica', 'Ventas') AS 'typeLabel'
					FROM `_calls` `ll`
					LEFT JOIN `_users` `u` USING (`user`)
					LEFT JOIN `customers` `c` USING (`id_customer`)
					WHERE {$this->array2filter($filters, $join)}
					ORDER BY `ll`.`date`";

			return $sql;

		}
		protected function getItemData( $id ){
			return $this->getListData( array('id_call' => array($id, '=')) );
		}
		private function getFilterFromModifier(){
			switch( $this->params['modifier'] ){
				case 'customers': return 'NOT ISNULL(`since`)';
				case 'potential': return 'ISNULL(`since`)';
			}
			return '1';		# No filter for status (show all customers)
		}

		protected function listForFieldUser(){
			return "SELECT	`user`,
							CONCAT(`name`, ' ', `lastName`)
					FROM `_users`";
		}

		public function onSuccess($input, $id){

			$data = $input;
			unset($data['caller'], $data['detail'], $data['id_call']);

			$data['by'] = getSes('user');
			$data['note'] = "(Llamada de {$input['caller']}) {$input['detail']}";
			$ans = $this->sqlEngine->insert($data, '_notes');

			if( !$input['type'] || !$ans->ID ) return;

			$data = array('model' => 'notes', 'uid' => $ans->ID);
			$this->sqlEngine->insert($data, 'activity');

		}

	}