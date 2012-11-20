<?php


class View_Customer extends View
{

	// Basic properties (for the view, usually)
	protected $__name   = 'Cliente';
	protected $__plural = 'Clientes';
	protected $__gender = 'm';

	protected $__descr_field = 'customer';// The most descriptive field of the model

	// Screen name of each field (real or aliased)
	protected $__screen_names = array(
		'number'         => 'Número',
		'customer'       => 'Empresa',
		'legal_name'     => 'Razón Social',
		'rut'            => 'RUT',
		'address'        => 'Dirección',
		'billingaddress' => 'Dir. de Facturación',
		'phone'          => 'Teléfono',
		'email'          => 'Email',
		'since'          => 'Fecha Ingreso',
		'subscribed'     => 'Subscripción',
		'location'       => 'Ciudad/Localidad',
		"CONCAT(`_users`.`name`, ' ', `_users`.`lastName`)" => 'Vendedor');

	protected $__tabular_fields = array(
		'id_customer', 'number', 'customer',
		'legal_name', 'address', 'phone',
		"CONCAT(`_users`.`name`, ' ', `_users`.`lastName`)"
	);


	protected function hash_field()
	{
		return "CONCAT(customer," .
		              "IF(legal_name = '', '', CONCAT(' (', legal_name, ')')))";
	}

}