<?php

class snp_EditItem extends SNP
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
		extract($this->View->getItemData($id, true));

		// Let's put some more info on each entry
		$rFields = array_flip($fields);

		foreach ($data as $scrname => $value)
		{
			$key = $rFields[$scrname];

			$props = $fieldinfo[$key];

			$myData[$key] = array('name'  => $scrname,
			                      'value' => $value,
			                      'props' => $fieldinfo[$key]);
		}

		# Form data blocks (for presentational purposes)
		$chunks = array_chunk($myData, ceil(count($myData)/2), true);

		$this->View->assign('data', $data);
		$this->View->assign('chunks', $chunks);
		$this->View->assign('fieldinfo', $fieldinfo);

		$this->View->assign('objectID', $id);

		$this->View->assign('inDialog', true);
	}

	public function _dialog()
	{
		$html = $this->html();

		$id = $this->params['id'];
		$data = $this->View->retrieve('data');

		// Value of the most descriptive field of this model?
		$field = $this->View->screen_names[$this->View->descr_field];
		$description = isset($data[$field]) ? $data[$field] : "con id {$id}";

		// Build Dialog title
		$title = "Editar {$this->View->name} {$description}";
		devMode() && ($title .= " <b>(objectID: {$id})</b>");

		return dialog($html, '#EditItem', array('title' => $title));
	}

}