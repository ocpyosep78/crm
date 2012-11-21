<?php


class Model_CustomerPotential extends Model_Customer
{

	public function find()
	{
		$this->where('ISNULL(`since`)');

		$args = func_get_args() + array(NULL, NULL, NULL, NULL);

		return parent::find($args[0], $args[1], $args[2], $args[3]);
	}

}