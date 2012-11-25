<?php


class snp_ViewItem extends SNP
{

	protected function assignVars()
	{
		// Include sub-Snippets' HTML: bigTools and simpleItem
		$params = ['parent' => 'commonList', 'action' => 'html'];

		$this->assign('bigTools', self::read('bigTools'));
		$this->assign('comboList', self::read('comboList'));
		$this->assign('simpleItem', self::read('simpleItem'));
		$this->assign('tabs', self::read('tabs'));
	}

}