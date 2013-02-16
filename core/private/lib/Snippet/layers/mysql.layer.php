<?php

	require_once( CONNECTION_PATH );

	class SnippetLayer_mysql extends Connection{
		
		private $mainTable;
		private $tables;
		private $fields;
		private $shown;
		private $keys;
		private $FKs;
		
		public function delete($table, $filters){
		
			return parent::delete($table, $filters);
		
		}
		
		public function update($data, $table, $keys){
		
			return parent::update($data, $table, $keys);
		
		}
	
		public function feed( $summary ){
			
			# We assume Source sends the right keys,
			# for this is internal and wouldn't be abused
			foreach( $summary as $k => $v ) $this->$k = $v;
			
		}
		
		public function generate( $what ){
			
			switch( $what ){
				case 'list': return $this->sql4List();
			}
			
		}
		
		private function sql4List(){
			
			$sql = "SELECT ";
			foreach( $this->shown as $field ){
				$fields[] = '`'.str_replace('.', '`.`', $field).'`';
			}
			$sql .= isset($fields) ? join(",\n       ", $fields) : '*';
			
			foreach( $this->mainTable as $code => $name ){
				$sql .= "\nFROM `{$name}` `{$code}`";
			}
			
			foreach( $this->tables as $code => $name ){
				$sql .= "\nLEFT JOIN `{$name}` `{$code}`";
			}
			
			return $sql;
			
		}
	
	}