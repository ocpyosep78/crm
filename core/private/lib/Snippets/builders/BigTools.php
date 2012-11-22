<?php

class snp_BigTools extends SNP
{

	private $buttons = ['list'   => 'listado',
	                    'create' => 'agregar',
	                    'view'   => 'ver información de',
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

	protected function _list(){
		return self::delegate('commonList', ['action' => 'insert']);
	}

	protected function _create(){
		return say('BigTools::create Under Construction');
	}

	protected function _view(){
		return say('BigTools::view Under Construction');
	}

	protected function _edit(){
		return self::delegate('editItem', ['action' => 'dialog']);
	}

	protected function _delete(){
		$deleted = $this->Model->delete($this->params['id']);
		db($deleted);
	}

}