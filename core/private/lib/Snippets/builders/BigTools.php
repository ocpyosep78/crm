<?php

class snp_BigTools extends SNP
{

	private $buttons = array(
		'list'		=> 'listado',
		'create'	=> 'agregar',
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
		$parent = $this->params['parent'];

		# Disable certain buttons depending on which snippet is using this one
		switch ($parent)
		{
			case 'commonList':
			case 'simpleList':
				unset($this->buttons['list']);
				break;

			case 'createItem':
			case 'viewItem':
			case 'editItem':
				unset($this->buttons[str_replace('Item', '', $parent)]);
				break;
		}

		$this->View->assign('bigButtons', $this->buttons);
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
		return $this->html;
	}

}