<?php

class snp_Tabs extends SNP
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
		$tabs = $this->View->getRelated();

		$this->View->assign('tabs', $tabs);
	}

	protected function _load()
	{
		$id = $this->params['id'];
		$tab = $this->params['tab'];

		return say("Object with id {$id}, requires loading tab {$tab}");
	}

}