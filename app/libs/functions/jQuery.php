<?php


class jQueryMethods
{

	private $selector;

	public function __construct($selector)
	{
		$this->selector = $selector;
	}

	public function __call($method, $arguments)
	{
		$selector = toJS($this->selector);
		$args = join(', ', array_map('toJS', $arguments));

		addScript("\$({$selector}).{$method}({$args})");

		return $this;
	}

}


function jQuery($selector='undefined')
{
	return new jQueryMethods($selector);
}