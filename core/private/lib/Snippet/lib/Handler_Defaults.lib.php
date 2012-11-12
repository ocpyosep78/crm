<?php

class Snippets_Handler_Defaults{

	protected function getBasicAttributes()   { return array(); }
	protected function getDatabaseDefinition(){ return array(); }
	protected function getListFields()        { return array(); }
	protected function getItemFields()        { return array(); }
	protected function getTools()             { return array(); }

	protected function getFieldsFor()         { return array(); }
	protected function getListData()          { return ''; }
	protected function getItemData()          { return ''; }

	protected function getValidationRuleSet() {}
	protected function strictValidation()     {}

	protected function prefetchUserInput()    {}
	protected function checkFilter()          {}
	protected function checkData()            {}

	public function onSuccess()               {}

}