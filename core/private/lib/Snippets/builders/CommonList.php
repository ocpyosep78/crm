<?php

class snp_CommonList extends SNP
{

	private $buttons = array(
		'view'		=> 'ver información de',
		'edit'		=> 'editar',
		'delete'	=> 'eliminar',
	);

	/**
	 * void void assignSnippetVars()
	 *      Vars that might be required by the view, besides the common ones.
	 *
	 * @return void
	 */
	protected function assignSnippetVars()
	{
		// Expected: $fields, $data, $hidden
		extract($this->View->getTabularData(30));

		foreach ($fields as $field)
		{
			$titles[] = addslashes($field);
		}

		$this->View->assign('titles' , $titles);
		$this->View->assign('data'   , $data);

		$this->View->assign('listButtons', $this->buttons);

		$this->View->assign('primary', $primary);
		$this->View->assign('toolTip', $this->View->descr_field);

		$params = array('parent' => 'commonList') + $this->params;
		$bigTools = self::getSnippet('bigTools', $params['model'], $params);

		$this->View->assign('bigTools', $bigTools);
	}

	public function updateContent()
	{

	}

	/**
	 * protected mixed snippetReturn()
	 *      After generating the html (stored in @html) perform final tasks,
	 * which might be ajax tasks, edition of the generate html, etc. The return
	 * of this method will be the return of getSnippet() as well; this should be
	 * a XajaxResponse object ideally, unless this snippet handles it specially.
	 *
	 * @return mixed
	 */
	public function snippetReturn()
	{
		return parent::snippetReturn();
	}

}