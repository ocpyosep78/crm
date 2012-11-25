<?php

class snp_CommonList extends SNP
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
		// Expected: $fields, $fieldinfo
		extract($this->View->getTabularParams());

		// Ask for 20 results, starting at $offset
		$offset = !empty($this->params['offset']) ? $this->params['offset'] : 0;
		$limit = "{$offset}, 20";

		// Apply filters (WHERE) and order, if provided
		$where = $this->params['where'];
		$order = $this->params['order'];

		$data = $this->Model->find($where, $fields, $order, $limit)->get();

		$titles = array_map('addslashes', $fields);

		$buttons = $this->buttons();
		unset($buttons['list'], $buttons['create']);

		$this->assign('titles', $titles);
		$this->assign('data', $data);
		$this->assign('buttons', $buttons);

		// Include two sub-Snippets as well: bigTools and comboList
		$this->assign('bigTools', self::read('bigTools'));
		$this->assign('comboList', self::read('comboList'));
	}

	protected function _view()
	{
		return $this->delegate('viewItem', 'insert', true);
	}

	protected function _dialog()
	{
		return $this->delegate('simpleItem', 'dialog', true);
	}

	protected function _edit()
	{
		return $this->delegate('editItem', 'dialog', true);
	}

	protected function _delete()
	{
		$Answer = $this->Model->delete($this->params['id']);

		return $Answer->failed
			? (say(devMode() ? $Answer->Error->error : $Answer->msg))
			: $this->delegate('commonList', 'insert', true);
	}

	protected function _restore()
	{
		$Answer = $this->Model->restore($this->params['id']);

		return $Answer->failed
			? (say(devMode() ? $Answer->Error->error : $Answer->msg))
			: $this->delegate('commonList', 'insert', true);
	}

	protected function _update()
	{
		return say('CommonList::update Under Construction');
	}

}