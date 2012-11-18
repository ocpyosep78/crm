<?php

class snp_ViewItem extends SNP
{

	/**
	 * void SNP assignSnippetVars()
	 *      Vars that might be required by the view, besides the common ones.
	 *
	 * @return SNP
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

		return $this;
	}

	public function lateTasks()
	{
		addElement('', '#viewItem');
		$this->params['writeTo'] = 'viewItem';

		parent::lateTasks();

		return addScript("\$('#viewItem').dialog({width:1000, modal:true, resizable:false})");
	}

}