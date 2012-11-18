<?php

class snp_CommonList extends SNP
{

	/**
	 * void SNP assignSnippetVars()
	 *      Vars that might be required by the view, besides the common ones.
	 *
	 * @return SNP
	 */
	protected function assignSnippetVars()
	{
		// Expected: $fields, $data, $hidden
		extract($this->View->getTabularData(30));

		// Get PK alias
		$scr = $this->View->screen_names;
		$pk = end(explode('.', array_shift($this->Model->getPk())));
		$primary = isset($scr[$pk]) ? $scr[$pk] : $pk;

		foreach ($fields as $field)
		{
			if (!in_array($field, $hidden))
			{
				$titles[] = addslashes($field);
			}
		}

		$tools = $this->getTools('view', 'create', 'edit', 'delete');

		$this->View->assign('titles' , $titles);
		$this->View->assign('data'   , $data);

		$this->View->assign('hidden' , $hidden);
		$this->View->assign('primary', $primary);
		$this->View->assign('toolTip', $this->View->descr_field);

		$this->View->assign('tools'  , $tools);

		return $this;
	}

	public function updateContent()
	{

	}

}