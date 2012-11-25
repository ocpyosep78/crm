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

		// Expected: $fields, $fieldinfo, $data
		extract($this->View->getItemData($id));

		// Part data into chunks, for presentational purposes
		$realfields = count($data);

		foreach ($data as $key => $item)
		{
			$realfields -= preg_match('#^__.+__$#', $key);
		}

		// "Pad" data if necessary to have an even amount of items
		($realfields % 2) && array_splice($data, $realfields, 0, ['' => '']);

		// Build two chunks of equal size
		$chunks = array_chunk($data, ceil($realfields/2), true);
		array_splice($chunks, 2);

		// Value of the most descriptive field of this model?
		$description = empty($data['__description__'])
			? "con id {$id}"
			: $data['__description__'];

		$title = "Detalle de {$this->View->name} {$description}";
		devMode() && ($title .= " <b>(objectID: {$id})</b>");

		$this->assign('title', $title);
		$this->assign('data', $data);
		$this->assign('chunks', $chunks);

		$this->assign('image', $this->View->getItemImage($id));
		$this->assign('img_height', (count($chunks[0]) * 24 + 34) . 'px');

		$this->assign('inDialog', ($this->params['action'] == 'dialog'));
		$this->assign('editable', $this->can('edit'));
	}

	protected function _dialog()
	{
		$html = $this->html();

		$id = $this->params['id'];
		$title = $this->retrieve('title');

		return $this->dialog($html, '#SimpleItem', ['title' => $title]);
	}

}