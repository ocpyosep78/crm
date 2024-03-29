<?php

class snp_CreateItem extends Snippet
{

	/**
	 * protected void assignVars()
	 *      This method is required. It will be called by final method ::html()
	 * right before interpreting the Snippet's template. Central vars are
	 * assigned after this method is called, on purpose, so you cannot overwrite
	 * them. Changing those assignments might break the library, so just: don't.
	 *
	 * @return void
	 */
	protected function assignVars() {}

	protected function _html()
	{
		return $this->delegate('editItem', ['id' => NULL, 'action' => 'html']);
	}

	protected function _dialog()
	{
		return $this->delegate('editItem', ['id' => NULL]);
	}

}