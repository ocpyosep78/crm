<?php


class snp_ViewItem extends SNP
{

	protected function assignVars() {}

	protected function _html()
	{
		return self::delegate('simpleItem', ['action' => 'html']);
	}

}