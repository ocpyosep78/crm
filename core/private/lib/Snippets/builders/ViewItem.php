<?php


class snp_ViewItem extends SNP
{

	protected function assignVars() {}

	protected function _html()
	{
		$params = array('action' => 'html') + $this->params;
		return self::snp('simpleItem', $params['model'], $params);
	}

}