<?php


class View_Product extends View
{

	// Basic properties (for the view, usually)
	protected $__name   = 'Producto';
	protected $__plural = 'Productos';
	protected $__gender = 'm';

	protected $__descr_field = 'name';  // Most descriptive field of the model

	// Screen name of each field (real or aliased)
	protected $__screen_names = [
//		'id_product' => '',
//		'id_category' => '',
		'category' => 'Categoría',
		'type' => 'Tipo',
		'name' => 'Nombre',
		'cost' => 'Costo',
		'price' => 'Precio',
		'description' => 'Descripción'];

	protected $__tabular_fields = [
		'category', 'type', 'name', 'price', 'description'];

}