<?php

class snp_CommonList extends SNP
{

	private $buttons = array(
		'view'		=> 'ver información de',
		'edit'		=> 'editar',
		'delete'	=> 'eliminar',
	);


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
		// Expected: $fields, $fieldinfo, $data, $primary
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
		$this->View->assign('primary', $primary);

		$this->View->assign('listButtons', $this->buttons);
		$this->View->assign('toolTip', $toolTip);

		// Include two sub-Snippets as well: bigTools and comboList
		$params = array('parent' => 'commonList',
		                'action' => 'html') + $this->params;

		$bigTools = self::snp('bigTools', $params['model'], $params);
		$this->View->assign('bigTools', $bigTools);

		$comboList = self::snp('comboList', $params['model'], $params);
		$this->View->assign('comboList', $comboList);
	}

	protected function _view()
	{
		return self::delegate('viewItem', array('action' => 'insert'));
	}

	protected function _dialogView()
	{
		return SNP::delegate('simpleItem', array('action' => 'dialog'));
	}

	protected function _edit()
	{
		return SNP::delegate('editItem', array('action' => 'dialog'));
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