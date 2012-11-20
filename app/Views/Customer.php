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
		'number'         => 'N�mero',
		'customer'       => 'Empresa',
		'legal_name'     => 'Raz�n Social',
		'rut'            => 'RUT',
		'address'        => 'Direcci�n',
		'billingaddress' => 'Dir. de Facturaci�n',
		'phone'          => 'Tel�fono',
		'email'          => 'Email',
		'since'          => 'Fecha Ingreso',
		'subscribed'     => 'Subscripci�n',
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