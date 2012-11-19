<?php

class snp_EditItem extends SNP
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
		extract($this->View->getItemData($this->params['filters'], true));

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

		$this->View->assign('chunks', $chunks);
		$this->View->assign('fieldinfo', $fieldinfo);

		$this->View->assign('objectID', $this->params['filters']);

		$this->View->assign('inDialog', true);
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
		$title = "Detalle de {$this->View->name}";

		if (devMode())
		{
			$title .= " <b>(objectID: {$this->params['filters']})</b>";
		}

		$dialogAtts = array('title' => $title);

		return dialog($this->html, '#editItem', $dialogAtts);
	}

}