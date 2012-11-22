<?php


class snp_ViewItem extends SNP
{

	protected function assignVars()
	{
		// Include sub-Snippets' HTML: bigTools and simpleItem
		$params = ['parent' => 'commonList', 'action' => 'html'];

		$this->View->assign('bigTools', self::read('bigTools'));
		$this->View->assign('comboList', self::read('comboList'));
		$this->View->assign('simpleItem', self::read('simpleItem'));
		$this->View->assign('tabs', self::read('tabs'));
	}

}