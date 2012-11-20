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
		// Expected: $fields, $data, $hidden
		extract($this->View->getTabularData(30));

		foreach ($fields as $field)
		{
			$titles[] = addslashes($field);
		}

		$toolTip = $this->View->screen_names[$this->View->descr_field];

		$this->View->assign('primary', $primary);

		$this->View->assign('titles', $titles);
		$this->View->assign('data', $data);

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
		return say('CommonList::view Under Construction');
	}

	protected function _dialogView()
	{
		$params = $this->params;
		$params['action'] = 'dialog';

		return SNP::snp('simpleItem', $params['model'], $params);
	}

	protected function _edit()
	{
		$params = $this->params;
		$params['action'] = 'dialog';

		return SNP::snp('editItem', $params['model'], $params);
	}

	protected function _delete()
	{
		return say('CommonList::delete Under Construction');
	}

	protected function _update()
	{
		return say('CommonList::update Under Construction');
	}

}