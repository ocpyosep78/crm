<?php


class View_Customer extends View
{

	protected $__name   = 'Cliente';
	protected $__plural = 'Clientes';
	protected $__gender = 'm';

	protected $__actions = ['newTech'  => ['title'   => 'Nueva ficha técnica para el cliente',
	                                       'depends' => '__id__'],
	                        'newEvent' => ['title'   => 'Nuevo evento para el cliente',
	                                       'depends' => '__id__']];

	protected $__descr_field = "CONCAT(customer, IF(legal_name = '', '', CONCAT(' (', legal_name, ')')))";

	// Screen name of each field (real or aliased)
	protected $__screen_names = [
		'customer'       => 'Nombre Comercial',
		'legal_name'     => 'Razón Social',
		'rut'            => 'RUT',

		'number'         => 'Nº de Cliente',
		'since'          => 'Fecha de Ingreso',
		"CONCAT(`_users`.`name`, ' ', `_users`.`lastName`)" => 'Vendedor',

		'phone'          => 'Teléfono',
		'email'          => 'Email',
		'address'        => 'Dirección',
		'billingaddress' => 'Dir. de Facturación',
		'location'       => 'Ciudad/Localidad',
		'subscribed'     => 'Subscripción'];

	protected $__tabular_fields = [
		'number', 'customer', 'legal_name', 'address', 'phone',
		"CONCAT(`_users`.`name`, ' ', `_users`.`lastName`)"];


	public function snp_newTech($params)
	{
		return oNav()->getPage('createTechVisits', [NULL, $params['id']]);
	}

	public function snp_newEvent($params)
	{
		return oNav()->getPage('createEvent', [NULL, $params['id']]);
	}

}