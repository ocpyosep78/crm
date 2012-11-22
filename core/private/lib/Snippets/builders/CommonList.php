<?php

class snp_CommonList extends SNP
{

	private $buttons = ['view'   => 'ver información de',
	                    'edit'   => 'editar',
	                    'delete' => 'eliminar'];


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
		$toolTip = $this->View->screen_names[$this->View->descr_field];

		$this->View->assign('titles', $titles);
		$this->View->assign('data', $data);

		$this->View->assign('listButtons', $this->buttons);
		$this->View->assign('toolTip', $toolTip);

		// Include two sub-Snippets as well: bigTools and comboList
		$this->View->assign('bigTools', self::read('bigTools'));
		$this->View->assign('comboList', self::read('comboList'));
	}

	protected function _view()
	{
		return self::delegate('viewItem', ['action' => 'insert']);
	}

	protected function _dialogView()
	{
		return SNP::delegate('simpleItem', ['action' => 'dialog']);
	}

	protected function _edit()
	{
		return SNP::delegate('editItem', ['action' => 'dialog']);
	}

	protected function _delete()
	{
		$Answer = $this->Model->delete($this->params['id']);

		if ($Answer->failed)
		{
			return say(devMode() ? $Answer->Error->error : $Answer->msg);
		}

		return $this->_insert();
	}

	protected function _update()
	{
		return say('CommonList::update Under Construction');
	}

}