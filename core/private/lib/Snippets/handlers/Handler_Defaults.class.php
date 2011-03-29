<?php

	class Snippets_Handler_Defaults{
	
		public function getBasicAttributes()		{ return array(); }
		public function getDatabaseDefinition()		{ return array(); }
		public function getListFields()				{ return array(); }
		public function getItemFields()				{ return array(); }
		public function getTools()					{ return array(); }
		public function checkFilter()				{}
		public function checkData()					{}
		public function validationRuleSet()			{}
		public function strictValidation()			{}
		
	}

?>