<?php

class snp_BigTools extends SNP
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
		$this->assign('buttons', $this->buttons());
	}

	protected function _list()
	{
		return $this->delegate('commonList', 'insert', true);
	}

	protected function _create()
	{
		return $this->delegate('createItem', 'dialog', true);
	}

	protected function _edit()
	{
		return true || ($this->params['starter'] == 'viewItem')
			? $this->delegate('editItem', 'insert', true)
			: $this->delegate('editItem', 'dialog', true);
	}

	protected function _view()
	{
		return $this->delegate('viewItem', 'insert', true);
	}

	protected function _delete()
	{
		$Answer = $this->Model->delete($this->params['id']);

		return $Answer->failed
			? (say(devMode() ? $Answer->Error->error : $Answer->msg))
			: $this->delegate($this->params['parent'], 'insert', true);
	}

	protected function _restore()
	{
		$Answer = $this->Model->restore($this->params['id']);

		return $Answer->failed
			? (say(devMode() ? $Answer->Error->error : $Answer->msg))
			: $this->delegate($this->params['parent'], 'insert', true);
	}

	protected function buttons()
	{
		switch ($this->params['starter'])
		{
			case 'commonList':
				return array_diff_key(parent::buttons(), ['list' => NULL]);

			case 'createItem':
				return parent::buttons(['list', 'create'], false);

			case 'editItem':
				return array_diff_key(parent::buttons(), ['edit' => NULL]);

			case 'viewItem':
				return array_diff_key(parent::buttons(), ['view' => NULL]);
		}
	}

}