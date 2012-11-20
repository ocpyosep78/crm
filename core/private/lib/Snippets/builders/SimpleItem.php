<?php

class snp_SimpleItem extends SNP
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
		$id = $this->params['id'];

		// Expected: $fields, $data, $toolTip, $hidden
		extract($this->View->getItemData($id));

		# Form data blocks (for presentational purposes)
		$chunks = array_chunk($data, ceil(count($data)/2), true);

		$this->View->assign('objectID', $id);
		$this->View->assign('data', $data);
		$this->View->assign('chunks', $chunks);

		$this->View->assign('editable', $this->can('edit'));

		$this->View->assign('inDialog', ($this->params['action'] == 'dialog'));
	}

	protected function _dialog()
	{
		$html = $this->html();

		$id = $this->params['id'];
		$data = $this->View->retrieve('data');

		// Value of the most descriptive field of this model?
		$field = $this->View->screen_names[$this->View->descr_field];
		$description = isset($data[$field]) ? $data[$field] : "con id {$id}";

		// Build Dialog title
		$title = "Detalle de {$this->View->name} {$description}";
		devMode() && ($title .= " <b>(objectID: {$id})</b>");

		return dialog($html, '#SimpleItem', array('title' => $title));
	}

}