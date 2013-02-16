<?php

class snp_ComboList extends Snippet
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
	protected function assignVars()
	{
		$this->assign('list', $this->View->getHashData());
		$this->assign('selected', $this->params['id']);
	}

	protected function _viewItem()
	{
		return $this->delegate('viewItem', 'insert', true);
	}

}