<?php

define('SNIPPETS_LAYERS', dirname(__FILE__).'/../layers');
define('SNIPPETS_LAYERS_ERROR', 'Snippets: Layers error');



class snp_Layers
{

	private $list=array();

	public function get($layer)
	{
		if (!empty($this->list[$layer]))
		{
			return $this->list[$layer];
		}

		$path = SNIPPETS_LAYERS."/{$layer}.layer.php";
		$class = 'snp_Layer_' . basename($layer);

		# Success
		is_file($path) && require_once $path;

		if (class_exists($class))
		{
			return $this->list[$layer] = new $class;
		}

		# Failure
		trigger_error('Snippets Layers: layer {$layer} not found.');
	}

}