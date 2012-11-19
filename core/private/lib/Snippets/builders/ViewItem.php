<?php

class snp_ViewItem extends SNP
{

	/**
	 * void void assignSnippetVars()
	 *      Vars that might be required by the view, besides the common ones.
	 *
	 * @return void
	 */
	protected function assignSnippetVars()
	{
		// Expected: $fields, $data, $toolTip, $hidden
		extract($this->View->getItemData($this->params['filters']));

		# Form data blocks (for presentational purposes)
		$chunks = array_chunk($data, ceil(count($data)/2), true);

		$this->View->assign('chunks', $chunks);
		$this->View->assign('objectID', $this->params['filters']);
		$this->View->assign('editable', $this->can('edit'));

		$this->View->assign('inDialog', true);

		// Value of the most descriptive field of this model?
		$description = isset($data[$this->View->descr_field])
			? $data[$this->View->descr_field]
			: "con id {$this->params['filters']}";

		$this->description_string = "{$this->View->name} {$description}";
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
		$title = "Detalle de {$this->description_string}";

		if (devMode())
		{
			$title .= " <b>(objectID: {$this->params['filters']})</b>";
		}

		$dialogAtts = array('title' => $title);

		return dialog($this->html, '#viewItem', $dialogAtts);
	}

}