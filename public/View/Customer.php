<?php


class View_Customer extends View
{

	protected $__name   = 'Cliente';
	protected $__plural = 'Clientes';
	protected $__gender = 'm';

	protected $__actions = ['newTech'  => ['title'   => 'Nueva ficha t�cnica para el cliente',
	                                       'depends' => '__id__'],
	                        'newEvent' => ['title'   => 'Nuevo evento para el cliente',
	                                       'depends' => '__id__']];

	protected $__descr_field = "CONCAT(customer, IF(legal_name = '', '', CONCAT(' (', legal_name, ')')))";

	// Screen name of each field (real or aliased)
	protected $__screen_names = [
		'customer'       => 'Nombre Comercial',
		'legal_name'     => 'Raz�n Social',
		'rut'            => 'RUT',

		'number'         => 'N� de Cliente',
		'since'          => 'Fecha de Ingreso',
		"CONCAT(`_users`.`name`, ' ', `_users`.`lastName`)" => 'Vendedor',

		'phone'          => 'Tel�fono',
		'email'          => 'Email',
		'address'        => 'Direcci�n',
		'billingaddress' => 'Dir. de Facturaci�n',
		'location'       => 'Ciudad/Localidad',
		'subscribed'     => 'Subscripci�n'];

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